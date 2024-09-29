<?php
namespace fmr\system\data\interface;

/**
 * Provides a method to access the unique id of an object.
 */
interface IDObjectInterface {
    /**
     * Returns the unique id of the object.
     *
     * @return  int
     */
    public function getObjectID(): int;
}