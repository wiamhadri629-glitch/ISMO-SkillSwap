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

$demandes = $db->prepare("
    SELECT d.*, u.nom, u.prenom, ae.note_mentor, ae.commentaire, ae.date_intervention,
           m.nom AS mentor_nom, m.prenom AS mentor_prenom
    FROM demandes_aide d
    JOIN utilisateurs u ON d.id_user = u.id_user
    LEFT JOIN aide_effectuee ae ON d.id_demande = ae.id_demande
    LEFT JOIN utilisateurs m ON ae.id_mentor = m.id_user
    WHERE d.status = 'resolue'
    ORDER BY COALESCE(ae.date_intervention, d.date_pub) DESC
");
$demandes->execute();
$demandes = $demandes->fetchAll();

$aides = $db->prepare("
    SELECT a.*, f.nom AS formateur_nom, f.prenom AS formateur_prenom,
           v.nom AS validateur_nom, v.prenom AS validateur_prenom
    FROM aides a
    JOIN utilisateurs f ON a.id_formateur = f.id_user
    LEFT JOIN utilisateurs v ON a.id_stagiaire_validateur = v.id_user
    WHERE a.statut = 'validee'
    ORDER BY a.date_validation DESC
");
$aides->execute();
$aides = $aides->fetchAll();

$total_entries = count($demandes) + count($aides);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Historique des Aides</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="marketplace.css">
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
        <div class="topbar-actions">
            <div class="topbar-left">
                <h1 class="section-title">Historique des Aides</h1>
                <a href="marketplace.php" class="btn secondary" style="text-decoration:none;"><i class="fas fa-arrow-left"></i> Retour au Marketplace</a>
            </div>
        </div>

        <?php if ($total_entries === 0): ?>
        <div style="text-align:center;padding:60px 20px;color:#64748b;">
            <i class="fas fa-clock-rotate-left" style="font-size:48px;margin-bottom:16px;opacity:0.5;"></i>
            <p style="font-size:18px;">Aucun historique pour le moment.</p>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
            <span style="background:#dcfce7;color:#166534;padding:10px 18px;border-radius:999px;font-size:14px;font-weight:600;">
                <i class="fas fa-circle-check"></i> Demandes résolues: <?= count($demandes) ?>
            </span>
            <span style="background:#f0fdf4;color:#166534;padding:10px 18px;border-radius:999px;font-size:14px;font-weight:600;">
                <i class="fas fa-check-circle"></i> Aides validées: <?= count($aides) ?>
            </span>
        </div>

        <?php if (count($demandes) > 0): ?>
        <div class="aides-section">
            <h2 class="section-subtitle"><i class="fas fa-circle-check"></i> Demandes résolues <span class="badge-count"><?= count($demandes) ?></span></h2>
            <div class="market-grid">
                <?php foreach ($demandes as $d):
                    $date = $d['date_intervention'] ?: $d['date_pub'];
                    $author_init = strtoupper(substr($d['prenom'], 0, 1) . substr($d['nom'], 0, 1));
                    $mentor_init = $d['mentor_nom'] ? strtoupper(substr($d['mentor_prenom'], 0, 1) . substr($d['mentor_nom'], 0, 1)) : '';
                ?>
                <article class="market-card">
                    <div class="card-head">
                        <div>
                            <p class="status-pill resolved">Résolue</p>
                            <h2 class="card-title"><?= htmlspecialchars($d['titre']) ?></h2>
                        </div>
                    </div>
                    <p class="card-copy"><?= htmlspecialchars(mb_substr($d['description'], 0, 200)) ?></p>
                    <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#64748b;">
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($date)) ?></span>
                        <?php if ($d['note_mentor']): ?>
                        <span><i class="fas fa-star" style="color:#f59e0b;"></i> Note: <?= $d['note_mentor'] ?>/5</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($d['commentaire']): ?>
                    <div style="background:#f8fafc;padding:12px 16px;border-radius:12px;font-size:13px;color:#475569;border:1px solid #e5e7eb;">
                        <i class="fas fa-quote-left" style="color:#94a3b8;margin-right:6px;"></i><?= htmlspecialchars($d['commentaire']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="card-footer" style="border-top:1px solid #f1f5f9;padding-top:14px;flex-wrap:wrap;">
                        <div class="author">
                            <span class="avatar-sm"><?= htmlspecialchars($author_init) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></strong>
                                <small>Demandeur</small>
                            </div>
                        </div>
                        <?php if ($d['mentor_nom']): ?>
                        <div class="author">
                            <span class="avatar-sm" style="background:#8b5cf6;"><?= htmlspecialchars($mentor_init) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($d['mentor_prenom'] . ' ' . $d['mentor_nom']) ?></strong>
                                <small>Mentor</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($aides) > 0): ?>
        <div class="aides-section">
            <h2 class="section-subtitle"><i class="fas fa-check-circle"></i> Aides validées <span class="badge-count"><?= count($aides) ?></span></h2>
            <div class="market-grid">
                <?php foreach ($aides as $a):
                    $f_init = strtoupper(substr($a['formateur_prenom'], 0, 1) . substr($a['formateur_nom'], 0, 1));
                    $v_init = $a['validateur_nom'] ? strtoupper(substr($a['validateur_prenom'], 0, 1) . substr($a['validateur_nom'], 0, 1)) : '';
                ?>
                <article class="market-card">
                    <div class="card-head">
                        <div>
                            <p class="status-pill validee">Validée</p>
                            <h2 class="card-title"><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                        </div>
                    </div>
                    <p class="card-copy"><?= htmlspecialchars($a['description'] ?? '') ?></p>
                    <?php if ($a['explication']): ?>
                    <div style="background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:10px;font-size:13px;">
                        <i class="fas fa-quote-left"></i> <?= htmlspecialchars($a['explication']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="card-footer" style="border-top:1px solid #f1f5f9;padding-top:14px;flex-wrap:wrap;">
                        <div class="author">
                            <span class="avatar-sm" style="background:var(--purple);"><?= htmlspecialchars($f_init) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($a['formateur_prenom'] . ' ' . $a['formateur_nom']) ?></strong>
                                <small>Formateur</small>
                            </div>
                        </div>
                        <?php if ($a['validateur_nom']): ?>
                        <div class="author">
                            <span class="avatar-sm" style="background:#16a34a;"><?= htmlspecialchars($v_init) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($a['validateur_prenom'] . ' ' . $a['validateur_nom']) ?></strong>
                                <small>Validée le <?= date('d/m/Y', strtotime($a['date_validation'])) ?></small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
