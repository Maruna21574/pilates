<?php
/**
 * Skopírujte ako data/config.php a vyplňte. Súbor config.php sa nikdy nedáva do gitu
 * (priečinok data/ je z webu zablokovaný cez .htaccess).
 */
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'dominika',
        'user' => 'root',
        'pass' => '',
    ],
    'stripe' => [
        'secret_key'     => '',   // sk_test_… (skúšobný režim) alebo sk_live_… (ostrý)
        'publishable_key'=> '',   // pk_test_… / pk_live_… (verejný kľúč, zobrazí sa v prehliadači)
        'webhook_secret' => '',   // whsec_… z Stripe → Developers → Webhooks
    ],
    // voliteľné: verejná adresa webu pre návrat zo Stripe (bez lomky na konci); inak sa odvodí z požiadavky
    'base_url' => '',
    // ako dlho sa drží miesto pri nezaplatenej online rezervácii (minúty, 30–1440)
    'hold_minutes' => 30,
];
