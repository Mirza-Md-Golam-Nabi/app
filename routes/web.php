<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $userPanel = \Filament\Facades\Filament::getPanel('user');

    return view('welcome', [
        'dashboardUrl' => $userPanel->getUrl(),
        'loginUrl' => $userPanel->getLoginUrl(),
        'registerUrl' => $userPanel->getRegistrationUrl(),
    ]);
});
