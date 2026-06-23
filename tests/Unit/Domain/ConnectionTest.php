<?php
declare(strict_types=1);
use Domain\Connection\Entities\Connection;

test('creates with unique id', function () {
    $c1 = Connection::create(1);
    $c2 = Connection::create(1);
    expect($c1->id)->not->toBe($c2->id);
});

test('max per user is 3', function () {
    expect(Connection::maxPerUser())->toBe(3);
});

test('detects expired connection', function () {
    $c = new Connection('abc', 1, new \DateTimeImmutable(), lastActivity: new \DateTimeImmutable('-10 minutes'));
    expect($c->isExpired(300))->toBeTrue();
});

test('active connection is not expired', function () {
    $c = new Connection('abc', 1, new \DateTimeImmutable(), lastActivity: new \DateTimeImmutable());
    expect($c->isExpired(300))->toBeFalse();
});
