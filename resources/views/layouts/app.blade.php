<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Budget') | Budget</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
        @include('partials.sidebar', ['active' => trim($__env->yieldContent('active_nav')) ?: 'dashboard'])

        <div class="flex min-h-screen flex-col">
            @include('partials.mobile-header')

            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
