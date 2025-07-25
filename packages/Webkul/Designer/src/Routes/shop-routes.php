<?php

use Illuminate\Support\Facades\Route;
use Webkul\Designer\Http\Controllers\Shop\DesignerController;

Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'designer'], function () {
    Route::get('', [DesignerController::class, 'index'])->name('shop.designer.index');
});