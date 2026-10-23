PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS livres (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre TEXT NOT NULL CHECK (length(trim(titre)) BETWEEN 1 AND 120),
    auteur TEXT NOT NULL CHECK (length(trim(auteur)) BETWEEN 1 AND 120),
    isbn TEXT NULL UNIQUE,
    annee_publication INTEGER NULL CHECK (
        annee_publication IS NULL
        OR annee_publication BETWEEN 1450 AND 2200
    ),
    cree_le TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS emprunts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    livre_id INTEGER NOT NULL,
    emprunteur_nom TEXT NOT NULL CHECK (length(trim(emprunteur_nom)) BETWEEN 2 AND 120),
    emprunteur_email TEXT NOT NULL,
    emprunte_le TEXT NOT NULL,
    echeance_le TEXT NOT NULL,
    retourne_le TEXT NULL,
    FOREIGN KEY (livre_id) REFERENCES livres(id) ON DELETE RESTRICT
);

-- Un même livre ne peut posséder qu'un seul emprunt non retourné.
-- Cette contrainte complète la règle métier et protège aussi la concurrence.
CREATE UNIQUE INDEX IF NOT EXISTS un_emprunt_actif_par_livre
    ON emprunts(livre_id)
    WHERE retourne_le IS NULL;

CREATE INDEX IF NOT EXISTS recherche_livres_titre
    ON livres(titre);

CREATE INDEX IF NOT EXISTS emprunts_par_livre
    ON emprunts(livre_id);
