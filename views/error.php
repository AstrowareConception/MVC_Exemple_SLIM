<?php
/** @var string $message */
/** @var callable $e */
?>
<section class="error-card">
    <p class="eyebrow">La requête n'a pas abouti</p>
    <h1><?= $e($pageTitle) ?></h1>
    <p class="lead"><?= $e($message) ?></p>
    <p><a class="button" href="/livres">Revenir au catalogue</a></p>
</section>
