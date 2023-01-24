<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\Framework\Foundation\Application;
use FluentCalendar\Database\DBMigrator;
use FluentCalendar\Database\DBSeeder;

class ActivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle($network_wide = false)
    {
        DBMigrator::run($network_wide);
        DBSeeder::run();
    }
}
