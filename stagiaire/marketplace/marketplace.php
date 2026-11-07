<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
extract($_SESSION);
$init = strtoupper(substr(isset($prenom) ? $prenom : '', 0, 1) . substr(isset($nom) ? $nom : '', 0, 1));
$current_url = $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

require_once __DIR__ . '/../../dbopen/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtre = isset($_GET['filtre']) ? trim($_GET['filtre']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_aide'])) {
    extract($_POST);
    $id_aide = (int)$id_aide;
    $stmt = $db->prepare("UPDATE aides SET statut = 'validee', id_stagiaire_validateur = ?, date_validation = NOW() WHERE id = ? AND statut = 'proposee'");
    $stmt->execute([$id_user, $id_aide]);
    header("Location: marketplace.php");
    exit;
}

$sql = "SELECT d.*, u.nom, u.prenom FROM demandes_aide d LEFT JOIN utilisateurs u ON d.id_user = u.id_user";
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

$msg = $_GET['msg'] ?? '';

$status_labels = ['ouverte' => 'Ouvert', 'en_cours' => 'En cours', 'resolue' => 'Résolu'];
$status_classes = ['ouverte' => 'open', 'en_cours' => 'in-progress', 'resolue' => 'resolved'];
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Marketplace</title>
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
            <a href="/phpprojet/stagiaire/marketplace/marketplace.php" class="nav-item<?= strpos($current_url,'marketplace')!==false&&strpos($current_url,'historique')===false?' active':'' ?>"><i class="fas fa-store"></i><span>Marketplace</span></a>
            <a href="/phpprojet/stagiaire/marketplace/historique.php" class="nav-item<?= strpos($current_url,'historique')!==false?' active':'' ?>"><i class="fas fa-clock-rotate"></i><span>Historique</span></a>
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
        <?php if (count($demandes) > 0): ?>
        <?php foreach ($demandes as $d):
          $author_init = strtoupper(substr($d['prenom'] ?? '', 0, 1) . substr($d['nom'] ?? '', 0, 1));
          $status = $d['status'] ?? 'ouverte';
          $status_label = $status_labels[$status] ?? 'Ouvert';
          $status_class = $status_classes[$status] ?? 'open';
          $is_owner = ($d['id_user'] ?? 0) == ($id_user ?? 0);
          $nb_proposals = $propositions_count_map[$d['id_demande']] ?? 0;
          $has_accepted = isset($accepted_map[$d['id_demande']]);
          $is_rated = isset($rated_map[$d['id_demande']]);
        ?>
        <article class="market-card" data-author="<?= htmlspecialchars($author_init) ?>" data-id="<?= $d['id_demande'] ?? 0 ?>">
          <div class="card-head">
            <div>
              <p class="status-pill <?= htmlspecialchars($status_class) ?>"><?= htmlspecialchars($status_label) ?></p>
              <h2 class="card-title"><?= htmlspecialchars($d['titre'] ?? 'Sans titre') ?></h2>
            </div>
          </div>
          <p class="card-copy"><?= htmlspecialchars($d['description'] ?? '') ?></p>
          <div class="card-footer">
            <div class="author">
              <span class="avatar-sm"><?= htmlspecialchars($author_init ?: '?') ?></span>
              <div>
                <strong><?= htmlspecialchars(trim(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?: 'Anonyme') ?></strong>
                <small><?= date('d/m/Y', strtotime($d['date_pub'] ?? 'now')) ?></small>
              </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
              <?php if ($is_owner): ?>
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
        <?php else: ?>
        <p style="grid-column:1/-1;text-align:center;color:#64748b;padding:40px;">Aucune demande d'aide pour le moment.</p>
        <?php endif; ?>
      </div>
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
  <script>window.currentUser = { initials: '<?= htmlspecialchars($init) ?>', name: '<?= htmlspecialchars("$prenom $nom") ?>', id: <?= (int)(isset($id_user) ? $id_user : 0) ?> };</script>
  <script src="/phpprojet/js/marketplace.js"></script>
  <script src="/phpprojet/js/profile-menu.js"></script>
  <script src="/phpprojet/js/search.js"></script>
</body>

</html>
