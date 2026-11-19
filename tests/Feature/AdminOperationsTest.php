<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingRate;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_the_admin_console(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_a_customer_cannot_open_admin_pages_or_write_catalog_and_settings(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/products')->assertForbidden();
        $this->post('/admin/categories', ['name' => 'Injeksi'])->assertForbidden();
        $this->put('/admin/settings', [])->assertForbidden();
        $this->get('/admin/payments/1/proof')->assertForbidden();
    }

    public function test_an_admin_without_an_mfa_session_is_redirected_to_security(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/admin')->assertRedirect(route('security'));
    }

    public function test_an_administrator_can_open_each_console_section(): void
    {
        $this->signInAdmin();
        $this->withoutVite();

        foreach (['', '/products', '/categories', '/orders', '/memberships', '/tickets', '/settings', '/shipping-rates'] as $path) {
            $this->get('/admin'.$path)->assertOk();
        }
    }

    public function test_sales_summary_counts_only_verified_product_values(): void
    {
        $this->signInAdmin();
        $this->withoutVite();
        $variant = $this->reservedVariant();
        $this->paymentReviewOrder($variant);
        $paid = $this->paymentReviewOrder($variant);
        $paid->update(['status' => 'processing']);

        $this->get('/admin')->assertOk()
            ->assertViewHas('counts', fn (array $counts): bool => $counts['sales'] === 250000)
            ->assertSee('Rp250.000');
    }

    public function test_an_administrator_can_create_a_product_with_images_and_a_variant(): void
    {
        $this->signInAdmin();
        $this->withoutVite();
        Storage::fake('public');

        $this->post('/admin/categories', ['name' => 'Seragam sekolah'])->assertRedirect('/admin/categories');
        $category = Category::query()->where('slug', 'seragam-sekolah')->firstOrFail();
        $this->post('/admin/products', [
            'name' => 'Batik Reguler',
            'category_id' => $category->id,
            'active' => 1,
            'members_only' => 1,
            'photos' => [UploadedFile::fake()->image('batik.jpg')],
        ])->assertSessionHasNoErrors();

        $product = Product::query()->where('slug', 'batik-reguler')->firstOrFail();
        self::assertTrue($product->members_only);
        Storage::disk('public')->assertExists($product->images()->sole()->path);

        $this->post(route('admin.products.variants.store', $product), [
            'name' => 'Ukuran M',
            'sku' => 'BATIK-M',
            'price' => 125000,
            'stock' => 8,
            'lead_days' => 0,
            'active' => 1,
        ])->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseHas('variants', ['product_id' => $product->id, 'sku' => 'BATIK-M', 'stock' => 8]);
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('BATIK-M');
    }

    public function test_stock_cannot_be_reduced_below_reservations(): void
    {
        $this->signInAdmin();
        $variant = $this->reservedVariant();

        $this->put(route('admin.products.variants.update', [$variant->product, $variant->id]), [
            'name' => 'Ukuran M',
            'sku' => 'BATIK-M',
            'price' => 125000,
            'stock' => 1,
            'lead_days' => 0,
            'active' => 1,
        ])->assertSessionHasErrors('stock');

        $this->assertDatabaseHas('variants', ['id' => $variant->id, 'stock' => 8, 'reserved' => 2]);
    }

    public function test_product_deletion_preserves_order_history(): void
    {
        $this->signInAdmin();
        $variant = $this->reservedVariant();
        $order = $this->paymentReviewOrder($variant);

        $this->delete(route('admin.products.destroy', $variant->product))->assertRedirect('/admin/products');

        self::assertFalse($variant->product->refresh()->active);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'variant_id' => $variant->id]);
    }

    public function test_payment_proof_requires_admin_and_approval_consumes_only_reserved_stock(): void
    {
        $admin = $this->signInAdmin();
        $this->withoutVite();
        $variant = $this->reservedVariant();
        $order = $this->paymentReviewOrder($variant);
        $payment = $order->payments()->create([
            'path' => 'payment-proofs/4c4783e5-7183-4db5-b695-27a1dd5dc79d.jpg',
            'mime' => 'image/jpeg',
            'status' => 'pending',
        ]);
        Storage::fake('local')->put($payment->path, 'private proof fixture');

        $url = route('admin.payments.proof', $payment);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertStreamedContent('private proof fixture');

        $this->actingAs(User::factory()->create());
        $this->get($url)->assertForbidden();

        $this->actingAs($admin)->withSession(['admin_mfa_user' => $admin->id]);
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('Buka bukti');
        $this->patch(route('admin.payments.update', $payment), ['action' => 'approve'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'approved', 'reviewed_by' => $admin->id]);
        $this->assertDatabaseHas('variants', ['id' => $variant->id, 'stock' => 6, 'reserved' => 0]);
        self::assertSame('processing', $order->refresh()->status);

        $this->patch(route('admin.orders.update', $order), ['action' => 'ship', 'tracking_number' => 'RESI123'])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped', 'tracking_number' => 'RESI123']);
    }

    public function test_membership_review_applies_to_pending_requests_only(): void
    {
        $customer = User::factory()->create();
        $membership = Membership::query()->create(['user_id' => $customer->id, 'school_name' => 'Sekolah Nusantara']);
        $this->signInAdmin();
        $this->withoutVite();

        $this->get('/admin/memberships')->assertOk()->assertSee('Sekolah Nusantara');
        $url = route('admin.memberships.update', $membership);
        $this->patch($url, ['status' => 'rejected'])->assertSessionHasErrors('note');
        $this->patch($url, ['status' => 'approved'])->assertSessionHasNoErrors();
        $this->patch($url, ['status' => 'rejected', 'note' => 'Tanggapan kedua'])->assertSessionHasErrors('membership');

        self::assertSame('approved', $membership->refresh()->status);
    }

    public function test_shop_settings_are_saved_using_the_storefront_keys_and_shapes(): void
    {
        $this->signInAdmin();
        $this->withoutVite();
        $settings = [
            'banner' => ['title' => 'Koleksi Sekolah', 'active' => 1],
            'faqs' => ['3' => ['question' => 'Kapan dikirim?', 'answer' => 'Dalam dua hari.']],
            'contact' => ['email' => 'admin@example.com'],
            'bank' => ['name' => 'Bank Demo', 'number' => '0012345678', 'holder' => 'Borongin'],
        ];
        $this->put(route('admin.settings.update'), $settings)->assertRedirect(route('admin.settings.edit'));

        self::assertEquals($settings['faqs']['3'], Setting::valueFor('faqs')[0]);
        self::assertSame('0012345678', Setting::valueFor('bank')['number']);
        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Kapan dikirim?');
    }

    public function test_pickup_rates_are_saved_without_a_province_or_fee(): void
    {
        $this->signInAdmin();
        $this->withoutVite();

        $this->post('/admin/shipping-rates', [
            'name' => 'Ambil di toko',
            'region' => 'DKI Jakarta',
            'price' => 20000,
            'active' => 1,
            'pickup' => 1,
        ])->assertRedirect('/admin/shipping-rates');

        $rate = ShippingRate::query()->where('name', 'Ambil di toko')->sole();
        self::assertNull($rate->region);
        self::assertSame(0, $rate->price);
        $this->get(route('admin.shipping-rates.edit', $rate))->assertOk()->assertSee('Ambil di toko');
    }

    public function test_an_administrator_can_respond_to_a_return_request(): void
    {
        $this->signInAdmin();
        $this->withoutVite();
        $order = $this->paymentReviewOrder($this->reservedVariant());
        $ticket = $order->tickets()->create([
            'kind' => 'return',
            'subject' => 'Ukuran terlalu kecil',
            'message' => 'Bisakah ukuran M ditukar?',
        ]);
        $this->get(route('admin.tickets.index', ['kind' => 'return']))->assertOk()->assertSee('Ukuran terlalu kecil');
        $this->get(route('admin.tickets.show', $ticket))->assertOk()->assertSee('Bisakah ukuran M ditukar?');

        $this->patch(route('admin.tickets.update', $ticket), [
            'status' => 'answered',
            'response' => 'Silakan bawa barang yang belum dipakai ke toko.',
        ])->assertRedirect(route('admin.tickets.show', $ticket));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'answered',
            'response' => 'Silakan bawa barang yang belum dipakai ke toko.',
        ]);
    }

    private function signInAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'two_factor_secret' => encrypt('test-factor'),
            'two_factor_confirmed_at' => now(),
        ]);
        $this->actingAs($admin)->withSession(['admin_mfa_user' => $admin->id]);

        return $admin;
    }

    private function reservedVariant(): Variant
    {
        $category = Category::query()->create(['name' => 'Seragam', 'slug' => 'seragam']);
        $product = $category->products()->create(['name' => 'Batik', 'slug' => 'batik']);

        return $product->variants()->create([
            'name' => 'Ukuran M', 'sku' => 'BATIK-M', 'price' => 125000, 'stock' => 8, 'reserved' => 2,
        ]);
    }

    private function paymentReviewOrder(Variant $variant): Order
    {
        $order = Order::query()->create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Pembeli Demo',
            'email' => 'pembeli@example.com',
            'phone' => '081234567890',
            'address' => ['address_line' => 'Jalan Demo', 'province' => 'DKI Jakarta', 'city' => 'Jakarta', 'postal_code' => '11234'],
            'shipping_name' => 'Kurir',
            'shipping_fee' => 10000,
            'subtotal' => 250000,
            'total' => 260000,
            'status' => 'payment_review',
            'payment_due_at' => now()->addDay(),
            'idempotency_key' => Str::random(32),
        ]);
        $order->items()->create([
            'variant_id' => $variant->id,
            'product_name' => 'Batik',
            'variant_name' => 'Ukuran M',
            'sku' => 'BATIK-M',
            'unit_price' => 125000,
            'quantity' => 2,
        ]);

        return $order;
    }
}
