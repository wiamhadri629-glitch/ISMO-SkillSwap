**Office de la formation professionnelle et de la promotion de travail** 

_**Formatrice :**_ LAFHAL Joairia 

_**Barème : /20**_ 

_**Filière : DEV 101**_ 

_**M107 : Sites Web dynamiques Contrôle Continu 3 : PROJET**_ 

## **Cahier des Charges Fonctionnel du Projet** 

## **ISMO-SkillSwap – Plateforme d’Entraide et de Valorisation des Compétences** 

## **1. Problématique à résoudre** 

- À l’ISMO Tétouan, les stagiaires rencontrent plusieurs difficultés : 

   - **Blocages techniques ponctuels** 

   - Bug en développement 

   - Problème de configuration (serveur, base de données…) 

   - Concept mal compris (POO, MVC, API, UML…) 

   - Difficulté dans un TP ou projet 

Ces blocages ralentissent l’apprentissage et créent une dépendance excessive au formateur. 

- **Manque de visibilité des compétences internes** 

- Impossible d’identifier rapidement « qui maîtrise React ? » 

- Qui est à l’aise en MERISE ? 

- Qui peut aider en SQL ou en déploiement ? 

- **Manque de valorisation des compétences pratiques** 

Les compétences acquises : 

- En auto-formation 

- Lors de projets personnels 

- Lors de hackathons ou stages 

ne sont pas reconnues officiellement. 

## **2. Introduction** 

## _Contexte du projet_ 

ISMO-SkillSwap transforme l’institut en **communauté apprenante intelligente** , où chaque stagiaire est à la fois : 

- apprenant 

- Mentor 

La plateforme devient une **place de marché interne des compétences** , favorisant : 

- L’entraide structurée 

- La reconnaissance des savoir-faire 

- La motivation par la valorisation 

_Objectif du projet_ 

Créer une application web permettant de : 

- Faciliter le dépannage technique en temps réel. 

- Identifier rapidement les compétences disponibles. 

- Permettre aux formateurs de valider les compétences. 

- Générer un **Passeport de Compétences dynamique** pour chaque stagiaire. 

- • Instaurer un système de réputation basé sur l’entraide. 

## _Objectifs fonctionnels_ 

|**Code**|**Objectif Fonctionnel**|
|---|---|
|OF1|Publier une demande d’aide technique|
|OF2|Permettre à un stagiaire de proposer son aide|
|OF3|Mettre en place un système de notation des mentors|
|OF4|Créer un profil de compétences détaillé|
|OF5|Permettre aux formateurs de valider les compétences|
|OF6|Générer un Passeport de Compétences exportable (PDF)|
|OF7|Mettre en place un système de badges et niveaux|
|OF8|Offrir un moteur de recherche par compétence|
|OF9|Tableau de bord statistique|



## _Acteurs du systemes_ 

|_Acteurs du systemes_||
|---|---|
|**Acteur**|**Rôle**|
|Stagiaire|Demander de l’aide, proposer son expertise|
|Mentor (stagiaire validé)|Répondre aux demandes|
|Formateur|Valider compétences et badges|
|Administrateur|Superviser la plateforme|



## **3. Fonctionnalités de base par acteur** 

_Module d’authentification et gestion des profils_ 

|**Fonctionnalité**|**Stagiaire**|**Stagiaire**|**Mentor**|**Formateur**|**Administrateur**|**Administrateur**|
|---|---|---|---|---|---|---|
|Inscription|✔||✔|✔|✔||
|Connexion|✔||✔|✔|✔||
|Modifierprofil|✔||✔|✔|✔||
|Ajouter compétences|✔||✔|✖|✖||
|Modifier compétences|✔||✔|✖|✖||
|Voirprofilspublics|✔||✔|✔|✔||
|Valider compte|✖||✖|✖|✔||
|Suspendre compte|✖||✖|✖|✔||
|_Module de Marketplace_|_d’Entraide_||||||
||||||||
|**Fonctionnalité**||**Stagiaire**||**Mentor**|**Formateur**|**Administrateur**|
|Publier demande d’aide||✔||✔|✖|✔|
|Modifier demande(si auteur)||✔||✔|✖|✔|
|Supprimer demande||✔(si<br>auteur)||✔(si<br>auteur)|✖|✔|
|Proposer son aide||✔||✔|✖|✖|



|Marquer comme résolu|✔(auteur)|✔(si<br>accepté)|✖|✔|
|---|---|---|---|---|
|Noter le mentor|✔|✔|✖|✖|
|Voir historique d’aides|✔|✔|✔|✔|
|Supprimer publication<br>inappropriée|✖|✖|✔|✔|



_Module gestion des compétences_ 

|Supprimer publication<br>inappropriée<br>✖<br>_Module gestion des compétences_||✖|✔|✔|
|---|---|---|---|---|
|**Fonctionnalité**|**Stagiaire**|**Mentor**|**Formateur**|**Administrateur**|
|Déclarer compétence|✔|✔|✖|✖|
|Choisir niveau estimé|✔|✔|✖|✖|
|Voir liste des compétences|✔|✔|✔|✔|
|Valider compétence|✖|✖|✔|✔|
|Refuser compétence|✖|✖|✔|✔|
|Modifier catalogue compétences|✖|✖|✖|✔|



## _Module passeport des compétences_ 

