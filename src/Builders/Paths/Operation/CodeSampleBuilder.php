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
            'objective' => ['lang' => 'csharp', 'label' => 'Objective-C'],
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
        if ($route->requestSchema) {
            $resolver = new SchemaResolver();
            return $resolver->resolve($route->requestSchema);
        }

        return [
            'id' => $this->faker->randomNumber(),
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
            'address' => [
                'street' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'zipcode' => $this->faker->postcode
            ],
            'created_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s')
        ];
    }
}
