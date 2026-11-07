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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_competence'])) {
    extract($_POST);
    $nom = trim(isset($nom) ? $nom : '');
    $description = trim(isset($description) ? $description : '');
    $categorie = trim(isset($categorie) ? $categorie : '');
    if (!empty($nom)) {
        $stmt = $db->prepare("INSERT INTO competences (nom, description, categorie) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $description, $categorie]);
    }
    header('Location: competences.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_competence'])) {
    extract($_POST);
    $id_competence = (int)$id_competence;
    $stmt = $db->prepare("DELETE FROM competences WHERE id_competence = ?");
    $stmt->execute([$id_competence]);
    header('Location: competences.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_competence'])) {
    extract($_POST);
    $id_competence = (int)$id_competence;
    $nom = trim(isset($nom) ? $nom : '');
    $description = trim(isset($description) ? $description : '');
    $categorie = trim(isset($categorie) ? $categorie : '');
    if (!empty($nom)) {
        $stmt = $db->prepare("UPDATE competences SET nom = ?, description = ?, categorie = ? WHERE id_competence = ?");
        $stmt->execute([$nom, $description, $categorie, $id_competence]);
    }
    header('Location: competences.php');
    exit;
}

$stmt = $db->query("SELECT * FROM competences ORDER BY nom ASC");
$competences = $stmt->fetchAll();

$stmt = $db->query("SELECT id_competence FROM competences");
$total_competences = count($stmt->fetchAll());

$stmt = $db->query("SELECT DISTINCT categorie FROM competences WHERE categorie IS NOT NULL AND categorie != ''");
$total_categories = count($stmt->fetchAll());

$edit_competence = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM competences WHERE id_competence = ?");
    $stmt->execute([$id_edit]);
    $edit_competence = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Compétences</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="/phpprojet/admin/badge/badges.css">
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
            <a href="/phpprojet/admin/competences/competences.php" class="nav-item<?= strpos($current_url,'competences')!==false?' active':'' ?>"><i class="fas fa-brain"></i><span>Compétences</span></a>
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
            <h2>Gestion des Compétences</h2>
            <p class="subtitle">Ajoutez, modifiez ou supprimez des compétences</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Total Compétences</h3>
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="stat-value"><?= $total_competences ?></div>
                    <div class="stat-change">Compétences enregistrées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Catégories</h3>
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-value"><?= $total_categories ?></div>
                    <div class="stat-change">Catégories distinctes</div>
                </div>
            </div>

            <section class="competences-section assign-section">
                <h3><?= $edit_competence ? 'Modifier la compétence' : 'Ajouter une compétence' ?></h3>
                <form method="POST" class="assign-form">
                    <div class="assign-row">
                        <div class="assign-field">
                            <label>Nom</label>
                            <input type="text" name="nom" class="filter-select" value="<?= htmlspecialchars($edit_competence['nom'] ?? '') ?>" required>
                        </div>
                        <div class="assign-field">
                            <label>Catégorie</label>
                            <input type="text" name="categorie" class="filter-select" value="<?= htmlspecialchars($edit_competence['categorie'] ?? '') ?>">
                        </div>
                        <div class="assign-field" style="flex:2">
                            <label>Description</label>
                            <input type="text" name="description" class="filter-select" value="<?= htmlspecialchars($edit_competence['description'] ?? '') ?>">
                        </div>
                        <div class="assign-field assign-btn-wrap">
                            <label>&nbsp;</label>
                            <?php if ($edit_competence): ?>
                            <input type="hidden" name="id_competence" value="<?= $edit_competence['id_competence'] ?>">
                            <button type="submit" name="modifier_competence" class="btn-assign"><i class="fas fa-save"></i> Modifier</button>
                            <a href="competences.php" class="btn-assign" style="display:inline-block;text-align:center;padding:8px 16px;background:#6b7280;color:#fff;text-decoration:none;border-radius:6px">Annuler</a>
                            <?php else: ?>
                            <button type="submit" name="ajouter_competence" class="btn-assign"><i class="fas fa-plus"></i> Ajouter</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </section>

            <section class="competences-section">
                <h3>Liste des compétences</h3>
                <table class="validations-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($competences)): ?>
                        <tr><td colspan="4">Aucune compétence.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($competences as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($c['categorie'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                            <td>
                                <a href="?edit=<?= $c['id_competence'] ?>" class="btn-assign" style="display:inline-block;text-align:center;padding:8px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-size:13px"><i class="fas fa-edit"></i> Modifier</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Confirmer la suppression de cette compétence ?')">
                                    <input type="hidden" name="id_competence" value="<?= $c['id_competence'] ?>">
                                    <button type="submit" name="supprimer_competence" class="btn-assign" style="background:#dc2625;font-size:13px"><i class="fas fa-trash"></i> Supprimer</button>
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
