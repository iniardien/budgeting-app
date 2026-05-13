<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgeting App</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen">

    <!-- Navbar -->
    <nav class="flex items-center justify-between px-8 py-5 border-b border-white/10">
        <h1 class="text-2xl font-bold">BudgetingApp</h1>

        <button class="bg-white text-black px-5 py-2 rounded-2xl">
            Get Started
        </button>
    </nav>

    <!-- Hero -->
    <section class="px-8 py-24 max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

        <div>
            <p class="uppercase tracking-[0.3em] text-sm text-gray-400 mb-4">
                Smart Personal Finance
            </p>

            <h2 class="text-5xl font-bold leading-tight mb-6">
                Manage your money with clarity.
            </h2>

            <p class="text-gray-400 text-lg mb-8">
                Track expenses, monitor income, and build healthier financial habits.
            </p>

            <div class="flex gap-4">
                <button class="bg-white text-black px-6 py-3 rounded-2xl">
                    Start Free
                </button>

                <button class="border border-white/20 px-6 py-3 rounded-2xl">
                    Learn More
                </button>
            </div>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-[2rem] p-6">

            <div class="flex justify-between items-center mb-8">
                <div>
                    <p class="text-gray-400 text-sm">Total Balance</p>
                    <h3 class="text-4xl font-bold mt-2">Rp12.450.000</h3>
                </div>

                <div class="bg-green-500/20 text-green-400 px-4 py-2 rounded-xl text-sm">
                    +12%
                </div>
            </div>

            <div class="space-y-4">

                <div class="bg-black/40 p-4 rounded-2xl flex justify-between">
                    <div>
                        <p class="font-medium">Food & Drinks</p>
                        <p class="text-gray-400 text-sm">Monthly Expense</p>
                    </div>

                    <p class="text-red-400">-Rp850.000</p>
                </div>

                <div class="bg-black/40 p-4 rounded-2xl flex justify-between">
                    <div>
                        <p class="font-medium">Freelance Income</p>
                        <p class="text-gray-400 text-sm">This Week</p>
                    </div>

                    <p class="text-green-400">+Rp2.500.000</p>
                </div>

            </div>
        </div>

    </section>

</body>
</html>