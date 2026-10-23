<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\CsrfGuard;
use App\Http\Input;
use App\Model\Exception\LivreAvecHistoriqueException;
use App\Model\Livre;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use App\View\HtmlView;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/** Contrôleur HTTP de l'interface HTML consacrée aux livres. */
final readonly class LivreController
{
    public function __construct(
        private LivreRepository $livres,
        private EmpruntRepository $emprunts,
        private HtmlView $view,
        private CsrfGuard $csrf,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $query = $request->getQueryParams();
        $recherche = Input::string($query, 'q');

        return $this->view->render($response, 'livres/index', [
            'pageTitle' => 'Catalogue',
            'livres' => $this->livres->tous($recherche),
            'recherche' => $recherche,
        ]);
    }

    public function voir(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        $livre = $id === null ? null : $this->livres->trouver($id);
        if ($livre === null) {
            return $this->introuvable($response);
        }

        return $this->view->render($response, 'livres/detail', [
            'pageTitle' => $livre->titre,
            'livre' => $livre,
            'empruntActif' => $this->emprunts->actifPourLivre($id),
            'historique' => $this->emprunts->historiquePourLivre($id),
            'csrf' => $this->csrf->token(),
        ]);
    }

    public function formulaireCreation(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'livres/formulaire', [
            'pageTitle' => 'Ajouter un livre',
            'action' => '/livres',
            'bouton' => 'Ajouter le livre',
            'valeurs' => $this->valeursVides(),
            'erreur' => null,
            'csrf' => $this->csrf->token(),
        ]);
    }

    public function creer(Request $request, Response $response): Response
    {
        $data = $this->body($request);
        if (!$this->csrf->isValid($data['csrf'] ?? null)) {
            return $this->interdit($response);
        }

        try {
            $livre = $this->construireLivre(null, $data);
            $id = $this->livres->creer($livre);
        } catch (InvalidArgumentException $exception) {
            return $this->view->render($response, 'livres/formulaire', [
                'pageTitle' => 'Ajouter un livre',
                'action' => '/livres',
                'bouton' => 'Ajouter le livre',
                'valeurs' => $this->valeursDepuis($data),
                'erreur' => $exception->getMessage(),
                'csrf' => $this->csrf->token(),
            ], 422);
        }

        return $response
            ->withHeader('Location', '/livres/' . $id)
            ->withStatus(303);
    }

    public function formulaireModification(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        $livre = $id === null ? null : $this->livres->trouver($id);
        if ($livre === null) {
            return $this->introuvable($response);
        }

        return $this->view->render($response, 'livres/formulaire', [
            'pageTitle' => 'Modifier ' . $livre->titre,
            'action' => '/livres/' . $id . '/modifier',
            'bouton' => 'Enregistrer les modifications',
            'valeurs' => $livre->toArray(),
            'erreur' => null,
            'csrf' => $this->csrf->token(),
        ]);
    }

    public function modifier(Request $request, Response $response, array $args): Response
    {
        $id = Input::positiveId($args['id'] ?? null);
        if ($id === null || $this->livres->trouver($id) === null) {
            return $this->introuvable($response);
        }

        $data = $this->body($request);
        if (!$this->csrf->isValid($data['csrf'] ?? null)) {
            return $this->interdit($response);
        }

        try {
            $livre = $this->construireLivre($id, $data);
            $this->livres->modifier($livre);
        } catch (InvalidArgumentException $exception) {
            return $this->view->render($response, 'livres/formulaire', [
                'pageTitle' => 'Modifier le livre',
                'action' => '/livres/' . $id . '/modifier',
                'bouton' => 'Enregistrer les modifications',
                'valeurs' => $this->valeursDepuis($data),
                'erreur' => $exception->getMessage(),
                'csrf' => $this->csrf->token(),
            ], 422);
        }

        return $response->withHeader('Location', '/livres/' . $id)->withStatus(303);
    }

    public function supprimer(Request $request, Response $response, array $args): Response
    {
        $data = $this->body($request);
        if (!$this->csrf->isValid($data['csrf'] ?? null)) {
            return $this->interdit($response);
        }

        $id = Input::positiveId($args['id'] ?? null);
        if ($id === null || $this->livres->trouver($id) === null) {
            return $this->introuvable($response);
        }

        try {
            $this->livres->supprimer($id);
        } catch (LivreAvecHistoriqueException $exception) {
            return $this->view->render($response, 'error', [
                'pageTitle' => 'Suppression impossible',
                'message' => $exception->getMessage(),
            ], 409);
        }

        return $response->withHeader('Location', '/livres')->withStatus(303);
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

    /** @return array<string,mixed> */
    private function body(Request $request): array
    {
        $body = $request->getParsedBody();

        return is_array($body) ? $body : [];
    }

    /** @return array<string,string|null> */
    private function valeursVides(): array
    {
        return ['titre' => '', 'auteur' => '', 'isbn' => '', 'annee_publication' => ''];
    }

    /** @param array<string,mixed> $data @return array<string,string> */
    private function valeursDepuis(array $data): array
    {
        return [
            'titre' => Input::string($data, 'titre'),
            'auteur' => Input::string($data, 'auteur'),
            'isbn' => Input::string($data, 'isbn'),
            'annee_publication' => Input::string($data, 'annee_publication'),
        ];
    }

    private function introuvable(Response $response): Response
    {
        return $this->view->render($response, 'error', [
            'pageTitle' => 'Livre introuvable',
            'message' => 'Aucun livre ne correspond à cet identifiant.',
        ], 404);
    }

    private function interdit(Response $response): Response
    {
        return $this->view->render($response, 'error', [
            'pageTitle' => 'Requête refusée',
            'message' => 'Le jeton CSRF est absent ou invalide.',
        ], 403);
    }
}
