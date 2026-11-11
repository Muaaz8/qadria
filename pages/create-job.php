<?php
$bonded_carriers = getMasters($pdo, 'bonded_carrier');
$customer_names = getMasters($pdo, 'customer_name');
$consignees = getMasters($pdo, 'consignee');
$containerSizes = getMasters($pdo, 'container_size');
$khiPorts = getMasters($pdo, 'khi_port');
$dryPorts = getMasters($pdo, 'dry_port');
$vessel_names = getMasters($pdo, 'vessel_name');
$shippingLines = getMasters($pdo, 'shipping_line');
$freight_forwarders = getMasters($pdo, 'freight_forwarder_name');
$depositors = getMasters($pdo, 'depositor');
$nextJobNumber = generateJobNumber($pdo);
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="dashboard-header">
    <div><h1 class="page-title">Create New Job</h1><p class="page-subtitle">Enter job details</p></div>
</div>

<div class="card"><div class="card-body">
    <form method="POST" action="system.php?page=create-job" id="jobForm">
        <div class="form-row">
            <div class="form-group"><label>Job Number (Auto)</label><input type="text" value="<?php echo $nextJobNumber; ?>" readonly class="form-control"></div>
            <div class="form-group"><label>Original Documents Received on</label><input type="date" name="original_documents_date" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Job Creation Dt</label><input type="date" name="job_date" value="<?php echo date('Y-m-d'); ?>" class="form-control"></div>
            <div class="form-group"><label>Bonded Carrier Name</label>
                <select name="bonded_carrier" class="form-control"><option value="">Select</option><?php foreach($bonded_carriers as $b): ?><option value="<?php echo htmlspecialchars($b['name']); ?>"><?php echo htmlspecialchars($b['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="bonded_carrier_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>D/O Collected on</label><input type="date" name="do_collected_date" class="form-control"></div>
            <div class="form-group"><label>Customer Name</label>
                <select name="customer_name" class="form-control"><option value="">Select</option><?php foreach($customer_names as $c): ?><option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="customer_name_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Consignee</label>
                <select name="consignee" class="form-control"><option value="">Select</option><?php foreach($consignees as $c): ?><option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="consignee_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
            <div class="form-group"><label>BL Number</label><input type="text" name="bl_number" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Container Number</label><input type="text" name="container_number" class="form-control"></div>
            <div class="form-group"><label>Container Size</label>
                <select name="container_size" class="form-control"><option value="">Select</option><?php foreach($containerSizes as $cs): ?><option value="<?php echo htmlspecialchars($cs['name']); ?>"><?php echo htmlspecialchars($cs['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="container_size_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>KHI Port</label>
                <select name="khi_port" class="form-control"><option value="">Select</option><?php foreach($khiPorts as $kp): ?><option value="<?php echo htmlspecialchars($kp['name']); ?>"><?php echo htmlspecialchars($kp['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="khi_port_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
            <div class="form-group"><label>Dry Port</label>
                <select name="dry_port" class="form-control"><option value="">Select</option><?php foreach($dryPorts as $dp): ?><option value="<?php echo htmlspecialchars($dp['name']); ?>"><?php echo htmlspecialchars($dp['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="dry_port_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Port of Loading</label><input type="text" name="port_of_loading" class="form-control"></div>
            <div class="form-group"><label>Port of Discharge</label><input type="text" name="port_of_discharge" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Vessel Name</label>
                <select name="vessel_name" class="form-control"><option value="">Select</option><?php foreach($vessel_names as $v): ?><option value="<?php echo htmlspecialchars($v['name']); ?>"><?php echo htmlspecialchars($v['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="vessel_name_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
            <div class="form-group"><label>Vessel Arrival Date</label><input type="date" name="vessel_arrival_date" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Shipping Line</label>
                <select name="shipping_line" class="form-control"><option value="">Select</option><?php foreach($shippingLines as $sl): ?><option value="<?php echo htmlspecialchars($sl['name']); ?>"><?php echo htmlspecialchars($sl['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="shipping_line_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
            <div class="form-group"><label>Freight Forwarder Name</label>
                <select name="freight_forwarder_name" class="form-control"><option value="">Select</option><?php foreach($freight_forwarders as $ff): ?><option value="<?php echo htmlspecialchars($ff['name']); ?>"><?php echo htmlspecialchars($ff['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="freight_forwarder_name_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Depositor</label>
                <select name="depositor" class="form-control"><option value="">Select</option><?php foreach($depositors as $d): ?><option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option><?php endforeach; ?></select>
                <input type="text" name="depositor_text" class="form-control" style="margin-top:5px;" placeholder="Or enter new">
            </div>
            <div class="form-group"><label>Free Time</label><input type="text" name="free_time" class="form-control"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Detention Tariff</label><input type="text" name="detention_tariff" class="form-control"></div>
            <div class="form-group"><label>SOC</label><select name="soc" class="form-control"><option value="No">No</option><option value="Yes">Yes</option></select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Mobile No</label><input type="text" name="mobile_no" class="form-control"></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="form-group"><label>Remarks</label><textarea name="remarks" rows="3" class="form-control"></textarea></div>
        <div style="margin-top:30px; display:flex; gap:15px;">
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Job</button>
            <a href="system.php?page=jobs" class="btn-danger" style="background:#81c784; text-decoration:none; padding:8px 20px; border-radius:8px;">Cancel</a>
        </div>
    </form>
</div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>