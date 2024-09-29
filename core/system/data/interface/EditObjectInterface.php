<?php
namespace fmr\system\data\interface;

/**
 * Abstract class for all data holder classes.
 */
interface EditObjectInterface extends StorableObjectInterface {
    /**
     * Creates a new object.
     *
     * @param array $parameters
     * @return  StorableObjectInterface
     */
    public static function create(array $parameters = []);

    /**
     * Updates this object.
     *
     * @param array $parameters
     */
    public function update(array $parameters = []);

    /**
     * Updates the counters of this object.
     *
     * @param array $counters
     */
    public function updateCounters(array $counters = []);

    /**
     * Deletes this object.
     */
    public function delete();

    /**
     * Deletes all objects with the given ids and returns the number of deleted
     * objects.
     *
     * @param array $objectIDs
     * @return  int
     */
    public static function deleteAll(array $objectIDs = []);
}