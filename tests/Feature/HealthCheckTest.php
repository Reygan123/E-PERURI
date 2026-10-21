<?php

test('health check returns successful status', function () {
    $response = $this->get('/health');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'timestamp',
            'app',
            'database' => [
                'status',
                'connection',
            ],
        ])
        ->assertJson([
            'status' => 'ok',
            'database' => [
                'status' => 'ok',
            ],
        ]);
});
