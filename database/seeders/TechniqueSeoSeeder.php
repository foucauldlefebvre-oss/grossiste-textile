<?php

namespace Database\Seeders;

use App\Models\MarkingTechnique;
use Illuminate\Database\Seeder;

class TechniqueSeoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'broderie' => [
                'seo_url' => 'broderie-textile',
                'meta_title' => 'Broderie textile professionnelle - Personnalisation haut de gamme',
                'meta_description' => 'Broderie textile professionnelle pour vetements et accessoires. Rendu haut de gamme, durable et resistant aux lavages. Devis gratuit sous 24h.',
                'seo_content' => '<h2>La broderie textile : le marquage haut de gamme par excellence</h2>
<p>La <strong>broderie textile</strong> est la technique de personnalisation la plus qualitative pour vos vetements professionnels. Realisee par des machines a broder numeriques multi-tetes, elle offre un rendu premium avec un relief et une brillance inegalee.</p>

<h3>Pourquoi choisir la broderie pour vos textiles ?</h3>
<p>La broderie se distingue par sa <strong>durabilite exceptionnelle</strong> : elle resiste a des centaines de lavages industriels sans alteration. C\'est la technique privilegiee pour les <strong>polos d\'entreprise</strong>, les <strong>chemises brodees</strong>, les <strong>softshells personnalisees</strong> et les <strong>vetements de travail haut de gamme</strong>.</p>

<h3>Formats et tarifs</h3>
<p>Nous proposons 4 formats de broderie adaptes a chaque usage :</p>
<ul>
<li><strong>Format A7</strong> (7,4 x 10,5 cm) : ideal pour les logos poitrine</li>
<li><strong>Format A6</strong> (10,5 x 14,8 cm) : parfait pour les blasons et ecussons</li>
<li><strong>Format A5</strong> (14,8 x 21 cm) : pour les marquages dos de taille moyenne</li>
<li><strong>Format A4</strong> (21 x 29,7 cm) : pour les grands visuels dos</li>
</ul>
<p>Nos tarifs sont degressifs a partir de <strong>20 pieces</strong>. Plus votre commande est importante, plus le prix unitaire diminue. Demandez un devis pour recevoir un tarif personnalise.</p>

<h3>Sur quels textiles broder ?</h3>
<p>La broderie est compatible avec la majorite des textiles : coton, polyester, polaire, softshell, nylon et melanges. Elle est particulierement recommandee sur les textiles epais et structures qui mettent en valeur le relief du fil.</p>',
            ],
            'serigraphie' => [
                'seo_url' => 'serigraphie-textile',
                'meta_title' => 'Serigraphie textile - Impression grand volume a prix degressif',
                'meta_description' => 'Serigraphie textile professionnelle pour t-shirts, sweats et vetements. Technique ideale pour les grandes series. Tarifs degressifs des 20 pieces.',
                'seo_content' => '<h2>La serigraphie textile : le meilleur rapport qualite-prix en volume</h2>
<p>La <strong>serigraphie textile</strong> (ou impression en serigraghie) est la technique de marquage la plus repandue pour les <strong>grandes series de t-shirts</strong>, sweats et vetements promotionnels. Chaque couleur est imprimee separement via un ecran de soie, offrant un rendu opaque et vibrant.</p>

<h3>Avantages de la serigraphie</h3>
<ul>
<li><strong>Prix imbattable en volume</strong> : plus la quantite augmente, plus le prix unitaire baisse</li>
<li><strong>Rendu opaque</strong> : ideal sur textiles clairs ET fonces</li>
<li><strong>Durabilite</strong> : excellente tenue au lavage</li>
<li><strong>Couleurs Pantone</strong> : reproduction fidele de vos couleurs de marque</li>
</ul>

<h3>Serigraphie sur textile clair vs fonce</h3>
<p>Le choix du textile influence le prix. Sur <strong>textile clair</strong> (blanc, gris clair, jaune), l\'impression est directe. Sur <strong>textile fonce</strong> (noir, marine, rouge), une sous-couche blanche est necessaire, ce qui impacte legerement le tarif.</p>

<h3>Pour quels projets ?</h3>
<p>La serigraphie est ideale pour les <strong>evenements</strong>, les <strong>associations</strong>, les <strong>equipes sportives</strong>, les <strong>festivals</strong> et toute commande a partir de 20 pieces. C\'est la technique la plus economique pour les visuels de 1 a 3 couleurs en grande quantite.</p>',
            ],
            'dtf' => [
                'seo_url' => 'transfert-dtf',
                'meta_title' => 'Transfert DTF - Impression textile quadri haute definition',
                'meta_description' => 'Transfert DTF (Direct to Film) pour impression textile quadri. Photos, degrades et visuels complexes sur tous textiles. Devis en ligne.',
                'seo_content' => '<h2>Le transfert DTF : l\'impression quadri accessible</h2>
<p>Le <strong>transfert DTF</strong> (Direct to Film) est une technique d\'impression recente qui permet de reproduire des <strong>visuels en quadrichromie</strong> (photos, degrades, logos complexes) sur quasiment tous les types de textiles.</p>

<h3>Comment fonctionne le DTF ?</h3>
<p>Le visuel est imprime en haute resolution sur un film special, puis recouvert d\'une poudre adhesive thermofusible. Le transfert est ensuite presse a chaud sur le textile. Le resultat : une impression <strong>photo-realiste</strong> avec des couleurs eclatantes.</p>

<h3>Avantages du DTF</h3>
<ul>
<li><strong>Quadrichromie</strong> : photos, degrades, effets de couleur sans limite</li>
<li><strong>Tous textiles</strong> : coton, polyester, melanges, clair ou fonce</li>
<li><strong>Petites et moyennes series</strong> : rentable des 10 pieces</li>
<li><strong>Toucher souple</strong> : film fin et flexible</li>
</ul>

<h3>Formats disponibles</h3>
<p>Du format A7 (logo poitrine) au format A3 (grand dos), nos tarifs sont degressifs et adaptes a chaque taille de visuel.</p>',
            ],
            'transfert-offset' => [
                'seo_url' => 'transfert-offset',
                'meta_title' => 'Transfert Offset - Impression textile quadri premium',
                'meta_description' => 'Transfert offset pour impression textile quadrichromie premium. Rendu professionnel, couleurs fideles. Ideal pour visuels detailles.',
                'seo_content' => '<h2>Le transfert offset : la qualite premium en quadrichromie</h2>
<p>Le <strong>transfert offset textile</strong> est une technique d\'impression quadri qui offre un rendu <strong>tres haute qualite</strong> avec une fidelite des couleurs exceptionnelle. Utilise pour les visuels complexes necessitant une reproduction parfaite.</p>

<h3>Offset vs DTF : quelles differences ?</h3>
<p>Le transfert offset offre une <strong>precision superieure</strong> dans les details et une meilleure fidelite colorimetrique que le DTF. Il est particulierement adapte aux <strong>logos avec des degrades fins</strong>, aux <strong>photos haute definition</strong> et aux projets ou la qualite d\'impression est primordiale.</p>

<h3>Quand choisir le transfert offset ?</h3>
<ul>
<li>Visuels avec beaucoup de details fins</li>
<li>Reproduction photographique exigeante</li>
<li>Projets corporate necessitant une fidelite colorimetrique Pantone</li>
<li>Quantites moyennes a importantes (a partir de 50 pieces)</li>
</ul>',
            ],
            'impression-dtg' => [
                'seo_url' => 'impression-dtg',
                'meta_title' => 'Impression DTG - Impression directe sur textile',
                'meta_description' => 'Impression DTG (Direct to Garment) pour personnalisation textile. Impression directe sans transfert, ideal petites series et prototypes.',
            ],
            'flocage-flex' => [
                'seo_url' => 'flocage-flex',
                'meta_title' => 'Flocage et flex - Marquage textile en decoupe',
                'meta_description' => 'Flocage et flex pour personnalisation textile. Marquage en decoupe numerique, ideal noms, numeros et logos simples. Devis gratuit.',
            ],
            'tissage' => [
                'seo_url' => 'tissage-textile',
                'meta_title' => 'Etiquettes tissees - Marquage textile tisse',
                'meta_description' => 'Etiquettes tissees et ecussons personnalises pour vetements. Marquage tisse haute qualite, ideal pour les marques de mode.',
            ],
            'badge-pvc' => [
                'seo_url' => 'badge-pvc',
                'meta_title' => 'Badges PVC personnalises - Marquage textile 3D',
                'meta_description' => 'Badges PVC personnalises en relief pour vetements et accessoires. Rendu 3D premium, resistant et waterproof.',
            ],
            'full-print' => [
                'seo_url' => 'full-print',
                'meta_title' => 'Impression full print - Sublimation integrale textile',
                'meta_description' => 'Impression full print par sublimation pour textiles polyester. Visuel bord a bord, couleurs illimitees, ideal maillots et vetements sport.',
            ],
        ];

        foreach ($data as $slug => $fields) {
            MarkingTechnique::where('slug', $slug)->update($fields);
        }
    }
}
