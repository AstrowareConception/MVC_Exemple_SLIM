<?php
/** @var string $action */
/** @var string $bouton */
/** @var array<string,mixed> $valeurs */
/** @var ?string $erreur */
/** @var string $csrf */
/** @var callable $e */
?>
<p><a class="back-link" href="/livres">← Retour au catalogue</a></p>
<section class="form-card">
    <div>
        <p class="eyebrow">Gestion du catalogue</p>
        <h1><?= $e($pageTitle) ?></h1>
        <p class="lead">Les contrôles HTML aident l'utilisateur ; le modèle PHP reste l'autorité métier.</p>
    </div>

    <?php if ($erreur !== null): ?>
        <div class="alert alert-error" role="alert"><?= $e($erreur) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $e($action) ?>" class="stacked-form">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">

        <label for="titre">Titre *</label>
        <input id="titre" name="titre" required maxlength="120" value="<?= $e($valeurs['titre'] ?? '') ?>">

        <label for="auteur">Auteur ou créateur *</label>
        <input id="auteur" name="auteur" required maxlength="120" value="<?= $e($valeurs['auteur'] ?? '') ?>">

        <div class="form-columns">
            <div>
                <label for="isbn">ISBN, facultatif</label>
                <input id="isbn" name="isbn" maxlength="17" value="<?= $e($valeurs['isbn'] ?? '') ?>" placeholder="9782266320481">
            </div>
            <div>
                <label for="annee_publication">Année, facultative</label>
                <input id="annee_publication" name="annee_publication" type="number" min="1450" max="<?= (int) date('Y') + 2 ?>" value="<?= $e($valeurs['annee_publication'] ?? '') ?>">
            </div>
        </div>

        <button class="button" type="submit"><?= $e($bouton) ?></button>
    </form>
</section>
