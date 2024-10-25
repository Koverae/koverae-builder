<?php

namespace Koverae\KoveraeBuilder;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Koverae\KoveraeBuilder\Skeleton\SkeletonClass
 */
class KoveraeBuilderFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'koverae-builder';
    }
}
