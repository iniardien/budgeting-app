@php
    $items = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard', 'short' => 'DB'],
        'categories' => ['label' => 'Categories', 'route' => 'categories.index', 'short' => 'CT'],
        'transactions' => ['label' => 'Transactions', 'route' => 'transactions.index', 'short' => 'TR'],
        'budgets' => ['label' => 'Budgets', 'route' => 'budgets.index', 'short' => 'BG'],
        'reports' => ['label' => 'Reports', 'route' => 'reports', 'short' => 'RP'],
        'settings' => ['label' => 'Settings', 'route' => 'settings', 'short' => 'ST'],
    ];
@endphp

<aside class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-20 items-center gap-3 border-b border-slate-200 px-6">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-sm">B</div>
        <div>
            <p class="text-lg font-semibold text-slate-900">Budget</p>
            <p class="text-sm text-slate-500">Manage your finances</p>
        </div>
    </div>

    <nav class="flex-1 space-y-2 px-4 py-6">
        @foreach ($items as $key => $item)
            <a href="{{ route($item['route']) }}" @class([
                'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition',
                'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100' => $active === $key,
                'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => $active !== $key,
            ])>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-xs font-semibold text-slate-500">
                    {{ $item['short'] }}
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="space-y-4 border-t border-slate-200 px-6 py-5">
        <div class="space-y-1 text-sm text-slate-500">
            <p class="font-medium text-slate-700">{{ auth()->user()->name }}</p>
            <p>{{ auth()->user()->email }}</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="budget-button budget-button-danger w-full">Logout</button>
        </form>
    </div>
</aside>
