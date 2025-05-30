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
            ->flatMap(static function (SecurityAttribute $attribute) {
                if ($attribute->enabled) {
                    if (is_array($attribute->scheme)) {
                        return collect($attribute->scheme)->map(function ($scheme) {
                            return SecurityRequirement::create()->securityScheme($scheme);
                        });
                    }

                    return [SecurityRequirement::create()->securityScheme($attribute->scheme)];
                }

                return null;
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
