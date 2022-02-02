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
     * @param array $args
     * @param $rules
     * @return array
     * @throws Exception
     */
    private static function overload(array $args, $rules): array
    {
        $rules = is_callable($rules) ? $rules() : $rules; // fn() => ... -> ...
        $rules = is_array($rules) ? $rules : [$rules]; // not_array -> [...]
        $count = 0;
        $index = $count;
        $newArgs = array_values($args);
        if (count($rules) !== 0) { // []
            if (!array_key_exists(0, $rules)) {
                throw new Exception("Array of rules must contain item with index 0");
            }
            foreach ($args as $arg) {
                $type = gettype($arg); // boolean, integer, double, string, array, object, resource, NULL, unknown type
                if (array_key_exists($count, $rules)) { // [count => ...], [...]
                    $index = $count;
                }
                var_dump($type);
                if ($type === 'object') {
                    $found = false;
                    if (is_array($rules[$index])) {
                        foreach (array_keys($rules[$index]) as $key) {
                            if (is_string($key)) {
                                if (is_subclass_of($arg, $key)) {
                                    if (is_callable($rules[$index][$key])) {
                                        $newArgs[$count] = $rules[$index][$key]($arg);
                                    } else {
                                        $newArgs[$count] = $rules[$index][$key];
                                    }
                                    $found = true;
                                    break;
                                }
                            }
                        }
                        if (!$found) {
                            foreach (array_values($rules[$index]) as $value) {
                                if (is_string($value)) {
                                    if (is_subclass_of($arg, $value)) {
                                        $newArgs[$count] = $arg;
                                        $found = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!$found) {
                            throw new Exception("Type mismatch of object with index " . $count);
                        }
                    } elseif (is_subclass_of($arg, $rules[$index])) {
                        $newArgs[$count] = $arg;
                    } else {
                        throw new Exception("Type mismatch of object with index " . $count);
                    }
                } else {
                    if (is_array($rules[$index])) { // [index => [...]]
                        if (in_array($type, $rules[$index])) { // [index => [..., type]]
                            $newArgs[$count] = $arg;
                        } elseif (array_key_exists($type, $rules[$index])) { // [index => [type => ...]]
                            if (is_callable($rules[$index][$type])) { // [index => [type => fn() => ...]]
                                $newArgs[$count] = $rules[$index][$type]($arg);
                            } else { // [index => [type => value]]
                                $newArgs[$count] = $rules[$index][$type]; // value
                            }
                        } else {
                            throw new Exception("Type mismatch on argument with index " . $count);
                        }
                    } elseif ($rules[$index] === $type) { // [index => not_array_value]
                        $newArgs[$count] = $arg;
                    } else {
                        throw new Exception("Type mismatch on argument with index " . $count);
                    }
                }
                $count++;
            }
        } else {
            throw new Exception("Array of rules is empty");
        }
        uksort($newArgs, fn($a, $b) => $a <=> $b);
        return $newArgs;
    }
}