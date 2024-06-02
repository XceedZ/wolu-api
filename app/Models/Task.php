<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'points',
        'deadline',
        'teacher_id',
        'files_upload',
    ];

    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }

    public function taskSubmission()
    {
        return $this->hasOne(TaskSubmission::class);
    }

    // Relasi untuk mengambil poin dari task langsung
    public function points()
    {
        return $this->hasOne(Task::class);
    }
}

