<header class="sticky top-0 z-10 border-b border-slate-200 bg-white/90 backdrop-blur lg:hidden">
    <div class="flex h-16 items-center justify-between px-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-sm">B</div>
            <div>
                <p class="text-sm font-semibold text-slate-900">Budget</p>
                <p class="text-xs text-slate-500">{{ auth()->user()->name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600">Logout</button>
        </form>
    </div>
</header>
