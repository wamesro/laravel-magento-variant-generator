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


/* Configurator Item */
Route::post('/create',         Tool::class . '@create');
Route::get('/get/{id}',         Tool::class . '@get');
Route::get('/getstates',         Tool::class . '@getStates');
Route::post('/delete/{id}',         Tool::class . '@delete');
// Patterns
Route::post('/{id}/patterns/append',         Tool::class . '@appendPatterns');
Route::post('/{id}/patterns/delete',         Tool::class . '@deletePatterns');
Route::get('/{id}/patterns/get',         Tool::class . '@getPatterns');
// Variants
Route::post('/{id}/variants/create',         Tool::class . '@createVariants');
Route::get('/{id}/variants/get',         Tool::class . '@getVariants');
Route::get('/{id}/variants/get/products',         Tool::class . '@getVariantsProducts');
Route::post('/{variantId}/pattern/change',         Tool::class . '@changePattern');
Route::get('/{id}/variants/get/place_patterns',         Tool::class . '@getPlacePatternsData');
Route::post('/{id}/variants/set/pattern_position',         Tool::class . '@setPatternPosition');
Route::get('/{variantId}/get/siblings',         Tool::class . '@getVariantSiblings');
// Export
Route::post('/export_ready/{id}',         Tool::class . '@exportReady');
Route::get('/csv/{id}',         Tool::class . '@getCsv');
