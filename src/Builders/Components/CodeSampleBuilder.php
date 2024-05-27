<?php

namespace Vyuldashev\LaravelOpenApi\Builders\Components;


use Vyuldashev\LaravelOpenApi\RouteInformation;

class CodeSampleBuilder
{
    public static function build (string $type, RouteInformation $route) : array
    {
        $data = [];

        if ($type === "curl") {
            $data['lang'] = "shell";
            $data['label'] = "CURL";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "php") {
            $data['lang'] = "go";
            $data['label'] = "PHP";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "node-js") {
            $data['lang'] = "js";
            $data['label'] = "JS - Node";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "node-xhr") {
            $data['lang'] = "js";
            $data['label'] = "JS - XHR";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "node-jquery") {
            $data['lang'] = "js";
            $data['label'] = "JS - JQuery";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "python") {
            $data['lang'] = "py";
            $data['label'] = "Python";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "java") {
            $data['lang'] = "java";
            $data['label'] = "Java";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "csharp") {
            $data['lang'] = "csharp";
            $data['label'] = "C# - Reshapr";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "objective") {
            $data['lang'] = "csharp";
            $data['label'] = "Objective-C - NSURL";
            $data['source'] = self::buildSource($type, $route);
        }

        if ($type === "swift") {
            $data['lang'] = "csharp";
            $data['label'] = "Swift - NSURL";
            $data['source'] = self::buildSource($type, $route);
        }

        return $data;
    }

    protected static function buildSource(string $type, RouteInformation $route) : string
    {
        $base_url = substr(config('openapi.collections.default.servers')[0]['url'], 0, -1);

        if ($type === "curl") {
            if ($route->method === 'get') {
                return 'curl -k -i -H "Content-Type: application/json" \
-H "Authorization: Bearer XXXXXXXXXXXXXXX" \
-X GET "' . $base_url . $route->uri .'"';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'curl -k -i -H "Content-Type: application/json" \
-H "Authorization: Bearer XXXXXXXXXXXXXXX" \
-X ' . strtoupper($route->method) . ' -d \'{"field_1":"xyz","field_2":"xyz"}\' "' . $base_url . $route->uri .'"';
            }

            if ($route->method === 'delete') {
                return 'curl -k -i -H "Content-Type: application/json" \
-H "Authorization: Bearer XXXXXXXXXXXXXXX" \
-X DELETE "' . $base_url . $route->uri .'"';
            }
        }

        if ($type === "php") {
            if ($route->method === 'get') {
                return 'setUrl(\'' . $base_url . $route->uri .'\');
$request->setMethod(HTTP_METH_GET);

$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'Authorization\' => \'Bearer XXXXXXXXXXXXXXX\',
    \'content-type\' => \'application/json\'
]);

try {
    $response = $request->send();
    echo $response->getBody();
} catch (HttpException $ex) {
    echo $ex;
}';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                $method = ($route->method === "post") ? 'HTTP_METH_POST' : 'HTTP_METH_PUT';
                return 'setUrl(\'' . $base_url . $route->uri .'\');
$request->setMethod(' . $method . ');

$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'Authorization\' => \'Bearer XXXXXXXXXXXXXXX\',
    \'content-type\' => \'application/json\'
]);

