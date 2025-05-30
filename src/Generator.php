<?php

namespace Vyuldashev\LaravelOpenApi;

use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use Illuminate\Support\Arr;
use Vyuldashev\LaravelOpenApi\Builders\ComponentsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\InfoBuilder;
use Vyuldashev\LaravelOpenApi\Builders\PathsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ServersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\TagsBuilder;

class Generator
{
    public string $version = OpenApi::OPENAPI_3_0_2;

    public const COLLECTION_DEFAULT = 'default';

    protected array $config;
    protected InfoBuilder $infoBuilder;
    protected ServersBuilder $serversBuilder;
    protected TagsBuilder $tagsBuilder;
    protected PathsBuilder $pathsBuilder;
    protected ComponentsBuilder $componentsBuilder;

    public function __construct(
        array $config,
        InfoBuilder $infoBuilder,
        ServersBuilder $serversBuilder,
        TagsBuilder $tagsBuilder,
        PathsBuilder $pathsBuilder,
        ComponentsBuilder $componentsBuilder
    ) {
        $this->config = $config;
        $this->infoBuilder = $infoBuilder;
        $this->serversBuilder = $serversBuilder;
        $this->tagsBuilder = $tagsBuilder;
        $this->pathsBuilder = $pathsBuilder;
        $this->componentsBuilder = $componentsBuilder;
    }

    /**
     * Get all tags used by routes in a collection
     *
     * @param string $collection
     * @return array
     */
    protected function getUsedTags(string $collection): array
    {
        $routes = $this->pathsBuilder->getRoutesForCollection($collection);
        $usedTags = [];

        foreach ($routes as $route) {
            $operationAttribute = $route->actionAttributes
                ->first(static fn(object $attribute) => $attribute instanceof \Vyuldashev\LaravelOpenApi\Attributes\Operation);

            if ($operationAttribute && isset($operationAttribute->tags)) {
                foreach ($operationAttribute->tags as $tag) {
                    if (is_string($tag)) {
                        $usedTags[] = $tag;
                    }
                }
            }
        }

        return array_unique($usedTags);
    }

    /**
     * Get all response factories used by routes in a collection
     *
     * @param string $collection
     * @return array
     */
    protected function getUsedResponseFactories(string $collection): array
    {
        $routes = $this->pathsBuilder->getRoutesForCollection($collection);
        $usedResponseFactories = [];

        foreach ($routes as $route) {
            $responseAttributes = $route->actionAttributes
                ->filter(static fn(object $attribute) => $attribute instanceof \Vyuldashev\LaravelOpenApi\Attributes\Response);

            foreach ($responseAttributes as $attribute) {
                $usedResponseFactories[] = $attribute->factory;
            }
        }

        return array_unique($usedResponseFactories);
    }

    /**
     * Get all schema factories used by routes in a collection
     * This is a simplified implementation that only checks for schemas used in responses
     * A more complete implementation would also check for schemas used in request bodies
     *
     * @param string $collection
     * @return array
     */
    protected function getUsedSchemaFactories(string $collection): array
    {
        $routes = $this->pathsBuilder->getRoutesForCollection($collection);
        $usedSchemaFactories = [];

        foreach ($routes as $route) {
            // Check for schemas in responses
            $responseAttributes = $route->actionAttributes
                ->filter(static fn(object $attribute) => $attribute instanceof \Vyuldashev\LaravelOpenApi\Attributes\Response);

            foreach ($responseAttributes as $attribute) {
                $responseFactory = app($attribute->factory);
                $reflection = new \ReflectionClass($responseFactory);

                // Look for properties that might contain schema factories
                foreach ($reflection->getProperties() as $property) {
                    $property->setAccessible(true);
                    $value = $property->getValue($responseFactory);

                    if (is_string($value) && class_exists($value) && 
                        is_subclass_of($value, \Vyuldashev\LaravelOpenApi\Factories\SchemaFactory::class)) {
                        $usedSchemaFactories[] = $value;
                    }
                }
            }

            // Check for schemas in request bodies
            $requestBodyAttributes = $route->actionAttributes
                ->filter(static fn(object $attribute) => $attribute instanceof \Vyuldashev\LaravelOpenApi\Attributes\RequestBody);

            foreach ($requestBodyAttributes as $attribute) {
                if (isset($attribute->content) && is_array($attribute->content)) {
                    foreach ($attribute->content as $content) {
                        if (isset($content['factory']) && 
                            is_subclass_of($content['factory'], \Vyuldashev\LaravelOpenApi\Factories\SchemaFactory::class)) {
                            $usedSchemaFactories[] = $content['factory'];
                        }
                    }
                }
            }
        }

        return array_unique($usedSchemaFactories);
    }

    public function generate(string $collection = self::COLLECTION_DEFAULT): OpenApi
    {
        $middlewares = Arr::get($this->config, 'collections.'.$collection.'.middlewares');

        $info = $this->infoBuilder->build(Arr::get($this->config, 'collections.'.$collection.'.info', []));
        $servers = $this->serversBuilder->build(Arr::get($this->config, 'collections.'.$collection.'.servers', []));

        // Get used tags for this collection
        $usedTagNames = $this->getUsedTags($collection);
        $allTags = $this->tagsBuilder->build(Arr::get($this->config, 'collections.'.$collection.'.tags', []));
        $tags = array_filter($allTags, function($tag) use ($usedTagNames) {
            return in_array($tag->name, $usedTagNames);
        });

        $paths = $this->pathsBuilder->build($collection, Arr::get($middlewares, 'paths', []));

        // Set used response and schema factories for this collection
        $usedResponseFactories = $this->getUsedResponseFactories($collection);
        $usedSchemaFactories = $this->getUsedSchemaFactories($collection);

        $components = $this->componentsBuilder->build(
            $collection, 
            Arr::get($middlewares, 'components', []),
            $usedResponseFactories,
            $usedSchemaFactories
        );

        $extensions = Arr::get($this->config, 'collections.'.$collection.'.extensions', []);

        $openApi = OpenApi::create()
            ->openapi(OpenApi::OPENAPI_3_0_2)
            ->info($info)
            ->servers(...$servers)
            ->paths(...$paths)
            ->components($components)
            ->security(...Arr::get($this->config, 'collections.'.$collection.'.security', []))
            ->tags(...$tags);

        foreach ($extensions as $key => $value) {
            $openApi = $openApi->x($key, $value);
        }

        return $openApi;
    }
}
