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

$filiere_filter = $_GET['filiere'] ?? '';

$sql = "
    SELECT u.id_user, u.nom, u.prenom, u.score, u.filiere
    FROM utilisateurs u
    WHERE u.role IN ('stagiaire', 'mentor')
";
$params = [];
if (!empty($filiere_filter)) {
    $sql .= " AND u.filiere = ?";
    $params[] = $filiere_filter;
}
$sql .= " ORDER BY u.score DESC LIMIT 50";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

foreach ($users as &$u) {
    $stmt = $db->prepare("SELECT id_badge FROM obtention_badges WHERE id_user = ?");
    $stmt->execute([$u['id_user']]);
    $u['badges_count'] = count($stmt->fetchAll());

    $stmt = $db->prepare("SELECT id_mentor FROM aide_effectuee WHERE id_mentor = ?");
    $stmt->execute([$u['id_user']]);
    $u['helps_count'] = count($stmt->fetchAll());
}
unset($u);

$stmt = $db->prepare("SELECT DISTINCT filiere FROM utilisateurs WHERE filiere IS NOT NULL AND filiere != '' ORDER BY filiere");
$stmt->execute();
$filieres = $stmt->fetchAll();

$current_user_id = $id_user;
$current_rank = 0;
$current_score = 0;
foreach ($users as $i => $u) {
    if ($u['id_user'] == $current_user_id) {
        $current_rank = $i + 1;
        $current_score = (int)$u['score'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Classement</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="classement.css">
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
        <section class="rank-header">
            <div>
                <p class="eyebrow">Classement</p>
                <h1>Les meilleurs contributeurs de la communauté ISMO</h1>
            </div>
        </section>

        <section class="rank-cards">
            <article class="rank-card highlight">
                <div class="rank-card-title">
                    <i class="fas fa-trophy"></i>
                    <div>
                        <span>Ton Rang</span>
                        <p>Classement global</p>
                    </div>
                </div>
                <strong>#<?= $current_rank ?: 'N/A' ?></strong>
            </article>
            <article class="rank-card">
                <div class="rank-card-title">
                    <i class="fas fa-star"></i>
                    <div>
                        <span>Tes Points</span>
                        <p>Score actuel</p>
                    </div>
                </div>
                <strong><?= number_format($current_score) ?></strong>
            </article>
            <article class="rank-card">
                <div class="rank-card-title">
                    <i class="fas fa-users"></i>
                    <div>
                        <span>Total</span>
                        <p>Participants classés</p>
                    </div>
                </div>
                <strong><?= count($users) ?></strong>
            </article>
        </section>

        <section class="top-list-section">
            <div class="list-header">
                <h2>Classement des utilisateurs</h2>
                <form method="GET">
                    <select name="filiere" onchange="this.form.submit()">
                        <option value="">Toutes les filières</option>
                        <?php foreach ($filieres as $f): ?>
                        <option value="<?= htmlspecialchars($f['filiere']) ?>" <?= $filiere_filter === $f['filiere'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['filiere']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="top-list">
                <?php if (empty($users)): ?>
                <p>Aucun utilisateur trouvé.</p>
                <?php endif; ?>
                <?php foreach ($users as $i => $u): ?>
                <article class="top-list-item">
                    <div class="top-rank">#<?= $i + 1 ?></div>
                    <div class="top-avatar"><?= strtoupper(substr($u['nom'],0,1).substr($u['prenom'],0,1)) ?></div>
                    <div class="top-info">
                        <strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong>
                        <span><?= htmlspecialchars($u['filiere'] ?? 'Non spécifié') ?></span>
                    </div>
                    <div class="top-score">
                        <span><?= number_format((int)$u['score']) ?></span>
                        <small><?= $u['badges_count'] ?> badges</small>
                    </div>
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
