<?php
declare(strict_types=1);
namespace Infrastructure\SSE;

use Domain\Connection\Entities\Connection;
use Domain\Connection\Repositories\ConnectionRepositoryInterface;
use Domain\Event\Repositories\EventRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SSEStreamHandler
{
    private const HEARTBEAT_INTERVAL = 15; // seconds
    private const MAX_EXECUTION_TIME = 3600; // 1 hour max

    public function __construct(
        private readonly EventRepositoryInterface $events,
        private readonly ConnectionRepositoryInterface $connections,
    ) {}

    public function stream(int $userId, ?string $lastEventId = null): StreamedResponse
    {
        // Check connection limit
        $activeCount = $this->connections->countByUser($userId);
        if ($activeCount >= Connection::maxPerUser()) {
            abort(429, 'Maximum concurrent connections reached');
        }

        $connection = Connection::create($userId);
        $this->connections->register($connection);

        return new StreamedResponse(function () use ($userId, $connection, $lastEventId) {
            // Disable output buffering
            if (ob_get_level()) ob_end_clean();
            ini_set('zlib.output_compression', '0');

            $startTime = time();
            $lastHeartbeat = time();

            // Send missed events on reconnection
            if ($lastEventId) {
                $missed = $this->events->getAfterEventId($userId, $lastEventId);
                foreach ($missed as $event) {
                    echo $event->toSSE();
                    flush();
                }
            }

            // Main event loop
            $subscriber = Redis::connection();
            $channel = "events:user:{$userId}";
            $broadcastChannel = "events:broadcast";

            while (true) {
                // Check execution time limit
                if ((time() - $startTime) >= self::MAX_EXECUTION_TIME) {
                    echo "event: connection.timeout\ndata: {\"message\":\"Connection timeout, please reconnect\"}\n\n";
                    flush();
                    break;
                }

                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }

                // Poll for new events from Redis
                $message = $subscriber->lpop($channel);
                $broadcast = $subscriber->lpop($broadcastChannel);

                if ($message) {
                    echo $message;
                    flush();
                    $this->connections->touch($connection->id);
                }

                if ($broadcast) {
                    echo $broadcast;
                    flush();
                }

                // Heartbeat to keep connection alive
                if ((time() - $lastHeartbeat) >= self::HEARTBEAT_INTERVAL) {
                    echo ": heartbeat\n\n";
                    flush();
                    $lastHeartbeat = time();
                    $this->connections->touch($connection->id);
                }

                // Free memory
                gc_collect_cycles();

                // Small sleep to prevent CPU spinning
                usleep(100000); // 100ms
            }

            $this->connections->unregister($connection->id);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Nginx
        ]);
    }
}
