<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        if ($app['config']->get('database.default') === 'sqlite' && $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            $dbPath = $app['config']->get('database.connections.sqlite.database');
            $dir = dirname($dbPath);
            if (! empty($dir) && ! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if (! file_exists($dbPath)) {
                touch($dbPath);
            }
        }

        return $app;
    }
}
