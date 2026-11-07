<?php
$page_title = 'Classement';
$page_css = 'classement.css';
require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$filiere_filter = $_GET['filiere'] ?? '';

$sql = "
    SELECT u.id_user, u.nom, u.prenom, u.filiere, u.score
    FROM utilisateurs u
    WHERE u.valide = 1
";
$params = [];
if (!empty($filiere_filter)) {
    $sql .= " AND u.filiere = ?";
    $params[] = $filiere_filter;
}
$sql .= " ORDER BY u.score DESC LIMIT 50";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$badge_counts = [];
$stmt2 = $db->query("SELECT id_user, COUNT(*) as cnt FROM obtention_badges GROUP BY id_user");
foreach ($stmt2->fetchAll() as $bc) {
    $badge_counts[$bc['id_user']] = (int)$bc['cnt'];
}
foreach ($users as &$u) {
    $u['badges_count'] = $badge_counts[$u['id_user']] ?? 0;
}
unset($u);

$stmt = $db->prepare("SELECT DISTINCT filiere FROM utilisateurs WHERE filiere IS NOT NULL AND filiere != '' ORDER BY filiere");
$stmt->execute();
$filieres = $stmt->fetchAll();

$current_user_rank = 0;
foreach ($users as $i => $u) {
    if ($u['id_user'] == $id_user) {
        $current_user_rank = $i + 1;
        break;
    }
}

$current_user_data = null;
foreach ($users as $u) {
    if ($u['id_user'] == $id_user) {
        $current_user_data = $u;
        break;
    }
}
?>
<section class="rank-header">
    <div>
        <p class="eyebrow">Classement des Stagiaires</p>
        <h1>Les meilleurs contributeurs de la communauté ISMO</h1>
    </div>
</section>

<section class="rank-cards">
    <article class="rank-card highlight">
        <div class="rank-card-title">
            <i class="fas fa-trophy"></i>
            <div>
                <span>Ton Rang</span>
                <p><?= $current_user_rank > 0 ? '#' . $current_user_rank : 'Non classé' ?></p>
            </div>
        </div>
        <strong>#<?= $current_user_rank ?: '-' ?></strong>
    </article>
    <article class="rank-card">
        <div class="rank-card-title">
            <i class="fas fa-star"></i>
            <div>
                <span>Tes Points</span>
                <p>Score actuel</p>
            </div>
        </div>
        <strong><?= htmlspecialchars($current_user_data['score'] ?? 0) ?></strong>
    </article>
    <article class="rank-card">
        <div class="rank-card-title">
            <i class="fas fa-medal"></i>
            <div>
                <span>Badges</span>
                <p>Obtenus</p>
            </div>
        </div>
        <strong><?= htmlspecialchars($current_user_data['badges_count'] ?? 0) ?></strong>
    </article>
</section>

<section class="top-list-section">
    <div class="list-header">
        <h2>Classement général</h2>
        <form method="GET">
            <select name="filiere" onchange="this.form.submit()">
                <option value="">Toutes les filières</option>
                <?php foreach ($filieres as $f): ?>
                <option value="<?= htmlspecialchars($f['filiere']) ?>" <?= $filiere_filter === $f['filiere'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['filiere']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="top-list">
        <?php $rank = 1; ?>
        <?php foreach ($users as $u):
            $init = strtoupper(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1));
            $is_me = $u['id_user'] == $id_user;
            $avatar_class = $rank % 2 === 0 ? 'top-avatar secondary' : 'top-avatar';
        ?>
        <article class="top-list-item" style="<?= $is_me ? 'border-color:#2563eb;background:#eff6ff;' : '' ?>">
            <div class="top-rank">#<?= $rank ?></div>
            <div class="<?= $avatar_class ?>"><?= htmlspecialchars($init) ?></div>
            <div class="top-info">
                <strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong>
                <span><?= $u['filiere'] ? htmlspecialchars($u['filiere']) : 'Non spécifié' ?></span>
            </div>
            <div class="top-score">
                <span><?= htmlspecialchars(number_format($u['score'])) ?></span>
                <small><?= htmlspecialchars($u['badges_count']) ?> badges</small>
            </div>
        </article>
        <?php $rank++; endforeach; ?>
    </div>
</section>

<section class="rank-banner">
    <div>
        <h2>Continue comme ça !</h2>
        <p><?= htmlspecialchars(number_format($current_user_data['score'] ?? 0)) ?> points</p>
    </div>
    <div class="progress-bar">
        <div class="progress" style="width: <?= min(100, ($current_user_data['score'] ?? 0) / 100) ?>%"></div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
