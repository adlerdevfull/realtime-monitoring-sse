<?php
declare(strict_types=1);
namespace Infrastructure\Persistence\Repositories;

use Domain\Event\Entities\Event;
use Domain\Event\Enums\EventType;
use Domain\Event\Repositories\EventRepositoryInterface;
use Illuminate\Support\Facades\Redis;

final class RedisEventRepository implements EventRepositoryInterface
{
    private const MAX_STORED_EVENTS = 50;
    private const EVENT_TTL = 3600; // 1 hour

    public function store(Event $event): void
    {
        $key = $this->getUserEventsKey($event->userId ?? 0);
        $serialized = json_encode($event->toArray());

        Redis::rpush($key, $serialized);
        Redis::ltrim($key, -self::MAX_STORED_EVENTS, -1);
        Redis::expire($key, self::EVENT_TTL);

        // Also store by entity for targeted queries
        if ($event->entityType && $event->entityId) {
            $entityKey = "events:entity:{$event->entityType}:{$event->entityId}";
            Redis::rpush($entityKey, $serialized);
            Redis::ltrim($entityKey, -self::MAX_STORED_EVENTS, -1);
            Redis::expire($entityKey, self::EVENT_TTL);
        }
    }

    public function getRecentForUser(int $userId, int $limit = 50): array
    {
        $key = $this->getUserEventsKey($userId);
        $items = Redis::lrange($key, -$limit, -1);
        return array_map(fn ($item) => $this->deserialize($item), $items);
    }

    public function getAfterEventId(int $userId, string $lastEventId): array
    {
        $all = $this->getRecentForUser($userId);
        $found = false;
        $result = [];

        foreach ($all as $event) {
            if ($found) {
                $result[] = $event;
            }
            if ($event->id === $lastEventId) {
                $found = true;
            }
        }

        return $result;
    }

    public function publish(Event $event): void
    {
        $ssePayload = $event->toSSE();

        if ($event->userId) {
            // Targeted event
            Redis::rpush("events:user:{$event->userId}", $ssePayload);
            Redis::expire("events:user:{$event->userId}", 60);
        } else {
            // Broadcast
            Redis::rpush("events:broadcast", $ssePayload);
            Redis::expire("events:broadcast", 60);
        }
    }

    private function getUserEventsKey(int $userId): string
    {
        return "events:stored:{$userId}";
    }

    private function deserialize(string $json): Event
    {
        $data = json_decode($json, true);
        return new Event(
            id: $data['id'],
            type: EventType::from($data['type']),
            data: $data['data'],
            occurredAt: new \DateTimeImmutable($data['occurred_at']),
            version: $data['version'] ?? 1,
            userId: $data['user_id'],
            entityType: $data['entity_type'],
            entityId: $data['entity_id'],
        );
    }
}
