# Rapport d'analyse - ISMO-SkillSwap

## Ce qui manque dans le projet (trié par priorité)

---

## 1. CRITIQUE - Application non fonctionnelle sans ça

| # | Problème | Détail |
|---|----------|--------|
| 1 | **Aucune session utilisateur** | Aucun `session_start()` nulle part. Après login, l'utilisateur est redirigé mais son identité n'est jamais stockée. Impossible de savoir qui est connecté. |
| 2 | **Aucune vérification d'authentification** | Toutes les pages sont accessibles sans être connecté. N'importe qui peut taper l'URL directement. |
| 3 | **Données utilisateur en dur (hardcoded)** | Tous les profils affichent "John Student", "Mohammed Admin", etc. en dur au lieu de charger depuis la BDD. Même les initiales sont codées en dur. |
| 4 | **Login ne crée pas de session + `extract()` dangereux** | Utilisation de `extract($_POST)` qui est une faille de sécurité. Aucune session créée après login. |
| 5 | **Inscription sans validation de compte** | Le champ `statut` / `valide` n'existe pas dans `utilisateurs`. Aucun workflow de validation de compte. |

---

## 2. HAUTE PRIORITÉ - Fonctionnalités CC3 manquantes

| # | Objectif CC3 | Statut |
|---|-------------|--------|
| 6 | **OF2 - Proposer son aide (proposition de stagiaire → apart du mentor)** | ❌ Totalement absent. La table `propositions_aide` existe mais est vide et aucune UI ne permet de proposer son aide sur une demande. |
| 7 | **OF3 - Notation des mentors** | ❌ Absent. La table `aide_effectuee` a `note_mentor` mais aucune interface de notation. |
| 8 | **OF6 - Passeport PDF exportable** | ❌ Absent. Le bouton "Download PDF" est juste décoratif. |
| 9 | **OF8 - Recherche/filtrage fonctionnel** | ❌ Les barres de recherche et les filtres sont purement décoratifs (HTML statique). Aucune logique de recherche ou filtrage. |
| 10 | **Rôle Mentor entièrement statique** | ❌ Le dossier `mentor/` ne contient que des fichiers `.html` statiques. Aucun PHP, aucune connexion BDD. Un mentor ne peut RIEN faire. |

---

## 3. MOYENNE PRIORITÉ - Implémentations partielles / buggées

| # | Problème | Détail |
|---|----------|--------|
| 11 | **Marketplace - Données en dur** | Les demandes d'aide affichées sont codées en dur dans le HTML. La BDD a 3 demandes mais elles ne sont jamais chargées. |
| 12 | **Ajouter demande - Insert sans `id_user`** | `ajouter_demande.php` insère sans `id_user` (car pas de session). |
| 13 | **Modifier/Supprimer demande** | `modifier_demande.php` et `supprimer_demande.php` sont des fichiers vides (0 octets). |
| 14 | **Gestion des compétences vide** | `ajouter_competence.php`, `modifie_competence.php`, `supprimer_competence.php` sont tous vides. |
| 15 | **Validation compétences - UI seulement** | Les pages de validation affichent un tableau statique. Aucun bouton "Valider/Refuser" fonctionnel. |
| 16 | **Badges - Pas de lien BDD** | La table `badges` et `obtention_badges` sont vides (0 lignes). L'UI badge est statique. |
| 17 | **Tableau de bord - Stats en dur** | Tous les tableaux de bord (stagiaire, formateur, admin) ont des chiffres codés en dur. |
| 18 | **Admin - Pas de gestion des utilisateurs** | L'admin ne peut pas valider/suspendre les comptes. Aucune page de gestion des utilisateurs. |
| 19 | **Pas de gestion de photos** | La colonne `photo` existe dans `utilisateurs` mais aucun upload ni affichage de photo. |
| 20 | **Inscription ne redirige pas** | Après inscription, juste un message "inscription réussie" - pas de redirection vers login. |
| 21 | **Hash SHA256 (faible)** | Le mot de passe est hashé en SHA256 au lieu de `password_hash()`/`password_verify()`. |
| 22 | **`extract($_POST)` dangereux** | Utilisé dans login ET inscription - vulnérabilité connue. |

---

## 4. FAIBLE PRIORITÉ - Propreté / UX

| # | Problème | Détail |
|---|----------|--------|
| 23 | **Duplication du code sidebar** | Chaque page a sa propre copie du code HTML de la sidebar. Aucun `include()` pour factoriser. |
| 24 | **Titre de page incorrect** | `stagiaire/accueil/accueil.php` titre : "Accueil **Admin**" au lieu de "Accueil Stagiaire" |
| 25 | **Styles d'erreur en inline** | Les messages d'erreur utilisent `style='color:red'` en dur au lieu des classes CSS. |
| 26 | **Double submit JS + PHP** | `ajouter_demande.php` a un handler JS qui intercepte le submit ET le PHP gère aussi le POST. |
| 27 | **Fichiers CSS manquants** | `accueil.css`, `marketplace.css`, `profile.css`, `passport.css`, `classement.css`, `validations.css`, `badges.css`, `parametres.css` sont référencés mais n'existent pas dans les dossiers respectifs. |
| 28 | **Pas de responsive design avancé** | Les breakpoints sont limités. Pas de menu hamburger pour mobile. |

---

## Résumé des tables BDD

| Table | Lignes | Notes |
|------|--------|-------|
| `utilisateurs` | 3 | A du data test |
| `demandes_aide` | 3 | A du data test |
| `badges` | 0 | Vide |
| `competences` | 0 | Vide |
| `user_competences` | 0 | Vide |
| `propositions_aide` | 0 | Vide |
| `aide_effectuee` | 0 | Vide |
| `validation_competence` | 0 | Vide |
| `obtention_badges` | 0 | Vide |

---

**Conclusion :** Le projet a une bonne base UI/UX (CSS soigné, sidebar par rôle, design moderne) et une structure BDD correcte, mais **presque toute la logique métier (backend PHP) reste à implémenter**. Les données sont partout en dur, il n'y a aucune session, et les fonctionnalités clés (proposer son aide, noter, PDF, recherche, badges) ne sont que des coquilles vides.
