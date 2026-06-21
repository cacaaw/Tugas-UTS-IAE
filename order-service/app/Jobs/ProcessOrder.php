<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch food details to check stock
        $foodResponse = Http::timeout(30)->get(
            rtrim(config('services.food_service.url'), '/') . '/api/foods/' . $this->order->food_id
        );

        if (! $foodResponse->successful()) {
            Log::error('ProcessOrder: Failed to fetch food from FoodService', ['order_id' => $this->order->id]);
            $this->order->update(['status' => 'failed_food_service_down']);
            return;
        }

        $food = $foodResponse->json();
        $currentQty = (int) ($food['qty'] ?? 0);
        $orderQty = (int) $this->order->quantity;

        if ($currentQty < $orderQty) {
            Log::warning('ProcessOrder: Insufficient food stock', ['order_id' => $this->order->id]);
            $this->order->update(['status' => 'failed_insufficient_stock']);
            return;
        }

        // Deduct quantity in FoodService using the stock endpoint so stock cannot go below zero.
        $updateFoodResponse = Http::timeout(30)->patch(
            rtrim(config('services.food_service.url'), '/') . '/api/foods/' . $this->order->food_id . '/stock',
            [
                'operation' => 'decrease',
                'quantity' => $orderQty,
            ]
        );

        if ($updateFoodResponse->status() === 409) {
            Log::warning('ProcessOrder: Insufficient food stock during deduction', ['order_id' => $this->order->id]);
            $this->order->update(['status' => 'failed_insufficient_stock']);
            return;
        }

        if (! $updateFoodResponse->successful()) {
            Log::error('ProcessOrder: Failed to update food quantity in FoodService', ['order_id' => $this->order->id]);
            $this->order->update(['status' => 'failed_update_stock']);
            return;
        }

        // Finalize order
        $this->order->update(['status' => 'created']);
        Log::info('ProcessOrder: Order processed successfully', ['order_id' => $this->order->id]);
    }
}
