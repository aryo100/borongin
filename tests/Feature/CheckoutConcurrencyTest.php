<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\Variant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class CheckoutConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_simultaneous_checkouts_compete_for_the_last_unit(): void
    {
        $this->compete(Str::uuid()->toString(), Str::uuid()->toString(), ['created', 'sold_out']);
    }

    public function test_simultaneous_submissions_with_the_same_key_replay_the_order(): void
    {
        $key = Str::uuid()->toString();
        $this->compete($key, $key, ['created', 'replayed']);
    }

    private function compete(string $firstKey, string $secondKey, array $expected): void
    {
        $category = Category::create(['slug' => 'stationery', 'name' => 'Stationery']);
        $product = Product::create(['category_id' => $category->id, 'slug' => 'pencil', 'name' => 'Pensil']);
        $variant = $product->variants()->create(['sku' => 'PEN-HB', 'name' => 'HB', 'price' => 24000, 'stock' => 1]);
        $rate = ShippingRate::create(['name' => 'Ambil di toko', 'price' => 0, 'pickup' => true]);
        $run = Str::random(12);
        $clients = ["checkout-{$run}-a", "checkout-{$run}-b"];
        $workers = [
            $this->worker($variant, $rate, $clients[0], $firstKey),
            $this->worker($variant, $rate, $clients[1], $secondKey),
        ];
        $inputs = [new InputStream, new InputStream];

        try {
            foreach ($workers as $index => $worker) {
                $worker->setInput($inputs[$index]);
                $worker->start();
            }
            foreach ($workers as $worker) {
                $ready = $worker->waitUntil(fn (string $type, string $output): bool => $type === Process::OUT && str_contains($output, "ready\n"));
                $this->assertTrue($ready, $worker->getOutput().$worker->getErrorOutput());
                $worker->clearOutput();
            }

            // Let both worker connections reach a lock before releasing the last unit.
            DB::transaction(function () use ($variant, $clients, $inputs, $workers): void {
                Variant::query()->lockForUpdate()->findOrFail($variant->id);
                foreach ($inputs as $input) {
                    $input->write("checkout\n");
                    $input->close();
                }

                $deadline = microtime(true) + 15;
                do {
                    DB::select('SELECT pg_stat_clear_snapshot()');
                    $blocked = DB::scalar("SELECT count(*) FROM pg_stat_activity WHERE application_name IN (?, ?) AND wait_event_type = 'Lock'", $clients);
                    if ((int) $blocked === 2) {
                        return;
                    }

                    foreach ($workers as $worker) {
                        if (! $worker->isRunning()) {
                            $this->fail('A checkout process exited before locking stock. '.$worker->getOutput().$worker->getErrorOutput());
                        }
                    }
                    usleep(20000);
                } while (microtime(true) < $deadline);

                $this->fail('Both checkout processes must overlap at the stock lock.');
            });

            $results = [];
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getOutput().$worker->getErrorOutput());
                $results[] = trim($worker->getOutput());
            }
            sort($results);

            $this->assertSame($expected, $results);
            $this->assertDatabaseCount('orders', 1);
            $this->assertSame(24000, Order::sole()->total);
            $this->assertSame(1, $variant->fresh()->reserved);
        } finally {
            foreach ($workers as $worker) {
                $worker->stop(1);
            }
        }
    }

    private function worker(Variant $variant, ShippingRate $rate, string $client, string $key): Process
    {
        return new Process([
            PHP_BINARY, 'tests/Support/checkout-worker.php', (string) $variant->id, (string) $rate->id, $client, $key,
        ], base_path(), [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => DB::getDefaultConnection(),
            'DB_URL' => config('database.connections.'.DB::getDefaultConnection().'.url'),
        ], timeout: 75);
    }
}