|**Fonctionnalité**|**Stagiaire**|**Mentor**|**Formateur**|**Administrateur**|
|---|---|---|---|---|
|Consulter sonpasseport|✔|✔|✔|✔|
|Voir compétences validées|✔|✔|✔|✔|
|Voir badges obtenus|✔|✔|✔|✔|
|Générer PDF|✔|✔|✔|✔|
|Voir classement filière|✔|✔|✔|✔|



_Module Gamification et badges_ 

|**Fonctionnalité**|**Stagiaire**|**Mentor**|**Formateur**|**Administrateur**|
|---|---|---|---|---|
|Gagner despoints|✔|✔|✖|✖|
|Voir son score|✔|✔|✔|✔|
|Attribuer badge officiel|✖|✖|✔|✔|
|Retirer badge|✖|✖|✔|✔|
|Voir TopMentors|✔|✔|✔|✔|



## _Recherche et filtrage_ 

|Voir son score<br>✔<br>Attribuer badge officiel✖<br>Retirer badge<br>✖<br>Voir TopMentors<br>✔<br>_Recherche et filtrage_|✔<br>✖<br>✖<br>✔|✔<br>✔<br>✔<br>✔|✔<br>✔<br>✔<br>✔||
|---|---|---|---|---|
|**Fonctionnalité**|**Stagiaire**|**Mentor**|**Formateur**|**Administrateur**|
|Recherchepar compétence|✔|✔|✔|✔|
|Filtrerpar niveau|✔|✔|✔|✔|
|Filtrerpar filière|✔|✔|✔|✔|
|Voir disponibilité mentor|✔|✔|✔|✔|



## _Tableau de bord_ 

|Recherchepar compétence✔<br>Filtrerpar niveau<br>✔<br>Filtrerpar filière<br>✔<br>Voir disponibilité mentor<br>✔<br>_Tableau de bord_|✔<br>✔<br>✔<br>✔|✔<br>✔<br>✔<br>✔|✔<br>✔<br>✔<br>✔||
|---|---|---|---|---|
|**Indicateur**|**Stagiaire**|**Mentor**|**Formateur**|**Administrateur**|
|Nombre d’aides réalisées|✔|✔|✔|✔|
|Note moyenne|✔|✔|✔|✔|
|Compétences lesplus demandées|✔|✔|✔|✔|
|Statistiquesglobalesplateforme|✖|✖|✔|✔|



## **4. Exigences techniques :** 

## _Technologies_ 

L'application sera développée en utilisant obligatoirement un langage de programmation côté serveur PHP vous pouvez utiliser des CMS ou des frameworks. 

La base de données utilisée sera spécifiée MySQL 

Les technologies frontales telles que HTML, CSS, JavaScript et les frameworks de développement JavaScript (par exemple, React, Angular, Vue.js) peuvent être utilisées. 

## _Interface utilisateur conviviale_ 

L'application web devra avoir une interface utilisateur conviviale et réactive, compatible avec les différents appareils (ordinateurs de bureau, tablettes, smartphones). 

- **Design simple et clair** (flat design ou minimaliste). 

- **Structure responsive** (grid flexible, CSS media queries). 

- **Navigation intuitive** : menu ou barre latérale. 

- **Feedback utilisateur** (alertes de validation, messages d’erreur). 

## _Sécurité (optionnel)_ 

La sécurité des utilisateurs et de leurs données personnelles sera une priorité absolue. 

Les bonnes pratiques de sécurité, telles que le hachage des mots de passe, l'utilisation de protocoles de sécurité (HTTPS), la protection contre les attaques courantes (injection SQL, cross-site Scripting, etc.) devront être mises en place. 

## _Performances_ 

L'application devra être optimisée pour des performances rapides et évolutives afin de gérer une grande quantité d'utilisateurs et de données. (optionnel) 

## _Planification des avancements :_ 

|**Phase**|**Tâches**|**Durée**|**Période**|
|---|---|---|---|
|**1. Conception**|- Modélisation MERISE (MCD et<br>MLD)|5 jours|Semaine 1|
||- Maquettage UI/UX (interfaces<br>web)<br>Avec figma|5 jours|**29-30 Avril**|
|**2. Architecture technique**|- Conception base de données<br>(MySQL)|15 jours|**11 mai**|
|**4. Développement**<br>**Frontend Web**|- Intégration des maquettes<br>(HTML CSS JS)|1<br>semaine|**18 mai**|
|**5. Développement Backend**<br>**(API)**|- Authentification, gestion des<br>rôles|1 semaine|**26 mai**|
||Fonctionnalités de la gestion|3<br>semaines|**20 juin**|
||Fonctionnalités du gamification|1 semaine|**28 juin**|



|**6. Fonctionnalités**<br>**avancées**|- tri, recherches filtrées|1 semaine||
|---|---|---|---|
|**7. Tests & validation**|- Tests unitaires,fonctionnels|3jours|**31juin**|
|**8. Présentation finale**|- Rapport final + diaporama de<br>soutenance|1 jour|**Debut**<br>**Juillet**|



## _Livrables_ 

Une fois développée, vous devez m’envoyer au plus tard 48h avant les présentations les livrables suivantes : 

- 1- Projet PHP compressé 

- 2- Rapport en PDF contenant ces principaux axes : 

   - Description des fonctionnalités du projet 

   - Planification du projet 

   - Gestion de l’équipe et distribution des taches 

   - La réalisation (Capture d’écran des interfaces) 

   - Les difficultés rencontrées 

   - Les extensions possibles 

- 3- Présentation numérique contenant les mêmes axes que le rapport (en PowerPoint ou genially ou autre outil .. ) 

