<?php
namespace fmr\system\data\interface;

/**
 * Abstract class for all data holder classes.
 *
 */
interface StorableObjectInterface {
    /**
     * Returns the value of a object data variable with the given name or `null` if no
     * such data variable exists.
     *
     * @param string $name
     * @return  mixed
     */
    public function __get($name);

    /**
     * Determines if the object data variable with the given name is set and
     * is not NULL.
     *
     * @param string $name
     * @return  bool
     */
    public function __isset($name);

    /**
     * Returns the value of all object data variables.
     *
     * @return  mixed[]
     */
    public function getData();

    /**
     * Returns the name of the database table.
     *
     * @return  string
     */
    public static function getDatabaseTableName();

    /**
     * Returns the alias of the database table.
     *
     * @return  string
     */
    public static function getDatabaseTableAlias();

    /**
     * Returns true if database table index is an identity column.
     *
     * @return  bool
     */
    public static function getDatabaseTableIndexIsIdentity();

    /**
     * Returns the name of the database table index.
     *
     * @return  string
     */
    public static function getDatabaseTableIndexName();
}