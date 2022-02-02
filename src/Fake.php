<?php

namespace Maxonfjvipon\OverloadedElephant;

final class Fake implements Fakeable
{
    public function fake(): string
    {
        return "Hello world";
    }
}