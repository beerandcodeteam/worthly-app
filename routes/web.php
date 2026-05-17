<?php

use App\Http\Controllers\SessionRestoreController;
use App\Livewire\Analyze\Composer;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\History\HistoryPage;
use App\Livewire\Home\HomePage;
use App\Livewire\Onboarding\Carousel;
use App\Livewire\Profile\ProfilePage;
use App\Livewire\Result\OffersPage;
use App\Livewire\Result\ResultPage;
use App\Livewire\Result\ReviewsPage;
use App\Livewire\Result\SimilarPage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', SessionRestoreController::class)->name('session.restore');

Route::get('/home', HomePage::class)->name('home');

Route::get('/history', HistoryPage::class)->name('history');

Route::get('/profile', ProfilePage::class)->name('profile');

Route::get('/analyze', Composer::class)->name('analyze');

Route::get('/analyses/{analysis}', ResultPage::class)
    ->whereNumber('analysis')
    ->name('analyses.show');

Route::get('/analyses/{analysis}/similar', SimilarPage::class)
    ->whereNumber('analysis')
    ->name('analyses.similar');

Route::get('/analyses/{analysis}/reviews', ReviewsPage::class)
    ->whereNumber('analysis')
    ->name('analyses.reviews');

Route::get('/analyses/{analysis}/offers', OffersPage::class)
    ->whereNumber('analysis')
    ->name('analyses.offers');

Route::get('/onboarding', Carousel::class)->name('onboarding');
Route::get('/register', Register::class)->name('register');
Route::get('/login', Login::class)->name('login');

if (App::environment('local')) {
    Route::get('/_dev/ui-kit', function () {
        return view('_dev.ui-kit');
    })->name('_dev.ui-kit');
}
