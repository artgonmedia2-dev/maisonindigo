<?php

namespace Database\Seeders;

use App\Enums\CollectionType;
use App\Enums\Gender;
use App\Models\Article;
use App\Models\Author;
use App\Models\Collection;
use App\Models\CollectionWash;
use Illuminate\Database\Seeder;

/**
 * Le cluster « jean baggy homme » : hub, sous-collections lavage, Journal.
 *
 * Contenu repris des sections 3, 4 et 6 de docs/MaisonIndigo_SEO_Baggy_Homme.md,
 * écrit dans la voix de la maison. Le corps des articles reste un plan de
 * sections à compléter dans le back-office : la structure et la réponse
 * directe, elles, sont livrées.
 *
 * Rejouable : tout passe par updateOrCreate, rien n'est dupliqué.
 */
class SeoClusterSeeder extends Seeder
{
    public function run(): void
    {
        $hub = $this->hub();
        $this->washPages($hub);
        $this->journal();
    }

    private function hub(): Collection
    {
        /** @var Collection $hub */
        $hub = Collection::query()->updateOrCreate(
            ['hub_gender' => Gender::Homme, 'hub_cut' => 'baggy'],
            [
                'slug' => 'jean-baggy-homme',
                'title' => 'Jean baggy homme',
                'description' => 'Les baggys de la maison, en denim 12 à 14 oz.',
                'type' => CollectionType::Rule,
                'rules' => ['gender' => 'homme', 'cut' => 'baggy'],
                'position' => 1,
                'is_visible' => true,
                'meta_title' => 'Jean baggy homme premium — Maison Indigo Maroc',
                'meta_description' => 'Jeans baggy homme en denim 12–14 oz, tailles 28 à 42, 3 longueurs. Échange offert, paiement à la livraison partout au Maroc.',
                'intro' => 'Le baggy de Maison Indigo tombe ample de la cuisse à la cheville, sur une taille mi-haute, '
                    ."avec une ouverture de jambe de 24 cm. C'est un jean large, pas un jean informe : la toile tient "
                    ."la ligne parce qu'elle pèse entre 12 et 14 oz et qu'elle ne contient pas d'élasthanne. "
                    .'Nous le tissons au Japon et en Turquie, et le proposons du 28 au 42, en longueurs 30, 32 et 34. '
                    .'Livraison partout au Maroc, paiement à la livraison, échange offert pendant 14 jours.',
                'content_blocks' => $this->hubBlocks(),
                'faq' => $this->hubFaq(),
            ],
        );

        return $hub;
    }

