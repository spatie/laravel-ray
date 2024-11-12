<?php

use function Pest\version;

it('can render and send markdown', function () {
    ray()->markdown('## Hello World!');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
})->skip(version_compare(version(), '2.0.0', '>='));


it('can render and send markdown for Pest 2', function () {
    ray()->markdown('## Hello World!');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
})->skip(version_compare(version(), '2.0.0', '<'));
