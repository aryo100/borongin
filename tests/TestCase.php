<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $url = $app['config']->get('database.connections.pgsql.url');
        if (! is_string($url) || parse_url($url, PHP_URL_HOST) !== 'test-db'
            || parse_url($url, PHP_URL_PATH) !== '/borongin_test') {
            throw new RuntimeException('Gunakan borongin.cmd test. Tes hanya boleh memakai service test-db dan database borongin_test.');
        }

        return $app;
    }
}