    /**
     * Les six sections H2 de la section 3 du document.
     *
     * @return list<array{title: string, body: string}>
     */
    private function hubBlocks(): array
    {
        return [
            [
                'title' => 'Comment tombe notre baggy',
                'body' => 'La taille se porte mi-haute, à deux doigts du nombril. La cuisse est ample sans être '
                    ."flottante, et la jambe descend droite jusqu'à une ouverture de 24 cm. À titre de comparaison, "
                    .'notre wide leg ouvre à 26 cm et évase à partir du genou ; le relaxed ouvre à 22 cm et reste '
                    .'plus proche de la jambe. Le baggy se situe entre les deux : le volume du wide leg, la ligne '
                    .'droite du straight.',
            ],
            [
                'title' => 'Quel lavage choisir',
                'body' => "L'indigo brut part très foncé et s'éclaircit là où vous vivez, aux genoux et aux poches. "
                    .'Le bleu stone est lavé à la pierre : il est portable dès le premier jour. Le noir est teint '
                    ."dans la masse et garde sa profondeur si vous le lavez à l'envers, à 30 degrés. Le clair a été "
                    .'délavé sans traitement chimique, pour les mois chauds.',
            ],
            [
                'title' => 'Trouver sa taille en baggy',
                'body' => 'Prenez votre taille habituelle : le volume est dans la coupe, pas dans la taille. '
                    ."Entre deux tailles, choisissez la plus petite, la toile se détend d'un demi-centimètre à la "
                    .'ceinture après quelques jours. Pour la longueur, comptez 30 en dessous de 1,70 m, 32 entre '
                    .'1,70 m et 1,80 m, 34 au-delà. Le quiz vous donne les trois en quatre questions.',
            ],
            [
                'title' => 'Avec quoi porter un jean baggy',
                'body' => 'Avec un t-shirt ajusté rentré dans la taille : le volume du bas répond à une ligne nette '
                    .'en haut. Avec une chemise en denim plus claire, ouverte sur un t-shirt uni. Avec une veste '
                    ."courte qui s'arrête à la ceinture, pour ne pas couper la silhouette en deux parties larges.",
            ],
            [
                'title' => 'Denim et fabrication',
                'body' => 'Nos toiles viennent du Japon, de Turquie et d\'Italie, entre 12 et 14 oz. Les baggys sont '
                    .'coupés sans élasthanne, en 100 % coton : ils se font à votre morphologie et gardent leur forme. '
                    .'Coutures rabattues aux entrejambes, rivets aux poches avant, boutonnière renforcée. '
                    .'Maison Indigo est une marque marocaine de jeans premium basée à Nador.',
            ],
            [
                'title' => 'Livraison et échange au Maroc',
                'body' => 'Casablanca, Rabat et Mohammedia sous 24 à 48 heures. Tanger, Marrakech, Fès, Agadir et '
                    .'Oujda sous 48 à 72 heures. Le reste du royaume sous 72 heures. Vous payez au livreur, '
                    .'en espèces. Si la taille ne convient pas, nous vous envoyons la bonne et vous remettez '
                    .'la première au livreur, sans frais, pendant 14 jours.',
            ],
        ];
    }

    /**
     * Les huit questions de la section 3, formulées comme on les pose.
     *
     * @return list<array{question: string, answer: string}>
     */
    private function hubFaq(): array
    {
        return [
            [
                'question' => 'Quelle est la différence entre un jean baggy et un jean large ?',
                'answer' => '« Jean large » désigne toutes les coupes amples. Le baggy en est une : ample de la '
                    .'cuisse à la cheville, ouverture de jambe de 24 cm, ligne droite. Le wide leg, lui, évase '
                    .'à partir du genou et ouvre à 26 cm.',
            ],
            [
                'question' => 'Un jean baggy homme taille grand ou petit ?',
                'answer' => 'Il taille normalement. Le volume vient de la coupe, pas de la taille : prenez votre '
                    .'taille habituelle. Entre deux tailles, choisissez la plus petite.',
            ],
            [
                'question' => 'Quelle longueur choisir pour un baggy ?',
                'answer' => 'Longueur 30 en dessous de 1,70 m, 32 entre 1,70 m et 1,80 m, 34 au-delà. '
                    .'Un baggy doit effleurer la chaussure, pas traîner au sol.',
            ],
            [
                'question' => 'Comment laver un baggy en denim brut ?',
                'answer' => 'Attendez deux à trois mois avant le premier lavage, le temps que le délavage se fasse '
                    .'là où vous bougez. Ensuite, à 30 degrés, à l\'envers, sans adoucissant, séchage à l\'air libre.',
            ],
            [
                'question' => 'Livrez-vous à Casablanca, Rabat, Tanger, Marrakech, Agadir et Oujda ?',
                'answer' => 'Oui, et partout ailleurs au Maroc. Comptez 24 à 48 heures sur Casablanca, Rabat et '
                    .'Mohammedia, 48 à 72 heures sur Tanger, Marrakech, Fès, Agadir et Oujda, 72 heures ailleurs.',
            ],
            [
                'question' => 'Peut-on payer à la livraison ?',
                'answer' => 'Oui. Vous réglez au livreur, en espèces, au moment où vous recevez le colis. '
                    .'Le virement bancaire est également possible.',
            ],
            [
                'question' => 'Peut-on échanger la taille ?',
                'answer' => 'Oui, pendant 14 jours et sans frais. Nous vous envoyons la nouvelle taille et vous '
                    .'remettez la première au livreur.',
            ],
            [
                'question' => 'Combien coûte un jean baggy homme de qualité au Maroc ?',
                'answer' => 'Entre 450 et 600 dh pour une toile de 12 à 14 oz coupée sans élasthanne. '
                    .'En dessous de 300 dh, la toile descend sous 10 oz : elle se déforme aux genoux en quelques '
                    .'semaines. Nos baggys sont à 499 et 549 dh.',
            ],
        ];
    }

