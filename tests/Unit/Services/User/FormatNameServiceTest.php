<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User;

use App\Services\User\FormatNameService;
use Tests\TestCase;

class FormatNameServiceTest extends TestCase
{
    public function test_initials_returns_empty_string_when_null(): void
    {
        $this->assertSame('', FormatNameService::initials(null));
    }

    public function test_initials_returns_empty_string_when_empty(): void
    {
        $this->assertSame('', FormatNameService::initials('   '));
    }

    public function test_initials_formats_single_word(): void
    {
        $this->assertSame('J.', FormatNameService::initials('John'));
    }

    public function test_initials_formats_multiple_words(): void
    {
        $this->assertSame('J. D.', FormatNameService::initials('John Doe'));
    }

    public function test_initials_ignores_extra_spaces(): void
    {
        $this->assertSame('J. R. R. T.', FormatNameService::initials('  John  Ronald Reuel   Tolkien '));
    }

    public function test_initials_capitalizes_letters(): void
    {
        $this->assertSame('L. T.', FormatNameService::initials('leo tolstoy'));
    }
}
