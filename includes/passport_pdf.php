<?php
session_start();
if (!isset($_SESSION['id_user'])) { header('Location: /phpprojet/login/index.php'); exit; }

$user_id = $_GET['user_id'] ?? $_SESSION['id_user'];

require_once __DIR__ . '/../dbopen/db.php';
require_once __DIR__ . '/../fpdf.php';

$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) { die("Utilisateur introuvable."); }

extract($user);

$stmt = $db->prepare("
    SELECT c.nom, uc.niveau, vc.date_validation
    FROM user_competences uc
    JOIN competences c ON uc.id_competence = c.id_competence
    LEFT JOIN validation_competence vc ON uc.id = vc.id_user_competence AND vc.status = 'validee'
    WHERE uc.id_user = ?
    ORDER BY c.nom
");
$stmt->execute([$id_user]);
$competences = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT b.nom, ob.date_obtention
    FROM obtention_badges ob
    JOIN badges b ON ob.id_badge = b.id_badge
    WHERE ob.id_user = ?
");
$stmt->execute([$id_user]);
$badges = $stmt->fetchAll();

class PassportPDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Helvetica', 'B', 18);
        $this->SetTextColor(37, 99, 235);
        $this->Cell(0, 14, 'ISMO-SkillSwap - Skills Passport', 0, 1, 'C');
        $this->SetDrawColor(37, 99, 235);
        $this->SetLineWidth(0.8);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }

    function Footer()
    {
        $this->SetY(-20);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.4);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 10, 'Genere par ISMO-SkillSwap le ' . date('d/m/Y à H:i'), 0, 0, 'C');
    }
}

$pdf = new PassportPDF();
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Info section
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(0, 10, 'Informations personnelles', 0, 1);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFont('Helvetica', '', 10);

$infos = [
    ['Nom complet', "$prenom $nom"],
    ['Email', $email],
    ['Role', $role],
    ['Filiere', $filiere ?: 'Non specifiee'],
    ['Membre depuis', isset($date_inscription) ? date('d/m/Y', strtotime($date_inscription)) : '-'],
    ['Score', (isset($score) ? $score : '0') . ' points'],
];
foreach ($infos as $i => $info) {
    $pdf->SetFillColor($i % 2 == 0 ? 249 : 255, $i % 2 == 0 ? 250 : 255, $i % 2 == 0 ? 251 : 255);
    $pdf->Cell(50, 8, $info[0], 0, 0, 'L', true);
    $pdf->Cell(0, 8, $info[1], 0, 1, 'L', true);
}

$pdf->Ln(6);

// Competences section
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(0, 10, 'Competences', 0, 1);
$pdf->SetTextColor(51, 51, 51);

if (count($competences) > 0) {
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetFillColor(37, 99, 235);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(80, 9, 'Competence', 1, 0, 'C', true);
    $pdf->Cell(50, 9, 'Niveau', 1, 0, 'C', true);
    $pdf->Cell(0, 9, 'Valide le', 1, 1, 'C', true);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->SetFont('Helvetica', '', 10);
    foreach ($competences as $i => $comp) {
        $level_label = match($comp['niveau']) {
            'avance' => 'Avance',
            'intermediaire' => 'Intermediaire',
            'debutant' => 'Debutant',
            default => $comp['niveau']
        };
        $date_val = $comp['date_validation'] ? date('d/m/Y', strtotime($comp['date_validation'])) : 'Non validee';
        $pdf->SetFillColor($i % 2 == 0 ? 240 : 255, $i % 2 == 0 ? 244 : 255, $i % 2 == 0 ? 255 : 255);
        $pdf->Cell(80, 8, '  ' . $comp['nom'], 1, 0, 'L', true);
        $pdf->Cell(50, 8, $level_label, 1, 0, 'C', true);
        $pdf->Cell(0, 8, $date_val, 1, 1, 'C', true);
    }
} else {
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->SetTextColor(156, 163, 175);
    $pdf->Cell(0, 10, 'Aucune competence declaree.', 0, 1);
}

$pdf->Ln(6);

// Badges section
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->SetTextColor(30, 64, 175);
$pdf->Cell(0, 10, 'Badges obtenus', 0, 1);
$pdf->SetTextColor(51, 51, 51);

if (count($badges) > 0) {
    $pdf->SetFont('Helvetica', '', 10);
    foreach ($badges as $badge) {
        $pdf->SetFillColor(219, 234, 254);
        $pdf->SetTextColor(29, 78, 216);
        $txt = '  ' . $badge['nom'] . ' (' . date('d/m/Y', strtotime($badge['date_obtention'])) . ')  ';
        $w = $pdf->GetStringWidth($txt) + 6;
        if ($pdf->GetX() + $w > 190) $pdf->Ln(8);
        $pdf->Cell($w, 8, $txt, 0, 0, 'L', true);
        $pdf->SetTextColor(51, 51, 51);
    }
    $pdf->Ln();
} else {
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->SetTextColor(156, 163, 175);
    $pdf->Cell(0, 10, 'Aucun badge obtenu.', 0, 1);
}

$filename = 'passport_' . $prenom . '_' . $nom . '.pdf';
$pdf->Output('D', $filename);
