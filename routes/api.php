<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Wamesro\LaravelMagentoVariantGenerator\Http\Controllers\Tool;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

// Route::get('/endpoint', function (Request $request) {
//     //
// });

Route::post('/create',         Tool::class . '@create');
Route::get('/get',         Tool::class . '@get');
