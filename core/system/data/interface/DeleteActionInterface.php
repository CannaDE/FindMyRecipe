<?php

namespace fmr\system\data;

/**
 * Every database object action whose objects can be deleted (via AJAX) has to
 * implement this interface.
 */
interface DeleteActionInterface {
    /**
     * Deletes the relevant objects and returns the number of deleted objects.
     *
     * @return  int
     */
    public function delete();

    /**
     * Validates the "delete" action.
     */
    public function validateDelete();
}