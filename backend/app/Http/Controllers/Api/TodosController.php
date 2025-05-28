<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ToDo;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TodosController extends Controller
{
    public function index(Request $request) {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = ToDo::with('categories');

        if(!$request->user()){
            return response()->json(['error' => 'Silakan login untuk melihat To-Do Anda.'], 401);
        }
        
        // Hanya tampilkan todo milik user yang sedang login
        $query->where('user_id', $request->user()->id);

        // Update status otomatis untuk semua todo yang terlambat
        $this->updateOverdueTodos($request);

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
           $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // search by name, description
        if (!empty($search)) {
           $query->where(function ($q) use ($search) {
                $q->where('judul_tugas', 'like', "%{$search}%")
                  ->orWhere('deskripsi_tugas', 'like', "%{$search}%");
            });
        }

        // Sorting data
        $query->orderBy($sortBy, $sortDir);

        // Pagination dengan parameter dinamis
        $todos = $query->paginate($perPage)->withQueryString();
        $categories = Category::all();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mendapatkan data todos',
            'data' => [
                'todos' => $todos,
                'categories' => $categories,
                'search' => $search,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
                'perPage' => $perPage
            ]
        ], 200);
    }

    // Method untuk update status otomatis
    private function updateOverdueTodos($request)
    {
        ToDo::where('user_id', $request->user()->id)
            ->where('status', '!=', 'selesai')
            ->where('tanggal_selesai', '<', Carbon::now())
            ->update(['status' => 'terlambat']);
    }

    public function categories(){
        $category = Category::all();
        
        return response()->json([
            'status' => true,
            'message' => 'Berhasil mendapatkan data',
            'data' => $category
        ], 200);
    }

    function postCategory(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category = new Category();
            $category->name = $request->name;
            $category->save();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan kategori',
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan kategori: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    function addTodo(Request $request){
        try {
            if(!$request->user()){
                return response()->json(['error' => 'Silakan login untuk menambahkan To-Do.'], 401);
            }
            
            $request->validate([
                "judul_tugas" => "required|string|max:255",
                "deskripsi_tugas" => "required|string|max:1000",
                "tanggal_selesai" => "required|date|after_or_equal:today",
                "categories.*" => "exists:categories,id",
            ]);

            $todo = new ToDo();
            $todo->user_id = $request->user()->id;
            $todo->judul_tugas = $request->judul_tugas;
            $todo->deskripsi_tugas = $request->deskripsi_tugas;
            $todo->tanggal_selesai = $request->tanggal_selesai;
            $todo->status = 'belum_dikerjakan';
            $todo->save();

            if($request->has('categories')){
                $todo->categories()->sync($request->categories);
            }

            return response()->json([
                'status' => true,
                'message' => 'Tugas berhasil ditambahkan!',
                'data' => $todo->load('categories')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan tugas: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    function editTodo(Request $request, $id){
        try {
            $todo = ToDo::find($id);
            if(!$todo){
                return response()->json([
                    'status' => false,
                    'message' => 'To-Do tidak ditemukan.',
                    'data' => null
                ], 404);
            }
            
            if($todo->user_id !== $request->user()->id){
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengedit To-Do ini.',
                    'data' => null
                ], 403);
            }

            // Cek apakah todo sudah selesai
            if($todo->status === 'selesai') {
                return response()->json([
                    'status' => false,
                    'message' => 'Tugas yang sudah selesai tidak dapat diedit.',
                    'data' => null
                ], 400);
            }

            $request->validate([
                "judul_tugas" => "required|string|max:255",
                "deskripsi_tugas" => "required|string|max:1000",
                "tanggal_selesai" => "required|date",
                "categories.*" => "exists:categories,id",
            ]);

            $todo->judul_tugas = $request->judul_tugas;
            $todo->deskripsi_tugas = $request->deskripsi_tugas;
            $todo->tanggal_selesai = $request->tanggal_selesai;
            
            // Update status jika deadline berubah
            if(Carbon::parse($request->tanggal_selesai)->isFuture() && $todo->status === 'terlambat') {
                $todo->status = 'belum_dikerjakan';
            }
            
            $todo->save();

            if($request->has('categories')){
                $todo->categories()->sync($request->categories);
            } else {
                $todo->categories()->detach();
            }

            return response()->json([
                'status' => true,
                'message' => 'Tugas berhasil diperbarui!',
                'data' => $todo->load('categories')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui tugas: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    function deleteTodo(Request $request, $id){
        try {
            $todo = ToDo::find($id);
            if (!$todo) {
                return response()->json([
                    'status' => false,
                    'message' => 'To-Do tidak ditemukan.',
                    'data' => null
                ], 404);
            }

            // Verifikasi kepemilikan todo
            if ($todo->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus To-Do ini.',
                    'data' => null
                ], 403);
            }

            $todo->delete();

            return response()->json([
                'status' => true,
                'message' => 'Tugas berhasil dihapus!',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus tugas: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    function toggleStatus(Request $request, $id){
        try {
            $todo = ToDo::find($id);

            if(!$todo){
                return response()->json([
                    'status' => false,
                    'message' => 'To-Do tidak ditemukan.',
                    'data' => null
                ], 404);
            }
            
            if($todo->user_id !== $request->user()->id){
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah To-Do ini.',
                    'data' => null
                ], 403);
            }

            // Validasi: Jika todo sudah selesai, tidak bisa diubah lagi
            if($todo->status === 'selesai') {
                return response()->json([
                    'status' => false,
                    'message' => 'Tugas yang sudah selesai tidak dapat diubah statusnya lagi.',
                    'data' => null
                ], 400);
            }

            // Toggle status dari belum_dikerjakan/terlambat ke selesai
            $todo->status = 'selesai';
            $todo->tanggal_diselesaikan = now();
            $todo->save();

            return response()->json([
                'status' => true,
                'message' => 'Tugas berhasil diselesaikan!',
                'data' => [
                    'id' => $todo->id,
                    'status' => $todo->status,
                    'tanggal_diselesaikan' => $todo->tanggal_diselesaikan
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status tugas: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }
}