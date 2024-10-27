<?php

/*
 * You can place your custom package configuration in here.
 */
return [

    /*
     * Default layout for your package views.
     * You can specify the path to a Blade layout that will be used
     * as the base layout for all package views.
     */
    'default_layout' => 'layouts.app', // Path to your default layout

    /*
     * Page Maker Configuration
     */
    'page_maker' => [

        /*
         * Default namespace for page classes.
         */
        'namespace' => 'App\\Livewire\\Page',

        /*

         * Default path for generated pages.
         * This is where the generated pages will be stored.
         */
        'default_path' => 'Livewire/Page/',

        /*
         * Default path for generated pages.
         * This is where the generated pages views will be stored.
         */
        'default_view_path' => 'views/livewire/page/',

        /*
         * Default path for generated pages.
         * This is where the generated pages tags will be stored.
         */
        'default_tag_path' => 'livewire.page.',
        
        // Other page maker related settings can be added here as needed.
    ],

];