<?php
session_start();
if(!isset($_SESSION['id_user'])){ header('Location: /phpprojet/login/index.php'); exit; }
extract($_SESSION);

$init = strtoupper(substr(isset($prenom) ? $prenom : '', 0, 1) . substr(isset($nom) ? $nom : '', 0, 1));
$current_url = $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

require_once __DIR__ . '/../../dbopen/db.php';

$id_demande = (int)($_GET['id_demande'] ?? 0);
$error = '';
$success = '';

$stmt = $db->prepare("SELECT d.*, u.nom, u.prenom FROM demandes_aide d JOIN utilisateurs u ON d.id_user = u.id_user WHERE d.id_demande = ?");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header("Location: marketplace.php");
    exit;
}

if ($demande['id_user'] != $id_user) {
    header("Location: marketplace.php");
    exit;
}

$stmt = $db->prepare("SELECT id FROM propositions_aide WHERE id_demande = ? AND status = 'acceptee' LIMIT 1");
$stmt->execute([$id_demande]);
$proposition = $stmt->fetch();

$stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_demande = ?");
$stmt->execute([$id_demande]);
$already_rated = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['noter'])) {
    extract($_POST);
    $note = (int)(isset($note) ? $note : 0);
    $commentaire = trim(isset($commentaire) ? $commentaire : '');

    if ($note < 1 || $note > 5) {
        $error = 'La note doit être comprise entre 1 et 5.';
    } elseif (!$proposition) {
        $error = 'Aucun mentor accepté pour cette demande.';
    } else {
        $stmt = $db->prepare("SELECT id_mentor FROM propositions_aide WHERE id_demande = ? AND status = 'acceptee' LIMIT 1");
        $stmt->execute([$id_demande]);
        $mentor = $stmt->fetchColumn();

        if ($already_rated) {
            $stmt = $db->prepare("UPDATE aide_effectuee SET note_mentor = ?, commentaire = ? WHERE id_demande = ?");
            $stmt->execute([$note, $commentaire, $id_demande]);
        } else {
            $stmt = $db->prepare("INSERT INTO aide_effectuee (id_demande, id_mentor, date_intervention, note_mentor, commentaire) VALUES (?, ?, NOW(), ?, ?)");
            $stmt->execute([$id_demande, $mentor, $note, $commentaire]);
        }

        if ($demande['status'] !== 'resolue') {
            $stmt = $db->prepare("UPDATE demandes_aide SET status = 'resolue' WHERE id_demande = ?");
            $stmt->execute([$id_demande]);

            $stmt = $db->prepare("UPDATE utilisateurs SET score = score + 10 WHERE id_user = ?");
            $stmt->execute([$mentor]);
        }

        $success = 'Merci ! Votre note a été enregistrée.';
    }
}

if ($already_rated) {
    $stmt = $db->prepare("SELECT note_mentor, commentaire FROM aide_effectuee WHERE id_demande = ?");
    $stmt->execute([$id_demande]);
    $rating = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Noter le mentor</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="/phpprojet/css/marketplace.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar sidebar-stagiaire">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <h1>ISMO-SkillSwap</h1>
                <p>ISMO Tétouan</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/phpprojet/stagiaire/accueil/accueil.php" class="nav-item<?= strpos($current_url,'accueil')!==false?' active':'' ?>"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="/phpprojet/stagiaire/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/stagiaire/marketplace/historique.php" class="nav-item"><i class="fas fa-clock-rotate"></i><span>Historique</span></a>
            <a href="/phpprojet/stagiaire/profile/profile.php" class="nav-item<?= strpos($current_url,'profile')!==false?' active':'' ?>"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="/phpprojet/stagiaire/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-passport"></i><span>Passport</span></a>
            <a href="/phpprojet/stagiaire/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?= htmlspecialchars($init) ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars("$prenom $nom") ?></p>
                    <p class="user-role"><?= ucfirst(isset($role) ? $role : 'stagiaire') ?></p>
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
                <div class="avatar-large"><?= htmlspecialchars($init) ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars("$prenom $nom") ?></p>
                    <p class="user-role"><?= ucfirst(isset($role) ? $role : 'stagiaire') ?></p>
                </div>
                <i class="fas fa-chevron-down" id="chevronIcon"></i>
            </div>
        </div>
        <div class="profile-dropdown" id="profileMenu">
            <div class="dropdown-header">
                <div class="dropdown-avatar"><?= htmlspecialchars($init) ?></div>
                <div class="dropdown-user-info">
                    <h4><?= htmlspecialchars("$prenom $nom") ?></h4>
                    <p>Platform <?= ucfirst(isset($role) ? $role : 'stagiaire') ?></p>
                    <span class="role-badge role-badge-stagiaire"><?= ucfirst(isset($role) ? $role : 'stagiaire') ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars(isset($email) ? $email : '') ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/stagiaire/passport/passport.php" class="dropdown-action"><i class="fas fa-download"></i> Download Skills Passport (PDF)</a>
                <a href="/phpprojet/stagiaire/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> View Profile</a>
                <a href="/phpprojet/stagiaire/profile/modifier_profile.php" class="dropdown-action"><i class="fas fa-gear"></i> Settings</a>
            </div>
            <div class="dropdown-signout">
                <a href="/phpprojet/login/index.php" class="dropdown-action signout"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
            </div>
        </div>
    </header>
    <main class="main-content">
<div class="topbar-actions">
  <div class="topbar-left">
    <h1 class="section-title">Noter le mentor</h1>
  </div>
</div>
<p style="margin-bottom:20px;color:#64748b;">Demande : <strong><?= htmlspecialchars($demande['titre']) ?></strong> — <a href="marketplace.php" style="color:#2563eb;">Retour au marketplace</a></p>

<?php if ($success): ?>
<div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!$success): ?>
<div class="market-card form-card">
    <form method="POST">
        <div class="form-group">
            <label>Note (1 à 5)</label>
            <select name="note" required>
                <option value="">Choisir...</option>
                <option value="1">1 - Très insuffisant</option>
                <option value="2">2 - Insuffisant</option>
                <option value="3">3 - Moyen</option>
                <option value="4">4 - Bien</option>
                <option value="5">5 - Excellent</option>
            </select>
        </div>
        <div class="form-group">
            <label>Commentaire</label>
            <textarea name="commentaire" rows="4" placeholder="Votre avis sur l'aide reçue..."></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" name="noter" class="btn primary">
                <i class="fas fa-star"></i> Noter et marquer comme résolu
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
    </main>
</div>
<script>
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu   = document.getElementById('profileMenu');
    const chevronIcon   = document.getElementById('chevronIcon');
    profileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle('open');
      chevronIcon.classList.toggle('rotated');
    });
    document.addEventListener('click', (e) => {
      if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.classList.remove('open');
        chevronIcon.classList.remove('rotated');
      }
    });
</script>
<script src="/phpprojet/js/profile-menu.js"></script>
</body>
</html>
