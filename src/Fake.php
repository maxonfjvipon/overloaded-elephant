<?php

namespace Maxonfjvipon\OverloadedElephant;

final class Fake implements Fakeable
{
    public $val;

    public function __construct(bool $val = false)
    {
        $this->val = $val;
    }

    public function fake(): string
    {
        return "Hello world";
    }
}