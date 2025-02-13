<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Quiz;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quiz = Quiz::withCount('questions')
                    ->where('user_id', auth()->user()->id)
                        ->orderBy('id', 'desc')
                            ->get();
        return view('dashboard.quizzes',[
            'quizzes' =>$quiz
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.create-quiz');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'timeLimit' => 'required|integer',
            'questions' => 'required|array',
        ]);
        $quiz = Quiz::create([
            'user_id' => auth()->id(),
            'title'=>$validator['title'],
            'description'=>$validator['description'],
            'time_limit'=>$validator['timeLimit'],
            'slug'=>Str::slug( strtotime('now') . '-'.  $request['title'])
        ]);

        foreach ($validator['questions'] as $question) {
            $questionItem = $quiz->questions()->create([
                'name'=>$question['quiz'],
            ]);
            foreach ($question['options'] as $optionKey => $option) {
                $questionItem->options()->create([
                    'name'=>$option,
                    'is_correct'=>$question['correct'] == $optionKey ? 1 : 0,
                ]);
            }
        }

        return to_route('my-quizzes');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        return view('dashboard.edit-quiz',[
            'quiz' => $quiz->load('questions.options'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validator = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'timeLimit' => 'required|integer',
            'questions' => 'required|array'
        ]);

        $quiz->title = $request['title'];
        $quiz->description = $request['description'];
        $quiz->time_limit = $request['timeLimit'];
        $quiz->slug = Str::slug( strtotime('now') . '-'.  $request['title']);

        $quiz->save();

        $quiz->questions()->delete();
        foreach ($validator['questions'] as $question) {
            $questionItem = $quiz->questions()->create([
                'name'=>$question['quiz'],
            ]);
            foreach ($question['options'] as $optionKey => $option) {
                $questionItem->options()->create([
                    'name'=>$option,
                    'is_correct'=>$question['correct'] == $optionKey ? 1 : 0,
                ]);
            }
        }

        return to_route('my-quizzes')->with('message', 'Quiz updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return to_route('my-quizzes');
    }
    public function startQuiz(string $slug){
        $quiz = Quiz::query()->where('slug', $slug)->with('questions.options')->first();
        return view('quiz.take-quiz',[
            'quiz' => $quiz,
        ]);
    }
    public function takeQuiz(string $slug, Request $request)
    {
        $validator = $request->validate([
            'answer' => 'required|integer|exists:options,id',
        ]);

        $user_id = auth()->id();
        $quiz = Quiz::where('slug', $slug)->first();

        $result = Result::where('quiz_id', $quiz->id)
            ->where('user_id', $user_id)
            ->first();

        if (!$result) {
            $result = Result::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user_id,
                'started_at' => now(),
            ]);

            Answer::create([
                'result_id' => $result->id,
                'option_id' => $validator['answer'],
            ]);

            // Get answered option IDs for this result
            $answeredOptionIds = Answer::where('result_id', $result->id)
                ->pluck('option_id')
                ->toArray();

            // Get quiz with questions and options, excluding answered options
            $quiz = Quiz::with(['questions.options' => function($query) use ($answeredOptionIds) {
                $query->whereNotIn('options.id', $answeredOptionIds);
            }])
                ->where('id', $quiz->id)
                ->first();

            return view('quiz.take-quiz', [
                'quiz' => $quiz,
            ]);
        }

        if ($result->finished_at >= now()) {
            return 'Seni vaqting tugagan yaramas!';
        }

        // Get answered option IDs for this result
        $answeredOptionIds = Answer::where('result_id', $result->id)
            ->pluck('option_id')
            ->toArray();

        // Get quiz with questions and options, excluding answered options
        $quiz = Quiz::with(['questions.options' => function($query) use ($answeredOptionIds) {
            $query->whereNotIn('options.id', $answeredOptionIds);
        }])
            ->where('id', $quiz->id)
            ->first();
        dd($answeredOptionIds, $quiz->load('questions.options'));
        return response()->json($quiz);
        Answer::create([
            'result_id' => $result->id,
            'option_id' => $validator['answer'],
        ]);

        return view('quiz.take-quiz', [
            'quiz' => $quiz,
        ]);
    }
}
