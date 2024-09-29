<?php
namespace fmr\system\data;

use BadMethodCallException;
use fmr\system\database\PreparedStatementConditionBuilder;
use fmr\system\event\EventHandler;
use fmr\system\exception\SystemException;
use fmr\FindMyRecipe;

/**
 * this class is the base class for database objects
 */
abstract class DatabaseObjectList implements \Countable {
    /**
     * class name
     * @var string
     */
    public string $className = '';

    /**
     * object class name
     * @var string
     */
    public string $objectClassName = '';

    /**
     * result objects
     * @var DatabaseObject[]
     */
    public array $objects = [];

    /**
     * ids of result objects
     * @var int[]
     */
    public ?array $objectIDs = null;

    /**
     * sql offset
     * @var int
     */
    public int $sqlOffset = 0;

    /**
     * sql limit
     * @var int
     */
    public int $sqlLimit = 0;

    /**
     * sql order by statement
     * @var string
     */
    public string $sqlOrderBy = '';

    /**
     * sql select parameters
     * @var string
     */
    public string $sqlSelects = '';

    /**
     * sql select joins which are necessary for where statements
     * @var string
     */
    public string $sqlConditionJoins = '';

    /**
     * sql select joins
     * @var string
     */
    public string $sqlJoins = '';

    /**
     * enables the automatic usage of the qualified shorthand
     * @var bool
     */
    public bool $useQualifiedShorthand = true;

    /**
     * sql conditions
     * @var PreparedStatementConditionBuilder
     */
    protected PreparedStatementConditionBuilder $conditionBuilder;

    /**
     * current iterator index
     * @var int
     */
    protected int $index = 0;

    /**
     * list of index to object relation
     * @var int[]
     */
    protected array $indexToObject = [];

    /**
     * constructor initialize and create the database data object
     */
    public function __construct() {
        // set class name
        if (empty($this->className)) {
            $className = static::class;

            if (\mb_substr($className, -4) == 'List') {
                $this->className = \mb_substr($className, 0, -4);
            }
        }

        $this->conditionBuilder = new PreparedStatementConditionBuilder();
        //EventHandler::getInstance()->fireAction($this, 'init');
    }

