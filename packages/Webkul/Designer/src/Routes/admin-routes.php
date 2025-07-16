<?php

use Illuminate\Support\Facades\Route;
use Webkul\Designer\Http\Controllers\Admin\DesignerController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/designer'], function () {
    Route::controller(DesignerController::class)->group(function () {
        Route::get('', 'index')->name('admin.designer.index');
    });
});
