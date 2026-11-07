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

$interventions = $db->prepare("
    SELECT ae.*, d.titre, d.description, d.date_pub,
           u.nom, u.prenom
    FROM aide_effectuee ae
    JOIN demandes_aide d ON ae.id_demande = d.id_demande
    JOIN utilisateurs u ON d.id_user = u.id_user
    WHERE ae.id_mentor = ?
    ORDER BY ae.date_intervention DESC
");
$interventions->execute([$id_user]);
$interventions = $interventions->fetchAll();

$aides = $db->prepare("
    SELECT a.*, v.nom AS v_nom, v.prenom AS v_prenom
    FROM aides a
    LEFT JOIN utilisateurs v ON a.id_stagiaire_validateur = v.id_user
    WHERE a.id_formateur = ? AND a.statut = 'validee'
    ORDER BY a.date_validation DESC
");
$aides->execute([$id_user]);
$aides = $aides->fetchAll();

$total_entries = count($interventions) + count($aides);
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
            <p style="font-size:14px;">Vos interventions et aides validées apparaîtront ici.</p>
        </div>
        <?php endif; ?>

        <?php if (count($interventions) > 0): ?>
        <div class="aides-section">
            <h2 class="section-subtitle"><i class="fas fa-handshake-angle"></i> Interventions effectuées <span class="badge-count"><?= count($interventions) ?></span></h2>
            <div class="market-grid">
                <?php foreach ($interventions as $iv):
                    $author_init = strtoupper(substr($iv['prenom'], 0, 1) . substr($iv['nom'], 0, 1));
                ?>
                <article class="market-card">
                    <div class="card-head">
                        <div>
                            <p class="status-pill resolved">Terminée</p>
                            <h2 class="card-title"><?= htmlspecialchars($iv['titre']) ?></h2>
                        </div>
                    </div>
                    <p class="card-copy"><?= htmlspecialchars(mb_substr($iv['description'], 0, 200)) ?></p>
                    <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#64748b;">
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($iv['date_intervention'])) ?></span>
                        <?php if ($iv['note_mentor']): ?>
                        <span><i class="fas fa-star" style="color:#f59e0b;"></i> Note reçue: <?= $iv['note_mentor'] ?>/5</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($iv['commentaire']): ?>
                    <div style="background:#f8fafc;padding:12px 16px;border-radius:12px;font-size:13px;color:#475569;border:1px solid #e5e7eb;">
                        <i class="fas fa-quote-left" style="color:#94a3b8;margin-right:6px;"></i><?= htmlspecialchars($iv['commentaire']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="card-footer" style="border-top:1px solid #f1f5f9;padding-top:14px;">
                        <div class="author">
                            <span class="avatar-sm"><?= htmlspecialchars($author_init) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($iv['prenom'] . ' ' . $iv['nom']) ?></strong>
                                <small>Auteur de la demande</small>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($aides) > 0): ?>
        <div class="aides-section">
            <h2 class="section-subtitle"><i class="fas fa-check-circle"></i> Mes aides validées <span class="badge-count"><?= count($aides) ?></span></h2>
            <div class="market-grid">
                <?php foreach ($aides as $a):
                    $v_init = $a['v_nom'] ? strtoupper(substr($a['v_prenom'], 0, 1) . substr($a['v_nom'], 0, 1)) : '??';
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
                    <div class="card-footer" style="border-top:1px solid #f1f5f9;padding-top:14px;">
                        <div class="author">
                            <span class="avatar-sm" style="background:var(--purple);"><?= htmlspecialchars($v_init) ?></span>
                            <div>
                                <strong><?= $a['v_nom'] ? htmlspecialchars($a['v_prenom'] . ' ' . $a['v_nom']) : 'En attente' ?></strong>
                                <small>Validée le <?= date('d/m/Y', strtotime($a['date_validation'])) ?></small>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
<script>window.currentUser = { initials: '<?= htmlspecialchars($init) ?>', name: '<?= htmlspecialchars("$prenom $nom") ?>' };</script>
<script src="/phpprojet/js/marketplace.js"></script>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