$request->setBody(\'{"field_1":"xyz","field_2":"abc"}\');

try {
    $response = $request->send();
    echo $response->getBody();
} catch (HttpException $ex) {
    echo $ex;
}';
            }

            if ($route->method === 'delete') {
                return 'setUrl(\'' . $base_url . $route->uri .'\');
$request->setMethod(HTTP_METH_DELETE);

$request->setHeaders([
    \'cache-control\' => \'no-cache\',
    \'Authorization\' => \'Bearer XXXXXXXXXXXXXXX\',
    \'content-type\' => \'application/json\'
]);

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
            if ($route->method === 'get') {
                return 'var request = require("request");

var options = {
    method: \'GET\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'Authorization\': \'Bearer XXXXXXXXXXXXXXX\'
        \'content-type\': \'application/json\'
    }
};

request(options, function (error, response, body) {
    if (error) throw new Error(error);
    console.log(body);
});';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'var request = require("request");

var options = {
    method: \'' . strtoupper($route->method) . '\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'Authorization\': \'Bearer XXXXXXXXXXXXXXX\'
        \'content-type\': \'application/json\'
    }
    body: { field_1: \'xyz\', field_2: \'abc\' },
    json: true
};

request(options, function (error, response, body) {
    if (error) throw new Error(error);
    console.log(body);
});';
            }

            if ($route->method === 'delete') {
                return 'var request = require("request");

var options = {
    method: \'DELETE\',
    url: \'' . $base_url . $route->uri .'\',
    headers:
    {
        \'cache-control\': \'no-cache\',
        \'Authorization\': \'Bearer XXXXXXXXXXXXXXX\'
        \'content-type\': \'application/json\'
    }
};

request(options, function (error, response, body) {
    if (error) throw new Error(error);
    console.log(body);
});';
            }
        }

        if ($type === "node-xhr") {
            if ($route->method === 'get') {
                return 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("GET", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'var data = JSON.stringify({
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
xhr.setRequestHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
            }

            if ($route->method === 'delete') {
                return 'var data = null;

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
    if (this.readyState === 4) {
        console.log(this.responseText);
    }
});

xhr.open("DELETE", "' . $base_url . $route->uri .'");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);';
            }
        }

        if ($type === "node-jquery") {
            if ($route->method === 'get') {
                return 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "GET",
    "headers": {
        "content-type": "application/json",
        "Authorization": "Bearer XXXXXXXXXXXXXXX",
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'var jsondata = {"field_1": "xyz","field_2": "abc"};
var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "' . strtoupper($route->method) . '",
    "headers": {
        "content-type": "application/json",
        "Authorization": "Bearer XXXXXXXXXXXXXXX",
        "cache-control": "no-cache"
    },
    "processData": false,
    "data": JSON.stringify(jsondata)
}

$.ajax(settings).done(function (response) {
  console.log(response);
});';
            }

            if ($route->method === 'delete') {
                return 'var settings = {
    "async": true,
    "crossDomain": true,
    "url": "' . $base_url . $route->uri .'",
    "method": "DELETE",
    "headers": {
        "content-type": "application/json",
        "Authorization": "Bearer XXXXXXXXXXXXXXX",
        "cache-control": "no-cache"
    }
}

$.ajax(settings).done(function (response) {
    console.log(response);
});';
            }
        }

        if ($type === "python") {
            if ($route->method === 'get') {
                return 'import requests

url = "' . $base_url . $route->uri .'"

headers = {
    \'content-type\': "application/json",
    \'Authorization\': "Bearer XXXXXXXXXXXXXXX",
    \'cache-control\': "no-cache"
}

response = requests.request("GET", url, headers=headers)

print(response.text)';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'import requests
import json

url = "' . $base_url . $route->uri .'"

payload = json.dumps( {"field_1": "xyz","field_2": "abc"} )
headers = {
    \'content-type\': "application/json",
    \'Authorization\': "Bearer XXXXXXXXXXXXXXX",
    \'cache-control\': "no-cache"
}

response = requests.request("' . strtoupper($route->method) . '", url, data=payload, headers=headers)

print(response.text)';
            }

            if ($route->method === 'delete') {
                return 'import requests

url = "' . $base_url . $route->uri .'"


headers = {
    \'content-type\': "application/json",
    \'Authorization\': "Bearer XXXXXXXXXXXXXXX",
    \'cache-control\': "no-cache"
}

response = requests.request("DELETE", url, headers=headers)

print(response.text)';
            }
        }

        if ($type === "java") {
            if ($route->method === 'get') {
                return 'HttpResponse response = Unirest.get("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("Authorization", "Bearer XXXXXXXXXXXXXXX")
.header("cache-control", "no-cache")
.asString();';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'HttpResponse response = Unirest.' . $route->method . '("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("Authorization", "Bearer XXXXXXXXXXXXXXX")
.header("cache-control", "no-cache")
.body("{\"field_1\":\"xyz\",\"field_2\":\"abc\"}")
.asString();';
            }

            if ($route->method === 'delete') {
                return 'HttpResponse response = Unirest.delete("' . $base_url . $route->uri .'")
.header("content-type", "application/json")
.header("Authorization", "Bearer XXXXXXXXXXXXXXX")
.header("cache-control", "no-cache")
.asString();';
            }
        }

        if ($type === "csharp") {
            if ($route->method === 'get') {
                return 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.GET);
request.AddHeader("cache-control", "no-cache");
request.AddHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
            }

            if ($route->method === 'post' || $route->method === 'put') {
                return 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.' . strtoupper($route->method) . ');
request.AddHeader("cache-control", "no-cache");
request.AddHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");
request.AddHeader("content-type", "application/json");
request.AddParameter("application/json", "{\"field_1\":\"xyz\",\"field_2\":\"abc\"}", ParameterType.RequestBody);
IRestResponse response = client.Execute(request);';
            }

            if ($route->method === 'delete') {
                return 'var client = new RestClient("' . $base_url . $route->uri . '");
var request = new RestRequest(Method.DELETE);
request.AddHeader("cache-control", "no-cache");
request.AddHeader("Authorization", "Bearer XXXXXXXXXXXXXXX");
request.AddHeader("content-type", "application/json");
IRestResponse response = client.Execute(request);';
            }
        }

        if ($type === "objective") {
            if ($route->method === 'get') {
                return '#import

NSDictionary *headers = @{ @"content-type": @"application/json",
                        @"Authorization": @"Bearer XXXXXXXXXXXXXXX",
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

NSDictionary *headers = @{ @"content-type": @"application/json",
                        @"Authorization": @"Bearer XXXXXXXXXXXXXXX",
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

NSDictionary *headers = @{ @"content-type": @"application/json",
                        @"Authorization": @"Bearer XXXXXXXXXXXXXXX",
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
            if ($route->method === 'get') {
                return 'import Foundation

let headers = [
    "content-type": "application/json",
    "Authorization": "Bearer XXXXXXXXXXXXXXX",
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

            if ($route->method === 'post' || $route->method === 'put') {
                return 'import Foundation

let headers = [
    "content-type": "application/json",
    "Authorization": "Bearer XXXXXXXXXXXXXXX",
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

            if ($route->method === 'delete') {
                return 'import Foundation

let headers = [
    "content-type": "application/json",
    "Authorization": "Bearer XXXXXXXXXXXXXXX",
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
        }

        return "";
    }
}
