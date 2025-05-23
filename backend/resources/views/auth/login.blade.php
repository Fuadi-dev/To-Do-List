<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login | TaskMaster</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and heading -->
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-indigo-600 tracking-tight">TaskMaster</h1>
            <p class="mt-2 text-sm text-gray-600">Sign in to manage your tasks</p>
        </div>

        <!-- Login form -->
        <div class="bg-white shadow-lg rounded-lg py-8 px-6">
            <form class="space-y-6" action="{{ route('login.post') }}" method="POST">
                @csrf
                
                <!-- Email input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" 
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                    </div>

                    <div class="text-sm">
                        <a href="" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <!-- Submit button -->
                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Sign in
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Register link -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Register now
                </a>
            </p>
        </div>
        
        <!-- Decorative element -->
        <div class="flex justify-center mt-4">
            <div class="h-1 w-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full"></div>
        </div>
    </div>

    <!-- Background decoration (optional) -->
    <div class="hidden lg:block fixed top-0 right-0 w-1/3 h-full bg-gradient-to-l from-indigo-100 to-transparent opacity-50 -z-10"></div>
    <div class="hidden lg:block fixed bottom-0 left-0 w-1/3 h-full bg-gradient-to-t from-indigo-100 to-transparent opacity-50 -z-10"></div>
</body>
</html>