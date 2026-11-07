<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
extract($_SESSION);
if ($role !== 'formateur') {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$init = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
$current_url = $_SERVER["PHP_SELF"];

require_once __DIR__ . '/../../dbopen/db.php';

$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_demande'])) {
    extract($_POST);
    $id = (int)$id_demande;
    $action = isset($action) ? $action : '';

    if ($action === 'approuver') {
        $stmt = $db->prepare("SELECT id_stagiaire FROM demandes_promotion WHERE id = ? AND statut = 'en_attente'");
        $stmt->execute([$id]);
        $demande = $stmt->fetch();

        if ($demande) {
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("UPDATE demandes_promotion SET statut = 'acceptee', date_traitement = NOW(), id_formateur = ? WHERE id = ? AND statut = 'en_attente'");
                $stmt->execute([$id_user, $id]);

                $stmt = $db->prepare("UPDATE utilisateurs SET role = 'mentor', valide = 1 WHERE id_user = ?");
                $stmt->execute([$demande['id_stagiaire']]);

                $db->commit();
                header('Location: promotions.php');
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                header('Location: promotions.php');
                exit;
            }
        }
    } elseif ($action === 'refuser') {
        $stmt = $db->prepare("UPDATE demandes_promotion SET statut = 'refusee', date_traitement = NOW(), id_formateur = ? WHERE id = ? AND statut = 'en_attente'");
        $stmt->execute([$id_user, $id]);
        header('Location: promotions.php');
        exit;
    }
}

$stmt = $db->prepare("
    SELECT dp.*, u.nom, u.prenom, u.email, u.photo
    FROM demandes_promotion dp
    JOIN utilisateurs u ON dp.id_stagiaire = u.id_user
    WHERE dp.statut = 'en_attente'
    ORDER BY dp.date_demande DESC
");
$stmt->execute();
$demandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Promotions</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
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
        <h2>Demandes de Promotion</h2>
        <p class="subtitle">Examinez et approuvez les demandes de passage de stagiaire à mentor</p>

        <?php if ($msg): ?>
        <div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
        <?php endif; ?>

        <table class="validations-table">
            <thead>
                <tr>
                    <th>Stagiaire</th>
                    <th>Aides effectuées</th>
                    <th>Note moyenne</th>
                    <th>Date de demande</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($demandes)): ?>
                <tr><td colspan="5">Aucune demande de promotion en attente.</td></tr>
                <?php endif; ?>
                <?php foreach ($demandes as $d): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></strong></td>
                    <td><?= (int)$d['nb_aides'] ?></td>
                    <td><?= number_format((float)$d['note_moyenne'], 2) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($d['date_demande'])) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_demande" value="<?= $d['id'] ?>">
                            <button type="submit" name="action" value="approuver" class="btn primary" style="padding:4px 12px;font-size:12px;" onclick="return confirm('Approuver cette demande ? Le stagiaire deviendra mentor.')">Approuver</button>
                            <button type="submit" name="action" value="refuser" class="btn secondary" style="padding:4px 12px;font-size:12px;" onclick="return confirm('Refuser cette demande ?')">Refuser</button>
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
