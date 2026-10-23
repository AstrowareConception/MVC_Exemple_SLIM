<?php
/** @var \App\Model\Livre $livre */
/** @var ?\App\Model\Emprunt $empruntActif */
/** @var list<\App\Model\Emprunt> $historique */
/** @var string $csrf */
/** @var callable $e */
?>
<p><a class="back-link" href="/livres">← Retour au catalogue</a></p>

<section class="detail-card">
    <div class="detail-main">
        <p class="eyebrow">Livre nº <?= $livre->id ?></p>
        <h1><?= $e($livre->titre) ?></h1>
        <p class="detail-author">par <?= $e($livre->auteur) ?></p>
        <dl class="facts">
            <div><dt>Publication</dt><dd><?= $livre->anneePublication ?? 'Non renseignée' ?></dd></div>
            <div><dt>ISBN</dt><dd><?= $livre->isbn === null ? 'Non renseigné' : $e($livre->isbn) ?></dd></div>
            <div><dt>État</dt><dd><span class="badge <?= $livre->disponible ? 'badge-success' : 'badge-warning' ?>"><?= $livre->disponible ? 'Disponible' : 'Emprunté' ?></span></dd></div>
        </dl>
        <div class="actions">
            <a class="button button-secondary" href="/livres/<?= $livre->id ?>/modifier">Modifier</a>
            <a class="button button-secondary" href="/api/livres/<?= $livre->id ?>">Voir le JSON</a>
            <form method="post" action="/livres/<?= $livre->id ?>/supprimer" onsubmit="return confirm('Supprimer ce livre ?');">
                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                <button class="button button-danger" type="submit">Supprimer</button>
            </form>
        </div>
    </div>

    <aside class="loan-panel">
        <?php if ($empruntActif !== null): ?>
            <p class="eyebrow">Emprunt en cours</p>
            <h2><?= $e($empruntActif->emprunteurNom) ?></h2>
            <p><?= $e($empruntActif->emprunteurEmail) ?></p>
            <p>Échéance : <strong><?= $e($empruntActif->echeanceLe->format('d/m/Y')) ?></strong></p>
            <form method="post" action="/emprunts/<?= $empruntActif->id ?>/retour">
                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                <button class="button" type="submit">Enregistrer le retour</button>
            </form>
        <?php else: ?>
            <p class="eyebrow">Nouvel emprunt</p>
            <h2>Prêter ce livre</h2>
            <form method="post" action="/livres/<?= $livre->id ?>/emprunter" class="stacked-form">
                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                <label for="emprunteur_nom">Nom</label>
                <input id="emprunteur_nom" name="emprunteur_nom" required minlength="2" maxlength="120">
                <label for="emprunteur_email">Adresse électronique</label>
                <input id="emprunteur_email" name="emprunteur_email" type="email" required>
                <button class="button" type="submit">Confirmer l'emprunt</button>
            </form>
        <?php endif; ?>
    </aside>
</section>

<section class="history-section">
    <h2>Historique des emprunts</h2>
    <?php if ($historique === []): ?>
        <p class="muted">Ce livre n'a encore jamais été emprunté.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Emprunteur</th><th>Emprunt</th><th>Échéance</th><th>Retour</th></tr></thead>
                <tbody>
                <?php foreach ($historique as $emprunt): ?>
                    <tr>
                        <td><?= $e($emprunt->emprunteurNom) ?></td>
                        <td><?= $e($emprunt->emprunteLe->format('d/m/Y')) ?></td>
                        <td><?= $e($emprunt->echeanceLe->format('d/m/Y')) ?></td>
                        <td><?= $emprunt->retourneLe?->format('d/m/Y') ?? 'En cours' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
