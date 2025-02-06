<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $data = [
        'user' => 'Abdulloh'
    ];
    return view('welcome',$data);
});
