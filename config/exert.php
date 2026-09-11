<?php

return [
    // Request input key used to select a registered action.
    'action_key' => 'action',

    // Where to read the action key: 'query', 'body', or 'both' (Laravel input()).
    'action_source' => 'query',

    // Directory for make:action, relative to app/. Also determines the namespace.
    // For example, 'Domain/Actions' generates classes in App\Domain\Actions.
    'actions_path' => 'Http/Actions',
];
