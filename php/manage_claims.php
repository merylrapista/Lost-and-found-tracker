<?php
session_start();
require_once "conn.php";

// Admin restriction
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📦 Manage Claims | Admin Panel</title>
<style>
body {font-family:'Poppins',sans-serif;margin:0;background:#f7f9fc;display:flex;min-height:100vh;}
.sidebar {width:230px;background:#0b2447;color:white;display:flex;flex-direction:column;align-items:center;padding:30px 0;position:fixed;height:100%;}
.sidebar h2 {margin-bottom:40px;font-size:22px;color:#00aaff;}
.sidebar a {text-decoration:none;color:white;width:80%;background:rgba(255,255,255,0.1);margin:6px 0;padding:12px;border-radius:10px;text-align:center;transition:background 0.3s;font-weight:500;}
.sidebar a:hover {background:rgba(255,255,255,0.3);}
.sidebar a.active {background:rgba(255,255,255,0.35);font-weight:bold;}
.main-content {flex:1;margin-left:230px;padding:20px;overflow:auto;}
header {background:#145aa3;color:white;padding:22px 20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);margin-bottom:10px;}
header h1 {margin:0;font-size:24px;}
.tab-container {margin-bottom:20px;}
.tab-btn {padding:8px 16px;cursor:pointer;border:none;border-radius:6px 6px 0 0;background:#ccc;margin-right:5px;font-weight:bold;transition:0.3s;}
.tab-btn.active {background:#145aa3;color:white;}
.tab-content {display:none;}
.tab-content.active {display:block;}
table {width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.1);margin-bottom:30px;}
th,td {padding:12px 15px;text-align:left;border-bottom:1px solid #ddd;font-size:14px;}
th {background:#145aa3;color:white;}
tr:hover {background:#f1f1f1;}
.action-btn {padding:6px 12px;border:none;border-radius:6px;color:white;cursor:pointer;transition:all 0.3s ease;font-size:14px;margin-right:4px;}
.activate {background:#145aa3;}
.deactivate {background:#ff6b6b;}
.view-btn {background:#3498db;}
.disabled {opacity:0.6;cursor:not-allowed;}
.status-pending {color:#888;font-weight:bold;}
.status-approved {color:#1dd1a1;font-weight:bold;}
.status-rejected {color:#b30000;font-weight:bold;}

/* Modal */
.modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
  transition: opacity 0.3s ease;
}

.modal-content {
  background: #fff;
  padding: 25px 30px;
  border-radius: 12px;
  width: 520px;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 8px 25px rgba(0,0,0,0.25);
  position: relative;
  animation: fadeIn 0.3s ease;
}

.close-modal {
  position: absolute;
  top: 10px; right: 15px;
  background: #ff6b6b;
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px; height: 28px;
  cursor: pointer;
  font-weight: bold;
}

.modal-body p {
  margin: 8px 0;
  line-height: 1.5;
  font-size: 14px;
}

.status-text {
  font-weight: bold;
  color: #145aa3;
}

#proof-section img {
  display: block;
  margin-top: 10px;
  border-radius: 10px;
  max-width: 100%;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.modal-footer {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@keyframes fadeIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

</style>
</head>
<body>
<div class="sidebar">
  <h2>⚙️ Admin Panel</h2>
  <a href="admin_dashboard.php">🏠 Dashboard</a>
  <a href="manage_users.php">👥 Manage Users</a>
  <a href="manage_reports.php">📄 Manage Reports</a>
  <a href="manage_claims.php" class="active">📦 Manage Claims</a>
  <a href="admin_notifications.php">🔔 Notifications</a>
  <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
<header><h1> Manage Claims</h1><p>Approve or reject user claims.</p></header>

<div class="tab-container">
  <button class="tab-btn active" data-tab="pending">Pending</button>
  <button class="tab-btn" data-tab="approved">Approved</button>
  <button class="tab-btn" data-tab="rejected">Rejected</button>
</div>

<div class="tab-content active" id="pending">
  <table id="pending-table">
    <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Claimant</th><th>Reporter</th><th>Status</th><th>Claimed At</th><th>Actions</th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<div class="tab-content" id="approved">
  <table id="approved-table">
    <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Claimant</th><th>Reporter</th><th>Status</th><th>Claimed At</th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<div class="tab-content" id="rejected">
  <table id="rejected-table">
    <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Claimant</th><th>Reporter</th><th>Status</th><th>Claimed At</th></tr></thead>
    <tbody></tbody>
  </table>
</div>
</div>

<!-- ✅ Modal -->
<!-- ✅ Modernized Modal -->
<div class="modal" id="claimModal">
  <div class="modal-content">
    <button class="close-modal">&times;</button>
    <h2 style="color:#145aa3;margin-bottom:10px;">🧾 Claim Verification Details</h2>
    
    <div class="modal-body">
      <p><strong>Verification Question 1:</strong> <span id="q1"></span></p>
      <p><strong>Verification Question 2:</strong> <span id="q2"></span></p>
      <p><strong>Verification Question 3:</strong> <span id="q3"></span></p>
      <p><strong>Additional Description:</strong> <span id="desc"></span></p>
      <p><strong>Status:</strong> <span id="claim-status" class="status-text"></span></p>
      <p><strong>Resolved At:</strong> <span id="resolved-at"></span></p>
      <div id="proof-section"></div>
    </div>

    <div class="modal-footer">
      <button id="approveBtn" class="action-btn activate"> Approve</button>
      <button id="rejectBtn" class="action-btn deactivate">Reject</button>
    </div>
  </div>
</div>


<script>
const tabs = document.querySelectorAll('.tab-btn');
const contents = document.querySelectorAll('.tab-content');
tabs.forEach(tab => tab.addEventListener('click', () => {
  tabs.forEach(t => t.classList.remove('active'));
  contents.forEach(c => c.classList.remove('active'));
  tab.classList.add('active');
  document.getElementById(tab.dataset.tab).classList.add('active');
}));

let claimsData = [];

async function fetchClaims() {
  const res = await fetch('fetch_claims.php');
  const data = await res.json();
  claimsData = data;
  renderTable('pending-table', data.pending, true);
  renderTable('approved-table', data.approved);
  renderTable('rejected-table', data.rejected);
}

function renderTable(tableId, claims, showActions=false) {
  const tbody = document.querySelector(`#${tableId} tbody`);
  tbody.innerHTML = '';
  claims.forEach((c, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${i+1}</td>
      <td>${c.item_name}</td>
      <td>${c.category}</td>
      <td>${c.claimant_name}</td>
      <td>${c.reporter_name}</td>
      <td class="status-${c.status.toLowerCase()}">${c.status}</td>
      <td>${new Date(c.created_at).toLocaleDateString()}</td>
      ${showActions ? `<td><button class="action-btn view-btn" data-id="${c.id}">View Details</button></td>` : ''}
    `;
    tbody.appendChild(tr);
  });
}

const modal = document.getElementById('claimModal');
const closeBtn = document.querySelector('.close-modal');
let currentClaimId = null;

document.addEventListener('click', e => {
  if (e.target.classList.contains('view-btn')) {
    const id = e.target.dataset.id;
    const claim = [...claimsData.pending, ...claimsData.approved, ...claimsData.rejected]
      .find(c => c.id == id);
    if (!claim) return;

    currentClaimId = id;
    document.getElementById('q1').textContent = claim.verification_q1 || 'N/A';
    document.getElementById('q2').textContent = claim.verification_q2 || 'N/A';
    document.getElementById('q3').textContent = claim.verification_q3 || 'N/A';
    document.getElementById('desc').textContent = claim.additional_description || 'N/A';
    document.getElementById('claim-status').textContent = claim.status || 'Pending';
    document.getElementById('resolved-at').textContent = claim.resolved_at 
      ? new Date(claim.resolved_at).toLocaleString() 
      : 'Not resolved yet';
    document.getElementById('proof-section').innerHTML = claim.proof_image 
      ? `<p><strong>Proof Image:</strong></p><img src="${claim.proof_image}" alt="Proof">`
      : '<p><em>No proof image provided.</em></p>';

    // Hide buttons if claim already processed
    document.getElementById('approveBtn').style.display = 
      (claim.status === 'Pending') ? 'inline-block' : 'none';
    document.getElementById('rejectBtn').style.display = 
      (claim.status === 'Pending') ? 'inline-block' : 'none';

    modal.style.display = 'flex';
  }
});

closeBtn.addEventListener('click', () => modal.style.display = 'none');

document.getElementById('approveBtn').addEventListener('click', () => handleClaimAction('approve'));
document.getElementById('rejectBtn').addEventListener('click', () => handleClaimAction('reject'));

async function handleClaimAction(action) {
  const fd = new FormData();
  fd.append('action', action);
  fd.append('id', currentClaimId);
  const res = await fetch('update_claim.php', { method: 'POST', body: fd });
  const result = await res.json();
  if (result.success) {
    alert(`Claim ${action === 'approve' ? 'approved' : 'rejected'} successfully!`);
    modal.style.display = 'none';
    fetchClaims();
  } else {
    alert('Error: ' + (result.error || 'Unknown error'));
  }
}

fetchClaims();
</script>

</script>
</body>
</html>
