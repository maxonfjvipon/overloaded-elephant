<?php

namespace Maxonfjvipon\OverloadedElephant\Tests;

use Exception;
use Maxonfjvipon\OverloadedElephant\Fake;
use Maxonfjvipon\OverloadedElephant\Fakeable;
use Maxonfjvipon\OverloadedElephant\Overloadable;
use phpDocumentor\Reflection\Types\False_;
use PHPUnit\Framework\TestCase;

class TestOverloadable extends TestCase
{
    use Overloadable;

    /**
     * @throws Exception
     */
    public function testOverloading()
    {
        $args = self::overload(["string", 2, new Fake, true], []);
    }

    /**
     * @param string $search
     * @param $replace
     * @param $subject
     * @return string|string[]
     * @throws Exception
     */
    public function replaced(string $search, $replace, $subject)
    {
        return str_replace($search, ...self::overload([$replace, $subject], [
            [
                'string',
                Text::class => fn(Text $text) => $text->asString(),
                'float' => "float",
                'boolean' => fn(bool $b) => $b ? "true" : "false"
            ]
        ]));
    }

    public static function new($subject, $toReplace, $replaceTo)
    {
        return new self(fn() => str_replace(...self::overload([$subject, $toReplace, $replaceTo], [
            ['string', Text::class => fn(Text $txt) => $txt->asString()]
        ])));
    }
}