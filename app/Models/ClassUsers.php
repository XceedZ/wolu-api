<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassUsers extends Model
{
    use HasFactory;

    protected $table = 'classes_users';

    protected $fillable = [
        'user_id',
        'class_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'class_id' => 'integer',
    ];

    public static $rules = [
        'user_id' => 'required|integer',
        'class_id' => 'required|integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class);
    }
}
