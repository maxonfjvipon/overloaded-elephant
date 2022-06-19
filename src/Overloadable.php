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
                if ($type === 'object') { // class name, callback
                    $found = false;
                    $this->checkRuleIsArray($rules[$index]);
                    foreach (array_keys($rules[$index]) as $ruleKey) {
                        if (is_string($ruleKey)) {
                            if (is_subclass_of($arg, $ruleKey) || get_class($arg) === $ruleKey) {
                                $this->checkRuleActionIsCallback($rules[$index][$ruleKey]);
                                $newArgs[$argKey] = $rules[$index][$ruleKey]($arg);
                                $found = true;
                                break;
                            }
                        }
                    }
                    if (!$found) {
                        foreach ($rules[$index] as $ruleValue) {
                            if (is_string($ruleValue)) {
                                if (is_subclass_of($arg, $ruleValue) || get_class($arg) === $ruleValue) {
                                    $newArgs[$argKey] = $arg;
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (!$found) {
                        $this->typeMismatch($type, $argKey);
                    }
                } else { // not object or callback
                    $this->checkRuleIsArray($rules[$index]);
                    if (in_array($type, $rules[$index]) || in_array($this->aliases[$type], $rules[$index])) {
                        $newArgs[$argKey] = $arg;
                    } elseif (array_key_exists($type, $rules[$index])) {
                        $this->checkRuleActionIsCallback($rules[$index][$type]);
                        $newArgs[$argKey] = $rules[$index][$type]($arg);
                    } elseif (array_key_exists($this->aliases[$type], $rules[$index])) {
                        $this->checkRuleActionIsCallback($rules[$index][$this->aliases[$type]]);
                        $newArgs[$argKey] = $rules[$index][$this->aliases[$type]]($arg);
                    } else {
                        $this->typeMismatch($type, $argKey);
                    }
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
        throw new Exception("Type mismatch of object with type: " . $argType ." with key: " . $argKey);
    }
}