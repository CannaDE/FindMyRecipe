<?php
namespace fmr\system\database;

use fmr\system\benchmark\Benchmark;
use fmr\system\exception\Database\DatabaseException;
use fmr\system\exception\ErrorException;
use fmr\FindMyRecipe;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

class Database {

    /**
     * @var PDO
     */
    private static PDO $pdo;

    /**
     * hostname for database
     * @var string
     */
    protected string $hostname = MYSQL_HOSTNAME;

    /**
     * username for current mysql database
     * @var string
     */
    protected string $username = MYSQL_USERNAME;

    /**
     * password for current mysql database
     * @var string
     */
    #[SensitiveParameter]
    protected string $password = MYSQL_PASSWORD;

    /**
     * databasename for current mysql database
     * @var string
     */
    protected string $databaseName = MYSQL_DATABASENAME;

    /**
     * @var int
     */
    private int $queryCount = 0;


    private int $activeTransactions = 0;


    function __construct() {

        try {
            $dsn = "mysql:host=".$this->hostname.";dbname=".$this->databaseName.";charset=utf8mb4";
            self::$pdo = new PDO($dsn, $this->username, $this->password);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new DatabaseException("Connection to database can not be established", $e->getMessage(), ['SQL Error Code' => $e->getCode()]);
        }
    }

    /**
     * Initiates a transaction.
     *
     * @return  bool        true on success
     * @throws  DatabaseTransactionException
     */
    public function beginTransaction()
    {
        try {
            if ($this->activeTransactions === 0) {
                if (TimeMonitoring::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start("BEGIN", Benchmark::TYPE_SQL_QUERY);
                }
                $result =  self::$pdo->beginTransaction();
            } else {
                if (TimeMonitoring::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start(
                        "SAVEPOINT level" . $this->activeTransactions,
                        Benchmark::TYPE_SQL_QUERY
                    );
                }
                $result = self::$pdo->exec("SAVEPOINT level" . $this->activeTransactions) !== false;
            }
            if (TimeMonitoring::benchmarkIsEnabled()) {
                Benchmark::getInstance()->stop();
            }

            $this->activeTransactions++;

            return $result;
        } catch (\PDOException $e) {
            throw new DatabaseTransactionException("Could not begin transaction", $e);
        }
    }

    /**
     * Commits a transaction and returns true if the transaction was successful.
     *
     * @return  bool
     * @throws  DatabaseTransactionException
     */
    public function commitTransaction()
    {
        if ($this->activeTransactions === 0) {
            return false;
        }

        try {
            $this->activeTransactions--;

            if ($this->activeTransactions === 0) {
                if (TimeMonitoring::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start("COMMIT", Benchmark::TYPE_SQL_QUERY);
                }
                $result = self::$pdo->commit();
            } else {
                if (TimeMonitoring::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start(
                        "RELEASE SAVEPOINT level" . $this->activeTransactions,
                        Benchmark::TYPE_SQL_QUERY
                    );
                }
                $result = self::$pdo->exec("RELEASE SAVEPOINT level" . $this->activeTransactions) !== false;
            }

            if (TimeMonitoring::benchmarkIsEnabled()) {
                Benchmark::getInstance()->stop();
            }

            return $result;
        } catch (\PDOException $e) {
            throw new DatabaseTransactionException("Could not commit transaction", $e);
        }
    }

    /**
     * Rolls back a transaction and returns true if the rollback was successful.
     *
     * @return  bool
     * @throws  DatabaseTransactionException
     */
    public function rollBackTransaction()
    {
        if ($this->activeTransactions === 0) {
            return false;
        }

        try {
            $this->activeTransactions--;
            if ($this->activeTransactions === 0) {
                if (TimeMonitoring::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start("ROLLBACK", Benchmark::TYPE_SQL_QUERY);
                }
                $result = self::$pdo->rollBack();
            } else {
                if (TimeMonitoring::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start(
                        "ROLLBACK TO SAVEPOINT level" . $this->activeTransactions,
                        Benchmark::TYPE_SQL_QUERY
                    );
                }
                $result = $this->pdo->exec("ROLLBACK TO SAVEPOINT level" . $this->activeTransactions) !== false;
            }

            if (TimeMonitoring::benchmarkIsEnabled()) {
                Benchmark::getInstance()->stop();
            }

            return $result;
        } catch (\PDOException $e) {
            throw new DatabaseTransactionException("Could not roll back transaction", $e);
        }
    }

