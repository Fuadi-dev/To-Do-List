<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ToDo extends Model
{
    protected $fillable = [
        'foto_tugas',
        'user_id',
        'judul_tugas',
        'deskripsi_tugas',
        'tanggal_tugas',
        'tanggal_selesai',
        'tanggal_diselesaikan',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'tanggal_diselesaikan' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate($date)
    {
        if ($date) {
            $carbonDate = Carbon::parse($date);
            return $carbonDate->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        }
        return null;
    }

    // Method untuk mengecek dan update status otomatis
    public function updateStatusIfOverdue()
    {
        if ($this->status !== 'selesai' && Carbon::parse($this->tanggal_selesai)->isPast()) {
            $this->status = 'terlambat';
            $this->save();
        }
    }

    // Accessor untuk mendapatkan status real-time
    public function getStatusAttribute($value)
    {
        // Jika sudah selesai, tetap selesai
        if ($value === 'selesai') {
            return $value;
        }
        
        // Jika belum selesai dan sudah melewati deadline, ubah ke terlambat
        if ($value !== 'selesai' && Carbon::parse($this->tanggal_selesai)->isPast()) {
            return 'terlambat';
        }
        
        return $value;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_todo', 'to_do_id', 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
