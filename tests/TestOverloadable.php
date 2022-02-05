<?php

namespace Maxonfjvipon\OverloadedElephant\Tests;

use Exception;
use Maxonfjvipon\OverloadedElephant\Fake;
use Maxonfjvipon\OverloadedElephant\Fakeable;
use Maxonfjvipon\OverloadedElephant\Overloadable;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use PHPUnit\Framework\TestCase;

class TestOverloadable extends TestCase
{
    use Overloadable;

    /**
     * @throws Exception
     */
    public function testOverloading()
    {
        self::assertEquals([1, 2], $this->overload([1, 2], 'integer'));
        self::assertEquals([1, 2.0], $this->overload([1, 2.0], fn() => ['integer', 'float']));
        self::assertEquals([1, 'hello'], $this->overload([1, 2], [
            'integer',
            ['int' => 'hello']
        ]));
        self::assertEquals(
            [false, 2, 4.6, 'hello world', [1, 2, 3, 4], "Hello world"],
            $this->overload([true, 1, 2.3, 'hello', [1, 2], new Fake], [[
                'bool' => fn(bool $bl) => !$bl,
                'int' => fn(int $i) => $i + 1,
                'float' => fn(float $fl) => $fl * 2,
                'string' => fn(string $str) => $str . ' world',
                'array' => fn(array $arr) => array_merge($arr, [3, 4]),
                Fakeable::class => fn(Fakeable $fk) => $fk->fake()
            ]])
        );
    }
}