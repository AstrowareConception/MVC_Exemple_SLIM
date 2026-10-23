<?php

declare(strict_types=1);

namespace App\View;

use Psr\Http\Message\ResponseInterface;

/** Centralise l'encodage et le format des réponses de l'API. */
final class JsonView
{
    /** @param array<string,string> $headers */
    public function render(
        ResponseInterface $response,
        mixed $data,
        int $status = 200,
        array $headers = [],
    ): ResponseInterface {
        $response = $response->withStatus($status);
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        if ($status === 204) {
            return $response;
        }

        $response->getBody()->write((string) json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    /** @param array<string,mixed> $details */
    public function error(
        ResponseInterface $response,
        string $code,
        string $message,
        int $status,
        array $details = [],
    ): ResponseInterface {
        return $this->render($response, [
            'erreur' => [
                'code' => $code,
                'message' => $message,
                'details' => (object) $details,
            ],
        ], $status);
    }
}
