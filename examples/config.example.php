<?php

return [

    'credentials' => [
        'oauth' => [
            /**
             * The client ID is required for some API calls.
             * You cannot create them yourself, you need to ask the StudioIntern team.
             */
            'client_id'     => '_your_client_id_',

            /**
             * The client secret is required for some API calls.
             * You cannot create it yourself, you need to ask the StudioIntern team.
             */
            'client_secret' => '_your_client_secret_',

            /**
             * The grant type is required for some API calls.
             * Don't change this unless you were told to do so by the StudioIntern team.
             */
            'grant_type'    => 'password',

            /**
             * The scope is required for some API calls.
             * Default is 'api:read api:write'.
             * Don't change this unless you were told to do so by the StudioIntern team.
             */
            'scope'         => 'api:read api:write',
        ],

        /**
         * The API key is required for ALL API calls.
         * You find it in the StudioIntern profile page.
         */
        'api_key'           => '_your_api_key_',

        /**
         * The tenant ID is required for ALL API calls.
         * This ist the hostname part of your StudioIntern account.
         */
        'tenant_id'         => '_your_tenant_id_',
    ],

    'headers' => [

        /**
         * The Accept header is required.
         * Default is 'application/json'.
         */
        'Accept'            => 'application/json',

        /**
         * You can add additional headers here if required.
         * For example, if may be useful to add a X-Forwarded-For header
         * if you are behind a firewall.
         */
        //'X-Forwarded-For'   => '192.168.178.10',
    ],

    'misc' => [

        /**
         * The API base URI is the base URL of the API.
         * Keep this as is unless you were told to change it by the StudioIntern team.
         */
        'api_base_uri'  => 'https://api.studiointern.de',

        /**
         * The storage can be 'session' or 'file'.
         * Default is 'session'.
         */
        'storage'       => 'session',

        /**
         * The storage path must be outside the webroot.
         * Adjust this to your needs, defaults to __DIR__ . '/storage'.
         * You can ignore this if you are setting the storage to 'session'.
         */
        'storage_path'  => __DIR__ . '/storage',

        /**
         * The client type can be 'guzzle' or 'curl'.
         * Default is 'guzzle'.
         */
        'client_type'   => 'guzzle',

        /**
         * The timezone is important for the token expiration check.
         * Default is 'Europe/Berlin'.
         */
        'timezone'      => 'Europe/Berlin',

        /**
         * The timeout is the maximum number of seconds to wait for a response.
         * Default is 30 seconds.
         */
        'timeout'       => 30,
    ]
];
