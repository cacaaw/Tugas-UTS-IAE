<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Food::all());
    }

    public function show(int $id): JsonResponse
    {
        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        return response()->json($food);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        $food = Food::create($validated);

        return response()->json($food, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'image' => ['sometimes', 'nullable', 'string'],
            'qty' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $food->fill($validated);
        $food->save();

        return response()->json($food);
    }

    public function destroy(int $id): JsonResponse
    {
        $food = Food::find($id);

        if (! $food) {
            return response()->json(['message' => 'Food not found'], 404);
        }

        $food->delete();

        return response()->json(['message' => 'Food deleted']);
    }
}
