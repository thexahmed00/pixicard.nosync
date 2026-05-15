<?php
// config/likecard.php

return [
    'email'         => env('LC_EMAIL'),
    'security_code' => env('LC_SECURITY_CODE'),
    'secret_iv'     => env('LC_SECRET_IV'),
    'hash_key'      => env('LC_HASH_KEY'),
    'secret_key'    => env('LC_SECRET_KEY'),
    'device_id'     => env('LC_DEVICE_ID'),
    'phone'         => env('LC_PHONE'),
    'lang_id'       => env('LC_LANG_ID', 1), // Default to 1 (English)
];
