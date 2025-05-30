<?php

namespace Vyuldashev\LaravelOpenApi\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CodeSample
{
    /** @var string|array<string> */
    public array|string $codes;

    public function __construct(string|array $codes = 'default')
    {
        $this->codes = $codes;
    }
}
