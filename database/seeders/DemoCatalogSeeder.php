<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Enums\Gender;
use App\Enums\ProductStatus;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeChart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Catalogue de démonstration : dix références, variantes taille × longueur,
 * quelques ruptures pour vérifier l'affichage « Me prévenir », et la remise
 * automatique « deux jeans, dix pour cent ».
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->products() as $index => $definition) {
            /** @var Gender $gender */
            $gender = $definition['gender'];
            /** @var string $cut */
            $cut = $definition['cut'];
            /** @var string $wash */
            $wash = $definition['wash'];

            $title = Product::composeTitle($cut, $wash);

            /** @var Product $product */
            $product = Product::query()->updateOrCreate(
                ['gender' => $gender, 'cut' => $cut, 'wash' => $wash],
                [
                    'slug' => Str::slug("{$title} {$gender->value}"),
                    'title' => $title,
                    'description' => $definition['description'],
                    'price' => $definition['price'] * 100,
                    'compare_at_price' => null,
                    'fabric_origin' => $definition['origin'],
                    'weight_oz' => $definition['weight'],
                    'composition' => $definition['composition'],
                    'model_height_cm' => $gender === Gender::Homme ? 180 : 172,
                    'model_size' => $gender === Gender::Homme ? '32/32' : '28/32',
                    'size_advice' => 'Entre deux tailles, choisissez la plus petite : le tissu se détend légèrement.',
                    'size_chart_id' => SizeChart::query()->where('gender', $gender)->where('cut', $cut)->value('id'),
                    'status' => ProductStatus::Active,
                    'is_new' => $definition['new'],
                    'is_featured' => $definition['featured'],
                    'is_atelier' => $definition['atelier'],
                    'meta_title' => "{$title} {$gender->getLabel()} · Maison Indigo",
                    'meta_description' => Str::limit($definition['description'], 300, ''),
                ],
            );

            $this->seedVariants($product, $index);
        }

        Discount::query()->updateOrCreate(
            ['code' => null, 'type' => DiscountType::Bundle],
            [
                'name' => 'Deux jeans, dix pour cent',
                'value' => 10,
                'rules' => ['min_qty' => 2],
                'active' => true,
            ],
        );
    }

    /**
     * Tailles paires pour la démo. Quelques ruptures volontaires : une taille
     * en rupture reste visible, barrée, avec « Me prévenir ».
     */
    private function seedVariants(Product $product, int $index): void
    {
        $sizes = $product->gender === Gender::Homme ? range(28, 42, 2) : range(26, 40, 2);
        $position = 0;

        foreach ($sizes as $sizeIndex => $size) {
            foreach (ProductVariant::LENGTHS as $lengthIndex => $length) {
                $outOfStock = ($sizeIndex + $lengthIndex + $index) % 7 === 0;
                $stock = $outOfStock ? 0 : 4 + (($sizeIndex * 3 + $lengthIndex + $index) % 9);

                ProductVariant::query()->updateOrCreate(
                    ['product_id' => $product->id, 'size' => $size, 'length' => $length],
                    [
                        'sku' => ProductVariant::buildSku($product, $size, $length),
                        'stock' => $stock,
                        'low_stock_threshold' => 3,
                        'position' => $position++,
                    ],
                );
            }
        }
    }

    /**
     * @return list<array{gender: Gender, cut: string, wash: string, price: int, origin: string, weight: float, composition: string, description: string, new: bool, featured: bool, atelier: bool}>
     */
    private function products(): array
    {
        $japonais = 'Denim japonais';
        $turc = 'Denim turc';
        $italien = 'Denim italien';

        return [
            [
                'gender' => Gender::Homme, 'cut' => 'straight', 'wash' => 'brut',
                'price' => 499, 'origin' => $japonais, 'weight' => 13.0, 'composition' => '98 % coton, 2 % élasthanne',
                'description' => 'Taille mi-haute, jambe droite du genou à la cheville. Denim japonais 13 oz, teint à l’indigo naturel, qui se patine avec vous. Le modèle fait 1,80 m et porte du 32/32.',
                'new' => true, 'featured' => true, 'atelier' => false,
            ],
            [
                'gender' => Gender::Homme, 'cut' => 'slim', 'wash' => 'noir',
                'price' => 449, 'origin' => $turc, 'weight' => 12.0, 'composition' => '97 % coton, 3 % élasthanne',
                'description' => 'Jambe ajustée sans serrer, taille mi-haute. Noir profond, stable au lavage. Le modèle fait 1,80 m et porte du 32/32.',
                'new' => true, 'featured' => false, 'atelier' => false,
            ],
            [
                'gender' => Gender::Homme, 'cut' => 'regular', 'wash' => 'stone',
                'price' => 449, 'origin' => $turc, 'weight' => 12.5, 'composition' => '99 % coton, 1 % élasthanne',
                'description' => 'La coupe de tous les jours : droite, aisée aux cuisses, sans excès. Lavage stone, bleu moyen. Le modèle fait 1,80 m et porte du 32/32.',
                'new' => false, 'featured' => true, 'atelier' => false,
            ],
            [
                'gender' => Gender::Homme, 'cut' => 'tapered', 'wash' => 'gris',
                'price' => 549, 'origin' => $italien, 'weight' => 13.5, 'composition' => '100 % coton',
                'description' => 'Aisé aux cuisses, resserré vers la cheville. Gris minéral tissé en Italie, sans élasthanne : il se fait à vous. Le modèle fait 1,80 m et porte du 32/32.',
                'new' => false, 'featured' => false, 'atelier' => true,
            ],
            [
                'gender' => Gender::Homme, 'cut' => 'relaxed', 'wash' => 'clair',
                'price' => 499, 'origin' => $japonais, 'weight' => 12.0, 'composition' => '100 % coton',
                'description' => 'Large et confortable, taille haute. Bleu clair délavé à la pierre, sans traitement chimique. Le modèle fait 1,80 m et porte du 32/32.',
                'new' => false, 'featured' => false, 'atelier' => false,
            ],
            [
                'gender' => Gender::Femme, 'cut' => 'wide_leg', 'wash' => 'stone',
                'price' => 549, 'origin' => $japonais, 'weight' => 12.5, 'composition' => '100 % coton',
                'description' => 'Taille haute, jambe large de la hanche à l’ourlet. Denim japonais 12,5 oz, lavage stone. Le modèle fait 1,72 m et porte du 28/32.',
                'new' => true, 'featured' => true, 'atelier' => false,
            ],
            [
                'gender' => Gender::Femme, 'cut' => 'straight', 'wash' => 'brut',
                'price' => 499, 'origin' => $japonais, 'weight' => 13.0, 'composition' => '98 % coton, 2 % élasthanne',
                'description' => 'Taille haute, jambe droite. Indigo brut qui s’éclaircit là où vous vivez. Le modèle fait 1,72 m et porte du 28/32.',
                'new' => true, 'featured' => false, 'atelier' => false,
            ],
            [
                'gender' => Gender::Femme, 'cut' => 'mom', 'wash' => 'clair',
                'price' => 449, 'origin' => $turc, 'weight' => 12.0, 'composition' => '100 % coton',
                'description' => 'Taille très haute, hanches aisées, jambe qui se resserre. Bleu clair délavé. Le modèle fait 1,72 m et porte du 28/32.',
                'new' => false, 'featured' => true, 'atelier' => false,
            ],
            [
                'gender' => Gender::Femme, 'cut' => 'slim', 'wash' => 'noir',
                'price' => 449, 'origin' => $turc, 'weight' => 12.0, 'composition' => '96 % coton, 4 % élasthanne',
                'description' => 'Ajusté de la hanche à la cheville, taille haute. Noir stable au lavage. Le modèle fait 1,72 m et porte du 28/32.',
                'new' => false, 'featured' => false, 'atelier' => false,
            ],
            [
                'gender' => Gender::Femme, 'cut' => 'flare', 'wash' => 'ecru',
                'price' => 599, 'origin' => $italien, 'weight' => 13.0, 'composition' => '100 % coton',
                'description' => 'Ajusté jusqu’au genou, évasé jusqu’à l’ourlet. Écru tissé en Italie, édition de l’atelier en petite série. Le modèle fait 1,72 m et porte du 28/34.',
                'new' => false, 'featured' => false, 'atelier' => true,
            ],
        ];
    }
}
