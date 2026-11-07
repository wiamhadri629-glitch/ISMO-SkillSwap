<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
extract($_SESSION);
$init = strtoupper(substr(isset($prenom) ? $prenom : '', 0, 1) . substr(isset($nom) ? $nom : '', 0, 1));
$current_url = $_SERVER["PHP_SELF"];

require_once __DIR__ . '/../../dbopen/db.php';

$stmt = $db->prepare("SELECT c.nom, uc.niveau, vc.date_validation FROM validation_competence vc JOIN user_competences uc ON vc.id_user_competence = uc.id JOIN competences c ON uc.id_competence = c.id_competence WHERE uc.id_user = ? AND vc.status = 'valide' ORDER BY vc.date_validation DESC");
$stmt->execute([isset($id_user) ? $id_user : 0]);
$validated_skills = $stmt->fetchAll();

$stmt = $db->prepare("SELECT b.nom, b.image, ob.date_obtention FROM obtention_badges ob JOIN badges b ON ob.id_badge = b.id_badge WHERE ob.id_user = ? ORDER BY ob.date_obtention DESC");
$stmt->execute([isset($id_user) ? $id_user : 0]);
$badges = $stmt->fetchAll();

$stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_mentor = ?");
$stmt->execute([isset($id_user) ? $id_user : 0]);
$helps_given = count($stmt->fetchAll());

$skill_count = count($validated_skills);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMO-SkillSwap - Passport</title>
    <link rel="stylesheet" href="/phpprojet/css/styles.css">
    <link rel="stylesheet" href="/phpprojet/css/passport.css">
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
            <a href="/phpprojet/stagiaire/profile/profile.php" class="nav-item<?= strpos($current_url,'profile')!==false?' active':'' ?>"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="/phpprojet/stagiaire/passport/passport.php" class="nav-item<?= strpos($current_url,'passport')!==false?' active':'' ?>"><i class="fas fa-passport"></i><span>Passport</span></a>
            <a href="/phpprojet/stagiaire/classement/classement.php" class="nav-item<?= strpos($current_url,'classement')!==false?' active':'' ?>"><i class="fas fa-ranking-star"></i><span>Classement</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?= htmlspecialchars($init) ?></div>
                <div>
                    <p class="user-name"><?= htmlspecialchars((isset($prenom) ? $prenom : '') . ' ' . (isset($nom) ? $nom : '')) ?></p>
                    <p class="user-role"><?= isset($role) ? ucfirst($role) : '' ?></p>
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
                    <p class="user-name"><?= htmlspecialchars((isset($prenom) ? $prenom : '') . ' ' . (isset($nom) ? $nom : '')) ?></p>
                    <p class="user-role"><?= isset($role) ? ucfirst($role) : '' ?></p>
                </div>
                <i class="fas fa-chevron-down" id="chevronIcon"></i>
            </div>
        </div>
        <div class="profile-dropdown" id="profileMenu">
            <div class="dropdown-header">
                <div class="dropdown-avatar"><?= htmlspecialchars($init) ?></div>
                <div class="dropdown-user-info">
                    <h4><?= htmlspecialchars((isset($prenom) ? $prenom : '') . ' ' . (isset($nom) ? $nom : '')) ?></h4>
                    <p><?= (isset($filiere) ? $filiere : '') ? "Filière " . htmlspecialchars($filiere) : "Platform " . (isset($role) ? ucfirst($role) : '') ?></p>
                    <span class="role-badge role-badge-stagiaire"><?= isset($role) ? ucfirst($role) : '' ?></span>
                </div>
            </div>
            <div class="dropdown-meta">
                <div class="meta-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars(isset($email) ? $email : '') ?></div>
                <div class="meta-item"><i class="fas fa-calendar"></i> Membre depuis <?= isset($date_inscription) ? date("d/m/Y",strtotime($date_inscription)) : '' ?></div>
            </div>
            <div class="dropdown-actions">
                <a href="/phpprojet/stagiaire/passport/generate_pdf.php" class="dropdown-action"><i class="fas fa-download"></i> Télécharger le Passeport (PDF)</a>
                <a href="/phpprojet/stagiaire/profile/profile.php" class="dropdown-action"><i class="fas fa-user"></i> Voir le profil</a>
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
                <h1>Votre portfolio de compétences validées</h1>
                <p class="page-copy">Visualisez vos compétences techniques certifiées et découvrez les actions pour progresser encore plus.</p>
            </div>
            <a href="generate_pdf.php" class="btn primary download-btn">
                <i class="fas fa-download"></i>
                Download PDF
            </a>
        </section>

        <section class="passport-summary-card">
            <div class="passport-row">
                <div class="passport-profile">
                    <div class="avatar-hero"><?= htmlspecialchars($init) ?></div>
                    <div>
                        <h2><?= htmlspecialchars((isset($prenom) ? $prenom : '') . ' ' . (isset($nom) ? $nom : '')) ?></h2>
                        <p>Stagiaire</p>
                        <p class="validated-skills"><?= $skill_count ?> Compétences validées</p>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($validated_skills)): ?>
        <section class="passport-skills-section">
            <h3>Compétences validées</h3>
            <div class="skills-grid">
                <?php foreach ($validated_skills as $skill): ?>
                <div class="skill-item">
                    <div class="skill-header">
                        <strong><?= htmlspecialchars($skill['nom']) ?></strong>
                        <span class="skill-badge info"><?= htmlspecialchars(ucfirst($skill['niveau'])) ?></span>
                    </div>
                    <small>Validé le <?= date('d/m/Y', strtotime($skill['date_validation'])) ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($badges)): ?>
        <section class="passport-badges-section">
            <h3>Badges obtenus</h3>
            <div class="badges-grid">
                <?php foreach ($badges as $badge): ?>
                <article class="badge-card obtained">
                    <div class="badge-icon"><?= $badge['image'] ? '<img src="' . htmlspecialchars($badge['image']) . '" alt="' . htmlspecialchars($badge['nom']) . '">' : '🏅' ?></div>
                    <h4><?= htmlspecialchars($badge['nom']) ?></h4>
                    <p>Obtenu le <?= date('d/m/Y', strtotime($badge['date_obtention'])) ?></p>
                    <span class="badge-status obtained">✔ Obtenu</span>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="passport-promo-card">
            <div>
                <h2>Améliorez vos compétences</h2>
                <p>Réalisez plus d'interventions pour valider de nouvelles compétences et augmenter votre niveau.</p>
            </div>
            <a href="../marketplace/marketplace.php" class="btn primary">Voir les opportunités</a>
        </section>
    </main>
</div>
<script src="/phpprojet/js/profile-menu.js"></script>
<script src="/phpprojet/js/search.js"></script>
</body>
</html>
