<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Paths\Operation;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Vyuldashev\LaravelOpenApi\Attributes\CodeSample as CodeSampleAttribute;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use Vyuldashev\LaravelOpenApi\RouteInformation;

class CodeSampleBuilder
{
    public function build(RouteInformation $route): ?array
    {
        // Get security schemes from Security attribute
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
            ->map(static function (CodeSampleAttribute $codeSample) use ($route, $securityEnabled, $securitySchemes) {
                $array_code = [];
                foreach ($codeSample->codes as $code) {
                    $array_code[] = self::generate($code, $route, $securityEnabled, $securitySchemes);
                }

                if ($array_code) {
                    return $array_code;
                }

                return null;
            })
            ->values()
            ->toArray();
    }

    protected static function generate(string $type, RouteInformation $route, bool $securityEnabled = false, array $securitySchemes = []) : array
    {
        $data = [];

        if ($type === "curl") {
            $data['lang'] = "shell";
            $data['label'] = "CURL";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "php") {
            $data['lang'] = "go";
            $data['label'] = "PHP";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "node-js") {
            $data['lang'] = "js";
            $data['label'] = "JS - Node";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "node-xhr") {
            $data['lang'] = "js";
            $data['label'] = "JS - XHR";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "node-jquery") {
            $data['lang'] = "js";
            $data['label'] = "JS - JQuery";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "python") {
            $data['lang'] = "py";
            $data['label'] = "Python";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "java") {
            $data['lang'] = "java";
            $data['label'] = "Java";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "csharp") {
            $data['lang'] = "csharp";
            $data['label'] = "C# - Reshapr";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "objective") {
            $data['lang'] = "csharp";
            $data['label'] = "Objective-C - NSURL";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        if ($type === "swift") {
            $data['lang'] = "csharp";
            $data['label'] = "Swift - NSURL";
            $data['source'] = self::buildSource($type, $route, $securityEnabled, $securitySchemes);
        }

        return $data;
    }

    protected static function buildSource(string $type, RouteInformation $route, bool $securityEnabled, array $securitySchemes) : string
    {
        $base_url = substr(config('openapi.collections.default.servers')[0]['url'], 0, -1);

        if ($type === "curl") {
            $auth_code = '';
            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $auth_code .= '
-H "Authorization: Bearer XXXXXXXXXXXXXXX" \ ';
                }
                if (in_array('BasicAuth', $securitySchemes)) {
                    $auth_code .= '
# Or with BasicAuth
-H "Authorization: Basic XXXXXXXXXXXXXXX" \ ';
                }
            }

            if ($route->method === 'get') {
                return 'curl -k -i -H "Content-Type: application/json" \ ' . $auth_code . '
-X GET "' . $base_url . $route->uri .'"';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'curl -k -i -H "Content-Type: application/json" \ ' . $auth_code . '
-X ' . strtoupper($route->method) . ' -d \'{"field_1":"xyz","field_2":"xyz"}\' \
"' . $base_url . $route->uri .'"';
            }

            if ($route->method === 'delete') {
                return 'curl -k -i -H "Content-Type: application/json" \ ' . $auth_code . '
-X DELETE "' . $base_url . $route->uri .'"';
            }
        }

        if ($type === "php") {
            $bearer = '';
            $basic_auth = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer = '
    \'Authorization\' => \'Bearer XXXXXXXXXXXXXXX\',';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth = '
    \'Authorization\' => \'Basic XXXXXXXXXXXXXXX\',';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer && $basic_auth) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $bearer . '
    \'content-type\' => \'application/json\'
]);
// Or with BasicAuth
$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $basic_auth . '
    \'content-type\' => \'application/json\'
])';
                    } elseif ($bearer) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $bearer . '
    \'content-type\' => \'application/json\'
])';
                    } elseif ($basic_auth) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $basic_auth . '
    \'content-type\' => \'application/json\'
])';
                    } else {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'content-type\' => \'application/json\'
])';
                    }
                } else {
                    $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'content-type\' => \'application/json\'
])';
                }

                return 'setUrl(\'' . $base_url . $route->uri .'\');
$request->setMethod(HTTP_METH_GET);

