<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Input;
use App\Model\Exception\EmpruntDejaRetourneException;
use App\Model\Exception\EmpruntIntrouvableException;
use App\Model\Exception\LivreIndisponibleException;
use App\Model\Exception\LivreIntrouvableException;
use App\Service\EmpruntService;
use App\View\JsonView;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class ApiEmpruntController
{
    public function __construct(
        private EmpruntService $service,
        private JsonView $view,
    ) {
    }

    public function emprunter(Request $request, Response $response, array $args): Response
    {
        $livreId = Input::positiveId($args['id'] ?? null);
        if ($livreId === null) {
            return $this->view->error($response, 'LIVRE_INTROUVABLE', 'Livre introuvable.', 404);
        }

        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->view->error($response, 'CORPS_INVALIDE', 'Un objet JSON est attendu.', 400);
        }

        try {
            $emprunt = $this->service->emprunter(
                $livreId,
                Input::string($data, 'emprunteur_nom'),
                Input::string($data, 'emprunteur_email'),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->view->error($response, 'DONNEES_INVALIDES', $exception->getMessage(), 422);
        } catch (LivreIntrouvableException $exception) {
            return $this->view->error($response, 'LIVRE_INTROUVABLE', $exception->getMessage(), 404);
        } catch (LivreIndisponibleException $exception) {
            return $this->view->error($response, 'LIVRE_INDISPONIBLE', $exception->getMessage(), 409);
        }

        return $this->view->render(
            $response,
            ['data' => $emprunt->toArray()],
            201,
            ['Location' => '/api/emprunts/' . $emprunt->id],
        );
    }

    public function retourner(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        if ($id === null) {
            return $this->view->error($response, 'EMPRUNT_INTROUVABLE', 'Emprunt introuvable.', 404);
        }

        try {
            $emprunt = $this->service->retourner($id);
        } catch (EmpruntIntrouvableException $exception) {
            return $this->view->error($response, 'EMPRUNT_INTROUVABLE', $exception->getMessage(), 404);
        } catch (EmpruntDejaRetourneException $exception) {
            return $this->view->error($response, 'EMPRUNT_DEJA_RETOURNE', $exception->getMessage(), 409);
        }

        return $this->view->render($response, ['data' => $emprunt->toArray()]);
    }
}
