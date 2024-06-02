<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Classes;
use App\Models\Answer;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with('questions.answers')->get();
        return response()->json($quizzes);
    }

    public function show($id)
    {
        $quiz = Quiz::with('questions.answers')->findOrFail($id);
        return response()->json($quiz);
    }

    public function getQuizForStudent($id)
    {
        $quiz = Quiz::with('questions.answers')->findOrFail($id);
        return response()->json($quiz);
    }

    public function submitQuizResult(Request $request, $quizId)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_answer_id' => 'required|exists:answers,id',
        ]);

        $quiz = Quiz::findOrFail($quizId);
        $score = 0;

        foreach ($request->answers as $answer) {
            $correctAnswer = $quiz->questions()
                ->findOrFail($answer['question_id'])
                ->answers()
                ->where('is_correct', true)
                ->first();

            if ($correctAnswer->id == $answer['selected_answer_id']) {
                $score++;
            }
        }

        $totalQuestions = $quiz->questions()->count();
        $percentageScore = ($score / $totalQuestions) * 100;

        return response()->json(['score' => $percentageScore], 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'timer' => 'nullable|integer',
            'class_id' => 'required|exists:classes,id',
            'questions' => 'nullable|array',
            'questions.*.question' => 'required_with:questions|string',
            'questions.*.image' => 'nullable|image|max:2048',
            'questions.*.answers' => 'nullable|array',
            'questions.*.answers.*.answer' => 'required_with:questions.*.answers|string',
            'questions.*.answers.*.is_correct' => 'required_with:questions.*.answers|boolean',
            'questions.*.answers.*.image' => 'nullable|image|max:2048',
        ]);

        $quiz = Quiz::create($request->only('title', 'description', 'deadline', 'timer', 'class_id'));

        if ($request->has('questions')) {
            foreach ($request->questions as $questionData) {
                $question = $quiz->questions()->create(['question' => $questionData['question']]);

                if (isset($questionData['image'])) {
                    $question->image_path = $questionData['image']->store('question_images', 'public');
                    $question->save();
                }

                if (isset($questionData['answers'])) {
                    foreach ($questionData['answers'] as $answerData) {
                        $answer = $question->answers()->create([
                            'answer' => $answerData['answer'],
                            'is_correct' => $answerData['is_correct']
                        ]);

                        if (isset($answerData['image'])) {
                            $answer->image_path = $answerData['image']->store('answer_images', 'public');
                            $answer->save();
                        }
                    }
                }
            }
        }

        return response()->json($quiz->load('questions.answers'), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'timer' => 'nullable|integer',
            'class_id' => 'sometimes|required|exists:classes,id',
        ]);

        $quiz = Quiz::findOrFail($id);
        $quiz->update($request->only('title', 'description', 'deadline', 'timer', 'class_id'));

        return response()->json($quiz);
    }

    public function destroy($id)
    {
        Quiz::destroy($id);
        return response()->json(null, 204);
    }

    public function storeResult(Request $request, $quizId)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'score' => 'required|integer',
        ]);

        $quizResult = QuizResult::create([
            'quiz_id' => $quizId,
            'student_id' => $request->student_id,
            'score' => $request->score,
        ]);

        return response()->json($quizResult, 201);
    }

    public function getResult($quizId, $studentId)
    {
        $result = QuizResult::where('quiz_id', $quizId)->where('student_id', $studentId)->firstOrFail();
        return response()->json($result);
    }

    public function getQuizzesByClass($classId)
    {
        $quizzes = Quiz::where('class_id', $classId)->with('questions.answers')->get();
        return response()->json($quizzes);
    }
}
