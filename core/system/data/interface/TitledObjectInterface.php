<?php

namespace fmr\system\data\interface;

/**
 * Every titled object has to implement this interface.
 */
interface TitledObjectInterface {
    /**
     * Returns the title of the object.
     *
     * @return  string
     */
    public function getTitle(): string;
}