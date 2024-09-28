<?php
namespace fmr;

use fmr\system\exception\SystemException;

abstract class Singleton {

    protected static array $singletonObjects;

    final protected function __construct() {
        $this->init();
    }

    /**
     * Object clone is disallowed
     *
     * @throws SystemException
     */
    final protected function __clone() : void {
        throw new SystemException('Clone of Singletons is not allowed');
    }

    /**
     * Object serializing is disallowed.
     */
    final public function __sleep() {
        throw new SystemException('Serializing of Singletons is not allowed');
    }

    protected function init() {}

    final public static function getInstance() {

        $className = static::class;
        if(!isset(self::$singletonObjects[$className])) {
            self::$singletonObjects[$className] = false;
            self::$singletonObjects[$className] = new $className();
        } else if(self::$singletonObjects[$className] === false) {
            throw new SystemException("Infinite loop detected while trying to retrieve object for '".$className."'");
        }
        return static::$singletonObjects[$className];
    }

    /**
     * Returns whether this singleton is already initialized.
     *
     * @return  bool
     */
    final public static function isInitialized(): bool {
        $className = static::class;

        return isset(self::$singletonObjects[$className]);
    }
}