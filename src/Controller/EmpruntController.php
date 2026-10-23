<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\CsrfGuard;
use App\Http\Input;
use App\Model\Exception\EmpruntDejaRetourneException;
use App\Model\Exception\EmpruntIntrouvableException;
use App\Model\Exception\LivreIndisponibleException;
use App\Model\Exception\LivreIntrouvableException;
use App\Service\EmpruntService;
use App\View\HtmlView;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class EmpruntController
{
    public function __construct(
        private EmpruntService $service,
        private HtmlView $view,
        private CsrfGuard $csrf,
    ) {
    }

    public function emprunter(Request $request, Response $response, array $args): Response
    {
        $data = $this->body($request);
        if (!$this->csrf->isValid($data['csrf'] ?? null)) {
            return $this->erreur($response, 'Requête refusée', 'Le jeton CSRF est absent ou invalide.', 403);
        }

        $livreId = Input::positiveId($args['id'] ?? null);
        if ($livreId === null) {
            return $this->erreur($response, 'Livre introuvable', 'Identifiant de livre invalide.', 404);
        }

        try {
            $this->service->emprunter(
                $livreId,
                Input::string($data, 'emprunteur_nom'),
                Input::string($data, 'emprunteur_email'),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->erreur($response, 'Données invalides', $exception->getMessage(), 422);
        } catch (LivreIntrouvableException $exception) {
            return $this->erreur($response, 'Livre introuvable', $exception->getMessage(), 404);
        } catch (LivreIndisponibleException $exception) {
            return $this->erreur($response, 'Livre indisponible', $exception->getMessage(), 409);
        }

        return $response->withHeader('Location', '/livres/' . $livreId)->withStatus(303);
    }

    public function retourner(Request $request, Response $response, array $args): Response
    {
        $data = $this->body($request);
        if (!$this->csrf->isValid($data['csrf'] ?? null)) {
            return $this->erreur($response, 'Requête refusée', 'Le jeton CSRF est absent ou invalide.', 403);
        }

        $empruntId = Input::positiveId($args['id'] ?? null);
        if ($empruntId === null) {
            return $this->erreur($response, 'Emprunt introuvable', "L'identifiant est invalide.", 404);
        }

        try {
            $emprunt = $this->service->retourner($empruntId);
        } catch (EmpruntIntrouvableException $exception) {
            return $this->erreur($response, 'Emprunt introuvable', $exception->getMessage(), 404);
        } catch (EmpruntDejaRetourneException $exception) {
            return $this->erreur($response, 'Retour déjà enregistré', $exception->getMessage(), 409);
        }

        return $response->withHeader('Location', '/livres/' . $emprunt->livreId)->withStatus(303);
    }

    /** @return array<string,mixed> */
    private function body(Request $request): array
    {
        $body = $request->getParsedBody();

        return is_array($body) ? $body : [];
    }

    private function erreur(Response $response, string $title, string $message, int $status): Response
    {
        return $this->view->render($response, 'error', [
            'pageTitle' => $title,
            'message' => $message,
        ], $status);
    }
}
