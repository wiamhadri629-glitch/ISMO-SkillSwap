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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtre = isset($_GET['filtre']) ? trim($_GET['filtre']) : '';
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_aide'])) {
    extract($_POST);
    $id_aide = (int)$id_aide;
    $stmt = $db->prepare("UPDATE aides SET statut = 'validee', id_stagiaire_validateur = ?, date_validation = NOW() WHERE id = ? AND statut = 'proposee'");
    $stmt->execute([$id_user, $id_aide]);
    header("Location: marketplace.php");
    exit;
}

$sql = "SELECT d.*, u.nom, u.prenom, u.photo FROM demandes_aide d LEFT JOIN utilisateurs u ON d.id_user = u.id_user";
$params = [];
$conditions = [];

if ($search !== '') {
    $conditions[] = "(titre LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filtre !== '' && in_array($filtre, ['ouverte', 'en_cours', 'resolue'])) {
    $conditions[] = "status = ?";
    $params[] = $filtre;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY date_pub DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();

$stmt = $db->prepare("SELECT id_demande FROM propositions_aide WHERE id_mentor = ?");
$stmt->execute([$id_user]);
$proposed_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

$propositions_count = $db->prepare("SELECT id_demande FROM propositions_aide WHERE id_demande IN (SELECT id_demande FROM demandes_aide WHERE id_user = ?) AND status = 'en_attente'");
$propositions_count->execute([$id_user]);
$propositions_count_map = [];
foreach ($propositions_count->fetchAll() as $pr) {
    $did = $pr['id_demande'];
    if (!isset($propositions_count_map[$did])) $propositions_count_map[$did] = 0;
    $propositions_count_map[$did]++;
}

$accepted_map = [];
$stmt_acc = $db->prepare("SELECT p.id_demande, p.id_mentor FROM propositions_aide p JOIN demandes_aide d ON p.id_demande = d.id_demande WHERE d.id_user = ? AND p.status = 'acceptee'");
$stmt_acc->execute([$id_user]);
foreach ($stmt_acc->fetchAll() as $acc) {
    $accepted_map[$acc['id_demande']] = $acc['id_mentor'];
}

$rated_map = [];
$stmt_rated = $db->prepare("SELECT id_demande FROM aide_effectuee WHERE id_demande IN (SELECT id_demande FROM demandes_aide WHERE id_user = ?)");
$stmt_rated->execute([$id_user]);
foreach ($stmt_rated->fetchAll() as $r) {
    $rated_map[$r['id_demande']] = true;
}

$aides_proposees = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a JOIN utilisateurs u ON a.id_formateur = u.id_user WHERE a.statut = 'proposee' ORDER BY a.date_creation DESC");
$aides_proposees->execute();
$aides_proposees = $aides_proposees->fetchAll();

$aides_validees_par_moi = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a JOIN utilisateurs u ON a.id_formateur = u.id_user WHERE a.statut = 'validee' AND a.id_stagiaire_validateur = ? ORDER BY a.date_validation DESC");
$aides_validees_par_moi->execute([$id_user]);
$aides_validees_par_moi = $aides_validees_par_moi->fetchAll();

$mes_aides = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a LEFT JOIN utilisateurs u ON a.id_stagiaire_validateur = u.id_user WHERE a.id_formateur = ? ORDER BY a.date_creation DESC");
$mes_aides->execute([$id_user]);
$mes_aides = $mes_aides->fetchAll();

$status_labels = ['ouverte' => 'Ouvert', 'en_cours' => 'En cours', 'resolue' => 'Résolu'];
$status_classes = ['ouverte' => 'open', 'en_cours' => 'in-progress', 'resolue' => 'resolved'];
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
        <div class="topbar-actions">
            <div class="topbar-left">
                <h1 class="section-title">Marketplace</h1>
                <a href="ajouter_demande.php" class="btn primary publish">+ Publier une demande</a>
                            </div>
        </div>

        <?php if ($msg): ?>
        <div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
        <?php endif; ?>

        <div class="search-row">
            <div class="search-large">
                <i class="fas fa-search"></i>
                <form method="GET" action="" style="display:flex;flex:1;gap:8px;">
                    <input type="text" name="search" placeholder="Rechercher par titre ou description..." value="<?= htmlspecialchars($search) ?>" style="flex:1;">
                    <button type="submit" class="btn secondary">Rechercher</button>
                    <?php if ($search !== '' || $filtre !== ''): ?>
                    <a href="marketplace.php" class="btn secondary">Effacer</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="filter-select">
                <form method="GET" action="">
                    <?php if ($search !== ''): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    <select name="filtre" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        <option value="ouverte" <?= $filtre === 'ouverte' ? 'selected' : '' ?>>Ouvert</option>
                        <option value="en_cours" <?= $filtre === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="resolue" <?= $filtre === 'resolue' ? 'selected' : '' ?>>Résolu</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="market-grid">
            <?php if (empty($demandes)): ?>
            <p style="grid-column:1/-1;text-align:center;padding:40px;color:#666;">Aucune demande trouvée.</p>
            <?php endif; ?>
            <?php foreach ($demandes as $d):
                $already_proposed = in_array($d['id_demande'], $proposed_ids);
                $author_exists = $d['id_user'] !== null;
                $author_init = $author_exists ? strtoupper(substr($d['prenom'], 0, 1) . substr($d['nom'], 0, 1)) : '??';
                $status = $d['status'];
                $status_cls = $status_classes[$status] ?? 'open';
                $status_lbl = $status_labels[$status] ?? ucfirst($status);
                $is_author = $author_exists && $d['id_user'] == $id_user;
                $nb_proposals = $propositions_count_map[$d['id_demande']] ?? 0;
                $has_accepted = isset($accepted_map[$d['id_demande']]);
                $is_rated = isset($rated_map[$d['id_demande']]);
            ?>
            <article class="market-card">
                <div class="card-head">
                    <div>
                        <p class="status-pill <?= $status_cls ?>"><?= htmlspecialchars($status_lbl) ?></p>
                        <h2 class="card-title"><?= htmlspecialchars($d['titre']) ?></h2>
                    </div>
                    <button class="btn-report" title="Signaler"><i class="fas fa-exclamation-triangle"></i></button>
                </div>
                <p class="card-copy"><?= htmlspecialchars($d['description']) ?></p>
                <div class="card-footer">
                    <div class="author">
                        <span class="avatar-sm"><?= htmlspecialchars($author_init) ?></span>
                        <div>
                            <strong><?= $author_exists ? htmlspecialchars($d['prenom'] . ' ' . $d['nom']) : 'Ancien utilisateur' ?></strong>
                            <small><?= date('d/m/Y', strtotime($d['date_pub'])) ?></small>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <?php if ($status === 'ouverte' && !$already_proposed && !$is_author): ?>
                        <a href="proposer_aide.php?id_demande=<?= (int)$d['id_demande'] ?>" class="btn primary" style="padding:10px 16px;font-size:13px;background:#2563eb;color:white;border:none;border-radius:10px;text-decoration:none;font-weight:600;">Proposer mon aide</a>
                        <?php elseif ($already_proposed && !$is_author): ?>
                        <span class="tag">Déjà proposé</span>
                        <?php endif; ?>
                        <?php if ($is_author): ?>
                        <?php if ($nb_proposals > 0 && $status === 'ouverte'): ?>
                        <a href="valider_aide.php?id_demande=<?= $d['id_demande'] ?>" class="btn primary" style="padding:10px 16px;font-size:13px;background:#8b5cf6;color:white;border:none;border-radius:10px;text-decoration:none;font-weight:600;">
                            <i class="fas fa-handshake-angle"></i> Voir les propositions (<?= $nb_proposals ?>)
                        </a>
                        <?php endif; ?>
                        <?php if ($has_accepted && !$is_rated): ?>
                        <a href="noter_mentor.php?id_demande=<?= $d['id_demande'] ?>" class="btn primary" style="padding:10px 16px;font-size:13px;background:#16a34a;color:white;border:none;border-radius:10px;text-decoration:none;font-weight:600;">
                            <i class="fas fa-star"></i> Noter le mentor
                        </a>
                        <?php endif; ?>
                        <?php if ($status === 'ouverte'): ?>
                        <a href="noter_mentor.php?id_demande=<?= $d['id_demande'] ?>" class="btn primary" style="padding:10px 16px;font-size:13px;background:#16a34a;color:white;border:none;border-radius:10px;text-decoration:none;font-weight:600;">Marquer comme résolu</a>
                        <?php endif; ?>
                        <a href="modifier_demande.php?id_demande=<?= $d['id_demande'] ?>" class="btn-edit" style="text-decoration:none;"><i class="fas fa-pen"></i></a>
                        <button class="btn-delete" onclick="if(confirm('Supprimer cette demande ?')) location.href='supprimer_demande.php?id_demande=<?= $d['id_demande'] ?>'">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Aides proposées -->
        <?php if (count($aides_proposees) > 0): ?>
        <div class="aides-section" style="margin-top:40px;">
          <h2 class="section-subtitle"><i class="fas fa-handshake-angle"></i> Aides proposées <span class="badge-count"><?= count($aides_proposees) ?></span></h2>
          <div class="market-grid">
            <?php foreach ($aides_proposees as $a):
              $creator_init = strtoupper(substr($a['prenom'], 0, 1) . substr($a['nom'], 0, 1));
              $is_my_aide = $a['id_formateur'] == $id_user;
            ?>
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
                  <span class="avatar-sm" style="background:var(--purple);"><?= htmlspecialchars($creator_init) ?></span>
                  <div>
                    <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                    <small>Proposée le <?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                  </div>
                </div>
                <?php if ($is_my_aide): ?>
                <a href="modifier_aide.php?id_aide=<?= $a['id'] ?>" class="btn-edit" style="text-decoration:none;border:1px solid #e5e7eb;padding:8px 14px;border-radius:10px;color:#64748b;font-size:13px;display:inline-flex;align-items:center;gap:4px;">
                  <i class="fas fa-pen"></i> Modifier
                </a>
                <?php else: ?>
                <form method="POST" onsubmit="return confirm('Valider cette aide ?')">
                  <input type="hidden" name="id_aide" value="<?= $a['id'] ?>">
                  <button type="submit" name="valider_aide" class="btn primary" style="padding:10px 16px;font-size:13px;background:#16a34a;color:white;border:none;border-radius:10px;text-decoration:none;font-weight:600;cursor:pointer;">
                    <i class="fas fa-check"></i> Valider l'aide
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Mes aides -->
        <?php if (count($mes_aides) > 0): ?>
        <div class="aides-section" style="margin-top:40px;">
          <h2 class="section-subtitle"><i class="fas fa-star"></i> Mes aides <span class="badge-count"><?= count($mes_aides) ?></span></h2>
          <div class="market-grid">
            <?php foreach ($mes_aides as $a):
              $creator_init = strtoupper(substr($a['prenom'] ?? '', 0, 1) . substr($a['nom'] ?? '', 0, 1));
              $status_class = $a['statut'] === 'validee' ? 'validee' : 'proposee';
              $status_label = $a['statut'] === 'validee' ? 'Validée' : 'Proposée';
            ?>
            <article class="market-card">
              <div class="card-head">
                <div>
                  <p class="status-pill <?= $status_class ?>"><?= $status_label ?></p>
                  <h2><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                </div>
              </div>
              <p class="card-copy"><?= htmlspecialchars($a['description']) ?></p>
              <?php if ($a['explication']): ?>
              <div style="background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:12px;font-size:13px;">
                <i class="fas fa-quote-left"></i> <?= htmlspecialchars($a['explication']) ?>
              </div>
              <?php endif; ?>
              <div class="card-footer">
                <div class="author">
                  <span class="avatar-sm" style="background:var(--purple);"><?= htmlspecialchars($creator_init) ?></span>
                  <div>
                    <?php if ($a['statut'] === 'validee' && $a['nom']): ?>
                    <strong>Validée par <?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                    <small>Le <?= date('d/m/Y', strtotime($a['date_validation'])) ?></small>
                    <?php else: ?>
                    <strong>En attente de validation</strong>
                    <small>Proposée le <?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                    <?php endif; ?>
                  </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                  <?php if ($a['statut'] === 'validee'): ?>
                  <a href="expliquer_aide.php?id_aide=<?= $a['id'] ?>" class="btn primary" style="padding:8px 14px;font-size:12px;background:#2563eb;color:white;border:none;border-radius:10px;text-decoration:none;font-weight:600;">
                    <i class="fas fa-pen"></i> <?= $a['explication'] ? 'Modifier' : 'Expliquer' ?>
                  </a>
                  <?php endif; ?>
                  <a href="modifier_aide.php?id_aide=<?= $a['id'] ?>" class="btn-edit" style="text-decoration:none;border:1px solid #e5e7eb;padding:8px 14px;border-radius:10px;color:#64748b;font-size:13px;display:inline-flex;align-items:center;gap:4px;">
                    <i class="fas fa-pen"></i> Modifier
                  </a>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Aides validées par moi -->
        <?php if (count($aides_validees_par_moi) > 0): ?>
        <div class="aides-section" style="margin-top:40px;">
          <h2 class="section-subtitle"><i class="fas fa-check-circle"></i> Aides que j'ai validées <span class="badge-count"><?= count($aides_validees_par_moi) ?></span></h2>
          <div class="market-grid">
            <?php foreach ($aides_validees_par_moi as $a):
              $creator_init = strtoupper(substr($a['prenom'], 0, 1) . substr($a['nom'], 0, 1));
            ?>
            <article class="market-card">
              <div class="card-head">
                <div>
                  <p class="status-pill validee">Validée</p>
                  <h2><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                </div>
              </div>
              <p class="card-copy"><?= htmlspecialchars($a['description']) ?></p>
              <?php if ($a['explication']): ?>
              <div style="background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:12px;font-size:13px;">
                <i class="fas fa-quote-left"></i> <?= htmlspecialchars($a['explication']) ?>
              </div>
              <?php endif; ?>
              <div class="card-footer">
                <div class="author">
                  <span class="avatar-sm" style="background:var(--purple);"><?= htmlspecialchars($creator_init) ?></span>
                  <div>
                    <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
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
