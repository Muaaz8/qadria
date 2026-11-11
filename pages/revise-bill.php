<?php
$userId = getUserId();
$bill_id = $_GET['id'];

if (!canUserViewBill($pdo, $userId, $bill_id)) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=bills");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM bills WHERE id = ? AND created_by = ?");
$stmt->execute([$bill_id, $userId]);
$original = $stmt->fetch();
if (!$original) { $_SESSION['error'] = "Bill not found!"; header("Location: system.php?page=bills"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$original['job_id']]);
$job = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = ? ORDER BY sort_order");
$stmt->execute([$bill_id]);
$original_items = $stmt->fetchAll();

$forwardingCombinedHeadings = getCombinedHeadings($pdo, 'forwarding', $userId);
$detentionCombinedHeadings = getCombinedHeadings($pdo, 'detention', $userId);
?>

<?php require_once 'includes/header.php'; ?>

<?php if (isset($_SESSION['error'])): ?><div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

<div class="dashboard-header"><div><h1 class="page-title">Revise Bill - Original: <?php echo htmlspecialchars($original['bill_number']); ?></h1><p class="page-subtitle">Job #: <?php echo htmlspecialchars($job['job_number']); ?></p></div></div>

<div class="card"><div class="card-body"><form method="POST" action="system.php?page=save-revised-bill" id="revisedBillForm">
<input type="hidden" name="job_id" value="<?php echo $original['job_id']; ?>">
<input type="hidden" name="parent_bill_id" value="<?php echo $original['id']; ?>">
<input type="hidden" name="revision_number" value="<?php echo ($original['revision_number'] ?? 0) + 1; ?>">
<div class="form-group"><label>Bill Type</label><select name="bill_type" id="bill_type" class="form-control" onchange="updateHeadings()"><option value="forwarding" <?php echo ($original['bill_type']=='forwarding')?'selected':''; ?>>Forwarding Bill</option><option value="detention" <?php echo ($original['bill_type']=='detention')?'selected':''; ?>>Detention Bill</option></select></div>
<div class="form-group"><label>Bill Items</label><div class="table-container"><table class="bill-items-table" style="width:100%;"><thead><tr style="background:#8e181e; color:white;"><th style="padding:10px; width:40%">Heading</th><th style="padding:10px; width:25%">Receipt Status</th><th style="padding:10px; width:25%">Amount</th><th style="padding:10px; width:10%">Action</th></tr></thead><tbody id="itemsBody"></tbody></table></div><button type="button" class="add-row" onclick="addRow()" style="background:#8e181e; color:white; border:none; padding:8px 16px; border-radius:8px; margin-top:10px;"><i class="fas fa-plus"></i> Add Row</button></div>
<div class="form-row"><div class="form-group"><label>Total Amount</label><input type="number" name="total_amount" id="totalAmount" readonly class="form-control" style="background:#f5f9f5;"></div><div class="form-group"><label>Advance Amount</label><input type="number" name="advance_amount" id="advanceAmount" step="0.01" onkeyup="calculateBalance()" class="form-control"></div><div class="form-group"><label>Balance Amount</label><input type="number" name="balance_amount" id="balanceAmount" readonly class="form-control" style="background:#f5f9f5;"></div></div>
<div class="form-group"><label>Amount in Words</label><input type="text" id="balanceWords" readonly class="form-control" style="background:#f5f9f5;"></div>
<div class="form-group"><label>Notes</label><textarea name="notes" rows="3" class="form-control"><?php echo htmlspecialchars($original['notes'] ?? ''); ?></textarea></div>
<div style="margin-top:30px; display:flex; gap:15px;"><button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Revised Bill</button><a href="system.php?page=bills" class="btn-danger" style="background:#81c784; text-decoration:none; padding:8px 20px; border-radius:8px;">Cancel</a></div>
</form></div></div>

<script>
var originalItems = <?php echo json_encode($original_items); ?>;
var forwardingHeadings = <?php echo json_encode($forwardingCombinedHeadings); ?>;
var detentionHeadings = <?php echo json_encode($detentionCombinedHeadings); ?>;

