<?php

use App\Http\Controllers\Storefront\LlmsController;
use App\Http\Controllers\Storefront\RobotsController;
use App\Http\Controllers\Storefront\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fichiers lus par les robots
|--------------------------------------------------------------------------
|
| Hors du groupe « web » : pas de session, pas de cookie XSRF, pas de
| Cache-Control privé. Un robot n'a que faire d'une session, et les
| en-têtes qu'elle pose empêchent la mise en cache de ces fichiers.
|
*/

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/llms.txt', LlmsController::class)->name('llms');
Route::get('/robots.txt', RobotsController::class)->name('robots');
