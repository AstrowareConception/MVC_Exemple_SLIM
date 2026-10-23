INSERT INTO livres (titre, auteur, isbn, annee_publication) VALUES
    ('Dune', 'Frank Herbert', '9782266320481', 1965),
    ('Fondation', 'Isaac Asimov', '9782070360536', 1951),
    ('Frankenstein', 'Mary Shelley', '9780141439471', 1818),
    ('Le Voyage de Chihiro', 'Hayao Miyazaki', NULL, 2001);

INSERT INTO emprunts (
    livre_id,
    emprunteur_nom,
    emprunteur_email,
    emprunte_le,
    echeance_le,
    retourne_le
) VALUES (
    2,
    'Ada Lovelace',
    'ada@example.test',
    date('now', '-5 days'),
    date('now', '+16 days'),
    NULL
);
