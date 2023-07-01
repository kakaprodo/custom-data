<?php

return [

    /**
     * the path where all actions will be created
     */
    'action_path' => app_path(''),

    /**
     * The name of all actions classes
     */
    'action_folder' => 'Action',

    /**
     * Where the custom data folder will be created.
     * Note that. after the package is installed you
     * can change this path to your desired location
     * but you should only resolve the namespacing if
     * needed
     */
    'data_path' => app_path('Http'),

    /**
     * The name of all customData classes
     */
    'data_folder' => 'CustomData',
];
