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

$stmt = $db->prepare("SELECT c.nom, uc.niveau FROM user_competences uc JOIN competences c ON uc.id_competence = c.id_competence WHERE uc.id_user = ?");
$stmt->execute([$id_user]);
$competences = $stmt->fetchAll();

$stmt = $db->prepare("SELECT b.id_badge, b.nom, b.image, b.points_requis, ob.date_obtention FROM badges b LEFT JOIN obtention_badges ob ON b.id_badge = ob.id_badge AND ob.id_user = ? ORDER BY b.id_badge");
$stmt->execute([$id_user]);
$badges_data = $stmt->fetchAll();

$stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_mentor = ?");
$stmt->execute([$id_user]);
$helps_given = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT note_mentor FROM aide_effectuee WHERE id_mentor = ? AND note_mentor IS NOT NULL");
$stmt->execute([$id_user]);
$notes = $stmt->fetchAll(PDO::FETCH_COLUMN);
$avg_rating = count($notes) > 0 ? round(array_sum($notes) / count($notes), 1) : '0.0';

$badge_obtained = 0;
foreach ($badges_data as $b) {
    if ($b['date_obtention']) $badge_obtained++;
}
$total_badges = count($badges_data);

$niveau_map = ['debutant' => 'Débutant', 'intermediaire' => 'Intermédiaire', 'avance' => 'Avancé'];
$niveau_class = ['debutant' => 'warning', 'intermediaire' => 'info', 'avance' => 'success'];
$progress_width = ['debutant' => 35, 'intermediaire' => 65, 'avance' => 90];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Profil Mentor</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="profile.css">
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
        <section class="profile-hero">
            <div class="profile-card">
                <div class="profile-summary">
                    <div class="profile-avatar hero"><?= htmlspecialchars($init) ?></div>
                    <div>
                        <h2><?= htmlspecialchars("$prenom $nom") ?></h2>
                        <p><?= $filiere ? htmlspecialchars("Filière $filiere") : 'Mentor' ?></p>
                        <p class="member-since">Membre depuis <?= date('d/m/Y', strtotime($date_inscription)) ?></p>
                    </div>
                </div>
                <div class="profile-actions-hero">
                    <a href="modifier_profile.php" class="btn primary">Modifier profil</a>
                </div>
            </div>
            <div class="profile-stats-hero">
                <article class="stat-card small">
                    <span class="stat-label">Aides réalisées</span>
                    <strong><?= $helps_given ?></strong>
                    <span class="stat-note">depuis l'inscription</span>
                </article>
                <article class="stat-card small">
                    <span class="stat-label">Note moyenne</span>
                    <strong><?= htmlspecialchars($avg_rating) ?></strong>
                    <span class="stat-note positive">sur 5</span>
                </article>
                <article class="stat-card small">
                    <span class="stat-label">Badges obtenus</span>
                    <strong><?= $badge_obtained ?>/<?= $total_badges ?></strong>
                    <span class="stat-note"><?= $total_badges - $badge_obtained ?> restants</span>
                </article>
            </div>
        </section>

        <section class="competence-section">
            <div class="section-heading">
                <h3>Mes Compétences</h3>
                <a href="../../stagiaire/competences/ajouter_competence.php" class="add-competence-btn">+ Ajouter une compétence</a>
            </div>
            <div class="skills-grid">
                <?php if (!empty($competences)): ?>
                <?php foreach ($competences as $comp):
                    $niveau = $comp['niveau'];
                    $label = $niveau_map[$niveau] ?? ucfirst($niveau);
                    $class = $niveau_class[$niveau] ?? 'info';
                    $width = $progress_width[$niveau] ?? 50;
                ?>
                <div class="skill-item">
                    <div class="skill-header">
                        <strong><?= htmlspecialchars($comp['nom']) ?></strong>
                        <span class="skill-badge <?= $class ?>"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= $width ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>Aucune compétence renseignée.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="badges-section">
            <div class="section-heading">
                <h3>Badges & Réalisations</h3>
            </div>
            <div class="badges-grid">
                <?php foreach ($badges_data as $badge):
                    $obtained = $badge['date_obtention'] !== null;
                ?>
                <article class="badge-card <?= $obtained ? 'obtained' : 'locked' ?>">
                    <div class="badge-icon"><?= $badge['image'] ? '<img src="' . htmlspecialchars($badge['image']) . '" alt="' . htmlspecialchars($badge['nom']) . '">' : ($obtained ? '🏅' : '🔒') ?></div>
                    <h4><?= htmlspecialchars($badge['nom']) ?></h4>
                    <p><?= $badge['points_requis'] ? htmlspecialchars((string)$badge['points_requis']) . ' points requis' : '' ?></p>
                    <span class="badge-status <?= $obtained ? 'obtained' : 'locked' ?>"><?= $obtained ? '✔ Obtenu' : 'Verrouillé' ?></span>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
