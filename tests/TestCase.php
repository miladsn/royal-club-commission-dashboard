<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.sqlite.database');
        $url = $app['config']->get('database.connections.sqlite.url');

        if ($connection !== 'sqlite' || $database !== ':memory:' || ! empty($url)) {
            throw new RuntimeException('Tests require an isolated in-memory SQLite database. Clear the configuration cache and try again.');
        }

        return $app;
    }
}
