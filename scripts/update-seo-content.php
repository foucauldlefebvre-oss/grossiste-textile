<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$contents = [

'broderie' => '<h2>La broderie textile : le marquage premium par excellence</h2>
<p>La <strong>broderie textile</strong> est la technique de marquage la plus noble et la plus durable. Realisee par des machines multi-tetes a commande numerique, elle consiste a broder directement votre logo ou motif sur le textile a l\'aide de fils de haute qualite. Le resultat est un marquage en relief, elegant et resistant aux lavages repetes.</p>

<h3>Pour quels textiles ?</h3>
<p>La broderie s\'adapte a la grande majorite des textiles : <strong>polos, chemises, vestes, casquettes, polaires, sweats, serviettes</strong> et bien plus. Elle est particulierement appreciee pour les vetements d\'entreprise, les uniformes et les tenues de representation.</p>

<h3>Avantages de la broderie</h3>
<ul>
<li><strong>Durabilite exceptionnelle</strong> : le marquage resiste a des centaines de lavages sans alteration</li>
<li><strong>Rendu premium</strong> : effet relief et toucher qualitatif, note qualite 5/5</li>
<li><strong>Polyvalence</strong> : compatible avec la plupart des textiles, y compris les matieres epaisses</li>
<li><strong>Perception de valeur</strong> : la broderie vehicule une image haut de gamme aupres de vos clients et collaborateurs</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Nombre de couleurs : illimite (chaque couleur = un fil different)</li>
<li>Minimum de commande : 20 pieces</li>
<li>Les degrades et photos ne sont pas realisables en broderie</li>
<li>Formats recommandes : de A7 (petit logo poitrine) a A4 (grand dos)</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Tous fichiers acceptes : .ai, .eps, .pdf, .png, .jpg, .psd. Notre equipe PAO se charge de creer le programme de broderie (piqure) adapte a votre logo.</p>

<h3>Delais de production</h3>
<p>Comptez <strong>2 a 6 semaines</strong> selon la periode, apres validation de votre BAT (Bon A Tirer).</p>',

'serigraphie' => '<h2>La serigraphie textile : ideale pour les grandes quantites</h2>
<p>La <strong>serigraphie textile</strong> est une technique d\'impression qui utilise des ecrans (un par couleur) pour deposer l\'encre directement sur le tissu. C\'est la methode la plus repandue pour le marquage de <strong>t-shirts, sweats et vetements promotionnels</strong> en grandes series.</p>

<h3>Comment ca fonctionne ?</h3>
<p>Chaque couleur de votre logo necessite la fabrication d\'un ecran (cadre). L\'encre est poussee a travers l\'ecran sur le textile, couleur par couleur. Plus la quantite est importante, plus le cout unitaire diminue, ce qui rend la serigraphie tres competitive sur les grandes series.</p>

<h3>Avantages de la serigraphie</h3>
<ul>
<li><strong>Prix degressif</strong> : le cout unitaire baisse significativement a partir de 100 pieces</li>
<li><strong>Couleurs vives et opaques</strong> : excellent rendu meme sur textiles fonces</li>
<li><strong>Durabilite</strong> : bonne tenue au lavage, note qualite 4/5</li>
<li><strong>Grands formats</strong> : impression possible jusqu\'a A3</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Minimum de commande : 20 pieces</li>
<li>1 a 6 couleurs maximum (pas de quadrichromie)</li>
<li>Les degrades et photos ne sont pas realisables</li>
<li>Cout de mise en route (ecrans) amorti sur la quantite</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Logo vectoriel : .ai, .eps, .pdf — ou JPEG/PNG haute qualite pour vectorisation par notre equipe.</p>

<h3>Delais de production</h3>
<p><strong>7 a 15 jours ouvres</strong> apres validation du BAT.</p>',

'impression-dtg' => '<h2>L\'impression DTG : la personnalisation photo sur textile</h2>
<p>L\'<strong>impression DTG</strong> (Direct To Garment) est une technique d\'impression numerique directe sur le textile. Comme une imprimante jet d\'encre, elle depose des encres a base d\'eau directement sur le tissu, permettant de reproduire des <strong>photos, degrades et designs complexes</strong> en qualite HD.</p>

<h3>Pour quels projets ?</h3>
<p>Le DTG est ideal pour les petites series personnalisees, les visuels photographiques, les designs multicolores complexes et les prototypes. Il est principalement utilise sur les <strong>textiles en coton</strong> (t-shirts, sweats, tote bags).</p>

<h3>Avantages du DTG</h3>
<ul>
<li><strong>Couleurs illimitees</strong> : impression photo, degrades, effets aquarelle</li>
<li><strong>Pas de minimum eleve</strong> : a partir de 10 pieces</li>
<li><strong>Toucher doux</strong> : l\'encre est absorbee par le tissu, pas de surepaisseur</li>
<li><strong>Ideal petites series</strong> : pas de frais de mise en route</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Fonctionne principalement sur le coton (min. 80%)</li>
<li>Rendu moins eclatant sur textiles fonces</li>
<li>Durabilite moindre qu\'en serigraphie ou broderie (note 3/5)</li>
<li>Cout unitaire plus eleve que la serigraphie sur grandes quantites</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Fichier sans fond : .png, .psd, .pdf, .ai, .eps. Resolution 300 DPI minimum recommandee.</p>

<h3>Delais de production</h3>
<p><strong>7 a 15 jours ouvres</strong> apres validation du BAT.</p>',

'flocage-flex' => '<h2>Le flocage et flex : marquage en decoupe pour logos simples</h2>
<p>Le <strong>flocage</strong> et le <strong>flex</strong> sont des techniques de marquage par thermo-transfert. Un film colore est decoupe a la forme de votre logo puis presse a chaud sur le textile. Le flex offre un rendu lisse et brillant, tandis que le flocage donne un toucher velours.</p>

<h3>Pour quels projets ?</h3>
<p>Ces techniques sont ideales pour les <strong>noms, prenoms, numeros, logos simples</strong> en 1 a 3 couleurs. Tres utilise pour les <strong>maillots de sport, vetements de travail</strong> et la personnalisation individuelle.</p>

<h3>Avantages du flex et flocage</h3>
<ul>
<li><strong>Personnalisation individuelle</strong> : ideal pour les prenoms et numeros</li>
<li><strong>Rendu net et precis</strong> : decoupe au millimetre</li>
<li><strong>Compatible tous textiles</strong> : coton, polyester, melange</li>
<li><strong>Petites quantites possibles</strong> : a partir de 10 pieces</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Logos simples uniquement (pas de degrades ni photos)</li>
<li>1 a 3 couleurs maximum</li>
<li>Pas adapte aux visuels tres detailles</li>
<li>Note qualite : 3/5</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Logo vectoriel : .ai, .eps, .pdf — ou JPEG/PNG haute qualite pour vectorisation.</p>

<h3>Delais de production</h3>
<p><strong>7 a 15 jours ouvres</strong> apres validation du BAT.</p>',

'dtf' => '<h2>Le transfert DTF : impression numerique polyvalente</h2>
<p>Le <strong>transfert DTF</strong> (Direct To Film) est une technique recente qui consiste a imprimer votre visuel sur un film special, puis a le transferer sur le textile par pressage a chaud. Il combine les avantages du DTG (couleurs illimitees) avec une compatibilite elargie a tous types de textiles.</p>

<h3>Avantages du DTF</h3>
<ul>
<li><strong>Couleurs illimitees</strong> : photos, degrades, designs complexes</li>
<li><strong>Compatible tous textiles</strong> : coton, polyester, nylon, melanges</li>
<li><strong>Bon rapport qualite-prix</strong> : competitif sur les petites et moyennes series</li>
<li><strong>A partir de 10 pieces</strong></li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Legerement en relief (film transfere)</li>
<li>Durabilite correcte mais inferieure a la serigraphie (note 2/5)</li>
<li>Sensible aux hautes temperatures (attention aux doudounes et softshells)</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Fichier sans fond : .png, .psd, .pdf, .ai, .eps.</p>

<h3>Delais de production</h3>
<p><strong>7 a 15 jours ouvres</strong> apres validation du BAT.</p>',

'tissage' => '<h2>Les etiquettes tissees : marquage integre haute qualite</h2>
<p>Le <strong>tissage</strong> consiste a fabriquer des etiquettes tissees reproduisant votre logo avec des fils colores. Ces etiquettes sont ensuite cousues sur le textile. C\'est la technique utilisee par les grandes marques pour leurs etiquettes de marque, de composition ou de taille.</p>

<h3>Avantages du tissage</h3>
<ul>
<li><strong>Rendu tres qualitatif</strong> : aspect marque premium (note 3.5/5)</li>
<li><strong>Durabilite extreme</strong> : le tissage ne s\'efface jamais</li>
<li><strong>Personnalisation complete</strong> : forme, taille, type de pli, fil dore ou argente</li>
<li><strong>Compatible tous textiles</strong></li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Minimum de commande : 100 pieces</li>
<li>Delai plus long que les autres techniques</li>
<li>Pas adapte aux visuels photographiques</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Tous fichiers acceptes : .ai, .eps, .pdf, .png, .jpg, .psd.</p>

<h3>Delais de production</h3>
<p><strong>4 a 6 semaines</strong> apres validation du BAT.</p>',

'badge-pvc' => '<h2>Les badges PVC : marquage en relief 3D</h2>
<p>Les <strong>badges PVC</strong> (ou ecussons PVC) sont des marquages moules en PVC souple reproduisant votre logo en 2D ou 3D avec un effet relief. Ils sont ensuite cousus ou thermocolles sur le textile. Tres utilises dans le <strong>sportswear, le workwear et l\'outdoor</strong>.</p>

<h3>Avantages des badges PVC</h3>
<ul>
<li><strong>Effet 3D et relief</strong> : rendu unique et moderne (note 3.5/5)</li>
<li><strong>Waterproof</strong> : resistant a l\'eau, ideal pour les vetements outdoor</li>
<li><strong>Couleurs Pantone precises</strong></li>
<li><strong>Tres resistant</strong> : ne se decolore pas, ne s\'effiloche pas</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Minimum de commande : 100 pieces</li>
<li>Format limite (generalement A7 a A6)</li>
<li>Delai de fabrication plus long</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Tous fichiers acceptes : .ai, .eps, .pdf, .png, .jpg, .psd.</p>

<h3>Delais de production</h3>
<p><strong>4 a 6 semaines</strong> apres validation du BAT.</p>',

'transfert-offset' => '<h2>Le transfert offset : impression quadri sur textile</h2>
<p>Le <strong>transfert offset</strong> (ou transfert numerique) est une technique d\'impression qui permet de reproduire des visuels en quadrichromie (CMJN) sur un papier transfert, puis de les appliquer sur le textile par pressage a chaud. C\'est la solution ideale pour les <strong>visuels photographiques en moyennes et grandes series</strong>.</p>

<h3>Avantages du transfert offset</h3>
<ul>
<li><strong>Couleurs illimitees et degrades</strong> : reproduction photo fidele</li>
<li><strong>Bonne durabilite</strong> : note qualite 4/5</li>
<li><strong>Prix competitif</strong> : a partir de 50 pieces</li>
<li><strong>Grand format possible</strong> : jusqu\'a A3</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>Minimum de commande : 50 pieces</li>
<li>Le visuel doit etre fourni sans fond</li>
<li>Leger toucher en surface (film transfere)</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Fichier sans fond : .png, .psd, .pdf, .ai, .eps.</p>

<h3>Delais de production</h3>
<p><strong>7 a 15 jours ouvres</strong> apres validation du BAT.</p>',

'transfert-serigraphique' => '<h2>Le transfert serigraphique : la qualite serigraphie sans minimum eleve</h2>
<p>Le <strong>transfert serigraphique</strong> combine la qualite de la serigraphie avec la flexibilite du transfert. Le visuel est d\'abord imprime en serigraphie sur un papier transfert, puis applique sur le textile par pressage a chaud. Le resultat est un marquage avec un <strong>rendu serigraphie, opaque et durable</strong>, mais avec des minimums de commande plus accessibles.</p>

<h3>Avantages du transfert serigraphique</h3>
<ul>
<li><strong>Rendu serigraphie</strong> : couleurs opaques et eclatantes, meme sur textiles fonces (note 4.5/5)</li>
<li><strong>Excellente durabilite</strong> : tenue comparable a la serigraphie directe</li>
<li><strong>A partir de 50 pieces</strong></li>
<li><strong>1 couleur</strong> : ideal pour les logos monochromes</li>
</ul>

<h3>Contraintes et limites</h3>
<ul>
<li>1 couleur uniquement</li>
<li>Minimum de commande : 50 pieces</li>
<li>Pas de degrades ni photos</li>
</ul>

<h3>Fichiers acceptes</h3>
<p>Logo vectoriel : .ai, .eps, .pdf — ou JPEG/PNG haute qualite pour vectorisation.</p>

<h3>Delais de production</h3>
<p><strong>7 a 15 jours ouvres</strong> apres validation du BAT.</p>',

];

foreach ($contents as $slug => $content) {
    $updated = App\Models\MarkingTechnique::where('slug', $slug)->update(['seo_content' => $content]);
    echo "$slug: " . ($updated ? 'OK' : 'NOT FOUND') . " (" . strlen($content) . " chars)\n";
}
