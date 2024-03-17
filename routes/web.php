<?php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



$uri = explode('/', request()->path());
$path = $uri[0] == ''?request()->path():$uri[0];
$param = isset($uri[1])?$uri[1]:'';

Route::prefix('/')->group(function () use ($path, $param) {
    Route::get('/', [MemberController::class, 'redirect']);
    Route::any("$path/{param?}", [MemberController::class, $path]);
});
