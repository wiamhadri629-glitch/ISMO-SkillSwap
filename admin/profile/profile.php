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

$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

if (!$user) {
    echo '<p>Utilisateur introuvable.</p>';
    exit;
}

$init = strtoupper(substr($user['nom'],0,1).substr($user['prenom'],0,1));

$stmt = $db->query("SELECT id FROM aide_effectuee");
$helps_count = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT ob.id_obtention FROM obtention_badges ob JOIN badges b ON ob.id_badge = b.id_badge WHERE ob.id_user = ?");
$stmt->execute([$id_user]);
$badges_obtained = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_badge FROM badges");
$total_badges = count($stmt->fetchAll());

$stmt = $db->prepare("
    SELECT c.nom, c.categorie, uc.niveau
    FROM user_competences uc
    JOIN competences c ON uc.id_competence = c.id_competence
    WHERE uc.id_user = ?
");
$stmt->execute([$id_user]);
$competences = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT b.nom, b.image, ob.date_obtention
    FROM obtention_badges ob
    JOIN badges b ON ob.id_badge = b.id_badge
    WHERE ob.id_user = ?
    ORDER BY ob.date_obtention DESC
");
$stmt->execute([$id_user]);
$user_badges = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Profil</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="profile.css">
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
        <section class="profile-hero">
            <div class="profile-card">
                <div class="profile-summary">
                    <div class="profile-avatar hero"><?= $init ?></div>
                    <div>
                        <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
                        <p>Administrateur</p>
                        <p class="member-since">Membre depuis <?= date('d/m/Y', strtotime($user['date_inscription'])) ?></p>
                    </div>
                </div>
                <div class="profile-actions-hero">
                    <a href="modifier_profile.php" class="btn primary">Modifier profil</a>
                </div>
            </div>
            <div class="profile-stats-hero">
                <article class="stat-card small">
                    <span class="stat-label">Aides réalisées</span>
                    <strong><?= $helps_count ?></strong>
                    <span class="stat-note">Total plateforme</span>
                </article>
                <article class="stat-card small">
                    <span class="stat-label">Points de réputation</span>
                    <strong><?= (int)$user['score'] ?></strong>
                    <span class="stat-note positive">Score actuel</span>
                </article>
                <article class="stat-card small">
                    <span class="stat-label">Badges obtenus</span>
                    <strong><?= $badges_obtained ?>/<?= $total_badges ?></strong>
                    <span class="stat-note"><?= $total_badges - $badges_obtained ?> restants</span>
                </article>
            </div>
        </section>

        <section class="competence-section">
            <div class="section-heading">
                <h3>Mes Compétences</h3>
            </div>
            <?php if (!empty($competences)): ?>
            <div class="skills-grid">
                <?php foreach ($competences as $comp): ?>
                <?php
                $level_class = match ($comp['niveau']) {
                    'debutant' => 'warning',
                    'intermediaire' => 'info',
                    'avance' => 'success',
                    default => 'info'
                };
                $level_pct = match ($comp['niveau']) {
                    'debutant' => 40,
                    'intermediaire' => 70,
                    'avance' => 95,
                    default => 50
                };
                ?>
                <div class="skill-item">
                    <div class="skill-header">
                        <strong><?= htmlspecialchars($comp['nom']) ?></strong>
                        <span class="skill-badge <?= $level_class ?>"><?= htmlspecialchars(ucfirst($comp['niveau'])) ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= $level_pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>Aucune compétence déclarée.</p>
            <?php endif; ?>
        </section>

        <section class="badges-section">
            <div class="section-heading">
                <h3>Badges & Réalisations</h3>
            </div>
            <div class="badges-grid">
                <?php
                $stmt_badges = $db->prepare("SELECT * FROM badges ORDER BY points_requis ASC");
                $stmt_badges->execute();
                $all_badges = $stmt_badges->fetchAll();
                $obtained_ids = array_column($user_badges, 'id_badge');
                ?>
                <?php foreach ($all_badges as $badge): ?>
                <?php $obtained = in_array($badge['id_badge'], $obtained_ids); ?>
                <article class="badge-card <?= $obtained ? 'obtained' : 'locked' ?>">
                    <div class="badge-icon"><?= htmlspecialchars($badge['image'] ?? '🏅') ?></div>
                    <h4><?= htmlspecialchars($badge['nom']) ?></h4>
                    <p><?= $badge['points_requis'] ?> points requis</p>
                    <span class="badge-status <?= $obtained ? 'obtained' : 'locked' ?>">
                        <?= $obtained ? '✔ Obtenu' : 'Verrouillé' ?>
                    </span>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
