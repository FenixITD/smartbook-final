<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Infrastructure\Persistence\EloquentTransactionManager;
use App\Listeners\MergeCartOnLoginListener;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Order;
use App\Models\Review;
use App\Observers\AuthorObserver;
use App\Observers\BookObserver;
use App\Observers\GenreObserver;
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
use Carbon\CarbonImmutable;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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

        $this->app->singleton(Client::class, static fn () => ClientBuilder::create()
            ->setHosts([config('elasticsearch.host')])
            ->build());
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

        // ── RabbitMQ Notification Observers
        Author::observe(AuthorObserver::class);
        Book::observe(BookObserver::class);
        Genre::observe(GenreObserver::class);
        Order::observe(OrderObserver::class);
        Review::observe(ReviewObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(static fn (): Password|null => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
