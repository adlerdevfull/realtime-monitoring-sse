<?php
declare(strict_types=1);
namespace Domain\Connection\Repositories;

use Domain\Connection\Entities\Connection;

interface ConnectionRepositoryInterface
{
    public function register(Connection $connection): void;
    public function unregister(string $connectionId): void;
    public function touch(string $connectionId): void;
    public function countByUser(int $userId): int;
    /** @return Connection[] */
    public function getActiveByUser(int $userId): array;
    public function cleanExpired(): int;
}
