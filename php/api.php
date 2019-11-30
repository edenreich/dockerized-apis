<?php

require_once __DIR__ . '/vendor/autoload.php';

use Swoole\Http\{Server, Request, Response, StatusCode};
use Ramsey\Uuid\Uuid;

class Cat implements JsonSerializable {
    private $id;
    private $name;
    private $age;

    public function __construct(array $attributes = [])
    {
        if (isset($attributes['id'])) {
            $this->setId($attributes['id']);
        }

        if (isset($attributes['name'])) {
            $this->setName($attributes['name']);
        }

        if (isset($attributes['age'])) {
            $this->setAge($attributes['age']);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'age' => $this->getAge()
        ];
    }
}

$cats = [
    new Cat([
        'id'   => '3dbac162-2ef9-400e-b168-e63cf0cde3f6',
        'name' => 'Hunter',
        'age'  => 4
    ]),
    new Cat([
        'id' => '0b8c0ae9-8a4a-4a73-90b7-df68769cd417',
        'name' => 'Garfield',
        'age'  => 2
    ]),
    new Cat([
        'id' => '1f1510e4-b9a4-483e-964a-30f1c2a47b8a',
        'name' => 'Oreo',
        'age'  => 3
    ])
];

class CatsController
{
    /**
     * Simulates in memory database.
     * 
     * @var Cat[] by reference
     */
    private $cats;

    /**
     * @param array &$cats
     */
    public function __construct(array &$cats)
    {
        $this->cats = &$cats;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * 
     * @return Cat[]
     */
    public function listCats(Request $request, Response $response, array $params = []): array
    {
        return $this->cats;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * 
     * @return Cat
     */
    public function getCat(Request $request, Response $response, array $params = []): ?Cat
    {
        foreach ($this->cats as $index => $cat) {
            if ($cat->getId() === $params['id']) {
                return $this->cats[$index];
            }
        }

        $response->status(StatusCode::NOT_FOUND);
        return null;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * 
     * @return Cat[]
     */
    public function createCat(Request $request, Response $response, array $params = []): array
    {
        $body = json_decode($request->rawContent(), true);

        $body['id'] = Uuid::uuid4();

        $cat = new Cat($body);
        $this->cats[] = $cat;

        $response->status(StatusCode::CREATED);
        return $this->cats;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * 
     * @return Cat[]
     */
    public function updateCat(Request $request, Response $response, array $params = []): array
    {
        $body = json_decode($request->rawContent(), true);

        foreach ($this->cats as $index => $cat) {
            if ($cat->getId() === $params['id']) {            
                $body['id'] = $body['id'] ?? $params['id'];
                $cat = new Cat($body);
                $this->cats[$index] = $cat;

                $response->status(StatusCode::CREATED);
            }
        }

        return $this->cats;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * 
     * @return Cat[]
     */
    public function deleteCat(Request $request, Response $response, array $params = []): array
    {
        foreach ($this->cats as $index => $cat) {
            if ($cat->getId() === $params['id']) {
                unset($this->cats[$index]);
                $this->cats = &array_values($this->cats);
            }
        }

        return $this->cats;
    }
}

$port = 8080;
$host = '0.0.0.0';

$server = new Server($host, $port);

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $router) {
    $router->get('/api/cats', 'CatsController@listCats');
    $router->get('/api/cats/{id}', 'CatsController@getCat');
    $router->post('/api/cats', 'CatsController@createCat');
    $router->put('/api/cats/{id}', 'CatsController@updateCat');
    $router->delete('/api/cats/{id}', 'CatsController@deleteCat');
});

$server->on('request', function (Request $request, Response $response) use ($dispatcher, &$cats) {

    $requestMethod = $request->server['request_method'];
    $requestUri = $request->server['request_uri'];

    $_SERVER['REQUEST_URI'] = $requestUri;
    $_SERVER['REQUEST_METHOD'] = $requestMethod;

    $_GET = $request->get ?? [];
    $_FILES = $request->files ?? [];

    if (false !== $pos = strpos($_SERVER['REQUEST_URI'], '?')) {
        $requestUri = substr($_SERVER['REQUEST_URI'], 0, $pos);
    }

    $requestUri = rawurldecode($requestUri);
    
    $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $requestUri);

    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            $json = [
                'status' => StatusCode::NOT_FOUND,
                'message' => 'Method Not Found',
                'errors' => [
                    sprintf('Method "%s" was not found', $requestMethod)
                ]
            ];
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $json = [
                'status' => StatusCode::METHOD_NOT_ALLOWED,
                'message' => 'Method Not Allowed',
                'errors' => [
                    sprintf('Method "%s" is not allowed', $requestMethod)
                ]
            ];
            break;
        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $params = $routeInfo[2];
            $parts = explode('@', $handler);
            $controller = $parts[0];
            $method = $parts[1];
            $controller = new $controller($cats);
            
            $json = $controller->$method($request, $response, $params);
            break;
    }

    $response->header('Content-Type', 'application/json');
    $response->end(json_encode($json));
});

$server->on('start', function (Server $server) {
    echo "Starting API server on $server->host:$server->port\n";
});

$server->start();
