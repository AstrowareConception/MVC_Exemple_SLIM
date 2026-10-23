<?php

declare(strict_types=1);

use App\Controller\ApiEmpruntController;
use App\Controller\ApiLivreController;
use App\Controller\EmpruntController;
use App\Controller\LivreController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

/** @var App $app */
/** @var LivreController $livreController */
/** @var EmpruntController $empruntController */
/** @var ApiLivreController $apiLivreController */
/** @var ApiEmpruntController $apiEmpruntController */

$app->get('/', static function (Request $request, Response $response): Response {
    return $response->withHeader('Location', '/livres')->withStatus(303);
});

// Interface HTML : les GET consultent, les POST modifient puis redirigent.
$app->get('/livres', [$livreController, 'index']);
$app->get('/livres/nouveau', [$livreController, 'formulaireCreation']);
$app->post('/livres', [$livreController, 'creer']);
$app->get('/livres/{id:[0-9]+}', [$livreController, 'voir']);
$app->get('/livres/{id:[0-9]+}/modifier', [$livreController, 'formulaireModification']);
$app->post('/livres/{id:[0-9]+}/modifier', [$livreController, 'modifier']);
$app->post('/livres/{id:[0-9]+}/supprimer', [$livreController, 'supprimer']);
$app->post('/livres/{id:[0-9]+}/emprunter', [$empruntController, 'emprunter']);
$app->post('/emprunts/{id:[0-9]+}/retour', [$empruntController, 'retourner']);

// Interface JSON : les mêmes repositories et services, une autre représentation.
$app->get('/api/livres', [$apiLivreController, 'index']);
$app->post('/api/livres', [$apiLivreController, 'creer']);
$app->get('/api/livres/{id:[0-9]+}', [$apiLivreController, 'voir']);
$app->patch('/api/livres/{id:[0-9]+}', [$apiLivreController, 'modifier']);
$app->delete('/api/livres/{id:[0-9]+}', [$apiLivreController, 'supprimer']);
$app->post('/api/livres/{id:[0-9]+}/emprunts', [$apiEmpruntController, 'emprunter']);
$app->get('/api/emprunts/{id:[0-9]+}', [$apiEmpruntController, 'voir']);
$app->patch('/api/emprunts/{id:[0-9]+}/retour', [$apiEmpruntController, 'retourner']);
