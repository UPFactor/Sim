<?php

namespace Sim\Adapters\Laravel;

use Illuminate\Support\Facades\Facade;

class SimFacade extends Facade{
    protected static function getFacadeAccessor(){
        return \Sim\Environment::class;
    }
}