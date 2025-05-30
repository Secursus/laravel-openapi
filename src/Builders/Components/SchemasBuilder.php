<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;

use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\SchemaFactory;
use Vyuldashev\LaravelOpenApi\Generator;

class SchemasBuilder extends Builder
{
    public function build(string $collection = Generator::COLLECTION_DEFAULT, array $usedSchemaFactories = []): array
    {
        $classes = $this->getAllClasses($collection)
            ->filter(static function ($class) {
                return
                    is_a($class, SchemaFactory::class, true) &&
                    is_a($class, Reusable::class, true);
            });

        // Filter by used schema factories if provided
        if (!empty($usedSchemaFactories)) {
            $classes = $classes->filter(function ($class) use ($usedSchemaFactories) {
                return in_array($class, $usedSchemaFactories);
            });
        }

        return $classes
            ->map(static function ($class) {
                /** @var SchemaFactory $instance */
                $instance = app($class);

                return $instance->build();
            })
            ->values()
            ->toArray();
    }
}
