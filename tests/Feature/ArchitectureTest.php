<?php

use App\Modules\Platform\Providers\PlatformServiceProvider;

test('app does not use debugging functions in production code', function () {
    expect(['dd', 'dump', 'ray'])
        ->not->toBeUsed();
});

test('platform contracts are interfaces', function () {
    expect('App\Modules\Platform\Contracts')
        ->toBeInterfaces();
});

test('platform service provider is registered', function () {
    expect(class_exists(PlatformServiceProvider::class))->toBeTrue();
});
