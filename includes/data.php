<?php
date_default_timezone_set('Europe/Bratislava');   // časy lekcií a rezervácií sú vždy slovenské
/**
 * Jediné miesto, kde sa nastavuje obsah webu.
 * Typy cvičení, kapacity, tím a rozvrh sa menia tu – stránky sa generujú z týchto dát.
 */

$site = [
    'url'     => 'https://www.organicpilates.sk',   // verejná adresa webu (kanonické odkazy, sitemap, náhľad pri zdieľaní)
    'name'    => 'Organic Pilates Studio',
    'tagline' => 'štúdio zdravého pohybu',
    'city'    => 'Žilina',
    'address' => 'Obežná 2/8563, 010 08 Žilina',
    'phone'   => '+421 911 174 567',
    'phone_href' => '+421911174567',
    'phone_person' => 'Eva Ďurišová',
    'email'   => 'studio@organicpilates.sk',
    'instagram' => 'https://instagram.com/organicpilateszilina',
    'facebook'  => 'http://facebook.com/Organic-Pilates-studio-106147114570088',
    'maps'    => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Obežná 2/8563, 010 08 Žilina'),
];

/**
 * Typy cvičení. `capacity` je predvolená kapacita lekcie, dá sa prepísať pri konkrétnom termíne.
 * `images` / `video` sú súbory v assets/media.
 * `price` (voliteľné, napr. '12 €') sa zobrazí v cenníku; kým chýba, ukáže sa zástupné „00 €".
 */
