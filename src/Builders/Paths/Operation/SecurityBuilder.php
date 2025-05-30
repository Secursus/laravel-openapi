<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use Vyuldashev\LaravelOpenApi\Attributes\Security as SecurityAttribute;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class SecurityBuilder
{
    public function build(RouteInformation $route): array
    {
        return $route->actionAttributes
            ->filter(static fn (object $attribute) => $attribute instanceof SecurityAttribute)
            ->map(static function (SecurityAttribute $attribute) {
                if ($attribute->enabled) {
                    $requirement = SecurityRequirement::create();

                    if (is_array($attribute->scheme)) {
                        foreach ($attribute->scheme as $scheme) {
                            $requirement = $requirement->securityScheme($scheme);
                        }
                        return $requirement;
                    }

                    return $requirement->securityScheme($attribute->scheme);
                }

                return null;
            })
            ->values()
            ->toArray();
    }
}
