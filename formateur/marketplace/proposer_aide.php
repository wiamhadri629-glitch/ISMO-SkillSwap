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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);
    $id_demande = (int) ($id_demande ?? 0);
    $message    = trim($message ?? '');

    if (!$id_demande) {
        $error = 'Demande introuvable.';
    } elseif (empty($message)) {
        $error = 'Veuillez décrire votre proposition.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO propositions_aide (id_demande, id_formateur, message, date_proposition, statut)
            VALUES (?, ?, ?, NOW(), 'en_attente')
        ");
        $stmt->execute([$id_demande, $id_user, $message]);

        $success = 'Votre aide a été proposée avec succès !';
    }
}

$demande_id = (int) ($_GET['demande'] ?? 0);
$demande = null;
if ($demande_id) {
    $stmt = $db->prepare("SELECT d.*, u.nom, u.prenom FROM demandes_aide d JOIN utilisateurs u ON d.id_user = u.id_user WHERE d.id_demande = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch();
}

$aides_from_me = $db->prepare("SELECT a.*, d.titre FROM aides a LEFT JOIN demandes_aide d ON a.id_stagiaire_validateur = d.id_user WHERE a.id_formateur = ? AND a.statut = 'validee' ORDER BY a.date_validation DESC");
$aides_from_me->execute([$id_user]);
$aides_from_me = $aides_from_me->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Proposer une aide</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="marketplace.css">
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
            <a href="/phpprojet/formateur/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false&&strpos($current_url,'historique')===false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/formateur/marketplace/historique.php" class="nav-item<?= strpos($current_url,'historique')!==false?' active':'' ?>"><i class="fas fa-clock-rotate"></i><span>Historique</span></a>
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
        <h1 class="section-title">Proposer une aide</h1>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($demande): ?>
        <div class="demande-preview card">
            <h2><?= htmlspecialchars($demande['titre']) ?></h2>
            <p><strong>Par :</strong> <?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></p>
            <p><?= htmlspecialchars($demande['description']) ?></p>
        </div>

        <form method="POST" class="form-proposition" style="margin-top:20px;">
            <input type="hidden" name="id_demande" value="<?= $demande['id_demande'] ?>">
            <div class="form-group">
                <label for="message">Votre proposition</label>
                <textarea name="message" id="message" rows="6" required placeholder="Expliquez comment vous pouvez aider..."></textarea>
            </div>
            <button type="submit" class="btn primary">Envoyer la proposition</button>
        </form>
        <?php elseif (!$success): ?>
        <p>Aucune demande sélectionnée. <a href="marketplace.php">Retour au marketplace</a></p>
        <?php endif; ?>

        <?php if (count($aides_from_me) > 0): ?>
        <h2 class="section-subtitle" style="margin-top:40px;">Mes aides validées</h2>
        <div class="market-grid">
            <?php foreach ($aides_from_me as $a): ?>
            <article class="market-card">
                <div class="card-head">
                    <p class="status-pill validee">Validée</p>
                    <h2><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                </div>
                <div class="card-footer">
                    <small>Validée le <?= date('d/m/Y', strtotime($a['date_validation'])) ?></small>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
