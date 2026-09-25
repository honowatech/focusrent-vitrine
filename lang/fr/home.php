<?php

return [
    'hero' => [
        'title_before' => 'La gestion locative',
        'title_highlight' => 'sans stress',
        'title_after' => ', optimisée pour l’Afrique',
        'subtitle' => 'Simplifiez la gestion de vos biens, automatisez la collecte des loyers, améliorez vos relations locataires et propulsez votre patrimoine immobilier.',
        'cta' => 'Create my account',
    ],
    'why' => [
        'title' => 'Pourquoi choisir Focus Rent ?',
        'items' => [
            [
                'icon' => 'bolt',
                'title' => '100 % digitale',
                'text' => 'Gérez tous vos biens depuis une interface unique et intuitive, avec alertes automatiques pour loyers et échéances.',
            ],
            [
                'icon' => 'mobile-alt',
                'title' => 'Compatibilité mobile',
                'text' => 'Travaillez de n’importe où depuis votre téléphone. Ne manquez plus une date importante.',
            ],
            [
                'icon' => 'users',
                'title' => 'Support réactif',
                'text' => '7 j/7, un agent est disponible pour un accompagnement humain et professionnel.',
            ],
        ],
    ],
    'features' => [
        'title' => 'Fonctionnalités principales',
        'cta' => 'Découvrir les tarifs',
        'items' => [
            [
                'icon' => 'university',
                'title' => 'Gestion de contrats',
                'text' => 'Créez, stockez et archivez vos baux, renouvellements, quittances et notifications automatisées.',
            ],
            [
                'icon' => 'calendar-check',
                'title' => 'Alertes et relances',
                'text' => 'Notifications automatiques pour loyers en retard. Relance manuelle à volonté.',
            ],
            [
                'icon' => 'chart-line',
                'title' => 'Tableaux de bord en temps réel',
                'text' => 'Pilotez votre patrimoine immobilier grâce à des dashboards détaillés et des rapports personnalisés.',
            ],
            [
                'icon' => 'sync-alt',
                'title' => 'Suivi des transactions',
                'text' => 'Chaque paiement effectué est enregistré de façon définitive, pour une visibilité sur le long terme.',
            ],
            [
                'icon' => 'comments',
                'title' => 'Communication facile',
                'text' => 'Envoyez des SMS à vos locataires à tout moment, pour informer ou relancer.',
            ],
            [
                'icon' => 'cogs',
                'title' => 'Maintenance et interventions',
                'text' => 'Enregistrez chaque problème signalé, vos prestataires et leurs interventions.',
            ],
        ],
    ],
    'gallery' => [
        'title' => 'Découvrez Focus Rent en images',
        'demo' => 'Démo',
        'prev' => 'Image précédente',
        'next' => 'Image suivante',
        'slides' => [
            ['src' => 'images/all_stats.webp', 'alt' => 'Tableau de bord Focus Rent'],
            ['src' => 'images/daily_stats.webp', 'alt' => 'Statistiques quotidiennes Focus Rent'],
            ['src' => 'images/settings.webp', 'alt' => 'Paramètres Focus Rent'],
        ],
    ],
    'pricing' => [
        'title' => 'Nos formules flexibles',
        'subtitle' => 'Une offre claire et adaptée à chaque gestionnaire, petit ou grand investisseur.',
        'period' => '/mois',
        'currency' => 'FCFA',
        'note' => 'Tarifs hors taxes. Facturation annuelle possible.',
        'choose' => 'Choisir :name',
        'plans' => [
            [
                'name' => 'Starter',
                'price' => '10 000',
                'price_value' => '10000',
                'icon' => 'home',
                'units' => '1 à 5 logements',
                'users' => '01 seul utilisateur',
                'multi_users' => false,
                'features' => [
                    'Gestion des contrats de bail',
                    'Tableaux de bord',
                    'Suivi des loyers et relances automatiques',
                    'Maintenance et interventions',
                    'Support e-mail et WhatsApp',
                ],
                'whatsapp' => 'Bonjour ! Je viens du site Focus Rent et je souhaite souscrire à la formule Starter.',
            ],
            [
                'name' => 'Business',
                'price' => '28 000',
                'price_value' => '28000',
                'icon' => 'home',
                'units' => 'Jusqu’à 20 logements',
                'users' => 'Multi-utilisateurs',
                'multi_users' => true,
                'features' => [
                    'Gestion des contrats de bail',
                    'Tableaux de bord',
                    'Suivi des loyers et relances automatiques',
                    'Maintenance et interventions',
                    'Support e-mail et WhatsApp',
                ],
                'whatsapp' => 'Bonjour ! Je viens du site Focus Rent et je souhaite souscrire à la formule Business.',
            ],
            [
                'name' => 'Premium',
                'price' => '59 000',
                'price_value' => '59000',
                'icon' => 'building',
                'units' => 'Jusqu’à 80 logements',
                'users' => 'Multi-utilisateurs',
                'multi_users' => true,
                'features' => [
                    'Gestion des contrats de bail',
                    'Tableaux de bord',
                    'Suivi des loyers et relances automatiques',
                    'Maintenance et interventions',
                    'Support e-mail et WhatsApp',
                ],
                'whatsapp' => 'Bonjour ! Je viens du site Focus Rent et je souhaite souscrire à la formule Premium.',
            ],
        ],
    ],
    'testimonials' => [
        'title' => 'Ils nous font confiance',
        'items' => [
            [
                'quote' => 'Focus Rent nous a permis de simplifier la gestion de nos +100 boutiques à Douala. Les alertes sont un vrai plus. Plus aucun oubli possible.',
                'author' => 'Odilon Pawa, @CTN Douala',
            ],
            [
                'quote' => 'Je gère mes logements à Yaoundé depuis l’Allemagne. Simplicité et efficacité, tout ce qu’il me fallait !',
                'author' => 'Adèle Mfonfou, Berlin / Yaoundé',
            ],
            [
                'quote' => 'Excellent pour mon agence : relances automatiques, envoi de SMS aux locataires, synthèses rapides pour ma comptabilité.',
                'author' => 'Abdoul Nji., Gestionnaire pro',
            ],
        ],
    ],
];
