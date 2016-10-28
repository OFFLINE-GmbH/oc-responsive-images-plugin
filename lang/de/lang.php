<?php return [
    'plugin'            => [
        'name'                       => 'ResponsiveImages',
        'description'                => 'Fügt srcset und sizes Attribute zu <img>-Tags hinzu',
        'author'                     => 'OFFLINE GmbH',
        'manage_settings'            => 'Einstellungen für responsive Bilder',
        'manage_settings_permission' => 'Kann ResponsiveImages-Einstellungen verwalten',
    ],
    'settings' => [

        'sections' => [
            'processing' => 'Verarbeitung',
            'processing_comment' => 'Einstellungen für die Verarbeitung der Bilder',

            'html' => 'HTML',
            'html_comment' => 'Einstellungen zum HTML-Code',
        ],

        'dimensions' => 'Generierte Bildgrössen',
        'dimensions_comment' => 'Komma-getrennte Liste mit zu erstellenden Bildbreiten (in px)',

        'allowed_extensions' => 'Beachtete Dateiendungen',
        'allowed_extensions_comment' => 'Komma-getrennte Liste mit Dateiendungen, die verarbeitet werden sollen',

        'log_unprocessable' => 'Unverarbeitbare Bilder loggen',
        'log_unprocessable_comment' => 'Erstellt einen Logeintrag für jedes Bild, das nicht verarbeitet werden kann',

        'alternative_src' => 'src-Attribut',
        'alternative_src_comment' => 'Verwende als Bildquelle dieses Attribut, nicht "src". Nützlich für Lazy-Loading-Plugins.',

        'alternative_src_set' => 'srcset-Attribut',
        'alternative_src_set_comment' => 'Füge die responsiven Bilder in dieses Attribut ein, nicht in "srcset". Nützlich für Lazy-Loading-Plugins.',

        'add_class' => 'class-Attribut',
        'add_class_comment' => 'Füge folgende Klasse zu jedem verarbeiteten Bild hinzu. Nützlich für die Verwendung mit CSS-Frameworks wie Bootstrap.',
    ]
];