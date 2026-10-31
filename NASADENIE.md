# Nasadenie na Websupport a správa webu

## Požiadavky na hosting
- PHP **8.1 alebo novší** (v administrácii Websupport v nastavení PHP), rozšírenia GD, mbstring, fileinfo (štandardne sú).
- Apache s podporou `.htaccess` (štandard na Websupporte).
- Databáza **nie je potrebná** – úpravy z admin panela sa ukladajú do súboru `data/content.json`.

## Čo nahrať cez FTP
Celý obsah projektu **okrem**:
- priečinka `img/` (originálne veľké fotky a videá, na webe sa nepoužívajú),
- súboru `NASADENIE.md`.

Priečinok `data/` nahrajte tiež (obsahuje `.htaccess`, ktorý ho chráni, a súbor s prístupom do adminu).

## Oprávnenia na zápis (dôležité)
Tieto priečinky musia byť zapisovateľné (v FTP klientovi zvyčajne 755, ak by neuspelo, tak 775):
- `data/` – ukladanie zmien, zálohy, prístup do adminu
- `assets/media/uploads/` – obrázky nahrané cez admin
- `assets/media/thumbs/` – automatické náhľady pre menu

Prehľad v admine (Prehľad → „Čo ešte treba doplniť“) ukáže, či zápis funguje.

## Nahrávanie videí
V admine (Videá) sa dajú nahrať videá vo formáte **MP4 (H.264)**. Úvodný obrázok vytvorí prehliadač z prvého záberu.
Server videá neprekódováva, preto ich pripravte vopred: odporúčame zvislé video do 720 px a približne 5 MB.

Maximálnu veľkosť určuje hosting (`upload_max_filesize` a `post_max_size`). V projekte je súbor `.user.ini`, ktorý ich nastavuje
na 64 MB; na Websupporte s PHP-FPM sa uplatní sám (zmena môže trvať niekoľko minút). Ak nie, limit upravte v administrácii
hostingu v nastavení PHP. Aktuálny limit vidno na stránke Videá v admine.

## Prvé prihlásenie
1. Otvorte `https://www.organicpilates.sk/admin/`.
2. Meno: `admin`, heslo: počiatočné heslo, ktoré ste dostali pri odovzdaní.
3. Admin vás hneď vyzve na zmenu hesla (min. 10 znakov).

**Zabudnuté heslo:** cez FTP zmažte `data/admin.json.php`, otvorte `/admin/setup.php` a zadajte inštalačný kód
(prvých 12 znakov hodnoty `$voucher_secret` v súbore `includes/data.php`) a nové heslo.

## Čo treba po nasadení nastaviť
1. V `includes/data.php` zmeňte `$voucher_secret` na vlastnú dlhú náhodnú hodnotu
   (podpisuje odkazy na tlač poukážok a slúži ako inštalačný kód). Zmena zneplatní už vydané odkazy na tlač.
2. V `includes/data.php` skontrolujte `$site['url']` (adresa webu pre sitemap a náhľad pri zdieľaní).
3. Vyskúšajte, že prichádzajú e-maily z kontaktného formulára, rezervácie a poukážok.
4. Doplňte polia `[doplniť]` v súbore `ochrana-udajov.php` (obchodné meno, IČO, doby uchovávania).
5. V admine vyplňte skutočný rozvrh, ceny, tím a recenzie (ukážkové texty sú označené v Prehľade).

## Čo sa upravuje kde
| Čo | Kde |
|---|---|
| Ceny, rozvrh, cvičenia, tím, recenzie, otázky, texty, fotky, galéria, oznam hore | Admin panel (`/admin/`) |
| Zálohy a vrátenie zmien | Admin → Zálohy |
| Predvolené (pôvodné) hodnoty | `includes/data.php` |
| Text ochrany osobných údajov | `ochrana-udajov.php` |

## Bezpečnosť v skratke
- Heslo je uložené len ako hash v `data/admin.json.php` (súbor sa nedá stiahnuť).
- Prihlásenie je obmedzené na 5 chybných pokusov za 15 minút z jednej adresy.
- Všetky formuláre v admine majú ochranu proti falošným požiadavkám (CSRF).
- Nahrané obrázky sa prekódujú na JPG (odstráni sa skrytý obsah) a v ich priečinku sa nespúšťajú skripty.
- Admin sa po 2 hodinách nečinnosti sám odhlási a vyhľadávače ho neindexujú.
- Odporúčame zapnúť HTTPS (na Websupporte bezplatný certifikát) – bez neho sa heslo prenáša nešifrovane.

## Rezervácie, databáza a platby (Stripe)
Rezervácie sa ukladajú do MySQL; obsadenosť v kalendári a čakacia listina sa počítajú z nej. Prehľad je v admine (Rezervácie a platby).

1. Na hostingu vytvorte MySQL databázu a skopírujte `data/config.sample.php` na `data/config.php` (nikdy nie do gitu) s údajmi k databáze. Tabuľky sa vytvoria samy pri prvom použití.
2. Bez `data/config.php` web funguje po starom (rezervácia len e-mailom, bez online platby).
3. Online platba sa zapne po vyplnení `stripe.secret_key` a `stripe.webhook_secret` a ceny lekcie v admine (Ceny, napr. „12 €“). Cena lekcie bez čísla = online platba sa pri nej nenaponúka.
4. Stripe → Developers → Webhooks: pridajte endpoint `https://www.organicpilates.sk/api/stripe-webhook.php` s udalosťami `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.expired`, `charge.refunded`. Jeho „Signing secret“ (`whsec_…`) vložte do configu.
5. Lokálne testovanie webhooku: `stripe listen --forward-to localhost/api/stripe-webhook.php`, testovacia karta 4242 4242 4242 4242.
6. Vrátenie peňazí robíte v Stripe (Payments → Refund); rezervácia sa v admine sama označí ako „Vrátené“.
