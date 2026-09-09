<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

// Sprint 1 : collections Femme / Homme / Nouveautés / Atelier. Sprint 3 : quiz « Trouver ma taille ».
// Les routes ci-dessous existent pour que la navigation soit résolue ; elles renvoient la home en attendant.
Route::get('/femme', HomeController::class)->name('collections.women');
Route::get('/homme', HomeController::class)->name('collections.men');
Route::get('/nouveautes', HomeController::class)->name('collections.new');
Route::get('/atelier', HomeController::class)->name('collections.atelier');
Route::get('/trouver-ma-taille', HomeController::class)->name('size-quiz');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
