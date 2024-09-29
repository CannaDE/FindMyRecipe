<?php

namespace fmr\system\data\interface;

use fmr\system\data\interface\EditObjectInterface;

/**
 * Abstract class for all cached data holder objects.
 */
interface EditCachedObjectInterface extends EditObjectInterface {
    /**
     * Resets the cache of this object type.
     */
    public static function resetCache();
}