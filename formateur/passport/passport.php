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

$search = $_GET['search'] ?? '';

$sql = "
    SELECT u.id_user, u.nom, u.prenom, u.email, u.filiere, u.score,
           vc.id as validation_id,
           c.nom as competence_nom
    FROM utilisateurs u
    LEFT JOIN user_competences uc ON u.id_user = uc.id_user
    LEFT JOIN validation_competence vc ON uc.id = vc.id_user_competence AND vc.status = 'valide'
    LEFT JOIN competences c ON uc.id_competence = c.id_competence
    WHERE u.role = 'stagiaire'
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ?)";
    $params = ["%$search%", "%$search%"];
}
$stmt = $db->prepare($sql);
$stmt->execute($params);
$raw = $stmt->fetchAll();

$students_map = [];
foreach ($raw as $row) {
    $uid = $row['id_user'];
    if (!isset($students_map[$uid])) {
        $students_map[$uid] = [
            'id_user' => $row['id_user'],
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'email' => $row['email'],
            'filiere' => $row['filiere'],
            'score' => $row['score'],
            'validations' => [],
            'competences' => []
        ];
    }
    if ($row['validation_id']) {
        $students_map[$uid]['validations'][$row['validation_id']] = true;
    }
    if ($row['competence_nom']) {
        $students_map[$uid]['competences'][$row['competence_nom']] = true;
    }
}

$students = [];
foreach ($students_map as $uid => $data) {
    $students[] = [
        'id_user' => $data['id_user'],
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'email' => $data['email'],
        'filiere' => $data['filiere'],
        'score' => $data['score'],
        'validations_count' => count($data['validations']),
        'competences' => !empty($data['competences']) ? implode(', ', array_keys($data['competences'])) : ''
    ];
}

usort($students, function ($a, $b) {
    return $b['validations_count'] - $a['validations_count'];
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Passport</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="passport.css">
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
        <section class="page-top">
            <div>
                <p class="eyebrow">Skills Passport</p>
                <h1>Compétences validées des stagiaires</h1>
                <p class="page-copy">Visualisez les compétences techniques certifiées de chaque stagiaire.</p>
            </div>
        </section>

        <div class="filters-row">
            <div class="filter-group">
                <form method="GET" style="display:flex;gap:10px;width:100%;">
                    <input type="text" name="search" placeholder="Rechercher par nom de stagiaire..." class="filter-input" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn primary">Filtrer</button>
                </form>
            </div>
        </div>

        <div class="passport-list">
            <?php if (empty($students)): ?>
            <p>Aucun stagiaire trouvé.</p>
            <?php endif; ?>
            <?php foreach ($students as $s): ?>
            <section class="passport-summary-card">
                <div class="passport-row">
                    <div class="passport-profile">
                        <div class="avatar-hero"><?= strtoupper(substr($s['nom'],0,1).substr($s['prenom'],0,1)) ?></div>
                        <div>
                            <h2><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></h2>
                            <p><?= htmlspecialchars($s['filiere'] ?? 'Aucune filière') ?></p>
                            <p class="validated-skills"><?= $s['validations_count'] ?> Compétences validées</p>
                        </div>
                    </div>
                    <div class="passport-details">
                        <p><strong>Email:</strong> <?= htmlspecialchars($s['email']) ?></p>
                        <p><strong>Score:</strong> <?= (int)$s['score'] ?></p>
                        <p><strong>Compétences:</strong> <?= htmlspecialchars($s['competences'] ?? 'Aucune') ?></p>
                        <a href="generate_pdf.php?user_id=<?= $s['id_user'] ?>" class="btn primary" style="margin-top:12px;display:inline-flex;padding:10px 18px;font-size:13px;text-decoration:none;"><i class="fas fa-download"></i> Download PDF</a>
                    </div>
                </div>
            </section>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
