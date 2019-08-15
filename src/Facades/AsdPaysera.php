<?php
namespace Asd\Paysera\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;
use Asd\Paysera\PayseraFaker;

class AsdPaysera extends BaseFacade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'asd.paysera'; }

    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap(new PayseraFaker(app('asd.paysera')));
    }

}