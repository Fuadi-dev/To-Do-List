<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ToDo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodosController extends Controller
{
    public function index(Request $request) {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'asc');

        $query = ToDo::with('categories');

        if(!Auth::user()){
            return redirect()->route('login')->with('error', 'Silakan login untuk melihat To-Do Anda.');
        }
        
        // Hanya tampilkan todo milik user yang sedang login
        $query->where('user_id', Auth::user()->id);

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
    function addTodo(Request $request){
        if(!Auth::check()){
            return redirect()->route('login')->with('error', 'Silakan login untuk menambahkan To-Do.');
        }
        $request->validate([
            "foto_tugas" => "image|mimes:jpeg,png,jpg,gif|max:2048",
            "judul_tugas" => "required|string|max:255",
            "deskripsi_tugas" => "required|string|max:1000",
            "tanggal_selesai" => "required|date|after_or_equal:tanggal_tugas",
            "status" => "required|in:belum_dikerjakan,proses,selesai",
            "categories.*" => "exists:categories,id",
        ]);

        if($request->hasFile('foto_tugas')){
            $imageName = time() . '_' . $request->file('foto_tugas')->getClientOriginalName();
            $imagePath = $request->file('foto_tugas')->storeAs('images', $imageName, 'public');
        } else {
            $imagePath = null; // Atau berikan nilai default jika tidak ada gambar
        }

        $todo = new ToDo();
        $todo->foto_tugas = $imagePath;
        $todo->user_id = Auth::id();
        $todo->judul_tugas = $request->judul_tugas;
        $todo->deskripsi_tugas = $request->deskripsi_tugas;
        $todo->tanggal_tugas = now();
        $todo->tanggal_selesai = $request->tanggal_selesai;
        $todo->status = $request->status;
        $todo->save();

        if($request->has('categories')){
            $todo->categories()->sync($request->categories);
        }else{
            $todo->categories()->detach();
        }
        if($todo){
            return redirect()->back()->with('success', 'To-Do berhasil ditambahkan!');
        } else {
            return redirect()->back()->with('error', 'Gagal menambahkan To-Do.');
        }
    }

    function editTodo(Request $request, $id){
        $todo = ToDo::findOrFail($id);
        if(!$todo){
            return redirect()->route('home')->with('error', 'To-Do tidak ditemukan.');
        }else if($todo->user_id != Auth::id()){
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses untuk mengedit To-Do ini.');
        }

        $request->validate([
            "foto_tugas" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            "judul_tugas" => "required|string|max:255",
            "deskripsi_tugas" => "required|string|max:1000",
            "tanggal_selesai" => "required|date|after_or_equal:tanggal_tugas",
            "status" => "required|in:belum_dikerjakan,proses,selesai", // Diperbaiki typo
            "categories.*" => "exists:categories,id",
        ]);

        if($request->hasFile('foto_tugas')){
            $imageName = time() . '_' . $request->file('foto_tugas')->getClientOriginalName();
            $imagePath = $request->file('foto_tugas')->storeAs('images', $imageName, 'public');
            $todo->foto_tugas = $imagePath;
        }

        $todo->judul_tugas = $request->judul_tugas;
        $todo->deskripsi_tugas = $request->deskripsi_tugas;
        $todo->tanggal_selesai = $request->tanggal_selesai;
        $todo->status = $request->status;
        $todo->save();
        
        if($request->has('categories')){
            $todo->categories()->sync($request->categories);
        }else{
            $todo->categories()->detach();
        }
        if($todo){
            return redirect()->back()->with('success', 'To-Do berhasil diperbarui!');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui To-Do.');
        }
    }

    function deleteTodo($id){
        $todo = ToDo::find($id);

        if(!$todo){
            return redirect()->back()->with('error', 'To-Do tidak ditemukan.');
        }else if($todo->user_id != Auth::id()){
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus To-Do ini.');
        }

        $todo->delete();

        if($todo){
            return redirect()->back()->with('success', 'To-Do berhasil dihapus!');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus To-Do.');
        }
    }
}
