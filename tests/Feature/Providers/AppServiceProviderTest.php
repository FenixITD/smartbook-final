<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Listeners\MergeCartOnLoginListener;
use App\Listeners\SendOrderCreatedEmailListener;
use App\Listeners\SendOrderStatusEmailListener;
use App\Providers\AppServiceProvider;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rules\Password;
use ReflectionClass;
use Tests\TestCase;

final class AppServiceProviderTest extends TestCase
{
    public function test_it_registers_all_application_services_into_container(): void
    {
        $this->assertInstanceOf(AuthorRepositoryInterface::class, $this->app->make(AuthorRepositoryInterface::class));
        $this->assertInstanceOf(BookRepositoryInterface::class, $this->app->make(BookRepositoryInterface::class));
        $this->assertInstanceOf(UserRepositoryInterface::class, $this->app->make(UserRepositoryInterface::class));
        $this->assertInstanceOf(TransactionManagerInterface::class, $this->app->make(TransactionManagerInterface::class));
    }

    public function test_it_boots_services_and_configures_application_defaults(): void
    {
        Event::fake();

        (new AppServiceProvider($this->app))->boot();

        Event::assertListening(Login::class, MergeCartOnLoginListener::class);
        Event::assertListening(OrderStatusChangedEvent::class, SendOrderStatusEmailListener::class);
        Event::assertListening(OrderCreatedEvent::class, SendOrderCreatedEmailListener::class);

        $date = Date::now();
        $this->assertInstanceOf(CarbonImmutable::class, $date);

        $rules = Password::defaults();
        $this->assertInstanceOf(Password::class, $rules);
    }

    /**
     * @throws \ReflectionException
     */
    public function test_it_applies_strict_security_rules_in_production_environment(): void
    {
        $this->app['env'] = 'production';

        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        $passwordValidationRules = Password::defaults();

        $reflection = new ReflectionClass($passwordValidationRules);

        $minProperty = $reflection->getProperty('min');
        $mixedCaseProperty = $reflection->getProperty('mixedCase');
        $lettersProperty = $reflection->getProperty('letters');
        $numbersProperty = $reflection->getProperty('numbers');
        $symbolsProperty = $reflection->getProperty('symbols');
        $uncompromisedProperty = $reflection->getProperty('uncompromised');

        $this->assertSame(12, $minProperty->getValue($passwordValidationRules));
        $this->assertTrue($mixedCaseProperty->getValue($passwordValidationRules));
        $this->assertTrue($lettersProperty->getValue($passwordValidationRules));
        $this->assertTrue($numbersProperty->getValue($passwordValidationRules));
        $this->assertTrue($symbolsProperty->getValue($passwordValidationRules));
        $this->assertTrue($uncompromisedProperty->getValue($passwordValidationRules));
    }
}
