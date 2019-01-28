<?php return [
    'plugin'            => [
        'name'                       => 'ResponsiveImages',
        'description'                => 'Fügt srcset und sizes Attribute zu <img>-Tags hinzu',
        'author'                     => 'OFFLINE GmbH',
        'manage_settings'            => 'Einstellungen für responsive Bilder',
        'manage_settings_permission' => 'Kann ResponsiveImages-Einstellungen verwalten',
    ],
    'settings' => [

        'tabs' => [
            'responsive_image' => 'Responsive Image',
            'focuspoint' => 'Focus-Point'
        ],

        'sections' => [
            'processing' => 'Verarbeitung',
            'processing_comment' => 'Einstellungen für die Verarbeitung der Bilder',

            'html' => 'HTML',
            'html_comment' => 'Einstellungen zum HTML-Code',

            'focuspoint' => 'Fokus-Punkt',
            'focuspoint_comment' => 'Einstellungen zum Fokus-Punkt',
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

        'focuspoint_class' => 'class-Attribut',
        'focuspoint_class_comment' => 'Individuelle Klasse für Fokuspunkt-Container  (standard: .focuspoint-container)',

        'focuspoint_data_x' => 'data-Attribut für X-Achse',
        'focuspoint_data_x_comment' => 'Dieses Attribut dient als Datenfeld eigens kreierten JavaScripts oder eigene Libraries für Focuspoint-Bilder (Voraussetzung: data-Attribut für Y-Achse muss ebenfalls gesetzt sein)',

        'focuspoint_data_y' => 'data-Attribut für Y-Achse',
        'focuspoint_data_y_comment' => 'Dieses Attribut dient als Datenfeld für eigens kreierten JavaScripts oder eigene Libraries für Focuspoint-Bilder (Voraussetzung: data-Attribut für X-Achse muss ebenfalls gesetzt sein)',

        'allow_inline_object' => 'Inline-CSS für object-fit und object-position',
        'allow_inline_object_comment' => 'Deaktiviere, wenn eigene Verarbeitung gewünscht.',

        'allow_inline_sizing' => 'Inline-CSS für Image Breite und Höhe',
        'allow_inline_sizing_comment' => 'Deaktiviere, wenn eigene Verarbeitung gewünscht.',
    ],
];