    public function handleLimitParameter($query, $limit = 0, $offset = 0) {

        $limit = intval($limit);
        $offset = intval($offset);
        if($limit < 0)
            throw new InvalidArgumentException("The limit must not be negative");
        if($offset < 0)
            throw new InvalidArgumentException("The offset must not be negative");

        if($limit != 0) {
            $query = preg_replace(
                '~(\s+FOR\s+UPDATE\s*)?$~',
                " LIMIT " . $limit . ($offset ? " OFFSET " . $offset : '') . "\\0",
                $query,
                1
            );
        }
        return $query;
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $statement
     * @param int $limit
     * @param int $offset
     * @throws  DatabaseException
     */
    public function prepareStatement(string $statement, int $limit = 0, int $offset = 0): PreparedStatement {
        $statement = $this->handleLimitParameter($statement, $limit, $offset);

        try {
            $pdoStatement = self::$pdo->prepare($statement);
            return new PreparedStatement($pdoStatement, $statement);
        } catch (PDOException $e) {
            throw new DatabaseException("Could not prepare statement '" . $statement . "'");
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $statement
     * @param int $limit
     * @param int $offset
     *
     * @return PreparedStatement
     * @throws DatabaseException
     */
    public function prepare(string $statement, int $limit = 0, int $offset = 0): PreparedStatement {
        return $this->prepareStatement($statement, $limit, $offset);
    }

    /**
     * return the last insert id.
     *
     * @param string $table
     * @param string $field
     *
     * @return  int
     * @throws DatabaseException
     */
    public function getLatestInsertID(string $table, string $field): int {
        try {
            return self::$pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new DatabaseException("Cannot fetch last insert id", ['SQL Error Code' => $e->getCode(), 'SQL Count' => $this->getQueryCount(), 'SQL Error' => $e->getMessage()], $e);
        }
    }

    /**
     * return pdo attribute
     * @param string $attribute
     * @return mixed
     */
    public function getAttribute(string $attribute): mixed {
        return self::$pdo->getAttribute(self::$pdo::ATTR_DRIVER_NAME);
    }

    /**
     * fetch all rows in data object
     *
     * @param string $className
     * @param PDOStatement $statement
     * @param string|null $keyProperty
     *
     * @return array
     */
    public function fetchObjects(string $className, PDOStatement $statement, string $keyProperty = null): array {
        $objects = [];
        while ($object = $statement->fetchObject($className)) {
            if ($keyProperty === null) {
                $objects[] = $object;
            } else {
                $objects[$object->{$keyProperty}] = $object;
            }
        }

        return $objects;
    }

    /**
     * Returns the sql version.
     *
     * @return  string
     */
    public function getVersion(): string {
        try {
            if (self::class !== null) {
                return self::$pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            }
        } catch (PDOException $e) {
            echo $e;
            exit;
        }

        return 'unknown';
    }

    /**
     * returns the database name.
     *
     * @return  string database name
     */
    public function getDatabaseName(): string {
        return $this->databaseName;
    }

    /**
     * returns the name of the database user.
     *
     * @return  string      username
     */
    public function getUser(): string {
        return $this->username;
    }

    /**
     * Returns the amount of executed sql queries.
     *
     * @return  int     query count
     */
    public function getQueryCount(): int {
        return $this->queryCount;
    }

    /**
     * add one to query count
     */
    public function incrementQueryCount(): void {
        $this->queryCount++;
    }

}