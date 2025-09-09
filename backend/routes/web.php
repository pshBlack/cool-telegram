<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Broadcast::routes(['middleware' => ['auth:sanctum']]);