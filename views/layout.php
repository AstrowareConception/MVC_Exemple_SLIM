<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var callable $e */
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($pageTitle) ?> · Bibliothèque MVC</title>
    <meta name="description" content="Application pédagogique MVC avec Slim 4, PDO et SQLite.">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/livres" aria-label="Retour au catalogue">
            <span class="brand-mark" aria-hidden="true">B</span>
            <span>
                <strong>Bibliothèque MVC</strong>
                <small>Slim 4 · SQLite · HTML + JSON</small>
            </span>
        </a>
        <nav aria-label="Navigation principale">
            <a href="/livres">Catalogue</a>
            <a class="button button-small" href="/livres/nouveau">Ajouter un livre</a>
            <a href="/api/livres">API JSON</a>
        </nav>
    </div>
</header>

<main class="container main-content">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">
        Exemple pédagogique BTS SIO SLAM — les vues n'exécutent aucune requête SQL.
    </div>
</footer>
</body>
</html>
