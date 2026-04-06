<?php
declare(strict_types=1);
namespace Infrastructure\Persistence\Repositories;

use Domain\Connection\Entities\Connection;
use Domain\Connection\Repositories\ConnectionRepositoryInterface;
use Illuminate\Support\Facades\Redis;

final class RedisConnectionRepository implements ConnectionRepositoryInterface
{
    private const PREFIX = 'sse:connections:';
    private const TTL = 600; // 10 min

    public function register(Connection $connection): void
    {
        $key = self::PREFIX . $connection->id;
        Redis::hset($key, [
            'user_id' => $connection->userId,
            'connected_at' => $connection->connectedAt->format('c'),
            'last_activity' => time(),
        ]);
        Redis::expire($key, self::TTL);
        Redis::sadd("sse:user:{$connection->userId}", $connection->id);
    }

    public function unregister(string $connectionId): void
    {
        $key = self::PREFIX . $connectionId;
        $userId = Redis::hget($key, 'user_id');
        Redis::del($key);
        if ($userId) {
            Redis::srem("sse:user:{$userId}", $connectionId);
        }
    }

    public function touch(string $connectionId): void
    {
        $key = self::PREFIX . $connectionId;
        Redis::hset($key, 'last_activity', time());
        Redis::expire($key, self::TTL);
    }

    public function countByUser(int $userId): int
    {
        return (int) Redis::scard("sse:user:{$userId}");
    }

    public function getActiveByUser(int $userId): array
    {
        $ids = Redis::smembers("sse:user:{$userId}");
        $connections = [];
        foreach ($ids as $id) {
            $data = Redis::hgetall(self::PREFIX . $id);
            if (!empty($data)) {
                $connections[] = new Connection(
                    $id, (int) $data['user_id'],
                    new \DateTimeImmutable($data['connected_at']),
                    lastActivity: (new \DateTimeImmutable())->setTimestamp((int) $data['last_activity']),
                );
            }
        }
        return $connections;
    }

    public function cleanExpired(): int
    {
        // Scan for expired connections
        $cleaned = 0;
        $cursor = 0;
        do {
            [$cursor, $keys] = Redis::scan($cursor, ['match' => self::PREFIX . '*', 'count' => 100]);
            foreach ($keys as $key) {
                $lastActivity = Redis::hget($key, 'last_activity');
                if ($lastActivity && (time() - (int) $lastActivity) > self::TTL) {
                    $connId = str_replace(self::PREFIX, '', $key);
                    $this->unregister($connId);
                    $cleaned++;
                }
            }
        } while ($cursor != 0);
        return $cleaned;
    }
}