    /**
     * Les quatre sous-collections lavage de la section 4.
     */
    private function washPages(Collection $hub): void
    {
        $pages = [
            'noir' => [
                'intro' => 'Le baggy noir est teint dans la masse, pas en surface : il garde sa profondeur au fil '
                    ."des lavages au lieu de virer au gris. C'est le lavage qui se porte le plus facilement le soir, "
                    .'avec une chemise claire ou un t-shirt blanc. Ouverture de jambe de 24 cm, denim 13 oz.',
                'faq' => [
                    [
                        'question' => 'Le baggy noir déteint-il ?',
                        'answer' => 'Les deux premiers lavages libèrent un peu de teinture. Lavez-le séparément '
                            .'la première fois, à 30 degrés et à l\'envers.',
                    ],
                    [
                        'question' => 'Comment garder un jean noir vraiment noir ?',
                        'answer' => 'Lavez-le à l\'envers, à 30 degrés, sans adoucissant, et faites-le sécher à '
                            ."l'ombre. Le soleil direct est ce qui délave le plus vite un noir.",
                    ],
                    [
                        'question' => 'Avec quoi porter un baggy noir ?',
                        'answer' => 'Avec un haut clair pour éviter le total look sombre, et des chaussures '
                            .'basses qui laissent voir l\'ourlet.',
                    ],
                ],
            ],
            'stone' => [
                'intro' => 'Le bleu moyen de la maison, lavé à la pierre. Il est portable dès le premier jour, sans '
                    .'la période de rodage du denim brut. Ouverture de jambe de 24 cm, denim 12,5 oz tissé en Turquie.',
                'faq' => [
                    [
                        'question' => 'Le bleu stone va-t-il encore s\'éclaircir ?',
                        'answer' => 'Très peu. Le lavage à la pierre a déjà fait l\'essentiel du travail : '
                            .'la couleur bouge de moins d\'un ton en un an.',
                    ],
                    [
                        'question' => 'Est-ce le bleu classique du jean ?',
                        'answer' => 'Oui, c\'est le bleu moyen que l\'on imagine en disant « un jean bleu ». '
                            .'Plus clair que le brut, plus soutenu que le délavé.',
                    ],
                    [
                        'question' => 'Se porte-t-il en toute saison ?',
                        'answer' => 'Oui. À 12,5 oz, il reste respirant en été et suffisamment dense en hiver.',
                    ],
                ],
            ],
            'brut' => [
                'intro' => 'Le denim brut part très foncé et se patine avec vous : les genoux, les poches et les '
                    ."plis de la cheville s'éclaircissent au fil des mois. C'est le lavage qui raconte votre façon "
                    .'de bouger. Denim japonais 13 oz, 100 % coton, ouverture de jambe de 24 cm.',
                'faq' => [
                    [
                        'question' => 'Combien de temps avant le premier lavage ?',
                        'answer' => 'Deux à trois mois de port régulier. Plus vous attendez, plus les contrastes '
                            .'du délavage seront marqués.',
                    ],
                    [
                        'question' => 'Le denim brut tache-t-il les vêtements clairs ?',
                        'answer' => 'Les premières semaines, un peu, surtout sur du blanc et sur du cuir clair. '
                            .'Cela cesse après le premier lavage.',
                    ],
                    [
                        'question' => 'Le brut est-il raide au début ?',
                        'answer' => 'Oui, quelques jours. Sans élasthanne, la toile s\'assouplit en prenant la '
                            .'forme de votre morphologie.',
                    ],
                ],
            ],
            'clair' => [
                'intro' => 'Un bleu clair délavé à la pierre, sans traitement chimique. C\'est le baggy des mois '
                    .'chauds : la toile est plus souple et la couleur allège la silhouette. '
                    .'Ouverture de jambe de 24 cm.',
                'faq' => [
                    [
                        'question' => 'Un jean clair marque-t-il davantage ?',
                        'answer' => 'Oui, les taches se voient plus. Un lavage à 30 degrés suffit dans la plupart '
                            .'des cas.',
                    ],
                    [
                        'question' => 'Avec quoi porter un baggy clair ?',
                        'answer' => 'Avec un haut plus soutenu — bleu marine, kaki, blanc cassé — pour équilibrer '
                            .'la silhouette.',
                    ],
                    [
                        'question' => 'Est-il moins chaud que le brut ?',
                        'answer' => 'Il est plus souple au toucher, mais le grammage reste proche. '
                            .'La différence tient surtout à la couleur.',
                    ],
                ],
            ],
        ];

        $position = 0;

        foreach ($pages as $wash => $contenu) {
            CollectionWash::query()->updateOrCreate(
                ['collection_id' => $hub->id, 'wash' => $wash],
                [
                    'intro' => $contenu['intro'],
                    'faq' => $contenu['faq'],
                    'is_visible' => true,
                    'position' => $position += 10,
                ],
            );
        }
    }

