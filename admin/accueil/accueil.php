<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
extract($_SESSION);
if ($role !== 'admin') {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$init = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
$current_url = $_SERVER["PHP_SELF"];

require_once __DIR__ . '/../../dbopen/db.php';

$stmt = $db->query("SELECT id_user FROM utilisateurs WHERE role = 'stagiaire'");
$total_stagiaires = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM utilisateurs WHERE role = 'mentor'");
$total_mentors = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM utilisateurs WHERE role = 'formateur'");
$total_formateurs = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM utilisateurs WHERE role = 'admin'");
$total_admins = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM utilisateurs WHERE valide = 1");
$total_valides = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM utilisateurs WHERE valide = 0 OR valide IS NULL");
$total_invalides = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_demande FROM demandes_aide");
$total_demands = count($stmt->fetchAll());

$stmt = $db->query("SELECT id FROM aide_effectuee");
$total_completed = count($stmt->fetchAll());

$stmt = $db->query("SELECT id FROM validation_competence");
$total_validations = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM obtention_badges");
$total_badges_attr = count($stmt->fetchAll());

$stmt = $db->query("SELECT note_mentor FROM aide_effectuee WHERE note_mentor IS NOT NULL");
$ratings = $stmt->fetchAll(PDO::FETCH_COLUMN);
$avg_rating = !empty($ratings) ? number_format(array_sum($ratings) / count($ratings), 1) : '0';

$stmt = $db->query("SELECT c.nom, uc.id as uc_id FROM competences c LEFT JOIN user_competences uc ON c.id_competence = uc.id_competence");
$rows = $stmt->fetchAll();
$temp = [];
foreach ($rows as $r) {
    $temp[$r['nom']][] = $r['uc_id'];
}
$skill_counts = [];
foreach ($temp as $nom => $ids) {
    $skill_counts[$nom] = count(array_filter($ids, function($v) { return $v !== null; }));
}
arsort($skill_counts);
$top_skills = [];
$i = 0;
foreach ($skill_counts as $nom => $cnt) {
    if ($i >= 5) break;
    $top_skills[] = ['nom' => $nom, 'cnt' => $cnt];
    $i++;
}
$max_cnt = !empty($top_skills) ? max(array_column($top_skills, 'cnt')) : 1;

$stmt = $db->query("SELECT id_demande FROM demandes_aide WHERE status = 'resolue'");
$resolved = count($stmt->fetchAll());
$resolution_rate = $total_demands > 0 ? round(($resolved / $total_demands) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Tableau de Bord Admin</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="accueil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar sidebar-admin">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/phpprojet/admin/accueil/accueil.php" class="nav-item<?= strpos($current_url,'accueil')!==false?' active':'' ?>"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="/phpprojet/admin/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/admin/utilisateurs/utilisateurs.php" class="nav-item<?= strpos($current_url,'utilisateurs')!==false?' active':'' ?>"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
            <a href="/phpprojet/admin/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-shield"></i><span>Modération</span></a>
            <a href="/phpprojet/admin/badge/badges.php" class="nav-item<?= strpos($current_url,'badge')!==false?' active':'' ?>"><i class="fas fa-medal"></i><span>Badges</span></a>
            <a href="/phpprojet/admin/validation/validations.php" class="nav-item<?= strpos($current_url,'validation')!==false?' active':'' ?>"><i class="fas fa-check-circle"></i><span>Validations</span></a>
            <a href="/phpprojet/admin/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
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
                    <span class="role-badge role-badge-admin"><?= ucfirst($role) ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
                <div class="meta-item"><i class="fas fa-calendar"></i> Membre depuis <?= date("d/m/Y",strtotime($date_inscription)) ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/admin/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> Voir le profil</a>
            </div>
            <div class="dropdown-signout">
                <a href="/phpprojet/login/deconnexion.php" class="dropdown-action signout"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
            </div>
        </div>
    </header>
    <main class="main-content">
        <h1 class="section-title">Tableau de Bord Admin</h1>
        <p class="sub">Vue d'ensemble de la plateforme ISMO-SkillSwap</p>
        <div class="cards-grid">
            <article class="stat-card">
                <div class="stat-head">
                    <span>Utilisateurs</span>
                    <span>👥</span>
                </div>
                <h2><?= $total_stagiaires + $total_mentors + $total_formateurs + $total_admins ?></h2>
                <p><?= $total_stagiaires ?> stagiaires · <?= $total_mentors ?> mentors · <?= $total_formateurs ?> formateurs</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Demandes</span>
                    <span>📋</span>
                </div>
                <h2><?= $total_demands ?></h2>
                <p>publiées sur la plateforme</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Validations</span>
                    <span>✅</span>
                </div>
                <h2><?= $total_validations ?></h2>
                <p>demandes de validation</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Comptes inactifs</span>
                    <span>⚠️</span>
                </div>
                <h2><?= $total_invalides ?></h2>
                <p>en attente de validation</p>
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
                    <h4><?= $total_stagiaires + $total_mentors + $total_formateurs + $total_admins ?></h4>
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
                    <h4><?= $avg_rating ?></h4>
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