$classes = [
    'reformer' => [
        'name' => 'Reformer',
        'short' => 'Pilates na reformeri',
        'blurb' => 'Cvičenie na stroji s pružinami v malej skupine',
        'capacity' => 4,
        'duration' => 55,
        'color' => '#3f7a74',
        'tag' => 'Malá skupina · max. 4',
        'lead' => 'Cvičenie na reformeri s pružinami, ktoré cvik podporia alebo ho spravia náročnejším.',
        'text' => 'Reformer je pilatesový stroj s posuvnou plošinou a pružinami. Dá sa nastaviť pre začiatočníčky aj pre pokročilé a trénuje celé telo – hlboký stabilizačný svalový korzet, držanie tela, pohyblivosť aj silu. V skupine je najviac štyri cvičiacich, takže inštruktorka má priestor všimnúť si každú.',
        'for' => ['Začiatočníčky aj pokročilé', 'Pre toho, kto chce cvičiť pod odborným dohľadom', 'Pre prácu s držaním tela a stabilitou'],
        'equipment' => 'Reformer, pomôcky pre doplnkové cviky',
        'images' => ['reformer-02.jpg', 'reformer-03.jpg', 'reformer-04.jpg', 'reformer-05.jpg', 'reformer-01.jpg'],
        'video' => 'reformer-v1',
        'icon' => 'reformer',
    ],
    'podlozky' => [
        'name' => 'Podložky',
        'short' => 'Pilates na podložkách',
        'blurb' => 'Klasický pilates na podložke s pomôckami',
        'capacity' => 11,
        'duration' => 55,
        'color' => '#5a8a84',
        'tag' => 'Skupina · max. 11',
        'lead' => 'Klasické pilatesové cvičenie na podložkách s malými pomôckami.',
        'text' => 'Skupinová lekcia na podložkách s pomôckami ako Magic Circle (pilatesový kruh), Foam Roller, BOSU či Arc Barrel. Dôraz je na dýchaní, stredu tela a plynulom pohybe. Dobrá voľba na pravidelné cvičenie v príjemnej skupine.',
        'for' => ['Pre všetky úrovne', 'Pre toho, kto chce pravidelný pohyb v skupine', 'Pre posilnenie stredu tela a uvoľnenie chrbta'],
        'equipment' => 'Podložka, Magic Circle, Foam Roller, BOSU, Arc Barrel',
        'images' => ['ring-01.jpg', 'studio-01.jpg'],
        'video' => 'ring-v1',
        'icon' => 'mat',
    ],
    'barre' => [
        'name' => 'Barre',
        'short' => 'Barre pilates',
        'blurb' => 'Pilates s prvkami baletu pri tyči',
        'capacity' => 11,
        'duration' => 55,
        'color' => '#7aa19c',
        'tag' => 'Skupina · max. 11',
        'lead' => 'Prvky baletu, pilatesu a funkčného cvičenia pri tyči pred zrkadlom.',
        'text' => 'Barre spája pilates s prvkami baletu. Pri tyči sa pracuje najmä na nohách, sedacích svaloch, stredu tela a držaní tela. Pohyby sú malé, presné a dynamické – vhodné pre tých, ktorí chcú tvarovať postavu a zlepšovať rovnováhu.',
        'for' => ['Pre všetky úrovne', 'Pre rovnováhu, tvarovanie a vytrvalosť', 'Pre toho, koho baví pohyb s hudbou'],
        'equipment' => 'Barre tyče, bloky, malé pomôcky',
        'images' => ['barre-01.jpg', 'barre-02.jpg', 'barre-03.jpg'],
        'video' => null,
        'icon' => 'barre',
    ],
    'ladder' => [
        'name' => 'Ladder Barrel',
        'short' => 'Ladder Barrel',
        'blurb' => 'Drevené náradie, individuálny tréning',
        'capacity' => 1,
        'duration' => 55,
        'color' => '#4a7c77',
        'tag' => 'Individuálny tréning',
        'lead' => 'Drevené pilatesové náradie, ktoré otvára chrbticu a naťahuje celé telo.',
        'text' => 'Ladder Barrel je klasické pilatesové náradie z dreva – oblúk s rebríkom. Pomáha s mobilitou chrbtice, naťahovaním a prácou s hlbokými svalmi. Ide o individuálny tréning: inštruktorka sa venuje jednému cvičiacemu a cviky upraví podľa jeho možností.',
        'for' => ['Pre tých, čo chcú zlepšiť pohyblivosť', 'Pre doplnenie reformeru a podložiek', 'Pre pokročilejšie aj zvedavé začiatočníčky'],
        'equipment' => 'Ladder Barrel, podložky',
        'images' => ['ladder-01.jpg', 'ladder-02.jpg', 'ladder-03.jpg', 'ladder-04.jpg'],
        'video' => 'ladder-v2',
        'icon' => 'barrel',
    ],
    'tehulky' => [
        'name' => 'Tehuľky',
        'short' => 'Cvičenie pre tehotné',
        'blurb' => 'Jemné cvičenie pre budúce mamičky',
        'capacity' => 8,
        'duration' => 55,
        'color' => '#92b0af',
        'tag' => 'Pre tehotné',
        'lead' => 'Jemné cvičenie pre budúce mamičky a priestor na stretnutia.',
        'text' => 'Skupinové cvičenie navrhnuté pre tehotné ženy s fitloptami, Arc Barrelmi, Magic Circle a tonizačnými loptičkami. Okrem pohybu je to aj komunita, kde si mamičky vymieňajú skúsenosti. Pred začiatkom cvičenia v tehotenstve odporúčame poradiť sa so svojím lekárom.',
        'for' => ['Pre tehotné ženy', 'Pre ľahký pohyb a uvoľnenie', 'Pre stretnutia s ďalšími mamičkami'],
        'equipment' => 'Fitlopty, Arc Barrel, Magic Circle, tonizačné loptičky',
        'images' => ['tehulky-01.jpg'],
        'pos' => '24% 50%', // výrez landscape fotky v kartách na výšku
        'video' => null,
        'icon' => 'mom',
    ],
    'vital' => [
        'name' => 'Vital Pilates',
        'short' => 'Cvičenie pre seniorov',
        'blurb' => 'Pomalšie tempo, dobrá nálada a pohyb',
        'capacity' => 8,
        'duration' => 55,
        'color' => '#6b9291',
        'tag' => 'Pre seniorov',
        'lead' => 'Pomalšie tempo, jasné cviky a dôraz na dobrú náladu.',
        'text' => 'Cvičenie pre seniorov zamerané na vitalitu, krvný obeh a dobrú náladu. Tempo je prispôsobené, cviky sú zrozumiteľné a atmosféra uvoľnená.',
        'for' => ['Pre seniorov a pre toho, kto potrebuje pomalšie tempo', 'Pre udržanie pohyblivosti a rovnováhy', 'Pre dobrú náladu a spoločnosť'],
        'equipment' => 'Podložky, jemné pomôcky',
        'images' => ['vital-01.jpg'],
        'pos' => '48% 50%',
        'video' => null,
        'icon' => 'vital',
    ],
];

