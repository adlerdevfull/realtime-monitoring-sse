<?php
declare(strict_types=1);
namespace Domain\Connection\Entities;

final class Connection
{
    private const MAX_CONNECTIONS_PER_USER = 3;

    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly \DateTimeImmutable $connectedAt,
        public ?string $lastEventId = null,
        public ?\DateTimeImmutable $lastActivity = null,
    ) {}

    public static function create(int $userId): self
    {
        return new self(
            id: bin2hex(random_bytes(16)),
            userId: $userId,
            connectedAt: new \DateTimeImmutable(),
            lastActivity: new \DateTimeImmutable(),
        );
    }

    public static function maxPerUser(): int
    {
        return self::MAX_CONNECTIONS_PER_USER;
    }

    public function isExpired(int $timeoutSeconds = 300): bool
    {
        if (!$this->lastActivity) return true;
        return (time() - $this->lastActivity->getTimestamp()) > $timeoutSeconds;
    }

    public function touch(): void
    {
        $this->lastActivity = new \DateTimeImmutable();
    }

    public function setLastEventId(string $eventId): void
    {
        $this->lastEventId = $eventId;
    }
}
