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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_badge'])) {
    extract($_POST);
    $id_user = (int)$id_user;
    $id_badge = (int)$id_badge;
    $stmt = $db->prepare("SELECT id_badge FROM obtention_badges WHERE id_user = ? AND id_badge = ? LIMIT 1");
    $stmt->execute([$id_user, $id_badge]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO obtention_badges (id_user, id_badge, date_obtention) VALUES (?, ?, NOW())");
        $stmt->execute([$id_user, $id_badge]);
    }
    header('Location: badges.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirer_badge'])) {
    extract($_POST);
    $id_user = (int)$id_user;
    $id_badge = (int)$id_badge;
    $stmt = $db->prepare("DELETE FROM obtention_badges WHERE id_user = ? AND id_badge = ?");
    $stmt->execute([$id_user, $id_badge]);
    header('Location: badges.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM badges ORDER BY points_requis ASC");
$stmt->execute();
$badges = $stmt->fetchAll();

$stmt = $db->query("SELECT id_badge FROM badges");
$total_badges_count = count($stmt->fetchAll());

$stmt = $db->query("SELECT id_user FROM obtention_badges WHERE MONTH(date_obtention) = MONTH(CURDATE()) AND YEAR(date_obtention) = YEAR(CURDATE())");
$monthly_assigned = count($stmt->fetchAll());

$stmt = $db->query("SELECT DISTINCT id_user FROM obtention_badges");
$badged_students = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT id_user, nom, prenom FROM utilisateurs WHERE role = 'stagiaire' ORDER BY nom ASC");
$stmt->execute();
$students = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT ob.date_obtention, b.nom as badge_nom, b.image,
           u.nom as user_nom, u.prenom as user_prenom,
           ob.id_user, ob.id_badge
    FROM obtention_badges ob
    JOIN badges b ON ob.id_badge = b.id_badge
    JOIN utilisateurs u ON ob.id_user = u.id_user
    ORDER BY ob.date_obtention DESC
    LIMIT 20
");
$stmt->execute();
$attributions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Badges</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="badges.css">
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
        <section class="profile-container">
            <h2>Gestion des Badges</h2>
            <p class="subtitle">Attribuez des badges aux stagiaires</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Total Badges</h3>
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value"><?= $total_badges_count ?></div>
                    <div class="stat-change">Badges disponibles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Attribués ce mois</h3>
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="stat-value"><?= $monthly_assigned ?></div>
                    <div class="stat-change positive">Ce mois</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Stagiaires Badgés</h3>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= $badged_students ?></div>
                    <div class="stat-change">Total distincts</div>
                </div>
            </div>

            <section class="badges-section">
                <h3>Badges Disponibles</h3>
                <div class="badges-grid">
                    <?php foreach ($badges as $badge): ?>
                    <?php
                    $stmt = $db->prepare("SELECT id_obtention FROM obtention_badges WHERE id_badge = ?");
                    $stmt->execute([$badge['id_badge']]);
                    $assigned_count = count($stmt->fetchAll());
                    ?>
                    <div class="badge-card">
                        <div class="badge-icon"><?= htmlspecialchars($badge['image'] ?? '🏅') ?></div>
                        <h4><?= htmlspecialchars($badge['nom']) ?></h4>
                        <p><?= $badge['points_requis'] ?> points requis</p>
                        <span class="badge-status obtained"><?= $assigned_count ?> attribués</span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($badges)): ?>
                    <p>Aucun badge défini.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="competences-section assign-section">
                <h3>Attribuer un Badge</h3>
                <form method="POST" class="assign-form">
                    <div class="assign-row">
                        <div class="assign-field">
                            <label>Stagiaire</label>
                            <select name="id_user" class="filter-select" required>
                                <option value="">Sélectionner un stagiaire...</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id_user'] ?>"><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="assign-field">
                            <label>Badge</label>
                            <select name="id_badge" class="filter-select" required>
                                <option value="">Sélectionner un badge...</option>
                                <?php foreach ($badges as $b): ?>
                                <option value="<?= $b['id_badge'] ?>"><?= htmlspecialchars($b['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="assign-field assign-btn-wrap">
                            <label>&nbsp;</label>
                            <button type="submit" name="assign_badge" class="btn-assign"><i class="fas fa-gift"></i> Attribuer</button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="competences-section">
                <h3>Attributions Récentes</h3>
                <table class="validations-table">
                    <thead>
                        <tr>
                            <th>Stagiaire</th>
                            <th>Badge</th>
                            <th>Date</th>
                            <th>Attribué par</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attributions)): ?>
                        <tr><td colspan="5">Aucune attribution.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($attributions as $a): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['user_prenom'] . ' ' . $a['user_nom']) ?></strong></td>
                            <td><span class="badge-tag"><?= htmlspecialchars($a['image'] ?? '🏅') ?> <?= htmlspecialchars($a['badge_nom']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($a['date_obtention'])) ?></td>
                            <td>Admin</td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Confirmer le retrait de ce badge ?')">
                                    <input type="hidden" name="id_user" value="<?= $a['id_user'] ?>">
                                    <input type="hidden" name="id_badge" value="<?= $a['id_badge'] ?>">
                                    <button type="submit" name="retirer_badge" class="btn-assign" style="background:#dc2625">Retirer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </section>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
