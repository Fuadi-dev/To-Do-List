<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Manager</title>
    @vite('resources/css/app.css')
    <!-- Heroicons (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/@heroicons/vue@2.0.16/outline.min.js"></script>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 py-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-indigo-600">TaskMaster</h1>
                <div class="flex items-center space-x-4">
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        + New Task
                    </button>
                    <div class="relative">
                        <img class="h-10 w-10 rounded-full bg-gray-300" src="https://ui-avatars.com/api/?name=User" alt="User profile" />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <header class="py-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl">
                        My Tasks
                    </h2>
                </div>
            </div>
        </header>

        <!-- Filters -->
        <div class="mb-8 bg-white shadow overflow-hidden sm:rounded-lg p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="w-full sm:w-auto">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="all">All</option>
                        <option value="belum_dikerjakan">Belum Dikerjakan</option>
                        <option value="proses">Dalam Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                
                <div class="w-full sm:w-auto">
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category" name="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="all">All Categories</option>
                        <option value="1">Work</option>
                        <option value="2">Personal</option>
                        <option value="3">Study</option>
                    </select>
                </div>
                
                <div class="w-full sm:w-auto">
                    <label for="date" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" name="date" id="date" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                </div>
                
                <div class="w-full sm:w-auto flex items-end">
                    <button type="button" class="mt-1 bg-indigo-100 text-indigo-700 px-4 py-2 rounded-md hover:bg-indigo-200 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Task Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-10">
            <!-- Task Card 1 -->
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                <div class="bg-indigo-500 h-2"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Dalam Proses</span>
                        <span class="text-sm text-gray-500">Due: 20 May 2025</span>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Buat Presentasi Proyek</h3>
                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">Menyiapkan slide presentasi untuk meeting dengan client besok pagi mengenai progress proyek website baru.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Work</span>
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">Urgent</span>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <button class="text-sm text-indigo-600 hover:text-indigo-900">View Details</button>
                        <div>
                            <button class="p-1 rounded-full hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Card 2 -->
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                <div class="bg-red-500 h-2"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Belum Dikerjakan</span>
                        <span class="text-sm text-gray-500">Due: 25 May 2025</span>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Belajar Laravel 10</h3>
                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">Mempelajari fitur-fitur baru pada Laravel 10 dan implementasinya dalam pengembangan web.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Study</span>
                        <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">Development</span>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <button class="text-sm text-indigo-600 hover:text-indigo-900">View Details</button>
                        <div>
                            <button class="p-1 rounded-full hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Card 3 -->
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                <div class="bg-green-500 h-2"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Selesai</span>
                        <span class="text-sm text-gray-500">Due: 15 May 2025</span>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Database Migration</h3>
                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">Melakukan migrasi database dari server lama ke server baru dengan zero downtime.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Work</span>
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Database</span>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <button class="text-sm text-indigo-600 hover:text-indigo-900">View Details</button>
                        <div>
                            <button class="p-1 rounded-full hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6 mb-8">
            <div class="flex flex-1 justify-between sm:hidden">
                <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">12</span> tasks
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" aria-current="page" class="relative z-10 inline-flex items-center border border-indigo-500 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-600 focus:z-20">1</a>
                        <a href="#" class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20">2</a>
                        <a href="#" class="relative hidden items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 md:inline-flex">3</a>
                        <a href="#" class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Detail Modal (hidden by default) -->
    <div class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full" id="taskModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3">
                    <h3 class="text-xl font-semibold text-gray-700">Task Details</h3>
                    <button class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <!-- Task details would go here -->
            </div>
        </div>
    </div>

    <script>
        // JavaScript for interactivity can be added here
        // For example, to show/hide the task detail modal
    </script>
</body>

</html>