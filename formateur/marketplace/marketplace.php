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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_demande'])) {
    extract($_POST);
    $id_demande = (int)$delete_demande;
    $stmt = $db->prepare("DELETE FROM propositions_aide WHERE id_demande = ?");
    $stmt->execute([$id_demande]);
    $stmt = $db->prepare("DELETE FROM demandes_aide WHERE id_demande = ?");
    $stmt->execute([$id_demande]);
    header('Location: marketplace.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_aide'])) {
    extract($_POST);
    $id_aide = (int)$id_aide;
    $stmt = $db->prepare("UPDATE aides SET statut = 'validee', id_stagiaire_validateur = ?, date_validation = NOW() WHERE id = ? AND statut = 'proposee'");
    $stmt->execute([$id_user, $id_aide]);
    header('Location: marketplace.php');
    exit;
}

$search = $_GET['search'] ?? '';
$sql = "
    SELECT d.*, u.nom, u.prenom
    FROM demandes_aide d
    JOIN utilisateurs u ON d.id_user = u.id_user
";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE (d.titre LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY d.date_pub DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();

$aides_proposees = $db->prepare("SELECT a.* FROM aides a WHERE a.id_formateur = ? AND a.statut = 'proposee' ORDER BY a.date_creation DESC");
$aides_proposees->execute([$id_user]);
$aides_proposees = $aides_proposees->fetchAll();

$aides_validees = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a LEFT JOIN utilisateurs u ON a.id_stagiaire_validateur = u.id_user WHERE a.id_formateur = ? AND a.statut = 'validee' ORDER BY a.date_validation DESC");
$aides_validees->execute([$id_user]);
$aides_validees = $aides_validees->fetchAll();
$success_msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Marketplace</title>
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
        <div class="topbar-actions">
            <div class="topbar-left">
                <h1 class="section-title">Marketplace</h1>
                <a href="proposer_aide.php" class="btn primary publish">+ Proposer une aide</a>
            </div>
        </div>

        <div class="search-row">
            <form method="GET" style="display:flex;gap:10px;width:100%;">
                <div class="search-large">
                    <span>🔍</span>
                    <input type="text" name="search" placeholder="Rechercher par titre ou auteur..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn primary">Rechercher</button>
            </form>
        </div>

        <div class="market-grid">
            <?php if ($success_msg): ?>
        <div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
        </div>
        <?php endif; ?>
        <?php if (empty($demandes)): ?>
            <p>Aucune demande trouvée.</p>
            <?php endif; ?>
            <?php foreach ($demandes as $d): ?>
            <?php
            $status_class = match ($d['status']) {
                'ouverte' => 'open',
                'en_cours' => 'in-progress',
                'resolue' => 'resolved',
                default => 'open'
            };
            $status_label = match ($d['status']) {
                'ouverte' => 'Ouvert',
                'en_cours' => 'En cours',
                'resolue' => 'Résolu',
                default => ucfirst($d['status'])
            };
            $init_author = strtoupper(substr($d['nom'],0,1).substr($d['prenom'],0,1));
            ?>
            <article class="market-card">
                <div class="card-head">
                    <div>
                        <p class="status-pill <?= $status_class ?>"><?= $status_label ?></p>
                        <h2><?= htmlspecialchars($d['titre']) ?></h2>
                    </div>
                    <form method="POST" onsubmit="return confirm('Supprimer cette demande ?')">
                        <input type="hidden" name="delete_demande" value="<?= $d['id_demande'] ?>">
                        <button type="submit" class="btn-report" title="Supprimer"><i class="fas fa-trash-can"></i></button>
                    </form>
                </div>
                <p class="card-copy"><?= htmlspecialchars(mb_substr($d['description'], 0, 200)) ?></p>
                <div class="card-footer">
                    <div class="author">
                        <span class="avatar-sm"><?= $init_author ?></span>
                        <div>
                            <strong><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></strong>
                            <small><?= date('d/m/Y', strtotime($d['date_pub'])) ?></small>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if (count($aides_proposees) > 0 || count($aides_validees) > 0): ?>
        <h2 class="section-subtitle" style="margin-top:40px;"><i class="fas fa-handshake-angle"></i> Mes aides proposées</h2>
        <?php endif; ?>

        <?php if (count($aides_proposees) > 0): ?>
        <div class="aides-section">
            <h3 class="section-subtitle">En attente <span class="badge-count"><?= count($aides_proposees) ?></span></h3>
            <div class="market-grid">
                <?php foreach ($aides_proposees as $a): ?>
                <article class="market-card">
                    <div class="card-head">
                        <div>
                            <p class="status-pill proposee">Proposée</p>
                            <h2><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                        </div>
                    </div>
                    <p class="card-copy"><?= htmlspecialchars($a['description']) ?></p>
                    <div class="card-footer">
                        <div class="author">
                            <span class="avatar-sm" style="background:var(--purple);"><?= $init ?></span>
                            <div>
                                <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong>
                                <small>Proposée le <?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                            </div>
                        </div>
                        <a href="modifier_aide.php?id_aide=<?= $a['id'] ?>" class="btn-edit" style="text-decoration:none;border:1px solid #e5e7eb;padding:8px 14px;border-radius:10px;color:#64748b;font-size:13px;display:inline-flex;align-items:center;gap:4px;">
                            <i class="fas fa-pen"></i> Modifier
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($aides_validees) > 0): ?>
        <div class="aides-section">
            <h3 class="section-subtitle">Validées <span class="badge-count"><?= count($aides_validees) ?></span></h3>
            <div class="market-grid">
                <?php foreach ($aides_validees as $a): ?>
                <article class="market-card">
                    <div class="card-head">
                        <div>
                            <p class="status-pill validee">Validée</p>
                            <h2><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                        </div>
                    </div>
                    <p class="card-copy"><?= htmlspecialchars($a['description']) ?></p>
                    <div class="card-footer">
                        <div class="author">
                            <span class="avatar-sm" style="background:var(--purple);"><?= $init ?></span>
                            <div>
                                <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong>
                                <small>Validée le <?= date('d/m/Y', strtotime($a['date_validation'])) ?> par <?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></small>
                            </div>
                        </div>
                        <a href="modifier_aide.php?id_aide=<?= $a['id'] ?>" class="btn-edit" style="text-decoration:none;border:1px solid #e5e7eb;padding:8px 14px;border-radius:10px;color:#64748b;font-size:13px;display:inline-flex;align-items:center;gap:4px;">
                            <i class="fas fa-pen"></i> Modifier
                        </a>
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
