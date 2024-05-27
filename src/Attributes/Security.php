<?php

namespace Vyuldashev\LaravelOpenApi\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Security
{
    /** @var bool */
    public bool $enabled;

    /** @var string|array<string> */
    public array|string $scheme;

    public function __construct(bool $enabled = false, string|array $scheme = 'default')
    {
        $this->enabled = $enabled;
        $this->scheme = $scheme;
    }
}
