<?php

namespace Vyuldashev\LaravelOpenApi\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CodeSample
{
    /** @var bool */
    public bool $bearer;

    /** @var string|array<string> */
    public array|string $codes;

    public function __construct(bool $bearer = false, string|array $codes = 'default')
    {
        $this->bearer = $bearer;
        $this->codes = $codes;
    }
}