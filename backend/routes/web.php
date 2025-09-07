<?php

use Illuminate\Support\Facades\Route;
use App\Models\Chat;
use illumkinate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return view('welcome');
});
