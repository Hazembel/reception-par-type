<?php

return [
    'hero' => [
        'tagline' => 'Official ASTRA technical data',
        'badge' => 'Official source OFROU / ASTRA · Synchronized monthly',
        'title' => 'The TG number of your',
        'title_highlight' => 'registration document',
        'title_suffix' => ', decoded.',
        'subtitle' => 'Instantly access official technical data for any vehicle homologated in Switzerland — weight, engine, rims, emissions, cantonal taxes, official PDF.',
        'search_placeholder' => 'TG number, VIN, make/model…',
        'search_button' => 'Search',
        'stats' => [
            'vehicles' => 'Indexed Vehicles',
            'brands' => 'Covered Brands',
            'uptime' => 'SLA Uptime',
        ],
    ],
    'photo_band' => [
        'subtitle' => 'Official OFROU Data',
        'title' => 'For every vehicle driving in Switzerland',
    ],
    'features' => [
        'badge' => 'Our Strengths',
        'title' => 'Why choose reception-par-type.ch?',
        'subtitle' => 'The reference platform for automotive professionals, importers, and cantonal services.',
        'items' => [
            [
                'title' => 'Instantaneous',
                'badge' => '< 200 ms',
                'desc' => 'Results in less than 200 ms from our database synchronized monthly with ASTRA TARGA files.',
            ],
            [
                'title' => 'Official Source',
                'badge' => 'ASTRA / OFROU',
                'desc' => 'Data directly sourced from TG-Automobil, TG-Moto, and emissionen.txt files published by the Federal Roads Office (OFROU).',
            ],
            [
                'title' => 'GDPR & FADP',
                'badge' => 'Hosted CH',
                'desc' => 'Hosted in Switzerland. No personal data resold. Compliant with the Federal Act on Data Protection (revised FADP 2023).',
            ],
            [
                'title' => 'Official PDF Sheet',
                'badge' => 'Levels 5+',
                'desc' => 'Generate a complete PDF homologation sheet with European equivalents (Directive 1999/37/EC) in one click, ready for your procedures.',
            ],
            [
                'title' => 'B2B REST API',
                'badge' => 'Levels 6-8',
                'desc' => 'Integrate our data into your systems: ERP, configurator, workshop software. API key per client, monthly quota, automatic 429.',
            ],
            [
                'title' => 'Cantonal Taxes',
                'badge' => '12 cantons',
                'desc' => 'Simulate the annual vehicle tax in 12 Swiss cantons. Calculation based on official scales (weight, power, CO₂).',
            ],
        ],
    ],
    'how_it_works' => [
        'image_annotation' => 'Box 24 of the circulation permit',
        'badge' => 'How it works',
        'title' => 'From TG number to full sheet in 3 seconds',
        'steps' => [
            [
                'title' => 'Find the TG number',
                'desc' => 'It is located in box 24 of your Swiss vehicle registration document (e.g., 27.012.000.08.00004). You can also enter a full VIN — it will automatically be truncated to 9 characters for the search.',
            ],
            [
                'title' => 'Get the technical sheet',
                'desc' => 'Kerb weight, GVW, power kW/HP, displacement, fuel, approved tyre sizes, CO₂ emissions, Euro code, emission standards.',
            ],
            [
                'title' => 'Export or integrate',
                'desc' => 'Download the official PDF sheet or consume our data via the REST API. Each data point carries its ASTRA field reference and its EU equivalent (Directive 1999/37/EC).',
            ],
        ],
    ],
    'api' => [
        'badge' => 'B2B REST API',
        'title' => 'Integrate our data into your tools',
        'desc' => 'API key per client, monthly quota tailored to your volume, structured JSON responses. Requests without results are not billed.',
        'endpoints' => [
            [
                'desc' => 'Complete technical sheet',
            ],
            [
                'desc' => 'Tyre sizes + equivalents ±8%',
            ],
            [
                'desc' => 'Official PDF sheet (level 5+)',
            ],
            [
                'desc' => 'Simulated cantonal tax',
            ],
        ],
        'docs_btn' => 'View documentation',
        'keys_btn' => 'My API keys →',
    ],
    'professionals' => [
        'badge' => 'Professionals',
        'title' => 'Integrate ASTRA data into your workshop software',
    ],
    'official' => [
        'title' => 'The reference for Swiss automotive professionals',
    ],
    'cta' => [
        'title' => 'Ready to get started?',
        'desc' => 'Free access for basic data. Professional plans starting from a few francs per month.',
        'search_btn' => 'Search for a vehicle →',
        'pricing_btn' => 'View pricing',
    ],
];
