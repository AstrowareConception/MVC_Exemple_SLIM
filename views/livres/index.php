<?php
/** @var list<\App\Model\Livre> $livres */
/** @var string $recherche */
/** @var callable $e */
?>
<section class="hero">
    <div>
        <p class="eyebrow">Catalogue</p>
        <h1>Une bibliothèque, deux représentations</h1>
        <p class="lead">Cette page HTML et l'API JSON utilisent exactement les mêmes modèles et repositories.</p>
    </div>
    <div class="hero-stat">
        <strong><?= count($livres) ?></strong>
        <span>livre<?= count($livres) > 1 ? 's' : '' ?> affiché<?= count($livres) > 1 ? 's' : '' ?></span>
    </div>
</section>

<form class="search-panel" method="get" action="/livres">
    <label for="q">Rechercher par titre ou auteur</label>
    <div class="search-row">
        <input id="q" name="q" value="<?= $e($recherche) ?>" placeholder="Ex. Dune ou Mary Shelley">
        <button class="button" type="submit">Rechercher</button>
        <?php if ($recherche !== ''): ?>
            <a class="button button-secondary" href="/livres">Effacer</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($livres === []): ?>
    <section class="empty-state">
        <h2>Aucun livre trouvé</h2>
        <p>Essayez une recherche différente ou ajoutez un ouvrage au catalogue.</p>
    </section>
<?php else: ?>
    <section class="book-grid" aria-label="Livres du catalogue">
        <?php foreach ($livres as $livre): ?>
            <article class="book-card">
                <div class="book-spine" aria-hidden="true"></div>
                <div class="book-card-content">
                    <span class="badge <?= $livre->disponible ? 'badge-success' : 'badge-warning' ?>">
                        <?= $livre->disponible ? 'Disponible' : 'Emprunté' ?>
                    </span>
                    <h2><a href="/livres/<?= $livre->id ?>"><?= $e($livre->titre) ?></a></h2>
                    <p class="author"><?= $e($livre->auteur) ?></p>
                    <p class="metadata">
                        <?= $livre->anneePublication === null ? 'Année inconnue' : $livre->anneePublication ?>
                        <?php if ($livre->isbn !== null): ?> · ISBN <?= $e($livre->isbn) ?><?php endif; ?>
                    </p>
                    <a class="text-link" href="/livres/<?= $livre->id ?>">Consulter la fiche →</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
