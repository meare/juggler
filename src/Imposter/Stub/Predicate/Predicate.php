<?php

namespace Meare\Juggler\Imposter\Stub\Predicate;

use function Meare\Juggler\json_object;

class Predicate implements IPredicate
{
    /**
     * List of predicates that are defined as array, e.g.:
     * "or": [
     *     { "startsWith": { "data": "start" } },
     *     ...
     * ]
     *
     * Other predicates are defined as object:
     * "not": {
     *     "equals": {
     *          ...
     *     }
     *  }
     */
    const ARRAY_PREDICATES = [self:: AND, self:: OR];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var string
     */
    private $operator;

    /**
     * @param string $operator
     * @param array  $fields
     */
    public function __construct(string $operator, array $fields)
    {
        $this->setOperator($operator);
        $this->setFields($fields);
    }

    /**
     * @param string $operator
     */
    private function setOperator(string $operator)
    {
        if (!in_array($operator, self::ALLOWED_OPERATORS)) {
            throw new \InvalidArgumentException(
                "Cannot set predicate operator: operator '$operator' is not allowed. List of allowed operators: " . implode(',', self::ALLOWED_OPERATORS)
            );
        }
        $this->operator = $operator;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param array|string $contract
     * @return Predicate
     */
    public static function createFromContract($contract) : self
    {
        return new self(key($contract), reset($contract));
    }

    /**
     * @return string
     */
    public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            $this->operator => $this->jsonSerializeFields(),
        ];
    }

    /**
     * @return array|\StdClass
     */
    private function jsonSerializeFields()
    {
        $this->normalizeFields();
        if (in_array($this->operator, self::ARRAY_PREDICATES)) {
            return $this->fields;
        } else {
            return json_object($this->fields);
        }
    }

    /**
     * There is no arrays in predicate fields - all empty fields must serialize as {}
     *
     * @return array
     */
    private function normalizeFields()
    {
        foreach ($this->fields as $key => $field) {
            if (is_array($field)) {
                $this->fields[$key] = json_object($field);
            }
        }
    }
}