<?php

namespace Webkul\Pos\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Pos\Database\Seeders\DatabaseSeeder;
use Webkul\Pos\Providers\PosServiceProvider;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->description = trans('pos::app.commands.install.description');

        parent::__construct();
    }

    /**
     * Install & configure the POS extension
     */
    public function handle()
    {
        $this->info(trans('pos::app.commands.install.starting-installation'));

        $this->warn(trans('pos::app.commands.install.migrating-tables'));
        $this->call('migrate');

        $this->warn(trans('pos::app.commands.install.seeding-tables'));
        app(DatabaseSeeder::class)->run();

        $this->warn(trans('pos::app.commands.install.publishing-assets'));
        $this->call('vendor:publish', [
            '--provider' => PosServiceProvider::class,
            '--force'    => true,
        ]);

        $this->warn(trans('pos::app.commands.install.clearing-cache'));
        $this->call('optimize:clear');

        $this->info(trans('pos::app.commands.install.installed-successfully'));
    }
}
