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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_demande'])) {
    extract($_POST);
    $id_demande = (int)$delete_demande;
    $stmt = $db->prepare("DELETE FROM propositions_aide WHERE id_demande = ?");
    $stmt->execute([$id_demande]);
    $stmt = $db->prepare("DELETE FROM demandes_aide WHERE id_demande = ?");
    $stmt->execute([$id_demande]);
    header('Location: marketplace.php');
    exit;
}

$search = $_GET['search'] ?? '';
$sql = "
    SELECT d.*, u.nom, u.prenom
    FROM demandes_aide d
    JOIN utilisateurs u ON d.id_user = u.id_user
";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE (d.titre LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY d.date_pub DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Marketplace</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="marketplace.css">
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
            <a href="/phpprojet/admin/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false&&strpos($current_url,'historique')===false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/admin/marketplace/historique.php" class="nav-item<?= strpos($current_url,'historique')!==false?' active':'' ?>"><i class="fas fa-clock-rotate"></i><span>Historique</span></a>
            <a href="/phpprojet/admin/competences/competences.php" class="nav-item<?= strpos($current_url,'admin/competences')!==false?' active':'' ?>"><i class="fas fa-code"></i><span>Compétences</span></a>
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
        <h1 class="section-title">Marketplace</h1>

        <div class="search-row">
            <form method="GET" style="display:flex;gap:10px;width:100%;">
                <div class="search-large">
                    <span>🔍</span>
                    <input type="text" name="search" placeholder="Rechercher par titre ou auteur..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn primary">Rechercher</button>
            </form>
        </div>

        <div class="market-grid">
            <?php if (empty($demandes)): ?>
            <p>Aucune demande trouvée.</p>
            <?php endif; ?>
            <?php foreach ($demandes as $d): ?>
            <?php
            $status_class = match ($d['status']) {
                'ouvert' => 'open',
                'en_cours' => 'in-progress',
                'resolu', 'termine' => 'resolved',
                default => 'open'
            };
            $status_label = match ($d['status']) {
                'ouvert' => 'Ouvert',
                'en_cours' => 'En cours',
                'resolu' => 'Résolu',
                'termine' => 'Terminé',
                default => ucfirst($d['status'])
            };
            $init_author = strtoupper(substr($d['nom'],0,1).substr($d['prenom'],0,1));
            ?>
            <article class="market-card">
                <div class="card-head">
                    <div>
                        <p class="status-pill <?= $status_class ?>"><?= $status_label ?></p>
                        <h2><?= htmlspecialchars($d['titre']) ?></h2>
                    </div>
                    <form method="POST" onsubmit="return confirm('Supprimer cette demande ?')">
                        <input type="hidden" name="delete_demande" value="<?= $d['id_demande'] ?>">
                        <button type="submit" class="btn-report" title="Supprimer"><i class="fas fa-trash-can"></i></button>
                    </form>
                </div>
                <p class="card-copy"><?= htmlspecialchars(mb_substr($d['description'], 0, 200)) ?></p>
                <div class="card-footer">
                    <div class="author">
                        <span class="avatar-sm"><?= $init_author ?></span>
                        <div>
                            <strong><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></strong>
                            <small><?= date('d/m/Y', strtotime($d['date_pub'])) ?></small>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
