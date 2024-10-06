<?php

namespace fmr\system\data;

use fmr\system\data\interface\EditObjectInterface;
use fmr\system\data\interface\StorableObjectInterface;
use fmr\system\data\trait\FastCreateTrate;
use fmr\system\database\PreparedStatementConditionBuilder;
use fmr\system\exception\ErrorException;
use fmr\FindMyRecipe;

/**
 * Basic implementation for object editors following the decorator pattern.
 */
abstract class DatabaseObjectEditor extends DatabaseObjectDecorator implements EditObjectInterface
{
    use FastCreateTrate {
        FastCreateTrate::fastCreate as private dboEditorCreateBase;
    }

    /**
     * @inheritDoc
     */
    public static function create(array $parameters = []) {
        return new static::$baseClass(static::dboEditorCreateBase($parameters));
    }

    /**
     * @inheritDoc
     */
    public function update(array $parameters = [])
    {
        if (empty($parameters)) {
            return;
        }

        $updateSQL = '';
        $statementParameters = [];
        foreach ($parameters as $key => $value) {
            if (!empty($updateSQL)) {
                $updateSQL .= ', ';
            }
            $updateSQL .= $key . ' = ?';
            $statementParameters[] = $value;
        }
        $statementParameters[] = $this->getObjectID();

        $sql = "UPDATE  " . static::getDatabaseTableName() . "
                SET     " . $updateSQL . "
                WHERE   " . static::getDatabaseTableIndexName() . " = ?";
        $statement = FindMyRecipe::getDB()->prepareStatement($sql);
        $statement->execute($statementParameters);
    }

    /**
     * @inheritDoc
     */
    public function updateCounters(array $counters = [])
    {
        if (empty($counters)) {
            return;
        }

        $updateSQL = '';
        $statementParameters = [];
        foreach ($counters as $key => $value) {
            if (!empty($updateSQL)) {
                $updateSQL .= ', ';
            }
            $updateSQL .= $key . ' = ' . $key . ' + ?';
            $statementParameters[] = $value;
        }
        $statementParameters[] = $this->getObjectID();

        $sql = "UPDATE  " . static::getDatabaseTableName() . "
                SET     " . $updateSQL . "
                WHERE   " . static::getDatabaseTableIndexName() . " = ?";
        $statement = FindMyRecipe::getDB()->prepareStatement($sql);
        $statement->execute($statementParameters);
    }

    /**
     * @inheritDoc
     */
    public function delete() {
        static::deleteAll([$this->getObjectID()]);
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = []): int {
        $affectedCount = 0;

        $itemsPerLoop = 1000;
        $loopCount = ceil(count($objectIDs) / $itemsPerLoop);

        TimeMonitoring::getDB()->beginTransaction();
        for ($i = 0; $i < $loopCount; $i++) {
            $batchObjectIDs = array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add(static::getDatabaseTableIndexName() . ' IN (?)', [$batchObjectIDs]);

            $sql = "DELETE FROM " . static::getDatabaseTableName() . "
                    " . $conditionBuilder;
            $statement = FindMyRecipe::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            $affectedCount += $statement->getAffectedRows();
        }
        FindMyRecipe::getDB()->commitTransaction();

        return $affectedCount;
    }

    /**
     * Creates a new object, returns null if the row already exists.
     *
     * @param array $parameters
     * @return  StorableObjectInterface|null
     * @since       5.3
     */
    public static function createOrIgnore(array $parameters = []) {
        try {
            return static::create($parameters);
        } catch (ErrorException $e) {
            // Error code 23000 = duplicate key
            if ($e->getCode() == '23000' && $e->getDriverCode() == '1062') {
                return null;
            }

            throw $e;
        }
    }
}