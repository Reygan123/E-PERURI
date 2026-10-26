<?php

use App\Modules\Arsip\Providers\ArsipServiceProvider;
use App\Modules\Platform\Providers\PlatformServiceProvider;
use App\Modules\Sdm\Providers\SdmServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    PlatformServiceProvider::class,
    ArsipServiceProvider::class,
    SdmServiceProvider::class,
];
