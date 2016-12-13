<?php

namespace TheLHC\SlackBot;

use Illuminate\Support\ServiceProvider;
use TheLHC\SlackBot\Console\MigrationCommand;

class SlackBotServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/slack_bot.php' => config_path('slack_bot.php')
        ], 'config');

        $this->commands([
            MigrationCommand::class
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/slack_bot.php', 'slack_bot');

        $this->app->bind(SlackBot::class, function ($app) {
            return new SlackBot($app['config']->get('slack_bot'));
        });
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'slack_bot.migration',
        ];
    }
}
