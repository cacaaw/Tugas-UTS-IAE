<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $usersPayload = $this->fetchServiceData('user_service', '/api/users');
        $foodsPayload = $this->fetchServiceData('food_service', '/api/foods');

        $ordersQuery = Order::query()->latest('id');
        $statusFilter = (string) $request->query('status', '');
        $searchFilter = trim((string) $request->query('q', ''));
        $userIdFilter = trim((string) $request->query('user_id', ''));

        if ($statusFilter !== '') {
            $ordersQuery->where('status', $statusFilter);
        }

        if ($userIdFilter !== '' && ctype_digit($userIdFilter)) {
            $ordersQuery->where('user_id', (int) $userIdFilter);
        }

        if ($searchFilter !== '') {
            $ordersQuery->where(function ($query) use ($searchFilter): void {
                $query->where('user_name', 'like', '%'.$searchFilter.'%')
                    ->orWhere('food_name', 'like', '%'.$searchFilter.'%')
                    ->orWhere('status', 'like', '%'.$searchFilter.'%');
            });
        }

        $metricsOrders = (clone $ordersQuery)->get();
        $orders = (clone $ordersQuery)->paginate(8)->withQueryString();

        $totalRevenue = (float) $metricsOrders->sum('total_price');
        $totalOrders = $metricsOrders->count();
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0.0;
        $uniqueUsers = $metricsOrders->pluck('user_id')->unique()->count();
        $statusSummary = $metricsOrders
            ->groupBy('status')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $peakStatusCount = max(1, (int) ($statusSummary->first() ?? 0));

        $filters = [
            'status' => $statusFilter,
            'q' => $searchFilter,
            'user_id' => $userIdFilter,
        ];

        return view('dashboard', [
            'users' => $usersPayload['data'],
            'foods' => $foodsPayload['data'],
            'orders' => $orders,
            'serviceHealth' => [
                'user_service' => $usersPayload,
                'food_service' => $foodsPayload,
            ],
            'metrics' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_order' => $averageOrder,
                'unique_users' => $uniqueUsers,
                'status_summary' => $statusSummary,
                'peak_status_count' => $peakStatusCount,
            ],
            'filters' => $filters,
        ]);
    }

    public function exportOrdersCsv(Request $request): StreamedResponse
    {
        $query = Order::query()->latest('id');

        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));
        $userId = trim((string) $request->query('user_id', ''));

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($userId !== '' && ctype_digit($userId)) {
            $query->where('user_id', (int) $userId);
        }

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search): void {
                $innerQuery->where('user_name', 'like', '%'.$search.'%')
                    ->orWhere('food_name', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
            });
        }

        $rows = $query->get([
            'id',
            'user_id',
            'user_name',
            'food_id',
            'food_name',
            'unit_price',
            'quantity',
            'total_price',
            'status',
            'created_at',
        ]);

        return response()->streamDownload(static function () use ($rows): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'id',
                'user_id',
                'user_name',
                'food_id',
                'food_name',
                'unit_price',
                'quantity',
                'total_price',
                'status',
                'created_at',
            ]);

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->id,
                    $row->user_id,
                    $row->user_name,
                    $row->food_id,
                    $row->food_name,
                    $row->unit_price,
                    $row->quantity,
                    $row->total_price,
                    $row->status,
                    optional($row->created_at)->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, 'orders-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function createUser(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $response = Http::timeout(5)->post($this->serviceUrl('user_service').'/api/users', $payload);

        return $this->redirectByResponse($response, 'User berhasil dibuat.', 'Gagal membuat user.');
    }

    public function updateUser(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $payload = array_filter($validated, static fn ($value) => $value !== null && $value !== '');

        if ($payload === []) {
            return back()->with('error', 'Tidak ada data user yang diubah.');
        }

        $response = Http::timeout(5)->patch($this->serviceUrl('user_service').'/api/users/'.$id, $payload);

        return $this->redirectByResponse($response, 'User berhasil diperbarui.', 'Gagal memperbarui user.');
    }

    public function deleteUser(int $id): RedirectResponse
    {
        $response = Http::timeout(5)->delete($this->serviceUrl('user_service').'/api/users/'.$id);

        return $this->redirectByResponse($response, 'User berhasil dihapus.', 'Gagal menghapus user.');
    }

    public function createFood(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $response = Http::timeout(5)->post($this->serviceUrl('food_service').'/api/foods', $payload);

        return $this->redirectByResponse($response, 'Food berhasil dibuat.', 'Gagal membuat food.');
    }

    public function updateFood(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $payload = array_filter($validated, static fn ($value) => $value !== null && $value !== '');

        if ($payload === []) {
            return back()->with('error', 'Tidak ada data food yang diubah.');
        }

        $response = Http::timeout(5)->patch($this->serviceUrl('food_service').'/api/foods/'.$id, $payload);

        return $this->redirectByResponse($response, 'Food berhasil diperbarui.', 'Gagal memperbarui food.');
    }

    public function deleteFood(int $id): RedirectResponse
    {
        $response = Http::timeout(5)->delete($this->serviceUrl('food_service').'/api/foods/'.$id);

        return $this->redirectByResponse($response, 'Food berhasil dihapus.', 'Gagal menghapus food.');
    }

    public function createOrder(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
            'food_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $response = Http::timeout(5)->post($this->orderApiUrl('/api/orders'), $payload);

        return $this->redirectByResponse($response, 'Order berhasil dibuat.', 'Gagal membuat order.');
    }

    public function updateOrder(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'min:1'],
            'food_id' => ['nullable', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $payload = array_filter($validated, static fn ($value) => $value !== null && $value !== '');

        if ($payload === []) {
            return back()->with('error', 'Tidak ada data order yang diubah.');
        }

        $response = Http::timeout(5)->patch($this->orderApiUrl('/api/orders/'.$id), $payload);

        return $this->redirectByResponse($response, 'Order berhasil diperbarui.', 'Gagal memperbarui order.');
    }

    public function deleteOrder(int $id): RedirectResponse
    {
        $response = Http::timeout(5)->delete($this->orderApiUrl('/api/orders/'.$id));

        return $this->redirectByResponse($response, 'Order berhasil dihapus.', 'Gagal menghapus order.');
    }

    private function serviceUrl(string $service): string
    {
        return rtrim((string) config('services.'.$service.'.url'), '/');
    }

    private function fetchServiceData(string $service, string $path): array
    {
        $start = microtime(true);
        $response = Http::timeout(5)->get($this->serviceUrl($service).$path);
        $latency = (int) round((microtime(true) - $start) * 1000);

        return [
            'ok' => $response->successful(),
            'latency_ms' => $latency,
            'data' => $response->successful() ? $response->json() : [],
        ];
    }

    private function orderApiUrl(string $path): string
    {
        return rtrim((string) config('app.url'), '/').$path;
    }

    private function redirectByResponse($response, string $successMessage, string $errorMessage): RedirectResponse
    {
        if ($response->successful()) {
            return redirect('/')->with('status', $successMessage);
        }

        $message = $response->json('message') ?: $errorMessage;

        return redirect('/')->with('error', $message);
    }
}
