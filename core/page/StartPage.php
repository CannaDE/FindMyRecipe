<?php
namespace fmr\page;

use fmr\data\ingredient\Ingredient;
use fmr\data\ingredient\IngredientList;
use fmr\FindMyRecipe;
class StartPage extends AbstractPage implements IAbstractPage {

    public string $templateName = "home";

    public string $pageTitle = "Bald verfügbar!";

    public array $data = [];

    public function readData() {
        parent::readData();
        $sql = "SELECT  c.name AS category_name,
                        i.name AS ingredient_name
                FROM    fmr_basic_ingredients i
                JOIN    fmr_basic_ingredients_link ic ON i.id = ic.ingredient_id
                JOIN    fmr_basic_ingredients_category c ON ic.category_id = c.id
                ORDER BY ic.category_id";
        $statement = FindMyRecipe::getDB()->prepareStatement($sql);
        $statement->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $this->data = $this->sortIngredients($rows);
    }

    public function assignVariables() {
        parent::assignVariables();
        FindMyRecipe::getTPL()->assign("ingredients", $this->data);
    }

    public function sortIngredients(array $rows): array
    {
        $sorted = [];
        
        foreach ($rows as $row) {
            $category = $row['category_name'];
            $ingredient = $row['ingredient_name'];
            
            if (!isset($sorted[$category])) {
                $sorted[$category] = [];
            }
            
            $sorted[$category][] = $ingredient;
        }
        
        return $sorted;
    }
}
