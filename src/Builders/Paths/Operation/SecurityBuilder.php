<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Vyuldashev\LaravelOpenApi\Annotations\Security as SecurityAnnotation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class SecurityBuilder
{
    public function build(RouteInformation $route): ?array
    {
        return collect($route->actionAnnotations)
            ->filter(static function ($annotation) {
                return $annotation instanceof SecurityAnnotation;
            })
            ->map(static function (SecurityAnnotation $security) {
                if ($security->enabled) {
                    return SecurityRequirement::create()
                        ->securityScheme($security->scheme)
                    ;
                }

                return null;
            })
            ->values()
            ->toArray();
    }
}