/** Individuálne lekcie – objednávajú sa dohodou, nie podľa rozvrhu. */
$individual = [
    'name' => 'Individuálne lekcie',
    'tag' => '1 alebo 2 osoby',
    'lead' => 'Inštruktorka sa venuje iba vám a program prispôsobí vašej kondícii, zdravotnému stavu a želaniam.',
    'text' => 'Individuálna lekcia pre jednu alebo dve osoby. Môžete využiť všetky pomôcky a stroje v štúdiu – reformer, Ladder Barrel aj malé náradie. Hodí sa na rehabilitačné cvičenie, na prípravu na skupinovky aj pre toho, kto sa chce cvičeniu venovať naplno.',
    'for' => ['Pre toho, kto potrebuje individuálny prístup', 'Pri špecifických zdravotných požiadavkách', 'Pre dvojice (kamarátky, partneri)'],
    'images' => ['reformer-03.jpg'],
];

/**
 * Tím. Mená k fotke nepriraďujeme, aby sa omylom nezamenili.
 * Tretie meno sa doplní (zatiaľ zástupný text).
 */
/* UKÁŽKOVÉ TEXTY: sú všeobecné a treba ich nahradiť skutočným predstavením každej osoby.
   `photo` (voliteľné) = súbor v assets/media s portrétom; bez neho sa zobrazia iniciály. */
$team = [
    [
        'name' => 'Eva Ďurišová', 'role' => 'Inštruktorka', 'initials' => 'ED', 'photo' => null,
        'bio' => 'Eva vedie lekcie v štúdiu a je kontaktnou osobou pre otázky k cvičeniu. Na hodinách dbá na pokojné tempo, jasné inštrukcie a na to, aby sa každý cvičiaci cítil bezpečne.',
        'focus' => ['Individuálny prístup ku každému', 'Dôraz na správnu techniku a dýchanie', 'Pokojná a podporná atmosféra'],
    ],
    [
        'name' => 'Dominika Brodová', 'role' => 'Inštruktorka', 'initials' => 'DB', 'photo' => null,
        'bio' => 'Dominika vedie lekcie v štúdiu. Na hodinách sa sústredí na presný a plynulý pohyb a cviky prispôsobuje úrovni skupiny, aby si zacvičili začiatočníčky aj pokročilé.',
        'focus' => ['Plynulý pohyb a práca so stredom tela', 'Cviky podľa úrovne skupiny', 'Príjemná energia na hodine'],
    ],
    [
        'name' => 'Meno doplníme', 'role' => 'Inštruktorka', 'initials' => 'OP', 'photo' => null,
        'bio' => 'Krátke predstavenie ďalšej inštruktorky doplníme.',
        'focus' => [],
    ],
];

/**
 * Ukážkový týždenný rozvrh (UKÁŽKA – nahradí ho reálny rozvrh štúdia).
 * day: 1 = pondelok … 7 = nedeľa.
 */
$schedule = [
    ['class' => 'reformer', 'day' => 1, 'time' => '08:30'],
    ['class' => 'podlozky', 'day' => 1, 'time' => '17:30'],
    ['class' => 'barre',    'day' => 1, 'time' => '18:45'],
    ['class' => 'reformer', 'day' => 2, 'time' => '09:00'],
    ['class' => 'tehulky',  'day' => 2, 'time' => '10:30'],
    ['class' => 'reformer', 'day' => 2, 'time' => '18:00'],
    ['class' => 'vital',    'day' => 3, 'time' => '09:30'],
    ['class' => 'podlozky', 'day' => 3, 'time' => '17:30'],
    ['class' => 'ladder',   'day' => 3, 'time' => '18:45'],
    ['class' => 'reformer', 'day' => 4, 'time' => '08:30'],
    ['class' => 'barre',    'day' => 4, 'time' => '17:30'],
    ['class' => 'reformer', 'day' => 4, 'time' => '18:45'],
    ['class' => 'tehulky',  'day' => 5, 'time' => '10:00'],
    ['class' => 'podlozky', 'day' => 5, 'time' => '17:00'],
    ['class' => 'reformer', 'day' => 6, 'time' => '09:00'],
    ['class' => 'barre',    'day' => 6, 'time' => '10:15'],
];

$faq = [
    ['Čo si mám priniesť na prvú hodinu?', 'Pohodlné oblečenie a protišmykové ponožky. Podložky a pomôcky máme v štúdiu. Na prvú hodinu stačí prísť o pár minút skôr.'],
    ['Musím už vedieť cvičiť pilates?', 'Nie. Lekcie sú vedené tak, aby ich zvládli aj začiatočníčky. Inštruktorka cviky upraví podľa vašich možností.'],
    ['Môžem cvičiť v tehotenstve?', 'Pre tehotné máme lekcie Tehuľky. Pred začiatkom cvičenia odporúčame poradiť sa s lekárom.'],
    ['Koľko ľudí je na lekcii?', 'Na reformeri najviac 4, na podložkách a barre najviac 11 cvičiacich. Preto je dobré miesto rezervovať vopred.'],
    ['Ako môžem zrušiť rezerváciu?', 'Podmienky zrušenia upresní štúdio. Pre zmenu termínu nás kontaktujte telefonicky alebo e-mailom.'],
];

