<?php
namespace fmr\system\exception\database;

use fmr\system\exception\SystemException;
use fmr\util\StringUtil;
use fmr\FindMyRecipe;

class DatabaseException extends SystemException {


    public function __construct(string $message = "",string $description = "", array $extraInformation = [], ?\Exception $previous = null) {
        parent::__construct($message, 0);
        $this->code = 16;

        if(!empty($this->description)) {
            $regex = StringUtil::parsePdoErrorMessage($this->description);

            
            if(!empty($regex) && isset($regex[3]))
                FindMyRecipe::getTemplateEngine()->assignVar(['description' => $regex[3]]);
            else if(isset($regex[2]))
                FindMyRecipe::getTemplateEngine()->assignVar(['description' => substr($regex[2], 2)]);
        }

    }
}