function updateHeadings(){var bt=document.getElementById('bill_type').value;var headings=bt=='forwarding'?forwardingHeadings:detentionHeadings;var selects=document.querySelectorAll('.heading-select');for(var k=0;k<selects.length;k++){var cv=selects[k].value;selects[k].innerHTML='<option value="">Select heading...</option>';for(var l=0;l<headings.length;l++){var opt=document.createElement('option');opt.value=headings[l];opt.text=headings[l];if(cv==headings[l])opt.selected=true;selects[k].appendChild(opt);}}}
function addRowWithData(h,s,a){var tbody=document.getElementById('itemsBody');var bt=document.getElementById('bill_type').value;var headings=bt=='forwarding'?forwardingHeadings:detentionHeadings;var hh='<td><select name="heading[]" class="heading-select" style="width:100%; padding:8px;"><option value="">Select heading...</option>';for(var k=0;k<headings.length;k++){var sl=(headings[k]==h)?'selected':'';hh+='<option value="'+headings[k]+'" '+sl+'>'+headings[k]+'</option>';}hh+='</select><input type="text" name="heading_text[]" placeholder="Or custom" style="width:100%; margin-top:5px; padding:6px;" value="'+(h&&!headings.includes(h)?h:'')+'"></td>';var sh='<td><select name="receipt_status[]" class="form-control"><option value="Receipted"'+(s=='Receipted'?' selected':'')+'>Receipted</option><option value="Unreceipted"'+(s=='Unreceipted'?' selected':'')+'>Unreceipted</option></select></td>';var ah='<td><input type="text" name="amount[]" onkeyup="calculateTotal()" placeholder="0.00" style="width:100%; padding:8px;" value="'+(a?a:'')+'"></td>';var ac='<td><button type="button" class="remove-row" onclick="removeRow(this)" style="background:#ef5350; color:white; border:none; padding:5px 10px;"><i class="fas fa-trash"></i></button></td>';var nr=document.createElement('tr');nr.innerHTML=hh+sh+ah+ac;tbody.appendChild(nr);}
function addRow(){var tbody=document.getElementById('itemsBody');var bt=document.getElementById('bill_type').value;var headings=bt=='forwarding'?forwardingHeadings:detentionHeadings;var hh='<td><select name="heading[]" class="heading-select" style="width:100%; padding:8px;"><option value="">Select heading...</option>';for(var k=0;k<headings.length;k++)hh+='<option value="'+headings[k]+'">'+headings[k]+'</option>';hh+='</select><input type="text" name="heading_text[]" placeholder="Or custom" style="width:100%; margin-top:5px; padding:6px;"></td>';var sh='<td><select name="receipt_status[]" class="form-control"><option value="Receipted">Receipted</option><option value="Unreceipted" selected>Unreceipted</option></select></td>';var ah='<td><input type="text" name="amount[]" onkeyup="calculateTotal()" placeholder="0.00" style="width:100%; padding:8px;"></td>';var ac='<td><button type="button" class="remove-row" onclick="removeRow(this)" style="background:#ef5350; color:white; border:none; padding:5px 10px;"><i class="fas fa-trash"></i></button></td>';var nr=document.createElement('tr');nr.innerHTML=hh+sh+ah+ac;tbody.appendChild(nr);}
function removeRow(btn){if(document.querySelectorAll('#itemsBody tr').length>1){btn.closest('tr').remove();calculateTotal();}else alert("At least one row required!");}
function calculateTotal(){var amts=document.querySelectorAll('#itemsBody input[name="amount[]"]');var tot=0;for(var i=0;i<amts.length;i++){var v=parseFloat(amts[i].value);if(!isNaN(v))tot+=v;}document.getElementById('totalAmount').value=tot.toFixed(2);calculateBalance();}
function calculateBalance(){var tot=parseFloat(document.getElementById('totalAmount').value)||0;var adv=parseFloat(document.getElementById('advanceAmount').value)||0;var bal=tot-adv;document.getElementById('balanceAmount').value=bal.toFixed(2);var w=numberToWords(bal);document.getElementById('balanceWords').value=w+" Rupees Only";}
function numberToWords(n){if(n==0)return"Zero";var o=["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine","Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"];var t=["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];function c(n){if(n<20)return o[n];if(n<100)return t[Math.floor(n/10)]+(n%10?" "+o[n%10]:"");if(n<1000)return o[Math.floor(n/100)]+" Hundred"+(n%100?" "+c(n%100):"");if(n<100000)return c(Math.floor(n/1000))+" Thousand"+(n%1000?" "+c(n%1000):"");if(n<10000000)return c(Math.floor(n/100000))+" Lakh"+(n%100000?" "+c(n%100000):"");return c(Math.floor(n/10000000))+" Crore"+(n%10000000?" "+c(n%10000000):"");}return c(Math.floor(n));}
document.addEventListener('DOMContentLoaded',function(){if(originalItems&&originalItems.length>0){for(var i=0;i<originalItems.length;i++)addRowWithData(originalItems[i].heading,originalItems[i].receipt_status,originalItems[i].amount);}else addRow();calculateTotal();updateHeadings();});
</script>

<?php require_once 'includes/footer.php'; ?>