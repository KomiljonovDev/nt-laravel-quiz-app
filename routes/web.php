<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use  App\Http\Controllers\QuizController;
use App\Http\Controllers\DashboardController;

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');
Route::get('/about', [HomeController::class, 'about'])->name('about');

Route::get('/take-quiz', [QuizController::class, 'takeQuiz'])->middleware('auth')->name('take-quiz');
Route::get('/dashboard', [DashboardController::class, 'home'])->name('dashboard');
Route::get('/dashboard/quizzes', [DashboardController::class, 'quizzes'])->name('my-quizzes');
Route::get('/dashboard/create-quiz', [DashboardController::class, 'createQuiz'])->name('create-quizzes');
Route::get('/dashboard/statistics', [DashboardController::class, 'statistics'])->name('create-quizzes');

//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
