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
