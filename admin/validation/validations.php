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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_validation'])) {
    extract($_POST);
    $id = (int)$id_validation;
    $action = isset($action) ? $action : '';
    $status = $action === 'valider' ? 'valide' : 'refuse';
    $stmt = $db->prepare("UPDATE validation_competence SET status = ?, id_formateur = ?, date_validation = NOW() WHERE id = ?");
    $stmt->execute([$status, $id_user, $id]);
    header('Location: validations.php');
    exit;
}

$search = $_GET['search'] ?? '';
$sql = "
    SELECT vc.id, vc.status, vc.date_validation,
           u.nom, u.prenom,
           c.nom as competence_nom, c.categorie,
           uc.niveau
    FROM validation_competence vc
    JOIN user_competences uc ON vc.id_user_competence = uc.id
    JOIN competences c ON uc.id_competence = c.id_competence
    JOIN utilisateurs u ON uc.id_user = u.id_user
";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE (u.nom LIKE ? OR u.prenom LIKE ? OR c.nom LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY vc.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$validations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Validations</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="validations.css">
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
        <h2>Demandes de Validation</h2>
        <p class="subtitle">Gérez les demandes de validation des compétences (vue globale)</p>

        <div class="filters-row">
            <div class="filter-group">
                <form method="GET" style="display:flex;gap:10px;width:100%;">
                    <input type="text" name="search" placeholder="Rechercher par stagiaire ou compétence..." class="filter-input" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn primary">Filtrer</button>
                </form>
            </div>
        </div>

        <table class="validations-table">
            <thead>
                <tr>
                    <th>Stagiaire</th>
                    <th>Compétence</th>
                    <th>Niveau</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($validations)): ?>
                <tr><td colspan="6">Aucune validation trouvée.</td></tr>
                <?php endif; ?>
                <?php foreach ($validations as $v): ?>
                <?php
                $status_label = match ($v['status']) {
                    'valide' => 'Validée',
                    'refuse' => 'Refusée',
                    default => 'En attente'
                };
                $status_class = match ($v['status']) {
                    'valide' => 'validated',
                    'refuse' => 'rejected',
                    default => 'pending'
                };
                $level_class = match ($v['niveau']) {
                    'debutant' => 'beginner',
                    'intermediaire' => 'intermed',
                    'avance' => 'advanced',
                    default => 'intermed'
                };
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($v['prenom'] . ' ' . $v['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($v['competence_nom']) ?></td>
                    <td><span class="level-badge <?= $level_class ?>"><?= htmlspecialchars(ucfirst($v['niveau'])) ?></span></td>
                    <td><?= $v['date_validation'] ? date('d/m/Y', strtotime($v['date_validation'])) : '-' ?></td>
                    <td><span class="status-badge <?= $status_class ?>"><?= $status_label ?></span></td>
                    <td>
                        <?php if ($v['status'] === null): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_validation" value="<?= $v['id'] ?>">
                            <button type="submit" name="action" value="valider" class="btn primary" style="padding:4px 12px;font-size:12px;">Approuver</button>
                            <button type="submit" name="action" value="refuser" class="btn secondary" style="padding:4px 12px;font-size:12px;">Refuser</button>
                        </form>
                        <?php else: ?>
                        &mdash;
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
