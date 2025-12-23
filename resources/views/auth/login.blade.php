<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - CicdBot Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-4">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">CicdBot 🤖</h1>
                <p class="text-gray-600 mt-2">Sign in to your dashboard</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email Address
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" 
                        id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="admin@example.com">
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" 
                        id="password" type="password" name="password" required autocomplete="current-password" placeholder="******************">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="inline-flex items-center text-sm text-gray-700" for="remember">
                        <input type="checkbox" name="remember" id="remember" class="form-checkbox h-4 w-4 text-blue-600" {{ old('remember') ? 'checked' : '' }}>
                        <span class="ml-2">Remember Me</span>
                    </label>
                    
                    
                </div>

                <div class="flex items-center justify-center">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline transition duration-150 ease-in-out" type="submit">
                        Sign In
                    </button>
                </div>
            </form>
            
            <p class="text-center text-gray-500 text-xs mt-4">
                &copy; {{ date('Y') }} CicdBot Manager. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>
