<?php

return [
    'project_id' => env('PAYSERA_PROJECT_ID', ''),
    'secret' => env('PAYSERA_SECRET', ''),
    'currency' => env('PAYSERA_CURRENCY', 'EUR'),
    'country' => env('PAYSERA_COUNTRY', 'LT'),
    'accept_url' => 'paysera/accept',
    'cancel_url' => 'paysera/cancel',
    'test' => env('PAYSERA_TEST', null),
];