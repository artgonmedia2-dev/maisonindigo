<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CollectionController;
use App\Http\Controllers\Storefront\CutHubController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\JournalController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\SizeQuizController;
use App\Http\Controllers\Storefront\StockAlertController;
use App\Services\SeoService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

// Collections
Route::get('/femme', [CollectionController::class, 'show'])->defaults('handle', 'women')->name('collections.women');
Route::get('/homme', [CollectionController::class, 'show'])->defaults('handle', 'men')->name('collections.men');
Route::get('/nouveautes', [CollectionController::class, 'show'])->defaults('handle', 'new')->name('collections.new');
Route::get('/atelier', [CollectionController::class, 'show'])->defaults('handle', 'atelier')->name('collections.atelier');

// Journal
Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
Route::get('/journal/auteur/{slug}', [JournalController::class, 'author'])->name('journal.author');
Route::get('/journal/{slug}', [JournalController::class, 'show'])->name('journal.show');

// L'ancienne adresse produit renvoie vers la nouvelle, en 301.
Route::get('/produit/{slug}', [ProductController::class, 'legacy'])->name('product.legacy');
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

// Pages d'information & légales
Route::get('/guide-des-tailles', function () {
    return Inertia::render('Static/GuideDesTailles');
})->name('size-guide');

Route::get('/la-maison', function (SeoService $seo) {
    $base = rtrim((string) config('app.url'), '/');

    return Inertia::render('Static/LaMaison', [
        // La page que les moteurs génératifs lisent pour décrire la marque.
        'seo' => $seo->forPage(
            __('storefront.about.meta_title'),
            __('seo.entity').'. '.__('storefront.about.meta_description'),
            '/la-maison',
            [
                ['name' => __('seo.breadcrumb.home'), 'url' => $base.'/'],
                ['name' => __('storefront.about.title'), 'url' => $base.'/la-maison'],
            ],
        ),
        'entity' => __('seo.entity'),
    ]);
})->name('about');

Route::get('/entretien', function () {
    return Inertia::render('Static/Entretien');
})->name('care');

Route::get('/faq', function () {
    return Inertia::render('Static/Faq');
})->name('faq');

Route::get('/contact', function () {
    return Inertia::render('Static/Contact');
})->name('contact');

Route::get('/cgv', function () {
    return Inertia::render('Static/Cgv');
})->name('terms');

Route::get('/retours', function () {
    return Inertia::render('Static/Retours');
})->name('returns');

Route::get('/confidentialite', function () {
    return Inertia::render('Static/Confidentialite');
})->name('privacy');

// Espace client
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
 * Adresses du cluster SEO. Elles se terminent par un motif à deux segments,
 * donc elles sont déclarées en dernier : le hub doit passer avant la fiche
 * produit, sinon /homme/jean-baggy serait lu comme un slug de produit.
 */
Route::whereIn('genre', ['homme', 'femme'])->group(function (): void {
    Route::get('/{genre}/jean-{coupe}', [CutHubController::class, 'show'])->name('hub.show');
    Route::get('/{genre}/jean-{coupe}/{lavage}', [CutHubController::class, 'wash'])->name('hub.wash');
    Route::get('/{genre}/{slug}', [ProductController::class, 'show'])->name('product.show');
});

require __DIR__.'/auth.php';
