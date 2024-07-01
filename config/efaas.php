<?php

return [

    /**
     * eFaas client config
     */
    'client' => [
        /**
         * eFaas Client ID
         */
        'client_id' => env('EFAAS_CLIENT_ID'),

        /**
         * eFaas Client Secret
         */
        'client_secret' => env('EFAAS_CLIENT_SECRET'),

        /**
         * eFaas Redirect url
         */
        'redirect' => env('EFAAS_REDIRECT_URI'),

        /**
         * Development mode
         * supports "production" and "development"
         */
        'mode' => env('EFAAS_MODE', 'development'),
    ],

    /*
     * This model will be used to store efaas session sids
     * The class must implement \Javaabu\EfaasSocialite\Contracts\EfaasSessionContract
     */
    'session_model' => \Javaabu\EfaasSocialite\Models\EfaasSession::class,

    /*
     * This handler will be used to manage saving and destroying efaas session records
     * The class must implement \Javaabu\EfaasSocialite\Contracts\EfaasSessionHandlerContract
     */
    'session_handler' => \Javaabu\EfaasSocialite\EfaasSessionHandler::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the EfaasSession model shipped with this package.
     */
    'table_name' => 'efaas_sessions',

    /*
     * This is the database connection that will be used by the migration and
     * the EfaasSession model shipped with this package. In case it's not set
     * Laravel's database.default will be used instead.
     */
    'database_connection' => env('EFAAS_SESSIONS_DB_CONNECTION'),
];
