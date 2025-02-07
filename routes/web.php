<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $data = [
        'user' => 'Abdulloh'
    ];
    return view('welcome',$data);
});
Route::get('about', function () {
    return view('about');
});
Route::get('take-quiz', function () {
    return view('quiz.take-quiz');
});
