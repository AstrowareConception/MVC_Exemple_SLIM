<?php

declare(strict_types=1);

use App\Controller\ApiEmpruntController;
use App\Controller\ApiLivreController;
use App\Controller\EmpruntController;
use App\Controller\LivreController;
use App\Database\ConnectionFactory;
use App\Http\CsrfGuard;
use App\Http\ErrorHandler;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use App\Service\EmpruntService;
use App\View\HtmlView;
use App\View\JsonView;
use Slim\Factory\AppFactory;

// Avec le serveur PHP intégré, les fichiers réels (CSS, images...) doivent être
// servis directement au lieu d'être transmis au routeur Slim.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_string($path) && $path !== '/' && is_file(__DIR__ . $path)) {
        return false;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

$settings = require dirname(__DIR__) . '/config/settings.php';
date_default_timezone_set($settings['timezone']);

session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

if (!is_file($settings['database_path'])) {
    throw new RuntimeException('Base absente : exécutez composer db:init avant de démarrer le serveur.');
}

// Composition root : toutes les dépendances sont assemblées à un seul endroit.
$pdo = ConnectionFactory::sqlite($settings['database_path']);
$livres = new LivreRepository($pdo);
$emprunts = new EmpruntRepository($pdo);
$empruntService = new EmpruntService($livres, $emprunts);
$htmlView = new HtmlView($settings['views_path']);
$jsonView = new JsonView();
$csrf = new CsrfGuard($_SESSION['csrf']);

$livreController = new LivreController($livres, $emprunts, $htmlView, $csrf);
$empruntController = new EmpruntController($empruntService, $htmlView, $csrf);
$apiLivreController = new ApiLivreController($livres, $emprunts, $jsonView);
$apiEmpruntController = new ApiEmpruntController($empruntService, $emprunts, $jsonView);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

require dirname(__DIR__) . '/config/routes.php';

$errorMiddleware = $app->addErrorMiddleware($settings['display_errors'], true, true);
$errorMiddleware->setDefaultErrorHandler(new ErrorHandler(
    $app->getResponseFactory(),
    $htmlView,
    $jsonView,
    $settings['display_errors'],
));

$app->run();