/** Oznam hore na stránke – zapína sa v admin paneli (Oznam hore). Predvolene vypnutý. */
$announcement = null;

/**
 * RECENZIE KLIENTOV – ZÁSTUPNÉ TEXTY. V admin paneli ich nahraďte skutočnými recenziami (napr. z Google)
 * a len so súhlasom autorov. `source` = 'google' ukáže logo Google (len pri recenziách, ktoré naozaj pochádzajú z Google).
 * Prázdny zoznam recenzie na webe skryje.
 */
$reviews = [
    ['name' => 'Meno Priezvisko', 'date' => 'dátum', 'rating' => 5, 'source' => 'google', 'text' => 'Sem vložte text skutočnej recenzie klientky. Odporúčame krátky a konkrétny text, najlepšie priamo skopírovaný z Google.'],
    ['name' => 'Meno Priezvisko', 'date' => 'dátum', 'rating' => 5, 'source' => 'google', 'text' => 'Sem vložte text ďalšej skutočnej recenzie. Pred zverejnením si vyžiadajte súhlas autorky.'],
    ['name' => 'Meno Priezvisko', 'date' => 'dátum', 'rating' => 5, 'source' => 'google', 'text' => 'Sem vložte text tretej skutočnej recenzie klientky štúdia.'],
    ['name' => 'Meno Priezvisko', 'date' => 'dátum', 'rating' => 5, 'source' => 'google', 'text' => 'Sem vložte text štvrtej skutočnej recenzie klientky štúdia.'],
];

/** Fotky v slideri na úvodnej stránke (súbory v assets/media). */
$hero_slides = [
    ['file' => 'reformer-02.jpg', 'alt' => 'Cvičenie na reformeri'],
    ['file' => 'ladder-01.jpg',   'alt' => 'Cvičenie na Ladder Barrel s inštruktorkou'],
    ['file' => 'ring-01.jpg',     'alt' => 'Skupinové cvičenie na podložkách s pilatesovým kruhom'],
    ['file' => 'barre-01.jpg',    'alt' => 'Barre pilates pri tyči pred zrkadlom'],
    ['file' => 'studio-02.jpg',   'alt' => 'Štúdio Organic Pilates s reformermi'],
];

/**
 * Knižnica videí (súbory v assets/media: <názov>.mp4 + úvodný obrázok <názov>.jpg).
 * home = zobrazí sa v sekcii „Čo vás čaká na prvej hodine“ na úvodnej stránke, gallery = v galérii.
 * Poradie v zozname = poradie na webe. Spravuje sa v admine (Videá).
 */
$videos = [
    ['file' => 'reformer-v1', 'title' => '',              'home' => false, 'gallery' => true],
    ['file' => 'reformer-v2', 'title' => '',              'home' => false, 'gallery' => true],
    ['file' => 'ladder-v2',   'title' => '',              'home' => false, 'gallery' => true],
    ['file' => 'ring-v1',     'title' => '',              'home' => false, 'gallery' => true],
    ['file' => 'ladder-v1',   'title' => '',              'home' => false, 'gallery' => true],
    ['file' => 'first-v1',    'title' => 'Prvá lekcia',   'home' => true,  'gallery' => true],
    ['file' => 'first-v2',    'title' => 'Druhá lekcia',  'home' => true,  'gallery' => false],
    ['file' => 'first-v3',    'title' => 'Tretia lekcia', 'home' => true,  'gallery' => false],
];

/**
 * Diely reformeru v 3D modeli (stránka Cvičenia). id musí zodpovedať dielu v assets/js/reformer3d.js
 * (frame, carriage, springs, footbar, shoulder, head, ropes); poradie = číslovanie bodov na modeli.
 */
