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

$id_demande = (int)($_GET['id_demande'] ?? 0);
$error = '';
$success = '';

if ($id_demande <= 0) {
    header("Location: marketplace.php");
    exit;
}

$stmt = $db->prepare("SELECT id_user, titre, description, status FROM demandes_aide WHERE id_demande = ?");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header("Location: marketplace.php");
    exit;
}

if ($demande['id_user'] == $id_user) {
    header("Location: marketplace.php");
    exit;
}

if ($demande['status'] !== 'ouverte') {
    header("Location: marketplace.php");
    exit;
}

$stmt = $db->prepare("SELECT id FROM propositions_aide WHERE id_demande = ? AND id_mentor = ?");
$stmt->execute([$id_demande, $id_user]);
if ($stmt->fetch()) {
    header("Location: marketplace.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposer'])) {
    extract($_POST);
    $message = trim($message ?? '');

    if (empty($message)) {
        $error = 'Veuillez écrire une explication pour votre proposition.';
    } else {
        $stmt = $db->prepare("INSERT INTO propositions_aide (id_demande, id_mentor, message, status) VALUES (?, ?, ?, 'en_attente')");
        $stmt->execute([$id_demande, $id_user, $message]);
        header("Location: marketplace.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Proposer mon aide</title>
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
            <a href="/phpprojet/mentor/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false&&strpos($current_url,'historique')===false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/mentor/marketplace/historique.php" class="nav-item<?= strpos($current_url,'historique')!==false?' active':'' ?>"><i class="fas fa-clock-rotate"></i><span>Historique</span></a>
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
        <div class="market-card header-card">
            <p class="eyebrow">Marketplace</p>
            <h1>Proposer mon aide</h1>
        </div>

        <?php if ($error): ?>
        <div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="market-card" style="margin-bottom:20px;">
            <div class="card-head">
                <div>
                    <p class="status-pill open">Demande</p>
                    <h2><?= htmlspecialchars($demande['titre']) ?></h2>
                </div>
            </div>
            <p class="card-copy"><?= htmlspecialchars($demande['description']) ?></p>
        </div>

        <div class="market-card form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Votre explication <span style="color:#ef4444;">*</span></label>
                    <textarea name="message" rows="6" placeholder="Expliquez comment vous comptez aider le stagiaire, les notions que vous allez aborder, etc." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="proposer" class="btn primary full">
                        <i class="fas fa-paper-plane"></i> Envoyer ma proposition
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
