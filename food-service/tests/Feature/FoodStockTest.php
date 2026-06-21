<?php

namespace Tests\Feature;

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_check_food_stock(): void
    {
        $food = Food::create([
            'name' => 'Nasi Goreng',
            'price' => 15000,
            'qty' => 5,
        ]);

        $response = $this->getJson("/api/foods/{$food->id}/stock?quantity=3");

        $response->assertOk()
            ->assertJson([
                'food_id' => $food->id,
                'qty' => 5,
                'requested_quantity' => 3,
                'is_available' => true,
            ]);
    }

    public function test_it_can_decrease_food_stock(): void
    {
        $food = Food::create([
            'name' => 'Mie Ayam',
            'price' => 12000,
            'qty' => 5,
        ]);

        $response = $this->patchJson("/api/foods/{$food->id}/stock", [
            'operation' => 'decrease',
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Food stock updated',
                'previous_qty' => 5,
                'qty' => 3,
            ]);

        $this->assertSame(3, (int) $food->fresh()->qty);
    }

    public function test_it_rejects_stock_decrease_that_would_make_qty_negative(): void
    {
        $food = Food::create([
            'name' => 'Es Teh',
            'price' => 5000,
            'qty' => 1,
        ]);

        $response = $this->patchJson("/api/foods/{$food->id}/stock", [
            'operation' => 'decrease',
            'quantity' => 2,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Insufficient food stock',
                'current_qty' => 1,
                'requested_quantity' => 2,
            ]);

        $this->assertSame(1, (int) $food->fresh()->qty);
    }
}
