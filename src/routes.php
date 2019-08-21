<?php

// accept page - where paysera redirects after successful payment
Route::get('paysera/accept', 'Asd\Paysera\PayseraWrapper@pageAccept');

// accept page - where paysera redirects upon payment cancellation
Route::get('paysera/cancel', 'Asd\Paysera\PayseraWrapper@pageCancel');

// paysera callback endpoint
Route::get('paysera/callback', 'Asd\Paysera\PayseraWrapper@callback');