' . $auth_code . ';

try {
    $response = $request->send();
    echo $response->getBody();
} catch (HttpException $ex) {
    echo $ex;
}';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $method = ($route->method === "post") ? 'HTTP_METH_POST' : 'HTTP_METH_PUT';

                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer && $basic_auth) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $bearer . '
    \'content-type\' => \'application/json\'
]);
// Or with BasicAuth
$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $basic_auth . '
    \'content-type\' => \'application/json\'
])';
                    } elseif ($bearer) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $bearer . '
    \'content-type\' => \'application/json\'
])';
                    } elseif ($basic_auth) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $basic_auth . '
    \'content-type\' => \'application/json\'
])';
                    } else {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'content-type\' => \'application/json\'
])';
                    }
                } else {
                    $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'content-type\' => \'application/json\'
])';
                }

                return 'setUrl(\'' . $base_url . $route->uri .'\');
$request->setMethod(' . $method . ');

' . $auth_code . ';

$request->setBody(\'{"field_1":"xyz","field_2":"abc"}\');

try {
    $response = $request->send();
    echo $response->getBody();
} catch (HttpException $ex) {
    echo $ex;
}';
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer && $basic_auth) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $bearer . '
    \'content-type\' => \'application/json\'
]);
// Or with BasicAuth
$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $basic_auth . '
    \'content-type\' => \'application/json\'
])';
                    } elseif ($bearer) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $bearer . '
    \'content-type\' => \'application/json\'
])';
                    } elseif ($basic_auth) {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',' . $basic_auth . '
    \'content-type\' => \'application/json\'
])';
                    } else {
                        $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'content-type\' => \'application/json\'
])';
                    }
                } else {
                    $auth_code = '$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'content-type\' => \'application/json\'
])';
                }

                return 'setUrl(\'' . $base_url . $route->uri .'\');
$request->setMethod(HTTP_METH_DELETE);

' . $auth_code . ';

try {
    $response = $request->send();
    echo $response->getBody();
} catch (HttpException $ex) {
    echo $ex;
}
                ';
            }
        }

        if ($type === "node-js") {
            $bearer = '';
            $basic_auth = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer = '
        \'Authorization\': \'Bearer XXXXXXXXXXXXXXX\',';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth = '
        \'Authorization\': \'Basic XXXXXXXXXXXXXXX\',';
                }
            }
            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer && $basic_auth) {
                        $auth_code = 'var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $bearer . '
        \'content-type\': \'application/json\'
    }
};
// Or with BasicAuth
var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $basic_auth . '
        \'content-type\': \'application/json\'
    }
};';
                    } elseif ($bearer) {
                        $auth_code = 'var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $bearer . '
        \'content-type\': \'application/json\'
    }
};';
                    } elseif ($basic_auth) {
                        $auth_code = 'var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $basic_auth . '
        \'content-type\': \'application/json\'
    }
};';
                    } else {
                        $auth_code = 'var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'content-type\': \'application/json\'
    }
};';
                    }
                } else {
                    $auth_code = 'var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'content-type\': \'application/json\'
    }
};';
                }

                return 'var request = require("request");

' . $auth_code . '

request(options, function (error, response, body) {
    if (error) throw new Error(error);
    console.log(body);
});';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer && $basic_auth) {
                        $auth_code = 'var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $bearer . '
        \'content-type\': \'application/json\'
    },
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};
// Or with BasicAuth
var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $basic_auth . '
        \'content-type\': \'application/json\'
    },
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};';
                    } elseif ($bearer) {
                        $auth_code = 'var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $bearer . '
        \'content-type\': \'application/json\'
    },
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};';
                    } elseif ($basic_auth) {
                        $auth_code = 'var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $basic_auth . '
        \'content-type\': \'application/json\'
    },
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};';
                    } else {
                        $auth_code = 'var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'content-type\': \'application/json\'
    },
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};';
                    }
                } else {
                    $auth_code = 'var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'content-type\': \'application/json\'
    },
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};';
                }

                return 'var request = require("request");

' . $auth_code . '

