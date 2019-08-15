<?php

namespace Asd\Paysera\Events;

class PayseraPaymentCallback
{
    /**
     * @var
     */
    public $callbackData;

    public function __construct($callbackData)
    {
        $this->callbackData = $callbackData;
    }
}