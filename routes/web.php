<?php

use App\Http\Controllers\SessionRestoreController;
use App\Livewire\Analyze\Composer;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Home\HomePage;
use App\Livewire\Onboarding\Carousel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', SessionRestoreController::class)->name('session.restore');

Route::get('/home', HomePage::class)->name('home');

Route::get('/analyze', Composer::class)->name('analyze');

Route::get('/analyses/{analysis}', function (string $analysis) {
    return view('placeholder.analysis-show', ['analysis' => $analysis]);
})->name('analyses.show');

Route::get('/onboarding', Carousel::class)->name('onboarding');
Route::get('/register', Register::class)->name('register');
Route::get('/login', Login::class)->name('login');

if (App::environment('local')) {
    Route::get('/_dev/ui-kit', function () {
        return view('_dev.ui-kit');
    })->name('_dev.ui-kit');
}
