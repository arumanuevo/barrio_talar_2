<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunMedicionesSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:mediciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar el seeder de mediciones';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Ejecutando el seeder de mediciones...');

        // Ejecutar el seeder
        Artisan::call('db:seed', [
            '--class' => 'MedicionesSeeder',
            '--force' => true
        ]);

        $this->info('Seeder de mediciones ejecutado correctamente!');
        return 0;
    }
}
