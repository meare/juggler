<?php

namespace Meare\Juggler\Imposter\Stub;


interface ICreatableFromContract
{
    /**
     * @param array|string $contract
     * @return mixed
     */
    public static function createFromContract($contract);
}