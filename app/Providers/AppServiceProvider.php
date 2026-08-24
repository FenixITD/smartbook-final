<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Infrastructure\Persistence\EloquentTransactionManager;
use App\Listeners\MergeCartOnLoginListener;
use App\Listeners\SendOrderCreatedEmailListener;
use App\Listeners\SendOrderStatusEmailListener;
use App\Models\ClickhouseActivity;
use App\Models\Order;
use App\Models\Review;
use App\Observers\ClickhouseActivityObserver;
use App\Observers\OrderObserver;
use App\Observers\ReviewObserver;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Repositories\Eloquent\AuthorRepository;
use App\Repositories\Eloquent\BookRepository;
use App\Repositories\Eloquent\CartItemRepository;
use App\Repositories\Eloquent\ConversationRepository;
use App\Repositories\Eloquent\FavoriteRepository;
use App\Repositories\Eloquent\GenreRepository;
use App\Repositories\Eloquent\MessageRepository;
use App\Repositories\Eloquent\OrderItemRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Cart\GuestCartStorageInterface;
use App\Services\Cart\SessionGuestCartStorage;
use App\Services\Clickhouse\ClickhouseActivityService;
use App\Services\Clickhouse\ClickhouseManagerService;
use Carbon\CarbonImmutable;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthorRepositoryInterface::class,
            AuthorRepository::class
        );

        $this->app->bind(
            BookRepositoryInterface::class,
            BookRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            CartItemRepositoryInterface::class,
            CartItemRepository::class
        );

        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );

        $this->app->bind(
            FavoriteRepositoryInterface::class,
            FavoriteRepository::class
        );

        $this->app->bind(
            GenreRepositoryInterface::class,
            GenreRepository::class
        );

        $this->app->bind(
            OrderItemRepositoryInterface::class,
            OrderItemRepository::class
        );

        $this->app->bind(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );

        $this->app->bind(
            TransactionManagerInterface::class,
            EloquentTransactionManager::class,
        );

        $this->app->bind(
            ConversationRepositoryInterface::class,
            ConversationRepository::class,
        );

        $this->app->bind(
            MessageRepositoryInterface::class,
            MessageRepository::class,
        );

        $this->app->bind(
            ActivityLogRepositoryInterface::class,
            ActivityLogRepository::class,
        );

        $this->app->bind(
            GuestCartStorageInterface::class,
            SessionGuestCartStorage::class,
        );

        // Single HTTP connection to ClickHouse reused for the whole request lifetime
        $this->app->singleton(
            ClickhouseManagerService::class
        );

        /**
         * Registering a client to work with the Elasticsearch search engine.
         */
        $this->app->singleton(Client::class, static fn () => ClientBuilder::create()
            ->setHosts([config('elasticsearch.host')])
            ->build()
        );

        $this->app->singleton(
            ClickhouseActivityService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Event::listen(
            Login::class,
            MergeCartOnLoginListener::class,
        );

        Event::listen(
            OrderStatusChangedEvent::class,
            SendOrderStatusEmailListener::class
        );

        Event::listen(
            OrderCreatedEvent::class,
            SendOrderCreatedEmailListener::class,
        );

        // ── RabbitMQ Notification Observers
        Order::observe(OrderObserver::class);
        Review::observe(ReviewObserver::class);
        ClickhouseActivity::observe(ClickhouseActivityObserver::class);

        Model::preventLazyLoading(config('app.env') !== 'production');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            config('app.env') === 'production',
        );

        Password::defaults(static fn (): Password => config('app.env') === 'production'
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
        );

        // Required by $middleware->throttleApi() (bootstrap/app.php); unauthenticated requests are keyed by IP
        RateLimiter::for('api', static fn (Request $request): Limit => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));
    }
}
