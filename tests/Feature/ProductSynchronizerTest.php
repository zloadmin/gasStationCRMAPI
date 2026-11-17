<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Usrbb\ProductSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return json_decode(file_get_contents(base_path('tests/Fixtures/usrbb/products.json')), true);
    }

    public function test_it_stores_products(): void
    {
        $synced = app(ProductSynchronizer::class)->sync($this->payload());

        $this->assertSame(2, $synced);

        $product = Product::findOrFail(183);
        $this->assertSame('АИ-95', $product->name);
        $this->assertSame('70.70', $product->price);
        $this->assertSame(643, $product->currency);
        $this->assertSame('АИ-95-К5', $product->good_name);
        $this->assertSame('excise', $product->good_type);
        $this->assertSame('a95', $product->yandex_type);
        $this->assertFalse($product->archived);
        $this->assertNull($product->data);
        $this->assertSame('2026-11-01 11:48:35', $product->remote_updated_at->toDateTimeString());
    }

    public function test_resync_updates_existing_products(): void
    {
        $synchronizer = app(ProductSynchronizer::class);
        $synchronizer->sync($this->payload());

        $payload = $this->payload();
        $payload['rows'][1]['price'] = '66.10';
        $synchronizer->sync($payload);

        $this->assertSame(2, Product::count());
        $this->assertSame('66.10', Product::findOrFail(182)->price);
    }
}
