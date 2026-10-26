<?php

it('redirects unauthenticated root to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
