<?php

namespace TheLHC\SlackBot\Console;

use Illuminate\Console\Command;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */

    protected $name = 'slack_bot:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Slack Log specifications.';

    /**
     * Handle command execution for laravel 5.1
     */
    public function fire()
    {
        $this->handle();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('');
        $this->info('Tables: slack_log');
        $this->comment('A migration that creates "Slack Bot" tables will be created in app/database/migrations directory');
        $this->line('');
        if ($this->confirm("Proceed with the migration creation? [Yes|no]")) {
            $this->line('');
            $this->info("Creating migration...");
            if ($this->createMigration()) {
                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Coudn't create migration.\n Check the write permissions".
                    " within the app/database/migrations directory."
                );
            }
            $this->line('');
        }
    }

    /**
     * Create the migration.
     *
     * @return bool
     */
    protected function createMigration()
    {
        $migration_file = base_path('/database/migrations').'/'.date('Y_m_d_His').'_create_slack_log_table.php';
        if (! file_exists($migration_file) && $fs = fopen($migration_file, 'x')) {
            fwrite($fs, file_get_contents(__DIR__.'/../migrations/create_slack_log_table.php'));
            fclose($fs);
            return true;
        }
        return false;
    }
}