$reformer_parts = [
    ['id' => 'frame',    'title' => 'Rám a koľajnice',   'text' => 'Pevný čierny kovový rám nesie celý stroj. Po jeho koľajniciach sa plošina plynulo kĺže tam a späť.'],
    ['id' => 'carriage', 'title' => 'Pohyblivá plošina', 'text' => 'Čalúnená plošina na kolieskach, na ktorej ležíte, sedíte alebo kľačíte. Pri cvičení sa pohybuje a vaše telo musí tento pohyb kontrolovať, preto sa zapájajú aj hlboké svaly.'],
    ['id' => 'springs',  'title' => 'Pružiny',           'text' => 'Určujú odpor, teda ako náročný cvik je. Inštruktorka ich pre každý cvik pripojí alebo odpojí tak, aby bol správne náročný práve pre vás.'],
    ['id' => 'footbar',  'title' => 'Opierka nôh',       'text' => 'Pevná tyč, o ktorú sa zapierajú nohy alebo ruky pri tlaku. Býva nastaviteľná podľa postavy.'],
    ['id' => 'shoulder', 'title' => 'Opierky ramien',    'text' => 'Zabraňujú skĺznutiu tela, keď sa odtláčate nohami. Vďaka nim sa môžete sústrediť na správny a plynulý pohyb.'],
    ['id' => 'head',     'title' => 'Opierka hlavy',     'text' => 'Podopiera hlavu a krk, aby zostali uvoľnené. Dá sa prispôsobiť potrebám cvičiaceho.'],
    ['id' => 'ropes',    'title' => 'Popruhy a kladky',  'text' => 'Lanká vedené cez kladky, s rúčkami pre ruky alebo slučkami pre nohy. Využívajú sa pri cvikoch pre paže, ramená, chrbát a nohy a na rozsah pohybu.'],
];

/**
 * Cenník vstupov a permanentiek (tabuľky v cenníku). Upravuje sa v admine (Ceny).
 * Každá tabuľka: title, head (hlavička stĺpcov), rows (riadky, každý riadok = bunky v poradí stĺpcov).
 */
$price_tables = [
    ['title' => 'Skupinové cvičenia: na podložkách a na reformeroch',
     'head'  => ['Kredit / platnosť', 'Cena', 'Cena s permanentkou podložky / reformer'],
     'rows'  => [
        ['Podložky jednorazový', '11 €', '–'],
        ['Reformer jednorazový', '22 €', '–'],
        ['Kredit na 1 mesiac', '44 €', '38 € (9,50 / 19,– vstup)'],
        ['Kredit na 1 mesiac', '88 €', '76 € (9,50 / 19,– vstup)'],
        ['Kredit na 2 mesiace', '110 €', '90 € (9,– / 18,– vstup)'],
        ['Kredit na 2 mesiace', '176 €', '144 € (9,– / 18,– vstup)'],
        ['Kredit na 3 mesiace', '352 €', '272 € (8,50 / 17,– vstup)'],
     ]],
    ['title' => 'Vital Pilates',
     'head'  => ['Vstup / platnosť', 'Cena'],
     'rows'  => [
        ['Jednorazový', '10 €'],
        ['Permanentka 8 vstupov / 2 mesiace', '60 € (7,50 vstup)'],
     ]],
    ['title' => 'Individuálne cvičenie: 1 osoba',
     'head'  => ['Vstup / platnosť', 'Cena', 'Cena s permanentkou'],
     'rows'  => [
        ['Jednorazový', '35 €', '–'],
        ['4 vstupy / 1 mesiac', '140 €', '132 € (33,– vstup)'],
        ['8 vstupov / 2 mesiace', '280 €', '256 € (32,– vstup)'],
        ['16 vstupov / 3 mesiace', '560 €', '496 € (31,– vstup)'],
     ]],
    ['title' => 'Individuálne cvičenie: 2 – 3 osoby',
     'head'  => ['Vstup / platnosť', 'Cena', 'Cena s permanentkou'],
     'rows'  => [
        ['Jednorazový za osobu', '24 €', '–'],
        ['4 vstupy / 1 mesiac', '96 €', '88 € (22,– vstup)'],
        ['8 vstupov / 2 mesiace', '192 €', '168 € (21,– vstup)'],
        ['16 vstupov / 3 mesiace', '384 €', '320 € (20,– vstup)'],
     ]],
    ['title' => 'Individuálne cvičenie: 4 osoby',
     'head'  => ['Vstup / platnosť', 'Cena', 'Cena s permanentkou'],
     'rows'  => [
        ['Jednorazový za osobu', '23 €', '–'],
        ['4 vstupy / 1 mesiac', '92 €', '80 € (20,– vstup)'],
        ['8 vstupov / 2 mesiace', '184 €', '152 € (19,– vstup)'],
        ['16 vstupov / 3 mesiace', '368 €', '288 € (18,– vstup)'],
     ]],
];

/** Kategórie fotiek v galérii. */
$gallery_cats = ['reformer' => 'Reformer', 'barre' => 'Barre', 'ladder' => 'Ladder Barrel', 'ring' => 'Podložky', 'studio' => 'Štúdio', 'team' => 'Tím', 'tehulky' => 'Tehuľky', 'vital' => 'Vital Pilates'];

