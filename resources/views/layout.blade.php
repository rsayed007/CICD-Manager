<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CicdBot Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800 hover:text-gray-700">CicdBot 🤖</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="text-center py-6 text-gray-500 text-sm">
        &copy; {{ date('Y') }} CicdBot Manager
    </footer>
</body>
</html>
