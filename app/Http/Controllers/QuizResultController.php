<?php

namespace App\Http\Controllers;

use App\Models\QuizResult;
use Illuminate\Http\Request;

class QuizResultController extends Controller
{
    public function index()
    {
        $results = QuizResult::with('quiz', 'student')->get();
        return response()->json($results);
    }

    public function show($id)
    {
        $result = QuizResult::with('quiz', 'student')->findOrFail($id);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'student_id' => 'required|exists:users,id',
            'score' => 'required|integer'
        ]);

        $result = QuizResult::create($request->all());
        return response()->json($result, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quiz_id' => 'sometimes|required|exists:quizzes,id',
            'student_id' => 'sometimes|required|exists:users,id',
            'score' => 'sometimes|required|integer'
        ]);

        $result = QuizResult::findOrFail($id);
        $result->update($request->all());
        return response()->json($result, 200);
    }

    public function destroy($id)
    {
        QuizResult::destroy($id);
        return response()->json(null, 204);
    }
}
