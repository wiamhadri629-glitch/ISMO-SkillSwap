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

$stmt = $db->prepare("SELECT COALESCE(score,0) as score FROM utilisateurs WHERE role = 'mentor' AND COALESCE(score,0) > (SELECT COALESCE(score,0) FROM utilisateurs WHERE id_user = ?)");
$stmt->execute([$id_user]);
$higher_scores = $stmt->fetchAll();
$user_rank = count($higher_scores) + 1;

$stmt = $db->prepare("SELECT COALESCE(score, 0) as score FROM utilisateurs WHERE id_user = ?");
$stmt->execute([$id_user]);
$user_score = (int)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT id_badge FROM obtention_badges WHERE id_user = ?");
$stmt->execute([$id_user]);
$user_badges = count($stmt->fetchAll());

$filiere_filter = $_GET['filiere'] ?? '';

$sql = "SELECT u.id_user, u.nom, u.prenom, COALESCE(u.score,0) as score, u.filiere FROM utilisateurs u WHERE u.role = 'mentor'";
$params = [];
if (!empty($filiere_filter)) {
    $sql .= " AND u.filiere = ?";
    $params[] = $filiere_filter;
}
$sql .= " ORDER BY score DESC LIMIT 10";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$top_users = $stmt->fetchAll();

$user_ids = array_column($top_users, 'id_user');
$badge_map = [];
if (!empty($user_ids)) {
    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
    $st = $db->prepare("SELECT id_user FROM obtention_badges WHERE id_user IN ($placeholders)");
    $st->execute($user_ids);
    $badge_rows = $st->fetchAll();
    foreach ($badge_rows as $br) {
        $uid = $br['id_user'];
        if (!isset($badge_map[$uid])) $badge_map[$uid] = 0;
        $badge_map[$uid]++;
    }
}
foreach ($top_users as &$user) {
    $user['badge_count'] = $badge_map[$user['id_user']] ?? 0;
}
unset($user);

$stmt = $db->prepare("SELECT DISTINCT filiere FROM utilisateurs WHERE filiere IS NOT NULL AND filiere != '' ORDER BY filiere");
$stmt->execute();
$filieres = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Classement Mentor</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="classement.css">
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
        <section class="rank-header">
            <div>
                <p class="eyebrow">Classement des Mentors</p>
                <h1>Les meilleurs contributeurs de la communauté ISMO</h1>
            </div>
        </section>

        <section class="rank-cards">
            <article class="rank-card highlight">
                <div class="rank-card-title">
                    <i class="fas fa-trophy"></i>
                    <div>
                        <span>Ton Rang</span>
                        <p>parmi tous les mentors</p>
                    </div>
                </div>
                <strong>#<?= $user_rank ?></strong>
            </article>
            <article class="rank-card">
                <div class="rank-card-title">
                    <i class="fas fa-star"></i>
                    <div>
                        <span>Tes Points</span>
                        <p>score de réputation</p>
                    </div>
                </div>
                <strong><?= number_format($user_score, 0, ',', ' ') ?></strong>
            </article>
            <article class="rank-card">
                <div class="rank-card-title">
                    <i class="fas fa-medal"></i>
                    <div>
                        <span>Badges</span>
                        <p>obtenus</p>
                    </div>
                </div>
                <strong><?= $user_badges ?></strong>
            </article>
        </section>

        <section class="top-list-section">
            <div class="list-header">
                <h2>Top 10 Mentors</h2>
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
                <?php foreach ($top_users as $i => $user):
                    $rank = $i + 1;
                    $init_user = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
                    $class = $user['id_user'] == $id_user ? ' highlight' : '';
                ?>
                <article class="top-list-item<?= $class ?>">
                    <div class="top-rank">#<?= $rank ?></div>
                    <div class="top-avatar"><?= htmlspecialchars($init_user) ?></div>
                    <div class="top-info">
                        <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
                        <span><?= htmlspecialchars($user['filiere'] ?? 'Mentor') ?></span>
                    </div>
                    <div class="top-score">
                        <span><?= number_format((int)$user['score'], 0, ',', ' ') ?></span>
                        <small><?= (int)$user['badge_count'] ?> badges</small>
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
