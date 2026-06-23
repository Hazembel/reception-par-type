<?php
// ═══════════════════════════════════════════════════════════════════════════
// database/factories/VehicleFactory.php
// ═══════════════════════════════════════════════════════════════════════════

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $marques = ['Volkswagen', 'Audi', 'BMW', 'Mercedes', 'Toyota', 'Renault', 'Peugeot'];
        $marque  = $this->faker->randomElement($marques);
        $modele  = $this->faker->bothify('Model-##');

        // Génère un numéro TG unique au format ASTRA
        $tg = sprintf(
            '27.%03d.%03d.%02d.%05d',
            $this->faker->numberBetween(1, 999),
            $this->faker->numberBetween(1, 999),
            $this->faker->numberBetween(0, 25),
            $this->faker->unique()->numberBetween(1, 99999)
        );

        return [
            // L'ULID est généré automatiquement par le trait HasUlids
            'numero_tg'         => $tg,
            'vin_prefix'        => strtoupper($this->faker->bothify('?#?###??#')), // 9 caractères
            'eu_type_approval'  => 'e' . $this->faker->numberBetween(1, 27) . '*2007/46*' . $this->faker->numberBetween(1000, 9999) . '*0' . $this->faker->numberBetween(1, 9),
            'marque'            => $marque,
            'modele'            => $modele,
            'variante'          => $this->faker->randomElement(['2.0 TDI', '1.6 TSI', 'eTron', '320d']),
            'vehicle_type'      => 'car',
            'slug'              => null, // Généré par le hook booted()
            'energie'           => $this->faker->randomElement(['01', '02', '14']),
            'puissance_kw'      => $this->faker->numberBetween(50, 300),
            'cylindree'         => $this->faker->randomElement([1395, 1598, 1968, 2993]),
            'boite_vitesse'     => $this->faker->randomElement(['M', 'A', 'DSG']),
            'poids_vide'        => $this->faker->numberBetween(1000, 2200),
            'poids_total'       => $this->faker->numberBetween(1600, 2800),
            'poids_remorquable' => $this->faker->numberBetween(0, 2500),
            'co2'               => $this->faker->numberBetween(0, 250),
            'code_emissions'    => $this->faker->randomElement(['EURO5', 'EURO6', 'EURO6d']),
            'pollution_norm'    => $this->faker->randomElement(['Euro 5', 'Euro 6', 'Euro 6d-ISC-FCM']),
            'nb_trous'          => $this->faker->randomElement([4, 5]),
            'entraxe'           => $this->faker->randomElement([100, 108, 112, 120]),
            'alesage'           => $this->faker->randomElement([57, 63, 66, 72]),
            'deport_et'         => $this->faker->numberBetween(20, 55),
            'pneus_origine'     => $this->faker->randomElement(['205/55R16 91V', '225/45R17 94W', '195/65R15 91H']),
            'is_active'         => true,
            'imported_at'       => now(),
        ];
    }

    /** État : moto */
    public function motorcycle(): static
    {
        return $this->state(fn() => [
            'vehicle_type'      => 'motorcycle',
            'cylindree'         => $this->faker->randomElement([125, 600, 750, 1000]),
            'poids_vide'        => $this->faker->numberBetween(120, 300),
            'poids_remorquable' => 0,
            'nb_trous'          => null,
            'entraxe'           => null,
        ]);
    }

    /** État : véhicule inactif */
    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }
}
