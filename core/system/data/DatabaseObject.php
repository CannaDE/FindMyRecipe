<?php
namespace fmr\system\data;

use fmr\system\data\interface\StorableObjectInterface;
use fmr\FindMyRecipe;

/**
 * Abstract class for all data holder classes.
 */
abstract class DatabaseObject implements StorableObjectInterface {
    /**
     * database table for this object
     * @var string
     */
    protected static string $databaseTableName = '';

    /**
     * indicates if database table index is an identity column
     * @var bool
     */
    protected static bool $databaseTableIndexIsIdentity = true;

    /**
     * name of the primary index column
     * @var string
     */
    protected static string $databaseTableIndexName = '';

    /**
     * sort field
     * @var mixed
     */
    protected static $sortBy;

    /**
     * sort order
     * @var mixed
     */
    protected static $sortOrder;

    /**
     * object data
     * @var array
     */
    protected $data;

    /**
     * Creates a new instance of the DatabaseObject class.
     *
     * @param mixed $id
     * @param array $row
     * @param DatabaseObject $object
     */
    public function __construct($id, ?array $row = null, ?self $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT  *
                    FROM    " . static::getDatabaseTableName() . "
                    WHERE   " . static::getDatabaseTableIndexName() . " = ?";
                    
            $statement = FindMyRecipe::getDB()->prepareStatement($sql);
            $statement->execute([$id]);
            $row = $statement->fetchArray();
            

            // enforce data type 'array'
            if ($row === false) {
                $row = [];
            }
        } elseif ($object !== null) {
            $row = $object->data;
        }

        $this->handleData($row);
    }

    /**
     * Stores the data of a database row.
     *
     * @param array $data
     */
    protected function handleData($data)
    {
        // provide a logical false value for - assumed numeric - primary index
        if (!isset($data[static::getDatabaseTableIndexName()])) {
            $data[static::getDatabaseTableIndexName()] = 0;
        }

        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Returns the id of the object.
     *
     * @return  int
     */
    public function getObjectID(): int
    {
        return $this->data[static::getDatabaseTableIndexName()];
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableName()
    {
        $className = static::class;
        $classParts = \explode('\\', $className);

        if (static::$databaseTableName !== '') {
            return $classParts[0] . '_' . static::$databaseTableName;
        }

        static $databaseTableNames = [];
        if (!isset($databaseTableNames[$className])) {
            $databaseTableNames[$className] = $classParts[0] . '_' . \strtolower(\implode(
                    '_',
                    \preg_split(
                        '~(?=[A-Z](?=[a-z]))~',
                        \array_pop($classParts),
                        -1,
                        \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
                    )
                ));
        }

        return $databaseTableNames[$className];
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableAlias()
    {
        if (static::$databaseTableName !== '') {
            return static::$databaseTableName;
        }

        $className = static::class;
        static $databaseTableAliases = [];
        if (!isset($databaseTableAliases[$className])) {
            $classParts = \explode('\\', $className);
            $databaseTableAliases[$className] = \strtolower(\implode(
                '_',
                \preg_split(
                    '~(?=[A-Z](?=[a-z]))~',
                    \array_pop($classParts),
                    -1,
                    \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
                )
            ));
        }

        return $databaseTableAliases[$className];
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableIndexIsIdentity(): bool
    {
        return static::$databaseTableIndexIsIdentity;
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableIndexName()
    {
        if (static::$databaseTableIndexName !== '') {
            return static::$databaseTableIndexName;
        }

        $className = static::class;
        static $databaseTableIndexNames = [];
        if (!isset($databaseTableIndexNames[$className])) {
            $classParts = \explode('\\', $className);
            $parts = \preg_split(
                '~(?=[A-Z](?=[a-z]))~',
                \array_pop($classParts),
                -1,
                \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
            );
            $databaseTableIndexNames[$className] = \strtolower(\array_pop($parts)) . 'ID';
        }

        return $databaseTableIndexNames[$className];
    }

    /**
     * Sorts a list of database objects.
     *
     * @param DatabaseObject[] $objects
     * @param mixed $sortBy
     * @param string $sortOrder
     * @param bool $maintainIndexAssociation
     */
    public static function sort(array &$objects, mixed $sortBy, string $sortOrder = 'ASC', bool $maintainIndexAssociation = true)
    {
        $sortArray = $objects2 = [];
        foreach ($objects as $idx => $obj) {
            /** @noinspection PhpVariableVariableInspection */
            $sortArray[$idx] = $obj->{$sortBy};

            // array_multisort will drop index association if key is not a string
            if ($maintainIndexAssociation) {
                $objects2[$idx . 'x'] = $obj;
            }
        }

        if ($maintainIndexAssociation) {
            $objects = [];
            \array_multisort($sortArray, $sortOrder == 'ASC' ? \SORT_ASC : \SORT_DESC, $objects2);

            $objects = [];
            foreach ($objects2 as $idx => $obj) {
                $objects[\substr($idx, 0, -1)] = $obj;
            }
        } else {
            \array_multisort($sortArray, $sortOrder == 'ASC' ? \SORT_ASC : \SORT_DESC, $objects);
        }
    }
}