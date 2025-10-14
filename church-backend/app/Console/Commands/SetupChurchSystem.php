<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupChurchSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'church:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the Church Database and Communication System';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Church Database and Communication System...');

        // Run migrations
        $this->info('Running database migrations...');
        Artisan::call('migrate');
        $this->info('✓ Migrations completed');

        // Run seeders
        $this->info('Seeding database with initial data...');
        Artisan::call('db:seed');
        $this->info('✓ Database seeded');

        // Generate JWT secret if not exists
        if (empty(config('jwt.secret'))) {
            $this->info('Generating JWT secret...');
            Artisan::call('jwt:secret');
            $this->info('✓ JWT secret generated');
        }

        $this->info('');
        $this->info('🎉 Church System setup completed successfully!');
        $this->info('');
        $this->info('Default users created:');
        $this->info('  Admin: admin@church.com / password');
        $this->info('  Pastor: pastor@church.com / password');
        $this->info('  Finance: finance@church.com / password');
        $this->info('');
        $this->info('You can now start the server with: php artisan serve');
    }
}
