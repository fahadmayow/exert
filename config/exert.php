<?php

return [
    // Request input key used to select a registered action.
    // Must start with an ASCII letter or underscore and contain only
    // ASCII letters, numbers, and underscores (no dots or brackets).
    'action_key' => 'action',

    // Where to read the action key: 'query', 'body', or 'both' (Laravel input()).
    'action_source' => 'query',

    // Directory for make:action, relative to app/. Also determines the namespace.
    // For example, 'Domain/Actions' generates classes in App\Domain\Actions.
    'actions_path' => 'Http/Actions',
];
