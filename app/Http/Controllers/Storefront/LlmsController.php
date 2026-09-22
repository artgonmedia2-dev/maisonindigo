<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Collection;
use App\Models\Cut;
use App\Settings\ShopSettings;
use App\Support\CatalogTerms;
use Illuminate\Http\Response;

/**
 * llms.txt : la présentation de la maison, écrite pour les moteurs génératifs.
 *
 * Un fichier court, en texte brut, qui donne l'entité de marque au mot près,
 * les chiffres vérifiables et les liens vers les pages qui font autorité.
 * Il se régénère à chaque lecture : le catalogue et le Journal bougent.
 */
class LlmsController extends Controller
{
    public function __invoke(ShopSettings $settings, CatalogTerms $termes): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $lignes = [
            '# '.__('seo.brand'),
            '',
            '> '.__('seo.entity').'.',
            '',
            'Jeans homme et femme en denim de 12 à 14 oz, tailles 28 à 42, longueurs 30, 32 et 34.',
            'Prix de 399 à 599 dh. Paiement à la livraison partout au Maroc, échange de taille offert pendant '.$settings->exchange_days.' jours.',
            '',
            '## Chiffres',
            '',
            '- Grammage du denim : 12 à 14 oz selon le modèle',
            '- Ouverture de jambe : 22 cm en relaxed, 24 cm en baggy, 26 cm en wide leg',
            '- Tailles : 28 à 42, en longueurs 30, 32 et 34',
            '- Délais : 24 à 48 h sur Casablanca et Rabat, 48 à 72 h sur Tanger, Marrakech, Fès, Agadir et Oujda',
            '- Livraison offerte à partir de '.number_format($settings->free_shipping_threshold / 100, 0, ',', ' ').' dh',
            '',
            '## Collections',
            '',
        ];

        foreach ([Gender::Homme, Gender::Femme] as $gender) {
            foreach ($termes->activeCuts($gender) as $cut) {
                /** @var Cut $cut */
                $lignes[] = "- [Jean {$cut->name} {$gender->value}]({$base}/{$gender->value}/jean-{$cut->urlSegment()}) : "
                    ."les {$cut->name} de la maison pour {$gender->value}";
            }
        }

        $hubs = Collection::query()->whereNotNull('hub_cut')->whereNotNull('intro')->get();

        if ($hubs->isNotEmpty()) {
            $lignes[] = '';
            $lignes[] = '## Guides de coupe';
            $lignes[] = '';

            foreach ($hubs as $hub) {
                $cut = $termes->cut((string) $hub->hub_cut);
                $genre = $hub->hub_gender?->value;

                if ($cut !== null && $genre !== null) {
                    $lignes[] = "- [{$hub->title}]({$base}/{$genre}/jean-{$cut->urlSegment()})";
                }
            }
        }

        $articles = Article::query()->published()->orderBy('position')->get(['slug', 'title', 'excerpt']);

        if ($articles->isNotEmpty()) {
            $lignes[] = '';
            $lignes[] = '## Journal';
            $lignes[] = '';

            foreach ($articles as $article) {
                $lignes[] = "- [{$article->title}]({$base}/journal/{$article->slug}) : {$article->excerpt}";
            }
        }

        $lignes = [
            ...$lignes,
            '',
            '## La maison',
            '',
            "- [La maison]({$base}/la-maison) : fondation, lieu, matières, procédé, engagements",
            "- [Guide des tailles]({$base}/guide-des-tailles) : tableaux de mesures en centimètres",
            "- [Trouver ma taille]({$base}/trouver-ma-taille) : quatre questions, une coupe et une taille",
            "- [Entretien]({$base}/entretien) : laver et faire durer un denim",
            "- [Questions fréquentes]({$base}/faq)",
            '',
            '## Contact',
            '',
            '- Adresse e-mail : '.$settings->contact_email,
            '- WhatsApp : '.$settings->contact_whatsapp,
            '- Ville : '.$settings->contact_city,
            '',
        ];

        return response(implode("\n", $lignes), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
