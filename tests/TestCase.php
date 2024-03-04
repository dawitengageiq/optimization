<?php

use Illuminate\Foundation\Application;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    //use DatabaseSetup;
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
    // protected function setUp()
    // {
    //     parent::setUp();
    //     $this->setupDatabase();
    // }
}
