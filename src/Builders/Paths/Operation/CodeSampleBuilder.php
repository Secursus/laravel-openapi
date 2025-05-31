<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use Vyuldashev\LaravelOpenApi\Attributes\CodeSample as CodeSampleAttribute;
use Vyuldashev\LaravelOpenApi\RouteInformation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CodeSampleBuilder
{
    protected $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(resource_path('templates/code_samples'));
        $this->twig = new Environment($loader);
    }

    public function build(RouteInformation $route): ?array
    {
        $securitySchemes = [];
        $securityEnabled = false;

        $securityAttributes = $route->actionAttributes
            ->filter(static function ($attribute) {
                return $attribute instanceof \Vyuldashev\LaravelOpenApi\Attributes\Security;
            });

        if ($securityAttributes->count() > 0) {
            $securityAttribute = $securityAttributes->first();
            $securityEnabled = $securityAttribute->enabled;
            if ($securityEnabled) {
                $securitySchemes = is_array($securityAttribute->scheme) ? $securityAttribute->scheme : [$securityAttribute->scheme];
            }
        }

        return $route->actionAttributes
            ->filter(static function ($attribute) {
                return $attribute instanceof CodeSampleAttribute;
            })
            ->map(function (CodeSampleAttribute $codeSample) use ($route, $securityEnabled, $securitySchemes) {
                $array_code = [];
                foreach ($codeSample->codes as $code) {
                    $array_code[] = $this->generateTemplate($code, $route, $securityEnabled, $securitySchemes);
                }

                if ($array_code) {
                    return $array_code;
                }

                return null;
            })
            ->values()
            ->toArray();
    }

    protected function generateTemplate(string $lang, RouteInformation $route, bool $securityEnabled, array $securitySchemes): array
    {
        $base_url = config('openapi.collections.default.servers')[0]['url'] ?? '';
        $base_url = rtrim($base_url, '/');

        $context = [
            'method' => $route->method,
            'uri' => $route->uri,
            'base_url' => $base_url,
            'security_enabled' => $securityEnabled,
            'security_schemes' => $securitySchemes,
            'sample_data' => ['field_1' => 'xyz', 'field_2' => 'abc']
        ];

        $languageMap = [
            'curl' => ['lang' => 'shell', 'label' => 'CURL'],
            'php' => ['lang' => 'go', 'label' => 'PHP'],
            'node-js' => ['lang' => 'js', 'label' => 'JS - Node'],
            'node-xhr' => ['lang' => 'js', 'label' => 'JS - XHR'],
            'node-jquery' => ['lang' => 'js', 'label' => 'JS - JQuery'],
            'python' => ['lang' => 'py', 'label' => 'Python'],
            'java' => ['lang' => 'java', 'label' => 'Java'],
            'csharp' => ['lang' => 'csharp', 'label' => 'C# - Reshapr'],
            'objective' => ['lang' => 'csharp', 'label' => 'Objective-C - NSURL'],
            'swift' => ['lang' => 'csharp', 'label' => 'Swift - NSURL'],
        ];

        $template = $this->twig->render("{$lang}.twig", $context);

        return [
            'lang' => $languageMap[$lang]['lang'] ?? $lang,
            'label' => $languageMap[$lang]['label'] ?? ucfirst($lang),
            'source' => $template
        ];
    }
}
