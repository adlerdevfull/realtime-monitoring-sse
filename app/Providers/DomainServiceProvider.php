<?php
declare(strict_types=1);
namespace App\Providers;
use Domain\Connection\Repositories\ConnectionRepositoryInterface;
use Domain\Event\Repositories\EventRepositoryInterface;
use Infrastructure\Persistence\Repositories\{RedisConnectionRepository, RedisEventRepository};
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(EventRepositoryInterface::class, RedisEventRepository::class);
        $this->app->bind(ConnectionRepositoryInterface::class, RedisConnectionRepository::class);
    }
}
