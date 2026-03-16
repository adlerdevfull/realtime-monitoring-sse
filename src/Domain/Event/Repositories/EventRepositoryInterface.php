<?php
declare(strict_types=1);
namespace Domain\Event\Repositories;

use Domain\Event\Entities\Event;

interface EventRepositoryInterface
{
    public function store(Event $event): void;
    /** @return Event[] */
    public function getRecentForUser(int $userId, int $limit = 50): array;
    /** @return Event[] */
    public function getAfterEventId(int $userId, string $lastEventId): array;
    public function publish(Event $event): void;
}
