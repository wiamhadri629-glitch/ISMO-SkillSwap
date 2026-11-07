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

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$valide_filter = $_GET['valide'] ?? '';

$sql = "SELECT id_user, nom, prenom, email, role, filiere, score, date_inscription, valide, photo FROM utilisateurs WHERE 1=1";
$params = [];
if (!empty($search)) {
    $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}
if ($valide_filter !== '') {
    $sql .= " AND valide = ?";
    $params[] = (int)$valide_filter;
}
$sql .= " ORDER BY date_inscription DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Utilisateurs</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="../accueil/accueil.css">
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
        <h1 class="section-title">Gestion des Utilisateurs</h1>
        <p class="sub">Gérez les comptes utilisateurs de la plateforme</p>

        <div class="filters-row">
            <div class="filter-group">
                <form method="GET" style="display:flex;gap:10px;width:100%;flex-wrap:wrap;">
                    <input type="text" name="search" placeholder="Rechercher par nom, prénom ou email..." class="filter-input" value="<?= htmlspecialchars($search) ?>" style="flex:2;min-width:200px;">
                    <select name="role" class="filter-select" onchange="this.form.submit()">
                        <option value="">Tous les rôles</option>
                        <option value="stagiaire" <?= $role_filter === 'stagiaire' ? 'selected' : '' ?>>Stagiaire</option>
                        <option value="mentor" <?= $role_filter === 'mentor' ? 'selected' : '' ?>>Mentor</option>
                        <option value="formateur" <?= $role_filter === 'formateur' ? 'selected' : '' ?>>Formateur</option>
                        <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <select name="valide" class="filter-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="1" <?= $valide_filter === '1' ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= $valide_filter === '0' ? 'selected' : '' ?>>En attente</option>
                    </select>
                    <button type="submit" class="btn primary">Filtrer</button>
                    <a href="utilisateurs.php" class="btn secondary">Réinitialiser</a>
                </form>
            </div>
        </div>

        <table class="validations-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Filière</th>
                    <th>Score</th>
                    <th>Inscription</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="8">Aucun utilisateur trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                <?php
                $role_label = match ($u['role']) {
                    'stagiaire' => 'Stagiaire',
                    'mentor' => 'Mentor',
                    'formateur' => 'Formateur',
                    'admin' => 'Admin',
                    default => ucfirst($u['role'])
                };
                $statut_label = $u['valide'] ? 'Actif' : 'Suspendu';
                $statut_class = $u['valide'] ? 'status-badge validated' : 'status-badge rejected';
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $role_label ?></td>
                    <td><?= htmlspecialchars($u['filiere'] ?? '-') ?></td>
                    <td><?= (int)$u['score'] ?></td>
                    <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                    <td><span class="<?= $statut_class ?>"><?= $statut_label ?></span></td>
                    <td>
                        <form method="POST" action="changer_statut.php" style="display:inline;">
                            <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                            <?php if (!$u['valide']): ?>
                            <button type="submit" name="action" value="valider" class="btn primary" style="padding:4px 12px;font-size:12px;">Activer</button>
                            <?php else: ?>
                            <button type="submit" name="action" value="suspendre" class="btn secondary" style="padding:4px 12px;font-size:12px;">Suspendre</button>
                            <?php endif; ?>
                        </form>
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
