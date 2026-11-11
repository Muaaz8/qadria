<?php
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : 0;
$userId = getUserId();

if (!canUserViewJob($pdo, $userId, $job_id)) {
    $_SESSION['error'] = "Permission denied!";
    header("Location: system.php?page=jobs");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();
if (!$job) {
    $_SESSION['error'] = "Job not found!";
    header("Location: system.php?page=jobs");
    exit();
}

$forwardingCombinedHeadings = getCombinedHeadings($pdo, 'forwarding', $userId);
$detentionCombinedHeadings = getCombinedHeadings($pdo, 'detention', $userId);
?>

<?php require_once 'includes/header.php'; ?>

<?php if (isset($_SESSION['error'])): ?><div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

<div class="dashboard-header"><div><h1 class="page-title">Create Bill for Job #: <?php echo htmlspecialchars($job['job_number']); ?></h1><p class="page-subtitle"><?php echo htmlspecialchars($job['consignee']); ?></p></div></div>

<div class="card"><div class="card-body"><form method="POST" action="system.php?page=save-bill" id="billForm"><input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
<div class="form-group"><label>Bill Type</label><select name="bill_type" id="bill_type" class="form-control" onchange="updateHeadingsAndLoadPreviousItems()"><option value="forwarding">Forwarding Bill</option><option value="detention">Detention / Placement Bill</option></select></div>
<div class="form-group"><label>Bill Items</label><div class="table-container"><table class="bill-items-table" style="width:100%; border-collapse:collapse;"><thead><tr style="background:#8e181e; color:white;"><th style="padding:10px; width:40%">Heading</th><th style="padding:10px; width:25%">Receipt Status</th><th style="padding:10px; width:25%">Amount (PKR)</th><th style="padding:10px; width:10%">Action</th></tr></thead><tbody id="itemsBody"></tbody></table></div><button type="button" class="add-row" onclick="addRow()" style="background:#8e181e; color:white; border:none; padding:8px 16px; border-radius:8px; margin-top:10px;"><i class="fas fa-plus"></i> Add Row</button></div>
<div class="form-row"><div class="form-group"><label>Total Amount</label><input type="number" name="total_amount" id="totalAmount" readonly class="form-control" style="background:#f5f9f5;"></div><div class="form-group"><label>Advance Amount</label><input type="number" name="advance_amount" id="advanceAmount" step="0.01" onkeyup="calculateBalance()" class="form-control"></div><div class="form-group"><label>Balance Amount</label><input type="number" name="balance_amount" id="balanceAmount" readonly class="form-control" style="background:#f5f9f5;"></div></div>
<div class="form-group"><label>Amount in Words</label><input type="text" id="balanceWords" readonly class="form-control" style="background:#f5f9f5;"></div>
<div class="form-group"><label>Notes</label><textarea name="notes" rows="3" class="form-control"></textarea></div>
<div style="margin-top:30px; display:flex; gap:15px;"><button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Bill</button><a href="system.php?page=jobs" class="btn-danger" style="background:#81c784; text-decoration:none; padding:8px 20px; border-radius:8px;">Cancel</a></div>
</form></div></div>

<script>
var jobId = <?php echo json_encode($job_id); ?>;
var forwardingHeadings = <?php echo json_encode($forwardingCombinedHeadings); ?>;
var detentionHeadings = <?php echo json_encode($detentionCombinedHeadings); ?>;

function updateHeadingsAndLoadPreviousItems() {
    var billType = document.getElementById('bill_type').value;
    var headings = billType == 'forwarding' ? forwardingHeadings : detentionHeadings;
    var selects = document.querySelectorAll('.heading-select');
    for(var k=0;k<selects.length;k++){var cv=selects[k].value;selects[k].innerHTML='<option value="">Select heading...</option>';for(var l=0;l<headings.length;l++){var opt=document.createElement('option');opt.value=headings[l];opt.text=headings[l];if(cv==headings[l])opt.selected=true;selects[k].appendChild(opt);}}
    fetch('system.php?page=get-previous-bill-items&job_id='+jobId+'&bill_type='+billType).then(r=>r.json()).then(data=>{if(data.items&&data.items.length>0){var tbody=document.getElementById('itemsBody');tbody.innerHTML='';for(var i=0;i<data.items.length;i++)addRowWithData(data.items[i].heading,data.items[i].receipt_status,data.items[i].amount);calculateTotal();}}).catch(err=>console.log(err));
}

function addRowWithData(headingValue,statusValue,amountValue){var tbody=document.getElementById('itemsBody');var billType=document.getElementById('bill_type').value;var headings=billType=='forwarding'?forwardingHeadings:detentionHeadings;var headingHtml='<td><select name="heading[]" class="heading-select" style="width:100%; padding:8px;"><option value="">Select heading...</option>';for(var k=0;k<headings.length;k++){var selected=(headings[k]==headingValue)?'selected':'';headingHtml+='<option value="'+headings[k]+'" '+selected+'>'+headings[k]+'</option>';}headingHtml+='</select><input type="text" name="heading_text[]" placeholder="Or custom" style="width:100%; margin-top:5px; padding:6px;" value="'+(headingValue&&!headings.includes(headingValue)?headingValue:'')+'"></td>';var statusHtml='<td><select name="receipt_status[]" class="form-control"><option value="Receipted"'+(statusValue=='Receipted'?' selected':'')+'>Receipted</option><option value="Unreceipted"'+(statusValue=='Unreceipted'?' selected':'')+'>Unreceipted</option></select></td>';var amountHtml='<td><input type="text" name="amount[]" onkeyup="calculateTotal()" placeholder="0.00" style="width:100%; padding:8px;" value="'+(amountValue?amountValue:'')+'"></td>';var actionHtml='<td><button type="button" class="remove-row" onclick="removeRow(this)" style="background:#ef5350; color:white; border:none; padding:5px 10px; border-radius:5px;"><i class="fas fa-trash"></i></button></td>';var newRow=document.createElement('tr');newRow.innerHTML=headingHtml+statusHtml+amountHtml+actionHtml;tbody.appendChild(newRow);}

function addRow(){var tbody=document.getElementById('itemsBody');var billType=document.getElementById('bill_type').value;var headings=billType=='forwarding'?forwardingHeadings:detentionHeadings;var headingHtml='<td><select name="heading[]" class="heading-select" style="width:100%; padding:8px;"><option value="">Select heading...</option>';for(var k=0;k<headings.length;k++)headingHtml+='<option value="'+headings[k]+'">'+headings[k]+'</option>';headingHtml+='</select><input type="text" name="heading_text[]" placeholder="Or custom" style="width:100%; margin-top:5px; padding:6px;"></td>';var statusHtml='<td><select name="receipt_status[]" class="form-control"><option value="Receipted">Receipted</option><option value="Unreceipted" selected>Unreceipted</option></select></td>';var amountHtml='<td><input type="text" name="amount[]" onkeyup="calculateTotal()" placeholder="0.00" style="width:100%; padding:8px;"></td>';var actionHtml='<td><button type="button" class="remove-row" onclick="removeRow(this)" style="background:#ef5350; color:white; border:none; padding:5px 10px; border-radius:5px;"><i class="fas fa-trash"></i></button></td>';var newRow=document.createElement('tr');newRow.innerHTML=headingHtml+statusHtml+amountHtml+actionHtml;tbody.appendChild(newRow);}

function removeRow(btn){if(document.querySelectorAll('#itemsBody tr').length>1){btn.closest('tr').remove();calculateTotal();}else alert("At least one row required!");}
function calculateTotal(){var amounts=document.querySelectorAll('#itemsBody input[name="amount[]"]');var total=0;for(var i=0;i<amounts.length;i++){var val=parseFloat(amounts[i].value);if(!isNaN(val))total+=val;}document.getElementById('totalAmount').value=total.toFixed(2);calculateBalance();}
function calculateBalance(){var total=parseFloat(document.getElementById('totalAmount').value)||0;var advance=parseFloat(document.getElementById('advanceAmount').value)||0;var balance=total-advance;document.getElementById('balanceAmount').value=balance.toFixed(2);var words=numberToWords(balance);document.getElementById('balanceWords').value=words+" Rupees Only";}
function numberToWords(n){if(n==0)return"Zero";var o=["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine","Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"];var t=["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];function c(n){if(n<20)return o[n];if(n<100)return t[Math.floor(n/10)]+(n%10?" "+o[n%10]:"");if(n<1000)return o[Math.floor(n/100)]+" Hundred"+(n%100?" "+c(n%100):"");if(n<100000)return c(Math.floor(n/1000))+" Thousand"+(n%1000?" "+c(n%1000):"");if(n<10000000)return c(Math.floor(n/100000))+" Lakh"+(n%100000?" "+c(n%100000):"");return c(Math.floor(n/10000000))+" Crore"+(n%10000000?" "+c(n%10000000):"");}return c(Math.floor(n));}
document.addEventListener('DOMContentLoaded',function(){addRow();calculateTotal();updateHeadingsAndLoadPreviousItems();});
</script>

<?php require_once 'includes/footer.php'; ?>