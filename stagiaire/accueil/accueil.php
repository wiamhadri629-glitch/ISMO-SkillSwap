<?php
$page_title = 'Accueil';
$page_css = 'dashboard.css';
require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$stmt = $db->prepare("SELECT id FROM aide_effectuee WHERE id_mentor = ?");
$stmt->execute([$id_user]);
$aides_realisees = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT id_demande FROM demandes_aide WHERE id_user = ? AND status != 'resolue'");
$stmt->execute([$id_user]);
$demandes_actives = count($stmt->fetchAll());

$stmt = $db->prepare("SELECT 1 FROM obtention_badges WHERE id_user = ?");
$stmt->execute([$id_user]);
$badges_obtenus = count($stmt->fetchAll());

$stmt = $db->query("SELECT status, COUNT(*) as count FROM demandes_aide GROUP BY status ORDER BY count DESC");
$demandes_stats = $stmt->fetchAll();

$stmt = $db->query("SELECT COUNT(*) FROM demandes_aide");
$total_demandes = $stmt->fetchColumn();
?>
<h1 class="section-title">Tableau de Bord</h1>
<p class="sub">Voici un aperçu de ton activité et de tes compétences validées</p>
<div class="cards-grid">
    <article class="stat-card">
        <div class="stat-head">
            <span>Aides réalisées</span>
            <i class="fas fa-users"></i>
        </div>
        <h2><?= htmlspecialchars($aides_realisees) ?></h2>
        <p>Total des aides</p>
    </article>
    <article class="stat-card">
        <div class="stat-head">
            <span>Score</span>
            <i class="fas fa-star"></i>
        </div>
        <h2><?= htmlspecialchars($score) ?></h2>
        <p>Points de réputation</p>
    </article>
    <article class="stat-card">
        <div class="stat-head">
            <span>Demandes actives</span>
            <i class="fas fa-comment"></i>
        </div>
        <h2><?= htmlspecialchars($demandes_actives) ?></h2>
        <p>En attente de solution</p>
    </article>
    <article class="stat-card">
        <div class="stat-head">
            <span>Badges obtenus</span>
            <i class="fas fa-medal"></i>
        </div>
        <h2><?= htmlspecialchars($badges_obtenus) ?></h2>
        <p>Continuez comme ça !</p>
    </article>
</div>
<section class="skills-section">
    <div class="section-header">
        <h3>Demandes par statut</h3>
    </div>
    <div class="skills-bar-list">
        <?php
        $labels = ['ouverte' => 'Ouvertes', 'en_cours' => 'En cours', 'resolue' => 'Résolues'];
        $colors = ['#2563eb', '#7c3aed', '#22c55e'];
        $i = 0;
        foreach ($demandes_stats as $stat):
            $pct = $total_demandes > 0 ? round($stat['count'] / $total_demandes * 100) : 0;
            $label = $labels[$stat['status']] ?? $stat['status'];
            $color = $colors[$i % count($colors)];
        ?>
        <div class="skill-bar-item">
            <div class="skill-bar-label">
                <span><?= htmlspecialchars($label) ?></span>
                <span><?= htmlspecialchars($stat['count']) ?> demandes</span>
            </div>
            <div class="skill-bar-track">
                <div class="skill-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
            </div>
        </div>
        <?php $i++; endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