    /**
     * counts the number
     *
     * @return  int
     */
    public function countObjects(): int {
        $sql = "SELECT  COUNT(*)
                FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder();
        $statement = FindMyRecipe::getDB()->prepareStatement($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * reads the object ids
     */
    public function readObjectIDs() {
        $this->objectIDs = [];
        $sql = "SELECT  " . $this->getDatabaseTableAlias() . "." . $this->getDatabaseTableIndexName() . " AS objectID
                FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder() . "
                " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = FindMyRecipe::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute($this->getConditionBuilder()->getParameters());
        $this->objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * reads the objects
     */
    public function readObjects()
    {
        if ($this->objectIDs !== null) {
            if (empty($this->objectIDs)) {
                return;
            }

            $objectIdPlaceholder = "?" . \str_repeat(',?', \count($this->objectIDs) - 1);

            $sql = "SELECT  " . (!empty($this->sqlSelects) ? $this->sqlSelects . ($this->useQualifiedShorthand ? ',' : '') : '') . "
                            " . ($this->useQualifiedShorthand ? $this->getDatabaseTableAlias() . '.*' : '') . "
                    FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                            " . $this->sqlJoins . "
                    WHERE   " . $this->getDatabaseTableAlias() . "." . $this->getDatabaseTableIndexName() . " IN ({$objectIdPlaceholder})
                            " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
            $statement = FindMyRecipe::getDB()->prepareStatement($sql);
            $statement->execute($this->objectIDs);
            $this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
        } else {
            $sql = "SELECT  " . (!empty($this->sqlSelects) ? $this->sqlSelects . ($this->useQualifiedShorthand ? ',' : '') : '') . "
                            " . ($this->useQualifiedShorthand ? $this->getDatabaseTableAlias() . '.*' : '') . "
                    FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                    " . $this->sqlJoins . "
                    " . $this->getConditionBuilder() . "
                    " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');

            $statement = FindMyRecipe::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
            $statement->execute($this->getConditionBuilder()->getParameters());
            $this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
        }


        // decorate objects
        if (!empty($this->decoratorClassName)) {
            foreach ($this->objects as &$object) {
                $object = new $this->decoratorClassName($object);
            }
            unset($object);
        }

        // use table index as array index
        $objects = $this->indexToObject = [];
        foreach ($this->objects as $object) {
            $objectID = $object->getObjectID();
            $objects[$objectID] = $object;

            $this->indexToObject[] = $objectID;
        }
        $this->objectIDs = $this->indexToObject;
        $this->objects = $objects;
    }

    /**
     * Returns the object ids of the list.
     *
     * @return  int[]
     */
    public function getObjectIDs()
    {
        return $this->objectIDs;
    }

    /**
     * Sets the object ids.
     *
     * @param int[] $objectIDs
     */
    public function setObjectIDs(array $objectIDs)
    {
        $this->objectIDs = \array_merge($objectIDs);
    }

    /**
     * Returns the objects of the list.
     *
     * @return  DatabaseObject[]
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Returns the condition builder object.
     *
     * @return  PreparedStatementConditionBuilder
     */
    public function getConditionBuilder()
    {
        return $this->conditionBuilder;
    }

    /**
     * Sets the condition builder dynamically.
     *
     * @param PreparedStatementConditionBuilder $conditionBuilder
     * @since   5.3
     */
    public function setConditionBuilder(PreparedStatementConditionBuilder $conditionBuilder)
    {
        $this->conditionBuilder = $conditionBuilder;
    }

    /**
     * Returns the name of the database table.
     *
     * @return  string
     */
    public function getDatabaseTableName(): string {
        return \call_user_func([$this->className, 'getDatabaseTableName']);
    }

    /**
     * Returns the name of the database table.
     *
     * @return  string
     */
    public function getDatabaseTableIndexName(): string {
        return \call_user_func([$this->className, 'getDatabaseTableIndexName']);
    }

    /**
     * Returns the name of the database table alias.
     *
     * @return  string
     */
    public function getDatabaseTableAlias(): string {
        return \call_user_func([$this->className, 'getDatabaseTableAlias']);
    }

    /**
     * @inheritDoc
     */
    public function count(): int {
        return count($this->objects);
    }

    #[\ReturnTypeWillChange]
    public function current(): DatabaseObject {
        $objectID = $this->indexToObject[$this->index];

        return $this->objects[$objectID];
    }

    /**
     * CAUTION: This methods does not return the current iterator index,
     * but the object key which maps to that index.
     *
     * @see \Iterator::key()
     */
    #[\ReturnTypeWillChange]
    public function key(): int {
        return $this->indexToObject[$this->index];
    }

    public function next(): void {
        $this->index++;
    }

    public function rewind(): void {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return isset($this->indexToObject[$this->index]);
    }

    public function seek($offset): void {
        $this->index = $offset;

        if (!$this->valid()) {
            throw new \OutOfBoundsException();
        }
    }

    /**
     * @inheritDoc
     */
    public function seekTo($objectID) {
        $this->index = \array_search($objectID, $this->indexToObject);

        if ($this->index === false) {
            throw new SystemException("object id '" . $objectID . "' is invalid");
        }
    }

    /**
     * @inheritDoc
     */
    public function search($objectID) {
        try {
            $this->seekTo($objectID);

            return $this->current();
        } catch (SystemException $e) {
            return null;
        }
    }

    /**
     * Returns the only object in this list or `null` if the list is empty.
     *
     * @return  DatabaseObject|null
     * @throws  BadMethodCallException     if list contains more than one object
     */
    public function getSingleObject(): ?DatabaseObject {
        if (count($this->objects) > 1) {
            throw new BadMethodCallException("Cannot get a single object when the list contains " . count($this->objects) . " objects.");
        }

        if (empty($this->objects)) {
            return null;
        }

        return \reset($this->objects);
    }
}