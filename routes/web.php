<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (App::environment('local')) {
    Route::get('/_dev/ui-kit', function () {
        return view('_dev.ui-kit');
    })->name('_dev.ui-kit');
}
