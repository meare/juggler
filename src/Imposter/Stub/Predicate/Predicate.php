<?php

namespace Meare\Juggler\Imposter\Stub\Predicate;

class Predicate implements IPredicate
{
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
    public function __construct($operator, array $fields)
    {
        $this->setOperator($operator);
        $this->setFields($fields);
    }

    /**
     * Returns list of operators that must be defined as array, e.g. 'or' operator:
     * "or": [
     *     { "startsWith": { "data": "start" } },
     *     ...
     * ]
     *
     * Other predicates must be defined as object:
     * "not": {
     *     "equals": {
     *          ...
     *     }
     *  }
     */
    public static function getArrayOperators() {
        return [self::OPERATOR_AND, self::OPERATOR_OR];
    }

    /**
     * @return array
     */
    public static function getAllowedOperators()
    {
        return [
            self::OPERATOR_EQUALS, self::OPERATOR_DEEP_EQUALS, self::OPERATOR_CONTAINS, self::OPERATOR_STARTS_WITH,
            self::OPERATOR_ENDS_WITH, self::OPERATOR_MATCHES, self::OPERATOR_EXISTS, self::OPERATOR_NOT,
            self::OPERATOR_OR, self::OPERATOR_AND, self::OPERATOR_INJECT,
        ];
    }

    /**
     * @param string $operator
     */
    private function setOperator($operator)
    {
        if (!in_array($operator, self::getAllowedOperators())) {
            throw new \InvalidArgumentException("Cannot create predicate object; Invalid operator: '$operator'");
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
    public static function createFromContract($contract)
    {
        return new self(key($contract), reset($contract));
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
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
        if (in_array($this->operator, self::getArrayOperators())) {
            return $this->fields;
        } else {
            return \Meare\Juggler\json_object($this->fields);
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
                $this->fields[$key] = \Meare\Juggler\json_object($field);
            }
        }
    }
}