request(options, function (error, response, body) {
    if (error) throw new Error(error);
    console.log(body);
});';
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer && $basic_auth) {
                        $auth_code = 'var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $bearer . '
        \'content-type\': \'application/json\'
    }
};
// Or with BasicAuth
var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $basic_auth . '
        \'content-type\': \'application/json\'
    }
};';
                    } elseif ($bearer) {
                        $auth_code = 'var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $bearer . '
        \'content-type\': \'application/json\'
    }
};';
                    } elseif ($basic_auth) {
                        $auth_code = 'var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',' . $basic_auth . '
        \'content-type\': \'application/json\'
    }
};';
                    } else {
                        $auth_code = 'var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'content-type\': \'application/json\'
    }
};';
                    }
                } else {
                    $auth_code = 'var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'content-type\': \'application/json\'
    }
};';
                }

                return 'var request = require("request");

' . $auth_code . '

request(options, function (error, response, body) {
    if (error) throw new Error(error);
    console.log(body);
});';
            }
        }

        if ($type === "node-xhr") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
xhr.setRequestHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
xhr.setRequestHeader("Authorization", "Basic XXXXXXXXXXXXXXX");';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");' . $bearer_code . '
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);

// Or with BasicAuth

var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");' . $basic_auth_code . '
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } elseif ($bearer_code) {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");' . $bearer_code . '
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");' . $basic_auth_code . '
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } else {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    }
                } else {
                    $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                }

                return $auth_code;
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var data = JSON.stringify({
    "field_1": "xyz",
    "field_2": "xyz"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("' . strtoupper($route->method) . '", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $bearer_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);

// Or with BasicAuth

var data = JSON.stringify({
    "field_1": "xyz",
    "field_2": "xyz"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("' . strtoupper($route->method) . '", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $basic_auth_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } elseif ($bearer_code) {
                        $auth_code = 'var data = JSON.stringify({
    "field_1": "xyz",
    "field_2": "xyz"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("' . strtoupper($route->method) . '", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $bearer_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var data = JSON.stringify({
    "field_1": "xyz",
    "field_2": "xyz"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("' . strtoupper($route->method) . '", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $basic_auth_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } else {
                        $auth_code = 'var data = JSON.stringify({
    "field_1": "xyz",
    "field_2": "xyz"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("' . strtoupper($route->method) . '", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    }
                } else {
                    $auth_code = 'var data = JSON.stringify({
    "field_1": "xyz",
    "field_2": "xyz"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("' . strtoupper($route->method) . '", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                }

                return $auth_code;
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $bearer_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);

// Or with BasicAuth

var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $basic_auth_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } elseif ($bearer_code) {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $bearer_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");' . $basic_auth_code . '
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    } else {
                        $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                    }
                } else {
                    $auth_code = 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
                }

                return $auth_code;
            }
        }

        if ($type === "node-jquery") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
        "Authorization": "Bearer XXXXXXXXXXXXXXX",';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
        "Authorization": "Basic XXXXXXXXXXXXXXX",';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",' . $bearer_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});

// Or with BasicAuth

var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",' . $basic_auth_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } elseif ($bearer_code) {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",' . $bearer_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",' . $basic_auth_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } else {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    }
                } else {
                    $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                }

                return $auth_code;
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",' . $bearer_code . '
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
    console.log(response);
});

// Or with BasicAuth

var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",' . $basic_auth_code . '
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } elseif ($bearer_code) {
                        $auth_code = 'var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",' . $bearer_code . '
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",' . $basic_auth_code . '
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } else {
                        $auth_code = 'var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    }
                } else {
                    $auth_code = 'var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                }

                return $auth_code;
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",' . $bearer_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});

// Or with BasicAuth

var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",' . $basic_auth_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } elseif ($bearer_code) {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",' . $bearer_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",' . $basic_auth_code . '
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    } else {
                        $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                    }
                } else {
                    $auth_code = 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
                }

                return $auth_code;
            }
        }

        if ($type === "python") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
    \'Authorization\': "Bearer XXXXXXXXXXXXXXX",';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
    \'Authorization\': "Basic XXXXXXXXXXXXXXX",';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $bearer_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)

# OR

