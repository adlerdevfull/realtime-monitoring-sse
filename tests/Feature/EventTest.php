<?php
declare(strict_types=1);
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create(['name'=>'Test','email'=>'test@test.com','password'=>bcrypt('password')]);
    $this->token = auth('api')->login($this->user);
});

test('can dispatch event', function () {
    $response = $this->withToken($this->token)->postJson('/api/v1/events/dispatch', [
        'type' => 'order.updated',
        'data' => ['order_id' => 1, 'status' => 'paid'],
        'user_id' => $this->user->id,
    ]);
    $response->assertStatus(201)->assertJsonPath('data.type', 'order.updated');
});

test('rejects invalid event type', function () {
    $response = $this->withToken($this->token)->postJson('/api/v1/events/dispatch', [
        'type' => 'invalid.type', 'data' => [],
    ]);
    $response->assertStatus(422);
});

test('can get recent events', function () {
    $this->withToken($this->token)->postJson('/api/v1/events/dispatch', [
        'type' => 'stock.changed', 'data' => ['product_id' => 1], 'user_id' => $this->user->id,
    ]);
    $response = $this->withToken($this->token)->getJson('/api/v1/events/recent');
    $response->assertOk()->assertJsonStructure(['data']);
});

test('can get connections info', function () {
    $response = $this->withToken($this->token)->getJson('/api/v1/events/connections');
    $response->assertOk()->assertJsonStructure(['data', 'count', 'max']);
});

test('requires authentication for events', function () {
    $this->getJson('/api/v1/events')->assertStatus(401);
});
