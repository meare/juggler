<?php

namespace Meare\Juggler\Imposter\Stub\Predicate;


use Meare\Juggler\Imposter\Stub\ICreatableFromContract;

interface IPredicate extends \JsonSerializable, ICreatableFromContract
{
    const EQUALS = 'equals';
    const DEEP_EQUALS = 'deepEquals';
    const CONTAINS = 'contains';
    const STARTS_WITH = 'startsWitch';
    const ENDS_WITH = 'endsWith';
    const MATCHES = 'matches';
    const EXISTS = 'exists';
    const NOT = 'not';
    const OR = 'or';
    const AND = 'and';
    const INJECT = 'inject';
    const ALLOWED_OPERATORS = [
        self::EQUALS, self::DEEP_EQUALS, self::CONTAINS, self::STARTS_WITH, self::ENDS_WITH,
        self::MATCHES, self::EXISTS, self::NOT, self:: OR, self:: AND, self::INJECT,
    ];
}