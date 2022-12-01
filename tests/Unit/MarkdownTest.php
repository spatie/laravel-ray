<?php

it('can render and send markdown', function () {
    ray()->markdown('## Hello World!');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});
