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

        $currentAction = $request->input(config('exert.action_key', 'action'));
        $actions = $this->getActions();

        // Only dispatch registered names; never treat user input as a class name.
        if (
            !is_string($currentAction) ||
            !array_key_exists($currentAction, $actions)
        ) {
            throw new NotFoundHttpException('Action not found.');
        }

        $actionClass = $actions[$currentAction];

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
        $request->attributes->set('exert.action', $currentAction);
        $request->attributes->set('exert.action_class', $actionClass);
        $request->attributes->set('exert.controller', static::class);
        $request->attributes->set('exert.action_id', static::class.'::'.$currentAction);

        return $action->initiate($request);
    }
}
