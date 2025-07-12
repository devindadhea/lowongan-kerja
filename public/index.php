<?php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Medoo\Medoo;


require __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Buat container
$container = new Container();
AppFactory::setContainer($container);

// === Registrasi Medoo
$container->set(Medoo::class, function () {
    return new Medoo([
        'type'     => 'mysql',
        'host'     => 'localhost',
        'database' => 'lowongan_kerja',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ]);
});

// registrasi
$container->set(Twig::class, function () {
    return Twig::create(__DIR__ . '/../views', ['cache' => false]);
});

use App\Controllers\Admin\JobController;
$container->set(JobController::class, function($c) {
    return new JobController(
        $c->get(Twig::class),
        $c->get(Medoo::class)
    );
});

// === Buat app
$app = AppFactory::create();
$app->setBasePath('/lowongan-kerja/public');

// === Tambah middleware Twig
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

// === Helper
function view($request, $response, $template, $data = [])
{
    global $container;
    $twig = $container->get(Twig::class); 
    return $twig->render($response, $template, $data);
}

function base_path()
{
    return '/lowongan-kerja/public';
}

// === Load routes
require __DIR__ . '/../routes/web.php';

$app->run();
