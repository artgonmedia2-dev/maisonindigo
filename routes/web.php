<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CollectionController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\SizeQuizController;
use App\Http\Controllers\Storefront\StockAlertController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

// Collections
Route::get('/femme', [CollectionController::class, 'show'])->defaults('handle', 'women')->name('collections.women');
Route::get('/homme', [CollectionController::class, 'show'])->defaults('handle', 'men')->name('collections.men');
Route::get('/nouveautes', [CollectionController::class, 'show'])->defaults('handle', 'new')->name('collections.new');
Route::get('/atelier', [CollectionController::class, 'show'])->defaults('handle', 'atelier')->name('collections.atelier');

// Fiche produit
Route::get('/produit/{product:slug}', [ProductController::class, 'show'])->name('product.show');
Route::post('/me-prevenir', [StockAlertController::class, 'store'])->middleware('throttle:stock-alert')->name('stock-alerts.store');

// Panier
Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::middleware('throttle:cart')->group(function () {
    Route::post('/panier', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/panier/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/panier/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
});

// Commande
Route::get('/commande', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/commande', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::get('/commande/merci/{order:number}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

// Trouver ma taille
Route::get('/trouver-ma-taille', [SizeQuizController::class, 'show'])->name('size-quiz');
Route::post('/trouver-ma-taille', [SizeQuizController::class, 'store'])->middleware('throttle:size-quiz')->name('size-quiz.store');
Route::delete('/trouver-ma-taille', [SizeQuizController::class, 'reset'])->name('size-quiz.reset');

// Espace client
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
