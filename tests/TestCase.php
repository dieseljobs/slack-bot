<?php

namespace TheLHC\SlackBot\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use TheLHC\SlackBot\SlackBotServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Dotenv\Dotenv;

class TestCase extends BaseTestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->schema = $this->app['db']->connection()->getSchemaBuilder();
        $this->runTestMigrations();
        $this->beforeApplicationDestroyed(function () {
            $this->rollbackTestMigrations();
        });
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $dotenv = new Dotenv(__DIR__);
        $dotenv->load();

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);

    }

    /**
     * Get package service providers.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SlackBotServiceProvider::class
        ];
    }

    /**
     * Run migrations for tables only used for testing purposes.
     *
     * @return void
     */
    protected function runTestMigrations()
    {
        if (! $this->schema->hasTable('slack_log')) {
            $this->schema->create('slack_log', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('channel');
                $table->text('message');
                $table->tinyInteger('q');
            });
        }
    }

    /**
     * Rollback migrations for tables only used for testing purposes.
     *
     * @return void
     */
    protected function rollbackTestMigrations()
    {
        $this->schema->drop('slack_log');
    }

}
