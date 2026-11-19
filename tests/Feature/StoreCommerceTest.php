<?php

namespace Tests\Feature;

use App\Mail\OrderAccess;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\User;
use App\Models\Variant;
use App\Services\OrderLifecycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreCommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_checkout_keeps_order_prices_when_the_catalog_changes(): void
    {
        $this->withoutVite();
        Mail::fake();
        $variant = $this->variant(['stock' => 2]);
        $rate = $this->rate();

        $this->post('/cart', ['variant_id' => $variant->id, 'quantity' => 1])->assertRedirect('/cart');
        $this->patch('/cart/'.$variant->id, ['quantity' => 2])->assertRedirect('/cart');
        $this->get('/checkout')->assertOk()->assertViewHas('subtotal', 48000);
        $this->post('/checkout', $this->buyer($rate, session('store.checkout_key')))->assertRedirect();
        $order = Order::sole();

        $this->assertEqualsWithDelta(now()->addDays(7)->timestamp, $order->payment_due_at->timestamp, 2);

        $variant->update(['price' => 28000]);
        $rate->update(['price' => 12000]);
        $this->assertSame(24000, $order->items()->sole()->unit_price);
        $this->assertSame(10000, $order->shipping_fee);
        $this->get('/orders/'.$order->public_id)->assertOk()->assertSee('Rp58.000');
    }

    public function test_a_second_cart_cannot_reserve_the_last_preorder_unit(): void
    {
        Mail::fake();
        $variant = $this->variant(['preorder' => true, 'lead_days' => 5]);
        $rate = $this->rate();

        $this->checkout($variant, $rate)->assertRedirect();
        $this->checkout($variant, $rate)->assertSessionHasErrors('cart');

        $order = Order::sole();
        $this->assertSame(1, $variant->fresh()->reserved);
        $this->assertSame(5, $order->eta_days);
        $this->assertSame(34000, $order->total);
    }

    public function test_a_retried_checkout_creates_only_one_order_and_reservation(): void
    {
        Mail::fake();
        $variant = $this->variant();
        $rate = $this->rate();
        $key = Str::uuid()->toString();

        $first = $this->checkout($variant, $rate, $key);
        $first->assertRedirect();
        $this->post('/checkout', $this->buyer($rate, $key))
            ->assertRedirect($first->headers->get('Location'));

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(1, $variant->fresh()->reserved);
    }

    public function test_checkout_rejects_a_mismatched_shipping_region(): void
    {
        $variant = $this->variant();
        $this->checkout($variant, $this->rate(), buyer: ['province' => 'Banten'])
            ->assertSessionHasErrors('shipping_rate_id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, $variant->fresh()->reserved);
    }

    public function test_pending_members_cannot_checkout_members_only_products(): void
    {
        $variant = $this->variant(product: ['members_only' => true]);
        $user = User::factory()->create();
        $user->membership()->create(['school_name' => 'Sekolah Nusantara', 'status' => 'pending']);

        $this->actingAs($user);
        $this->checkout($variant, $this->rate())->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, $variant->fresh()->reserved);
    }

    public function test_guest_access_requires_a_live_signed_link_and_is_scoped_to_one_order(): void
    {
        Mail::fake();
        $this->checkout($this->variant(), $this->rate())->assertRedirect();
        $order = Order::sole();
        $path = '/orders/'.$order->public_id;

        $this->withSession(['store.guest_orders' => []]);
        $this->get($path)->assertForbidden();
        $expired = URL::temporarySignedRoute('store.orders.access', now()->subMinute(), ['order' => $order->public_id], absolute: false);
        $this->get($expired)->assertForbidden();
        $valid = URL::temporarySignedRoute('store.orders.access', now()->addMinute(), ['order' => $order->public_id], absolute: false);
        $this->get($valid.'&changed=1')->assertForbidden();
        $this->get($valid)->assertRedirect($path);
        $this->get($path.'/invoice')->assertOk()->assertHeader('content-type', 'application/pdf');

        $second = $order->replicate(['public_id', 'idempotency_key']);
        $second->public_id = Str::ulid()->toString();
        $second->idempotency_key = Str::uuid()->toString();
        $second->save();
        $this->get('/orders/'.$second->public_id.'/invoice')->assertForbidden();
        $this->post('/orders/'.$second->public_id.'/payment', [])->assertForbidden();
        $this->post('/orders/'.$second->public_id.'/tickets', [])->assertForbidden();
    }

    public function test_order_lookup_mails_the_signed_link_only_for_the_matching_guest_email(): void
    {
        Mail::fake();
        $this->checkout($this->variant(), $this->rate())->assertRedirect();
        $order = Order::sole();
        $this->withSession(['store.guest_orders' => []]);

        $this->post('/orders/lookup', ['public_id' => $order->public_id, 'email' => 'different@example.test'])
            ->assertRedirect('/orders/lookup');
        Mail::assertQueuedCount(1);

        $this->post('/orders/lookup', ['public_id' => $order->public_id, 'email' => 'BUDI@example.test'])
            ->assertRedirect('/orders/lookup');
        Mail::assertQueuedCount(2);
        Mail::assertQueued(OrderAccess::class, fn (OrderAccess $mail) => $mail->hasTo('budi@example.test')
            && str_starts_with($mail->url, rtrim(config('app.url'), '/').'/orders/'.$order->public_id.'/access?'));
        $this->get('/orders/'.$order->public_id)->assertForbidden();
    }

    public function test_a_signed_guest_link_cannot_grant_access_to_a_registered_order(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());
        $this->checkout($this->variant(), $this->rate())->assertRedirect();
        $order = Order::sole();
        $url = URL::temporarySignedRoute('store.orders.access', now()->addMinute(), ['order' => $order->public_id], absolute: false);

        $this->actingAs(User::factory()->create());
        $this->get('/orders/'.$order->public_id.'/invoice')->assertForbidden();
        $this->get($url)->assertForbidden();
    }

    public function test_proof_is_reencoded_on_private_storage_before_stock_is_committed(): void
    {
        Mail::fake();
        Storage::fake('local');
        Storage::fake('public');
        $variant = $this->variant();
        $this->checkout($variant, $this->rate())->assertRedirect();
        $order = Order::sole();
        $path = '/orders/'.$order->public_id.'/payment';

        $this->post($path, ['proof' => UploadedFile::fake()->createWithContent('proof.php', '<?php echo 1;')])
            ->assertSessionHasErrors('proof');
        $this->post($path, ['proof' => UploadedFile::fake()->image('proof.png')])->assertRedirect();

        $payment = $order->payments()->sole();
        Storage::disk('local')->assertExists($payment->path);
        Storage::disk('public')->assertMissing($payment->path);
        $this->assertSame(IMAGETYPE_JPEG, getimagesizefromstring(Storage::disk('local')->get($payment->path))[2]);
        $this->assertSame('payment_review', $order->fresh()->status);
        $this->assertSame(1, $variant->fresh()->reserved);

        $admin = User::factory()->create(['role' => 'admin']);
        app(OrderLifecycle::class)->approvePayment($payment, $admin);

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(0, $variant->fresh()->reserved);
        $this->assertSame(0, $variant->fresh()->stock);
    }

    public function test_unpaid_order_expiry_releases_reservations_once(): void
    {
        Mail::fake();
        $variant = $this->variant();
        $rate = $this->rate();
        $this->checkout($variant, $rate)->assertRedirect();
        $order = Order::sole();
        $order->update(['payment_due_at' => now()->subMinute()]);

        $lifecycle = app(OrderLifecycle::class);
        $this->assertSame(1, $lifecycle->expireDue());
        $this->assertSame(0, $lifecycle->expireDue());
        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame(0, $variant->fresh()->reserved);
        $this->assertSame(1, $variant->fresh()->stock);
        $this->checkout($variant, $rate)->assertRedirect();
        $this->assertDatabaseCount('orders', 2);
    }

    private function variant(array $values = [], array $product = []): Variant
    {
        $category = Category::create(['slug' => 'stationery', 'name' => 'Stationery']);
        $product = Product::create($product + [
            'category_id' => $category->id,
            'slug' => 'pencil',
            'name' => 'Pensil',
            'description' => 'Pensil kayu',
            'active' => true,
        ]);

        return $product->variants()->create($values + [
            'sku' => 'PEN-HB', 'name' => 'HB', 'price' => 24000, 'stock' => 1, 'active' => true,
        ]);
    }

    private function rate(): ShippingRate
    {
        return ShippingRate::create(['name' => 'Reguler', 'region' => 'DKI Jakarta', 'price' => 10000, 'active' => true]);
    }

    private function checkout(Variant $variant, ShippingRate $rate, ?string $key = null, array $buyer = []): TestResponse
    {
        $key ??= Str::uuid()->toString();

        return $this->withSession(['store.cart' => [$variant->id => 1], 'store.checkout_key' => $key])
            ->post('/checkout', $buyer + $this->buyer($rate, $key));
    }

    private function buyer(ShippingRate $rate, string $key): array
    {
        return [
            'name' => 'Budi', 'email' => 'budi@example.test', 'phone' => '081234567890',
            'shipping_rate_id' => $rate->id, 'idempotency_key' => $key,
            'address_line' => 'Jalan Melati 10', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '10110',
        ];
    }
}
