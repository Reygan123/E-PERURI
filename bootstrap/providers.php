<?php

use App\Modules\Arsip\Providers\ArsipServiceProvider;
use App\Modules\Platform\Providers\PlatformServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    PlatformServiceProvider::class,
    ArsipServiceProvider::class,
];
