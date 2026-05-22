# Documentation API — Gestion Pédagogique

> Documentation complète de l'API REST. Base URL : `http://localhost:8000/api/v1`
>
> Toutes les routes protégées nécessitent le header :
> ```
> Authorization: Bearer <token>
> ```

---

## Rôles

| Rôle | Description |
|------|-------------|
| `MANAGER` | Responsable pédagogique — accès complet |
| `CM` | Attaché — valide les sessions, gère les absences |
| `COACH` | Professeur — consulte ses cours et sessions |
| `APPRENANT` | Étudiant — émargement, absences, justifications |

---

## Authentification

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `POST` | `/login` | Public | Connexion — retourne un Bearer token |
| `GET` | `/logout` | Authentifié | Déconnexion |

---

## Utilisateurs

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `GET` | `/users` | MANAGER, CM | Liste · filtre `?role=` |
| `GET` | `/users/{id}` | MANAGER, CM | Détail |
| `POST` | `/users` | MANAGER | Créer |
| `PATCH` | `/users/{id}` | MANAGER | Modifier |
| `DELETE` | `/users/{id}` | MANAGER | Supprimer |

Champs spécifiques aux professeurs (COACH) : `specialite`, `grade` (optionnels).

---

## Années scolaires

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `GET` | `/annees-scolaires` | Authentifié | Liste |
| `POST` | `/annees-scolaires` | MANAGER | Créer |
| `PATCH` | `/annees-scolaires/{id}` | MANAGER | Modifier |
| `DELETE` | `/annees-scolaires/{id}` | MANAGER | Supprimer |
| `GET` | `/annees-scolaires/{id}/classes` | Authentifié | Classes planifiées |
| `POST` | `/annees-scolaires/{id}/classes` | MANAGER | Planifier une classe |
| `DELETE` | `/annees-scolaires/{id}/classes/{classeId}` | MANAGER | Retirer une classe |
| `GET` | `/annees-scolaires/{id}/semestres` | Authentifié | Semestres de l'année |

---

## Ressources de base *(CRUD — MANAGER uniquement)*

`/classes` · `/salles` · `/semestres` · `/modules`

---

## Cours

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `GET` | `/cours` | Authentifié | Liste |
| `GET` | `/cours/{id}` | Authentifié | Détail (heures effectuées / restantes) |
| `POST` | `/cours` | MANAGER | Créer avec classes associées |
| `PATCH` | `/cours/{id}` | MANAGER | Modifier |
| `DELETE` | `/cours/{id}` | MANAGER | Supprimer |
| `GET` | `/cours/{id}/etudiants` | MANAGER | Étudiants inscrits |
| `GET` | `/cours/professeur/{id}` | COACH, MANAGER, CM | Cours d'un prof · `?periode=jour\|semaine&date=` |
| `GET` | `/cours/etudiant/{id}` | APPRENANT, MANAGER, CM | Cours d'un étudiant · `?periode=&date=&module_id=` |

---

## Sessions de cours

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `GET` | `/cours/{id}/sessions` | Authentifié | Sessions d'un cours |
| `GET` | `/sessions/{id}` | Authentifié | Détail |
| `POST` | `/sessions` | MANAGER | Planifier (vérifie quota + disponibilités) |
| `PATCH` | `/sessions/{id}` | MANAGER | Modifier |
| `PATCH` | `/sessions/{id}/annuler` | MANAGER, COACH | Annuler |
| `GET` | `/sessions/professeur/{id}` | COACH, MANAGER, CM | Sessions d'un prof · `?periode=jour\|semaine&date=` |
| `GET` | `/sessions/etudiant/{id}` | APPRENANT, MANAGER, CM | Sessions d'un étudiant · `?periode=&date=&module_id=` |
| `GET` | `/sessions/professeur/{id}/heures-mois` | COACH, CM, MANAGER | Heures du mois · `?mois=&annee=&module_id=` |
| `GET` | `/sessions/professeur/{id}/bilan` | CM, MANAGER | Bilan mensuel avec quota · `?mois=&annee=&module_id=` |

---

## Inscriptions

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `GET` | `/inscriptions` | MANAGER, CM | Liste |
| `POST` | `/inscriptions/importer` | MANAGER | Import Excel (`multipart/form-data`, champ `fichier`) |
| `DELETE` | `/inscriptions/{id}` | MANAGER | Supprimer |

Colonnes attendues dans le fichier Excel : `nom` · `prenom` · `email` · `telephone` · `login`

---

## Émargement

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `POST` | `/sessions/{id}/signer` | APPRENANT | Signer sa présence *(disponible 30 min après le début)* |
| `GET` | `/sessions/{id}/emargements` | CM, MANAGER | Liste des émargements |
| `POST` | `/sessions/{id}/valider` | CM | Valider → génère les absences automatiquement |
| `DELETE` | `/sessions/{id}/valider` | CM | Invalider *(supprime les absences générées)* |

---

## Absences

| Méthode | Endpoint | Accès | Description |
|---------|----------|-------|-------------|
| `GET` | `/absences/etudiant/{id}` | APPRENANT, MANAGER, CM | Absences · `?semestre_id=` |
| `PATCH` | `/absences/{id}/justifier` | APPRENANT | Soumettre une justification |
| `PATCH` | `/absences/{id}/traiter` | CM | Accepter ou refuser une justification |
| `GET` | `/absences/professeur/{id}` | CM, MANAGER | Absences dans les cours d'un prof · `?mois=&annee=&module_id=` |

---

## Logique métier

### Planification d'une session
- Vérifie que le quota horaire global du cours n'est pas dépassé
- Vérifie qu'aucune autre session n'occupe le professeur au même créneau
- Vérifie que la salle n'est pas déjà réservée au même créneau

### Émargement
- La liste s'ouvre **30 minutes après le début** de la session
- À la validation CM, les absents sont automatiquement enregistrés

### Notifications (par semestre)
- **≥ 10h** d'absences non justifiées → avertissement (mail + base)
- **≥ 20h** d'absences non justifiées → convocation (mail + base)
