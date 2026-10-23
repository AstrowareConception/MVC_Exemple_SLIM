<?php

declare(strict_types=1);

namespace App\Http;

use App\View\HtmlView;
use App\View\JsonView;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;

/** Transforme les erreurs non prévues sans divulguer de détails en production. */
final readonly class ErrorHandler
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private HtmlView $html,
        private JsonView $json,
        private bool $displayDetails,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $status = $exception instanceof HttpException ? $exception->getCode() : 500;
        $status = $status >= 400 && $status <= 599 ? $status : 500;
        $message = $status === 404 ? 'La ressource demandée est introuvable.' : 'Une erreur interne est survenue.';

        if ($logErrors) {
            error_log(sprintf('[%s] %s', $exception::class, $exception->getMessage()));
        }
        if ($this->displayDetails && $displayErrorDetails) {
            $message .= ' Détail local : ' . $exception->getMessage();
        }

        $response = $this->responseFactory->createResponse($status);
        if (str_starts_with($request->getUri()->getPath(), '/api/')) {
            return $this->json->error($response, $status === 404 ? 'INTROUVABLE' : 'ERREUR_INTERNE', $message, $status);
        }

        return $this->html->render($response, 'error', [
            'pageTitle' => 'Erreur',
            'message' => $message,
        ], $status);
    }
}
