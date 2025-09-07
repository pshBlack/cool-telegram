<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Broadcast::routes(['middleware' => ['auth:sanctum']]);