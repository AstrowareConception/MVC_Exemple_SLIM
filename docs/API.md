# Guide de l'API JSON

L'API utilise `Content-Type: application/json`. Les propriétés inconnues sont ignorées lors d'un `PATCH`; les propriétés absentes conservent leur valeur courante.

## Livre

```json
{
  "id": 1,
  "titre": "Dune",
  "auteur": "Frank Herbert",
  "isbn": "9782266320481",
  "annee_publication": 1965,
  "disponible": true
}
```

Champs d'entrée :

| Champ | Type | Règle |
|---|---|---|
| `titre` | chaîne | obligatoire, 1 à 120 caractères |
| `auteur` | chaîne | obligatoire, 1 à 120 caractères |
| `isbn` | chaîne ou `null` | 10 ou 13 caractères, espaces et tirets tolérés |
| `annee_publication` | entier ou `null` | de 1450 à l'année courante + 2 |

## Emprunter

```http
POST /api/livres/1/emprunts
Content-Type: application/json

{
  "emprunteur_nom": "Ada Lovelace",
  "emprunteur_email": "ada@example.test"
}
```

L'échéance est calculée à J+21. Un second emprunt actif sur le même livre produit un conflit `409`.
La réponse `201` fournit dans `Location` l'URL `GET /api/emprunts/{id}` permettant de relire l'emprunt.

## Retourner

```http
PATCH /api/emprunts/1/retour
```

Le retour est daté du jour. Rendre une seconde fois le même emprunt produit un conflit `409`.

## Erreurs

Toutes les erreurs explicitement traitées respectent le contrat suivant :

```json
{
  "erreur": {
    "code": "LIVRE_INDISPONIBLE",
    "message": "Ce livre est déjà emprunté."
  }
}
```

| Statut | Signification habituelle |
|---:|---|
| 400 | le corps n'est pas un objet JSON exploitable |
| 404 | le livre ou l'emprunt n'existe pas |
| 409 | l'état courant interdit l'action |
| 422 | une valeur ne respecte pas les invariants |
| 500 | erreur imprévue, détails masqués en production |

Le contrat complet, exploitable par Swagger Editor ou un générateur de client, est décrit dans `openapi.yaml`.
