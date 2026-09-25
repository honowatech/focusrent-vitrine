<?php

return [

    'name' => env('ADMIN_NAME', 'Superadmin'),

    'email' => env('ADMIN_EMAIL', 'admin@focusrent.cm'),

    /*
    | Mot de passe initial du superadmin. Il est appliqué à la création
    | du compte, à chaque déploiement, tant que ce compte n'existe pas.
    */
    'password' => env('ADMIN_PASSWORD', 'Fr0cus-Adm!26'),

];
