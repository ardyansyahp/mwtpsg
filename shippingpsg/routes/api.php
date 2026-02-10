<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PartCheckController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Part Check API for Inoac Special Handling
Route::get('/part/check/{partNumber}', [PartCheckController::class, 'check']);
