<?php

declare(strict_types=1);

namespace App\View;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/** Rend un template PHP dans un layout commun. */
final class HtmlView
{
    public function __construct(private readonly string $viewsPath)
    {
    }

    /** @param array<string,mixed> $data */
    public function render(
        ResponseInterface $response,
        string $template,
        array $data = [],
        int $status = 200,
    ): ResponseInterface {
        if (preg_match('/^[a-z0-9_\/-]+$/i', $template) !== 1) {
            throw new RuntimeException('Nom de vue invalide.');
        }

        $templatePath = $this->viewsPath . '/' . $template . '.php';
        $layoutPath = $this->viewsPath . '/layout.php';
        if (!is_file($templatePath) || !is_file($layoutPath)) {
            throw new RuntimeException("Vue introuvable : {$template}.");
        }

        // L'échappement appartient à la représentation HTML, au dernier moment.
        $e = static fn (mixed $value): string => htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        extract($data, EXTR_SKIP);
        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        $pageTitle = isset($data['pageTitle']) ? (string) $data['pageTitle'] : 'Bibliothèque MVC';
        ob_start();
        require $layoutPath;
        $html = (string) ob_get_clean();

        $response->getBody()->write($html);

        return $response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withStatus($status);
    }
}
