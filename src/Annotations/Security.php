<?php

namespace Vyuldashev\LaravelOpenApi\Annotations;

/**
 * @Annotation
 *
 * @Target({"METHOD"})
 */
class Security
{
    /** @var boolean */
    public $enabled;
    
    /** @var string */
    public $scheme;
}
