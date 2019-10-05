<?php return [
    'plugin'            => [
        'name'                       => 'ResponsiveImages',
        'description'                => 'Ajoute les attributs srcset et taille à vos balises <img>',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Réglages pour les images adaptatives',
        'manage_settings_permission' => 'Peut accéder aux paramètres de ResponsiveImages.',
    ],

    'settings' => [

        'tabs' => [
            'responsive_image' => 'Responsive Image',
            'focuspoint' => 'Focus-Point'
        ],

        'sections' => [
            'processing' => 'Traitement',
            'processing_comment' => 'Configurez le traitement de vos images',

            'html' => 'HTML',
            'html_comment' => 'Paramètres du code HTML',
        ],

        'dimensions' => 'Tailles d\'images générées',
        'dimensions_comment' => 'Liste séparée par des virgules des largeurs d\'image à générer (en px)',

        'allowed_extensions' => 'Extensions de fichier traitées',
        'allowed_extensions_comment' => 'Liste séparée par des virgules des extensions de fichiers qui doivent être traitées.',

        'log_unprocessable' => 'Journaliser les images non manipulables',
        'log_unprocessable_comment' => 'Crée une entrée de journal pour chaque image qui n\'a pas pu être traitée.',

        'alternative_src' => 'attribut src',
        'alternative_src_comment' => 'Utilisez cet attribut comme source d\'image au lieu de "src". Utile pour les plugins "lazy loading".',

        'alternative_src_set' => 'attribut srcset',
        'alternative_src_set_comment' => 'Ajoutez les jeux d\'images générés à cet attribut au lieu de "srcset". Utile pour les plugins "lazy loading".',

        'add_class' => 'attribut class',
        'add_class_comment' => 'Ajoutez cette classe à chaque image traitée. Utile si vous utilisez un framework css comme Bootstrap.',
    ]
];