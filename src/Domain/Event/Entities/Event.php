<?php
declare(strict_types=1);
namespace Domain\Event\Entities;

use Domain\Event\Enums\EventType;

final class Event
{
    public function __construct(
        public readonly string $id,
        public readonly EventType $type,
        public readonly array $data,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly int $version = 1,
        public readonly ?int $userId = null, // target user (null = broadcast)
        public readonly ?string $entityType = null,
        public readonly ?int $entityId = null,
    ) {}

    public static function create(EventType $type, array $data, ?int $userId = null, ?string $entityType = null, ?int $entityId = null): self
    {
        return new self(
            id: self::generateId(),
            type: $type,
            data: self::sanitizeData($data),
            occurredAt: new \DateTimeImmutable(),
            userId: $userId,
            entityType: $entityType,
            entityId: $entityId,
        );
    }

    public function toSSE(): string
    {
        $payload = json_encode([
            'type' => $this->type->value,
            'data' => $this->data,
            'timestamp' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'version' => $this->version,
        ], JSON_THROW_ON_ERROR);

        return "id: {$this->id}\nevent: {$this->type->value}\ndata: {$payload}\n\n";
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'data' => $this->data,
            'occurred_at' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'version' => $this->version,
            'user_id' => $this->userId,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
        ];
    }

    private static function generateId(): string
    {
        return hrtime(true) . '-' . bin2hex(random_bytes(4));
    }

    private static function sanitizeData(array $data): array
    {
        // Mask sensitive fields
        $sensitive = ['password', 'token', 'secret', 'credit_card', 'cvv'];
        array_walk_recursive($data, function (&$value, $key) use ($sensitive) {
            if (in_array(strtolower($key), $sensitive)) {
                $value = '***REDACTED***';
            }
        });
        return $data;
    }
}
