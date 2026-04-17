<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Application\Event\Commands\EventDispatcher;
use Domain\Connection\Repositories\ConnectionRepositoryInterface;
use Domain\Event\Enums\EventType;
use Domain\Event\Repositories\EventRepositoryInterface;
use Infrastructure\SSE\SSEStreamHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly SSEStreamHandler $sseHandler,
        private readonly EventDispatcher $dispatcher,
        private readonly EventRepositoryInterface $events,
        private readonly ConnectionRepositoryInterface $connections,
    ) {}

    /**
     * SSE endpoint - maintains open connection
     */
    public function stream(Request $request): StreamedResponse
    {
        $userId = auth('api')->id();
        $lastEventId = $request->header('Last-Event-ID');

        return $this->sseHandler->stream($userId, $lastEventId);
    }

    /**
     * Dispatch a new event (for testing/manual trigger)
     */
    public function dispatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|string|in:order.updated,stock.changed,task.completed,system.alert',
            'data' => 'required|array',
            'user_id' => 'sometimes|integer',
            'entity_type' => 'sometimes|string',
            'entity_id' => 'sometimes|integer',
        ]);

        $event = $this->dispatcher->dispatch(
            EventType::from($data['type']),
            $data['data'],
            $data['user_id'] ?? null,
            $data['entity_type'] ?? null,
            $data['entity_id'] ?? null,
        );

        return response()->json(['data' => $event->toArray()], 201);
    }

    /**
     * Get recent events for current user (REST fallback)
     */
    public function recent(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $limit = (int) $request->get('limit', 50);
        $events = $this->events->getRecentForUser($userId, $limit);

        return response()->json([
            'data' => array_map(fn ($e) => $e->toArray(), $events),
        ]);
    }

    /**
     * Get active connections for current user
     */
    public function connections(): JsonResponse
    {
        $userId = auth('api')->id();
        $active = $this->connections->getActiveByUser($userId);

        return response()->json([
            'data' => array_map(fn ($c) => [
                'id' => $c->id,
                'connected_at' => $c->connectedAt->format('c'),
                'last_activity' => $c->lastActivity?->format('c'),
            ], $active),
            'count' => count($active),
            'max' => \Domain\Connection\Entities\Connection::maxPerUser(),
        ]);
    }
}
