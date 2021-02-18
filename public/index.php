<?php


// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use function Symfony\Component\String\s;

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!');
    return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
});

//$app->get('/users', function ($request, $response) {
//    return $response->write('GET /users');
//});
//
//$app->post('/users', function ($request, $response) {
//    return $response->withStatus(302);
//});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->get('/users/{id}/{nickname}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => $args['nickname']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->get('/users', function ($request, $response) use ($users){
    $term = $request->getQueryParam('term');
    $result = collect($users)->filter(fn($user) => empty($user) ? true : s($user)->containsAny($term));
    $params = [
        'users'=> $users,
        'user' => $result,
        'term' => $term
    ];

    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});
$app->run();