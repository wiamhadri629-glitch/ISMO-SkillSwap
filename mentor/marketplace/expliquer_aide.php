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

$id_aide = 0;
if (isset($_GET['id_aide'])) { $id_aide = (int)$_GET['id_aide']; }
elseif (isset($_POST['id_aide'])) { $id_aide = (int)$_POST['id_aide']; }
$error = '';

if ($id_aide <= 0) {
    header("Location: marketplace.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expliquer'])) {
    extract($_POST);
    $explication = trim($explication ?? '');

    if (empty($explication)) {
        $error = 'Veuillez écrire une explication.';
    } else {
        $stmt = $db->prepare("UPDATE aides SET explication = ? WHERE id = ? AND id_formateur = ? AND statut = 'validee'");
        $stmt->execute([$explication, $id_aide, $id_user]);
        header("Location: marketplace.php");
        exit;
    }
}

$stmt = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a JOIN utilisateurs u ON a.id_stagiaire_validateur = u.id_user WHERE a.id = ? AND a.id_formateur = ? AND a.statut = 'validee'");
$stmt->execute([$id_aide, $id_user]);
$a = $stmt->fetch();

if (!$a) {
    header("Location: marketplace.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Expliquer mon aide</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="marketplace.css">
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
        <h1 class="section-title">Expliquer mon aide</h1>

        <?php if ($error): ?>
        <div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <p class="status-pill validee">Validée</p>
                    <h2 style="margin:8px 0;"><?= htmlspecialchars($a['titre']) ?></h2>
                </div>
            </div>
            <p style="color:#475569;"><?= htmlspecialchars($a['description']) ?></p>
            <p style="color:#475569;font-size:13px;margin-top:12px;">
                <i class="fas fa-user-check"></i> Validée par <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                le <?= date('d/m/Y', strtotime($a['date_validation'])) ?>
            </p>
        </div>

        <div class="card" style="padding:24px;">
            <form method="POST">
                <input type="hidden" name="id_aide" value="<?= $id_aide ?>">
                <div class="form-group">
                    <label>Expliquez qui vous avez aidé et ce qui a été fait <span style="color:#ef4444;">*</span></label>
                    <textarea name="explication" rows="6" placeholder="Ex : J'ai aidé Ahmed sur les boucles PHP..." required><?= htmlspecialchars($a['explication'] ?? '') ?></textarea>
                </div>
                <div style="margin-top:20px;">
                    <button type="submit" name="expliquer" class="btn primary">
                        <i class="fas fa-pen"></i> Enregistrer l'explication
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
