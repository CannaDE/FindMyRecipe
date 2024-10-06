<?php

namespace fmr\system\data\interface;

/**
 * Default interface for DatabaseObject-related actions.
 *
 */
interface DatabaseObjectActionInterface {
    /**
     * Executes the previously chosen action.
     */
    public function executeAction();

    /**
     * Validates action-related parameters.
     */
    public function validateAction();

    /**
     * Returns active action name.
     *
     * @return  string
     */
    public function getActionName();

    /**
     * Returns DatabaseObject-related object ids.
     *
     * @return  int[]
     */
    public function getObjectIDs();

    /**
     * Returns action-related parameters.
     *
     * @return  mixed[]
     */
    public function getParameters();

    /**
     * Returns results returned by active action.
     *
     * @return  mixed
     */
    public function getReturnValues();
}