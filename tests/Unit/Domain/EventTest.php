<?php
declare(strict_types=1);
use Domain\Event\Entities\Event;
use Domain\Event\Enums\EventType;

test('creates event with unique id', function () {
    $e1 = Event::create(EventType::OrderUpdated, ['order_id' => 1, 'status' => 'paid']);
    $e2 = Event::create(EventType::OrderUpdated, ['order_id' => 2, 'status' => 'shipped']);
    expect($e1->id)->not->toBe($e2->id);
});

test('sanitizes sensitive data', function () {
    $e = Event::create(EventType::SystemAlert, ['message' => 'test', 'password' => 'secret123', 'token' => 'abc']);
    expect($e->data['password'])->toBe('***REDACTED***');
    expect($e->data['token'])->toBe('***REDACTED***');
    expect($e->data['message'])->toBe('test');
});

test('formats as SSE', function () {
    $e = Event::create(EventType::StockChanged, ['product_id' => 5, 'quantity' => -3]);
    $sse = $e->toSSE();
    expect($sse)->toContain("id: {$e->id}");
    expect($sse)->toContain("event: stock.changed");
    expect($sse)->toContain("data: ");
    expect($sse)->toEndWith("\n\n");
});

test('toArray includes all fields', function () {
    $e = Event::create(EventType::TaskCompleted, ['task_id' => 10], userId: 5, entityType: 'task', entityId: 10);
    $arr = $e->toArray();
    expect($arr['user_id'])->toBe(5);
    expect($arr['entity_type'])->toBe('task');
    expect($arr['entity_id'])->toBe(10);
});