import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $basic_auth_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)';
                    } elseif ($bearer_code) {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $bearer_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $basic_auth_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)';
                    } else {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)';
                    }
                } else {
                    $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)';
                }

                return $auth_code;
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",' . $bearer_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)

# OR

import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",' . $basic_auth_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)';
                    } elseif ($bearer_code) {
                        $auth_code = 'import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",' . $bearer_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",' . $basic_auth_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)';
                    } else {
                        $auth_code = 'import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)';
                    }
                } else {
                    $auth_code = 'import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)';
                }

                return $auth_code;
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $bearer_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)

# OR

import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $basic_auth_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)';
                    } elseif ($bearer_code) {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $bearer_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",' . $basic_auth_code . '
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)';
                    } else {
                        $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)';
                    }
                } else {
                    $auth_code = 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)';
                }

                return $auth_code;
            }
        }

        if ($type === "java") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
                .header("Authorization", "Bearer XXXXXXXXXXXXXXX")';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
                .header("Authorization", "Basic XXXXXXXXXXXXXXX")';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $bearer_code . '
.header("cache-control", "no-cache")
.asString();

// Or with BasicAuth

HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $basic_auth_code . '
.header("cache-control", "no-cache")
.asString();';
                    } elseif ($bearer_code) {
                        $auth_code = 'HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $bearer_code . '
.header("cache-control", "no-cache")
.asString();';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $basic_auth_code . '
.header("cache-control", "no-cache")
.asString();';
                    } else {
                        $auth_code = 'HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("cache-control", "no-cache")
.asString();';
                    }
                } else {
                    $auth_code = 'HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("cache-control", "no-cache")
.asString();';
                }

                return $auth_code;
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $bearer_code . '
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();

// Or with BasicAuth

HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $basic_auth_code . '
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();';
                    } elseif ($bearer_code) {
                        $auth_code = 'HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $bearer_code . '
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $basic_auth_code . '
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();';
                    } else {
                        $auth_code = 'HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();';
                    }
                } else {
                    $auth_code = 'HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();';
                }

                return $auth_code;
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $bearer_code . '
.header("cache-control", "no-cache")
.asString();

// Or with BasicAuth

HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $basic_auth_code . '
.header("cache-control", "no-cache")
.asString();';
                    } elseif ($bearer_code) {
                        $auth_code = 'HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $bearer_code . '
.header("cache-control", "no-cache")
.asString();';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")' . $basic_auth_code . '
.header("cache-control", "no-cache")
.asString();';
                    } else {
                        $auth_code = 'HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("cache-control", "no-cache")
.asString();';
                    }
                } else {
                    $auth_code = 'HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("cache-control", "no-cache")
.asString();';
                }

                return $auth_code;
            }
        }

        if ($type === "csharp") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
request.AddHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
request.AddHeader("Authorization", "Basic XXXXXXXXXXXXXXX");';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");' . $bearer_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);

// Or with BasicAuth

var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");' . $basic_auth_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    } elseif ($bearer_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");' . $bearer_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");' . $basic_auth_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    } else {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    }
                } else {
                    $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                }

                return $auth_code;
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");' . $bearer_code . '
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);

// Or with BasicAuth

var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");' . $basic_auth_code . '
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);';
                    } elseif ($bearer_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");' . $bearer_code . '
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");' . $basic_auth_code . '
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);';
                    } else {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);';
                    }
                } else {
                    $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);';
                }

                return $auth_code;
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");' . $bearer_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);

// Or with BasicAuth

var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");' . $basic_auth_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    } elseif ($bearer_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");' . $bearer_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");' . $basic_auth_code . '
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    } else {
                        $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                    }
                } else {
                    $auth_code = 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
                }

                return $auth_code;
            }
        }

        if ($type === "objective") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
                        @"Authorization": @"Bearer XXXXXXXXXXXXXXX",';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
                        @"Authorization": @"Basic XXXXXXXXXXXXXXX",';
                }
            }

            if ($route->method === 'get') {
                return '#import

NSDictionary *headers = @{ @"content-type": @"application/json",' . $bearer_code . '
                        @"cache-control": @"no-cache";

NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:@"' . $base_url . $route->uri . '"]
                                                    cachePolicy:NSURLRequestUseProtocolCachePolicy
                                                    timeoutInterval:10.0];
