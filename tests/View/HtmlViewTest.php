<?php

declare(strict_types=1);

namespace Tests\View;

use App\View\HtmlView;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;

final class HtmlViewTest extends TestCase
{
    public function testEchappeLeContenuDynamique(): void
    {
        $view = new HtmlView(dirname(__DIR__, 2) . '/views');
        $response = $view->render(
            (new ResponseFactory())->createResponse(),
            'error',
            ['pageTitle' => 'Erreur', 'message' => '<script>alert(1)</script>'],
            422,
        );
        $html = (string) $response->getBody();

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
    }
}
