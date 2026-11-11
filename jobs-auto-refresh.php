<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

$query = "SELECT j.*, u.full_name as created_by_name 
          FROM jobs j 
          LEFT JOIN users u ON j.created_by = u.id 
          ORDER BY j.id DESC LIMIT 50";

$stmt = $pdo->query($query);

while($job = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $socClass = ($job['soc'] == 'Yes') ? 'active' : 'cancelled';
    ?>
    <tr ondblclick="showJobWithBills(<?php echo $job['id']; ?>)" 
        data-job-id="<?php echo $job['id']; ?>" 
        style="cursor:pointer;">
        
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <strong><?php echo htmlspecialchars($job['job_number'] ?? ''); ?></strong>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo !empty($job['job_date']) ? date('d-m-Y', strtotime($job['job_date'])) : '-'; ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['original_documents_date'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['bonded_carrier'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['do_collected_date'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['customer_name'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['consignee'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['bl_number'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['container_number'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['container_size'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['vessel_name'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo !empty($job['vessel_arrival_date']) ? date('d-m-Y', strtotime($job['vessel_arrival_date'])) : '-'; ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;" 
            title="<?php echo htmlspecialchars($job['description'] ?? ''); ?>">
            <?php echo htmlspecialchars(substr($job['description'] ?? '-', 0, 50)); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['free_till'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['port_of_loading'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['port_of_discharge'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['dry_port'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['shipping_line'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['freight_forwarder_name'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['depositor'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['free_time'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['detention_tariff'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <span class="status-badge <?php echo $socClass; ?>">
                <?php echo htmlspecialchars($job['soc'] ?? 'No'); ?>
            </span>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['mobile_no'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['mbl_number'] ?? '-'); ?>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <span class="status-badge <?php echo $job['status'] ?? 'active'; ?>">
                <?php echo htmlspecialchars($job['status'] ?? 'active'); ?>
            </span>
        </td>
        <td style="padding:8px 6px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;">
            <?php echo htmlspecialchars($job['created_by_name'] ?? '-'); ?>
        </td>
        <td class="action-cell" style="padding:8px 6px; border-bottom:1px solid #e8eaed; white-space:nowrap; text-align:center;">
            <button class="btn-info btn-sm" onclick="event.stopPropagation(); showJobWithBills(<?php echo $job['id']; ?>)" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn-warning btn-sm" onclick="event.stopPropagation(); editJob(<?php echo $job['id']; ?>)" title="Edit Job">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn-danger btn-sm" onclick="event.stopPropagation(); deleteJob(<?php echo $job['id']; ?>)" title="Delete Job">
                <i class="fas fa-trash"></i>
            </button>
            <button class="btn-primary btn-sm" onclick="event.stopPropagation(); checkAndCreateBill(<?php echo $job['id']; ?>)" title="Create Bill">
                <i class="fas fa-file-invoice"></i>
            </button>
        </td>
    </tr>
    <?php
}
?>