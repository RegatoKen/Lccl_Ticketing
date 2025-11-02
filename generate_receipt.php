<?php
session_start();
require 'config/db_connect.php';
require 'vendor/tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['payment_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_id = intval($_GET['payment_id']);

// Fetch payment and ticket details
$stmt = $conn->prepare("
    SELECT p.*, u.username 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->bind_param("ii", $payment_id, $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die('Payment not found');
}

// Fetch tickets for this payment
$tickets = [];
$stmt = $conn->prepare("
    SELECT e.title, e.date, e.price, t.quantity
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id = ? AND t.created_at >= DATE_SUB(?, INTERVAL 30 SECOND)
    ORDER BY e.title
");
$stmt->bind_param("is", $user_id, $payment['created_at']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

// Create PDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'LCCL Ticketing System', 0, true, 'C', 0);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Official Receipt', 0, true, 'C', 0);
        $this->Ln(10);
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0);
    }
}

// Initialize PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('LCCL Ticketing');
$pdf->SetAuthor('LCCL Ticketing System');
$pdf->SetTitle('Receipt #' . $payment_id);

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Add page
$pdf->AddPage();

// Receipt details
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Receipt No: ' . $payment_id, 0, 1);
$pdf->Cell(0, 10, 'Date: ' . $payment['created_at'], 0, 1);
$pdf->Cell(0, 10, 'Customer: ' . htmlspecialchars($payment['username']), 0, 1);
$pdf->Cell(0, 10, 'Payment Method: ' . $payment['method'], 0, 1);
$pdf->Ln(10);

// Table header
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(80, 10, 'Event', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Price', 1, 1, 'C', true);

// Table content
$pdf->SetFont('helvetica', '', 12);
$total = 0;
foreach ($tickets as $ticket) {
    $subtotal = $ticket['price'] * $ticket['quantity'];
    $total += $subtotal;
    
    $pdf->Cell(80, 10, $ticket['title'], 1);
    $pdf->Cell(35, 10, date('M d, Y', strtotime($ticket['date'])), 1);
    $pdf->Cell(25, 10, $ticket['quantity'], 1, 0, 'C');
    $pdf->Cell(35, 10, '₱ ' . number_format($subtotal, 2), 1, 1, 'R');
}

// Total
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(140, 10, 'Total Amount:', 1, 0, 'R', true);
$pdf->Cell(35, 10, '₱ ' . number_format($total, 2), 1, 1, 'R', true);

// Output PDF
$pdf->Output('LCCL_Receipt_' . $payment_id . '.pdf', 'D');