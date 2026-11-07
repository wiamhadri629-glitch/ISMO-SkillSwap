<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Modifier l\'aide';
$page_css = 'marketplace.css';

$id_aide = 0;
if (isset($_GET['id_aide'])) { $id_aide = (int)$_GET['id_aide']; }
elseif (isset($_POST['id_aide'])) { $id_aide = (int)$_POST['id_aide']; }
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    extract($_POST);
    require_once __DIR__ . '/../../dbopen/db.php';
    $id_user = $_SESSION['id_user'];
    $titre = trim($titre ?? '');
    $description = trim($description ?? '');

    if (empty($titre)) {
        $error = 'Le titre est obligatoire.';
    } elseif (empty($description)) {
        $error = 'La description est obligatoire.';
    } else {
        $stmt = $db->prepare("UPDATE aides SET titre = ?, description = ? WHERE id = ? AND id_formateur = ?");
        $stmt->execute([$titre, $description, $id_aide, $id_user]);
        header("Location: marketplace.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
$id_user = $_SESSION['id_user'];

$stmt = $db->prepare("SELECT * FROM aides WHERE id = ? AND id_formateur = ?");
$stmt->execute([$id_aide, $id_user]);
$a = $stmt->fetch();

if (!$a) {
    header("Location: marketplace.php");
    exit;
}
?>
<div class="market-card header-card">
    <p class="eyebrow">Marketplace</p>
    <h1>Modifier l'aide</h1>
</div>

<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="market-card form-card">
    <form method="POST">
        <input type="hidden" name="id_aide" value="<?= $id_aide ?>">
        <div class="form-group">
            <label>Titre de l'aide</label>
            <input type="text" name="titre" value="<?= htmlspecialchars($a['titre']) ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="8" required><?= htmlspecialchars($a['description']) ?></textarea>
        </div>
        <div class="form-actions" style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" name="modifier" class="btn primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <a href="marketplace.php" class="btn secondary" style="padding:14px 22px;background:#f3f4f6;color:#333;border-radius:14px;font-weight:600;text-decoration:none;">Annuler</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
