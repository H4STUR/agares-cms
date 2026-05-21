<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['setting:enable_api,true,abort403'])->group(function () {
    foreach (glob(__DIR__ . '/api/v*.php') as $file) {
        require $file;
    }
});
