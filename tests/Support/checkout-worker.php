<?php

use App\Services\Checkout;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[, $variantId, $rateId, $client, $key] = $argv;
DB::select('SELECT set_config(?, ?, false)', ['application_name', $client]);
echo "ready\n";

if (trim((string) fgets(STDIN)) !== 'checkout') {
    exit(1);
}

try {
    $order = $app->make(Checkout::class)->place([(int) $variantId => 1], [
        'name' => 'Budi',
        'email' => 'budi@example.test',
        'phone' => '081234567890',
        'shipping_rate_id' => (int) $rateId,
        'idempotency_key' => $key,
    ], null);

    echo $order->wasRecentlyCreated ? 'created' : 'replayed';
} catch (ValidationException $exception) {
    if (! isset($exception->errors()['cart'])) {
        throw $exception;
    }

    echo 'sold_out';
}
