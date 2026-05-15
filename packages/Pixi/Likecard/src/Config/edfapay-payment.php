<?php
return [  // ← Must return array, not string
    'edfapay' => [
        'code' => 'edfapay',
        'title' => 'Edfapay',
        'description' => 'Edfapay Payment Gateway',
        'class' => 'Pixi\\Likecard\\Payment\\Edfapay',
        'active' => true,
        'sort' => 5,
    ],
];