/**
 * Predvolená galéria: všetky fotky `kategoria-NN.jpg` z assets/media. Zmeny z admina (poradie, kategórie,
 * pridané a odstránené fotky) ju nahradia.
 */
function gallery_default(): array {
    global $gallery_cats;
    $out = [];
    foreach (glob(__DIR__ . '/../assets/media/*-[0-9][0-9].jpg') ?: [] as $f) {
        $b = basename($f); $cat = explode('-', $b)[0];
        if (isset($gallery_cats[$cat]) && !in_array($cat, ['tehulky', 'vital'], true)) $out[] = ['file' => $b, 'cat' => $cat];
    }
    return $out;
}

/** Ostatné nastavenia. `pass_price` je cena permanentky/balíka (text, napr. „45 €“). */
$settings = ['voucher_validity_months' => 12, 'book_months' => 12, 'google_reviews_url' => null, 'pass_price' => ''];

/**
 * Upraviteľné texty webu. Admin panel (Texty stránok) ich zobrazí podľa tohto zoznamu;
 * nový text pridáte sem a v šablóne ho zobrazíte cez e(T('kluc')).
 * g = skupina, l = popis poľa, t = typ (line/area), d = predvolený text.
 */
$text_defs = [
    // Úvodná stránka
    'home_hero_lead'    => ['g' => 'Úvodná stránka', 'l' => 'Úvodný text pod nadpisom', 't' => 'area', 'd' => 'Cvičenie v malých skupinách, na reformeri aj na podložkách. Pre začiatočníčky, tehotné, seniorov aj tých, ktorí chcú viac.'],
    'home_classes_title'=> ['g' => 'Úvodná stránka', 'l' => 'Nadpis sekcie lekcií', 't' => 'line', 'd' => 'Nájdite lekciu, ktorá vám sadne'],
    'home_classes_lead' => ['g' => 'Úvodná stránka', 'l' => 'Text pod nadpisom cvičení', 't' => 'area', 'd' => 'Každá lekcia je iná. Pozrite, čo je čo a pre koho je určené.'],
    'home_steps_title'  => ['g' => 'Úvodná stránka', 'l' => 'Nadpis „Ako to funguje“', 't' => 'line', 'd' => 'Od rezervácie po prvú hodinu'],
    'home_step1_title'  => ['g' => 'Úvodná stránka', 'l' => 'Krok 1 – nadpis', 't' => 'line', 'd' => 'Vyberte lekciu'],
    'home_step1_text'   => ['g' => 'Úvodná stránka', 'l' => 'Krok 1 – text', 't' => 'area', 'd' => 'Pozrite si rozvrh a vyberte cvičenie aj čas, ktorý vám vyhovuje.'],
    'home_step2_title'  => ['g' => 'Úvodná stránka', 'l' => 'Krok 2 – nadpis', 't' => 'line', 'd' => 'Rezervujte si miesto'],
    'home_step2_text'   => ['g' => 'Úvodná stránka', 'l' => 'Krok 2 – text', 't' => 'area', 'd' => 'Pri každej lekcii vidíte, koľko miest je voľných. Skupiny sú malé.'],
    'home_step3_title'  => ['g' => 'Úvodná stránka', 'l' => 'Krok 3 – nadpis', 't' => 'line', 'd' => 'Príďte si zacvičiť'],
    'home_step3_text'   => ['g' => 'Úvodná stránka', 'l' => 'Krok 3 – text', 't' => 'area', 'd' => 'Stačí pohodlné oblečenie a protišmykové ponožky. Zvyšok nájdete u nás.'],
    'home_video_title'  => ['g' => 'Úvodná stránka', 'l' => 'Nadpis videí „Prvá hodina“', 't' => 'line', 'd' => 'Čo vás čaká na prvej hodine'],
    'home_studio_title' => ['g' => 'Úvodná stránka', 'l' => 'Sekcia Štúdio – nadpis', 't' => 'line', 'd' => 'Svetlý priestor, v ktorom sa dá dýchať'],
    'home_studio_text'  => ['g' => 'Úvodná stránka', 'l' => 'Sekcia Štúdio – text', 't' => 'area', 'd' => 'Drevená podlaha, zrkadlá, rastliny a veľa denného svetla. Máme reformery, Ladder Barrel, barre tyče aj malé pomôcky, takže viete skúsiť viac než jeden štýl cvičenia.'],
    'reviews_title'     => ['g' => 'Úvodná stránka', 'l' => 'Nadpis recenzií', 't' => 'line', 'd' => 'Čo hovoria naše klientky'],
    // Ostatné stránky
    'classes_lead'      => ['g' => 'Ostatné stránky', 'l' => 'Lekcie – úvodný text', 't' => 'area', 'd' => 'Každá lekcia má svoj štýl, tempo aj pomôcky. Vyberte si podľa toho, čo vám práve vyhovuje.'],
    'book_lead'         => ['g' => 'Ostatné stránky', 'l' => 'Rezervácia – úvodný text', 't' => 'area', 'd' => 'Reformer je pre najviac 4 cvičiace, podložky a barre pre najviac 11. Pri každej lekcii vidíte voľné miesta.'],
    'price_lead'        => ['g' => 'Ostatné stránky', 'l' => 'Cenník – úvodný text', 't' => 'area', 'd' => 'Vyberte si službu a pozrite si, čo obsahuje a koľko stojí.'],
    'pass_title'        => ['g' => 'Ostatné stránky', 'l' => 'Cenník – permanentky: nadpis', 't' => 'line', 'd' => 'Permanentky a balíky'],
    'pass_text'         => ['g' => 'Ostatné stránky', 'l' => 'Cenník – permanentky: text', 't' => 'area', 'd' => 'Viac vstupov za výhodnejšiu cenu. Ceny tabuliek upravíte v Ceny.'],
    'price_valid'       => ['g' => 'Ostatné stránky', 'l' => 'Cenník – platnosť', 't' => 'line', 'd' => 'Platný od 1. 5. 2026'],
    'price_note'        => ['g' => 'Ostatné stránky', 'l' => 'Cenník – poznámka pod tabuľkami', 't' => 'area', 'd' => 'Dĺžka cvičenia (lekcie) je 50 min. Všetky permanentky sú neprenosné a časovo obmedzené.'],
    'gift_lead'         => ['g' => 'Ostatné stránky', 'l' => 'Poukážky – úvodný text', 't' => 'area', 'd' => 'Darčeková poukážka na lekcie pilatesu. Obdarovaná osoba si cvičenie aj termín vyberie sama.'],
    'gift_title'        => ['g' => 'Ostatné stránky', 'l' => 'Poukážky – nadpis', 't' => 'line', 'd' => 'Darček, ktorý poteší telo aj hlavu'],
    'gift_text'         => ['g' => 'Ostatné stránky', 'l' => 'Poukážky – popis', 't' => 'area', 'd' => 'Môžete darovať poukážku na ľubovoľnú sumu alebo na konkrétne cvičenie. Stačí vyplniť formulár a my sa vám ozveme.'],
    'contact_lead'      => ['g' => 'Ostatné stránky', 'l' => 'Kontakt – úvodný text', 't' => 'area', 'd' => 'Máte otázku k cvičeniu alebo chcete dohodnúť individuálnu lekciu? Ozvite sa.'],
    'banner_text'       => ['g' => 'Ostatné stránky', 'l' => 'Banner „Rezervovať lekciu“ – text', 't' => 'area', 'd' => 'Vyberte si lekciu, pozrite voľné miesta a rezervujte si ju online. Skupiny sú malé, preto sa miesta rýchlo plnia.'],
    'r3d_title'         => ['g' => 'Ostatné stránky', 'l' => '3D model reformeru – nadpis', 't' => 'line', 'd' => 'Čo je reformer a na čo slúži?'],
    'r3d_text'          => ['g' => 'Ostatné stránky', 'l' => '3D model reformeru – úvodný text', 't' => 'area', 'd' => 'Reformer je pilatesový stroj s pohyblivou plošinou a pružinami. Odpor sa dá nastaviť, preto je vhodný pre začiatočníčky aj pokročilé. Otáčajte model a kliknite na jednotlivé diely, aby ste videli, na čo slúžia.'],
    // O nás
    'about_lead'        => ['g' => 'O nás', 'l' => 'Úvodný text', 't' => 'area', 'd' => 'Pilates, ktorý berie ohľad na vaše telo, tempo aj nároky.'],
    'about_team_title'  => ['g' => 'O nás', 'l' => 'Tím – nadpis', 't' => 'line', 'd' => 'Inštruktorky, ktoré vás vedú'],
    'about_team_text'   => ['g' => 'O nás', 'l' => 'Tím – text', 't' => 'area', 'd' => 'Lekcie vedú inštruktorky štúdia. Cviky upravia podľa vášho tela, úrovne aj zdravotného stavu, aby ste sa počas hodiny cítili bezpečne. Kliknite na meno a dozviete sa o nich viac.'],
    'about_studio_title'=> ['g' => 'O nás', 'l' => 'Priestor – nadpis', 't' => 'line', 'd' => 'Štúdio plné svetla'],
    'about_studio_text1'=> ['g' => 'O nás', 'l' => 'Priestor – 1. odsek', 't' => 'area', 'd' => 'V štúdiu nájdete reformery, Cadillac, Ladder Barrel, barre tyče, podložky a množstvo malých pomôcok. Svetlé steny, drevená podlaha a zeleň vytvárajú pokojné prostredie.'],
    'about_studio_text2'=> ['g' => 'O nás', 'l' => 'Priestor – 2. odsek', 't' => 'area', 'd' => 'Skupiny sú malé: na reformeri najviac štyria cvičiaci, na podložkách a barre najviac jedenásti.'],
    'about_p_title'     => ['g' => 'O nás', 'l' => 'Zásady – nadpis sekcie', 't' => 'line', 'd' => 'Prečo sa u nás cvičí dobre'],
    'about_p1_title'    => ['g' => 'O nás', 'l' => 'Zásada 1 – nadpis', 't' => 'line', 'd' => 'Malé skupiny'],
    'about_p1_text'     => ['g' => 'O nás', 'l' => 'Zásada 1 – text', 't' => 'area', 'd' => 'Inštruktorka má čas na každého, preto sme obmedzili kapacitu.'],
    'about_p2_title'    => ['g' => 'O nás', 'l' => 'Zásada 2 – nadpis', 't' => 'line', 'd' => 'Individuálny prístup'],
    'about_p2_text'     => ['g' => 'O nás', 'l' => 'Zásada 2 – text', 't' => 'area', 'd' => 'Cviky upravíme podľa vašej úrovne a zdravotného stavu.'],
    'about_p3_title'    => ['g' => 'O nás', 'l' => 'Zásada 3 – nadpis', 't' => 'line', 'd' => 'Pokojné prostredie'],
    'about_p3_text'     => ['g' => 'O nás', 'l' => 'Zásada 3 – text', 't' => 'area', 'd' => 'Svetlý priestor, v ktorom sa dá sústrediť na seba.'],
];
$texts = array_map(fn($x) => $x['d'], $text_defs);

