<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 24)->default('customer');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('members_only')->default(false);
            $table->boolean('featured')->default(false);
            $table->timestamps();
            $table->index(['category_id', 'active']);
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->bigInteger('price');
            $table->integer('stock')->default(0);
            $table->integer('reserved')->default(0);
            $table->boolean('preorder')->default(false);
            $table->integer('lead_days')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('region')->nullable();
            $table->bigInteger('price');
            $table->boolean('pickup')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            $table->jsonb('address')->nullable();
            $table->string('shipping_name');
            $table->bigInteger('shipping_fee');
            $table->bigInteger('subtotal');
            $table->bigInteger('total');
            $table->integer('eta_days')->default(0);
            $table->string('status', 32)->default('awaiting_payment');
            $table->timestampTz('payment_due_at');
            $table->string('tracking_number', 100)->nullable();
            $table->string('idempotency_key', 128)->unique();
            $table->timestamps();
            $table->index(['status', 'payment_due_at']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->string('variant_name');
            $table->string('sku');
            $table->bigInteger('unit_price');
            $table->integer('quantity');
            $table->integer('lead_days')->default(0);
            $table->timestamps();
            $table->unique(['order_id', 'variant_id']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime', 60);
            $table->string('status', 24)->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 24);
            $table->string('subject');
            $table->text('message');
            $table->string('status', 24)->default('open');
            $table->text('response')->nullable();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('school_name');
            $table->string('status', 24)->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->jsonb('value');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE variants ADD CONSTRAINT variants_valid_stock CHECK (stock >= reserved AND reserved >= 0 AND price >= 0 AND lead_days >= 0)');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_valid_totals CHECK (subtotal >= 0 AND shipping_fee >= 0 AND total = subtotal + shipping_fee AND eta_days >= 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_valid_quantity CHECK (quantity > 0 AND unit_price >= 0 AND lead_days >= 0)');
    }

    public function down(): void
    {
        foreach (['settings', 'memberships', 'tickets', 'payments', 'order_items', 'orders', 'shipping_rates', 'variants', 'product_images', 'products', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn([
            'role', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        ]));
    }
};
