<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use Vyuldashev\LaravelOpenApi\Attributes\CodeSample as CodeSampleAttribute;
use Vyuldashev\LaravelOpenApi\Attributes\Security;
use Vyuldashev\LaravelOpenApi\SchemaResolver;
use Vyuldashev\LaravelOpenApi\RouteInformation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Faker\Factory;

class CodeSampleBuilder
{
    protected $twig;
    protected $faker;

    public function __construct()
    {
        $template_path = __DIR__ . '/../../../../resources/templates/code_samples';
        $loader = new FilesystemLoader($template_path);
        $this->twig = new Environment($loader);
        $this->faker = Factory::create();
    }

    public function build(RouteInformation $route): ?array
    {
        $security_schemes = [];
        $security_enabled = false;

        $security_attributes = $route->actionAttributes
            ->filter(static function ($attribute) {
                return $attribute instanceof Security;
            });

        if ($security_attributes->count() > 0) {
            /** @var Security $security_attribute */
            $security_attribute = $security_attributes->first();
            $security_enabled = $security_attribute->enabled;
            if ($security_enabled) {
                $security_schemes = is_array($security_attribute->scheme) ?
                    $security_attribute->scheme :
                    [$security_attribute->scheme]
                ;
            }
        }

        return $route->actionAttributes
            ->filter(static function ($attribute) {
                return $attribute instanceof CodeSampleAttribute;
            })
            ->map(function (CodeSampleAttribute $codeSample) use ($route, $security_enabled, $security_schemes) {
                $array_code = [];
                foreach ($codeSample->codes as $code) {
                    $array_code[] = $this->generateTemplate($code, $route, $security_enabled, $security_schemes);
                }

                if ($array_code) {
                    return $array_code;
                }

                return null;
            })
            ->values()
            ->toArray()
        ;
    }

    protected function generateTemplate(string $lang, RouteInformation $route, bool $security_enabled, array $security_schemes): array
    {
        $base_url = config('openapi.collections.default.servers')[0]['url'] ?? '';
        $base_url = rtrim($base_url, '/');

        $context = [
            'method' => $route->method,
            'uri' => $route->uri,
            'base_url' => $base_url,
            'security_enabled' => $security_enabled,
            'security_schemes' => $security_schemes,
            'sample_data' => $this->generateSampleData($route)
        ];

        $languageMap = [
            'curl' => ['lang' => 'shell', 'label' => 'CURL'],
            'php' => ['lang' => 'go', 'label' => 'PHP'],
            'javascript' => ['lang' => 'js', 'label' => 'Javascript'],
            'python' => ['lang' => 'py', 'label' => 'Python'],
            'java' => ['lang' => 'java', 'label' => 'Java'],
            'csharp' => ['lang' => 'csharp', 'label' => 'C#'],
            'swift' => ['lang' => 'csharp', 'label' => 'Swift'],
            'ruby' => ['lang' => 'go', 'label' => 'Ruby'],
            'go' => ['lang' => 'go', 'label' => 'Go'],
        ];

        $template = $this->twig->render("{$lang}.twig", $context);

        return [
            'lang' => $languageMap[$lang]['lang'] ?? $lang,
            'label' => $languageMap[$lang]['label'] ?? ucfirst($lang),
            'source' => $template
        ];
    }

    protected function generateSampleData(RouteInformation $route): array
    {
        if ($route->requestBodyInstance && 
            property_exists($route->requestBodyInstance, 'content') && 
            $route->requestBodyInstance->content && 
            is_array($route->requestBodyInstance->content)) {
            
            $targetMediaTypeString = 'application/json';
            $foundMediaType = null;
            
            foreach ($route->requestBodyInstance->content as $index => $mediaTypeObject) {
                if ($mediaTypeObject instanceof \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType) {
                    $currentMediaType = $mediaTypeObject->mediaType; 
                    if ($currentMediaType === $targetMediaTypeString) {
                        $foundMediaType = $mediaTypeObject;
                        break;
                    }
                }
            }

            if ($foundMediaType) {
                if (property_exists($foundMediaType, 'example') && $foundMediaType->example) {
                    $exampleValue = $foundMediaType->example;
                    if ($foundMediaType->example instanceof \GoldSpecDigital\ObjectOrientedOAS\Objects\Example) {
                        $exampleValue = $foundMediaType->example->value;
                    }
                    
                    if (is_array($exampleValue)) {
                        return $exampleValue;
                    }
                }
            }
        }

        if ($route->requestSchema) {
            $resolver = new SchemaResolver();
            $resolvedExample = $resolver->resolve($route->requestSchema); 
            
            if (is_array($resolvedExample)) {
                return $resolvedExample;
            }
        }

        return $this->generateFromFaker();
    }

    protected function generateFromFaker(): array
    {
        return [];
    }
}
