<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TodoApp - Kelola Tugas Anda</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen" x-data="todoApp()">
    @include('components.navbar')
    
    {{-- <!-- Alert Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
                    <i class="fas fa-times cursor-pointer"></i>
                </span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
                    <i class="fas fa-times cursor-pointer"></i>
                </span>
            </div>
        </div>
    @endif --}}

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Daftar Tugas Anda</h1>
            <p class="text-gray-600">Kelola dan pantau progres tugas harian Anda</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" x-data="{ showFilters: false }">
            <form method="GET" action="{{ route('home') }}" class="space-y-4">
                <!-- Search Bar -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ $search }}" 
                                   placeholder="Cari tugas berdasarkan judul atau deskripsi..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <button type="button" @click="showFilters = !showFilters"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </div>

                <!-- Advanced Filters -->
                <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan Berdasarkan</label>
                        <select name="sort_by" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                            <option value="judul_tugas" {{ $sortBy == 'judul_tugas' ? 'selected' : '' }}>Judul Tugas</option>
                            <option value="tanggal_selesai" {{ $sortBy == 'tanggal_selesai' ? 'selected' : '' }}>Deadline</option>
                            <option value="status" {{ $sortBy == 'status' ? 'selected' : '' }}>Status</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Arah</label>
                        <select name="sort_dir" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="asc" {{ $sortDir == 'asc' ? 'selected' : '' }}>A-Z / Terlama</option>
                            <option value="desc" {{ $sortDir == 'desc' ? 'selected' : '' }}>Z-A / Terbaru</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Halaman</label>
                        <select name="per_page" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Add Todo Button -->
        <div class="mb-6">
            <button @click="openAddModal()" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>Tambah Tugas Baru
            </button>
        </div>

        <!-- Todo Cards Grid -->
  <!-- Todo Aktif -->
        @php
            $todosActive = $todos->filter(function($todo) {
                return $todo->status !== 'selesai';
            });
            $todosCompleted = $todos->filter(function($todo) {
                return $todo->status === 'selesai';
            });
            $todosOverdue = $todosActive->filter(function($todo) {
                return $todo->status === 'terlambat';
            });
            $todosInProgress = $todosActive->filter(function($todo) {
                return $todo->status === 'belum_dikerjakan';
            });
        @endphp

        <!-- Tugas Terlambat -->
        @if($todosOverdue->count() > 0)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-red-700 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Tugas Terlambat
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($todosOverdue as $todo)
                        <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <!-- Status Badge -->
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Terlambat
                                    </span>
                                </div>

                                <!-- Todo Title -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 cursor-pointer" @click="viewTodo({{ $todo->id }})">{{ $todo->judul_tugas }}</h3>
                                
                                <!-- Todo Description -->
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($todo->deskripsi_tugas, 100) }}</p>

                                <!-- Categories -->
                                @if($todo->categories->count() > 0)
                                    <div class="mb-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($todo->categories as $category)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Deadline (Overdue) -->
                                <div class="flex items-center text-sm text-red-600 mb-4 font-medium">
                                    <i class="fas fa-calendar-times mr-2"></i>
                                    Terlambat: {{ \Carbon\Carbon::parse($todo->tanggal_selesai)->diffForHumans() }}
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <!-- Checkbox untuk toggle status -->
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="todo-overdue-{{ $todo->id }}"
                                                   @change="confirmToggleStatus({{ $todo->id }}, $event)"
                                                   class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2 cursor-pointer">
                                            <label for="todo-overdue-{{ $todo->id }}" 
                                                   class="ml-2 text-sm text-gray-600 cursor-pointer">
                                                Tandai Selesai
                                            </label>
                                        </div>

                                        <div class="flex space-x-3">
                                            <button @click="viewTodo({{ $todo->id }})" 
                                                    class="text-blue-600 hover:text-blue-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button @click="editTodo({{ $todo->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteTodo({{ $todo->id }})" 
                                                    class="text-red-600 hover:text-red-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($todo->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tugas Aktif (yang belum terlambat) -->
        @if($todosInProgress->count() > 0)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-tasks mr-2 text-indigo-600"></i>Tugas Aktif
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($todosInProgress as $todo)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <!-- Status Badge -->
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Belum Dikerjakan
                                    </span>
                                </div>

                                <!-- Todo Title -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 cursor-pointer" @click="viewTodo({{ $todo->id }})">{{ $todo->judul_tugas }}</h3>
                                
                                <!-- Todo Description -->
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($todo->deskripsi_tugas, 100) }}</p>

                                <!-- Categories -->
                                @if($todo->categories->count() > 0)
                                    <div class="mb-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($todo->categories as $category)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Deadline -->
                                <div class="flex items-center text-sm text-gray-400 mb-4">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Deadline: {{ \Carbon\Carbon::parse($todo->tanggal_selesai)->format('d M Y') }}
                                    @if(\Carbon\Carbon::parse($todo->tanggal_selesai)->isToday())
                                        <span class="ml-2 text-orange-600 font-medium">(Hari ini!)</span>
                                    @elseif(\Carbon\Carbon::parse($todo->tanggal_selesai)->isTomorrow())
                                        <span class="ml-2 text-yellow-600 font-medium">(Besok)</span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <!-- Checkbox untuk toggle status -->
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="todo-{{ $todo->id }}"
                                                   @change="confirmToggleStatus({{ $todo->id }}, $event)"
                                                   class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2 cursor-pointer">
                                            <label for="todo-{{ $todo->id }}" 
                                                   class="ml-2 text-sm text-gray-600 cursor-pointer">
                                                Tandai Selesai
                                            </label>
                                        </div>

                                        <div class="flex space-x-3">
                                            <button @click="viewTodo({{ $todo->id }})" 
                                                    class="text-blue-600 hover:text-blue-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button @click="editTodo({{ $todo->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteTodo({{ $todo->id }})" 
                                                    class="text-red-600 hover:text-red-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($todo->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Todo Selesai -->
        @if($todosCompleted->count() > 0)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-600"></i>Tugas Selesai
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($todosCompleted as $todo)
                        <div class="bg-gray-50 rounded-lg shadow-sm border border-gray-200 overflow-hidden opacity-75">
                            <div class="p-6">
                                <!-- Status Badge -->
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Selesai
                                    </span>
                                </div>

                                <!-- Todo Title (dengan strikethrough) -->
                                <h3 class="text-lg font-semibold text-gray-600 mb-2 cursor-pointer line-through" @click="viewTodo({{ $todo->id }})">{{ $todo->judul_tugas }}</h3>
                                
                                <!-- Todo Description -->
                                <p class="text-gray-500 text-sm mb-4 line-clamp-3">{{ Str::limit($todo->deskripsi_tugas, 100) }}</p>

                                <!-- Categories -->
                                @if($todo->categories->count() > 0)
                                    <div class="mb-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($todo->categories as $category)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Deadline -->
                                <div class="flex items-center text-sm text-gray-400 mb-4">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    @if($todo->tanggal_diselesaikan)
                                        Selesai pada: {{ \Carbon\Carbon::parse($todo->tanggal_diselesaikan)->format('d M Y H:i') }}
                                    @else
                                        Deadline: {{ \Carbon\Carbon::parse($todo->tanggal_selesai)->format('d M Y') }}
                                    @endif
                                </div>

                                <!-- Action Buttons (hanya view dan delete untuk todo selesai) -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <!-- Checkbox untuk todo selesai (disabled) -->
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="todo-completed-{{ $todo->id }}"
                                                   checked
                                                   disabled
                                                   class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded cursor-not-allowed opacity-60">
                                            <label for="todo-completed-{{ $todo->id }}" 
                                                   class="ml-2 text-sm text-gray-500 cursor-not-allowed">
                                                Selesai
                                            </label>
                                        </div>
                                        
                                        <div class="flex space-x-3">
                                            <button @click="viewTodo({{ $todo->id }})" 
                                                    class="text-blue-600 hover:text-blue-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button @click="deleteTodo({{ $todo->id }})" 
                                                    class="text-red-600 hover:text-red-800 transition-colors duration-200 p-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <span class="text-gray-400 p-2 cursor-not-allowed" title="Tugas selesai tidak dapat diedit">
                                                <i class="fas fa-edit"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($todo->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Jika tidak ada todo sama sekali -->
        @if($todosActive->count() == 0 && $todosCompleted->count() == 0)
            <div class="col-span-full text-center py-12">
                <i class="fas fa-tasks text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada tugas</h3>
                <p class="text-gray-500 mb-4">Mulai dengan menambahkan tugas pertama Anda!</p>
                <button @click="openAddModal()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                    Tambah Tugas
                </button>
            </div>
        @endif
      

        <!-- Pagination -->
        @if($todos->hasPages())
            <div class="flex justify-center">
                {{ $todos->links() }}
            </div>
        @endif
    </div>

    <!-- Add Todo Modal -->
    <div x-show="showAddModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Tugas Baru</h3>
                    <button @click="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit="submitAddForm" method="POST" action="{{ route('todo.add') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Tugas</label>
                        <input type="file" name="foto_tugas" accept="image/*"
                               @change="previewAddImage($event)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        
                        <!-- Image Preview for Add Modal -->
                        <div x-show="addImagePreview" class="mt-4">
                            <div class="relative inline-block">
                                <img :src="addImagePreview" alt="Preview" class="w-32 h-24 object-cover rounded-lg border-2 border-gray-300">
                                <button type="button" @click="removeAddImagePreview()" 
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Tugas *</label>
                        <input type="text" name="judul_tugas" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Tugas *</label>
                        <textarea name="deskripsi_tugas" rows="4" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                        <input type="date" name="tanggal_selesai" required min="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    {{-- <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="belum_dikerjakan">Belum Dikerjakan</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div> --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" @click="closeAddModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Todo Modal -->
    <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Edit Tugas</h3>
                    <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit="submitEditForm" :action="`/todo/${selectedTodo.id}/edit`" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Tugas</label>
                        <input type="file" name="foto_tugas" accept="image/*"
                               @change="previewEditImage($event)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        
                        <!-- Current Image Display -->
                        <div class="mt-3">
                            <p class="text-sm text-gray-600 mb-2">Foto saat ini:</p>
                            <div x-show="selectedTodo.foto_tugas && !editImagePreview" class="relative inline-block">
                                <img :src="`/storage/${selectedTodo.foto_tugas}`" alt="Current Image" 
                                     class="w-32 h-24 object-cover rounded-lg border-2 border-gray-300">
                            </div>
                        </div>
                        
                        <!-- New Image Preview -->
                        <div x-show="editImagePreview" class="mt-3">
                            <p class="text-sm text-gray-600 mb-2">Preview foto baru:</p>
                            <div class="relative inline-block">
                                <img :src="editImagePreview" alt="New Preview" 
                                     class="w-32 h-24 object-cover rounded-lg border-2 border-indigo-300">
                                <button type="button" @click="removeEditImagePreview()" 
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Tugas *</label>
                        <input type="text" name="judul_tugas" :value="selectedTodo.judul_tugas" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Tugas *</label>
                        <textarea name="deskripsi_tugas" rows="4" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  x-text="selectedTodo.deskripsi_tugas"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                        <input type="date" name="tanggal_selesai" :value="selectedTodo.tanggal_selesai" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    {{-- <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="belum_dikerjakan" :selected="selectedTodo.status === 'belum_dikerjakan'">Belum Dikerjakan</option>
                            <option value="selesai" :selected="selectedTodo.status === 'selesai'">Selesai</option>
                        </select>
                    </div> --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           :checked="selectedTodo.categories && selectedTodo.categories.some(cat => cat.id === {{ $category->id }})"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" @click="closeEditModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Todo Modal -->
    <div x-show="showViewModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Detail Tugas</h3>
                    <button @click="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <!-- Image with Zoom Feature -->
                    <div x-show="selectedTodo.foto_tugas">
                        <div class="relative group">
                            <img :src="`/storage/${selectedTodo.foto_tugas}`" :alt="selectedTodo.judul_tugas" 
                                 class="w-full h-64 object-cover rounded-lg cursor-pointer transition-transform duration-200 hover:scale-105"
                                 @click="openImageModal()">
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                              :class="{
                                'bg-red-100 text-red-800': selectedTodo.status === 'belum_dikerjakan',
                                'bg-green-100 text-green-800': selectedTodo.status === 'selesai'
                              }"
                              x-text="getStatusText(selectedTodo.status)">
                        </span>
                    </div>

                    <!-- Title -->
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900" x-text="selectedTodo.judul_tugas"></h4>
                    </div>

                    <!-- Description -->
                    <div>
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Deskripsi:</h5>
                        <p class="text-gray-600 leading-relaxed" x-text="selectedTodo.deskripsi_tugas"></p>
                    </div>

                    <!-- Categories -->
                    <div x-show="selectedTodo.categories && selectedTodo.categories.length > 0">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Kategori:</h5>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="category in selectedTodo.categories" :key="category.id">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800"
                                      x-text="category.name"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-1">Tanggal Dibuat:</h5>
                            <p class="text-gray-600" x-text="formatDate(selectedTodo.created_at)"></p>
                        </div>
                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-1">
                                <span x-show="selectedTodo.status === 'selesai' && selectedTodo.tanggal_diselesaikan">Selesai pada:</span>
                                <span x-show="selectedTodo.status !== 'selesai' || !selectedTodo.tanggal_diselesaikan">Deadline:</span>
                            </h5>
                            <p class="text-gray-600">
                                <span x-show="selectedTodo.status === 'selesai' && selectedTodo.tanggal_diselesaikan" 
                                      x-text="formatDateTime(selectedTodo.tanggal_diselesaikan)"></span>
                                <span x-show="selectedTodo.status !== 'selesai' || !selectedTodo.tanggal_diselesaikan" 
                                      x-text="formatDate(selectedTodo.tanggal_selesai)"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    {{-- <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button @click="closeViewModal(); editTodo(selectedTodo.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </button>
                        <button @click="deleteTodo(selectedTodo.id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>Hapus
                        </button>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Image Zoom Modal -->
    <div x-show="showImageModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-60" style="display: none;">
        <div class="relative max-w-4xl max-h-[90vh] p-4">
            <button @click="closeImageModal()" 
                    class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70 z-10">
                <i class="fas fa-times text-lg"></i>
            </button>
            <img :src="`/storage/${selectedTodo.foto_tugas}`" :alt="selectedTodo.judul_tugas" 
                 class="max-w-full max-h-full object-contain rounded-lg"
                 @click="closeImageModal()">
        </div>
    </div>

    <script>
        function todoApp() {
            return {
                showAddModal: false,
                showEditModal: false,
                showViewModal: false,
                showImageModal: false,
                selectedTodo: {},
                todos: @json($todos->items()),
                addImagePreview: null,
                editImagePreview: null,

                openAddModal() {
                    this.showAddModal = true;
                    this.addImagePreview = null;
                    document.body.style.overflow = 'hidden';
                },

                closeAddModal() {
                    this.showAddModal = false;
                    this.addImagePreview = null;
                    document.body.style.overflow = 'auto';
                    // Reset form
                    const form = document.querySelector('form[action="{{ route('todo.add') }}"]');
                    if (form) form.reset();
                },

                editTodo(todoId) {
                    const todo = this.todos.find(t => t.id === todoId);
                    if (todo) {
                        // Cek apakah todo sudah selesai
                        if (todo.status === 'selesai') {
                            this.showNotification('Tugas yang sudah selesai tidak dapat diedit!', 'error');
                            return;
                        }
                        this.selectedTodo = { ...todo };
                        this.showEditModal = true;
                        this.showViewModal = false;
                        this.editImagePreview = null;
                        document.body.style.overflow = 'hidden';
                    }
                },

                closeEditModal() {
                    this.showEditModal = false;
                    this.selectedTodo = {};
                    this.editImagePreview = null;
                    document.body.style.overflow = 'auto';
                },

                viewTodo(todoId) {
                    const todo = this.todos.find(t => t.id === todoId);
                    if (todo) {
                        this.selectedTodo = { ...todo };
                        this.showViewModal = true;
                        document.body.style.overflow = 'hidden';
                    }
                },

                closeViewModal() {
                    this.showViewModal = false;
                    this.selectedTodo = {};
                    document.body.style.overflow = 'auto';
                },

                openImageModal() {
                    this.showImageModal = true;
                },

                closeImageModal() {
                    this.showImageModal = false;
                },

                previewAddImage(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.addImagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                removeAddImagePreview() {
                    this.addImagePreview = null;
                    const fileInput = document.querySelector('input[name="foto_tugas"]');
                    if (fileInput) fileInput.value = '';
                },

                previewEditImage(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.editImagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                removeEditImagePreview() {
                    this.editImagePreview = null;
                    const fileInput = document.querySelector('input[name="foto_tugas"][accept="image/*"]');
                    if (fileInput) fileInput.value = '';
                },

                async submitAddForm(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showNotification(data.message, 'success');
                            this.closeAddModal();
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showNotification(data.error || 'Terjadi kesalahan', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showNotification('Terjadi kesalahan saat menambahkan tugas', 'error');
                    }
                },

                async submitEditForm(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    
                    // Add method spoofing for PUT request
                    formData.append('_method', 'PUT');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST', // Laravel menggunakan POST dengan method spoofing
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showNotification(data.message, 'success');
                            this.closeEditModal();
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showNotification(data.error || 'Terjadi kesalahan', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showNotification('Terjadi kesalahan saat memperbarui tugas', 'error');
                    }
                },

                async deleteTodo(todoId) {
                    const confirmed = await this.showConfirmationModal(
                        'Konfirmasi Hapus Tugas',
                        'Apakah Anda yakin ingin menghapus tugas ini?',
                        'Tugas yang dihapus tidak dapat dikembalikan.',
                        'Ya, Hapus',
                        'Batal'
                    );

                    if (!confirmed) return;

                    try {
                        const response = await fetch(`/todo/${todoId}/delete`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showNotification(data.message, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showNotification(data.error || 'Terjadi kesalahan', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showNotification('Terjadi kesalahan saat menghapus tugas', 'error');
                    }
                },

                getStatusText(status) {
                    const statusMap = {
                        'belum_dikerjakan': 'Belum Dikerjakan',
                        'terlambat': 'Terlambat',
                        'selesai': 'Selesai'
                    };
                    return statusMap[status] || status;
                },

                formatDate(dateString) {
                    const options = { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    };
                    return new Date(dateString).toLocaleDateString('id-ID', options);
                },

                formatDateTime(dateString) {
                    const options = { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    return new Date(dateString).toLocaleDateString('id-ID', options);
                },

                async confirmToggleStatus(todoId, event) {
                    // Cek status todo sebelum konfirmasi
                    const todo = this.todos.find(t => t.id === todoId);
                    
                    if (todo && todo.status === 'selesai') {
                        this.showNotification('Tugas yang sudah selesai tidak dapat diubah statusnya lagi.', 'error');
                        // Reset checkbox ke checked karena todo sudah selesai
                        event.target.checked = true;
                        return;
                    }

                    // Untuk todo yang belum selesai, tampilkan konfirmasi
                    const confirmed = await this.showConfirmationModal(
                        'Konfirmasi Penyelesaian Tugas',
                        `Apakah Anda yakin ingin menandai tugas "${todo.judul_tugas}" sebagai selesai?`,
                        'Tugas yang sudah diselesaikan tidak dapat diubah kembali.',
                        'Ya, Selesaikan',
                        'Batal'
                    );

                    if (confirmed) {
                        this.toggleTodoStatus(todoId);
                    } else {
                        // Reset checkbox ke unchecked karena user membatalkan
                        event.target.checked = false;
                    }
                },

                showConfirmationModal(title, message, warning, confirmText, cancelText) {
                    return new Promise((resolve) => {
                        // Create modal element
                        const modal = document.createElement('div');
                        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                        modal.innerHTML = `
                            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-lg font-medium text-gray-900">${title}</h3>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">${message}</p>
                                    <p class="text-xs text-red-600 font-medium">${warning}</p>
                                </div>
                                
                                <div class="flex justify-end space-x-3">
                                    <button id="cancelBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                                        ${cancelText}
                                    </button>
                                    <button id="confirmBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                        <i class="fas fa-check mr-2"></i>${confirmText}
                                    </button>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(modal);
                        document.body.style.overflow = 'hidden';

                        // Add event listeners
                        const confirmBtn = modal.querySelector('#confirmBtn');
                        const cancelBtn = modal.querySelector('#cancelBtn');

                        const cleanup = () => {
                            document.body.removeChild(modal);
                            document.body.style.overflow = 'auto';
                        };

                        confirmBtn.addEventListener('click', () => {
                            cleanup();
                            resolve(true);
                        });

                        cancelBtn.addEventListener('click', () => {
                            cleanup();
                            resolve(false);
                        });

                        // Close on click outside
                        modal.addEventListener('click', (e) => {
                            if (e.target === modal) {
                                cleanup();
                                resolve(false);
                            }
                        });

                        // Close on Escape key
                        const escapeHandler = (e) => {
                            if (e.key === 'Escape') {
                                cleanup();
                                resolve(false);
                                document.removeEventListener('keydown', escapeHandler);
                            }
                        };
                        document.addEventListener('keydown', escapeHandler);
                    });
                },

                async toggleTodoStatus(todoId) {
                    try {
                        const response = await fetch(`/todo/${todoId}/toggle-status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update todo status in local data
                            const todo = this.todos.find(t => t.id === todoId);
                            if (todo) {
                                todo.status = data.status;
                                todo.tanggal_diselesaikan = data.tanggal_diselesaikan;
                            }

                            // Show success message
                            this.showNotification(data.message, 'success');

                            // Refresh page to update the UI layout (active vs completed sections)
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showNotification(data.error || 'Terjadi kesalahan', 'error');
                            
                            // Reset checkbox jika terjadi error
                            const checkbox = document.getElementById(`todo-${todoId}`);
                            if (checkbox) checkbox.checked = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showNotification('Terjadi kesalahan saat mengubah status', 'error');
                        
                        // Reset checkbox jika terjadi error
                        const checkbox = document.getElementById(`todo-${todoId}`);
                        if (checkbox) checkbox.checked = false;
                    }
                },

                showNotification(message, type = 'success') {
                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 ${
                        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'
                    }`;
                    notification.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                            <span>${message}</span>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">&times;</button>
                        </div>
                    `;

                    document.body.appendChild(notification);

                    // Auto remove after 3 seconds
                    setTimeout(() => {
                        if (notification.parentElement) {
                            notification.remove();
                        }
                    }, 3000);
                }
            }
        }
    </script>
</body>
</html>
