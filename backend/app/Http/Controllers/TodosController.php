<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ToDo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TodosController extends Controller
{
    public function index(Request $request) {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = ToDo::with('categories');

        if(!Auth::user()){
            return redirect()->route('login')->with('error', 'Silakan login untuk melihat To-Do Anda.');
        }
        
        // Hanya tampilkan todo milik user yang sedang login
        $query->where('user_id', Auth::user()->id);

        // Update status otomatis untuk semua todo yang terlambat
        $this->updateOverdueTodos();

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
           $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // search by name, description, and game version
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

        return view('index', compact('todos', 'categories', 'search', 'sortBy', 'sortDir', 'perPage'));
    }

    // Method untuk update status otomatis
    private function updateOverdueTodos()
    {
        ToDo::where('user_id', Auth::id())
            ->where('status', '!=', 'selesai')
            ->where('tanggal_selesai', '<', Carbon::now())
            ->update(['status' => 'terlambat']);
    }

    function addTodo(Request $request){
        if(!Auth::check()){
            return response()->json(['error' => 'Silakan login untuk menambahkan To-Do.'], 401);
        }
        
        $request->validate([
            "foto_tugas" => "image|mimes:jpeg,png,jpg,gif|max:2048",
            "judul_tugas" => "required|string|max:255",
            "deskripsi_tugas" => "required|string|max:1000",
            "tanggal_selesai" => "required|date|after_or_equal:today",
            "categories.*" => "exists:categories,id",
        ]);

        if($request->hasFile('foto_tugas')){
            $imageName = time() . '_' . $request->file('foto_tugas')->getClientOriginalName();
            $imagePath = $request->file('foto_tugas')->storeAs('images', $imageName, 'public');
        } else {
            $imagePath = null;
        }

        $todo = new ToDo();
        $todo->foto_tugas = $imagePath;
        $todo->user_id = Auth::id();
        $todo->judul_tugas = $request->judul_tugas;
        $todo->deskripsi_tugas = $request->deskripsi_tugas;
        $todo->tanggal_selesai = $request->tanggal_selesai;
        $todo->status = 'belum_dikerjakan';
        $todo->save();

        if($request->has('categories')){
            $todo->categories()->sync($request->categories);
        }

        if($todo){
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditambahkan!'
            ]);
        } else {
            return response()->json(['error' => 'Gagal menambahkan tugas.'], 500);
        }
    }

    function editTodo(Request $request, $id){
        $todo = ToDo::find($id);
        if(!$todo){
            return response()->json(['error' => 'To-Do tidak ditemukan.'], 404);
        }else if($todo->user_id != Auth::id()){
            return response()->json(['error' => 'Anda tidak memiliki akses untuk mengedit To-Do ini.'], 403);
        }

        // Cek apakah todo sudah selesai
        if($todo->status === 'selesai') {
            return response()->json(['error' => 'Tugas yang sudah selesai tidak dapat diedit.'], 400);
        }

        $request->validate([
            "foto_tugas" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            "judul_tugas" => "required|string|max:255",
            "deskripsi_tugas" => "required|string|max:1000",
            "tanggal_selesai" => "required|date",
            "categories.*" => "exists:categories,id",
        ]);

        if($request->hasFile('foto_tugas')){
            if($todo->foto_tugas && Storage::disk('public')->exists($todo->foto_tugas)) {
                Storage::disk('public')->delete($todo->foto_tugas);
            }
            $imageName = time() . '_' . $request->file('foto_tugas')->getClientOriginalName();
            $imagePath = $request->file('foto_tugas')->storeAs('images', $imageName, 'public');
            $todo->foto_tugas = $imagePath;
        }

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
        }else{
            $todo->categories()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui!'
        ]);
    }

    function deleteTodo($id){
        $todo = ToDo::find($id);

        if(!$todo){
            return response()->json(['error' => 'To-Do tidak ditemukan.'], 404);
        }else if($todo->user_id != Auth::id()){
            return response()->json(['error' => 'Anda tidak memiliki akses untuk menghapus To-Do ini.'], 403);
        }

        if($todo->foto_tugas && Storage::disk('public')->exists($todo->foto_tugas)) {
            Storage::disk('public')->delete($todo->foto_tugas);
        }

        $todo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus!'
        ]);
    }

    function toggleStatus($id){
        $todo = ToDo::find($id);

        if(!$todo){
            return response()->json(['error' => 'To-Do tidak ditemukan.'], 404);
        }else if($todo->user_id != Auth::id()){
            return response()->json(['error' => 'Anda tidak memiliki akses untuk mengubah To-Do ini.'], 403);
        }

        // Validasi: Jika todo sudah selesai, tidak bisa diubah lagi
        if($todo->status === 'selesai') {
            return response()->json([
                'error' => 'Tugas yang sudah selesai tidak dapat diubah statusnya lagi.'
            ], 400);
        }

        // Toggle status dari belum_dikerjakan/terlambat ke selesai
        $todo->status = 'selesai';
        $todo->tanggal_diselesaikan = now();
        $todo->save();

        return response()->json([
            'success' => true,
            'status' => $todo->status,
            'tanggal_diselesaikan' => $todo->tanggal_diselesaikan,
            'message' => 'Tugas berhasil diselesaikan!'
        ]);
    }
}
