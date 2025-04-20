<?php

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

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

Route::get('storage/{folder}/{subfolder?}/{filename}', function ($folder, $subfolder = null, $filename) {
    $path = $subfolder 
        ? storage_path('app/public/' . $folder . '/' . $subfolder . '/' . $filename)
        : storage_path('app/public/' . $folder . '/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});