[request setHTTPMethod:@"GET"];
[request setAllHTTPHeaderFields:headers];

NSURLSession *session = [NSURLSession sharedSession];
NSURLSessionDataTask *dataTask =   [session dataTaskWithRequest:request
                                            completionHandler:^(NSData *data, NSURLResponse *response, NSError *error) {
                                                if (error) {
                                                    NSLog(@"%@", error);
                                                } else {
                                                    NSHTTPURLResponse *httpResponse = (NSHTTPURLResponse *) response;
                                                    NSLog(@"%@", httpResponse);
                                                }
                                            }];
[dataTask resume];';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return '#import

NSDictionary *headers = @{ @"content-type": @"application/json",' . $bearer_code . '
                        @"cache-control": @"no-cache" };
NSDictionary *parameters = @{ @"field_1": @"xyz",
                            @"field_2": @"abc" };

NSData *postData = [NSJSONSerialization dataWithJSONObject:parameters options:0 error:nil];

NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:@"' . $base_url . $route->uri . '"]
                                                    cachePolicy:NSURLRequestUseProtocolCachePolicy
                                                    timeoutInterval:10.0];
[request setHTTPMethod:@"' . strtoupper($route->method) . '"];
[request setAllHTTPHeaderFields:headers];
[request setHTTPBody:postData];

NSURLSession *session = [NSURLSession sharedSession];
NSURLSessionDataTask *dataTask =   [session dataTaskWithRequest:request
                                            completionHandler:^(NSData *data, NSURLResponse *response, NSError *error) {
                                                if (error) {
                                                    NSLog(@"%@", error);
                                                } else {
                                                    NSHTTPURLResponse *httpResponse = (NSHTTPURLResponse *) response;
                                                    NSLog(@"%@", httpResponse);
                                                }
                                            }];
[dataTask resume];';
            }

            if ($route->method === 'delete') {
                return '#import

NSDictionary *headers = @{ @"content-type": @"application/json",' . $bearer_code . '
                        @"cache-control": @"no-cache" };

NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:@"' . $base_url . $route->uri . '"]
                                                    cachePolicy:NSURLRequestUseProtocolCachePolicy
                                                    timeoutInterval:10.0];
[request setHTTPMethod:@"DELETE"];
[request setAllHTTPHeaderFields:headers];

NSURLSession *session = [NSURLSession sharedSession];
NSURLSessionDataTask *dataTask =   [session dataTaskWithRequest:request
                                            completionHandler:^(NSData *data, NSURLResponse *response, NSError *error) {
                                                if (error) {
                                                    NSLog(@"%@", error);
                                                } else {
                                                    NSHTTPURLResponse *httpResponse = (NSHTTPURLResponse *) response;
                                                    NSLog(@"%@", httpResponse);
                                                }
                                            }];
[dataTask resume];';
            }
        }

        if ($type === "swift") {
            $bearer_code = '';
            $basic_auth_code = '';

            if ($securityEnabled) {
                if (in_array('BearerToken', $securitySchemes)) {
                    $bearer_code = '
    "Authorization": "Bearer XXXXXXXXXXXXXXX",';
                }

                if (in_array('BasicAuth', $securitySchemes)) {
                    $basic_auth_code = '
    "Authorization": "Basic XXXXXXXXXXXXXXX",';
                }
            }

            if ($route->method === 'get') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $bearer_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                       cachePolicy: .useProtocolCachePolicy,
                                       timeoutInterval: 10.0)
request.httpMethod = "GET"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()

// Or with BasicAuth

import Foundation

let headers = [
    "content-type": "application/json",' . $basic_auth_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                       cachePolicy: .useProtocolCachePolicy,
                                       timeoutInterval: 10.0)
request.httpMethod = "GET"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } elseif ($bearer_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $bearer_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                       cachePolicy: .useProtocolCachePolicy,
                                       timeoutInterval: 10.0)
