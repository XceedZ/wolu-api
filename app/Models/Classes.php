<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_name',
        'description',
        'background_img',
        'teacher_id',
    ];
    public function generateShareToken()
    {
        $this->share_token = Str::random(10);
        $this->save();
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'classes_users', 'class_id', 'user_id');
    }
}
