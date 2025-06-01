<?php

namespace Vyuldashev\LaravelOpenApi;

use Faker\Factory;
use Faker\Generator;

class SchemaResolver
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function resolve(array $schema): mixed
    {
        if (is_null($schema)) {
            return null;
        }

        if (isset($schema['example'])) {
            return $schema['example'];
        }

        if (isset($schema['enum']) && is_array($schema['enum']) && !empty($schema['enum'])) {
            return $this->faker->randomElement($schema['enum']);
        }

        $type = $schema['type'] ?? null;

        switch ($type) {
            case 'object':
                return $this->resolveObject($schema);
            case 'array':
                return $this->resolveArray($schema);
            case 'string':
                return $this->resolveString($schema);
            case 'integer':
            case 'number':
                return $this->resolveNumber($schema);
            case 'boolean':
                return $this->faker->boolean;
            default:
                return null;
        }
    }

    protected function resolveObject(array $schema): array
    {
        $result = [];
        $properties = $schema['properties'] ?? [];

        foreach ($properties as $propertyName => $propertySchema) {
            $result[$propertyName] = $this->resolve($propertySchema);
        }

        return $result;
    }

    protected function resolveArray(array $schema): array
    {
        $result = [];
        $itemsSchema = $schema['items'] ?? [];
        $minItems = $schema['minItems'] ?? 1;
        $maxItems = $schema['maxItems'] ?? 1;

        $count = $this->faker->numberBetween($minItems, $maxItems);

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->resolve($itemsSchema);
        }

        return $result;
    }

    protected function resolveString(array $schema): string
    {
        $format = $schema['format'] ?? null;
        $pattern = $schema['pattern'] ?? null;
        $minLength = $schema['minLength'] ?? 0;
        $maxLength = $schema['maxLength'] ?? 255;

        if ($pattern) {
            if ($pattern === '^[a-zA-Z0-9]+$') {
                return $this->faker->regexify('[a-zA-Z0-9]{' . $minLength . ',' . $maxLength . '}');
            }
            if ($pattern === '^[a-z0-9]+$') {
                return $this->faker->regexify('[a-z0-9]{' . $minLength . ',' . $maxLength . '}');
            }
            return $this->faker->regexify($pattern);
        }

        switch ($format) {
            case 'email':
                return $this->faker->email;
            case 'date':
                return $this->faker->date();
            case 'date-time':
                return $this->faker->dateTime()->format('c');
            case 'uuid':
                return $this->faker->uuid;
            case 'ipv4':
                return $this->faker->ipv4;
            case 'ipv6':
                return $this->faker->ipv6;
            case 'url':
                return $this->faker->url;
            default:
                return $this->faker->text($maxLength);
        }
    }

    protected function resolveNumber(array $schema)
    {
        $type = $schema['type'];
        $minimum = $schema['minimum'] ?? ($type === 'integer' ? 1 : 0.1);
        $maximum = $schema['maximum'] ?? ($type === 'integer' ? 100 : 1000.0);

        if ($type === 'integer') {
            return $this->faker->numberBetween((int)$minimum, (int)$maximum);
        }

        return $this->faker->randomFloat(2, $minimum, $maximum);
    }
}