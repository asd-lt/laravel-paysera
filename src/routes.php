<?php


//TODO: remove
Route::get('paysera', function () {
    echo 'Hello from the calculator package!';
});

Route::get('paysera/accept', function () {
    echo 'Accept page';
});

Route::get('paysera/cancel', function () {
    echo 'Cancel page';
});

Route::get('paysera/callback', 'Asd\Paysera\PayseraWrapper@callback');