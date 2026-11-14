<?php

/*
|--------------------------------------------------------------------------
| Règles communes du module de recherche
|--------------------------------------------------------------------------
|
| Ce fichier pilote à la fois la palette (MiniSearch, via l'attribut
| data-search-rules du <dialog>) et la page de résultats serveur
| (App\Support\SearchEngine). Les deux doivent trouver la même chose.
|
*/

return [

    // Longueur de la requête acceptée.
    'min_chars' => 2,
    'max_chars' => 80,

    // Résultats affichés par groupe dans la palette, puis sur la page.
    'per_group' => 5,
    'per_group_page' => 8,

    // Ordre des groupes : ce que les visiteurs cherchent le plus en premier,
    // les pages en dernier.
    'groups' => ['feature', 'plan', 'faq', 'page'],

    // Pondération des champs.
    'boost' => [
        'label' => 5,
        'title' => 1.5,
        'excerpt' => 2,
        'domain' => 1.5,
        'body' => 1,
    ],

    // Une faute tolérée à partir de cette longueur.
    'fuzzy_min_chars' => 5,

    // Un mot tapé qui existe tel quel dans l'index garde ses prolongements
    // (« bail » → « bailleur ») mais loin derrière.
    'known_word' => [
        'min_chars' => 4,
        'prefix_weight' => 0.25,
    ],

    // Groupes de synonymes. Les synonymes de moins de 4 lettres ne
    // s'appliquent qu'en mot entier.
    'synonyms' => [
        ['prix', 'tarif', 'tarifs', 'abonnement', 'formule', 'price', 'pricing', 'plan', 'cout', 'coût'],
        ['mobile money', 'orange money', 'mtn', 'momo', 'paiement', 'payer', 'payment'],
        ['loyer', 'loyers', 'rent', 'quittance', 'quittances'],
        ['bail', 'baux', 'contrat', 'contrats', 'lease', 'leases'],
        ['locataire', 'locataires', 'tenant', 'tenants'],
        ['proprietaire', 'bailleur', 'agence', 'gestionnaire', 'landlord', 'agency'],
        ['sms', 'notification', 'notifications', 'relance', 'relances', 'alerte', 'alertes', 'reminder', 'reminders'],
        ['logement', 'logements', 'appartement', 'maison', 'immeuble', 'unite', 'unites', 'unit', 'units', 'property'],
        ['demo', 'démo', 'essai', 'test', 'trial'],
        ['whatsapp', 'contact', 'support', 'assistance', 'aide', 'help'],
        ['dashboard', 'tableau de bord', 'statistiques', 'rapport', 'reporting'],
        ['maintenance', 'intervention', 'interventions', 'reparation', 'repair'],
        ['securite', 'donnees', 'confidentialite', 'rgpd', 'privacy', 'data'],
        ['cgu', 'conditions', 'terms'],
    ],

    // Mots vides retirés de la requête et de l'index.
    'stop_words' => [
        'le', 'la', 'les', 'l', 'un', 'une', 'des', 'de', 'du', 'd', 'et', 'ou', 'en', 'au', 'aux',
        'a', 'à', 'ce', 'ces', 'cet', 'cette', 'se', 'sa', 'son', 'ses', 'sur', 'pour', 'par', 'pas',
        'ne', 'est', 'sont', 'que', 'qui', 'quoi', 'dans', 'avec', 'vos', 'vous', 'nous', 'notre', 'nos',
        'the', 'an', 'and', 'or', 'of', 'to', 'in', 'on', 'for', 'is', 'are', 'it', 'its', 'with',
        'your', 'you', 'our', 'we', 'this', 'that', 'be', 'by', 'at', 'as', 'from',
    ],

];
