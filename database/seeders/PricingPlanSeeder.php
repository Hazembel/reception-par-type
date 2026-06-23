<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

/**
 * Seeder : PricingPlanSeeder
 *
 * Initialise les 8 niveaux d'abonnement avec leurs quotas et prix.
 * Idempotent : utilise updateOrCreate() — peut être relancé sans doublon.
 *
 * Usage :
 *   php artisan db:seed --class=PricingPlanSeeder
 *   php artisan migrate --seed   (en développement)
 */
class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'level'                => 1,
                'name_fr'              => 'Gratuit',
                'name_de'              => 'Kostenlos',
                'name_it'              => 'Gratuito',
                'name_en'              => 'Free',
                'price_monthly_chf'    => 0,
                'price_yearly_chf'     => 0,
                'token_price_chf'      => 200,   // 2.00 CHF/jeton
                'web_monthly_limit'    => 10,
                'api_monthly_limit'    => 0,
                'api_rate_per_minute'  => 0,
                'can_export_pdf'       => false,
                'can_export_csv'       => false,
                'can_use_api'          => false,
                'can_compare'          => false,
                'can_use_tax_calc'     => false,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 1,
                'description_fr'       => '10 consultations gratuites par mois. Sans carte bancaire.',
            ],
            [
                'level'                => 2,
                'name_fr'              => 'Starter',
                'name_de'              => 'Starter',
                'name_it'              => 'Starter',
                'name_en'              => 'Starter',
                'price_monthly_chf'    => 900,   // 9.00 CHF
                'price_yearly_chf'     => 9000,  // 90.00 CHF (2 mois offerts)
                'token_price_chf'      => 200,
                'web_monthly_limit'    => 50,
                'api_monthly_limit'    => 0,
                'api_rate_per_minute'  => 0,
                'can_export_pdf'       => true,
                'can_export_csv'       => false,
                'can_use_api'          => false,
                'can_compare'          => true,
                'can_use_tax_calc'     => false,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 2,
                'description_fr'       => '50 fiches/mois + export PDF.',
            ],
            [
                'level'                => 3,
                'name_fr'              => 'Basic',
                'name_de'              => 'Basic',
                'name_it'              => 'Basic',
                'name_en'              => 'Basic',
                'price_monthly_chf'    => 1900,
                'price_yearly_chf'     => 19000,
                'token_price_chf'      => 180,  // Légèrement moins cher au niveau 3
                'web_monthly_limit'    => 150,
                'api_monthly_limit'    => 0,
                'api_rate_per_minute'  => 0,
                'can_export_pdf'       => true,
                'can_export_csv'       => true,
                'can_use_api'          => false,
                'can_compare'          => true,
                'can_use_tax_calc'     => true,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 3,
                'description_fr'       => '150 fiches/mois + export CSV + simulateur fiscal.',
            ],
            [
                'level'                => 4,
                'name_fr'              => 'Pro',
                'name_de'              => 'Pro',
                'name_it'              => 'Pro',
                'name_en'              => 'Pro',
                'price_monthly_chf'    => 4900,
                'price_yearly_chf'     => 49000,
                'token_price_chf'      => 150,
                'web_monthly_limit'    => 500,
                'api_monthly_limit'    => 0,
                'api_rate_per_minute'  => 0,
                'can_export_pdf'       => true,
                'can_export_csv'       => true,
                'can_use_api'          => false,
                'can_compare'          => true,
                'can_use_tax_calc'     => true,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 4,
                'description_fr'       => '500 fiches/mois. Idéal pour les professionnels.',
            ],
            [
                'level'                => 5,
                'name_fr'              => 'Pro+',
                'name_de'              => 'Pro+',
                'name_it'              => 'Pro+',
                'name_en'              => 'Pro+',
                'price_monthly_chf'    => 9900,
                'price_yearly_chf'     => 99000,
                'token_price_chf'      => 120,
                'web_monthly_limit'    => 1000,
                'api_monthly_limit'    => 0,
                'api_rate_per_minute'  => 0,
                'can_export_pdf'       => true,
                'can_export_csv'       => true,
                'can_use_api'          => false,
                'can_compare'          => true,
                'can_use_tax_calc'     => true,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 5,
                'description_fr'       => '1\'000 fiches/mois, accès illimité aux outils.',
            ],
            [
                'level'                => 6,
                'name_fr'              => 'Business',
                'name_de'              => 'Business',
                'name_it'              => 'Business',
                'name_en'              => 'Business',
                'price_monthly_chf'    => 19900,
                'price_yearly_chf'     => 199000,
                'token_price_chf'      => 100,
                'web_monthly_limit'    => -1,    // Illimité
                'api_monthly_limit'    => 5000,
                'api_rate_per_minute'  => 30,
                'can_export_pdf'       => true,
                'can_export_csv'       => true,
                'can_use_api'          => true,
                'can_compare'          => true,
                'can_use_tax_calc'     => true,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 6,
                'description_fr'       => 'Accès web illimité + API 5\'000 appels/mois.',
            ],
            [
                'level'                => 7,
                'name_fr'              => 'Business+',
                'name_de'              => 'Business+',
                'name_it'              => 'Business+',
                'name_en'              => 'Business+',
                'price_monthly_chf'    => 39900,
                'price_yearly_chf'     => 399000,
                'token_price_chf'      => 80,
                'web_monthly_limit'    => -1,
                'api_monthly_limit'    => 15000,
                'api_rate_per_minute'  => 60,
                'can_export_pdf'       => true,
                'can_export_csv'       => true,
                'can_use_api'          => true,
                'can_compare'          => true,
                'can_use_tax_calc'     => true,
                'is_public'            => true,
                'is_active'            => true,
                'display_order'        => 7,
                'description_fr'       => 'Accès illimité + API 15\'000 appels/mois + webhooks.',
            ],
            [
                'level'                => 8,
                'name_fr'              => 'Entreprise',
                'name_de'              => 'Enterprise',
                'name_it'              => 'Azienda',
                'name_en'              => 'Enterprise',
                'price_monthly_chf'    => 0,     // Sur devis
                'price_yearly_chf'     => null,
                'token_price_chf'      => 50,
                'web_monthly_limit'    => -1,
                'api_monthly_limit'    => -1,    // Illimité
                'api_rate_per_minute'  => 300,
                'can_export_pdf'       => true,
                'can_export_csv'       => true,
                'can_use_api'          => true,
                'can_compare'          => true,
                'can_use_tax_calc'     => true,
                'is_public'            => false, // Affiché "Sur devis" uniquement
                'is_active'            => true,
                'display_order'        => 8,
                'description_fr'       => 'Solution sur-mesure. API illimitée, marque blanche, SLA garanti.',
            ],
        ];

        foreach ($plans as $planData) {
            PricingPlan::updateOrCreate(
                ['level' => $planData['level']],
                $planData
            );
        }

        // Invalider le cache après le seeding
        PricingPlan::invalidateCache();

        $this->command->info('✅ 8 niveaux tarifaires initialisés.');
    }
}
