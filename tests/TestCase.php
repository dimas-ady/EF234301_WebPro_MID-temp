<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set([
            'app.env' => 'testing',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = $this->app->make('config');

        if ($configuration->get('database.default') !== 'sqlite'
            || $configuration->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Feature tests must use the isolated SQLite in-memory database.');
        }

        $this->withoutVite();
        $this->withHeader('Sec-Fetch-Site', 'same-origin');
    }
}
