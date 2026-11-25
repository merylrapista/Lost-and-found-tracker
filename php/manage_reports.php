<?php
session_start();
require_once "conn.php";

// Only allow admin
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
<title>📄 Manage Reports | Admin Panel</title>
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
header p {margin:5px 0 0 0;font-size:14px;text-align:right;}

/* Tabs */
.tab-container {margin-bottom:20px;}
.tab-btn {
    padding:8px 16px;
    cursor:pointer;
    border:none;
    border-radius:6px 6px 0 0;
    background:#ccc;
    margin-right:5px;
    font-weight:bold;
    transition:0.3s;
}
.tab-btn.active { background:#145aa3; color:white; }
.tab-content {display:none;}
.tab-content.active { display:block; }

table {width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.1);margin-bottom:30px;}
th, td {padding:12px 15px;text-align:left;border-bottom:1px solid #ddd;font-size:14px;}
th {background:#145aa3;color:white;}
tr:hover {background:#f1f1f1;}

.action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    margin-right: 4px;
}

/* Approve / Activate */
.activate { background: #1dd1a1; }
.activate:hover { background: #16a085; }

/* Deactivate / Reject */
.deactivate { background: #ff6b6b; }
.deactivate:hover { background: #c0392b; }

/* Disabled state */
.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none; /* prevent hover effects */
    filter: grayscale(50%); /* optional, makes it look faded */
}


.status-missing { color:#ff6b6b; font-weight:bold; }
.status-open { color:#1dd1a1; font-weight:bold; }
.status-rejected { color:#b30000; font-weight:bold; }
.status-claimed { color:#0088cc; font-weight:bold; }
.status-pending { color:#888; font-weight:bold; }
.status-approved { 
    color: #1dd1a1;  /* same green as Open, or choose a different color */ 
    font-weight: bold; 
}

</style>
</head>
<body>

<div class="sidebar">
    <h2>⚙️ Admin Panel</h2>
    <a href="admin_dashboard.php" class="<?= $current_page=='admin_dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_reports.php" class="active">📄 Manage Reports</a>
    <a href="manage_claims.php">📦 Manage Claims</a>
    <a href="admin_notifications.php">🔔 Notifications</a>
    <a href="../html/index.html">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1> Manage Reports</h1>
        <p>Approve, reject, or mark items as claimed.</p>
    </header>

    <div class="tab-container">
        <button class="tab-btn active" data-tab="lost">Lost Items</button>
        <button class="tab-btn" data-tab="found">Found Items</button>
    </div>

    <!-- Lost Items Tab -->
    <div class="tab-content active" id="lost">
        <table id="lost-table">
            <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Item</th>
                <th>Category</th>
                <th>Location</th>
                <th>Date Reported</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Found Items Tab -->
    <div class="tab-content" id="found">
        <table id="found-table">
            <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Item</th>
                <th>Category</th>
                <th>Location</th>
                <th>Date Reported</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
// Tab switching
const tabs = document.querySelectorAll('.tab-btn');
const contents = document.querySelectorAll('.tab-content');
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});

// Fetch reports and render rows
async function fetchReports() {
    const response = await fetch('fetch_reports.php');
    const data = await response.json();

    function renderRows(tableId, reports) {
        const tbody = document.querySelector(`#${tableId} tbody`);
        tbody.innerHTML = '';
        reports.forEach((r, i) => {
            const tr = document.createElement('tr');
            tr.dataset.id = r.report_id;
            tr.innerHTML = `
                <td>${i+1}</td>
                <td>${r.full_name}</td>
                <td>${r.item_name}</td>
                <td>${r.category}</td>
                <td>${r.location}</td>
                <td>${new Date(r.date_reported).toLocaleDateString()}</td>
                <td class="status-${r.status.toLowerCase()}">${r.status}</td>
                <td>
                    <button class="action-btn activate" ${r.status === 'Missing' || r.status === 'Open' || r.status === 'Rejected' || r.status === 'Claimed' ? 'disabled' : ''} data-action="approve" data-id="${r.report_id}">Approve</button>
                    <button class="action-btn deactivate" ${r.status === 'Missing' || r.status === 'Open' || r.status === 'Rejected' || r.status === 'Claimed' ? 'disabled' : ''} data-action="reject" data-id="${r.report_id}">Reject</button>
                    <button class="action-btn activate" ${r.status === 'Rejected' || r.status === 'Claimed' ? 'disabled' : ''} data-action="claimed" data-id="${r.report_id}">Mark Claimed</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }


    renderRows('lost-table', data.lost);
    renderRows('found-table', data.found);
}

// Handle actions
document.addEventListener('click', async (e) => {
    const btn = e.target;
    if (!btn.dataset.action) return;

    const action = btn.dataset.action;
    const id = btn.dataset.id;

    // Send action to backend
    const formData = new FormData();
    formData.append('action', action);
    formData.append('id', id);
    const res = await fetch('update_report.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (!data.success) {
        alert('Failed to update report');
        return;
    }

    // Update status in table
    const tr = btn.closest('tr');
    const statusTd = tr.querySelector('td:nth-child(7)'); // status column
    const newStatus = data.new_status; // use DB value
    statusTd.textContent = newStatus;
    statusTd.className = `status-${newStatus.toLowerCase()}`;

    // Buttons in this row
    const approveBtn = tr.querySelector('button[data-action="approve"]');
    const rejectBtn = tr.querySelector('button[data-action="reject"]');
    const claimedBtn = tr.querySelector('button[data-action="claimed"]');

    if (newStatus === 'Missing' || newStatus === 'Open') {
        approveBtn.disabled = true;
        rejectBtn.disabled = true;
        claimedBtn.disabled = false;
    } else if (newStatus === 'Rejected' || newStatus === 'Claimed') {
        approveBtn.disabled = true;
        rejectBtn.disabled = true;
        claimedBtn.disabled = true;
    } else {
        approveBtn.disabled = false;
        rejectBtn.disabled = false;
        claimedBtn.disabled = false;
    }
});


fetchReports(); // initial load

</script>
</body>
</html>