/**
 * DARČEKOVÉ POUKÁŽKY
 * $voucher_secret podpisuje odkaz na tlač poukážky (v e-maile pre štúdio), aby ho nikto nemohol sfalšovať.
 * Držte ho v tajnosti a po nasadení ho zmeňte na vlastnú dlhú náhodnú hodnotu.
 */
$voucher_secret = 'bfe5d3135e22cb272d7dd559d6ebfdb757540e932b4d0e6c';

/* ---------- uplatnenie zmien z admin panela (data/content.json) ---------- */
require_once __DIR__ . '/content.php';
$__d = ['site' => $site, 'classes' => $classes, 'individual' => $individual, 'team' => $team, 'schedule' => $schedule, 'faq' => $faq,
        'reviews' => $reviews, 'announcement' => $announcement, 'settings' => $settings, 'hero_slides' => $hero_slides, 'gallery' => gallery_default(), 'texts' => $texts, 'videos' => $videos, 'price_tables' => $price_tables];
$__r = content_apply($__d, content_load());
[$site, $classes, $individual, $team, $schedule, $faq, $reviews, $announcement, $settings, $hero_slides, $gallery, $texts, $videos, $price_tables] =
    [$__r['site'], $__r['classes'], $__r['individual'], $__r['team'], $__r['schedule'], $__r['faq'], $__r['reviews'], $__r['announcement'], $__r['settings'], $__r['hero_slides'], $__r['gallery'], $__r['texts'], $__r['videos'], $__r['price_tables']];
$videos_home    = array_values(array_filter($videos, fn($v) => $v['home']));
$videos_gallery = array_values(array_filter($videos, fn($v) => $v['gallery']));
$voucher_validity_months = $settings['voucher_validity_months'];
$google_reviews_url      = $settings['google_reviews_url'];
unset($__r);   // $__d ostáva k dispozícii adminu ako „pôvodné hodnoty“

/** Upraviteľný text webu (pozri $text_defs). */
function T(string $key): string { global $texts; return $texts[$key] ?? ''; }

/** Aktuálna stránka pre zvýraznenie menu. */
function is_current(string $file): string {
    return basename($_SERVER['SCRIPT_NAME']) === $file ? ' aria-current="page"' : '';
}

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function media(string $f): string { return 'assets/media/' . $f; }
