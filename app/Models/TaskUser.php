<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    use HasFactory;

    protected $table = 'tasks_users'; // Nama tabel relasi

    protected $fillable = [
        'task_id',
        'user_id',
    ];
}