    /**
     * Les six articles de la section 6 : « En bref » rédigé, plan H2 complet,
     * corps à compléter dans le back-office.
     */
    private function journal(): void
    {
        /** @var Author $author */
        $author = Author::query()->updateOrCreate(
            ['slug' => 'atelier-maison-indigo'],
            [
                'name' => 'L’atelier Maison Indigo',
                'role' => 'Coupes, tailles et denim',
                'bio' => 'Nous coupons, mesurons et portons nos jeans avant de les vendre. Ce que vous lisez ici '
                    .'vient de l’atelier de Nador, pas d’une fiche produit.',
            ],
        );

        $articles = $this->articles();

        foreach ($articles as $rang => $article) {
            Article::query()->updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content_blocks' => array_map(
                        fn (string $titre): array => ['title' => $titre, 'body' => ''],
                        $article['plan'],
                    ),
                    'faq' => $article['faq'],
                    'author_id' => $author->id,
                    // Le premier de la liste est le plus récent.
                    'published_at' => now()->subDays($rang + 1),
                    'position' => ($rang + 1) * 10,
                ],
            );
        }
    }

    /**
     * @return list<array{slug: string, title: string, excerpt: string, plan: list<string>, faq: list<array{question: string, answer: string}>}>
     */
    private function articles(): array
    {
        return [
            [
                'slug' => 'comment-porter-jean-baggy-homme',
                'title' => 'Comment porter un jean baggy homme : 5 silhouettes qui marchent',
                'excerpt' => 'Un baggy se porte avec une ligne nette en haut : t-shirt ajusté rentré dans la taille, '
                    .'chemise ouverte sur un uni, veste courte qui s’arrête à la ceinture. '
                    .'Évitez le volume des deux côtés à la fois.',
                'plan' => [
                    'Baggy et t-shirt ajusté',
                    'Baggy et chemise en denim',
                    'Baggy et veste courte',
                    'Baggy et maille fine',
                    'Baggy et chaussures : ce qui change tout',
                    'Les erreurs à éviter',
                    'Quelle taille pour que le baggy tombe bien',
                ],
                'faq' => [
                    ['question' => 'Un baggy se porte-t-il avec des baskets montantes ?', 'answer' => 'Oui, à condition que l’ourlet tombe au-dessus de la tige, sinon la jambe paraît écrasée.'],
                    ['question' => 'Faut-il rentrer son t-shirt dans un baggy ?', 'answer' => 'C’est ce qui marque la taille et équilibre le volume du bas. Sinon, choisissez un haut court.'],
                    ['question' => 'Un baggy convient-il aux hommes petits ?', 'answer' => 'Oui, avec une longueur 30 et une taille mi-haute qui allonge la jambe.'],
                    ['question' => 'Peut-on porter un baggy au bureau ?', 'answer' => 'En indigo brut ou en noir, avec une chemise et des chaussures basses, oui.'],
                ],
            ],
            [
                'slug' => 'baggy-wide-leg-relaxed-differences',
                'title' => 'Baggy, wide leg, relaxed : les différences, en centimètres',
                'excerpt' => 'Le baggy ouvre à 24 cm et descend droit, le wide leg ouvre à 26 cm en évasant depuis '
                    .'le genou, le relaxed ouvre à 22 cm et reste proche de la jambe. '
                    .'Trois volumes, trois morphologies.',
                'plan' => [
                    'Les trois coupes en un tableau',
                    'Le baggy : ample et droit',
                    'Le wide leg : évasé depuis le genou',
                    'Le relaxed : de l’aisance, sans volume',
                    'Quelle coupe pour quelle morphologie',
                    'Comment mesurer l’ouverture de jambe chez vous',
                ],
                'faq' => [
                    ['question' => 'Baggy et loose, est-ce la même chose ?', 'answer' => 'Oui, « loose » est le mot anglais employé pour la même idée : un jean ample et droit.'],
                    ['question' => 'Quelle coupe si j’ai les cuisses larges ?', 'answer' => 'Le relaxed ou le baggy : tous deux laissent de la place à la cuisse sans créer de volume à la cheville.'],
                    ['question' => 'Le wide leg est-il un jean féminin ?', 'answer' => 'Non, il existe dans les deux collections. La différence tient à la hauteur de taille.'],
                    ['question' => 'Peut-on retoucher un baggy trop long ?', 'answer' => 'Oui, mais faites conserver l’ourlet d’origine si la toile est brute : le délavage y est différent.'],
                ],
            ],
            [
                'slug' => 'quelle-taille-jean-baggy-homme',
                'title' => 'Quelle taille pour un jean baggy homme',
                'excerpt' => 'Prenez votre taille habituelle : le volume vient de la coupe, pas de la taille. '
                    .'Entre deux tailles, choisissez la plus petite. Pour la longueur, comptez 30 en dessous de '
                    .'1,70 m, 32 jusqu’à 1,80 m, 34 au-delà.',
                'plan' => [
                    'Mesurer son tour de taille en deux minutes',
                    'Le tableau des tailles, du 28 au 42',
                    'Taille haute, mi-haute, basse : ce que ça change',
                    'Choisir sa longueur : 30, 32 ou 34',
                    'Entre deux tailles : la règle de la maison',
                    'Ce que fait la toile après trois semaines',
                ],
                'faq' => [
                    ['question' => 'Un baggy taille-t-il grand ?', 'answer' => 'Non. Il tombe ample par construction, mais la ceinture correspond à votre taille habituelle.'],
                    ['question' => 'Le baggy se porte-t-il en taille haute ou basse ?', 'answer' => 'Chez nous, mi-haute : à deux doigts du nombril. C’est ce qui tient le volume en place.'],
                    ['question' => 'Que faire si je suis entre deux tailles ?', 'answer' => 'Prenez la plus petite. La toile se détend d’environ un demi-centimètre à la ceinture.'],
                    ['question' => 'Puis-je échanger si je me trompe ?', 'answer' => 'Oui, pendant 14 jours et sans frais, partout au Maroc.'],
                ],
            ],
            [
                'slug' => 'jean-baggy-homme-prix-maroc',
                'title' => 'Jean baggy homme : quel prix au Maroc, et que vaut un baggy à 300 dh',
                'excerpt' => 'Au Maroc, un baggy se trouve entre 150 et 1 200 dh. En dessous de 300 dh, la toile '
                    .'descend sous 10 oz et se déforme aux genoux en quelques semaines. '
                    .'Entre 450 et 600 dh, vous achetez une toile de 12 à 14 oz qui tient plusieurs années.',
                'plan' => [
                    'La grille de prix, de 150 à 1 200 dh',
                    'Ce que change le grammage de la toile',
                    'Ce que change la coupe et le montage',
                    'Quand « pas cher » finit par coûter cher',
                    'Comment reconnaître une bonne toile en magasin',
                    'Ce que coûte un baggy Maison Indigo, et pourquoi',
                ],
                'faq' => [
                    ['question' => 'Existe-t-il des baggys corrects à moins de 300 dh ?', 'answer' => 'Rarement. À ce prix, la toile pèse moins de 10 oz et contient souvent de l’élasthanne, qui se relâche.'],
                    ['question' => 'Pourquoi un jean premium coûte-t-il plus de 450 dh ?', 'answer' => 'La toile représente l’essentiel du coût : une toile japonaise 13 oz coûte trois fois une toile légère standard.'],
                    ['question' => 'Combien de temps dure un baggy à 500 dh ?', 'answer' => 'Trois à cinq ans en port régulier, si vous le lavez peu et à froid.'],
                    ['question' => 'Les soldes valent-elles le coup sur un jean ?', 'answer' => 'Sur une bonne toile, oui. Sur une toile légère, le prix bas ne compense pas la durée de vie.'],
                ],
            ],
            [
                'slug' => 'levis-baggy-homme-maroc-alternatives',
                'title' => 'Levi’s baggy homme au Maroc : modèles, prix constatés et alternatives',
                'excerpt' => 'Levi’s propose plusieurs coupes amples pour homme, autour de 700 à 1 100 dh au Maroc '
                    .'selon le modèle et le point de vente. Les alternatives locales se situent entre 450 et 600 dh '
                    .'pour un grammage équivalent.',
                'plan' => [
                    'Les coupes amples Levi’s pour homme',
                    'Prix constatés au Maroc',
                    'Où les trouver : boutiques et sites',
                    'Comparatif : grammage, coupe, tailles disponibles',
                    'Si vous cherchez un baggy premium fabriqué pour le Maroc',
                ],
                'faq' => [
                    ['question' => 'Les tailles Levi’s correspondent-elles aux nôtres ?', 'answer' => 'Les deux suivent la taille US en pouces. Vérifiez surtout l’ouverture de jambe, qui varie d’un modèle à l’autre.'],
                    ['question' => 'Levi’s fabrique-t-il au Maroc ?', 'answer' => 'Non, la production est internationale. Nos toiles sont coupées et montées à Nador.'],
                    ['question' => 'Quelle différence de grammage ?', 'answer' => 'Les coupes amples Levi’s se situent souvent entre 11 et 13 oz. Nos baggys sont entre 12,5 et 13 oz.'],
                    ['question' => 'Peut-on essayer avant d’acheter ?', 'answer' => 'Chez nous, le paiement se fait à la livraison et l’échange de taille est offert pendant 14 jours.'],
                ],
            ],
            [
                'slug' => 'entretien-jean-baggy-denim-brut',
                'title' => 'Entretien du denim brut : laver son baggy sans l’abîmer',
                'excerpt' => 'Attendez deux à trois mois avant le premier lavage, puis lavez à 30 degrés, à l’envers, '
                    .'sans adoucissant, et faites sécher à l’air libre. C’est ce qui donne au denim brut ses '
                    .'contrastes de délavage.',
                'plan' => [
                    'Pourquoi attendre avant le premier lavage',
                    'Le premier lavage, étape par étape',
                    'À quelle fréquence laver ensuite',
                    'Sécher sans déformer',
                    'Aérer, détacher, ranger',
                    'Ce qu’il ne faut jamais faire',
                ],
                'faq' => [
                    ['question' => 'Peut-on mettre un jean brut au congélateur ?', 'answer' => 'Cela ne désinfecte pas. Aérer une nuit à l’extérieur est plus efficace.'],
                    ['question' => 'À quelle fréquence laver un jean brut ?', 'answer' => 'Toutes les dix à quinze sorties, une fois le premier lavage passé.'],
                    ['question' => 'Le sèche-linge abîme-t-il le denim ?', 'answer' => 'Oui, il rétrécit la toile et fatigue les coutures. Séchez à plat ou sur cintre.'],
                    ['question' => 'Comment enlever une tache sans laver tout le jean ?', 'answer' => 'Tamponnez à l’eau froide avec un linge propre, sans frotter, et laissez sécher à l’air.'],
                ],
            ],
        ];
    }
}
