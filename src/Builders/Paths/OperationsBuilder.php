<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock;
use Vyuldashev\LaravelOpenApi\Attributes\Operation as OperationAttribute;
use Vyuldashev\LaravelOpenApi\Builders\ExtensionsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\CallbacksBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\CodeSampleBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ParametersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\RequestBodyBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ResponsesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\SecurityBuilder;
use Vyuldashev\LaravelOpenApi\Factories\ServerFactory;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class OperationsBuilder
{
    protected CallbacksBuilder $callbacksBuilder;
    protected ParametersBuilder $parametersBuilder;
    protected CodeSampleBuilder $codeSampleBuilder;
    protected RequestBodyBuilder $requestBodyBuilder;
    protected ResponsesBuilder $responsesBuilder;
    protected ExtensionsBuilder $extensionsBuilder;
    protected SecurityBuilder $securityBuilder;

    public function __construct(
        CallbacksBuilder   $callbacksBuilder,
        ParametersBuilder  $parametersBuilder,
        CodeSampleBuilder  $codeSampleBuilder,
        RequestBodyBuilder $requestBodyBuilder,
        ResponsesBuilder   $responsesBuilder,
        ExtensionsBuilder  $extensionsBuilder,
        SecurityBuilder    $securityBuilder
    )
    {
        $this->callbacksBuilder = $callbacksBuilder;
        $this->parametersBuilder = $parametersBuilder;
        $this->codeSampleBuilder = $codeSampleBuilder;
        $this->requestBodyBuilder = $requestBodyBuilder;
        $this->responsesBuilder = $responsesBuilder;
        $this->extensionsBuilder = $extensionsBuilder;
        $this->securityBuilder = $securityBuilder;
    }

    /**
     * @param RouteInformation[]|Collection $routes
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function build(array|Collection $routes): array
    {
        $operations = [];

        /** @var RouteInformation[] $routes */
        foreach ($routes as $route) {
            /** @var OperationAttribute|null $operationAttribute */
            $operationAttribute = $route->actionAttributes
                ->first(static fn(object $attribute) => $attribute instanceof OperationAttribute);

            $route->requestSchema = $operationAttribute->requestSchema ?? null;
            $route->responseSchema = $operationAttribute->responseSchema; // This seems to be OperationAttribute specific, not from RequestBody
            $operationId = optional($operationAttribute)->id;
            $tags = $operationAttribute->tags ?? [];
            $servers = collect($operationAttribute->servers)
                ->filter(fn($server) => app($server) instanceof ServerFactory)
                ->map(static fn($server) => app($server)->build())
                ->toArray();

            $parameters = $this->parametersBuilder->build($route);
            $requestBody = $this->requestBodyBuilder->build($route);
            $route->requestBodyInstance = $requestBody; // Assign instance to RouteInformation

            if ($requestBody) {
                
                if ($requestBody->content === null) {
                } else {
                    
                    $targetMediaTypeString = 'application/json';
                    $foundMediaTypeObject = null;

                    if (is_array($requestBody->content)) {
                        foreach ($requestBody->content as $index => $mediaTypeObject) {
                            if ($mediaTypeObject instanceof \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType) {
                                $currentMediaTypeString = $mediaTypeObject->mediaType;
                                if ($currentMediaTypeString === $targetMediaTypeString) {
                                    $foundMediaTypeObject = $mediaTypeObject;
                                    break; 
                                }
                            } else {
                            }
                        }
                    } else {
                    }

                    if ($foundMediaTypeObject) {
                        // Vérifier si la propriété 'schema' existe et n'est pas nulle sur l'objet MediaType trouvé
                        if (property_exists($foundMediaTypeObject, 'schema') && $foundMediaTypeObject->schema) {
                            // Assigner la version tableau du schéma
                            $route->requestSchema = $foundMediaTypeObject->schema->toArray();
                        } else {
                        }
                    } else {
                    }
                }
            } else {
            }

            $codeSample = $this->codeSampleBuilder->build($route);

            $responses = $this->responsesBuilder->build($route);
            $callbacks = $this->callbacksBuilder->build($route);
            $security = $this->securityBuilder->build($route);

            $operation = Operation::create()
                ->action(Str::lower($operationAttribute->method) ?: $route->method)
                ->tags(...$tags)
                ->deprecated($this->isDeprecated($route->actionDocBlock))
                ->description($route->actionDocBlock->getDescription()->render() !== '' ? $route->actionDocBlock->getDescription()->render() : null)
                ->summary($route->actionDocBlock->getSummary() !== '' ? $route->actionDocBlock->getSummary() : null)
                ->operationId($operationId)
                ->parameters(...$parameters)
                ->requestBody($requestBody)
                ->responses(...$responses)
                ->callbacks(...$callbacks)
                ->servers(...$servers);

            if (count($security) === 1 && $security[0]->securityScheme === null) {
                $operation = $operation->noSecurity();
            } else {
                $operation = $operation->security(...$security);
            }

            if (!empty($codeSample)) {
                $operation->x('codeSamples', $codeSample[0]);
            }

            $this->extensionsBuilder->build($operation, $route->actionAttributes);

            $operations[] = $operation;
        }

        return $operations;
    }

    protected function isDeprecated(?DocBlock $actionDocBlock): ?bool
    {
        if ($actionDocBlock === null) {
            return null;
        }

        $deprecatedTag = $actionDocBlock->getTagsByName('deprecated');

        if (count($deprecatedTag) > 0) {
            return true;
        }

        return null;
    }
}
