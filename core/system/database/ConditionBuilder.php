<?php

namespace fmr\system\database;

/**
 * Builds a sql query 'where' condition.
 */
class ConditionBuilder {
    /**
     * must be true to add the 'WHERE' keyword automatically
     * @var bool
     */
    protected $addWhereKeyword = true;

    /**
     * string used for concatenating conditions
     * @var string
     */
    protected $concat = '';

    /**
     * conditions string
     * @var string
     */
    protected $conditions = '';

    /**
     * Creates a new ConditionBuilder object.
     *
     * @param bool $addWhereKeyword
     * @param string $concat
     */
    public function __construct($addWhereKeyword = true, $concat = 'AND')
    {
        $this->addWhereKeyword = $addWhereKeyword;
        $this->concat = ($concat == 'OR') ? ' OR ' : ' AND ';
    }

    /**
     * Adds a new condition.
     *
     * @param mixed $condition
     */
    public function add($condition)
    {
        $conditions = $condition;
        if (!\is_array($conditions)) {
            $conditions = [$condition];
        }

        foreach ($conditions as $condition) {
            if (!empty($this->conditions)) {
                $this->conditions .= $this->concat;
            }
            $this->conditions .= $condition;
        }
    }

    /**
     * Returns the build condition.
     *
     * @return  string
     */
    public function __toString()
    {
        return (($this->addWhereKeyword && $this->conditions) ? 'WHERE ' : '') . $this->conditions;
    }

    /**
     * Enables / disables the where keyword.
     *
     * @param bool $enable
     */
    public function enableWhereKeyword($enable = true)
    {
        $this->addWhereKeyword = $enable;
    }
}