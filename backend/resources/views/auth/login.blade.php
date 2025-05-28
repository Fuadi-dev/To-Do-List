<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login | TaskMaster</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4" x-data="authApp()">
    
    <!-- Error Notification Pop-up -->
    @if(session('error') || $errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-[-50px]" 
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-[-50px]"
             class="fixed top-4 right-4 z-50 max-w-sm w-full">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-red-800">Login Gagal</h3>
                        <div class="mt-1 text-sm text-red-600">
                            @if(session('error'))
                                {{ session('error') }}
                            @endif
                            @if($errors->any())
                                <ul class="list-disc list-inside mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3 flex-shrink-0">
                        <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Notification Pop-up -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-[-50px]" 
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-[-50px]"
             class="fixed top-4 right-4 z-50 max-w-sm w-full">
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-green-800">Berhasil</h3>
                        <div class="mt-1 text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    </div>
                    <div class="ml-3 flex-shrink-0">
                        <button @click="show = false" class="text-green-400 hover:text-green-600 transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-md w-full space-y-8">
        <!-- Logo and heading -->
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-indigo-600 tracking-tight">TaskMaster</h1>
            <p class="mt-2 text-sm text-gray-600">Login untuk memanagement tugasmu</p>
        </div>

        <!-- Login form -->
        <div class="bg-white shadow-lg rounded-lg py-8 px-6">
            <form class="space-y-6" action="{{ route('login.post') }}" method="POST" @submit="submitForm">
                @csrf
                
                <!-- Email input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': hasEmailError }"
                            value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                            class="appearance-none block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': hasPasswordError }">
                        <button type="button" @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
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
                        <a href="#" @click="showForgotPasswordModal = true" class="font-medium text-indigo-600 hover:text-indigo-500 cursor-pointer">
                            Lupa password?
                        </a>
                    </div>
                </div>

                <!-- Submit button -->
                <div>
                    <button type="submit" :disabled="isLoading"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isLoading">Login</span>
                        <span x-show="isLoading" class="flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Masuk...
                        </span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Register link -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Register
                </a>
            </p>
        </div>
        
        <!-- Decorative element -->
        <div class="flex justify-center mt-4">
            <div class="h-1 w-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full"></div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div x-show="showForgotPasswordModal" x-transition.opacity 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" 
         style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Lupa Password</h3>
                <button @click="showForgotPasswordModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Fitur reset password belum tersedia. Silakan hubungi administrator untuk reset password.
            </p>
            <div class="flex justify-end">
                <button @click="showForgotPasswordModal = false" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Background decoration -->
    <div class="hidden lg:block fixed top-0 right-0 w-1/3 h-full bg-gradient-to-l from-indigo-100 to-transparent opacity-50 -z-10"></div>
    <div class="hidden lg:block fixed bottom-0 left-0 w-1/3 h-full bg-gradient-to-t from-indigo-100 to-transparent opacity-50 -z-10"></div>

    <script>
        function authApp() {
            return {
                showPassword: false,
                isLoading: false,
                showForgotPasswordModal: false,
                hasEmailError: {{ $errors->has('email') ? 'true' : 'false' }},
                hasPasswordError: {{ $errors->has('password') ? 'true' : 'false' }},

                submitForm(event) {
                    this.isLoading = true;
                    // Form akan submit secara normal, loading state akan reset saat halaman reload
                },

                // Auto hide notifications after 5 seconds
                init() {
                    setTimeout(() => {
                        const notifications = document.querySelectorAll('[x-data*="show: true"]');
                        notifications.forEach(notification => {
                            const alpineData = Alpine.$data(notification);
                            if (alpineData && alpineData.show) {
                                alpineData.show = false;
                            }
                        });
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>