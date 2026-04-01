<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicion;
use App\Models\User;
use App\Models\Medidor;
use Carbon\Carbon;
use Faker\Factory as Faker;

class MedicionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Obtener todos los lotes con usuarios asociados
        $lotes = User::where('lote', '!=', '0')
                   ->where('medidor', '!=', 'N/A')
                   ->orderByRaw("CAST(REGEXP_REPLACE(lote, '[^0-9]', '') AS UNSIGNED)") // Ordenar numéricamente
                   ->get();

        $faker = Faker::create();

        foreach ($lotes as $lote) {
            $medidor = Medidor::where('lote', $lote->lote)->first();
            if (!$medidor) {
                continue;
            }

            // Fecha inicial: hace 12 meses desde hoy
            $fechaInicio = Carbon::now()->subMonths(12);

            // Valor inicial del medidor (entre 1000 y 5000)
            $valorAnterior = rand(1000, 5000);

            // Generar 12 mediciones (una por mes)
            for ($i = 0; $i < 12; $i++) {
                // Calcular la fecha actual (sumando meses)
                $fechaActual = $fechaInicio->copy()->addMonths($i);

                // Generar un consumo normal (entre 5 y 30 m³)
                $consumoNormal = rand(5, 30);

                // Decidir si esta medición será anómala (10% de probabilidad)
                $esAnomala = rand(1, 100) <= 10;

                if ($esAnomala) {
                    // Generar un consumo anómalo (muy alto o muy bajo)
                    $consumo = rand(1, 2) == 1 ? rand(100, 300) : rand(0, 2);
                } else {
                    $consumo = $consumoNormal;
                }

                // Calcular el valor medido actual
                $valorActual = $valorAnterior + $consumo;

                // Crear la medición con el lote completo (incluyendo "bis" si existe)
                Medicion::create([
                    'lote' => $lote->lote, // Usamos el lote completo
                    'medidor' => $lote->medidor,
                    'periodo' => 30,
                    'indice' => $i + 1,
                    'fecha' => $fechaActual->format('Y-m-d'),
                    'vencimiento' => $fechaActual->copy()->addDays(15)->format('Y-m-d'),
                    'tomaant' => $i == 0 ? $fechaActual->copy()->subMonth()->format('Y-m-d') : $fechaActual->copy()->subMonth()->format('Y-m-d'),
                    'medidaant' => $i == 0 ? $valorAnterior : $valorAnterior,
                    'valormedido' => $valorActual,
                    'consumo' => $consumo,
                    'inspector' => 'Inspector Automático',
                    'foto' => 'Sin foto',
                    'pagado' => rand(0, 1)
                ]);

                // Actualizar el valor anterior para la próxima iteración
                $valorAnterior = $valorActual;
            }
        }
    }
}

