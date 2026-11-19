<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingRate;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Seed demo hanya tersedia untuk pengembangan atau pengujian.');
        }

        DB::transaction(function (): void {
            $categories = [
                'batik' => 'Batik', 'seragam' => 'Seragam', 'buku' => 'Buku',
                'aksesoris' => 'Aksesoris', 'pramuka' => 'Pramuka', 'perlengkapan-sekolah' => 'Perlengkapan sekolah',
            ];
            foreach ($categories as $slug => $name) {
                Category::firstOrCreate(['slug' => $slug], [
                    'name' => $name, 'description' => 'Pilihan '.$name.' untuk kebutuhan sekolah dan koperasi.',
                    'image_path' => 'images/catalog/cat-'.$slug.'.jpg',
                ]);
            }

            $rows = json_decode(file_get_contents(__DIR__.'/data/catalog.json'), true, 512, JSON_THROW_ON_ERROR);
            foreach ($rows as $row) {
                $product = Product::firstOrCreate(['slug' => $row['slug']], [
                    'category_id' => Category::whereSlug($row['category'])->firstOrFail()->id,
                    'name' => $row['name'],
                    'description' => $row['name'].' adalah bagian dari katalog contoh Borongin. Lihat pilihan varian dan estimasi pengiriman sebelum memesan.',
                    'active' => true, 'featured' => $row['featured'], 'members_only' => $row['members_only'],
                ]);
                $product->images()->firstOrCreate(['path' => $row['image_path']], ['alt' => $row['name'], 'position' => 0]);
                $options = $row['category'] === 'seragam' ? ['S', 'M', 'L', 'XL'] : ['Reguler'];
                foreach ($options as $index => $option) {
                    $product->variants()->firstOrCreate(['sku' => $row['sku'].'-'.($index + 1)], [
                        'name' => $option, 'price' => $row['price'] + $index * 5000,
                        'stock' => $row['preorder'] ? 16 : 24 - $index * 3,
                        'reserved' => 0, 'preorder' => $row['preorder'],
                        'lead_days' => $row['preorder'] ? 10 : 2, 'active' => true,
                    ]);
                }
            }

            foreach ([
                ['Ambil di toko', null, 0, true],
                ['Pengiriman Jawa Barat', 'Jawa Barat', 18000, false],
                ['Pengiriman DKI Jakarta', 'DKI Jakarta', 20000, false],
                ['Pengiriman Jawa Tengah', 'Jawa Tengah', 26000, false],
                ['Pengiriman Banten', 'Banten', 24000, false],
            ] as [$name, $region, $price, $pickup]) {
                ShippingRate::firstOrCreate(['name' => $name], compact('region', 'price', 'pickup'));
            }
            foreach ([
                'bank' => ['name' => 'Bank Contoh', 'number' => '0000000000', 'holder' => 'Borongin Demo'],
                'contact' => ['email' => 'hello@example.test', 'phone' => '', 'address' => 'Lokasi contoh koperasi'],
                'banner' => ['title' => 'Pilihan baik untuk sekolah.', 'description' => 'Batik, seragam, dan perlengkapan belajar pilihan koperasi.', 'image_path' => null],
                'faqs' => [
                    ['question' => 'Bagaimana cara membayar?', 'answer' => 'Transfer sesuai instruksi yang tampil, lalu unggah bukti dari halaman pesanan.'],
                    ['question' => 'Apa itu pre-order?', 'answer' => 'Barang dipersiapkan sesuai estimasi pada produk. Pengiriman dilakukan saat pesanan siap.'],
                ],
            ] as $key => $value) {
                Setting::firstOrCreate(compact('key'), compact('value'));
            }

            $customer = User::firstOrCreate(['email' => 'pelanggan@example.test'], [
                'name' => 'Pelanggan Contoh', 'password' => Str::password(32),
            ]);
            $school = User::firstOrCreate(['email' => 'sekolah@example.test'], [
                'name' => 'Anggota Sekolah Contoh', 'password' => Str::password(32),
            ]);
            Membership::firstOrCreate(['user_id' => $school->id], [
                'school_name' => 'SDIT Contoh Nusantara', 'status' => 'approved',
            ]);
            $variant = Variant::where('sku', 'BRG-001-1')->firstOrFail();

            foreach (['completed', 'shipped', 'awaiting_payment'] as $status) {
                $order = Order::firstOrCreate(['idempotency_key' => 'seed-borongin-'.$status], [
                    'public_id' => (string) Str::ulid(), 'user_id' => $customer->id,
                    'name' => $customer->name, 'email' => $customer->email, 'phone' => '080000000000',
                    'address' => ['province' => 'Jawa Barat', 'city' => 'Depok', 'address_line' => 'Alamat contoh', 'postal_code' => '16411'],
                    'shipping_name' => 'Pengiriman Jawa Barat', 'shipping_fee' => 18000,
                    'subtotal' => $variant->price, 'total' => $variant->price + 18000,
                    'eta_days' => $variant->lead_days, 'status' => $status,
                    'payment_due_at' => now()->addDays(7),
                    'tracking_number' => $status === 'awaiting_payment' ? null : 'DEMO-'.strtoupper($status),
                ]);
                if ($order->wasRecentlyCreated) {
                    $order->items()->create([
                        'variant_id' => $variant->id, 'product_name' => $variant->product->name,
                        'variant_name' => $variant->name, 'sku' => $variant->sku, 'unit_price' => $variant->price,
                        'quantity' => 1, 'lead_days' => $variant->lead_days,
                    ]);
                    if ($status === 'awaiting_payment') {
                        $variant->increment('reserved');
                    }
                }
            }
        });
    }
}
