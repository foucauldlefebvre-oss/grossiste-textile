<?php

/**
 * Donnees produits pour CategoryProductSeeder
 * Genere depuis les donnees scrappees de marquage-textile.fr
 * Format: [nom, reference, marque, composition, grammage (g/m2), coupe, prix HT (EUR)]
 *
 * Total: 873 produits dans 83 categories
 */

return [

    // ===== ACCESSOIRES BEBE (5) =====
    'accessoires-bebe' => [
        ['Dream', 'VACOUDRE', 'Valento', '--', 240, 'bebe', 1.73],
        ['Crib bebe', 'VACOUCRI', 'Valento', '--', 310, 'bebe', 2.23],
        ['Baby Soft Cap', 'BBBBABZ36', 'BabyBugz', '100% coton organic', null, 'bebe', 3.91],
        ['PLAID', 'BBBABZ24', 'BabyBugz', '100% coton organique', null, 'bebe', 7.23],
        ['Cape de bain', 'ARPE73250', '', '100% coton', 385, 'bebe', 9.19],
    ],

    // ===== ACCESSOIRES PRO (12) =====
    'accessoires-pro' => [
        ['Zebra', 'VACHZEB', 'Valento', '100% polyester', 290, 'mixte', 0.30],
        ['Giraffe', 'VACHGIF', 'Valento', '100% polyester', 290, 'mixte', 0.34],
        ['Bremen', 'VACHBRE', 'Valento', '100% polyester', 210, 'mixte', 0.34],
        ['Pudding', '335-15129', 'Valento', '100% polyester', 220, 'mixte', 0.41],
        ['Berry', 'VAGAPUD', 'Valento', '100% polyester', 220, 'mixte', 0.68],
        ['Bower', '337-15135', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 0.99],
        ['Spinner', 'VACUBOW', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 1.24],
        ['Coulant', 'VACUCOU', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 1.49],
        ['Coffee', 'VATACOF', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 1.73],
        ['Cordon', 'VACUCOR', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 1.98],
        ['Block pro, poche amovible', 'SOACBL', 'Sol\'s', 'Canvas 300: 65% polyester, 35% coton', null, 'mixte', 2.25],
        ['Cabernet', 'VATACAB', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 2.72],
    ],

    // ===== ACCESSOIRES SPORT (1) =====
    'accessoires-sport' => [
        ['Sac a dos cordon Zorzal', '1020', '', '100% polyester', 150, 'mixte', 0.99],
    ],

    // ===== BANDANAS (4) =====
    'bandanas' => [
        ['Fiesta', 'VAFOUFIE', 'Valento', '100% polyester', 65, 'mixte', 0.28],
        ['Foulard carre Val', 'VABACA', 'Valento', '65% polyester / 35% coton', 125, 'mixte', 0.86],
        ['Bandana Bib', 'BBBABZ23', 'BabyBugz', '100% coton (gris: 98% coton / 2% polyester)', null, 'bebe', 2.33],
        ['Festero (lot de 100)', 'ROFOUFES', 'Roly', '100% polyester', null, 'mixte', 24.75],
    ],

    // ===== BLOUSES VESTES TRAVAIL (22) =====
    'blouses-vestes-travail' => [
        ['Lince', 'VABLLIN', 'Valento', '65% polyester, 35% coton', 120, 'mixte', 3.22],
        ['Costa', 'VABLCOS', 'Valento', '100% coton', 170, 'mixte', 3.22],
        ['Wiper', 'VABLWIP', 'Valento', '100% coton', 180, 'mixte', 3.22],
        ['Blouse ecolier Notes', 'VABLNO', 'Valento', '65% polyester, 35% coton', null, 'enfant', 4.89],
        ['Tunique Sante Link', 'VABLTUN', 'Valento', '65% polyester, 35% coton', 190, 'mixte', 6.07],
        ['Alanis', 'VABLALA', 'Valento', '65% polyester, 35% coton', 140, 'mixte', 6.86],
        ['Blouse femme Smart', 'VABLSMA', 'Valento', '65% polyester, 35% coton', 200, 'femme', 7.13],
        ['Blouse Ferox Women', 'ROBLFEXW', 'Roly', '94% polyester, 6% elasthanne', 170, 'femme', 7.26],
        ['Blouse Ferox', 'ROBLFEX', 'Roly', '94% polyester, 6% elasthanne', 170, 'homme', 7.26],
        ['Creta', 'VABLCRE', 'Valento', '100% coton', 250, 'mixte', 7.52],
        ['Blouse Load', 'VABLLOA', 'Valento', '65% polyester, 35% coton', 190, 'mixte', 7.92],
        ['Veste de travail Galen', 'VABLGAL', 'Valento', '65% polyester, 35% coton', 210, 'mixte', 8.18],
        ['Mirka', 'VABLMIR', 'Valento', '65% polyester, 35% coton', 210, 'mixte', 10.30],
        ['Chispa veste', 'VABLCHI', 'Valento', '100% polyester', 250, 'mixte', 12.10],
        ['Veste hotellerie manches courtes Alex', 'SNVEALE', '', '50% coton, 50% polyester', 210, 'mixte', 13.66],
        ['Veste hotellerie manches longues Alex', 'SNVEALEML', '', '50% coton, 50% polyester', 210, 'mixte', 13.66],
        ['Winterfell veste', 'VAPANWIN', 'Valento', '65% polyester, 35% coton; doublure 100% polyester', 210, 'mixte', 14.84],
        ['Veste de cuisine Alban', 'SNVEALB', '', 'Polyester/coton', 210, 'mixte', 15.01],
        ['Blouse rayee sans manche Adriana', '4GLUBV', '', 'Polyester/coton raye', 160, 'femme', 15.52],
        ['Blouse sans manche Madona', 'SNVBLMAD', '', '65% polyester, 35% coton', 195, 'femme', 15.76],
        ['Blouson Scoot manches amovibles', 'VAVESCO', 'Valento', '100% polyester ext. + doublure', 220, 'mixte', 22.64],
        ['T-shirt Modacrylique', 'ROTSMODAC', 'Kariban', '60% modacrylique, 39% coton, 1% carbone', 220, 'mixte', 23.77],
    ],

    // ===== BOBS (4) =====
    'bobs' => [
        ['Fisher', 'VACAFIS', 'Valento', '100% coton', 220, 'mixte', 1.24],
        ['Forest', 'VACAFOR', 'Valento', '100% coton', 400, 'mixte', 1.58],
        ['B720', 'BEECHB720', '', '100% paille papier', null, 'mixte', 3.12],
        ['B658', 'BECAB658', '', '100% toile de coton', null, 'mixte', 3.42],
    ],

    // ===== BODIES BEBE (12) =====
    'bodies-bebe' => [
        ['Body sans manches Kiddy', 'VABOKID', 'Valento', '100% coton ring spun', 210, 'bebe', 1.68],
        ['Body Teddy', 'VABETED', 'Valento', '100% coton ring spun', 210, 'bebe', 1.72],
        ['Body Piccolo', 'VABEPIC', 'Valento', '100% coton ring spun', 210, 'bebe', 1.88],
        ['BODY HONEY LS', 'ROBOHOLS', 'Roly', '96% coton peigne, 4% elasthanne', null, 'bebe', 2.49],
        ['Body Continental Classic Bio MC', '231-10557', 'Continental Clothing', '100% coton peigne biologique', 200, 'bebe', 3.26],
        ['Baby Envelope Neck Body', 'BBBABZ10', 'BabyBugz', '100% coton peigne interlock', 200, 'bebe', 3.42],
        ['Baby Organic Vest Bodysuit', 'BBBBABZ39', 'BabyBugz', '100% coton organic', null, 'bebe', 3.51],
        ['Organic Baby Short Sleeve Bodysuit', 'BBBABZ10TLC', 'BabyBugz', '100% coton peigne organic', 200, 'bebe', 3.71],
        ['Body Continental Classic Bio ML', 'COBOJUMBE', 'Continental Clothing', '100% coton peigne biologique', 200, 'bebe', 3.82],
        ['Baby Ringer Bodysuit', 'BBBABZ19', 'BabyBugz', '100% coton peigne (Marl: 96% coton, 4% polyester)', 200, 'bebe', 3.89],
        ['Organic Baby Long Sleeve Bodysuit', 'BBBABZ30TLC', 'BabyBugz', '100% coton peigne organic', null, 'bebe', 4.90],
        ['Organic Bambino', 'SOBEORG', 'Sol\'s', '100% coton agriculture biologique', 220, 'bebe', 6.07],
    ],

    // ===== BODYWARMER PRO (3) =====
    'bodywarmer-pro' => [
        ['Thunder Valento', 'VABOTHU', 'Valento', '65% polyester, 35% coton', 220, 'mixte', 9.77],
        ['Gilet Highway', 'VAHVHIG', 'Valento', '100% polyester', 200, 'mixte', 13.16],
        ['Bodywarmer ALMANZOR', 'ROBOALM', 'Roly', '80% polyester, 20% coton', 200, 'mixte', 11.15],
    ],

    // ===== BONNETS (15) =====
    'bonnets' => [
        ['B44', 'BEBOB44', '', '100% acrylique Soft-Touch', null, 'mixte', 1.18],
        ['Bonnet B45', 'BEBOB45', '', '100% acrylique Soft-Touch', null, 'mixte', 1.62],
        ['B450B bonnet junior', 'B455B', '', '100% acrylique', 69, 'enfant', 1.87],
        ['B426', 'B426', '', '100% acrylique', null, 'mixte', 1.93],
        ['B421', 'B421', '', '100% acrylique', null, 'mixte', 1.93],
        ['B453', 'B453', '', '100% acrylique', null, 'mixte', 1.97],
        ['B451', 'B451', '', '100% acrylique Soft-Touch', null, 'mixte', 2.13],
        ['B450', 'B450', '', '100% acrylique Soft-Touch', null, 'mixte', 2.13],
        ['Baby T-knot Hat', 'BBBBABZ15', 'BabyBugz', '100% coton peigne', 200, 'bebe', 2.13],
        ['B445', 'B445', '', '100% acrylique Soft-Touch', null, 'mixte', 2.17],
        ['B447', 'B447', '', '100% acrylique Soft-Touch', null, 'mixte', 2.34],
        ['B416', 'B416', '', '100% acrylique Soft-Spun', null, 'mixte', 2.57],
        ['B429', 'BEBOB429', '', '57% acrylique / 22% polyester / 19% coton / 2% elasthanne', null, 'mixte', 2.96],
        ['B433', 'BEBOB433', '', '100% acrylique Soft-Spun', null, 'mixte', 3.22],
        ['B412', 'BEBOB412', '', '100% acrylique Soft-Spun', null, 'mixte', 3.26],
    ],

    // ===== BONNETS BAVOIRS BEBE (5) =====
    'bonnets-bavoirs-bebe' => [
        ['Baboir Babib', 'SOBABAB', 'Sol\'s', '100% coton ringspun jersey', 180, 'bebe', 1.81],
        ['Baby Bib With Contrast Ties', 'BBBABZ16C', 'BabyBugz', '100% coton peigne', 220, 'bebe', 1.97],
        ['Baby T-knot Hat', 'BBBBABZ15', 'BabyBugz', '100% coton peigne (Gris: 85% coton, 15% polyester)', 200, 'bebe', 2.13],
        ['Baby Bib', 'BBBBABZ12', 'BabyBugz', '100% coton peigne (Gris: 85% coton, 15% polyester)', 220, 'bebe', 2.52],
        ['Baby Striped T-knot Hat', 'BBBABZ15S', 'BabyBugz', '100% coton peigne', 200, 'bebe', 2.92],
    ],

    // ===== CASQUETTES CLASSIQUES (25) =====
    'casquettes-classiques' => [
        ['Painter', 'VACAPAI', 'Valento', '100% coton', 200, 'mixte', 0.50],
        ['Casquette Basica', 'ROCASBAS', 'Roly', '80% polyester / 20% coton', null, 'mixte', 0.65],
        ['Casquette Promotion', 'VACAPRO', 'Valento', '100% coton', 185, 'mixte', 0.79],
        ['Casquette 5 panneaux Eris', 'ROCAERI', 'Roly', '100% coton', 170, 'mixte', 0.81],
        ['Casquette promotionnelle Uranus', '835', 'Roly', '100% coton', 170, 'mixte', 0.86],
        ['Panel', 'ROCASPAN', 'Roly', '100% coton', null, 'mixte', 0.94],
        ['Tecnica', 'ROCASTEC', 'Roly', '100% polyester microfibre', null, 'mixte', 1.02],
        ['B10B', 'BECAB10B', '', '100% coton Twill', null, 'enfant', 1.58],
        ['B10', 'B10', '', '100% coton Twill', null, 'mixte', 1.67],
        ['B610C', 'B171', '', '100% coton Twill', null, 'mixte', 1.83],
        ['B41', 'B41', '', '100% coton Twill', null, 'mixte', 1.87],
        ['B615B junior', 'B615B', '', '100% coton Twill', null, 'enfant', 1.87],
        ['B58', 'B58', '', '100% coton Drill', null, 'mixte', 1.87],
        ['B610', 'B610', '', '100% coton Twill', null, 'mixte', 1.87],
        ['B361', 'B361', '', '95% coton / 5% elasthanne', null, 'mixte', 1.97],
        ['B15', 'B15', '', '100% coton Drill', null, 'mixte', 2.27],
        ['B15C', 'B15C', '', '100% coton Drill', null, 'mixte', 2.39],
        ['B34', 'B34', '', '100% coton lourd lave', null, 'mixte', 2.47],
        ['Mountain', 'VACAMOU', 'Valento', '100% coton', null, 'mixte', 2.57],
        ['B669', 'BECAB669', '', '75% polyester / 25% coton brosse (visiere 100% coton)', null, 'mixte', 2.82],
        ['B159', 'BECAB159', '', '100% coton Drill', null, 'mixte', 2.96],
        ['B626', 'BECAB626', '', '100% coton lourd lave', null, 'mixte', 3.61],
        ['B38', 'BECAB38', '', '100% coton lourd lave', null, 'mixte', 3.75],
        ['Baby Soft Cap', 'BBBBABZ36', 'BabyBugz', '100% coton organic', null, 'bebe', 3.91],
        ['Casquette Adidas Cresting de performance', 'JSBTQ2', 'Roly', '100% polyester', 101, 'mixte', 7.55],
    ],

    // ===== CASQUETTES SNAPBACK (6) =====
    'casquettes-snapback' => [
        ['B645', 'B645', '', '100% polyester', null, 'mixte', 1.97],
        ['Casquette filet trucker B640', 'B640', '', '100% coton (face) / 100% polyester (filet)', null, 'mixte', 2.25],
        ['B660', 'B660', '', '100% polyester Twill', null, 'mixte', 2.57],
        ['B694', 'B694', '', '100% coton (face) / 100% polyester (filet)', null, 'mixte', 2.57],
        ['B691', 'BECAB691', '', '100% coton Twill', null, 'mixte', 2.86],
        ['B630', 'BEECAB630', '', '80% polyester / 20% coton', null, 'mixte', 4.55],
    ],

    // ===== CEINTURES PARAPLUIES (6) =====
    'ceintures-parapluies' => [
        ['Brooklyn', 'VACEIBROO', 'Valento', 'N/A', null, 'mixte', 0.71],
        ['P201', 'CGPAP201', '', '100% polyester', null, 'mixte', 3.00],
        ['Cravate Globe', '677', 'Sol\'s', '100% polyester satin', null, 'mixte', 5.69],
        ['P211', 'CGPAP211', '', '100% polyester', null, 'mixte', 4.65],
        ['P202', 'CGPAP202', '', '100% polyester', null, 'mixte', 4.73],
        ['Cravate fine Gatsby', '676', 'Sol\'s', '100% polyester satin', null, 'mixte', 4.88],
    ],

    // ===== CHAPEAUX (3) =====
    'chapeaux' => [
        ['B88', 'BEBOB88', '', '65% polyester / 35% coton', null, 'mixte', 3.75],
        ['B635', 'BEECHB635', '', '47% laine / 40% polyester / 13% viscose', null, 'mixte', 3.88],
        ['B622', 'BECAB622', '', '47% laine / 40% polyester / 13% viscose', null, 'mixte', 4.70],
    ],

    // ===== CHAPKAS (2) =====
    'chapkas' => [
        ['B345', 'BEBOB345', '', '100% nylon Taslon (ext.), fourrure synthetique (int.)', null, 'mixte', 6.32],
        ['B346', 'BEBOB346', '', '100% toile de coton (doublure fourrure synthetique)', null, 'mixte', 7.87],
    ],

    // ===== CHASUBLES DOSSARDS (4) =====
    'chasubles-dossards' => [
        ['Peto', 'RODOSPET', 'Roly', '100% polyester', 130, 'mixte', 1.42],
        ['Chasuble Anfield', 'SOTSAN', 'Sol\'s', '100% polyester mesh', 80, 'mixte', 1.67],
        ['Wembley', 'VADOSWEM', 'Valento', '100% polyester', 150, 'mixte', 2.08],
        ['Parade reversible', 'VADOSPAR', 'Valento', '100% polyester', 150, 'mixte', 3.87],
    ],

    // ===== CHAUSSETTES (9) =====
    'chaussettes' => [
        ['Ansar', 'VASVANS', 'Valento', 'N/A', null, 'mixte', 0.50],
        ['Carabu', 'VASVCAR', 'Valento', 'N/A', null, 'mixte', 0.94],
        ['Azor', '246-11091', '', 'N/A', null, 'mixte', 1.44],
        ['Soccer', 'ROCHAUSOC', 'Roly', 'Tibia: 100% polyester; Pied: 80% polyester / 17% coton / 3% elasthanne', null, 'enfant', 1.47],
        ['Chaussettes lot de 3', 'FOUN67600', 'Fruit of the Loom', '67% coton / 13% polyamide / 18% polyester / 2% elasthanne', null, 'mixte', 3.87],
        ['Chaussettes de ville OFG', 'KACHAK818', 'Kariban', '89% coton / 9% polyamide / 2% elasthanne', null, 'mixte', 5.74],
        ['Chaussettes de ville OFG (variante)', '885-38636', 'Kariban', '89% coton / 9% polyamide / 2% elasthanne', null, 'mixte', 5.74],
        ['Chaussettes quart lot de 3', 'FOUN67602', 'Fruit of the Loom', '70% coton / 6% polyamide / 22% polyester / 2% elasthanne', null, 'mixte', 4.56],
        ['Chaussettes lot de 3', 'FOUN67600', 'Fruit of the Loom', '72% coton / 23% polyamide / 4% polyester / 1% elasthanne', null, 'mixte', 5.08],
    ],

    // ===== CHEMISES MANCHES COURTES (12) =====
    'chemises-manches-courtes' => [
        ['Chemisette Sofia', 'ROCHSOF', 'Roly', '65% polyester / 35% coton popeline', 130, 'femme', 6.60],
        ['Chemisette Aifos', 'ROCHAIF', 'Roly', '65% polyester / 35% coton popeline', 130, 'femme', 7.79],
        ['Smart mc women', 'BCCHSMAWC', 'B&C', '65% polyester, 35% coton peigne', 115, 'femme', 10.34],
        ['Smart mc men', 'BCCHSMAW', 'B&C', '65% polyester, 35% coton peigne', 115, 'homme', 10.34],
        ['Heritage mc women', '574-22048', 'B&C', '100% coton peigne', 125, 'femme', 11.98],
        ['Heritage mc men', 'BCCHHERC', 'B&C', '100% coton peigne', 120, 'homme', 11.98],
        ['Oxford mc women', '570-21888', 'B&C', '70% coton peigne, 30% polyester', 135, 'femme', 12.42],
        ['Oxford mc men', 'BCCHEOXFCM', 'B&C', '70% coton peigne, 30% polyester', 135, 'homme', 12.42],
        ['Sharp mc men', 'BCCHSHAMC', 'B&C', '100% coton peigne', 130, 'homme', 14.33],
        ['Sharp mc women', 'BCCHSHAMCW', 'B&C', '100% coton peigne', 130, 'femme', 14.33],
        ['Black tie mc men', 'BCCHBLAMC', 'B&C', '97% coton peigne, 3% elasthanne', 135, 'homme', 14.55],
        ['Black tie mc women', 'BCCHBLAMCW', 'B&C', '97% coton peigne, 3% elasthanne', 135, 'femme', 14.55],
    ],

    // ===== CHEMISES MANCHES LONGUES (26) =====
    'chemises-manches-longues' => [
        ['Chemise Sofia LS', 'ROCHSOFLS', 'Roly', '65% polyester / 35% coton popeline', 130, 'femme', 8.15],
        ['Chemise Aifos', 'ROCHAIFLS', 'Roly', '65% polyester / 35% coton popeline', 130, 'mixte', 8.91],
        ['Chemise de travail multi poche', 'VACHECON', 'Valento', '100% coton', 180, 'mixte', 9.50],
        ['Smart ls women', '578-22227', 'B&C', '65% polyester, 35% coton peigne', 115, 'femme', 10.16],
        ['Chemise Moscu Femme', 'ROCHMOSW', 'Roly', '97% coton / 3% spandex', 130, 'femme', 11.92],
        ['Chemise Moscu', 'ROCHMOS', 'Roly', '97% coton / 3% spandex', 130, 'homme', 11.92],
        ['Smart ls men', 'BCCHSMALS', 'B&C', '65% polyester, 35% coton peigne', 115, 'homme', 12.31],
        ['Chemise popeline ML femme K542', 'RMR5TD', 'Kariban', '100% coton', null, 'femme', 12.82],
        ['Chemise popeline ML Kariban K541', '7ZEO9T', 'Kariban', '100% coton', null, 'homme', 12.82],
        ['Heritage ls men', 'BCCHHERWC', 'B&C', '100% coton peigne', 120, 'homme', 13.21],
        ['Heritage ls women', 'BCCHHERW', 'B&C', '100% coton peigne', 120, 'femme', 13.21],
        ['Oxford men', 'BCCHEOXFCF', 'B&C', '70% coton peigne, 30% polyester', 135, 'homme', 13.66],
        ['Oxford women', 'BCCHEOXFW', 'B&C', '70% coton peigne, 30% polyester', 135, 'femme', 13.66],
        ['Chemise popeline ML enfant K521', 'KACHK521', 'Kariban', '100% coton', null, 'enfant', 14.14],
        ['Barry man Denim', 'SOCHBAR', 'Sol\'s', '100% coton', null, 'homme', 14.30],
        ['Barry Woman Denim', '681-25279', 'Sol\'s', '100% coton', null, 'femme', 14.30],
        ['Sharp ls men', 'BCCHSHA', 'B&C', '100% coton peigne', 130, 'homme', 15.45],
        ['Sharp ls women', 'BCCHSHAW', 'B&C', '100% coton peigne', 130, 'femme', 15.45],
        ['Black tie ls men', 'BCCHBLA', 'B&C', '97% coton peigne, 3% elasthanne', 135, 'homme', 15.67],
        ['Black tie ls women', 'BCCHBLAW', 'B&C', '97% coton peigne, 3% elasthanne', 135, 'femme', 15.67],
        ['Chemise denim (femme)', 'BCCHDENW', 'B&C', '100% coton denim twill', null, 'femme', 16.07],
        ['Chemise denim men', 'BCCHDEN', 'B&C', '100% coton denim twill', null, 'homme', 16.07],
        ['Chemise ML sans repassage femme', 'KACHEK538', 'Kariban', '100% coton micro serge', 120, 'femme', 23.25],
        ['Chemise ML sans repassage homme', 'KACHEK537', 'Kariban', '100% coton micro serge', 120, 'homme', 23.25],
        ['Chemise coton tencel femme', 'RUCHEJ955F', 'Russell', '54% coton / 46% Tencel', 136, 'femme', 24.51],
        ['Chemise coton tencel homme', 'RUCHEJ954', 'Russell', '54% coton / 46% Tencel', 136, 'homme', 24.51],
    ],

    // ===== CHIFFONS (1) =====
    'chiffons' => [
        ['Torchon fabrique en France', 'KatoK139', 'Kariban', '100% coton', null, 'mixte', 3.55],
    ],

    // ===== COMBINAISONS TRAVAIL (5) =====
    'combinaisons-travail' => [
        ['Kevin', 'VAPANKEV', 'Valento', '65% polyester, 35% coton', 210, 'mixte', 13.44],
        ['Ropper', 'VACOROP', 'Valento', '100% coton', 250, 'mixte', 15.96],
        ['Combinaisons 2 Fermetures Gilbert', 'SNCOMGIL', '', 'Polyester/coton', 245, 'mixte', 17.58],
        ['Combinaison 2 Fermetures Victor', 'SNCOVIC', '', 'Polyester/coton', 245, 'mixte', 19.20],
        ['Combinaison Ignifuge Blazer', 'ROCOBLA', 'Roly', '60% modacrylique, 37% coton, 3% fibre de carbone', 220, 'mixte', 38.88],
    ],

    // ===== COUPE VENT (8) =====
    'coupe-vent' => [
        ['Escocia', 'ROCOUESC', 'Roly', '100% polyester Taffeta avec traitement silver coating', null, 'enfant', 5.52],
        ['KWAY Rain', 'VAKWRAI', 'Valento', '100% Polyester', 100, 'mixte', 6.27],
        ['Veste de Sport Val Court', 'VAVECOU', 'Valento', '100% polyester, tissu acetate dual-flock', 250, 'enfant', 6.34],
        ['Impermeable Leger Island', 'ROIMPISL', 'Roly', '100% polyester taffeta', 70, 'mixte', 6.93],
        ['Angelo', 'ROCOUANG', 'Roly', '100% polyester', null, 'enfant', 9.23],
        ['Veste Impermeable Utah Unisexe', 'ROVEUTA', 'Roly', '100% polyester impermeable', null, 'mixte', 16.46],
        ['Veste Nimbus Everett Cover Femme', 'NIMVEEVF', 'Nimbus', 'Ponge 100% Polyester avec revetement PU; Doublure: Maille 100% Polyester', null, 'femme', 55.89],
        ['Veste Nimbus Everett Cover', 'NIMVEEV', 'Nimbus', 'Ponge 100% Polyester avec revetement PU; Doublure: Maille 100% Polyester', null, 'homme', 55.89],
    ],

    // ===== COUVERTURES (2) =====
    'couvertures' => [
        ['Cushion', 'VACOUCUS', 'Valento', '100% polyester', 310, 'mixte', 3.22],
        ['Snow', 'VACOUSNO', 'Valento', '100% polyester', 240, 'mixte', 4.46],
    ],

    // ===== DEBARDEURS (15) =====
    'debardeurs' => [
        ['Valento Athletic', '', 'Valento', '100% polyester', 160, 'mixte', 1.09],
        ['Debardeur femme Shura', 'ROTSSHU', 'Roly', '100% polyester pique knit', 140, 'femme', 1.84],
        ['Debardeur femme et enfant Brenda', 'BCVOQP', 'Roly', '100% coton (Gris: 85%/15% viscose)', 160, 'femme', 2.03],
        ['Debardeur Cawley', 'ROTSCAW', 'Roly', '94% coton / 6% elasthanne', 180, 'mixte', 2.03],
        ['Debardeur Andre', '', 'Roly', '100% polyester', 140, 'mixte', 2.03],
        ['Debardeur Interlagos', 'ROTSINTE', 'Roly', '100% polyester microperfore', 140, 'mixte', 2.08],
        ['Debardeur enfant Aida', 'ROTSAIDK', 'Roly', '92% polyester / 8% elasthanne', 160, 'enfant', 2.26],
        ['Debardeur Adulte et enfant Texas', 'EKJLM9', 'Roly', '100% coton single jersey (Gris: 85%/15% viscose)', 155, 'enfant', 2.34],
        ['Debardeur femme Carolina', 'ROTSCAR', 'Roly', '100% coton, cote 1x1', 220, 'femme', 2.39],
        ['Debardeur Femme Aida', 'ROTSAID', 'Roly', '92% polyester / 8% elasthanne', 160, 'femme', 2.56],
        ['Women\'s Vintage Organic Slub Tank T', 'MATSM127', 'Stanley/Stella', '100% coton bio', 150, 'femme', 3.91],
        ['Brassiere femme sans couture UE', 'KABRAK870', 'Roly', '93% polyamide / 7% elasthanne', null, 'femme', 3.99],
        ['Women\'s Vintage Organic Slub Tank T (var.)', 'MATSM125', 'Stanley/Stella', '100% coton bio', 150, 'femme', 4.41],
        ['Women\'s Black Label Tencel Tank Vest', 'MATSM117', 'Stanley/Stella', '100% Tencel Lyocell', null, 'femme', 4.41],
        ['Tencel Black Label Strap Vest', '526-17136', 'Stanley/Stella', '100% Tencel Lyocell', null, 'femme', 6.32],
    ],

    // ===== DOUDOUNES (7) =====
    'doudounes' => [
        ['Montana Valento', 'VADOUMON', 'Valento', 'Exterieur: 95% polyester / 5% PU; Doublure polaire 100% polyester', 170, 'mixte', 10.63],
        ['Doudoune sans manches Oslo femme', 'RODOUOSLF', 'Roly', '100% polyester, 290 taffeta; Rembourrage: 100% polyester', 65, 'femme', 14.40],
        ['Doudoune sans manches Oslo homme et enfant', 'RODOUOSLO', 'Roly', '100% polyester, 300T; Rembourrage: 100% polyester', 290, 'enfant', 14.40],
        ['Doudoune Finland homme', 'RODOUFIN', 'Roly', '100% polyester, 300T; Rembourrage: polyester', 290, 'homme', 16.46],
        ['Doudoune Finland femme', 'RODOUFINF', 'Roly', '100% polyester, 300T; Rembourrage: polyester', 290, 'femme', 16.46],
        ['Doudoune a capuche Norway', 'RODOUNOR', 'Roly', '100% polyester, 300T; Doublure et rembourrage polyester', 290, 'mixte', 17.05],
        ['Doudoune a capuche Norway Femme', 'RODOUNORF', 'Roly', '100% polyester, 300T; Doublure et rembourrage polyester', 290, 'femme', 17.05],
    ],

    // ===== ECHARPES GANTS (9) =====
    'echarpes-gants' => [
        ['B900', 'BEECB900', '', '100% polyester microfibres', null, 'mixte', 1.28],
        ['Echarpe bicolore "Team"', 'RECHR146X', 'Result', '100% acrylique tricot', 210, 'mixte', 3.13],
        ['B479', 'BEECB479', '', '100% acrylique Soft-Touch', null, 'mixte', 3.75],
        ['B491', 'BEGAB491', '', '95% acrylique / 5% elasthanne (variante: 67% acrylique / 28% polypropylene / 5% elasthanne)', null, 'mixte', 3.75],
        ['Cravate Globe', '677', 'Sol\'s', '100% polyester satin', null, 'mixte', 5.69],
        ['B495', 'BEGAB495', '', '100% acrylique Twill', null, 'mixte', 3.91],
        ['B468', 'BEECB468', '', '100% acrylique Soft-Touch', null, 'mixte', 4.25],
        ['Cravate fine Gatsby', '676', 'Sol\'s', '100% polyester satin', null, 'mixte', 4.88],
        ['B424', 'BEECB424', '', '100% acrylique Soft-Touch', null, 'mixte', 4.90],
    ],

    // ===== GILETS CARDIGANS (11) =====
    'gilets-cardigans' => [
        ['Bodywarmer Safari', 'VABOSAF', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 7.66],
        ['Baby Bomber Jacket', 'BBBABZ40', 'BabyBugz', '80% coton peigne organique GOTS, 20% polyester', 200, 'bebe', 9.45],
        ['Cardigan Russel Enfant', 'RUSWJ273B', 'Russell', '50% coton, 50% polyester', 295, 'enfant', 10.03],
        ['Bodywarmer Reporter', 'VABOREP', 'Valento', '65% polyester, 35% coton', 220, 'mixte', 10.63],
        ['Cardigan Russel', 'RUSWJ273M', 'Russell', '50% coton, 50% polyester', 295, 'homme', 12.61],
        ['Golden men', 'SOPUGOLW', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 280, 'homme', 13.82],
        ['Golden Women', '563-21554', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 280, 'femme', 13.82],
        ['Griffin', 'SAPUGRIW', 'Sol\'s', '70% viscose, 30% polyamide', 275, 'homme', 15.59],
        ['Griffith', 'SAPUGRI', 'Sol\'s', '70% viscose, 30% polyamide', 275, 'femme', 15.59],
        ['Gordon men', 'SOPUGOR', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 280, 'homme', 15.77],
        ['Gordon Women', 'SOPUGORW', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 280, 'femme', 15.77],
    ],

    // ===== GILETS POLAIRE (13) =====
    'gilets-polaire' => [
        ['Glace', 'VAPOGLA', 'Valento', '100% Polyester', 300, 'mixte', 7.13],
        ['Polaire Dakota', 'VAPODAK', 'Valento', '100% Polyester', 300, 'mixte', 7.92],
        ['Pacific', 'VAPOPAC', 'Valento', '100% Polyester', 300, 'mixte', 8.45],
        ['Polaire North Enfant', 'SOPONORK', 'Sol\'s', '100% polyester densite accrue', 300, 'enfant', 8.58],
        ['Polaire Sols North', 'SOPONOR', 'Sol\'s', '100% polyester densite accrue', 300, 'homme', 9.11],
        ['Polaire North Femme', 'SOPONORW', 'Sol\'s', '100% polyester densite accrue', 300, 'femme', 9.11],
        ['Polaire Grizzly', 'PEDPOLGRI', '', '100% micropolaire polyester', 300, 'homme', 9.24],
        ['Polaire Grizzly Femme', 'PEDPOLGRIW', '', '100% micropolaire polyester', 300, 'femme', 9.24],
        ['Polaire Pirineo (Artic)', 'ROPOPIR', 'Roly', '100% polyester, polaire', 300, 'homme', 9.50],
        ['Pirineo Woman', 'ROPOPIRW', 'Roly', '100% polyester, polaire', 300, 'femme', 9.50],
        ['Polaire Luciane', 'ROPOLUC', 'Roly', '100% polyester micropolaire', 300, 'homme', 10.88],
        ['Polaire Luciane Woman', 'ROPOLUCW', 'Roly', '100% polyester micropolaire', 300, 'femme', 10.88],
        ['Siberian double polaire', 'VAPOSIB', 'Valento', '100% Polyester', 300, 'mixte', 18.48],
    ],

    // ===== HAUTE VISIBILITE (6) =====
    'haute-visibilite' => [
        ['Gilet de securite Sirio', 'ROGISIR', 'Roly', '100% polyester, jersey simple', 135, 'mixte', 1.40],
        ['Secure pro', 'SOVISEC', 'Sol\'s', '100% polyester mesh', 120, 'mixte', 1.71],
        ['Road', 'VAHVROA', 'Valento', '100% polyester', 300, 'mixte', 13.44],
        ['Polaire Airport', 'VAHVAIR', 'Valento', '100% polyester', 300, 'mixte', 14.00],
        ['Blouson haute visibilite Albert', 'SNMAALB', '', '60% coton, 40% polyester (fluo: 80% poly, 20% coton)', 310, 'mixte', 19.49],
        ['Softshell Storm', 'VAHVSTO', 'Valento', '--', null, 'mixte', 20.44],
    ],

    // ===== HAUTS DE TRAVAIL (16) =====
    'hauts-de-travail' => [
        ['Eagle', 'ROTSBEA', 'Valento', '100% coton', 160, 'mixte', 1.58],
        ['Server', 'BHULPN', 'Valento', '100% coton', 160, 'mixte', 1.88],
        ['Soldier', 'VATSSOL', 'Valento', '100% coton', 160, 'mixte', 1.98],
        ['T-shirt de travail RX151', 'RTTSRX151', 'ProRTX', '50% coton, 50% polyester', 180, 'homme', 2.38],
        ['Mountain', 'VACAMOU', 'Valento', '100% coton', 200, 'mixte', 2.57],
        ['T-shirt avec poche Teckel', '36-29463', 'Roly', '100% coton (Gris chine: 85% coton, 15% viscose)', 160, 'mixte', 2.86],
        ['Venur', 'VAPOLVEN', 'Valento', '100% coton', 220, 'mixte', 4.36],
        ['Polo Patrol 220g', 'VAPOLPAT', 'Valento', '100% coton ring spun', 220, 'mixte', 4.84],
        ['Polo Kariban Enfant', 'KAPOK249', 'Kariban', '100% coton', null, 'enfant', 7.91],
        ['Chemise de travail multi poche', 'VACHECON', 'Valento', '100% coton', null, 'mixte', 9.50],
        ['T-shirt haute visibilite Tauri', 'ROTSTAU', 'Roly', '55% coton, 45% polyester', 170, 'mixte', 9.90],
        ['Polo Kariban Homme', 'KAPOK241', 'Kariban', '100% coton', null, 'homme', 11.28],
        ['Polo Kariban Femme', 'KAPOK242', 'Kariban', '100% coton', null, 'femme', 11.28],
        ['Chemise popeline ML femme K542', 'RMR5TD', 'Valento', '--', null, 'femme', 12.82],
        ['Chemise popeline ML Kariban K541', '7ZEO9T', 'Kariban', '--', null, 'homme', 12.82],
        ['Veste Ignifuge Cuiser', 'ROVECUI', 'Roly', '60% modacrylique, 37% coton, 3% fibre de carbone', 220, 'mixte', 26.73],
    ],

    // ===== JOGGING SPORT (6) =====
    'jogging-sport' => [
        ['Leggins Femme et Enfant Leire', 'ROJOLEI', 'Roly', '92% coton, 8% elasthanne', 270, 'femme', 6.99],
        ['Pantalon de sport Argos', 'ROPAARG', 'Roly', '100% polyester interlock', 220, 'mixte', 7.39],
        ['Leggins Bbox', 'ROJOBO', 'Roly', '90% coton, 10% elasthanne', 280, 'femme', 7.66],
        ['Jogging Adulte/enfant New astun', 'ROJONEW', 'Roly', '65% polyester, 35% coton', 290, 'enfant', 9.37],
        ['Jogging Adelpho', 'ROJOGADE', 'Roly', '60% coton, 40% polyester molleton', 280, 'homme', 9.57],
        ['Jogging Adelpho woman', 'ROJOGADEW', 'Roly', '60% coton, 40% polyester molleton', 280, 'femme', 9.57],
    ],

    // ===== JUPES ROBES (2) =====
    'jupes-robes' => [
        ['Coktail', 'SOROCOK', 'Sol\'s', '100% polyester', 130, 'femme', 2.86],
        ['Jupe Patty', 'ROJUPAT', 'Roly', '95% coton / 5% elasthanne', 220, 'femme', 4.37],
    ],

    // ===== MAILLOTS DE BAIN (4) =====
    'maillots-de-bain' => [
        ['Maillot de bain Aqua', 'ROMBAQU', 'Roly', '100% polyester', 100, 'mixte', 4.41],
        ['Maillot de bain Balos', 'ROMBBAL', 'Roly', '100% polyester', 100, 'mixte', 4.68],
        ['Pantalon costume', 'KAPAK730', 'Kariban', '64% polyester / 34% viscose / 2% elasthanne', null, 'mixte', 30.49],
        ['Veste de costume Kariban', 'KAVEK6130', 'Kariban', '64% polyester / 34% viscose / 2% elasthanne (doublure taffetas polyester)', null, 'mixte', 69.08],
    ],

    // ===== MAILLOTS T SHIRTS SPORT (30) =====
    'maillots-t-shirts-sport' => [
        ['Valento Athletic', '225-10115', '', '100% polyester', 160, 'mixte', 1.09],
        ['T-shirt sport enfant Barhein', 'ROTSBARK', 'Roly', '100% polyester interlock gaufre', 135, 'enfant', 1.49],
        ['T-shirt sport Barhein', 'ROTSBAR', 'Roly', '100% polyester interlock gaufre', 135, 'homme', 1.68],
        ['T-shirt sport femme Barhein', 'ROTSBARW', 'Roly', '100% polyester interlock gaufre', 135, 'femme', 1.68],
        ['Debardeur femme Shura', 'ROTSSHU', 'Roly', '100% polyester pique', 140, 'femme', 1.84],
        ['Maillot enfant Indianapolis', 'ROTSINDK', 'Roly', '100% polyester bird\'s eye', null, 'enfant', 1.92],
        ['T-shirt Montecarlo Enfant', 'ROTSMONTK', 'Roly', '100% polyester pique', 150, 'enfant', 1.98],
        ['Debardeur Cawley', 'ROTSCAW', 'Roly', '94% coton, 6% elasthanne', null, 'mixte', 2.03],
        ['Debardeur Andre', '53-31134', '', '100% polyester', 140, 'mixte', 2.03],
        ['Debardeur Interlagos', 'ROTSINTE', 'Roly', '100% polyester microperfore', null, 'mixte', 2.08],
        ['Maillot Suzuka Femme', 'ROTSDETW', 'Roly', 'Polyester bird\'s eye + mesh 3D', 130, 'femme', 2.20],
        ['Maillot Detroit Enfant', 'ROTSDETK', 'Roly', 'Polyester bird\'s eye + mesh 3D', null, 'enfant', 2.20],
        ['Debardeur enfant Aida', 'ROTSAIDK', 'Roly', '92% polyester, 8% elasthanne', 160, 'enfant', 2.26],
        ['Debardeur Texas', 'RODETEX', 'Roly', '100% coton', 155, 'mixte', 2.34],
        ['Debardeur femme Carolina', 'ROTSCAR', 'Roly', '100% coton', 220, 'femme', 2.39],
        ['Debardeur Femme Aida', 'ROTSAID', 'Roly', '92% polyester, 8% elasthanne', 160, 'femme', 2.56],
        ['Sporty Woman', '644-24490', 'Sol\'s', '100% polyester mesh', 140, 'femme', 2.61],
        ['ML polyester unisexe Montecarlo', '54-43774', 'Roly', '100% polyester', 140, 'mixte', 2.73],
        ['T-shirt sport Avus', '951-42926', 'Roly', '92% polyester, 8% elasthanne', 160, 'mixte', 3.38],
        ['T-shirt Shanghai LS', 'ROTSSHLS', 'Roly', '100% polyester', null, 'mixte', 3.40],
        ['Athletico', 'SOTSATH', 'Sol\'s', '100% polyester interlock', 140, 'mixte', 6.07],
        ['Classico', 'SOTSCLA', 'Sol\'s', '100% polyester mesh', 150, 'mixte', 6.32],
        ['T-shirt Prime', 'ROTSPRI', 'Roly', '92% polyamide, 8% elasthanne', 200, 'mixte', 6.97],
        ['T-shirt gardien Porto Enfant', 'ROTSPORK', 'Roly', '100% polyester', 160, 'enfant', 7.10],
        ['T-shirt gardien Porto', 'ROTSPOR', 'Roly', '100% polyester', 160, 'mixte', 7.10],
        ['Sweat technique 1/4 zip Epiro', 'ROTSEPI', 'Roly', '100% polyester', 200, 'mixte', 8.45],
        ['BERLIN MEN', 'SOTSBER', 'Sol\'s', '90% polyester, 10% elasthanne', 275, 'homme', 13.66],
        ['BERLIN WOMEN', 'SOTSBERW', 'Sol\'s', '90% polyester, 10% elasthanne', 275, 'femme', 13.66],
        ['Vetement seconde-peau ML Core', 'NIKSW231', 'Nimbus', '84% polyester, 16% spandex; panneau: 100% polyester', 300, 'mixte', 34.02],
        ['Veste manches courtes Adidas', '611-23212', 'Adidas', '100% polyester', 130, 'mixte', 34.83],
    ],

    // ===== MANTEAUX HIVER (2) =====
    'manteaux-hiver' => [
        ['Veste Nimbus Providence Homme', 'NIVEPRO', 'Nimbus', '100% Nylon; Doublure: 100% Polyester mesh', 700, 'homme', 51.03],
        ['Veste Nimbus Kirkwood', 'NIMVEKIR', 'Nimbus', 'Exterieur: 100% Nylon Taslan (respirant/coupe-vent/impermeable); Interieur: 100% Nylon matelasse; Doublure: 100% Polyester', null, 'mixte', 90.83],
    ],

    // ===== NAPPES SERVIETTES (22) =====
    'nappes-serviettes' => [
        ['W701', 'WETOW701', '', '100% coton', null, 'mixte', 0.98],
        ['P1005L', 'CGTOP1005L', '', '100% coton', 210, 'mixte', 1.15],
        ['P1230', 'CGGAP1230', '', '100% coton', null, 'mixte', 1.31],
        ['OWKK', 'LITAOWKK', '', '100% coton', 220, 'mixte', 1.39],
        ['P1220', 'CGTAP1220', '', '100% coton', 140, 'mixte', 1.60],
        ['Mini', 'ROTABMIN', 'Roly', '80% polyester, 20% coton', 220, 'mixte', 1.78],
        ['Potholder', 'LIMAPH2020', '', '65% polyester, 35% coton', 240, 'mixte', 2.16],
        ['Classic', 'COBOGROBE', 'Continental Clothing', '80% polyester, 20% coton', 220, 'mixte', 2.48],
        ['Orly', 'ROSERORL', 'Roly', '80% polyester, 20% polyamide', 280, 'mixte', 2.52],
        ['Gant OWR', 'LIGAOWR', '', '65% polyester, 35% coton', 240, 'mixte', 3.18],
        ['P1235', 'CGTAP1235', '', '65% coton, 35% polyester', 290, 'mixte', 3.36],
        ['Torchon 2 rayures OFG', 'KATOK138', 'Kariban', '100% coton', null, 'mixte', 3.82],
        ['Essuie-vaisselle Bio', 'KAESK137', 'Kariban', '100% coton bio', null, 'mixte', 3.82],
        ['P1240', 'CGTAP1240', '', '65% coton, 35% polyester', 290, 'mixte', 4.00],
        ['TS8090', 'LITATS8090', '', '80% polyester, 20% coton', null, 'mixte', 5.98],
        ['BG44', 'BAPOBG44', '', '100% Nylon Twill', null, 'mixte', 4.11],
        ['BG861', 'BAPOBG961', '', '100% Nylon Twill', null, 'mixte', 4.21],
        ['CS3060', 'LITACS3060', '', '80% polyester, 20% coton', null, 'mixte', 4.57],
        ['HS8073', 'LITAHS8073', '', '65% polyester, 35% coton', 240, 'mixte', 7.02],
        ['Denim please', 'BCTADEN', 'B&C', '100% coton denim', null, 'mixte', 7.06],
        ['100100z', 'LITA100100', '', '65% polyester, 35% coton', 240, 'mixte', 8.29],
        ['Torchon made in France', 'KATOK136', 'Kariban', 'Melange coton/lin', null, 'mixte', 8.66],
    ],

    // ===== PANIERS RANGEMENT (1) =====
    'paniers-rangement' => [
        ['W580', 'WECUW580', '', '100% coton canvas', null, 'mixte', 10.51],
    ],

    // ===== PANTALONS DE TRAVAIL (17) =====
    'pantalons-de-travail' => [
        ['Pixel', 'VAPANPIX', 'Valento', '65% polyester, 35% coton', 190, 'mixte', 4.70],
        ['Caster', 'VAPANCAS', 'Valento', '65% polyester, 35% coton', 210, 'mixte', 5.54],
        ['Pantalon de travail Robble', 'VAPANROB', 'Valento', '65% polyester, 35% coton', 250, 'mixte', 6.93],
        ['Pantalon poche genoux', 'SNPAADRP', '', '100% coton', 330, 'mixte', 7.92],
        ['Force', 'VAPANFOR', 'Valento', '100% polyester', 270, 'mixte', 8.45],
        ['Pantalon Brick', 'VAHVBRI', 'Valento', '100% coton', 250, 'mixte', 8.45],
        ['Pantalon Femme Daily', 'ROPANTDAILYW', 'Roly', '65% polyester, 35% coton', null, 'femme', 8.82],
        ['Pantalon de travail Adrien', 'SNPAADR', '', '60% coton, 40% polyester', 300, 'mixte', 8.87],
        ['Pantalon de sante Fiber', '1037-50708', 'Roly', '94% polyester, 6% elasthanne', 170, 'mixte', 9.24],
        ['Drill', 'KACHK840', 'Kariban', '65% polyester, 35% coton', 210, 'mixte', 9.24],
        ['Pantalon Train', 'VAHVTRA', 'Valento', '100% polyester', 210, 'mixte', 9.77],
        ['Darko', 'VAPANDAR', 'Valento', '65% polyester, 35% coton', 220, 'mixte', 9.77],
        ['Pantalon de travail Protect', 'ROPANTPRO', 'Roly', '65% polyester, 35% coton', null, 'mixte', 10.20],
        ['Martin', 'VAPANMAR', 'Valento', '95% polyester, 5% elasthanne', 250, 'mixte', 12.32],
        ['Winterfell (pantalon)', '47OGRQ', 'Valento', '65% polyester, 35% coton', 210, 'mixte', 13.44],
        ['Pantalon Ignifuge Ranger', 'ROPARAN', 'Roly', '60% modacrylique, 37% coton, 3% fibre de carbone', 220, 'mixte', 22.28],
        ['Parka Traffic', 'ENZZCV', 'Valento', '--', 200, 'mixte', 23.69],
    ],

    // ===== PANTALONS JOGGINGS BEBE (1) =====
    'pantalons-joggings-bebe' => [
        ['Baby All-In-One', 'BBBABZ25', 'BabyBugz', '80% coton, 20% polyester', null, 'bebe', 9.45],
    ],

    // ===== PANTALONS LONGS (18) =====
    'pantalons-longs' => [
        ['Cosmo', 'VAPANCOS', 'Valento', '65% polyester / 35% coton', 240, 'mixte', 6.20],
        ['Advance', 'VAPANADV', 'Valento', '65% polyester / 35% coton', 210, 'mixte', 6.34],
        ['Chispa', 'VAPANCHI', 'Valento', '100% coton', 250, 'mixte', 6.86],
        ['Legging 7/8 sans couture UE', 'KALEGK7011', 'Kariban', '93% polyamide / 7% elasthanne', null, 'femme', 6.88],
        ['Baby Sweatpants/Joggers', 'BBBABZ33', 'BabyBugz', '80% coton / 20% polyester', null, 'bebe', 4.90],
        ['Pantalon Daily Next', 'ROPANTDAI', 'Roly', '80% polyester / 20% coton', 210, 'mixte', 7.71],
        ['Legging femme sans couture UE', 'KALEGK7010', 'Kariban', '93% polyamide / 7% elasthanne', null, 'femme', 7.79],
        ['Pregon', 'VAPANPRE', 'Valento', '65% polyester / 35% coton', 210, 'mixte', 7.92],
        ['Pantalon de peintre Pinter', 'ROPANTPEI', 'Roly', '65% polyester / 35% coton', 200, 'homme', 8.04],
        ['Pantalon Femme Daily', 'ROPANTDAILYW', 'Roly', '65% polyester / 35% coton', null, 'femme', 8.82],
        ['Pantalon Daily', 'ROPANTDAILY', 'Roly', '65% polyester / 35% coton', null, 'homme', 8.82],
        ['Martin', 'VAPANMAR', 'Valento', '95% coton / 5% elasthanne', 250, 'mixte', 12.32],
        ['Pantalon de travail Protect', 'ROPANTPRO', 'Roly', '65% polyester / 35% coton', null, 'homme', 10.20],
        ['Pantalon femme Hilton', 'ROPANHIL', 'Roly', '97% coton / 3% elasthanne', 280, 'femme', 13.22],
        ['Pantalon Rits', 'ROPANRIT', 'Roly', '95% coton / 5% elasthanne', 260, 'homme', 13.22],
        ['Pantalon molleton ecoresponsable', 'KAPANK7025', 'Kariban', '85% coton / 15% polyester (bio/recycle)', null, 'mixte', 18.16],
        ['Pantalon Kariban 5 poches', 'KAPAK7003', 'Kariban', '97% coton / 3% elasthanne', null, 'homme', 21.42],
        ['Pantalon chino homme UE', 'KAPANK7000', 'Kariban', '97% coton / 3% elasthanne', null, 'homme', 30.22],
    ],

    // ===== PARKAS (6) =====
    'parkas' => [
        ['Parka Europa Homme Enfant', 'ROPAEUR', 'Roly', '100% polyester (exterieur impermeable, interieur matelasse, col polaire)', null, 'enfant', 17.58],
        ['Parka Europa Woman', 'ROPAEURW', 'Roly', '100% polyester (exterieur impermeable, interieur matelasse)', null, 'femme', 17.58],
        ['Parka a Capuche Recyclee', 'KAPARK6152', 'Kariban', '100% polyester recycle, finition peau de peche, doublure taffeta', null, 'mixte', 23.17],
        ['Parka Femme', 'KAPAK6108', 'Kariban', 'Exterieur: 100% polyester Oxford; Interieur: polyester matelasse', 450, 'femme', 25.61],
        ['Parka Enfant', 'KAPAK996', 'Kariban', 'Exterieur: 100% polyester Oxford; Interieur: polyester matelasse', 450, 'enfant', 25.61],
        ['Parka Homme', 'KAPAK677', 'Kariban', 'Exterieur: 100% polyester Oxford; Interieur: polyester matelasse', 450, 'homme', 29.97],
    ],

    // ===== POLAIRES SANS MANCHES (4) =====
    'polaires-sans-manches' => [
        ['Polaire sans manches Bellagio', 'ROPOBEL', 'Roly', '100% polyester, tissu polaire', 290, 'mixte', 7.21],
        ['Gilet polaire sans manches unisexe', 'SOPOGSM', 'Sol\'s', '100% polyester densite accrue', 320, 'mixte', 8.91],
        ['Gilet micropolaire homme', 'RUPO8720M', 'Russell', 'Polaire anti-peluche 100% Polyester', 320, 'homme', 12.82],
        ['Gilet micropolaire femme', 'RUPO8720F', 'Russell', 'Polaire anti-peluche 100% Polyester', 320, 'femme', 12.82],
    ],

    // ===== POLOS BICOLOR (4) =====
    'polos-bicolor' => [
        ['Venur', 'VAPOLVEN', 'Valento', '100% coton', 220, 'mixte', 9.68],
        ['Nautic', 'ROPOLNAU', 'Valento', '100% coton', 220, 'mixte', 9.88],
        ['Server', 'VAPOLSER', 'Valento', '100% coton', 220, 'mixte', 10.23],
        ['Polo Montreal', 'ROPOLMON', 'Roly', '100% coton peigne, maille piquee', 220, 'mixte', 10.42],
    ],

    // ===== POLOS DE SPORT (10) =====
    'polos-de-sport' => [
        ['Polo Homme et enfant Monzha', 'ROPOLMON-7EZ', 'Roly', '100% polyester', 150, 'enfant', 3.07],
        ['Polo Femme Monzha', 'ROPOLMONW', 'Roly', '100% polyester', 150, 'femme', 3.07],
        ['Polo technique Montmelo', 'ROPOLMONT', 'Roly', '100% polyester', 150, 'mixte', 3.66],
        ['Avant', 'VAPOLAVA', 'Valento', '100% coton', 300, 'mixte', 6.05],
        ['Kick', 'VAPOLKIC', 'Valento', '100% coton', 300, 'mixte', 6.07],
        ['Ruck', 'VAPOLRUC', 'Valento', '100% coton', 300, 'mixte', 6.60],
        ['Scrum', 'VAPOLSCR', 'Valento', '100% coton', 300, 'mixte', 6.76],
        ['Polo Rugby Enfant', 'FROPOFR109', 'Front Row', '100% coton', 270, 'enfant', 11.52],
        ['Polo Rugby femme', 'FROPOFR101', 'Front Row', '100% coton', 270, 'femme', 13.16],
        ['Polo Rugby unisexe/Homme', 'FROPOFR100', 'Front Row', '100% coton', 270, 'homme', 16.40],
    ],

    // ===== POLOS MANCHES COURTES (32) =====
    'polos-manches-courtes' => [
        ['Polo Pegaso Child', 'ROPOPEGK', 'Roly', '60% coton / 40% polyester', 190, 'enfant', 3.69],
        ['Valley', 'VAPOLVAL', 'Valento', '100% coton', 220, 'mixte', 5.61],
        ['Polo Centauro', 'ROPOLCENT', 'Roly', '60% polyester / 40% coton', 180, 'mixte', 5.65],
        ['Hawk', 'VAPOLHAW', 'Valento', '100% coton', 220, 'mixte', 5.81],
        ['Polos Star Enfant', 'ROPOSTARK', 'Roly', '100% coton', 200, 'enfant', 4.10],
        ['Polo chine Bowie', 'ROPOBOW', 'Roly', '100% coton', 200, 'mixte', 6.38],
        ['Golf', 'VAPOLGOL', 'Valento', '100% coton', 220, 'mixte', 4.36],
        ['Polo id.001 Woman', 'BCPOID001W', 'B&C', '100% coton ringspun', 180, 'femme', 4.44],
        ['Polo id.001', 'BCPOID001', 'B&C', '100% coton ringspun', 180, 'homme', 4.44],
        ['Polo Pro Femme RX0F', 'RTPORX01F', 'ProRTX', 'Polycotton', 220, 'femme', 4.50],
        ['Polo Pro RX101', 'RTPORX101', 'ProRTX', 'Polycotton', 220, 'homme', 4.50],
        ['Polo Austral', 'ROPOLAUS', 'Roly', '100% coton pique', 180, 'mixte', 4.64],
        ['Polo Imperium', 'ROPOIMP', 'Roly', '100% coton pique', 220, 'homme', 6.93],
        ['Polo Pegaso Femme', 'ROPOPEGW', 'Roly', '60% coton / 40% polyester', 190, 'femme', 4.73],
        ['Polo Pegaso', 'ROPOPEG', 'Roly', '60% coton / 40% polyester', 190, 'homme', 4.73],
        ['Polos Star', 'ROPOSTAR', 'Roly', '100% coton pique', 200, 'homme', 4.82],
        ['Vega', 'VAPOLVEG', 'Valento', '100% coton', 220, 'mixte', 4.82],
        ['Polos Star Femme', 'ROPOSTARW', 'Roly', '100% coton pique', 200, 'femme', 4.82],
        ['Polo Patrol 220g', 'VAPOLPAT', 'Valento', '100% coton ring spun', 220, 'homme', 4.84],
        ['Polo Pique Bio180 Homme', 'KAPOK2025', 'B&C', 'Coton biologique', 180, 'homme', 7.62],
        ['Polo Pique Bio180 Femme', 'KAPOK2026', 'B&C', 'Coton biologique', 180, 'femme', 7.62],
        ['Polo Kariban Enfant', 'KAPOK249', 'Kariban', '100% coton (90% coton/10% viscose pour Oxford Grey)', 220, 'enfant', 7.91],
        ['Polo BBR Prestige Men', 'SOPOPRE', 'BBR', '100% coton pique', 200, 'homme', 8.55],
        ['Polo BBR Prestige Women', 'SOPOPREW', 'BBR', '100% coton pique', 200, 'femme', 8.55],
        ['Polo Heavymill 230g Femme', 'BCPOHEAF', 'B&C', '100% coton peigne', 230, 'femme', 14.40],
        ['Polo Heavymill 230g Homme', 'BCPOHEA', 'B&C', '100% coton peigne', 230, 'homme', 14.40],
        ['Polo Kariban Homme', 'KAPOK241', 'Kariban', '100% coton (lavable a 60 deg C)', 220, 'homme', 14.10],
        ['Polo Kariban Femme', 'KAPOK242', 'Kariban', '100% coton', 220, 'femme', 14.10],
        ['Polo ATF Pierrot Fabrique en France', 'SOPOPIE', 'ATF', '100% coton issu de l\'agriculture biologique', 220, 'mixte', 19.00],
        ['Polo Adidas Performance (Homme)', 'ADPO028', 'Adidas', '100% polyester', 130, 'homme', 28.00],
        ['Polo Adidas Performance Femme', 'ADPO028F', 'Adidas', '100% polyester', 130, 'femme', 28.00],
        ['Polo \'Breathe Colour Block\'', 'NKPO208', 'Nike', '100% polyester (Technologie Dri-FIT)', null, 'mixte', 43.00],
    ],

    // ===== POLOS MANCHES LONGUES (6) =====
    'polos-manches-longues' => [
        ['Polo ML Enfant Carpe', 'ROPOLCARK', 'Roly', '65% polyester / 35% coton, maille piquee', 220, 'enfant', 9.02],
        ['Polo ML Estrella Enfant', 'ROPOESTK', 'Roly', '100% coton (Gris 58: 85% coton / 15% viscose)', 220, 'enfant', 10.15],
        ['Polo Manches longues CARPE', 'ROPOLCAR', 'Roly', '65% polyester / 35% coton, maille piquee', 220, 'mixte', 10.93],
        ['Polo ML Estrella', 'ROPOLEST', 'Roly', '100% coton', 220, 'mixte', 11.09],
        ['Polo Estrella Femme', 'ROPOLESTW', 'Roly', '100% coton', 220, 'femme', 11.09],
        ['Polo ML chine Dylan', 'ROPODYL', 'Roly', '50% coton / 50% polyester, maille piquee', 250, 'mixte', 14.19],
    ],

    // ===== POLOS PRO (4) =====
    'polos-pro' => [
        ['Polo haute visibilite Build', 'VAHVBUI', 'Valento', '100% polyester Move-Dry', 160, 'mixte', 5.81],
        ['Polo haute visibilite Atrio', 'ROPOATR', 'Roly', 'Polyester/coton', null, 'mixte', 12.00],
        ['Polo LS haute visibilite Atrio', 'ROPOATRLS', 'Roly', 'Polyester/coton', null, 'mixte', 14.40],
        ['Polo Ignifuge Santana', 'ROPOSAN', 'Roly', '60% modacrylique, 37% coton, 3% fibre de carbone', 220, 'mixte', 27.54],
    ],

    // ===== POLOS RUGBY (8) =====
    'polos-rugby' => [
        ['Avant', 'VAPOLAVA', 'Valento', '100% coton', 320, 'mixte', 9.17],
        ['Kick', 'VAPOLKIC', 'Valento', '100% coton', 320, 'mixte', 9.20],
        ['Ruck', 'VAPOLRUC', 'Valento', '100% coton', 320, 'homme', 10.00],
        ['Scrum', 'VAPOLSCR', 'Valento', '100% coton', 320, 'homme', 10.24],
        ['Polo Rugby Enfant', 'FROPOFR109', 'Front Row', '100% coton', 270, 'enfant', 14.40],
        ['Polo Rugby Femme', 'FROPOFR101', 'Front Row', '100% coton', 280, 'femme', 16.45],
        ['Polo Rugby Bi Color K213', 'KAPOK213', 'Kariban', '100% coton', 280, 'mixte', 18.12],
        ['Polo Rugby Unisexe/Homme', 'FROPOFR100', 'Front Row', '100% coton', 270, 'homme', 20.50],
    ],

    // ===== POLOS SPORT (3) =====
    'polos-sport' => [
        ['Polo Homme et Enfant Monzha', 'ROPOLMON', 'Roly', '100% polyester', 150, 'enfant', 6.82],
        ['Polo Femme Monzha', 'ROPOLMONW', 'Roly', '100% polyester', 150, 'femme', 6.82],
        ['Polo technique Montmelo', 'ROPOLMONT', 'Roly', '100% polyester', 145, 'mixte', 8.14],
    ],

    // ===== PULLS COL ROND (3) =====
    'pulls-col-rond' => [
        ['Ginger men', 'SOPUFINW', 'Sol\'s', '70% viscose, 30% polyamide', 275, 'homme', 14.11],
        ['Ginger Women', '559-21362', 'Sol\'s', '70% viscose, 30% polyamide', 275, 'femme', 14.11],
        ['Pro Security Sweater', 'RTSWPRO', 'Result', 'Corps 100% acrylique, emplacements 65% polyester / 35% coton', 470, 'mixte', 14.78],
    ],

    // ===== PULLS COL V (5) =====
    'pulls-col-v' => [
        ['Galaxy men', 'SOPUGAL', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 240, 'homme', 13.10],
        ['Galaxy Women', 'SOPUGALW', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 240, 'femme', 13.10],
        ['Glory men', 'SOPUGLO', 'Sol\'s', '70% viscose, 30% polyamide', 275, 'homme', 14.11],
        ['Glory women', 'SOPUGLOW', 'Sol\'s', '70% viscose, 30% polyamide', 275, 'femme', 14.11],
        ['Pull zip Adidas a bandes', 'ADPU030', 'Adidas', '96% polyester, 4% elasthanne', 180, 'mixte', 28.35],
    ],

    // ===== PULLS POLAIRE (5) =====
    'pulls-polaire' => [
        ['Calgary', 'VAPOCAL', 'Valento', '100% polyester', 220, 'mixte', 3.47],
        ['Pull micropolaire enfant', 'ROPUHYMK', 'Hymalaya', '100% polyester, micropolaire', 155, 'enfant', 3.74],
        ['Pull micropolaire', 'ROPUHYM', 'Hymalaya', '100% polyester, micropolaire', 155, 'mixte', 4.64],
        ['Pull micropolaire femme', 'ROPUHYMF', 'Hymalaya', '100% polyester, micropolaire', 155, 'femme', 4.64],
        ['Trekking', 'VAPOTRE', 'Valento', '100% polyester', 220, 'mixte', 4.70],
    ],

    // ===== PULLS SANS MANCHES (1) =====
    'pulls-sans-manches' => [
        ['Gentlemen', 'SOPUGEN', 'Sol\'s', '49% coton, 49% acrylique, 2% polyamide', 280, 'mixte', 9.57],
    ],

    // ===== PYJAMAS BEBE (3) =====
    'pyjamas-bebe' => [
        ['Baby Rompasuit', 'BBBBABZ13', 'BabyBugz', '100% coton peigne (Gris: 85% coton, 15% polyester)', 200, 'bebe', 6.60],
        ['Baby Organic Sleepsuit with Scratch Mitts', 'BBBBABZ35', 'BabyBugz', '100% coton peigne organic', null, 'bebe', 7.34],
        ['Baby Stripy Rompasuit', 'BBBABZ13S', 'BabyBugz', '100% coton peigne', 200, 'bebe', 8.42],
    ],

    // ===== SACOCHES POLYESTER (6) =====
    'sacoches-polyester' => [
        ['Wall Street', 'SOSAWAL', 'Sol\'s', 'Polyester', null, 'mixte', 8.01],
        ['Portfolio Briefcase BG33', 'BG33', '', 'Polyester 600D', null, 'mixte', 9.24],
        ['Sac messager deux tons BG218', 'BG218', '', 'Polyester 600D', null, 'mixte', 12.42],
        ['Sac messager sublimation BG965', 'BG965', '', 'Polyester / 600D polyester', null, 'mixte', 9.45],
        ['Coque iPad/tablette BG973', 'BG973', '', 'Polyester / 600D polyester', null, 'mixte', 10.51],
        ['Transit, sacoche ordinateur', 'SOSATRA', 'Sol\'s', 'Polyester', null, 'mixte', 26.67],
    ],

    // ===== SACS A DOS (19) =====
    'sacs-a-dos' => [
        ['Sac a dos cordon Zorzal', '1020', '', '100% polyester 600D', 150, 'mixte', 0.99],
        ['Campus', 'SOSACAMP', 'Sol\'s', 'N/A', null, 'mixte', 3.42],
        ['Sac a dos festival bio W185', 'WESSACW185', '', '100% coton biologique', 170, 'mixte', 3.51],
        ['BG125J', 'BESABG125J', '', 'Polyester 600D', null, 'mixte', 5.44],
        ['Sac a dos Marabu', '1016', '', '100% polyester 900D', 550, 'mixte', 3.74],
        ['BG14', 'BASABG14', '', 'Polyester 600D', null, 'mixte', 5.54],
        ['Rider (enfant)', '664', 'Sol\'s', '100% polyester', null, 'mixte', 4.04],
        ['Sac a dos Rider', 'SOSARID', 'Sol\'s', 'N/A', null, 'mixte', 4.10],
        ['BG125', 'BESABG125', '', 'Polyester 600D', null, 'mixte', 6.11],
        ['Perry sac a dos feminin', 'SOSAPER', 'Sol\'s', 'Oxford 100% polyester', null, 'mixte', 6.23],
        ['Sac a dos moderne Chucao', 'ROSACCHUC', 'Roly', '100% polyester', 400, 'mixte', 6.27],
        ['Hiker', 'ROSACHIK', 'Roly', '100% polyester 600D/PVC', null, 'mixte', 6.64],
        ['Express sac a dos', 'SOSAEXP', 'Sol\'s', 'Polyester', null, 'mixte', 4.62],
        ['BG145', 'BASABG145', '', '600D polyester', null, 'mixte', 7.02],
        ['BG140S', 'BASABG140S', '', '600D polyester', null, 'mixte', 4.90],
        ['BG140', 'BASABG140', '', '600D polyester', null, 'mixte', 7.34],
        ['Sac a dos Heritage BG825', 'BG825', '', 'Polyester 600D', null, 'mixte', 8.92],
        ['Sac a dos sublimation BG955', 'BG955', '', 'Polyester / 600D polyester', null, 'mixte', 12.04],
        ['Sac a dos Pro Athleisure BG550', 'BG550', '', 'Polyester 600D/420D', null, 'mixte', 13.38],
    ],

    // ===== SACS COTON (27) =====
    'sacs-coton' => [
        ['W103', 'W103', '', '100% coton', 140, 'mixte', 0.59],
        ['Tote bag Roly Mountain', 'ROSAMOUN', 'Roly', '100% coton', 140, 'mixte', 0.73],
        ['W104', 'W104', '', '100% coton', 140, 'mixte', 0.74],
        ['Sac coton ecoresponsable W100', 'W100', '', '100% coton', 100, 'mixte', 0.78],
        ['W115 small', 'W115', '', '100% coton', 140, 'mixte', 0.79],
        ['Tote bags promo', 'RL100', '', '100% coton', 145, 'mixte', 0.81],
        ['Sac grande anse W107', 'W107', '', '100% coton', 140, 'mixte', 0.92],
        ['Sac commerce equitable W101s', 'W101s', '', '100% coton', 140, 'mixte', 0.99],
        ['Sac ecoresponsable W101', 'W101', '', '100% coton', 140, 'mixte', 1.09],
        ['W118', 'W118', '', '100% coton organic', 115, 'mixte', 1.38],
        ['W115 large', 'W115', '', '100% coton', 140, 'mixte', 1.44],
        ['Sac gym W110', 'W110', '', '100% coton', 140, 'mixte', 1.47],
        ['Sac shopping W125', 'W125', '', '100% coton', 140, 'mixte', 1.48],
        ['Sac pour bouteille W620', 'W620', '', '100% toile de coton', 407, 'mixte', 1.76],
        ['W530', 'W530', '', '100% toile de coton brossee', 407, 'mixte', 1.87],
        ['Tote bag bio W180', 'WESSACW180', '', '100% coton biologique', 170, 'mixte', 2.46],
        ['Sac Canvas W108', 'WESSACW108', '', '100% toile de coton', 270, 'mixte', 2.78],
        ['Aurora', 'SOSAAU', 'Sol\'s', '100% coton', 150, 'mixte', 2.99],
        ['W820', 'WESAW820', '', '100% toile de coton biologique brossee', 407, 'mixte', 3.26],
        ['Sac coton epais W671', 'WESSACW671', '', '100% toile de coton', 407, 'mixte', 3.58],
        ['Tribeca', 'SOSATRI', 'Sol\'s', '100% coton', 235, 'mixte', 5.58],
        ['W821', 'WESAW821', '', '100% toile de coton biologique brossee', 407, 'mixte', 3.91],
        ['Sac bio XL W855', 'WESSACW855', '', '100% toile de coton biologique', 340, 'mixte', 5.91],
        ['Brixton', 'SOSABRI', 'Sol\'s', 'Feutrine', 620, 'mixte', 6.11],
        ['Sac coton bio 340g W850', 'W850', '', '100% coton organic', 340, 'mixte', 4.54],
        ['Sac canvas epais W623', 'WESSACW623', '', '100% toile de coton', 407, 'mixte', 4.64],
        ['Sunset', 'SOSASUN', 'Sol\'s', '100% coton jersey', null, 'mixte', 8.11],
    ],

    // ===== SACS POLYESTER (49) =====
    'sacs-polyester' => [
        ['Sac sport Urban', 'ROSWURBW', 'Roly', '100% polyester 210D', null, 'mixte', 0.74],
        ['BG5', 'BG5', '', 'Polyester 210D', 80, 'mixte', 0.98],
        ['BG38', 'BG38', '', 'Polyester 600D', null, 'mixte', 1.14],
        ['College', 'College', 'Sol\'s', 'Polyester bicolore', null, 'mixte', 1.53],
        ['BG46', 'BG46', '', 'Polyester 600D', null, 'mixte', 1.63],
        ['BG901', 'BG901', '', 'Polyester 300D', null, 'mixte', 1.67],
        ['BG40', 'BG40', '', 'Polyester 600D', null, 'mixte', 1.73],
        ['District', 'District', '', 'Canvas', 235, 'mixte', 1.86],
        ['Fame', 'SOSAFAM', 'Sol\'s', '80% coton / 20% polyester', 240, 'mixte', 2.02],
        ['BG944', 'BAPOBG944', '', 'Microfibre polyester / 600D polyester', null, 'mixte', 2.08],
        ['W540', 'WESAW540', '', '100% toile de coton brossee', 407, 'mixte', 2.17],
        ['BG42', 'BASABG42', '', 'Polyester 600D', null, 'mixte', 2.52],
        ['Cambridge', 'SOSACAM', 'Sol\'s', 'N/A', null, 'mixte', 2.73],
        ['BG150', 'BASABG150', '', '210D Polyester', null, 'mixte', 2.86],
        ['BG110', 'BASABG110', '', 'Polyester 300D', 150, 'mixte', 2.92],
        ['JNS21', 'LISAHNS21', '', '80% coton / 20% polyester', null, 'mixte', 2.94],
        ['BG47', '429', '', 'Polyester 600D', null, 'mixte', 3.12],
        ['BG18', 'BASABG18', '', 'Polyester 600D', null, 'mixte', 3.42],
        ['BG540', 'BASABG540', '', 'Polyester 600D/420D', null, 'mixte', 3.61],
        ['BG48', 'BESABG48', '', 'Polyester 600D', null, 'mixte', 3.71],
        ['BG26', 'BASABG26', '', 'Polyester 600D', null, 'mixte', 3.78],
        ['Corporate', 'SOSACOR', 'Sol\'s', 'N/A', null, 'mixte', 3.84],
        ['BG53', 'BASABG53', '', 'Polyester 600D', null, 'mixte', 3.91],
        ['Manathan', 'SOSAMA', 'Sol\'s', 'N/A', null, 'mixte', 5.74],
        ['BG44', 'BAPOBG44', '', 'Polyester 600D', null, 'mixte', 4.11],
        ['Turbo', 'ROSACTUR', 'Roly', '100% polyester 600D', null, 'mixte', 4.14],
        ['Cosmo (reflechissant)', '654', 'Sol\'s', '100% polyester', null, 'mixte', 4.14],
        ['BG861', 'BG861', '', '100% Nylon Twill', null, 'mixte', 4.21],
        ['BG16', 'BG16', '', 'Polyester 600D', null, 'mixte', 4.21],
        ['BG71', 'BG71', '', 'Polyester 600D', null, 'mixte', 6.28],
        ['BG940', 'BG940', '', 'Microfibre / 600D polyester', null, 'mixte', 4.31],
        ['BG21', 'BG21', '', 'Polyester 600D', null, 'mixte', 6.60],
        ['BG227', 'BG227', '', '600D Polyester', null, 'mixte', 6.60],
        ['Laguna sac polochon', 'SOSAPOL', 'Sol\'s', '80% coton / 20% polyester', 240, 'mixte', 6.61],
        ['BG963', 'BESABG963', '', 'Microfibre polyester / 600D polyester', null, 'mixte', 6.81],
        ['BG211', 'BASABG211', '', '600D polyester', null, 'mixte', 7.02],
        ['BG542', 'BASABG542', '', '600D/420D polyester', null, 'mixte', 4.90],
        ['Striker', 'ROSACSTR', 'Roly', '100% polyester 600D/PVC', null, 'mixte', 7.70],
        ['BG175', 'BESABG175', '', '600D polyester', null, 'mixte', 7.87],
        ['BG212', 'BASABG212', '', '600D polyester', null, 'mixte', 8.40],
        ['Sac baril Camo BG173', 'BG173', '', 'Polyester 600D', null, 'mixte', 8.40],
        ['Week end', 'SOSAWEE', 'Sol\'s', 'N/A', null, 'mixte', 9.54],
        ['Sac de sport Freestyle BG200', 'BG200', '', 'Polyester 600D', null, 'mixte', 9.70],
        ['Sac bowling retro BG75', 'BG75', '', 'Polyester 600D', null, 'mixte', 10.30],
        ['Sac de voyage classique BG22', 'BG22', '', 'Polyester 600D', null, 'mixte', 10.51],
        ['Sac sport 72cm', 'SOSASTA', 'Sol\'s', 'Polyester', null, 'mixte', 13.75],
        ['BG613', 'BASABG613', '', 'Polyester 600D', null, 'mixte', 15.40],
        ['BG25', 'BASABG25', '', 'Polyester 600D', null, 'mixte', 24.70],
        ['Sac de sport Adidas', 'ADSA180', 'Adidas', '100% polyester', 467, 'mixte', 31.59],
    ],

    // ===== SERVIETTES BAIN PEIGNOIRS (13) =====
    'serviettes-bain-peignoirs' => [
        ['Gant de toilette', 'ARGAGAN', '', '100% coton', 500, 'mixte', 0.87],
        ['Bubble', 'VASEBUB', 'Valento', '100% coton', 500, 'mixte', 1.04],
        ['Soap', 'VASESOA', 'Valento', '100% coton', 500, 'mixte', 2.03],
        ['Sponge', 'VASESPO', 'Valento', '100% coton', 500, 'mixte', 4.18],
        ['Serviette organic coton', 'KASERK101', 'Kariban', '100% coton bio', 450, 'mixte', 4.87],
        ['Serviette de plage Bio', 'KASERK102', 'Kariban', '100% coton bio', 450, 'mixte', 7.93],
        ['Serviette Bio', 'KASEK100', 'Kariban', '100% coton bio', null, 'mixte', 9.04],
        ['Serviettes full print 30x50', 'MESE3050', '', '60% coton, 40% microfibre', 400, 'mixte', 10.40],
        ['Peignoir de bain a capuche Bio', 'KAPEIK140', 'Kariban', '100% coton', 270, 'mixte', 14.70],
        ['Serviettes full print 50x100', 'MESE50100', '', '60% coton, 40% microfibre', 400, 'mixte', 18.48],
        ['Serviettes full print 70x140', 'MESE70140', '', '60% coton, 40% microfibre', 400, 'mixte', 33.66],
        ['Serviettes full print 80x160 (x5)', 'MESE80160', '', '60% coton, 40% microfibre', 400, 'mixte', 39.60],
        ['Serviettes full print 100x180 (x5)', 'MESE100180', '', '60% coton, 40% microfibre', 400, 'mixte', 52.80],
    ],

    // ===== SHORTS BERMUDAS (5) =====
    'shorts-bermudas' => [
        ['Short court Nelly femme et enfant', 'ROSHONEL', 'Roly', '100% coton, maille single jersey', 210, 'femme', 2.66],
        ['Lake', 'VASHLAK', 'Valento', 'Poly-coton', 240, 'mixte', 6.86],
        ['Bermuda Ringo', 'ROBERRIN', 'Roly', '97% coton / 3% elasthanne', 290, 'mixte', 7.92],
        ['Bermuda Amazonas', 'ROBERAMA', 'Roly', '100% coton', 200, 'mixte', 8.32],
        ['Bermuda chino homme UE', 'KAPANK7001', 'Kariban', '97% coton / 3% elasthanne', null, 'homme', 22.20],
    ],

    // ===== SHORTS SPORT (5) =====
    'shorts-sport' => [
        ['Short polyester Adulte et Enfant', 'ROSHOPLA', 'Roly', '100% polyester', 140, 'enfant', 2.19],
        ['Short Coton Adulte et enfant Sport', 'ROSHOSPO', 'Roly', '100% coton (Gris: 85% coton, 15% viscose)', 200, 'enfant', 3.85],
        ['Legging Carla Adulte et enfant', 'ROSHOCAR', 'Roly', '92% coton, 8% elasthanne', 270, 'femme', 4.50],
        ['Short sport Andy', 'ROSHOAND', 'Roly', '100% polyester', 130, 'mixte', 6.03],
        ['Short Spiro', 'ROSHOSPI', 'Roly', '65% polyester, 35% coton Terry', 290, 'mixte', 6.07],
    ],

    // ===== SLIPS CALECONS (8) =====
    'slips-calecons' => [
        ['Fenix', 'VASVFEN', 'Valento', '80% polyester / 10% nylon / 6% elasthanne / 4% autre', null, 'mixte', 0.69],
        ['Zeus', 'VASVZEU', 'Valento', '90% coton / 10% elasthanne', 180, 'mixte', 1.39],
        ['Discovery', 'VASVDIS', 'Valento', '90% coton / 10% elasthanne', 180, 'mixte', 1.49],
        ['Boxer Bio homme', 'KABOK804', 'Kariban', 'Coton biologique', 170, 'homme', 3.51],
        ['Shorty woman', 'PRUN8000', '', '95% coton / 5% elasthanne', 180, 'femme', 8.00],
        ['Slip lot de 2', 'FOUN67018', 'Fruit of the Loom', '95% coton / 5% elasthanne', null, 'femme', 6.19],
        ['Boxer lot de 2 (variant 1)', 'FOUN67027', 'Fruit of the Loom', '95% coton / 5% elasthanne', null, 'homme', 7.64],
        ['Boxer lot de 2 (variant 2)', 'FOUN67026', 'Fruit of the Loom', '95% coton / 5% elasthanne', null, 'homme', 8.47],
    ],

    // ===== SOFTSHELLS (15) =====
    'softshells' => [
        ['Tundra', 'VASOTUN', 'Valento', '90% polyester / 10% elasthanne', 350, 'mixte', 15.84],
        ['Softshell sans manches Nevada', 'ROSOFNEV', 'Roly', '92% polyester / 8% elasthanne', 300, 'mixte', 17.95],
        ['Softshell Antartida Enfant', 'ROSOFANTK', 'Roly', '92% polyester / 8% elasthanne', 300, 'enfant', 18.84],
        ['Softshell Horizon', 'VASOHOR', 'Valento', '85% polyester / 10% PU / 5% elasthanne', 350, 'mixte', 19.32],
        ['Softshell Nebraska', 'ROSONEB', 'Roly', '92% polyester / 8% elasthanne', 300, 'homme', 19.82],
        ['Softshell Nebraska Femme', 'ROSONEBF', 'Roly', '92% polyester / 8% elasthanne', 300, 'femme', 19.82],
        ['Softshell Antartida', 'ROSOFANT', 'Roly', '92% polyester / 8% elasthanne', 300, 'homme', 21.12],
        ['Softshell Antartida Woman', 'ROSOFANTW', 'Roly', '92% polyester / 8% elasthanne', 300, 'femme', 21.12],
        ['Softshell Rudolph', 'ROSOFRUD', 'Roly', '95% polyester / 5% elasthanne (3 couches)', 300, 'mixte', 23.64],
        ['Softshell Saponi', 'vasosap', 'Valento', '85% polyester / 10% PU / 5% elasthanne', 350, 'mixte', 26.84],
        ['Softshell Regatta', 'RESOFRG701', 'Result', '100% polyester', 300, 'mixte', 28.35],
        ['Softshell Replay Man', 'SOSOFREP', 'Sol\'s', '94% polyester / 6% elasthanne; doublure polaire 100% polyester', 320, 'homme', 29.59],
        ['Softshell Replay Woman', 'SOSOFREPW', 'Sol\'s', '94% polyester / 6% elasthanne; doublure polaire 100% polyester', 320, 'femme', 29.59],
        ['Blummer', 'VASOBLU', 'Valento', '90% polyester / 10% elasthanne', 525, 'mixte', 30.24],
        ['Softshell Betavia', 'VASOFBET', 'Valento', '85% polyester / 10% PU / 5% elasthanne', 350, 'mixte', 31.92],
    ],

    // ===== SWEATS BEBE (5) =====
    'sweats-bebe' => [
        ['Sweat a capuche SupaSoft bebes', 'DAVDHY', 'Valento', '80% coton ringspun, 20% polyester', 280, 'bebe', 6.02],
        ['Baby Sweatshirt', 'BBBABZ31', 'BabyBugz', '80% coton, 20% polyester (Grey: 84% coton, 16% polyester)', null, 'bebe', 6.28],
        ['Baby Hoodie', 'BBBBABZ32', 'BabyBugz', '80% coton, 20% polyester', null, 'bebe', 7.34],
        ['Baby Bomber Jacket', 'BBBABZ40', 'BabyBugz', '80% coton peigne organique GOTS, 20% polyester', 200, 'bebe', 9.45],
        ['Baby All-In-One', 'BBBABZ25', 'BabyBugz', '80% coton, 20% polyester', null, 'bebe', 9.45],
    ],

    // ===== SWEATS CAPUCHE (26) =====
    'sweats-capuche' => [
        ['Sweat a capuche SupaSoft pour bebes', 'JH02B', 'AWDis', '80% coton, 20% polyester', 280, 'bebe', 6.02],
        ['Baby Hoodie', 'BBBBABZ32', 'BabyBugz', '80% coton, 20% polyester', 280, 'bebe', 7.34],
        ['Petros (capuche)', 'ROSWPET', 'Roly', '65% polyester, 35% coton', 290, 'mixte', 8.42],
        ['Sweat a capuche de baseball enfant', 'ASSWJH09', 'AWDis', '80% coton, 20% polyester', 280, 'enfant', 8.45],
        ['Sweat a capuche enfant', 'JH01J', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'enfant', 8.45],
        ['Amandus', 'ROSWAMA', 'Roly', '100% coton slub Terry fleece', 250, 'mixte', 8.94],
        ['Sweat a capuche Girlies College', 'AWSWJH001F', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'femme', 9.36],
        ['Sweat a capuche College', 'AWSWJH001', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 9.44],
        ['Sweat a capuche de baseball', 'AWSWJH009', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 9.66],
        ['Sweat a capuche polyester Arizona', 'VASWARI', 'Valento', '100% polyester', 280, 'mixte', 9.90],
        ['Sweat a capuche Capucha', 'ROSWCAP', 'Roly', '65% polyester, 35% coton', 280, 'mixte', 10.23],
        ['Sweat a capuche SG27', 'SGSWS27', 'SG', '80% coton peigne, 20% polyester', 280, 'mixte', 10.23],
        ['Sweat ecoresponsable a capuche enfant', 'KASWK4029', 'Kariban', '85% coton, 15% polyester', 280, 'enfant', 10.43],
        ['Sweat contraste Urban', 'ROSWURB', 'Roly', '65% polyester, 35% coton', 280, 'mixte', 11.36],
        ['Urban Woman', 'WEZ4HP', 'Roly', '65% polyester, 35% coton', 280, 'femme', 11.36],
        ['Sweat ID003', 'GOV4ST', 'B&C', '80% coton, 20% polyester', 280, 'mixte', 11.76],
        ['Sweat a capuche Varsity', 'AWSWJH003', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 11.98],
        ['Camo hoodies', 'AWSWJH014', 'AWDis', '70% coton, 30% polyester', 280, 'mixte', 13.98],
        ['Sweat a capuche de sport en polyester', 'AWSWJH006', 'AWDis', '100% polyester', 200, 'mixte', 14.11],
        ['Sweat Kariban femme coton bio/poly', 'KASWK483', 'Kariban', '80% coton bio, 20% polyester', 280, 'femme', 14.24],
        ['Sweat Kariban homme coton bio/poly', 'KASWK482', 'Kariban', '80% coton bio, 20% polyester', 280, 'homme', 14.24],
        ['Sweat ecoresponsable a capuche femme', 'KASWK4028', 'Kariban', '85% coton, 15% polyester', 280, 'femme', 14.34],
        ['Sweat ecoresponsable a capuche homme', 'KASWK4027', 'Kariban', '85% coton, 15% polyester', 280, 'homme', 14.34],
        ['Sweat a capuche Vinson', 'ROSWVIN', 'Roly', '60% coton bio peigne, 40% polyester recycle', 300, 'mixte', 15.62],
        ['B&C hooded', 'BCSWHOO', 'B&C', '80% coton peigne, 20% polyester', 280, 'mixte', 17.58],
        ['Sweat ATF Gabriel (made in France)', 'SOSWGAB', 'Sol\'s (ATF)', '80% coton bio, 20% polyester recycle', null, 'mixte', 25.92],
    ],

    // ===== SWEATS COL ROND (22) =====
    'sweats-col-rond' => [
        ['Sweat Academy col rond enfant', 'AWSWACAE', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'enfant', 5.28],
        ['Sweat-shirt ecoresponsable enfant', 'KASWK4026', 'Kariban', '85% coton bio, 15% polyester recycle', null, 'enfant', 5.74],
        ['Sweat Academy col V enfant', 'AWSWACVE', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'enfant', 5.91],
        ['Rango', 'VASWRAN', 'Valento', '50% coton, 50% polyester', 295, 'mixte', 6.02],
        ['Baby Sweatshirt', 'BBBABZ31', 'BabyBugz', '80% coton, 20% polyester', null, 'bebe', 6.28],
        ['Sweat col rond enfant', 'AWSWJH030J', 'AWDis', '80% coton, 20% polyester', 280, 'enfant', 6.47],
        ['Sweat col rond Enfant Clasica', 'ROSWCLAK', 'Roly', '50% coton, 50% polyester', 280, 'enfant', 6.47],
        ['Sweat Academy col rond adulte', 'AWSWACAA', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 6.55],
        ['Sweat col rond enfants', 'ASSWJH30J', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'enfant', 4.55],
        ['Sweat col V Academy', 'AWSWACVA', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 7.18],
        ['Sweatshirt col rond', 'AWSWJH030', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 7.85],
        ['Sweat col rond Unisexe Clasica', 'ROSWCLA', 'Roly', '50% coton, 50% polyester', 280, 'mixte', 8.18],
        ['Petros', 'ROSWPET', 'Roly', '65% polyester, 35% coton', 290, 'mixte', 8.42],
        ['Sweat col rond SET IN', 'BCSWSET', 'B&C', '80% coton peigne ringspun, 20% polyester', 280, 'mixte', 9.02],
        ['Malone', 'ROSWMAL', 'Roly', '65% polyester, 35% coton slub', 290, 'homme', 9.74],
        ['Malone woman', 'ROSWMAL', 'Roly', '65% polyester, 35% coton slub', 290, 'femme', 9.74],
        ['Sweat roundneck Clique', 'CLSWROU', 'Clique', '65% polyester, 35% coton', 280, 'mixte', 12.32],
        ['Sweat-shirt ecoresponsable unisexe', 'KASWK4025', 'Kariban', '85% coton bio, 15% polyester recycle', null, 'mixte', 14.00],
        ['Pro Security Sweater', 'RTSWPRO', 'Result', '100% acrylique (corps)', 470, 'mixte', 14.78],
        ['Sweat-shirt col rond Kariban', 'KASWK4007', 'Kariban', '100% coton', 300, 'mixte', 15.48],
        ['Sweat droit col rond Open Hem', 'BCSWHEMM', 'B&C', '80% coton peigne, 20% polyester', null, 'mixte', 16.38],
        ['Sweat ALIX made in France', 'SOSWALI', 'Sol\'s', '80% coton biologique, 20% polyester recycle', null, 'mixte', 24.71],
    ],

    // ===== SWEATS PULLS PRO (8) =====
    'sweats-pulls-pro' => [
        ['Rango', '263-11942', '', '50% polyester, 50% coton', 295, 'mixte', 6.02],
        ['Thunder', 'VASWTHU', 'Valento', '50% polyester, 50% coton', 310, 'mixte', 7.92],
        ['Comando', 'VAPUCOM', 'Valento', '100% coton', 475, 'mixte', 10.14],
        ['Driver', 'VAPUDRI', 'Valento', '100% coton', 475, 'mixte', 13.10],
        ['Arce', 'VAPUAR', 'Valento', '100% coton', 525, 'mixte', 14.11],
        ['Sweat-shirt col rond Kariban', 'KASWK4007', 'Kariban', '100% coton exterieur', null, 'mixte', 15.48],
        ['Sweat Ignifuge Defender', 'ROSWDEF', 'Roly', '60% modacrylique, 39% coton, 1% carbone', 220, 'mixte', 22.28],
        ['Sweat ALIX made in France', '985-47139', '', '80% coton bio, 20% polyester recycle', null, 'mixte', 24.71],
    ],

    // ===== SWEATS SANS MANCHES (1) =====
    'sweats-sans-manches' => [
        ['Veste a manches courtes Adidas', 'ADSW031', 'Adidas', '100% polyester', 130, 'mixte', 43.00],
    ],

    // ===== SWEATS ZIPPES (6) =====
    'sweats-zippes' => [
        ['Thunder', 'VASWTHU', 'Valento', '50% coton, 50% polyester', 310, 'mixte', 7.92],
        ['Sweat-shirt zippe Classic 80/20 Enfant', 'FOSW62005', 'Fruit of the Loom', '80% coton, 20% polyester', 280, 'enfant', 8.18],
        ['Sweat zippé Ulan', 'ROSWCAI', 'Roly', '50% coton, 50% polyester', 280, 'mixte', 10.16],
        ['Sweat-shirt zippe Fresher', 'AWSWJH047', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 12.21],
        ['Sweat zippe Spider Men', 'BCSWZIPM', 'B&C', '80% coton peigne, 20% polyester', 280, 'homme', 17.69],
        ['Pull zip Adidas a bandes', 'ADPU030', 'Adidas', '96% polyester, 4% elasthanne', 180, 'mixte', 28.35],
    ],

    // ===== SWEATS ZIPPES CAPUCHE (9) =====
    'sweats-zippes-capuche' => [
        ['Sweat zippe court Girlie', 'ASSWJH056', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'femme', 9.66],
        ['Montblanc', 'ROSWMON', 'Roly', '65% polyester, 35% coton', 280, 'mixte', 12.00],
        ['Sweat zip capuche varsity JH053', 'AWSWJH53', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 13.33],
        ['Sweat-shirt zippe a capuche', 'ASSWJH050', 'AWDis', '80% coton ringspun, 20% polyester', 280, 'mixte', 13.60],
        ['Sweat zippe ecoresponsable homme', 'KASWK4030', 'Kariban', '85% coton bio, 15% polyester recycle', 280, 'homme', 16.21],
        ['Sweat zippe ecoresponsable femme', 'KASWK4031', 'Kariban', '85% coton bio, 15% polyester recycle', 280, 'femme', 16.21],
        ['Hooded Full Zip /women', 'BCSWHFZW', 'B&C', '80% coton peigne, 20% polyester', 280, 'femme', 16.34],
        ['Sweat capuche zippe homme', 'BCSWHFM', 'B&C', '80% coton peigne, 20% polyester', 280, 'homme', 16.34],
        ['Sweat a capuche zippe bio poches laterales 320g', 'COSWEP61Z', 'Continental Clothing', 'Coton bio / polyester recycle', 320, 'mixte', 16.60],
    ],

    // ===== T SHIRTS BEBE (7) =====
    't-shirts-bebe' => [
        ['Pupy', 'VABEPUP', 'Valento', '100% coton', 160, 'bebe', 0.74],
        ['T-shirt baby Roly', 'ROBEBAB', 'Roly', '100% coton peigne', 160, 'bebe', 1.75],
        ['T-shirt mariniere bebe', 'SOTSMILEB', 'Sol\'s', '98% coton, 2% viscose', 150, 'bebe', 3.11],
        ['Baby T-Shirt', 'BBBABZ02', 'BabyBugz', '100% coton peigne', 200, 'bebe', 3.22],
        ['Baby Long Sleeve Top', 'BBBABZ11', 'BabyBugz', '100% coton peigne', 200, 'bebe', 3.91],
        ['T-shirt mariniere ML', '852-36463', 'Sol\'s', '100% coton peigne', 150, 'bebe', 4.91],
        ['T-shirt mariniere femme ML', '853-36487', 'Sol\'s', '100% coton peigne', 150, 'femme', 4.91],
    ],

    // ===== T SHIRTS BICOLOR (20) =====
    't-shirts-bicolor' => [
        ['Combi Valento', 'OPFN1Q', 'Valento', '100% coton', 160, 'enfant', 1.73],
        ['Caiman', 'VATSCAI', 'Valento', '100% coton', 160, 'enfant', 1.73],
        ['Server', 'BHULPN', 'Valento', '100% coton', 160, 'mixte', 1.88],
        ['Soldier', 'VATSSOL', 'Valento', '100% coton', 160, 'mixte', 1.98],
        ['T-shirt sport Tokyo', 'ROTSTOK', 'Roly', '100% polyester', 140, 'mixte', 2.34],
        ['T-shirt Ringer Fruit of the Loom', 'FRUTS61168', 'Fruit of the Loom', '100% coton', 165, 'mixte', 2.67],
        ['T-shirt Mariniere Bebe', 'SOTSMILEB', 'Sol\'s', '100% coton peigne Ringspun', 150, 'bebe', 3.11],
        ['T-shirt mariniere enfant', 'SOTSMILEE', 'Sol\'s', '98% coton / 2% viscose', 150, 'enfant', 3.66],
        ['Baby Baseball T', 'BBBABZ43', 'BabyBugz/Stanley Stella', '100% coton bio', 200, 'bebe', 3.71],
        ['T-shirt marin bio enfant', 'KATSK3035', 'Kariban', '100% coton bio', 150, 'enfant', 5.60],
        ['T-shirt Joplin Tie-dye', 'ROTSJOP', 'Roly', '100% coton', 160, 'mixte', 5.65],
        ['T-shirt pro Expedition', 'ZZTSEXP', 'Roly', '65% polyester / 35% coton', 160, 'mixte', 4.14],
        ['Baby Stripy Long Sleeve T', '487-16646', 'BabyBugz/Stanley Stella', '100% coton', 200, 'bebe', 4.28],
        ['T-shirt mariniere', 'SOTSMILE', 'Sol\'s', '98% coton / 2% viscose', 150, 'homme', 6.43],
        ['T-shirt mariniere femme', 'SOTSMILEW', 'Sol\'s', '98% coton / 2% viscose', 150, 'femme', 6.43],
        ['Baby Stripy T', 'BBBABZ38', 'BabyBugz/Stanley Stella', '100% coton', 200, 'bebe', 4.70],
        ['T-shirt mariniere manche longue', 'SOTSMARI', 'Sol\'s', '100% coton bio', 150, 'homme', 4.91],
        ['T-shirt mariniere femme manche longue', 'SOTSMARIF', 'Sol\'s', '100% coton bio', 150, 'femme', 4.91],
        ['T-shirt marin bio', 'KATSK3033', 'Kariban', '100% coton bio', 150, 'homme', 7.58],
        ['T-shirt marin bio femme', 'KATSK3034', 'Kariban', '100% coton bio', 150, 'femme', 7.58],
    ],

    // ===== T SHIRTS COL ROND COTON (60) =====
    't-shirts-col-rond-coton' => [
        ['Pupy', 'VABEPUP', 'Valento', '100% coton', 160, 'enfant', 0.74],
        ['Eagle', 'ROTSBEA', 'Valento', '100% coton', 160, 'enfant', 1.58],
        ['T-shirt ATOMIC 150', 'ROTSATO', 'Roly', '100% coton, maille single', 150, 'mixte', 1.71],
        ['T-shirt baby Roly', 'ROBEBAB', 'Roly', '100% coton peigne, single jersey', 160, 'bebe', 1.75],
        ['T-shirt Braco 190g Enfant', 'ROTSBRAK', 'Roly', '100% coton peigne, single jersey', 190, 'enfant', 1.98],
        ['T-shirt Guadalupe', 'ROTSGUA', 'Roly', '100% coton (Gris: 85% coton/15% viscose)', 155, 'femme', 2.05],
        ['Regent kids', 'SOTSREGK', 'Sol\'s', '100% coton semi-peigne Ringspun', 150, 'enfant', 2.07],
        ['T-shirt Stafford Enfant', 'ROTSSTAK', 'Roly', '100% coton carde (Gris: 85%/15% viscose)', 190, 'enfant', 2.08],
        ['Racing', 'VATSRAC', 'Valento', '100% coton', 160, 'enfant', 2.10],
        ['T-shirt femme et enfant Jamaica', 'ROTSJAM', 'Roly', '100% coton, single jersey', 155, 'femme', 2.16],
        ['T-shirt Valueweight', 'FRTSVAL', 'Fruit of the Loom', '100% coton (Ash: 99%/1% polyester)', 165, 'homme', 2.21],
        ['Regent', '29 / SOTSIM', 'Sol\'s', '100% coton semi-peigne Ringspun', 150, 'homme', 2.30],
        ['T-shirt coton bio Basset Woman', '841', 'Roly', '100% coton bio', 170, 'femme', 2.32],
        ['T-shirt coton bio Basset', 'ROTSBES', 'Roly', '100% coton bio', 170, 'mixte', 2.32],
        ['Beagle', '33', 'Roly', '100% coton peigne', 155, 'mixte', 2.37],
        ['Imperial kids', 'SOTSIMPK', 'Sol\'s', '100% coton semi-peigne Ringspun', 190, 'enfant', 2.41],
        ['T-shirt Dogo premium', 'ROTSDOG', 'Roly', '100% coton', 165, 'mixte', 2.43],
        ['T-shirt femme Capri', 'ROTSCAP', 'Roly', '100% coton', 170, 'femme', 2.49],
        ['Funky', 'SOTSFUN', 'Sol\'s', '100% coton semi-peigne Ringspun', 150, 'mixte', 2.49],
        ['Milky', '629', 'Sol\'s', '100% coton semi-peigne Ringspun', 150, 'mixte', 2.49],
        ['T-shirt Imperial Sol\'s', '29 / SOTSIM', 'Sol\'s', '100% coton semi-peigne Ringspun', 190, 'homme', 2.61],
        ['Imperial woman', 'SOTSIMPW', 'Sol\'s', '100% coton semi-peigne Ringspun', 190, 'femme', 2.61],
        ['T-shirt femme Cies', 'ROTSCIE', 'Roly', '100% coton', 165, 'femme', 2.66],
        ['Organic T Adulte et enfant', '637 / SOTSORGT', 'Sol\'s', '100% coton peigne bio certifie OCS', 160, 'enfant', 2.67],
        ['Organic T Woman', 'KJOKZX', 'Sol\'s', '100% coton peigne bio', 160, 'femme', 2.67],
        ['T-shirt Golden Enfant', 'ROTSGOLK', 'Roly', '100% coton bio peigne', 170, 'enfant', 2.69],
        ['T-shirt croptop Dominicia', 'ROTSDOM', 'Roly', '100% coton', 170, 'femme', 2.70],
        ['T-shirt Terrier', 'ROTSTER', 'Roly', '100% coton', 150, 'femme', 2.72],
        ['T-shirt Teckel (avec poche)', 'ROTSTEC', 'Roly', '100% coton', 160, 'mixte', 2.86],
        ['Bali', 'ROTSBAL', 'Roly', '95% coton / 5% elasthanne', 200, 'femme', 2.91],
        ['T-shirt Braco 190g', 'ROTSBRA', 'Roly', '100% coton peigne', 190, 'homme', 3.02],
        ['T-shirt Stafford Adulte', 'ROTSSTA', 'Roly', '100% coton (Gris: 85%/15% viscose)', 190, 'homme', 3.06],
        ['T-shirt allonge Collie', 'ROTSCOL', 'Roly', '100% coton', 155, 'mixte', 3.07],
        ['T-shirt mariniere bebe', 'SOTSMILEB', 'Sol\'s', '100% coton peigne Ringspun', 150, 'bebe', 3.11],
        ['T-shirt bio 190g', 'KATSK30321C', 'Roly', '100% coton bio', 190, 'mixte', 3.24],
        ['T-shirt Golden Femme', 'ROTSGOLF', 'Roly', '100% coton bio peigne', 160, 'femme', 3.47],
        ['T-shirt Golden Homme', 'ROTSGOL', 'Roly', '100% coton bio peigne', 170, 'homme', 3.47],
        ['Women\'s No Label Organic T', 'X0XLA2', 'Stanley/Stella (via B&C)', '100% coton bio peigne', 160, 'femme', 3.47],
        ['Men\'s No Label Organic T', 'QANRGE', 'Stanley/Stella (via B&C)', '100% coton bio peigne', 160, 'homme', 3.47],
        ['T-shirt mariniere enfant', 'SOTSMILEE', 'Sol\'s', '100% coton peigne', 150, 'enfant', 3.66],
        ['T-shirt marin bio enfant', 'KATS-K3033', 'Kariban', '100% coton bio', 150, 'enfant', 5.60],
        ['Camo men', 'SOTSCAMW', 'Sol\'s', '100% coton semi-peigne', 150, 'homme', 5.74],
        ['Camo women', 'SRLDVS', 'Sol\'s', '100% coton semi-peigne', 150, 'femme', 5.74],
        ['T-shirt bio Breda Unisexe et enfant', 'ROTSBRE', 'Roly', '100% coton bio (Gris: 85%/15% viscose)', 175, 'enfant', 4.10],
        ['T-shirt mariniere femme', 'SOTSMILEW', 'Sol\'s', '100% coton peigne', 150, 'femme', 6.43],
        ['T-shirt mariniere', 'SOTSMILE', 'Sol\'s', '100% coton peigne', 150, 'homme', 6.43],
        ['T-shirt organic 180g Inspire+', 'TM048', 'B&C', '100% coton bio ring-spun', 175, 'homme', 4.45],
        ['T-shirt organic FEMME 180g Inspire+', 'TM048F', 'B&C', '100% coton bio ring-spun', 175, 'femme', 4.45],
        ['Women\'s Organic Roll Sleeve T', 'BCTSTW43', 'Stanley/Stella', '100% coton bio', 150, 'femme', 4.80],
        ['T-shirt Husky femme', 'JJAPOZ', 'Roly', '100% coton (effet denim)', 165, 'femme', 4.83],
        ['T-shirt Husky', 'ROTSHUS', 'Roly', '100% coton (effet denim)', 165, 'homme', 4.83],
        ['T-shirt mariniere femme manche longue', 'SOTSMARIF', 'Sol\'s', '100% coton peigne', 150, 'femme', 4.91],
        ['T-shirt mariniere manche longue', 'SOTSMARI', 'Sol\'s', '100% coton peigne', 150, 'homme', 4.91],
        ['T-shirt marin bio femme', 'KATSK3034', 'Kariban', '100% coton bio', 150, 'femme', 7.58],
        ['T-shirt marin bio', 'KATSK3033', 'Kariban', '100% coton bio', 150, 'homme', 7.58],
        ['T-shirt enfant made in France', 'AFTTSENF', 'Confection francaise', '100% coton peigne', 150, 'enfant', 13.35],
        ['T-shirts coton bio homme Made in France', 'LTFTSHO', 'Confection francaise', '100% coton bio', 160, 'homme', 13.44],
        ['T-shirts coton bio femme Made in France', 'LTFTSHF', 'Confection francaise', '100% coton bio', 160, 'femme', 13.44],
        ['T-shirt femme made in France', 'AFTTSFEM', 'Confection francaise', '100% coton peigne', 150, 'femme', 13.90],
        ['T-shirt Homme made in France', 'AFTTSHO', 'Confection francaise', '100% coton peigne', 150, 'homme', 13.90],
    ],

    // ===== T SHIRTS COL V (7) =====
    't-shirts-col-v' => [
        ['Sun', 'ROVASUN', 'Valento', '100% polyester', 160, 'mixte', 1.45],
        ['Cobra', 'VATSCOB', 'Valento', '90% coton / 10% elasthanne', 190, 'mixte', 2.03],
        ['T-shirt Victoria', 'TOTSVIC', 'Roly', '100% coton, maille single', 155, 'femme', 2.06],
        ['T-shirt col V Samoyedo', 'ROTSSAM', 'Roly', '100% coton, single jersey', 155, 'mixte', 2.54],
        ['Men\'s No Label Organic T V-Neck', 'BCTSTW44', 'Stanley/Stella', '100% coton bio peigne', 160, 'homme', 3.47],
        ['Woman\'s No Label Organic T V-Neck', '531-17537', 'Stanley/Stella', '100% coton bio peigne', 160, 'femme', 3.47],
        ['T-shirt col fendu Belice', 'ROTSBEL', 'Roly', '95% coton peigne / 5% elasthanne', 200, 'mixte', 3.69],
    ],

    // ===== T SHIRTS MANCHES LONGUES (13) =====
    't-shirts-manches-longues' => [
        ['T-shirt Baby LS', 'ROTSBABLS', 'Roly', '100% coton peigne, single jersey', 160, 'bebe', 1.84],
        ['T-shirt Extreme enfant', 'ROTSEXTK', 'Roly', '100% coton, maille single (Gris: 85%/15% viscose)', 160, 'enfant', 2.31],
        ['ML polyester Montecarlo', '54-43774-EA7', 'Roly', '100% polyester', 140, 'enfant', 2.73],
        ['T-shirt Pointer enfant', 'ROTSPOINK', 'Roly', '100% coton, single jersey', 165, 'enfant', 2.75],
        ['T-shirt ML Extreme', 'ROTSEXT', 'Roly', '100% coton, maille single', 160, 'homme', 3.65],
        ['T-shirt ML Extreme woman', 'ROTSEXTW', 'Roly', '100% coton, maille single', 160, 'femme', 3.65],
        ['T-shirt manches longues Pointer', 'ROTSPOIN', 'Roly', '100% coton, single jersey', 165, 'homme', 3.85],
        ['T-shirt ML poche Shiba', 'ROTSSHI', 'Roly', '100% coton', 165, 'mixte', 3.89],
        ['Imperial manches longues femme', '632-23801', 'Sol\'s', '100% coton semi-peigne Ringspun', 190, 'femme', 5.81],
        ['Imperial manches longues', 'SOTSIMPLS', 'Sol\'s', '100% coton semi-peigne Ringspun', 190, 'homme', 5.81],
        ['T-shirt Marin ML Femme', '635-23980', 'Sol\'s', '100% coton peigne Ringspun', 150, 'femme', 8.94],
        ['T-shirt Marin Manches Longues', 'SOTSMARML', 'Sol\'s', '100% coton peigne Ringspun', 150, 'homme', 8.94],
        ['T-shirt Modacrylique', 'ROTSMODAC', 'Kariban', '60% modacrylique / 39% coton / 1% carbone', 220, 'mixte', 23.77],
    ],

    // ===== T SHIRTS POLYESTER (49) =====
    't-shirts-polyester' => [
        ['T-shirt sport enfant Barhein', 'ROTSBARK', 'Roly', '100% polyester waffle interlock', 135, 'enfant', 1.49],
        ['T-shirt sport femme Barhein', 'ROTSBARW', 'Roly', '100% polyester waffle interlock', 135, 'femme', 1.68],
        ['T-shirt sport Barhein', 'ROTSBAR', 'Roly', '100% polyester waffle interlock', 135, 'homme', 1.68],
        ['T-shirt Camimera', 'ROTSCAM', 'Roly', '100% polyester', 135, 'enfant', 1.74],
        ['T-shirt Imola Enfant', 'ROTSIMOK', 'Roly', '50% polyester recycle / 50% polyester interlock', 135, 'enfant', 1.81],
        ['T-shirt Daytona', 'ROTSDAY', 'Roly', '100% polyester interlock', 135, 'enfant', 1.81],
        ['Debardeur polyester Andre', 'ROTSAND', 'Roly', '100% polyester pique knit', 140, 'mixte', 1.87],
        ['T-shirt sport Maurice', 'VATSMAU', 'Roly', '100% polyester', 145, 'mixte', 1.89],
        ['Maillot enfant Indianapolis', 'ROTSINDK', 'Roly', '100% bird\'s eye polyester', 140, 'enfant', 1.92],
        ['T-shirt Montecarlo Enfant', 'ROTSMONTK', 'Roly', '100% polyester pique', 150, 'enfant', 1.98],
        ['T-shirt SUBLIMA', 'ROTSSUB', 'Roly', '100% polyester (touche coton)', 145, 'mixte', 2.07],
        ['Maillot Baseball Indianapolis', 'ROTSIND', 'Roly', '100% bird\'s eye polyester', 140, 'homme', 2.08],
        ['T-shirt Imola homme', 'ROTSIMO', 'Roly', '50% polyester recycle / 50% polyester', 135, 'homme', 2.08],
        ['T-shirt Imola femme', 'ROTSIMOW', 'Roly', '50% polyester recycle / 50% polyester', 135, 'femme', 2.08],
        ['Sporty Kids', '645-24928', 'Sol\'s', '100% polyester', 140, 'enfant', 2.34],
        ['T-shirt Montecarlo femme', 'ROTSMONTW', 'Roly', '100% polyester pique', 150, 'femme', 2.34],
        ['T-shirt technique Montecarlo', 'ROTSMONT', 'Roly', '100% polyester pique', 150, 'homme', 2.34],
        ['Maillot Detroit Enfant', 'ROTSDETK', 'Roly', '100% bird\'s eye polyester', 140, 'enfant', 2.34],
        ['Maillot Suzuka Femme', 'ROTSDETW', 'Roly', '100% bird\'s eye polyester', 130, 'femme', 2.34],
        ['T-shirt Shanghai kid', 'ROTSSHAK', 'Roly', '100% bird\'s eye / microperforated polyester', 140, 'enfant', 2.34],
        ['Maeva Crop Top', 'SOTSMAE', 'Sol\'s', '100% polyester', 130, 'femme', 2.34],
        ['T-shirt sport Tokyo', 'ROTSTOK', 'Roly', '100% polyester', 140, 'mixte', 2.34],
        ['T-shirt FOX', '911-40454', 'Roly', '65% polyester / 35% coton', 150, 'homme', 2.34],
        ['T-shirt FOX Woman', 'ROTSFOX', 'Roly', '65% polyester / 35% coton', 150, 'femme', 2.34],
        ['T-shirt Akita', 'ROTSATI', 'Roly', '100% polyester touche coton', 145, 'mixte', 2.43],
        ['T-shirt Shanghai', 'ROTSSHA', 'Roly', '100% bird\'s eye polyester', 140, 'homme', 2.48],
        ['T-shirt Shanghai women', 'ROTSSHAW', 'Roly', '100% bird\'s eye polyester', 140, 'femme', 2.48],
        ['Maillot Bugatti Enfant', 'ROTSBUGK', 'Roly', '100% polyester', 140, 'enfant', 2.56],
        ['Maillot Detroit Homme', 'ROTSDET', 'Roly', '100% bird\'s eye polyester', 140, 'homme', 2.56],
        ['Sporty Woman', '644-24748', 'Sol\'s', '100% polyester mesh', 140, 'femme', 2.61],
        ['Sporty T-shirt technique', 'SOTSSPOK', 'Sol\'s', '100% polyester mesh', 140, 'homme', 2.61],
        ['Maillot Monaco', 'ROTSMONA', 'Roly', '100% polyester', 140, 'homme', 2.67],
        ['Sporty LS Woman', 'SOTSSPOLSW', 'Sol\'s', '100% polyester mesh', 140, 'femme', 2.71],
        ['Sporty LS', 'SOTSSPOLS', 'Sol\'s', '100% polyester mesh', 140, 'homme', 2.71],
        ['Mont Carlo LS', 'ROTSMONLS', 'Roly', '100% polyester', 140, 'enfant', 2.73],
        ['T-shirt bicolor polyester Sepang', 'ROTSSEP', 'Roly', '100% polyester', 130, 'mixte', 2.75],
        ['Maillot Austin Enfant', 'ROTSAUSK', 'Roly', '100% polyester', 140, 'enfant', 2.79],
        ['Maillot Bugatti Adulte', 'ROTSBUG', 'Roly', '100% polyester', 140, 'homme', 2.92],
        ['Maillot Austin Femme', 'ROTSAUSW', 'Roly', '100% polyester', 140, 'femme', 3.27],
        ['Maillot polyester chine Austin', 'ROTSAUS', 'Roly', '100% polyester', 140, 'homme', 3.27],
        ['T-shirt sport Avus', '951-43338', 'Roly', '92% polyester / 8% elasthanne', 160, 'femme', 3.38],
        ['T-shirt Shanghai LS', 'ROTSSHLS', 'Roly', '100% polyester (bird\'s eye + microperfore)', 140, 'mixte', 3.40],
        ['T-shirt Pixel', 'ROTSPIX', 'Roly', '100% polyester', 140, 'mixte', 3.42],
        ['Debardeur reflechissant Misano', 'ROTSMIS', 'Roly', '100% polyester (interlock + microperfore)', 140, 'mixte', 3.62],
        ['T-shirt BAKU', 'ROTSBAK', 'Roly', '65% polyester / 35% coton', 150, 'homme', 3.86],
        ['T-shirt sport Aintree', 'ROTSAIN', 'Roly', '92% polyester / 8% elasthanne', 160, 'femme', 3.86],
        ['T-shirt Prime enfant', 'ROTSPRIK', 'Roly', '92% polyamide / 8% elasthanne', 200, 'enfant', 6.97],
        ['T-shirt gardien Porto Enfant', 'ROTSPORK', 'Roly', '100% polyester', 160, 'enfant', 7.10],
        ['T-shirt gardien Porto', 'ROTSPOR', 'Roly', '100% polyester', 160, 'mixte', 7.10],
    ],

    // ===== TABLIERS (8) =====
    'tabliers' => [
        ['Chef', 'VATACHE', 'Valento', '65% polyester, 35% coton', 200, 'mixte', 1.06],
        ['Tablier promotionnel enfant', 'CGTAP1220K', '', '100% coton', null, 'mixte', 1.19],
        ['Timbal (special sublimation)', 'VATATIM', 'Valento', '100% polyester', 180, 'mixte', 1.73],
        ['Tablier benoit', 'ROTABCHE', 'Roly', '80% polyester, 20% coton', 220, 'mixte', 4.14],
        ['BBQ6050', 'LITA6050K', '', '65% polyester, 35% coton', 240, 'mixte', 4.64],
        ['Tablier avec poche en coton Bio', 'KATABK8007', 'Kariban', '100% coton bio', 280, 'mixte', 8.27],
        ['Denim Hobby Apron', 'LITAHS9090JNS', '', '80% coton, 20% polyester', 240, 'mixte', 10.05],
        ['Tablier Origine France Garantie', 'PATABK8012', '', '100% coton', 300, 'mixte', 14.81],
    ],

    // ===== TAIES OREILLER (1) =====
    'taies-oreiller' => [
        ['W350', 'WEMAW350', '', '100% toile de coton lave, commerce equitable', null, 'mixte', 3.46],
    ],

    // ===== TENUES COMPLETES SPORT (3) =====
    'tenues-completes-sport' => [
        ['Ensemble short maillot Maracana', 'VASPMAR', 'Valento', '100% polyester', 150, 'enfant', 4.46],
        ['Survetement Esparta', 'ROSUESP', 'Roly', '100% polyester', 210, 'enfant', 11.70],
        ['Survetement Creta', 'ROSUCRE', 'Roly', '100% polyester', 210, 'enfant', 16.80],
    ],

    // ===== VALISES (6) =====
    'valises' => [
        ['BG460', 'BASABG460', '', 'Polyester 600D', null, 'mixte', 21.22],
        ['Voyager', 'SOSAVOY', 'Sol\'s', 'Polyester', null, 'mixte', 26.33],
        ['BG461', 'BASABG461', '', 'Polyester 600D', null, 'mixte', 33.65],
        ['BG470', 'BAVABG470', '', 'Polyester', null, 'mixte', 36.81],
        ['BG462', 'BASABG462', '', 'Polyester 600D', null, 'mixte', 38.88],
        ['BG463', '453', '', 'Polyester 600D', null, 'mixte', 44.18],
    ],

    // ===== VESTES ETE (3) =====
    'vestes-ete' => [
        ['Veste sans manches Winner', 'SOBOWIN', 'Sol\'s', 'Exterieur: 100% nylon 210T; Doublure: 100% polyester', 210, 'mixte', 8.98],
        ['Veste Nimbus Providence Femme', 'NIVEPROF', 'Nimbus', '100% Nylon; Doublure: 100% Polyester maille fine', null, 'femme', 51.03],
        ['Veste Nimbus Kirkwood Femme', '618-23299', 'Nimbus', 'Exterieur: 100% Nylon Taslan; Interieur matelasse: 100% Nylon; Doublure: 100% Polyester', null, 'femme', 90.83],
    ],

    // ===== VESTES SPORT (2) =====
    'vestes-sport' => [
        ['Sweat a capuche polyester Arizona', '1027-50199', 'Valento', '100% polyester', 280, 'enfant', 9.90],
        ['Sweat a capuche de sport polyester', '534-47951', 'Awdis', '100% polyester', 200, 'enfant', 14.11],
    ],

    // ===== VESTES TEDDY (4) =====
    'vestes-teddy' => [
        ['Veste College', 'AWSWJH041', 'Awdis', '80% coton ringspun, 20% polyester', 280, 'mixte', 12.21],
        ['Veste Varsity Electric', 'ASSWJH044', 'Awedis', 'Corps: 80% coton ringspun, 20% polyester / Manches: 80% polyester, 20% coton', 280, 'mixte', 14.00],
        ['Veste Varsity Enfants', 'JH043J', '', '80% coton ringspun, 20% polyester', 330, 'enfant', 15.20],
        ['Veste Varsity Teddy', 'JH043', '', '80% coton ringspun, 20% polyester', 330, 'mixte', 16.00],
    ],
];
