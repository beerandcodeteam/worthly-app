<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    // The root route is the session-restore entrypoint: it always redirects (to login or home).
    expect($response->status())->toBeIn([200, 302]);
});
