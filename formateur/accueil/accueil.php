<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
extract($_SESSION);
$init = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
$current_url = $_SERVER["PHP_SELF"];

require_once __DIR__ . '/../../dbopen/db.php';

$stmt = $db->prepare("SELECT 1 FROM aide_effectuee");
$stmt->execute();
$total_helps = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT note_mentor FROM aide_effectuee WHERE note_mentor IS NOT NULL");
$stmt->execute();
$ratings = $stmt->fetchAll(PDO::FETCH_COLUMN);
$avg_rating = count($ratings) > 0 ? number_format(array_sum($ratings) / count($ratings), 1) : '0';

$stmt = $db->prepare("SELECT DISTINCT id_user FROM demandes_aide");
$stmt->execute();
$students_followed = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT id FROM validation_competence WHERE status IS NULL");
$stmt->execute();
$pending_validations = count($stmt->fetchAll());

$stmt = $db->query("
    SELECT c.nom, COUNT(uc.id) as cnt
    FROM competences c
    LEFT JOIN user_competences uc ON c.id_competence = uc.id_competence
    GROUP BY c.id_competence
    ORDER BY cnt DESC
    LIMIT 5
");
$top_skills = $stmt->fetchAll();
$max_cnt = !empty($top_skills) ? max(array_column($top_skills, 'cnt')) : 1;

$stmt = $db->prepare("SELECT id_user FROM utilisateurs");
$stmt->execute();
$total_users = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT id_demande FROM demandes_aide");
$stmt->execute();
$total_demands = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT 1 FROM aide_effectuee");
$stmt->execute();
$total_completed = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT id_user FROM obtention_badges");
$stmt->execute();
$total_badges_attr = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT note_mentor FROM aide_effectuee WHERE note_mentor IS NOT NULL");
$stmt->execute();
$ratings_global = $stmt->fetchAll(PDO::FETCH_COLUMN);
$avg_rating_global = count($ratings_global) > 0 ? number_format(array_sum($ratings_global) / count($ratings_global), 1) : '0';

$stmt = $db->prepare("SELECT id_demande FROM demandes_aide WHERE status = 'resolue'");
$stmt->execute();
$resolved = count($stmt->fetchAll());
$resolution_rate = $total_demands > 0 ? round(($resolved / $total_demands) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Tableau de Bord</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="accueil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar sidebar-formateur">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/phpprojet/formateur/accueil/accueil.php" class="nav-item<?= strpos($current_url,'accueil')!==false?' active':'' ?>"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="/phpprojet/formateur/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/formateur/profile/profile.php" class="nav-item<?= strpos($current_url,'profile')!==false?' active':'' ?>"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="/phpprojet/formateur/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-passport"></i><span>Passport</span></a>
            <a href="/phpprojet/formateur/validation/validations.php" class="nav-item<?= strpos($current_url,'validation')!==false?' active':'' ?>"><i class="fas fa-check-circle"></i><span>Validations</span></a>
            <a href="/phpprojet/formateur/badge/badges.php" class="nav-item<?= strpos($current_url,'badge')!==false?' active':'' ?>"><i class="fas fa-medal"></i><span>Badges</span></a>
            <a href="/phpprojet/formateur/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?= $init ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars("$prenom $nom") ?></p>
                    <p class="user-role"><?= ucfirst($role) ?></p>
                </div>
            </div>
        </div>
    </aside>
    <header class="top-header">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher..." id="globalSearch">
        </div>
        <div class="header-actions">
            <button class="notification-btn"><i class="fas fa-bell"></i><span class="notification-badge">0</span></button>
            <div class="user-menu" id="profileToggle">
                <div class="avatar-large"><?= $init ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars("$prenom $nom") ?></p>
                    <p class="user-role"><?= ucfirst($role) ?></p>
                </div>
                <i class="fas fa-chevron-down" id="chevronIcon"></i>
            </div>
        </div>
        <div class="profile-dropdown" id="profileMenu">
            <div class="dropdown-header">
                <div class="dropdown-avatar"><?= $init ?></div>
                <div class="dropdown-user-info">
                    <h4><?= htmlspecialchars("$prenom $nom") ?></h4>
                    <p><?= $filiere ? "Filière $filiere" : "Platform ".ucfirst($role) ?></p>
                    <span class="role-badge role-badge-formateur"><?= ucfirst($role) ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
                <div class="meta-item"><i class="fas fa-calendar"></i> Membre depuis <?= date("d/m/Y",strtotime($date_inscription)) ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/formateur/passport/passport.php" class="dropdown-action"><i class="fas fa-download"></i> Télécharger le Passeport (PDF)</a>
                <a href="/phpprojet/formateur/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> Voir le profil</a>
            </div>
            <div class="dropdown-signout">
                <a href="/phpprojet/login/deconnexion.php" class="dropdown-action signout"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
            </div>
        </div>
    </header>
    <main class="main-content">
        <h1 class="section-title">Tableau de Bord</h1>
        <p class="sub">Voici un aperçu de ton activité et des compétences validées</p>
        <div class="cards-grid">
            <article class="stat-card">
                <div class="stat-head">
                    <span>Aides réalisées</span>
                    <span>👥</span>
                </div>
                <h2><?= $total_helps ?></h2>
                <p>Total plateforme</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Note moyenne</span>
                    <span>⭐</span>
                </div>
                <h2><?= $avg_rating ?></h2>
                <p>sur 5</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Stagiaires suivis</span>
                    <span>👤</span>
                </div>
                <h2><?= $students_followed ?></h2>
                <p>Total plateforme</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Validations en attente</span>
                    <span>⏳</span>
                </div>
                <h2><?= $pending_validations ?></h2>
                <p>à traiter</p>
            </article>
        </div>

        <section class="skills-section">
            <div class="section-header">
                <h3>Compétences les plus demandées</h3>
            </div>
            <div class="skills-bar-list">
                <?php foreach ($top_skills as $skill): ?>
                <div class="skill-bar-item">
                    <div class="skill-bar-label">
                        <span><?= htmlspecialchars($skill['nom']) ?></span>
                        <span><?= $skill['cnt'] ?> déclarations</span>
                    </div>
                    <div class="skill-bar-track">
                        <div class="skill-bar-fill" style="width:<?= ($skill['cnt'] / $max_cnt) * 100 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($top_skills)): ?>
                <p>Aucune compétence enregistrée.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="stats-global">
            <div class="section-header">
                <h3>Statistiques globales de la plateforme</h3>
            </div>
            <div class="global-grid">
                <div class="global-card">
                    <span class="global-icon">👥</span>
                    <h4><?= $total_users ?></h4>
                    <p>Utilisateurs inscrits</p>
                </div>
                <div class="global-card">
                    <span class="global-icon">📋</span>
                    <h4><?= $total_demands ?></h4>
                    <p>Demandes publiées</p>
                </div>
                <div class="global-card">
                    <span class="global-icon">✅</span>
                    <h4><?= $total_completed ?></h4>
                    <p>Aides complétées</p>
                </div>
                <div class="global-card">
                    <span class="global-icon">🏅</span>
                    <h4><?= $total_badges_attr ?></h4>
                    <p>Badges attribués</p>
                </div>
                <div class="global-card">
                    <span class="global-icon">⭐</span>
                    <h4><?= $avg_rating_global ?></h4>
                    <p>Note moyenne globale</p>
                </div>
                <div class="global-card">
                    <span class="global-icon">📊</span>
                    <h4><?= $resolution_rate ?>%</h4>
                    <p>Taux de résolution</p>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
