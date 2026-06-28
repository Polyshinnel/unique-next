<?php

namespace Tests\Feature\Environment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TestingDatabaseIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_environment_uses_isolated_database(): void
    {
        self::assertSame('mysql', config('database.default'));
        self::assertSame('uniqset2_testing', config('database.connections.mysql.database'));
        self::assertSame('uniqset2_testing', config('database.connections.mysql.username'));
        self::assertNotSame('uniqset2', config('database.connections.mysql.database'));
        self::assertNotSame('uniqset2', config('database.connections.mysql.username'));
    }
}
