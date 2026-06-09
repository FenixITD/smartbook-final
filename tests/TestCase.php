<?php

declare(strict_types=1);

namespace Tests;

use App\Models\ClickhouseActivity;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ClickhouseActivity::unsetEventDispatcher();
    }
}
