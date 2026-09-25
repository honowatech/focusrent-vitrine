<?php

return [
    'hero' => [
        'title_before' => 'Rental management',
        'title_highlight' => 'without the stress',
        'title_after' => ', built for Africa',
        'subtitle' => 'Simplify how you run your properties, automate rent collection, improve tenant relationships and grow your real-estate portfolio.',
        'cta' => 'Create my account',
    ],
    'why' => [
        'title' => 'Why choose Focus Rent?',
        'items' => [
            [
                'icon' => 'bolt',
                'title' => 'Fully digital',
                'text' => 'Manage every property from one intuitive interface, with automatic alerts for rent and due dates.',
            ],
            [
                'icon' => 'mobile-alt',
                'title' => 'Mobile-ready',
                'text' => 'Work from anywhere on your phone. Never miss an important date.',
            ],
            [
                'icon' => 'users',
                'title' => 'Responsive support',
                'text' => '7 days a week, a real person is available for professional, human support.',
            ],
        ],
    ],
    'features' => [
        'title' => 'Core features',
        'cta' => 'See pricing',
        'items' => [
            [
                'icon' => 'university',
                'title' => 'Lease management',
                'text' => 'Create, store and archive leases, renewals, receipts and automated notices.',
            ],
            [
                'icon' => 'calendar-check',
                'title' => 'Alerts and reminders',
                'text' => 'Automatic notices for late rent. Manual follow-up whenever you need it.',
            ],
            [
                'icon' => 'chart-line',
                'title' => 'Real-time dashboards',
                'text' => 'Steer your portfolio with detailed dashboards and custom reports.',
            ],
            [
                'icon' => 'sync-alt',
                'title' => 'Transaction tracking',
                'text' => 'Every payment is recorded permanently, giving you long-term visibility.',
            ],
            [
                'icon' => 'comments',
                'title' => 'Easy communication',
                'text' => 'Text your tenants at any time to inform them or follow up.',
            ],
            [
                'icon' => 'cogs',
                'title' => 'Maintenance and jobs',
                'text' => 'Log every reported issue, your contractors and their interventions.',
            ],
        ],
    ],
    'gallery' => [
        'title' => 'See Focus Rent in pictures',
        'demo' => 'Demo',
        'prev' => 'Previous slide',
        'next' => 'Next slide',
        'slides' => [
            ['src' => 'images/all_stats.webp', 'alt' => 'Focus Rent dashboard'],
            ['src' => 'images/daily_stats.webp', 'alt' => 'Focus Rent daily statistics'],
            ['src' => 'images/settings.webp', 'alt' => 'Focus Rent settings'],
        ],
    ],
    'pricing' => [
        'title' => 'Flexible plans',
        'subtitle' => 'A clear offer for every manager, from small landlords to larger investors.',
        'period' => '/month',
        'currency' => 'XAF',
        'note' => 'Prices exclude tax. Annual billing available.',
        'choose' => 'Choose :name',
        'plans' => [
            [
                'name' => 'Starter',
                'price' => '10,000',
                'price_value' => '10000',
                'icon' => 'home',
                'units' => '1 to 5 units',
                'users' => '1 user',
                'multi_users' => false,
                'features' => [
                    'Lease management',
                    'Dashboards',
                    'Rent tracking and automatic reminders',
                    'Maintenance and jobs',
                    'Email and WhatsApp support',
                ],
                'whatsapp' => 'Hello! I am coming from the Focus Rent website and I want to subscribe to the Starter plan.',
            ],
            [
                'name' => 'Business',
                'price' => '28,000',
                'price_value' => '28000',
                'icon' => 'home',
                'units' => 'Up to 20 units',
                'users' => 'Multiple users',
                'multi_users' => true,
                'features' => [
                    'Lease management',
                    'Dashboards',
                    'Rent tracking and automatic reminders',
                    'Maintenance and jobs',
                    'Email and WhatsApp support',
                ],
                'whatsapp' => 'Hello! I am coming from the Focus Rent website and I want to subscribe to the Business plan.',
            ],
            [
                'name' => 'Premium',
                'price' => '59,000',
                'price_value' => '59000',
                'icon' => 'building',
                'units' => 'Up to 80 units',
                'users' => 'Multiple users',
                'multi_users' => true,
                'features' => [
                    'Lease management',
                    'Dashboards',
                    'Rent tracking and automatic reminders',
                    'Maintenance and jobs',
                    'Email and WhatsApp support',
                ],
                'whatsapp' => 'Hello! I am coming from the Focus Rent website and I want to subscribe to the Premium plan.',
            ],
        ],
    ],
    'testimonials' => [
        'title' => 'Trusted by property managers',
        'items' => [
            [
                'quote' => 'Focus Rent simplified the management of our 100+ shops in Douala. The alerts are a real plus. Nothing falls through the cracks.',
                'author' => 'Odilon Pawa, @CTN Douala',
            ],
            [
                'quote' => 'I manage my units in Yaoundé from Germany. Simple and effective — exactly what I needed.',
                'author' => 'Adèle Mfonfou, Berlin / Yaoundé',
            ],
            [
                'quote' => 'Excellent for my agency: automatic reminders, SMS to tenants, quick summaries for my accounts.',
                'author' => 'Abdoul Nji., professional manager',
            ],
        ],
    ],
];
