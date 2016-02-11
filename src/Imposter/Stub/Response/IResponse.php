<?php

namespace Meare\Juggler\Imposter\Stub\Response;

use Meare\Juggler\Imposter\Stub\ICreatableFromContract;

interface IResponse extends \JsonSerializable, ICreatableFromContract
{
    const TYPE_IS = 'is';
    const TYPE_PROXY = 'proxy';
    const TYPE_INJECT = 'inject';
    const ALLOWED_TYPES = [self::TYPE_IS, self::TYPE_PROXY, self::TYPE_INJECT];

    /**
     * @return string
     */
    public function getType() : string;
}