<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'grade',
    ];

    public function submission()
    {
        return $this->belongsTo(TaskSubmission::class, 'submission_id');
    }
}
