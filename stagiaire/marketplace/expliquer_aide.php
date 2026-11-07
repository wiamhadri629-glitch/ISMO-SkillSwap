<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Expliquer mon aide';
$page_css = 'marketplace.css';

$id_aide = 0;
if (isset($_GET['id_aide'])) { $id_aide = (int)$_GET['id_aide']; }
elseif (isset($_POST['id_aide'])) { $id_aide = (int)$_POST['id_aide']; }
$error = '';

if ($id_aide <= 0) {
    header("Location: marketplace.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expliquer'])) {
    extract($_POST);
    require_once __DIR__ . '/../../dbopen/db.php';
    $id_user = $_SESSION['id_user'];
    $explication = trim($explication ?? '');

    if (empty($explication)) {
        $error = 'Veuillez écrire une explication.';
    } else {
        $stmt = $db->prepare("UPDATE aides SET explication = ? WHERE id = ? AND id_formateur = ? AND statut = 'validee'");
        $stmt->execute([$explication, $id_aide, $id_user]);
        header("Location: marketplace.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$stmt = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a JOIN utilisateurs u ON a.id_stagiaire_validateur = u.id_user WHERE a.id = ? AND a.id_formateur = ? AND a.statut = 'validee'");
$stmt->execute([$id_aide, $id_user]);
$a = $stmt->fetch();

if (!$a) {
    header("Location: marketplace.php");
    exit;
}
?>
<div class="market-card header-card">
    <p class="eyebrow">Marketplace</p>
    <h1>Expliquer mon aide</h1>
</div>

<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="market-card" style="margin-bottom:20px;">
    <div class="card-head">
        <div>
            <p class="status-pill validee">Validée</p>
            <h2><?= htmlspecialchars($a['titre']) ?></h2>
        </div>
    </div>
    <p class="card-copy"><?= htmlspecialchars($a['description']) ?></p>
    <div class="card-footer">
        <div style="color:#475569;font-size:13px;">
            <i class="fas fa-user-check"></i> Validée par <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
            le <?= date('d/m/Y', strtotime($a['date_validation'])) ?>
        </div>
    </div>
</div>

<div class="market-card form-card">
    <form method="POST">
        <input type="hidden" name="id_aide" value="<?= $id_aide ?>">
        <div class="form-group">
            <label>Expliquez qui vous avez aidé et ce qui a été fait <span style="color:#ef4444;">*</span></label>
            <textarea name="explication" rows="6" placeholder="Ex : J'ai aidé Ahmed sur les boucles PHP, nous avons fait plusieurs exercices ensemble..." required><?= htmlspecialchars($a['explication'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" name="expliquer" class="btn primary full">
                <i class="fas fa-pen"></i> Enregistrer l'explication
            </button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
