<?php

return [
    /**
     * Namespace for the cache keys. This is useful if you have multiple applications using the same cache store.
     */
    'cache_prefix' => 'currency-exchange-rates',

    /**
     * Default provider to use for fetching exchange rates.
     */
    'default_provider' => 'frankfurter',

    'frankfurter' => [
        'cache_ttl' => 60, // in minutes
    ],
];
