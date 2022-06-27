<?php

namespace Maxonfjvipon\OverloadedElephant;

use Exception;

/**
 * Overloadable
 * @package Maxonfjvipon\OverloadedElephant
 */
trait Overloadable
{
    /**
     * @var array|string[] $PRIMITIVES
     */
    private array $PRIMITIVES = [
        'integer', 'int', 'double', 'float', 'boolean', 'bool', 'string',
        'array', 'NULL', 'null', 'unknown type', 'unknown'
    ];

    /**
     * @var array $aliases
     */
    private array $aliases = [
        'integer' => 'int',
        'double' => 'float',
        'boolean' => 'bool',
        'string' => 'string',
        'array' => 'array',
        'NULL' => 'null',
        'unknown type' => 'unknown'
    ];

    /**
     * @param array $args
     * @param array<array> $rules
     * @return array
     * @throws Exception
     */
    private function overload(array $args, array $rules): array
    {
        $counter = 0;
        $index = $counter;
        $newArgs = [];
        if (count($rules) !== 0) { // []
            foreach ($args as $argKey => $arg) {
                $type = gettype($arg); // boolean, integer, double, string, array, object, resource, NULL, unknown type
                if (array_key_exists($counter, $rules)) { // [counter => [...]], [...]
                    $index = $counter;
                }
                if ($type === 'object') {
                    $found = false;
                    $this->checkRuleIsArray($rules[$index]);
                    $filteredRules = array_filter(
                        $rules[$index],
                        fn($value, $key) => !in_array($value, $this->PRIMITIVES) && !in_array($key, $this->PRIMITIVES),
                        ARRAY_FILTER_USE_BOTH
                    );
                    foreach (array_keys($filteredRules) as $ruleKey) {
                        if (is_string($ruleValue = $filteredRules[$ruleKey])) {
                            if (is_subclass_of($arg, $ruleValue) || get_class($arg) === $ruleValue) {
                                $newArgs[$argKey] = $arg;
                                $found = true;
                                break;
                            }
                        } else {
                            if (is_string($ruleKey)) {
                                if (is_subclass_of($arg, $ruleKey) || get_class($arg) === $ruleKey) {
                                    $this->checkRuleActionIsCallback($rules[$index][$ruleKey]);
                                    $newArgs[$argKey] = $rules[$index][$ruleKey]($arg);
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (!$found) {
                        $newArgs[$argKey] = $arg;
                    }
                } else if (array_key_exists($type, $rules[$index])) { // if type is primitive and exists in array
                    $this->checkRuleActionIsCallback($rules[$index][$type]);
                    $newArgs[$argKey] = $rules[$index][$type]($arg);
                } else if (array_key_exists($this->aliases[$type], $rules[$index])) { // if type is primitive and exists as alias
                    $this->checkRuleActionIsCallback($rules[$index][$this->aliases[$type]]);
                    $newArgs[$argKey] = $rules[$index][$this->aliases[$type]]($arg);
                } else if (in_array($type, $rules[$index])) {
                    $newArgs[$argKey] = $arg;
                } else if (in_array($this->aliases[$type], $rules[$index])) {
                    $newArgs[$argKey] = $arg;
                } else { // any other type
                    $newArgs[$argKey] = $arg;
                }
                $counter++;
            }
        } else {
            return $args;
        }
        return $newArgs;
    }

    /**
     * @throws Exception
     */
    private function checkRuleIsArray($rule)
    {
        if (!is_array($rule))
            throw new Exception("Rule instance must be an array. See documentation");
    }

    /**
     * @throws Exception
     */
    private function checkRuleActionIsCallback($ruleAction)
    {
        if (!is_callable($ruleAction)) {
            throw new Exception("Action with argument must be provided only via callbacks");
        }
    }

    /**
     * @throws Exception
     */
    private function typeMismatch($argType, $argKey)
    {
        throw new Exception("Type mismatch of object with type: " . $argType . " with key: " . $argKey);
    }
}