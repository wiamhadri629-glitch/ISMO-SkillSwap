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

$stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_mentor = ?");
$stmt->execute([$id_user]);
$helps_given = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT note_mentor FROM aide_effectuee WHERE id_mentor = ? AND note_mentor IS NOT NULL");
$stmt->execute([$id_user]);
$notes = $stmt->fetchAll(PDO::FETCH_COLUMN);
$avg_rating = count($notes) > 0 ? round(array_sum($notes) / count($notes), 1) : '0.0';

$stmt = $db->prepare("SELECT id_demande FROM propositions_aide WHERE id_mentor = ?");
$stmt->execute([$id_user]);
$active_propositions = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT id_badge FROM obtention_badges WHERE id_user = ?");
$stmt->execute([$id_user]);
$badge_count = count($stmt->fetchAll());

$total_badges = 0;
$stmt2 = $db->query("SELECT COUNT(*) FROM badges");
$total_badges = (int)$stmt2->fetchColumn();

$stmt = $db->query("SELECT c.nom, uc.id_user FROM competences c LEFT JOIN user_competences uc ON c.id_competence = uc.id_competence");
$rows = $stmt->fetchAll();
$counts = [];
foreach ($rows as $row) {
    $nom = $row['nom'];
    if (!isset($counts[$nom])) $counts[$nom] = [];
    if ($row['id_user'] !== null) $counts[$nom][$row['id_user']] = true;
}
uasort($counts, fn($a, $b) => count($b) - count($a));
$top_skills = [];
$i = 0;
foreach ($counts as $nom => $users) {
    if ($i >= 5) break;
    $top_skills[] = ['nom' => $nom, 'cnt' => count($users)];
    $i++;
}
$max_count = 1;
if (!empty($top_skills)) {
    $max_count = max((int)$top_skills[0]['cnt'], 1);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Accueil Mentor</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="accueil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar sidebar-mentor">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/phpprojet/mentor/accueil/accueil.php" class="nav-item<?= strpos($current_url,'accueil')!==false?' active':'' ?>"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="/phpprojet/mentor/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/mentor/profile/profile.php" class="nav-item<?= strpos($current_url,'profile')!==false?' active':'' ?>"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="/phpprojet/mentor/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-passport"></i><span>Passport</span></a>
            <a href="/phpprojet/mentor/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
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
                    <span class="role-badge role-badge-mentor"><?= ucfirst($role) ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
                <div class="meta-item"><i class="fas fa-calendar"></i> Membre depuis <?= date("d/m/Y",strtotime($date_inscription)) ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/mentor/passport/passport.php" class="dropdown-action"><i class="fas fa-download"></i> Télécharger le Passeport (PDF)</a>
                <a href="/phpprojet/mentor/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> Voir le profil</a>
            </div>
            <div class="dropdown-signout">
                <a href="/phpprojet/login/deconnexion.php" class="dropdown-action signout"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
            </div>
        </div>
    </header>
    <main class="main-content">
        <h1 class="section-title">Tableau de Bord</h1>
        <p class="sub">Voici un aperçu de ton activité et de tes compétences validées</p>
        <div class="cards-grid">
            <article class="stat-card">
                <div class="stat-head">
                    <span>Aides réalisées</span>
                    <span>👥</span>
                </div>
                <h2><?= $helps_given ?></h2>
                <p>depuis ton inscription</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Note moyenne</span>
                    <span>⭐</span>
                </div>
                <h2><?= htmlspecialchars($avg_rating) ?></h2>
                <p>sur 5</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Propositions actives</span>
                    <span>💬</span>
                </div>
                <h2><?= $active_propositions ?></h2>
                <p>en attente de réponse</p>
            </article>
            <article class="stat-card">
                <div class="stat-head">
                    <span>Badges obtenus</span>
                    <span>🏅</span>
                </div>
                <h2><?= $badge_count ?></h2>
                <p><?= $total_badges - $badge_count ?> restants</p>
            </article>
        </div>

        <section class="skills-section">
            <div class="section-header">
                <h3>Compétences les plus demandées</h3>
            </div>
            <div class="skills-bar-list">
                <?php foreach ($top_skills as $skill): $pct = round(((int)$skill['cnt'] / $max_count) * 100); ?>
                <div class="skill-bar-item">
                    <div class="skill-bar-label"><span><?= htmlspecialchars($skill['nom']) ?></span><span><?= (int)$skill['cnt'] ?> demandes</span></div>
                    <div class="skill-bar-track"><div class="skill-bar-fill" style="width:<?= $pct ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
