<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\BlueprintFramework\Extensions\modrinthbrowser\MinecraftController;

Route::post('/download', [MinecraftController::class, 'download'])->name('extension.modrinthbrowser.download');
