<?php

namespace Meare\Juggler\Imposter\Stub\Predicate;


use Meare\Juggler\Imposter\Stub\ICreatableFromContract;

interface IPredicate extends \JsonSerializable, ICreatableFromContract
{
    const OPERATOR_EQUALS = 'equals';
    const OPERATOR_DEEP_EQUALS = 'deepEquals';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_STARTS_WITH = 'startsWitch';
    const OPERATOR_ENDS_WITH = 'endsWith';
    const OPERATOR_MATCHES = 'matches';
    const OPERATOR_EXISTS = 'exists';
    const OPERATOR_NOT = 'not';
    const OPERATOR_OR = 'or';
    const OPERATOR_AND = 'and';
    const OPERATOR_INJECT = 'inject';
}