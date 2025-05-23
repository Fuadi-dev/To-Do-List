<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
    ];

    public function ToDo(){
        return $this->belongsToMany(ToDo::class, 'category_todo', 'category_id', 'to_do_id');
    }
}
