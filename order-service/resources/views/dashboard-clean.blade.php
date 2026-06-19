<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opsboard | Service Integration</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <span class="logo"></span>
                <div>
                    <h1>Opsboard</h1>
                    <p>Telkom University Integration Suite</p>
                </div>
            </div>

            <nav class="menu" id="menuTabs">
                <button class="active" data-target="usersView">Users</button>
                <button data-target="foodsView">Foods</button>
                <button data-target="ordersView">Orders</button>
            </nav>

            <div class="sidebar-note">
                Dashboard ini ada di OrderService dan bertindak sebagai command center untuk operasi lintas layanan UserService, FoodService, dan OrderService.
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Top Bar -->
            <section class="topbar">
                <div>
                    <h2>Service-to-Service Command Dashboard</h2>
                    <p>Interface production-style untuk monitoring, CRUD, filter data, dan export order report.</p>
                </div>
                <div class="top-actions">
                    <button class="btn btn-ghost" id="themeToggle" type="button">Mode Gelap</button>
                    <a class="btn btn-warn" data-no-loading="true" href="{{ route('ui.orders.export', ['q' => $filters['q'], 'status' => $filters['status'], 'user_id' => $filters['user_id']]) }}">Export CSV</a>
                    <a class="btn btn-ghost" data-loading-trigger="true" href="{{ route('dashboard') }}">Refresh</a>
                </div>
            </section>

            <!-- Toast Notifications -->
            @if(session('status') || session('error') || $errors->any())
                <div class="toast-stack" id="toastStack">
                    @if(session('status'))
                        <div class="toast success">{{ session('status') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="toast error">{{ session('error') }}</div>
                    @endif

                    @foreach($errors->all() as $error)
                        <div class="toast error">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- KPI Metrics -->
            <section class="kpi-grid">
                <article class="card kpi">
                    <small>Total Orders</small>
                    <strong>{{ number_format($metrics['total_orders']) }}</strong>
                </article>
                <article class="card kpi">
                    <small>Total Revenue</small>
                    <strong>Rp {{ number_format((float) $metrics['total_revenue'], 0, ',', '.') }}</strong>
                </article>
                <article class="card kpi">
                    <small>Average Order Value</small>
                    <strong>Rp {{ number_format((float) $metrics['average_order'], 0, ',', '.') }}</strong>
                </article>
                <article class="card kpi">
                    <small>Unique Buyers</small>
                    <strong>{{ number_format($metrics['unique_users']) }}</strong>
                </article>
            </section>

            <!-- Service Health & Status Distribution -->
            <section class="split">
                <article class="card">
                    <h3 class="section-title">Service Health</h3>
                    <div class="health-row">
                        <span class="pill {{ $serviceHealth['user_service']['ok'] ? 'ok' : 'bad' }}">
                            UserService: {{ $serviceHealth['user_service']['ok'] ? 'Online' : 'Offline' }} ({{ $serviceHealth['user_service']['latency_ms'] }} ms)
                        </span>
                        <span class="pill {{ $serviceHealth['food_service']['ok'] ? 'ok' : 'bad' }}">
                            FoodService: {{ $serviceHealth['food_service']['ok'] ? 'Online' : 'Offline' }} ({{ $serviceHealth['food_service']['latency_ms'] }} ms)
                        </span>
                        <span class="pill ok">OrderService: Online</span>
                    </div>
                </article>

                <article class="card">
                    <h3 class="section-title">Status Distribution</h3>
                    @if($metrics['status_summary']->count() > 0)
                        <div class="bars">
                            @foreach($metrics['status_summary'] as $status => $count)
                                <div class="bar-row">
                                    <span>{{ $status }}</span>
                                    <progress max="{{ $metrics['peak_status_count'] }}" value="{{ $count }}"></progress>
                                    <strong>{{ $count }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Belum ada data order pada filter aktif.</p>
                    @endif
                </article>
            </section>

            <!-- Filter Section -->
            <section class="card">
                <h3 class="section-title">Filter Orders</h3>
                <form method="GET" action="{{ route('dashboard') }}" class="toolbar">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari user, makanan, status...">
                    <select name="status">
                        <option value="">Semua status</option>
                        @foreach(['created', 'paid', 'cancelled', 'completed'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="user_id" min="1" value="{{ $filters['user_id'] }}" placeholder="Filter User ID">
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <a class="btn btn-ghost" href="{{ route('dashboard') }}">Reset</a>
                </form>
            </section>

            <!-- Create Forms -->
            <section class="create-grid">
                <!-- Create User -->
                <article class="card create-box">
                    <h3>Create User</h3>
                    <form method="POST" action="{{ route('ui.users.store') }}">
                        @csrf
                        <input type="text" name="name" placeholder="Nama user" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="address" placeholder="Alamat">
                        <input type="password" name="password" placeholder="Password min 6" required>
                        <button class="btn btn-primary" type="submit">Create User</button>
                    </form>
                </article>

                <!-- Create Food -->
                <article class="card create-box">
                    <h3>Create Food</h3>
                    <form method="POST" action="{{ route('ui.foods.store') }}">
                        @csrf
                        <input type="text" name="name" placeholder="Nama menu" required>
                        <input type="number" name="price" step="0.01" min="0" placeholder="Harga" required>
                        <textarea name="description" placeholder="Deskripsi"></textarea>
                        <button class="btn btn-primary" type="submit">Create Food</button>
                    </form>
                </article>

                <!-- Create Order -->
                <article class="card create-box">
                    <h3>Create Order</h3>
                    <form method="POST" action="{{ route('ui.orders.store') }}">
                        @csrf
                        <select name="user_id" required>
                            <option value="">Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user['id'] }}">#{{ $user['id'] }} - {{ $user['name'] }}</option>
                            @endforeach
                        </select>
                        <select name="food_id" required>
                            <option value="">Pilih Food</option>
                            @foreach($foods as $food)
                                <option value="{{ $food['id'] }}">#{{ $food['id'] }} - {{ $food['name'] }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="quantity" min="1" placeholder="Quantity" required>
                        <button class="btn btn-primary" type="submit">Create Order</button>
                    </form>
                </article>
            </section>

            <!-- Tab Panels -->
            <section class="tab-panels">
                <!-- Users Panel -->
                <article class="card panel-view active" id="usersView">
                    <h3 class="section-title">Users Workspace</h3>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th class="sticky-head">ID</th>
                                    <th class="sticky-head">Name</th>
                                    <th class="sticky-head">Email</th>
                                    <th class="sticky-head">Address</th>
                                    <th class="sticky-head">Edit</th>
                                    <th class="sticky-head">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user['id'] }}</td>
                                        <td>{{ $user['name'] }}</td>
                                        <td>{{ $user['email'] }}</td>
                                        <td>{{ $user['address'] ?? '-' }}</td>
                                        <td>
                                            <form class="edit-form" method="POST" action="{{ route('ui.users.update', $user['id']) }}">
                                                @csrf
                                                <input type="text" name="name" placeholder="Nama baru">
                                                <input type="email" name="email" placeholder="Email baru">
                                                <input type="text" name="address" placeholder="Alamat baru">
                                                <input type="password" name="password" placeholder="Password baru">
                                                <button class="btn btn-ghost" type="submit">Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('ui.users.delete', $user['id']) }}" onsubmit="return confirm('Hapus user ini?')">
                                                @csrf
                                                <button class="btn btn-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted">Tidak ada user.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <!-- Foods Panel -->
                <article class="card panel-view" id="foodsView">
                    <h3 class="section-title">Foods Workspace</h3>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th class="sticky-head">ID</th>
                                    <th class="sticky-head">Name</th>
                                    <th class="sticky-head">Price</th>
                                    <th class="sticky-head">Description</th>
                                    <th class="sticky-head">Edit</th>
                                    <th class="sticky-head">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($foods as $food)
                                    <tr>
                                        <td>{{ $food['id'] }}</td>
                                        <td>{{ $food['name'] }}</td>
                                        <td>Rp {{ number_format((float) $food['price'], 0, ',', '.') }}</td>
                                        <td>{{ $food['description'] ?? '-' }}</td>
                                        <td>
                                            <form class="edit-form" method="POST" action="{{ route('ui.foods.update', $food['id']) }}">
                                                @csrf
                                                <input type="text" name="name" placeholder="Nama baru">
                                                <input type="number" name="price" step="0.01" min="0" placeholder="Harga baru">
                                                <textarea name="description" placeholder="Deskripsi baru"></textarea>
                                                <button class="btn btn-ghost" type="submit">Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('ui.foods.delete', $food['id']) }}" onsubmit="return confirm('Hapus food ini?')">
                                                @csrf
                                                <button class="btn btn-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted">Tidak ada food.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <!-- Orders Panel -->
                <article class="card panel-view" id="ordersView">
                    <h3 class="section-title">Orders Workspace</h3>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th class="sticky-head">ID</th>
                                    <th class="sticky-head">User</th>
                                    <th class="sticky-head">Food</th>
                                    <th class="sticky-head">Qty</th>
                                    <th class="sticky-head">Total</th>
                                    <th class="sticky-head">Status</th>
                                    <th class="sticky-head">Date</th>
                                    <th class="sticky-head">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>
                                            @if(isset($order->user_name))
                                                #{{ $order->user_id }} - {{ $order->user_name }}
                                            @else
                                                #{{ $order->user_id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($order->food_name))
                                                #{{ $order->food_id }} - {{ $order->food_name }}
                                            @else
                                                #{{ $order->food_id }}
                                            @endif
                                        </td>
                                        <td>{{ $order->quantity }}</td>
                                        <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="pill {{ $order->status === 'completed' ? 'ok' : ($order->status === 'cancelled' ? 'bad' : '') }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('ui.orders.delete', $order->id) }}" onsubmit="return confirm('Hapus order ini?')" style="display: inline;">
                                                @csrf
                                                <button class="btn btn-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-muted">Tidak ada order.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </main>
    </div>

    <!-- External JavaScript -->
    <script src="{{ asset('assets/dashboard.js') }}"></script>
</body>
</html>
