<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Modifier la compétence';
$page_css = 'competences.css';

$id = 0;
if (isset($_GET['id'])) { $id = (int)$_GET['id']; }
elseif (isset($_POST['id'])) { $id = (int)$_POST['id']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    extract($_POST);
    require_once __DIR__ . '/../../dbopen/db.php';
    $id_user = $_SESSION['id_user'];

    $niveau = $niveau ?? '';

    if (!in_array($niveau, ['debutant', 'intermediaire', 'avance'])) {
        $error = 'Niveau invalide.';
    } else {
        $stmt = $db->prepare("UPDATE user_competences SET niveau = ? WHERE id = ? AND id_user = ?");
        $stmt->execute([$niveau, $id, $id_user]);
        header('Location: competences.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$stmt = $db->prepare("
    SELECT uc.*, c.nom, c.categorie
    FROM user_competences uc
    JOIN competences c ON uc.id_competence = c.id_competence
    WHERE uc.id = ? AND uc.id_user = ?
");
$stmt->execute([$id, $id_user]);
$uc = $stmt->fetch();

if (!$uc) {
    header('Location: competences.php');
    exit;
}
?>
<section class="competence-section">
    <div class="section-heading">
        <h3>Modifier le niveau</h3>
    </div>
    <p style="margin-bottom:18px;">Compétence : <strong><?= htmlspecialchars($uc['nom']) ?></strong> (<?= htmlspecialchars($uc['categorie']) ?>)</p>

    <?php if (isset($error)): ?>
    <div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="max-width:400px;">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-group" style="margin-bottom:18px;display:flex;flex-direction:column;gap:8px;">
            <label style="font-weight:600;">Niveau</label>
            <select name="niveau" required style="padding:14px;border-radius:14px;border:1px solid #e5e7eb;font-size:15px;">
                <option value="debutant" <?= $uc['niveau'] === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                <option value="intermediaire" <?= $uc['niveau'] === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                <option value="avance" <?= $uc['niveau'] === 'avance' ? 'selected' : '' ?>>Avancé</option>
            </select>
        </div>
        <div class="form-actions" style="display:flex;gap:12px;">
            <button type="submit" name="modifier" class="btn primary" style="padding:14px 28px;background:#2563eb;color:white;border:none;border-radius:14px;font-weight:700;cursor:pointer;">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <a href="competences.php" style="padding:14px 28px;background:#f3f4f6;color:#333;border-radius:14px;font-weight:600;text-decoration:none;">Annuler</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
