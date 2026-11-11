<?php
$userId = getUserId();
$bill_id = $_GET['id'];

if (!canUserViewBill($pdo, $userId, $bill_id)) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=bills");
    exit();
}

if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT b.*, j.job_number, j.consignee, j.bl_number, j.bonded_carrier, j.do_collected_date, j.container_number, j.container_size, j.vessel_name, j.port_of_loading, j.port_of_discharge, j.vessel_arrival_date, j.shipping_line, j.customer_name, j.free_time, j.khi_port, j.dry_port, j.freight_forwarder_name, j.depositor, j.detention_tariff, j.soc, j.mobile_no, j.description, j.original_documents_date, j.job_date, j.remarks FROM bills b LEFT JOIN jobs j ON b.job_id = j.id WHERE b.id = ?");
    $stmt->execute([$bill_id]);
} else {
    $stmt = $pdo->prepare("SELECT b.*, j.job_number, j.consignee, j.bl_number, j.bonded_carrier, j.do_collected_date, j.container_number, j.container_size, j.vessel_name, j.port_of_loading, j.port_of_discharge, j.vessel_arrival_date, j.shipping_line, j.customer_name, j.free_time, j.khi_port, j.dry_port, j.freight_forwarder_name, j.depositor, j.detention_tariff, j.soc, j.mobile_no, j.description, j.original_documents_date, j.job_date, j.remarks FROM bills b LEFT JOIN jobs j ON b.job_id = j.id WHERE b.id = ? AND b.created_by = ?");
    $stmt->execute([$bill_id, $userId]);
}
$bill = $stmt->fetch();
if (!$bill) { $_SESSION['error'] = "Bill not found!"; header("Location: system.php?page=bills"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = ? ORDER BY sort_order");
$stmt->execute([$bill_id]);
$bill_items = $stmt->fetchAll();
$bill_items = array_filter($bill_items, function($item) { return floatval($item['amount'] ?? 0) > 0; });
?>

<?php require_once 'includes/header.php'; ?>

<div class="no-print" style="margin-bottom:20px; display:flex; gap:10px; justify-content:flex-end;">
    <button class="btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    <button class="btn-danger" onclick="location.href='system.php?page=export-pdf&id=<?php echo $bill_id; ?>'"><i class="fas fa-file-pdf"></i> PDF</button>
    <button class="btn-success" onclick="location.href='system.php?page=export-excel&id=<?php echo $bill_id; ?>'"><i class="fas fa-file-excel"></i> Excel</button>
    <button class="btn-danger" onclick="location.href='system.php?page=bills'" style="background:#81c784;"><i class="fas fa-arrow-left"></i> Back</button>
</div>

<div class="invoice-wrapper" style="background:white; border-radius:16px; padding:30px; max-width:1000px; margin:0 auto;">
    <div class="invoice-header" style="text-align:center; border-bottom:2px solid #8e181e; padding-bottom:20px; margin-bottom:30px;">
        <img class="logo-img" src="img/logo.png" alt="Logo" style="height:50px;" onerror="this.style.display='none'">
        <p>Suite # 123, Business Center, Karachi, Pakistan</p>
        <p>Tel: 32420877 | Email: Qadriainternational5@gmail.com</p>
    </div>
    <div style="text-align:center; font-size:18px; font-weight:bold; margin:20px 0; color:#8e181e;"><?php echo ($bill['is_revised'] ? 'REVISED ' : '') . strtoupper($bill['bill_type']); ?> BILL</div>
    
    <div style="display:flex; justify-content:space-between; margin-bottom:30px; padding:15px; background:#f8fafd; border-radius:8px;">
        <div><p><strong>Bill to:</strong><br><?php echo htmlspecialchars($bill['customer_name']); ?></p><p><strong>Messer:</strong><br><?php echo htmlspecialchars($bill['consignee']); ?></p></div>
        <div style="text-align:right;"><p><strong>Bill Id:</strong> <?php echo htmlspecialchars($bill['bill_number']); ?></p><p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($bill['created_at'])); ?></p><p><strong>Job Creation Dt:</strong> <?php echo !empty($bill['job_date']) ? date('d-m-Y', strtotime($bill['job_date'])) : '-'; ?></p></div>
    </div>
    
    <div style="margin-bottom:30px; padding:15px; background:#fff8e1; border-radius:8px; border-left:4px solid #479ace;">
        <h4 style="margin:0 0 10px 0; color:#e65100;">SHIPMENT DETAILS</h4>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; font-size:12px;">
            <div><strong>BL Number:</strong><br><?php echo htmlspecialchars($bill['bl_number'] ?? '-'); ?></div>
            <div><strong>Container Number:</strong><br><?php echo htmlspecialchars($bill['container_number'] ?? '-'); ?></div>
            <div><strong>Container Size:</strong><br><?php echo htmlspecialchars($bill['container_size'] ?? '-'); ?></div>
            <div><strong>Vessel Name:</strong><br><?php echo htmlspecialchars($bill['vessel_name'] ?? '-'); ?></div>
            <div><strong>Port of Discharge:</strong><br><?php echo htmlspecialchars($bill['port_of_discharge'] ?? '-'); ?></div>
            <div><strong>Vessel Arrival Date:</strong><br><?php echo !empty($bill['vessel_arrival_date']) ? date('d-m-Y', strtotime($bill['vessel_arrival_date'])) : '-'; ?></div>
            <div><strong>Shipping Line:</strong><br><?php echo htmlspecialchars($bill['shipping_line'] ?? '-'); ?></div>
            <div><strong>Free Time:</strong><br><?php echo htmlspecialchars($bill['free_time'] ?? '-'); ?></div>
            <div><strong>Bonded Carrier:</strong><br><?php echo htmlspecialchars($bill['bonded_carrier'] ?? '-'); ?></div>
            <div><strong>Dry Port:</strong><br><?php echo htmlspecialchars($bill['dry_port'] ?? '-'); ?></div>
            <div><strong>SOC:</strong><br><?php echo htmlspecialchars($bill['soc'] ?? 'No'); ?></div>
            <div><strong>Description:</strong><br><?php echo htmlspecialchars(substr($bill['description'] ?? '-', 0, 50)); ?></div>
        </div>
    </div>
    
    <table style="width:100%; border-collapse:collapse; margin-bottom:30px;">
        <thead><tr style="background:#8e181e; color:white;"><th style="padding:10px;">#</th><th style="padding:10px;">Description</th><th style="padding:10px;">Status</th><th style="padding:10px;">Amount (PKR)</th></tr></thead>
        <tbody><?php $counter = 1; foreach($bill_items as $item): ?><tr><td style="padding:10px; border-bottom:1px solid #ddd;"><?php echo $counter++; ?></td><td style="padding:10px; border-bottom:1px solid #ddd;"><?php echo htmlspecialchars($item['heading']); ?></td><td style="padding:10px; border-bottom:1px solid #ddd;"><?php echo $item['receipt_status'] ?? 'Unreceipted'; ?></td><td style="padding:10px; border-bottom:1px solid #ddd; text-align:right;"><?php echo number_format($item['amount'], 2); ?></td></tr><?php endforeach; ?></tbody>
    </table>
    
    <div style="text-align:right; margin-top:20px; padding-top:20px; border-top:2px solid #8e181e;">
        <p>Total: PKR <?php echo number_format($bill['total_amount'], 2); ?></p>
        <p>Advance: PKR <?php echo number_format($bill['advance_amount'], 2); ?></p>
        <p><strong>Balance Due: PKR <?php echo number_format($bill['balance_amount'], 2); ?></strong></p>
        <?php if($bill['balance_words']): ?><p><small><?php echo $bill['balance_words']; ?></small></p><?php endif; ?>
    </div>
    
    <div style="text-align:center; margin-top:50px; font-size:10px; color:#666; border-top:1px solid #eee; padding-top:20px;">
        <p>If you have any questions, please contact: Tell # 32420877 | Email: Qadriainternational5@gmail.com</p>
        <p>Thank You For Your Business! | NTN# 1234567-8 | ST# 123456789</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>