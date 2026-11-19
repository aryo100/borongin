<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_is_repeatable(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertDatabaseCount('products', 24);
        $this->assertDatabaseCount('categories', 6);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseHas('variants', ['sku' => 'BRG-001-1', 'reserved' => 1]);
    }
}
