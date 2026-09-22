<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\SizeQuiz\RecommendSize;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\SizeQuizRequest;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * « Trouver ma taille » : quatre étapes, une recommandation, trois produits.
 * Le résultat est gardé en session pour survivre au rechargement.
 */
class SizeQuizController extends Controller
{
    public const SESSION_KEY = 'size_quiz';

    public function show(Request $request): Response
    {
        /** @var array{answers: array<string, mixed>, result: array<string, mixed>}|null $stored */
        $stored = $request->session()->get(self::SESSION_KEY);

        $products = [];

        if ($stored !== null) {
            $products = ProductCardResource::collection(
                $this->matchingProducts(Gender::from((string) $stored['result']['gender']), (string) $stored['result']['cut'], (int) $stored['result']['size'], (int) $stored['result']['length'])
            )->toArray($request);
        }

        return Inertia::render('SizeQuiz/Index', [
            'meta' => [
                'title' => __('storefront.size_quiz.title'),
                'description' => __('storefront.size_quiz.description'),
            ],
            'answers' => $stored['answers'] ?? null,
            'result' => $stored['result'] ?? null,
            'products' => $products,
            'options' => [
                'genders' => array_map(fn (Gender $gender): array => ['value' => $gender->value, 'label' => $gender->getLabel()], Gender::cases()),
                'hips' => array_map(fn (string $hips): array => ['value' => $hips, 'label' => __("storefront.size_quiz.hips.$hips")], RecommendSize::HIPS),
                'fits' => array_map(fn (string $fit): array => ['value' => $fit, 'label' => __("storefront.size_quiz.fit.$fit"), 'description' => __("storefront.size_quiz.fit_description.$fit")], RecommendSize::FITS),
            ],
        ]);
    }

    public function store(SizeQuizRequest $request, RecommendSize $recommend): RedirectResponse
    {
        $validated = $request->validated();

        $result = $recommend->handle(
            Gender::from((string) $validated['gender']),
            (int) $validated['waist_cm'],
            (int) $validated['height_cm'],
            (string) $validated['hips'],
            (string) $validated['fit'],
        );

        $request->session()->put(self::SESSION_KEY, [
            'answers' => $validated,
            'result' => $result->toArray(),
        ]);

        return redirect()->route('size-quiz');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('size-quiz');
    }

    /**
     * Trois produits actifs de la coupe recommandée, en stock dans la taille conseillée en priorité.
     *
     * @return Collection<int, Product>
     */
    private function matchingProducts(Gender $gender, string $cut, int $size, int $length): Collection
    {
        $inSize = fn (Builder $variants) => $variants->where('size', $size)->where('length', $length)->where('stock', '>', 0);

        $products = Product::query()
            ->active()
            ->where('gender', $gender)
            ->where('cut', $cut)
            ->with(['variants', 'media'])
            ->orderByDesc('is_new')
            ->get()
            ->sortByDesc(fn (Product $product): int => $product->variants->contains(fn ($variant): bool => $variant->size === $size && $variant->length === $length && $variant->stock > 0) ? 1 : 0)
            ->values();

        if ($products->count() < 3) {
            $more = Product::query()
                ->active()
                ->where('gender', $gender)
                ->whereNotIn('id', $products->pluck('id'))
                ->whereHas('variants', $inSize)
                ->with(['variants', 'media'])
                ->orderByDesc('is_featured')
                ->limit(3 - $products->count())
                ->get();

            $products = $products->concat($more);
        }

        return $products->take(3)->values();
    }
}
