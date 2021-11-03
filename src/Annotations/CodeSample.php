<?php

namespace Vyuldashev\LaravelOpenApi\Annotations;

/**
 * @Annotation
 *
 * @Target({"METHOD"})
 */
class CodeSample
{
    /** @var boolean */
    public $bearer;

    /** @var string|array<string> */
    public $codes;
}
