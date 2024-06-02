<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassUsers;
use App\Models\TaskSubmission;
use App\Models\SubmissionFile;
use App\Models\TaskGrade;
use App\Models\User; // Pastikan untuk menambahkan ini
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskSubmissionController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Mulai memproses pengunggahan file');
    
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'student_id' => 'required|exists:users,id',
            'files_upload.*' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,mp4,mkv|max:10240',
        ]);
    
        Log::info('Validasi berhasil', ['validated' => $validated]);
    
        // Membuat satu entri di tabel task_submissions
        $submission = TaskSubmission::create([
            'task_id' => $request->task_id,
            'student_id' => $request->student_id,
            'submitted_at' => now(),
        ]);
    
        Log::info('Submission dibuat', ['submission' => $submission]);
    
        // Mengunggah setiap file dan membuat entri di tabel submission_files
        foreach ($request->file('files_upload') as $file) {
            $filePath = $file->store('files', 'public');
            Log::info('File disimpan', ['filePath' => $filePath]);
    
            $fileName = $file->getClientOriginalName();
            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
            ]);
    
            Log::info('File berhasil disimpan di database', ['fileName' => $fileName]);
        }
    
        return response()->json(['submission' => $submission->load('files')], 201);
    }
    

    public function getSubmissionsByTaskId($taskId)
    {
        $submissions = TaskSubmission::with('files', 'student:id,fullname', )->where('task_id', $taskId)->get();
        return response()->json($submissions);
    }

    // Mendapatkan pengumpulan tugas siswa tertentu
    public function getSubmissionByStudent($taskId, $studentId)
    {
        $submission = TaskSubmission::with('files', 'student:id,fullname')->where('task_id', $taskId)->where('student_id', $studentId)->firstOrFail();
        return response()->json($submission);
    }

    // Menilai tugas siswa
    public function gradeSubmission(Request $request, $submissionId)
{
    $request->validate([
        'grade' => 'required|integer',
    ]);

    $submission = TaskSubmission::findOrFail($submissionId);
    $grade = TaskGrade::updateOrCreate(
        ['submission_id' => $submission->id],
        ['grade' => $request->grade]
    );

    return response()->json(['grade' => $grade], 201);
}

public function getSubmission($taskId, $studentId)
{
    $submission = TaskSubmission::with('files', 'student', 'grade', 'task:id,title,points')->where('task_id', $taskId)->where('task_id', $taskId)->where('student_id', $studentId)->firstOrFail();
    return response()->json($submission);
}


    // Mendapatkan daftar siswa yang belum mengumpulkan tugas berdasarkan task_id dan classroom_id
    public function getStudentsWhoHaveNotSubmitted($taskId, $classroomId)
{
    // Dapatkan id siswa yang telah mengumpulkan tugas untuk task_id tertentu
    $submittedUserIds = TaskSubmission::where('task_id', $taskId)->pluck('student_id');

    // Dapatkan id siswa yang terdaftar dalam kelas tersebut
    $allUserIdsInClass = ClassUsers::where('class_id', $classroomId)->pluck('user_id');

    // Filter siswa yang belum mengumpulkan tugas
    $notSubmittedUserIds = $allUserIdsInClass->reject(function ($userId) use ($submittedUserIds) {
        return $submittedUserIds->contains($userId);
    });

    // Dapatkan data lengkap siswa yang belum mengumpulkan tugas
    $studentsData = User::whereIn('id', $notSubmittedUserIds)->get();

    return response()->json($studentsData);
}
}
