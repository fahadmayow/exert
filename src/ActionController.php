<?php

namespace Exert;

use Illuminate\Http\Request;
use LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Selects a registered action from request input 
 * and delegates execution to it.
 */
abstract class ActionController
{
    public const DEFAULT_ACTION = '/';

    public const FALLBACK_ACTION = '*';

    /**
     * Map permitted action names to their action class names.
     *
     * @var array<string, class-string<ActionInterface>>
     */
    protected array $actions = [];

    /**
     * Expose the registered action names and classes for inspection tools.
     *
     * @return array<string, class-string<ActionInterface>>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Resolve and execute the requested action, or throw a 404 if unavailable.
     */
    public function resolve(Request $request): mixed
    {
        // Clear previous metadata if the same request is resolved more than once.
        foreach (['exert.action', 'exert.action_class', 'exert.controller', 'exert.action_id'] as $key) {
            $request->attributes->remove($key);
        }

        $actionKey = config('exert.action_key', 'action');

        if (
            !is_string($actionKey) ||
            preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/', $actionKey) !== 1
        ) {
            throw new LogicException(
                'exert.action_key must start with a letter or underscore '
                .'and contain only letters, numbers, and underscores.'
            );
        }

        $input = match (config('exert.action_source', 'query')) {
            'query' => $request->query(),
            'body' => $request->isJson()
                ? $request->json()->all()
                : $request->post(),
            'both' => $request->input(),
            default => throw new LogicException('exert.action_source must be query, body, or both.'),
        };
        $hasAction = array_key_exists($actionKey, $input);
        $currentAction = $hasAction ? $input[$actionKey] : null;
        $actions = $this->getActions();

        $resolvedAction = match (true) {
            !$hasAction && array_key_exists(self::DEFAULT_ACTION, $actions) => self::DEFAULT_ACTION,
            !$hasAction => self::FALLBACK_ACTION,
            is_string($currentAction)
                && $currentAction !== self::DEFAULT_ACTION
                && array_key_exists($currentAction, $actions) => $currentAction,
            default => self::FALLBACK_ACTION,
        };

        // Only dispatch registered names; never treat user input as a class name.
        if (!array_key_exists($resolvedAction, $actions)) {
            throw new NotFoundHttpException('Action not found.');
        }

        $actionClass = $actions[$resolvedAction];

        if (!class_exists($actionClass)) {
            throw new NotFoundHttpException('Action not found.');
        }

        // Use the container so action constructors can receive dependencies too.
        $action = app()->make($actionClass);

        // A registered class without the contract is a configuration error.
        if (!$action instanceof ActionInterface) {
            throw new LogicException(
                $actionClass . ' must implement ActionInterface.'
            );
        }

        // Use validated registry values, not arbitrary client input, as operation labels.
        $request->attributes->set('exert.action', $resolvedAction);
        $request->attributes->set('exert.action_class', $actionClass);
        $request->attributes->set('exert.controller', static::class);
        $request->attributes->set('exert.action_id', static::class.'::'.$resolvedAction);

        return $action->initiate($request);
    }
}
