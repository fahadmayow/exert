<?php

namespace Exert;

use Illuminate\Http\Request;

/**
 * Defines the entry point used by the controller to execute an action.
 * Leaves handle() out so each action can declare its own dependencies.
 */
interface ActionInterface
{
    /**
     * Process the request and return the action's result or an early response.
     */
    public function initiate(Request $request): mixed;
}
