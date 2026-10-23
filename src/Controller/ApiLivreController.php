<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Input;
use App\Model\Exception\LivreAvecHistoriqueException;
use App\Model\Livre;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use App\View\JsonView;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/** Contrôleur de représentation JSON : aucun SQL n'est dupliqué ici. */
final readonly class ApiLivreController
{
    public function __construct(
        private LivreRepository $livres,
        private EmpruntRepository $emprunts,
        private JsonView $view,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $query = $request->getQueryParams();
        $recherche = Input::string($query, 'q');
        $livres = array_map(
            static fn (Livre $livre): array => $livre->toArray(),
            $this->livres->tous($recherche),
        );

        return $this->view->render($response, [
            'data' => $livres,
            'meta' => ['nombre' => count($livres), 'recherche' => $recherche],
        ]);
    }

    public function voir(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        $livre = $id === null ? null : $this->livres->trouver($id);
        if ($livre === null) {
            return $this->view->error($response, 'LIVRE_INTROUVABLE', 'Livre introuvable.', 404);
        }

        $data = $livre->toArray();
        $data['emprunt_actif'] = $this->emprunts->actifPourLivre($id)?->toArray();

        return $this->view->render($response, ['data' => $data]);
    }

    public function creer(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->view->error($response, 'CORPS_INVALIDE', 'Un objet JSON est attendu.', 400);
        }

        try {
            $livre = $this->construireLivre(null, $data);
            $id = $this->livres->creer($livre);
            $created = $this->livres->trouver($id);
        } catch (InvalidArgumentException $exception) {
            return $this->view->error($response, 'DONNEES_INVALIDES', $exception->getMessage(), 422);
        }

        return $this->view->render(
            $response,
            ['data' => $created?->toArray()],
            201,
            ['Location' => '/api/livres/' . $id],
        );
    }

    public function modifier(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        $existing = $id === null ? null : $this->livres->trouver($id);
        if ($existing === null) {
            return $this->view->error($response, 'LIVRE_INTROUVABLE', 'Livre introuvable.', 404);
        }

        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->view->error($response, 'CORPS_INVALIDE', 'Un objet JSON est attendu.', 400);
        }

        $merged = array_merge($existing->toArray(), $data);
        try {
            $livre = $this->construireLivre($id, $merged);
            $this->livres->modifier($livre);
        } catch (InvalidArgumentException $exception) {
            return $this->view->error($response, 'DONNEES_INVALIDES', $exception->getMessage(), 422);
        }

        return $this->view->render($response, ['data' => $this->livres->trouver($id)?->toArray()]);
    }

    public function supprimer(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        if ($id === null || $this->livres->trouver($id) === null) {
            return $this->view->error($response, 'LIVRE_INTROUVABLE', 'Livre introuvable.', 404);
        }

        try {
            $this->livres->supprimer($id);
        } catch (LivreAvecHistoriqueException $exception) {
            return $this->view->error($response, 'SUPPRESSION_IMPOSSIBLE', $exception->getMessage(), 409);
        }

        return $this->view->render($response, null, 204);
    }

    /** @param array<string,mixed> $data */
    private function construireLivre(?int $id, array $data): Livre
    {
        return new Livre(
            $id,
            Input::string($data, 'titre'),
            Input::string($data, 'auteur'),
            Input::string($data, 'isbn'),
            Input::nullableInt($data, 'annee_publication', "L'année de publication"),
        );
    }
}
