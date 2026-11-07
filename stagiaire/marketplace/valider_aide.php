<?php
session_start();
if(!isset($_SESSION['id_user'])){ header('Location: /phpprojet/login/index.php'); exit; }
extract($_SESSION);

$init = strtoupper(substr(isset($prenom) ? $prenom : '', 0, 1) . substr(isset($nom) ? $nom : '', 0, 1));
$current_url = $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

require_once __DIR__ . '/../../dbopen/db.php';

$id_demande = (int)($_GET['id_demande'] ?? 0);
$msg = $_GET['msg'] ?? '';
$error = '';

if ($id_demande <= 0) {
    header("Location: marketplace.php");
    exit;
}

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

$stmt = $db->prepare("SELECT p.*, u.nom, u.prenom, u.photo FROM propositions_aide p JOIN utilisateurs u ON p.id_mentor = u.id_user WHERE p.id_demande = ? AND p.status = 'en_attente' ORDER BY p.id ASC");
$stmt->execute([$id_demande]);
$propositions = $stmt->fetchAll();

$stmt = $db->prepare("SELECT p.*, u.nom, u.prenom FROM propositions_aide p JOIN utilisateurs u ON p.id_mentor = u.id_user WHERE p.id_demande = ? AND p.status = 'acceptee' LIMIT 1");
$stmt->execute([$id_demande]);
$acceptee = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_proposition'])) {
    extract($_POST);
    $id_proposition = (int)$id_proposition;

    $stmt = $db->prepare("SELECT id, id_mentor FROM propositions_aide WHERE id = ? AND id_demande = ? AND status = 'en_attente'");
    $stmt->execute([$id_proposition, $id_demande]);
    $proposition = $stmt->fetch();

    if (!$proposition) {
        $error = "Cette proposition n'est plus disponible.";
    } else {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE propositions_aide SET status = 'acceptee' WHERE id = ?");
            $stmt->execute([$id_proposition]);

            $stmt = $db->prepare("UPDATE demandes_aide SET status = 'resolue' WHERE id_demande = ?");
            $stmt->execute([$id_demande]);

            $stmt = $db->prepare("UPDATE utilisateurs SET score = score + 10 WHERE id_user = ?");
            $stmt->execute([$proposition['id_mentor']]);

            $db->commit();
            header("Location: valider_aide.php?id_demande=$id_demande");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Une erreur est survenue lors de la validation.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Valider l'aide</title>
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
            <a href="/phpprojet/stagiaire/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false&&strpos($current_url,'valider')===false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
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
<div class="market-card header-card">
    <p class="eyebrow">Marketplace</p>
    <h1>Valider l'aide</h1>
</div>

<?php if ($msg): ?>
<div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="market-card" style="margin-bottom:20px;">
    <div class="card-head">
        <div>
            <p class="status-pill <?= $demande['status'] === 'resolue' ? 'resolved' : 'open' ?>"><?= $demande['status'] === 'resolue' ? 'Résolu' : 'Ouvert' ?></p>
            <h2><?= htmlspecialchars($demande['titre']) ?></h2>
        </div>
    </div>
    <p class="card-copy"><?= htmlspecialchars($demande['description']) ?></p>
</div>

<?php if ($acceptee): ?>
<div class="market-card" style="margin-bottom:20px;border-left:4px solid #16a34a;">
    <div class="card-head">
        <div>
            <p class="status-pill validee" style="background:#16a34a;">Validée</p>
            <h2>Aide validée</h2>
        </div>
    </div>
    <p class="card-copy">Cette demande a déjà été validée. Le mentor <strong><?= htmlspecialchars($acceptee['prenom'] . ' ' . $acceptee['nom']) ?></strong> a été notifié.</p>
    <div class="card-footer">
        <a href="marketplace.php" class="btn secondary">Retour au marketplace</a>
    </div>
</div>
<?php elseif (empty($propositions)): ?>
<div class="market-card" style="margin-bottom:20px;">
    <p class="card-copy" style="text-align:center;padding:20px;">Aucune proposition en attente pour cette demande.</p>
    <div class="card-footer">
        <a href="marketplace.php" class="btn secondary">Retour au marketplace</a>
    </div>
</div>
<?php else: ?>
<div class="aides-section">
    <h2 class="section-subtitle"><i class="fas fa-handshake-angle"></i> Propositions reçues <span class="badge-count"><?= count($propositions) ?></span></h2>
    <p style="color:var(--muted);margin-bottom:20px;">Lisez attentivement l'explication du mentor, puis validez si vous avez compris l'aide proposée.</p>

    <?php foreach ($propositions as $p):
        $mentor_init = strtoupper(substr($p['prenom'], 0, 1) . substr($p['nom'], 0, 1));
    ?>
    <div class="market-card" style="margin-bottom:16px;">
        <div class="card-head">
            <div>
                <p class="status-pill proposee">Proposition</p>
                <h2>Explication de <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></h2>
            </div>
        </div>
        <div class="author" style="margin-bottom:16px;">
            <span class="avatar-sm"><?= htmlspecialchars($mentor_init) ?></span>
            <div>
                <strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong>
            </div>
        </div>
        <div style="background:#f8fafc;padding:16px;border-radius:10px;margin-bottom:16px;border:1px solid #e2e8f0;">
            <p style="white-space:pre-wrap;margin:0;line-height:1.7;"><?= htmlspecialchars($p['message'] ?? '') ?></p>
        </div>
        <div class="card-footer">
            <form method="POST" onsubmit="return confirm('Confirmez-vous avoir lu et compris l\'explication du mentor ? Cette action validera l\'aide et clôturera la demande.')">
                <input type="hidden" name="id_proposition" value="<?= $p['id'] ?>">
                <button type="submit" name="valider_proposition" class="btn primary" style="padding:10px 20px;font-size:13px;background:#16a34a;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-check"></i> Valider - J'ai compris l'explication
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
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
