<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opsboard | Service Integration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f3f6f8;
            --panel: #ffffff;
            --ink: #142028;
            --muted: #5f7280;
            --line: #d9e2e8;
            --header-bg: rgba(255, 255, 255, 0.85);
            --sidebar-bg: #f8fbfd;
            --table-head: #f8fbfd;
            --table-row: #edf2f5;
            --brand: #0f7c82;
            --brand-dark: #0b5f63;
            --accent: #f59e0b;
            --danger: #c7362f;
            --ok: #147d46;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 28px rgba(20, 32, 40, 0.08);
            --radius: 14px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--ink);
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 16% -6%, rgba(15, 124, 130, 0.14) 0%, transparent 38%),
                radial-gradient(circle at 87% -8%, rgba(245, 158, 11, 0.16) 0%, transparent 34%),
                var(--bg);
        }

            body[data-theme="dark"] {
                --bg: #10171d;
                --panel: #18232b;
                --ink: #e6edf2;
                --muted: #a6bbc8;
                --line: #29404f;
                --header-bg: rgba(24, 35, 43, 0.9);
                --sidebar-bg: #121c23;
                --table-head: #1d2b34;
                --table-row: #243743;
                --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
                --shadow-md: 0 14px 28px rgba(0, 0, 0, 0.32);
            }

        .app {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 262px 1fr;
        }

        .sidebar {
            border-right: 1px solid var(--line);
            background: var(--sidebar-bg);
            padding: 22px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px 16px;
            border-bottom: 1px dashed var(--line);
            margin-bottom: 14px;
        }

        .logo {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .logo::before,
        .logo::after {
            content: "";
            position: absolute;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 999px;
        }

        .logo::before {
            width: 18px;
            height: 4px;
            left: 8px;
            top: 10px;
        }

        .logo::after {
            width: 12px;
            height: 4px;
            left: 8px;
            top: 19px;
        }

        .brand h1 {
            margin: 0;
            font-size: 1rem;
            font-family: "Outfit", sans-serif;
            font-weight: 700;
        }

        .brand p {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 0.76rem;
        }

        .menu {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }

        .menu button {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 11px;
            padding: 10px 11px;
            text-align: left;
            font: inherit;
            cursor: pointer;
            font-weight: 600;
            color: var(--ink);
        }

        .menu button.active {
            border-color: rgba(15, 124, 130, 0.35);
            background: linear-gradient(135deg, rgba(15, 124, 130, 0.12), rgba(15, 124, 130, 0.05));
            color: var(--brand);
        }

        .sidebar-note {
            margin-top: auto;
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 12px;
            padding: 10px;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .main {
            padding: 20px;
            display: grid;
            gap: 14px;
        }

        .topbar {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--header-bg);
            backdrop-filter: blur(6px);
            box-shadow: var(--shadow-sm);
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .topbar h2 {
            margin: 0;
            font-family: "Outfit", sans-serif;
            font-weight: 700;
            font-size: clamp(1.2rem, 2vw, 1.5rem);
        }

        .topbar p {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 9px 12px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff;
        }

        .btn-ghost {
            border-color: var(--line);
            color: var(--ink);
            background: var(--panel);
        }

        .btn-warn {
            border-color: rgba(245, 158, 11, 0.45);
            color: #9a6604;
            background: rgba(245, 158, 11, 0.1);
        }

        body[data-theme="dark"] .btn-warn {
            color: #f6cb7a;
            border-color: rgba(245, 158, 11, 0.5);
            background: rgba(245, 158, 11, 0.16);
        }

        .btn-danger {
            border-color: rgba(199, 54, 47, 0.4);
            color: var(--danger);
            background: rgba(199, 54, 47, 0.1);
        }

        .toast-stack {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1200;
            display: grid;
            gap: 8px;
            width: min(360px, 90vw);
        }

        .toast {
            border-radius: 12px;
            padding: 11px 13px;
            border: 1px solid var(--line);
            background: var(--panel);
            box-shadow: var(--shadow-md);
            animation: fade .2s ease;
        }

        .toast.success {
            color: var(--ok);
            border-color: rgba(20, 125, 70, 0.3);
            background: rgba(20, 125, 70, 0.12);
        }

        .toast.error {
            color: var(--danger);
            border-color: rgba(199, 54, 47, 0.3);
            background: rgba(199, 54, 47, 0.12);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--panel);
            box-shadow: var(--shadow-sm);
            padding: 14px;
        }

        .kpi small {
            color: var(--muted);
            display: block;
            margin-bottom: 6px;
            font-size: 0.83rem;
            font-weight: 600;
        }

        .kpi strong {
            font-family: "Outfit", sans-serif;
            font-size: 1.45rem;
            letter-spacing: -0.02em;
        }

        .health-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pill {
            border-radius: 999px;
            border: 1px solid var(--line);
            padding: 6px 10px;
            font-size: 0.8rem;
            font-weight: 600;
            background: var(--panel);
        }

        .pill.ok {
            color: var(--ok);
            border-color: rgba(20, 125, 70, 0.35);
            background: rgba(20, 125, 70, 0.08);
        }

        .pill.bad {
            color: var(--danger);
            border-color: rgba(199, 54, 47, 0.35);
            background: rgba(199, 54, 47, 0.08);
        }

        .split {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 10px;
        }

        .bars {
            display: grid;
            gap: 8px;
            margin-top: 8px;
        }

        .bar-row {
            display: grid;
            grid-template-columns: 92px 1fr 34px;
            gap: 7px;
            align-items: center;
            font-size: 0.83rem;
        }

        progress {
            width: 100%;
            height: 10px;
            border: none;
            border-radius: 999px;
            overflow: hidden;
            background: var(--table-row);
        }

        progress::-webkit-progress-bar {
            background: var(--table-row);
            border-radius: 999px;
        }

        progress::-webkit-progress-value {
            border-radius: 999px;
            background: linear-gradient(120deg, var(--brand), #1aa1a8);
        }

        progress::-moz-progress-bar {
            border-radius: 999px;
            background: linear-gradient(120deg, var(--brand), #1aa1a8);
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr auto auto;
            gap: 8px;
            align-items: end;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px;
            font: inherit;
            background: var(--panel);
            color: var(--ink);
        }

        textarea {
            resize: vertical;
            min-height: 44px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid rgba(15, 124, 130, 0.2);
            border-color: rgba(15, 124, 130, 0.45);
        }

        .create-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .create-box h3,
        .section-title {
            margin: 0 0 8px;
            font-family: "Outfit", sans-serif;
            font-size: 1.02rem;
            font-weight: 700;
        }

        .create-box form {
            display: grid;
            gap: 7px;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: auto;
            background: var(--panel);
        }

        table {
            border-collapse: collapse;
            min-width: 1020px;
            width: 100%;
        }

        th,
        td {
            text-align: left;
            border-bottom: 1px solid var(--table-row);
            padding: 9px;
            vertical-align: top;
            font-size: 0.86rem;
        }

        th {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            background: var(--table-head);
        }

        th.sticky-head {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(5, 12, 18, 0.58);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 999;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-card {
            width: min(540px, 100%);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--shadow-md);
            padding: 16px;
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .modal-head h4 {
            margin: 0;
            font-family: "Outfit", sans-serif;
            font-size: 1.15rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .detail-item {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px;
            background: rgba(127, 127, 127, 0.04);
        }

        .detail-item small {
            display: block;
            color: var(--muted);
            margin-bottom: 2px;
            font-weight: 600;
        }

        .edit-form {
            min-width: 190px;
            display: grid;
            gap: 6px;
        }

        .tab-panels {
            display: grid;
            gap: 10px;
        }

        .pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .pagination-links {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .page-link {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 0.82rem;
            color: var(--ink);
            text-decoration: none;
            background: var(--panel);
        }

        .page-link.active {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }

        .page-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(8, 15, 22, 0.45);
            z-index: 1300;
            padding: 16px;
        }

        .loading-overlay.active {
            display: flex;
        }

        .skeleton-panel {
            width: min(820px, 96vw);
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 14px;
        }

        .skeleton-row,
        .skeleton-pill {
            border-radius: 8px;
            background: linear-gradient(
                90deg,
                rgba(120, 140, 150, 0.16) 0%,
                rgba(120, 140, 150, 0.28) 50%,
                rgba(120, 140, 150, 0.16) 100%
            );
            background-size: 280% 100%;
            animation: shimmer 1.2s infinite linear;
        }

        .skeleton-pill {
            height: 30px;
            width: 180px;
            margin-bottom: 10px;
        }

        .skeleton-row {
            height: 14px;
            margin: 8px 0;
        }

        .skeleton-row.short {
            width: 55%;
        }

        @keyframes shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }

        .panel-view {
            display: none;
        }

        .panel-view.active {
            display: block;
            animation: fade .2s ease;
        }

        @keyframes fade {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .text-muted {
            color: var(--muted);
            margin: 0;
            font-size: 0.85rem;
        }

        .error-list {
            margin: 6px 0 0 18px;
        }

        @media (max-width: 1260px) {
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .split {
                grid-template-columns: 1fr;
            }

            .toolbar {
                grid-template-columns: 1fr 1fr;
            }

            .create-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 980px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }

            .menu {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .main {
                padding: 12px;
            }

            .menu {
                grid-template-columns: 1fr;
            }

            .toolbar {
                grid-template-columns: 1fr;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <span class="logo"></span>
            <div>
                <h1>Opsboard</h1>
                <p>Telkom University Integration Suite</p>
            </div>
        </div>

        <div class="menu" id="menuTabs">
            <button class="active" data-target="usersView">Users</button>
            <button data-target="foodsView">Foods</button>
            <button data-target="ordersView">Orders</button>
        </div>

        <div class="sidebar-note">
            Dashboard ini ada di OrderService dan bertindak sebagai command center untuk operasi lintas layanan UserService, FoodService, dan OrderService.
        </div>
    </aside>

    <main class="main">
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

        <section class="create-grid">
            <article class="card create-box">
                <h3>Create User</h3>
                <form method="POST" action="{{ route('ui.users.store') }}">
                    @csrf
                    <input type="text" name="name" placeholder="Nama user" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password min 6" required>
                    <button class="btn btn-primary" type="submit">Create User</button>
                </form>
            </article>

            <article class="card create-box">
                <h3>Create Food</h3>
                <form method="POST" action="{{ route('ui.foods.store') }}">
                    @csrf
                    <input type="text" name="name" placeholder="Nama menu" required>
                    <input type="number" name="price" step="0.01" min="0" placeholder="Harga" required>
                    <textarea name="description" placeholder="Deskripsi"></textarea>
                    <input type="text" name="image" placeholder="URL Gambar">
                    <input type="number" name="qty" min="0" placeholder="Qty">
                    <button class="btn btn-primary" type="submit">Create Food</button>
                </form>
            </article>

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

        <section class="tab-panels">
            <article class="card panel-view active" id="usersView">
                <h3 class="section-title">Users Workspace</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th class="sticky-head">ID</th>
                            <th class="sticky-head">Name</th>
                            <th class="sticky-head">Email</th>
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
                                <td>
                                    <form class="edit-form" method="POST" action="{{ route('ui.users.update', $user['id']) }}">
                                        @csrf
                                        <input type="text" name="name" placeholder="Nama baru">
                                        <input type="email" name="email" placeholder="Email baru">
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
                            <tr><td colspan="5" class="text-muted">Tidak ada user.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="card panel-view" id="foodsView">
                <h3 class="section-title">Foods Workspace</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th class="sticky-head">ID</th>
                            <th class="sticky-head">Image</th>
                            <th class="sticky-head">Name</th>
                            <th class="sticky-head">Price</th>
                            <th class="sticky-head">Qty</th>
                            <th class="sticky-head">Description</th>
                            <th class="sticky-head">Edit</th>
                            <th class="sticky-head">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($foods as $food)
                            <tr>
                                <td>{{ $food['id'] }}</td>
                                <td>
                                    @if(!empty($food['image']))
                                        <img src="{{ $food['image'] }}" alt="{{ $food['name'] }}" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $food['name'] }}</td>
                                <td>Rp {{ number_format((float) $food['price'], 0, ',', '.') }}</td>
                                <td>{{ $food['qty'] ?? 0 }}</td>
                                <td>{{ $food['description'] ?? '-' }}</td>
                                <td>
                                    <form class="edit-form" method="POST" action="{{ route('ui.foods.update', $food['id']) }}">
                                        @csrf
                                        <input type="text" name="name" placeholder="Nama baru">
                                        <input type="number" name="price" step="0.01" min="0" placeholder="Harga baru">
                                        <textarea name="description" placeholder="Deskripsi baru"></textarea>
                                        <input type="text" name="image" placeholder="URL Gambar baru">
                                        <input type="number" name="qty" min="0" placeholder="Qty baru">
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
                            <th class="sticky-head">Status</th>
                            <th class="sticky-head">Total</th>
                            <th class="sticky-head">Detail</th>
                            <th class="sticky-head">Edit</th>
                            <th class="sticky-head">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>#{{ $order->user_id }} - {{ $order->user_name }}</td>
                                <td>#{{ $order->food_id }} - {{ $order->food_name }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td>{{ $order->status }}</td>
                                <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-ghost order-detail-btn"
                                        data-order='{{ json_encode([
                                            "id" => $order->id,
                                            "user" => "#".$order->user_id." - ".$order->user_name,
                                            "food" => "#".$order->food_id." - ".$order->food_name,
                                            "quantity" => $order->quantity,
                                            "status" => $order->status,
                                            "unit_price" => (float) $order->unit_price,
                                            "total_price" => (float) $order->total_price,
                                            "created_at" => optional($order->created_at)->format('Y-m-d H:i:s'),
                                        ]) }}'
                                    >Lihat</button>
                                </td>
                                <td>
                                    <form class="edit-form" method="POST" action="{{ route('ui.orders.update', $order->id) }}">
                                        @csrf
                                        <input type="number" name="user_id" min="1" placeholder="User ID baru">
                                        <input type="number" name="food_id" min="1" placeholder="Food ID baru">
                                        <input type="number" name="quantity" min="1" placeholder="Qty baru">
                                        <select name="status">
                                            <option value="">Pilih status</option>
                                            @foreach(['created', 'paid', 'cancelled', 'completed'] as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-ghost" type="submit">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('ui.orders.delete', $order->id) }}" onsubmit="return confirm('Hapus order ini?')">
                                        @csrf
                                        <button class="btn btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-muted">Belum ada order pada filter aktif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    <p class="text-muted">
                        Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} order
                    </p>
                    <div class="pagination-links">
                        <a class="page-link {{ $orders->onFirstPage() ? 'disabled' : '' }}" href="{{ $orders->previousPageUrl() ?: '#' }}">Sebelumnya</a>
                        @for($page = 1; $page <= $orders->lastPage(); $page++)
                            <a class="page-link {{ $orders->currentPage() === $page ? 'active' : '' }}" href="{{ $orders->url($page) }}">{{ $page }}</a>
                        @endfor
                        <a class="page-link {{ $orders->hasMorePages() ? '' : 'disabled' }}" href="{{ $orders->nextPageUrl() ?: '#' }}">Berikutnya</a>
                    </div>
                </div>
            </article>
        </section>
    </main>
</div>

<div class="loading-overlay" id="loadingOverlay" aria-hidden="true">
    <div class="skeleton-panel">
        <div class="skeleton-pill"></div>
        <div class="skeleton-row"></div>
        <div class="skeleton-row short"></div>
        <div class="skeleton-row"></div>
        <div class="skeleton-row"></div>
        <div class="skeleton-row short"></div>
    </div>
</div>

<div class="modal-backdrop" id="orderModal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h4>Detail Order</h4>
            <button type="button" class="btn btn-ghost" id="closeOrderModal">Tutup</button>
        </div>
        <div class="detail-grid" id="orderDetailGrid">
            <div class="detail-item"><small>ID</small><strong id="detail-id">-</strong></div>
            <div class="detail-item"><small>Status</small><strong id="detail-status">-</strong></div>
            <div class="detail-item"><small>User</small><strong id="detail-user">-</strong></div>
            <div class="detail-item"><small>Food</small><strong id="detail-food">-</strong></div>
            <div class="detail-item"><small>Quantity</small><strong id="detail-qty">-</strong></div>
            <div class="detail-item"><small>Unit Price</small><strong id="detail-unit">-</strong></div>
            <div class="detail-item"><small>Total Price</small><strong id="detail-total">-</strong></div>
            <div class="detail-item"><small>Created At</small><strong id="detail-created">-</strong></div>
        </div>
    </div>
</div>

<script>
    (function () {
        const menuButtons = Array.from(document.querySelectorAll('#menuTabs button'));
        const views = Array.from(document.querySelectorAll('.panel-view'));
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('opsboard-theme');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const orderModal = document.getElementById('orderModal');
        const closeOrderModal = document.getElementById('closeOrderModal');
        const detailButtons = Array.from(document.querySelectorAll('.order-detail-btn'));
        const forms = Array.from(document.querySelectorAll('form'));
        const loadingLinks = Array.from(document.querySelectorAll('[data-loading-trigger="true"]'));
        const toastStack = document.getElementById('toastStack');

        const showLoading = () => {
            if (loadingOverlay) {
                loadingOverlay.classList.add('active');
                loadingOverlay.setAttribute('aria-hidden', 'false');
            }
        };

        const setTheme = (theme) => {
            document.body.setAttribute('data-theme', theme);
            localStorage.setItem('opsboard-theme', theme);
            if (themeToggle) {
                themeToggle.textContent = theme === 'dark' ? 'Mode Terang' : 'Mode Gelap';
            }
        };

        setTheme(savedTheme === 'dark' ? 'dark' : 'light');

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const current = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                setTheme(current === 'dark' ? 'light' : 'dark');
            });
        }

        forms.forEach((form) => {
            if (form.dataset.noLoading === 'true') {
                return;
            }

            form.addEventListener('submit', () => {
                showLoading();
            });
        });

        loadingLinks.forEach((link) => {
            link.addEventListener('click', () => {
                showLoading();
            });
        });

        if (toastStack) {
            setTimeout(() => {
                toastStack.style.opacity = '0';
                toastStack.style.transition = 'opacity .35s ease';
            }, 2600);

            setTimeout(() => {
                if (toastStack.parentNode) {
                    toastStack.parentNode.removeChild(toastStack);
                }
            }, 3100);
        }

        menuButtons.forEach((button) => {
            button.addEventListener('click', () => {
                menuButtons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');

                const targetId = button.getAttribute('data-target');
                views.forEach((view) => {
                    view.classList.toggle('active', view.id === targetId);
                });
            });
        });

        const params = new URLSearchParams(window.location.search);
        if (params.has('page') || params.has('status') || params.has('q') || params.has('user_id')) {
            const ordersTab = menuButtons.find((button) => button.getAttribute('data-target') === 'ordersView');
            if (ordersTab) {
                ordersTab.click();
            }
        }

        const formatCurrency = (value) => {
            const number = Number(value || 0);
            return 'Rp ' + number.toLocaleString('id-ID');
        };

        const openModal = (order) => {
            document.getElementById('detail-id').textContent = order.id ?? '-';
            document.getElementById('detail-status').textContent = order.status ?? '-';
            document.getElementById('detail-user').textContent = order.user ?? '-';
            document.getElementById('detail-food').textContent = order.food ?? '-';
            document.getElementById('detail-qty').textContent = order.quantity ?? '-';
            document.getElementById('detail-unit').textContent = formatCurrency(order.unit_price);
            document.getElementById('detail-total').textContent = formatCurrency(order.total_price);
            document.getElementById('detail-created').textContent = order.created_at ?? '-';
            orderModal.classList.add('open');
            orderModal.setAttribute('aria-hidden', 'false');
        };

        const closeModal = () => {
            orderModal.classList.remove('open');
            orderModal.setAttribute('aria-hidden', 'true');
        };

        detailButtons.forEach((button) => {
            button.addEventListener('click', () => {
                try {
                    const payload = JSON.parse(button.getAttribute('data-order') || '{}');
                    openModal(payload);
                } catch (error) {
                    console.error('Failed parsing order detail payload', error);
                }
            });
        });

        if (closeOrderModal) {
            closeOrderModal.addEventListener('click', closeModal);
        }

        if (orderModal) {
            orderModal.addEventListener('click', (event) => {
                if (event.target === orderModal) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && orderModal.classList.contains('open')) {
                closeModal();
            }
        });
    })();
</script>
</body>
</html>
