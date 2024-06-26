<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// rutas de prueba
Route::get('/', static function () {
    return view('welcome');
});

Route::get('/welcome', static function () {
    /** @noinspection ForgottenDebugOutputInspection */
    return phpinfo();
});

Route::get('/pruebas2/{nombre?}', static function ($nombre = null) {
    $texto = 'texto desde la ruta: '.$nombre;

    return "<h2>$texto</h2>";
});

Route::get('/pruebas/{nombre?}', static function ($nombre = null) {

    $texto = 'texto desde la ruta y pasando por view: '.$nombre;

    return view('pruebas', [
        'texto' => $texto,
    ]);
});

Route::get('/animales', [PruebasController::class, 'index']);
Route::get('/testOrm', [PruebasController::class, 'testOrm']);

//rutas api
// pruebas
Route::get('/usuario/pruebas', [UserController::class, 'pruebas']);
Route::get('/categoria/pruebas', [CategoryController::class, 'pruebas']);
Route::get('/posts/pruebas', [PostController::class, 'pruebas']);

// user controler
Route::post('/api/user/register', [UserController::class, 'register']);
Route::post('/api/user/login', [UserController::class, 'login']);
Route::put('/api/user/update', [UserController::class, 'update'])->middleware('api.auth');
Route::post('/api/user/upload', [UserController::class, 'uploadAvatar'])->middleware('api.auth');
Route::get('/api/user/avatar/{filename}', [UserController::class, 'getImage']);
Route::get('/api/user/{id}', [UserController::class, 'getUserDetails'])->middleware('api.auth');
