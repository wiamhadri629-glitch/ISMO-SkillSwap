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

$stmt = $db->prepare("SELECT 1 FROM propositions_aide WHERE id_formateur = ?");
$stmt->execute([$id_user]);
$total_propositions = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT 1 FROM aides WHERE id_formateur = ? AND statut = 'validee'");
$stmt->execute([$id_user]);
$total_aides = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT ae.note_mentor FROM aide_effectuee ae JOIN demandes_aide da ON ae.id_demande = da.id_demande WHERE da.id_user = ? AND ae.note_mentor IS NOT NULL");
$stmt->execute([$id_user]);
$profile_notes = $stmt->fetchAll(PDO::FETCH_COLUMN);
$avg_note = count($profile_notes) > 0 ? number_format(array_sum($profile_notes) / count($profile_notes), 1) : '—';
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
    <aside class="sidebar sidebar-formateur">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/phpprojet/formateur/accueil/accueil.php" class="nav-item<?= strpos($current_url,'accueil')!==false?' active':'' ?>"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="/phpprojet/formateur/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/formateur/profile/profile.php" class="nav-item<?= strpos($current_url,'profile')!==false?' active':'' ?>"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="/phpprojet/formateur/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-passport"></i><span>Passport</span></a>
            <a href="/phpprojet/formateur/validation/validations.php" class="nav-item<?= strpos($current_url,'validation')!==false?' active':'' ?>"><i class="fas fa-check-circle"></i><span>Validations</span></a>
            <a href="/phpprojet/formateur/badge/badges.php" class="nav-item<?= strpos($current_url,'badge')!==false?' active':'' ?>"><i class="fas fa-medal"></i><span>Badges</span></a>
            <a href="/phpprojet/formateur/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
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
                    <span class="role-badge role-badge-formateur"><?= ucfirst($role) ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
                <div class="meta-item"><i class="fas fa-calendar"></i> Membre depuis <?= date("d/m/Y",strtotime($date_inscription)) ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/formateur/passport/passport.php" class="dropdown-action"><i class="fas fa-download"></i> Télécharger le Passeport (PDF)</a>
                <a href="/phpprojet/formateur/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> Voir le profil</a>
            </div>
            <div class="dropdown-signout">
                <a href="/phpprojet/login/deconnexion.php" class="dropdown-action signout"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
            </div>
        </div>
    </header>
    <main class="main-content">
        <h1 class="section-title">Profil</h1>

        <div class="profile-header-card">
            <div class="profile-avatar-big"><?= $init ?></div>
            <div>
                <h2><?= htmlspecialchars("$prenom $nom") ?></h2>
                <p class="role-badge profile-role"><?= ucfirst($role) ?></p>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></p>
                <p><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($filiere ?? 'Non renseignée') ?></p>
                <p><i class="fas fa-calendar"></i> Inscrit le <?= date('d/m/Y', strtotime($date_inscription)) ?></p>
            </div>
        </div>

        <section class="profile-stats-section">
            <h3 class="section-subtitle">Statistiques</h3>
            <div class="stat-row">
                <div class="stat-box">
                    <span><?= $total_propositions ?></span>
                    <p>Propositions envoyées</p>
                </div>
                <div class="stat-box">
                    <span><?= $total_aides ?></span>
                    <p>Aides validées</p>
                </div>
                <div class="stat-box">
                    <span><?= $avg_note ?>/5</span>
                    <p>Note moyenne reçue</p>
                </div>
            </div>
        </section>
        <a href="modifier_profile.php" class="btn primary" style="margin-top:20px;display:inline-block;">Modifier profil</a>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
