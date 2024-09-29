<?php
namespace fmr\system\database;

use fmr\system\data\DatabaseObject;
use fmr\system\exception\database\DatabaseException;
use fmr\system\exception\database\DatabaseQueryException;
use fmr\system\exception\database\PreparedStatementException;
use fmr\system\exception\SystemException;
use fmr\FindMyRecipe;
use PDOStatement;

/**
 * @method fetch(int|null $type)
 * @method fetchAll()
 */
class PreparedStatement {

    /**
     * Database Object Class
     * @var string|Database
     */
    protected Database|string $db;

    /**
     * parameters for prepared sql query
     * @var array
     */
    protected array $parameters = [];

    /**
     * standard PDO Statement object class
     * @var PDOStatement
     */
    protected PDOStatement $pdoStatement;

    /**
     * current prepared sql query
     * @var string|mixed
     */
    protected string $query;

    /**
     * constructor for prepared statement class
     *
     * @param PDOStatement $pdoStatement    PDO Statement for prepared statement
     * @param string $query                 current sql query string for prepared statement
     */
    public function __construct(PDOStatement $pdoStatement, string $query = '') {
        $this->db = FindMyRecipe::getDB();
        $this->pdoStatement = $pdoStatement;
        $this->query = $query;

    }

    public function __call($name, $arguments)
    {
        if (!\method_exists($this->pdoStatement, $name)) {
            throw new PreparedStatementException("unknown method '" . $name . "'");
        }

        try {
            return \call_user_func_array([$this->pdoStatement, $name], $arguments);
        } catch (\PDOException $e) {
            throw new PreparedStatementException("Could call '" . $name . "' on '" . $this->query . "'");
        }
    }

    /**
     * @param array $parameters
     * @param null $logFile
     *
     * @return bool
     * @throws DatabaseException
     */
    public function execute(array $parameters = [], $logFile = null): bool {
            $this->parameters = $parameters;
            $this->incrementQueryCount();

        try {
            $result = $this->pdoStatement->execute($this->parameters);

            if($result)
                return true;
        } catch (\PDOException $e) {
            throw new DatabaseQueryException("Could not execute statement in Database", $e->getMessage());
        }
    }

    /**
     * Fetches the next row from a result set in an array.
     *
     * @param int|null $type fetch type
     * @return  mixed
     */
    public function fetchArray(int $type = null): mixed
    {
        // get fetch style
        if ($type === null) {
            $type = \PDO::FETCH_ASSOC;
        }

        return $this->fetch($type);
    }

    /**
     * count row from result
     * @return int
     */
    public function count(): int {
        return $this->pdoStatement->columnCount();
    }

    /**
     * fetches the next row from a result set in an array
     *
     * @param int|null $type fetch type
     * @return  mixed
     * @see     PreparedStatement::fetchArray()
     */
    public function fetchSingleRow(int $type = null): mixed {
        return $this->fetchArray($type);
    }

    /**
     * Fetches the next row from a result set in a database object.
     *
     * @param string $className
     * @return DatabaseObject|null
     */
    public function fetchObject(string $className): ?DatabaseObject {
        try {
            $row = $this->fetchArray();
            if ($row !== false) {
                return new $className(null, $row);
            }
        } catch (\PDOException | SystemException $e) {
            cfwDebug($e);
        }


        return null;
    }

    /**
     * Fetches the next row from a result set in a database object
     *
     * @param string $className
     * @return  DatabaseObject|null
     */
    public function fetchSingleObject(string $className): ?DatabaseObject {
        $row = $this->fetchSingleRow();
        if ($row !== false) {
            return new $className(null, $row);
        }

        return null;
    }

    public function fetchColumn() {
        $row = $this->pdoStatement->fetchColumn();
        if(!$row)
            return $row;
        return null;
    }

    /**
     * fetches all rows
     *
     * @param string $className
     * @param string|null $keyProperty
     * @return  DatabaseObject[]
     */
    public function fetchObjects(string $className, string $keyProperty = null): array {
        $objects = [];
        while ($object = $this->fetchObject($className)) {
            if ($keyProperty === null) {
                $objects[] = $object;
            } else {
                $objects[$object->{$keyProperty}] = $object;
            }
        }
        return $objects;
    }

    /**
     * counts number of affected rows by the last sql statement
     *
     * @return  int     number of affected rows
     * @throws  DatabaseException
     */
    public function getAffectedRows(): int {
        try {
            return $this->pdoStatement->rowCount();
        } catch (\PDOException $e) {
            throw new SystemException("Could fetch affected rows for '" . $this->query);
        }
    }

    /**
     * Returns the number of the last error.
     *
     * @return  int
     */
    public function getErrorNumber(): int {
        return $this->pdoStatement->errorCode();
    }

    /**
     * Returns the description of the last error.
     *
     * @return  string
     */
    public function getErrorDesc(): string {
        $errorInfoArray = $this->pdoStatement->errorInfo();
        if (isset($errorInfoArray[2])) {
            return $errorInfoArray[2];
        }

        return '';
    }

    /**
     * Returns the SQL query of this statement.
     *
     * @return  string
     */
    public function getSQLQuery(): string {
        return $this->query;
    }

    /**
     * Returns the SQL query parameters of this statement.
     *
     * @return  array
     */
    public function getSQLParameters(): array {
        return $this->parameters;
    }

    /**
     * Returns the amount of executed sql queries.
     *
     * @return  int
     */
    public function getQueryCount(): int {
        return $this->db->getQueryCount();
    }

    /**
     * Increments the query counter by one.
     */
    public function incrementQueryCount(): void {
        $this->db->incrementQueryCount();
    }


}