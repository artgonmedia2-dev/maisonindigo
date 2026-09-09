<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Sprint 1 : nouveautés et collections mises en avant.
        return Inertia::render('Home', [
            'meta' => [
                'title' => null,
                'description' => __('storefront.meta.home_description'),
            ],
        ]);
    }
}
