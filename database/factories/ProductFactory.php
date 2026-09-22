<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CatalogTerms;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        // Le trio genre x coupe x lavage est unique en base : on écarte les
        // combinaisons déjà présentes (catalogue de démonstration, autres fabriques).
        $taken = Product::query()
            ->get(['gender', 'cut', 'wash'])
            ->map(fn (Product $product): string => "{$product->gender->value}|{$product->cut}|{$product->wash}")
            ->all();

        $available = array_values(array_diff(self::combinations(), $taken));

        if ($available === []) {
            $available = self::combinations();
        }

        /** @var string $combination */
        $combination = fake()->unique()->randomElement($available);
        [$genderValue, $cutValue, $washValue] = explode('|', $combination);

        $gender = Gender::from($genderValue);
        $title = Product::composeTitle($cutValue, $washValue);
        $priceDh = fake()->randomElement([399, 449, 499, 549, 599]);

        return [
            'slug' => Str::slug("{$title} {$gender->value}"),
            'title' => $title,
            'gender' => $gender,
            'cut' => $cutValue,
            'wash' => $washValue,
            'description' => 'Taille mi-haute, jambe droite du genou à la cheville. Entre deux tailles, choisissez la plus petite.',
            'price' => $priceDh * 100,
            'compare_at_price' => null,
            'fabric_origin' => fake()->randomElement(['Denim japonais', 'Denim turc', 'Denim italien']),
            'weight_oz' => fake()->randomElement([12.0, 12.5, 13.0, 13.5, 14.0]),
            'composition' => '98 % coton, 2 % élasthanne',
            'model_height_cm' => $gender === Gender::Homme ? 180 : 172,
            'model_size' => $gender === Gender::Homme ? '32/32' : '28/32',
            'size_advice' => 'Entre deux tailles, choisissez la plus petite : le tissu se détend légèrement.',
            'size_chart_id' => null,
            'status' => ProductStatus::Active,
            'is_new' => false,
            'is_featured' => false,
            'is_atelier' => false,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    /**
     * Le titre « {Coupe} {Lavage} » et le slug suivent toujours les attributs finaux,
     * y compris quand un test force le genre, la coupe ou le lavage.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Product $product): void {
            $product->title = Product::composeTitle($product->cut, $product->wash);
            $product->slug = Str::slug("{$product->title} {$product->gender->value}");
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ProductStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ProductStatus::Archived]);
    }

    public function isNew(): static
    {
        return $this->state(fn (array $attributes) => ['is_new' => true]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }

    public function atelier(): static
    {
        return $this->state(fn (array $attributes) => ['is_atelier' => true]);
    }

    /**
     * Crée toutes les variantes taille × longueur (tailles paires par défaut).
     *
     * @param  list<int>|null  $sizes
     */
    public function withVariants(?array $sizes = null, int $stock = 10): static
    {
        $sizes ??= range(26, 42, 2);

        return $this->afterCreating(function (Product $product) use ($sizes, $stock): void {
            $position = 0;

            foreach ($sizes as $size) {
                foreach (ProductVariant::LENGTHS as $length) {
                    ProductVariant::factory()->for($product)->create([
                        'size' => $size,
                        'length' => $length,
                        'sku' => ProductVariant::buildSku($product, $size, $length),
                        'stock' => $stock,
                        'position' => $position++,
                    ]);
                }
            }
        });
    }

    /**
     * Toutes les combinaisons genre × coupe × lavage autorisées.
     *
     * @return list<string>
     */
    private static function combinations(): array
    {
        $combinations = [];

        $termes = app(CatalogTerms::class);
        $washes = $termes->activeWashes();

        foreach (Gender::cases() as $gender) {
            foreach ($termes->activeCuts($gender) as $cut) {
                foreach ($washes as $wash) {
                    $combinations[] = "{$gender->value}|{$cut->slug}|{$wash->slug}";
                }
            }
        }

        return $combinations;
    }
}
