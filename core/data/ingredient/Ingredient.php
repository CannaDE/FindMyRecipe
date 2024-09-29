<?php
namespace fmr\data\ingredient;

use fmr\system\data\DatabaseObject;
use fmr\FindMyRecipe;

class Ingredient extends DatabaseObject {

    /**
     * @inheritdoc
     */
    protected static string $databaseTableName = 'basic_ingredients';

    /**
     * @inheritdoc
     */
    protected static string $databaseTableIndexName = 'id';

    public function __construct($id, ?array $row = null, ?self $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT  c.name AS category_name,
                            i.name AS ingredient_name,
                            i.id AS ingredient_id
                    FROM    " . static::getDatabaseTableName() . " i
                    JOIN    " . static::getDatabaseTableName() . "_link ic ON i.id = ic.ingredient_id
                    JOIN    " . static::getDatabaseTableName() . "_category c ON ic.category_id = c.id
                    WHERE   i." . static::getDatabaseTableIndexName() . " = ?
                    ORDER BY c.name, i.name";
                    
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
}