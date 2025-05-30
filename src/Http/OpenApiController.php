<?php

namespace Vyuldashev\LaravelOpenApi\Http;

use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use Illuminate\Support\Facades\Route;
use Vyuldashev\LaravelOpenApi\Generator;

class OpenApiController
{
    public function show(Generator $generator): OpenApi
    {
        $routeName = Route::currentRouteName();
        $collection = 'default';

        if ($routeName) {
            $parts = explode('.', $routeName);
            if (count($parts) >= 2) {
                $collection = $parts[1];
            }
        }

        return $generator->generate($collection);
    }
}
