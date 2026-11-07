<?php
$page_title = 'Mes Compétences';
$page_css = 'competences.css';
require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$stmt = $db->prepare("
    SELECT uc.id, c.id_competence, c.nom, c.categorie, uc.niveau
    FROM user_competences uc
    JOIN competences c ON uc.id_competence = c.id_competence
    WHERE uc.id_user = ?
    ORDER BY c.categorie, c.nom
");
$stmt->execute([$id_user]);
$mes_competences = $stmt->fetchAll();

$mes_ids = array_column($mes_competences, 'id_competence');

$stmt = $db->query("SELECT * FROM competences ORDER BY categorie, nom");
$all_competences = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<section class="competence-section">
    <div class="section-heading">
        <h3>Mes Compétences</h3>
    </div>

    <?php if ($msg): ?>
    <div style="background:#dcfce7;color:#166534;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <?php if (count($mes_competences) > 0): ?>
    <div class="skills-grid">
        <?php foreach ($mes_competences as $comp):
            $level_class = match($comp['niveau']) {
                'avance' => 'success',
                'intermediaire' => 'info',
                'debutant' => 'warning',
                default => 'info'
            };
            $level_label = match($comp['niveau']) {
                'avance' => 'Avancé',
                'intermediaire' => 'Intermédiaire',
                'debutant' => 'Débutant',
                default => $comp['niveau']
            };
            $progress_pct = match($comp['niveau']) {
                'avance' => 100,
                'intermediaire' => 66,
                'debutant' => 33,
                default => 50
            };
        ?>
        <div class="skill-item">
            <div class="skill-header">
                <strong><?= htmlspecialchars($comp['nom']) ?></strong>
                <span class="skill-badge <?= $level_class ?>"><?= htmlspecialchars($level_label) ?></span>
            </div>
            <div class="progress-bar">
                <div class="progress" style="width: <?= $progress_pct ?>%"></div>
            </div>
            <div style="display:flex;gap:8px;margin-top:8px;">
                <a href="modifie_competence.php?id=<?= $comp['id'] ?>" class="btn-edit" style="text-decoration:none;border:1px solid #e5e7eb;padding:6px 12px;border-radius:8px;color:#64748b;font-size:13px;display:inline-flex;align-items:center;gap:4px;">
                    <i class="fas fa-pen"></i> Modifier
                </a>
                <a href="supprimer_competence.php?id=<?= $comp['id'] ?>" class="btn-edit" style="text-decoration:none;border:1px solid #e5e7eb;padding:6px 12px;border-radius:8px;color:#ef4444;font-size:13px;display:inline-flex;align-items:center;gap:4px;" onclick="return confirm('Supprimer cette compétence ?')">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>Vous n'avez pas encore déclaré de compétences.</p>
    <?php endif; ?>
</section>

<section class="competence-section" style="margin-top:24px;">
    <div class="section-heading">
        <h3>Ajouter une compétence</h3>
    </div>
    <form method="POST" action="ajouter_competence.php" style="max-width:500px;">
        <div class="form-group" style="margin-bottom:18px;display:flex;flex-direction:column;gap:8px;">
            <label style="font-weight:600;">Compétence</label>
            <select name="id_competence" required style="padding:14px;border-radius:14px;border:1px solid #e5e7eb;font-size:15px;">
                <option value="">Choisir...</option>
                <?php foreach ($all_competences as $comp): ?>
                <?php if (!in_array($comp['id_competence'], $mes_ids)): ?>
                <option value="<?= $comp['id_competence'] ?>"><?= htmlspecialchars($comp['nom']) ?> (<?= htmlspecialchars($comp['categorie']) ?>)</option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:18px;display:flex;flex-direction:column;gap:8px;">
            <label style="font-weight:600;">Niveau</label>
            <select name="niveau" required style="padding:14px;border-radius:14px;border:1px solid #e5e7eb;font-size:15px;">
                <option value="debutant">Débutant</option>
                <option value="intermediaire" selected>Intermédiaire</option>
                <option value="avance">Avancé</option>
            </select>
        </div>
        <button type="submit" class="btn primary" style="padding:14px 28px;background:#2563eb;color:white;border:none;border-radius:14px;font-weight:700;cursor:pointer;">
            <i class="fas fa-plus"></i> Ajouter
        </button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