request.httpMethod = "GET"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $basic_auth_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                       cachePolicy: .useProtocolCachePolicy,
                                       timeoutInterval: 10.0)
request.httpMethod = "GET"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } else {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                       cachePolicy: .useProtocolCachePolicy,
                                       timeoutInterval: 10.0)
request.httpMethod = "GET"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    }
                } else {
                    $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                       cachePolicy: .useProtocolCachePolicy,
                                       timeoutInterval: 10.0)
request.httpMethod = "GET"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                }

                return $auth_code;
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $bearer_code . '
    "cache-control": "no-cache"
]
let parameters = [
    "field_1": "xyz",
    "field_2": "abc"
] as [String : Any]

let postData = JSONSerialization.data(withJSONObject: parameters, options: [])

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "' . strtoupper($route->method) . '"
request.allHTTPHeaderFields = headers
request.httpBody = postData as Data

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()

// Or with BasicAuth

import Foundation

let headers = [
    "content-type": "application/json",' . $basic_auth_code . '
    "cache-control": "no-cache"
]
let parameters = [
    "field_1": "xyz",
    "field_2": "abc"
] as [String : Any]

let postData = JSONSerialization.data(withJSONObject: parameters, options: [])

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "' . strtoupper($route->method) . '"
request.allHTTPHeaderFields = headers
request.httpBody = postData as Data

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } elseif ($bearer_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $bearer_code . '
    "cache-control": "no-cache"
]
let parameters = [
    "field_1": "xyz",
    "field_2": "abc"
] as [String : Any]

let postData = JSONSerialization.data(withJSONObject: parameters, options: [])

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "' . strtoupper($route->method) . '"
request.allHTTPHeaderFields = headers
request.httpBody = postData as Data

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $basic_auth_code . '
    "cache-control": "no-cache"
]
let parameters = [
    "field_1": "xyz",
    "field_2": "abc"
] as [String : Any]

let postData = JSONSerialization.data(withJSONObject: parameters, options: [])

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "' . strtoupper($route->method) . '"
request.allHTTPHeaderFields = headers
request.httpBody = postData as Data

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } else {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",
    "cache-control": "no-cache"
]
let parameters = [
    "field_1": "xyz",
    "field_2": "abc"
] as [String : Any]

let postData = JSONSerialization.data(withJSONObject: parameters, options: [])

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "' . strtoupper($route->method) . '"
request.allHTTPHeaderFields = headers
request.httpBody = postData as Data

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    }
                } else {
                    $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",
    "cache-control": "no-cache"
]
let parameters = [
    "field_1": "xyz",
    "field_2": "abc"
] as [String : Any]

let postData = JSONSerialization.data(withJSONObject: parameters, options: [])

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "' . strtoupper($route->method) . '"
request.allHTTPHeaderFields = headers
request.httpBody = postData as Data

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                }

                return $auth_code;
            }

            if ($route->method === 'delete') {
                $auth_code = '';

                if ($securityEnabled) {
                    if ($bearer_code && $basic_auth_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $bearer_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "DELETE"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()

// Or with BasicAuth

import Foundation

let headers = [
    "content-type": "application/json",' . $basic_auth_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "DELETE"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } elseif ($bearer_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $bearer_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "DELETE"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } elseif ($basic_auth_code) {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",' . $basic_auth_code . '
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "DELETE"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    } else {
                        $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "DELETE"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                    }
                } else {
                    $auth_code = 'import Foundation

let headers = [
    "content-type": "application/json",
    "cache-control": "no-cache"
]

let request = NSMutableURLRequest(url: NSURL(string: "' . $base_url . $route->uri . '")! as URL,
                                        cachePolicy: .useProtocolCachePolicy,
                                        timeoutInterval: 10.0)
request.httpMethod = "DELETE"
request.allHTTPHeaderFields = headers

let session = URLSession.shared
let dataTask = session.dataTask(with: request as URLRequest, completionHandler: { (data, response, error) -> Void in
    if (error != nil) {
        print(error)
    } else {
        let httpResponse = response as? HTTPURLResponse
        print(httpResponse)
    }
})

dataTask.resume()';
                }

                return $auth_code;
            }
        }

        return "";
    }
}
