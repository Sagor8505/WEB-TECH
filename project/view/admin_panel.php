<?php
session_start();
require_once('../model/userModel.php');

// Ensure admin login
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$roleFilter = $_POST['roleFilter'] ?? 'All';
$validationMessage = '';
$allUsers = getAlluser();
$roleList = ['All'];
foreach ($allUsers as $u) {
    $roleVal = $u['role'] ?? '';
    if ($roleVal && !in_array($roleVal, $roleList, true)) $roleList[] = $roleVal;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - User Management</title>
<link rel="stylesheet" href="../asset/ad.css">
<style>
.admin-card{max-width:1100px;margin:18px auto;padding:18px;background:#fff;border-radius:8px;box-shadow:0 0 8px #ddd;}
table.users{width:100%;border-collapse:collapse;margin-top:12px;}
table.users th, table.users td{border:1px solid #e1e1e1;padding:8px;text-align:left;}
table.users th{background:#f7f7f7;}
table.users tr:nth-child(even){background:#fafafa;}
.actions button{margin-right:6px;}
fieldset.controls{border:none;padding:0;margin:0;}
.validation{font-weight:700;margin:8px 0;color:green;}
.warn{color:red;font-weight:700;}
</style>
<script>
// Fetch users via AJAX
function fetchUsers(roleFilter='All') {
    let xhr = new XMLHttpRequest();
    xhr.open('POST', '../controller/loadUsers.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        let userList = document.getElementById('userList');
        userList.innerHTML = '';
        if(xhr.status === 200) {
            let res = JSON.parse(xhr.responseText);
            if(res.status==='success') {
                res.users.forEach(u=>{
                    userList.innerHTML += `<tr>
                        <td><input type='checkbox' name='user_ids[]' value='${u.id}'></td>
                        <td>${u.id}</td>
                        <td>${u.username}</td>
                        <td>${u.email}</td>
                        <td>${u.role}</td>
                        <td class='actions'>
                            <button type='button' onclick='editUser(${u.id})'>Edit</button>
                            <button type='button' onclick='deleteUser(${u.id})'>Delete</button>
                        </td>
                    </tr>`;
                });
            } else {
                userList.innerHTML = `<tr><td colspan="6">No users found.</td></tr>`;
            }
        }
    };
    xhr.send('roleFilter='+encodeURIComponent(roleFilter));
}

// Edit user
function editUser(userId){ window.location.href='editUser.php?id='+userId; }

// Delete single user
function deleteUser(userId){
    if(confirm('Are you sure you want to delete this user?')){
        let xhr = new XMLHttpRequest();
        xhr.open('GET','../controller/deleteUser.php?id='+userId,true);
        xhr.onload=function(){ fetchUsers(); };
        xhr.send();
    }
}

// Bulk delete
function bulkDelete(){
    let selected = [];
    document.querySelectorAll("input[name='user_ids[]']:checked").forEach(cb=>selected.push(cb.value));
    if(selected.length===0){ alert("Select at least one user."); return; }
    if(confirm("Delete selected users?")){
        let xhr = new XMLHttpRequest();
        xhr.open('POST','../controller/bulkDelete.php',true);
        xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xhr.onload=function(){ fetchUsers(); };
        xhr.send('user_ids='+JSON.stringify(selected));
    }
}

// Filter by role
function applyFilter(){
    let roleFilter = document.getElementById('roleFilter').value;
    fetchUsers(roleFilter);
}

// Select/Deselect all checkboxes
function toggleSelectAll(cb){
    document.querySelectorAll("input[name='user_ids[]']").forEach(c=>c.checked=cb.checked);
}

// Export CSV
function exportCSV(){
    document.getElementById('exportCSVForm').submit();
}

window.onload = function(){ fetchUsers(); };
</script>
</head>
<body>
<div class="admin-card">
<h1>Admin Panel - User Management</h1>

<form id="filterForm" onsubmit="event.preventDefault(); applyFilter();">
<fieldset class="controls">
<label for="roleFilter">Filter by role:</label>
<select id="roleFilter" name="roleFilter" onchange="applyFilter()">
<?php foreach($roleList as $r): ?>
<option value="<?= h($r) ?>" <?= ($r===$roleFilter)?'selected':'' ?>><?= h($r) ?></option>
<?php endforeach; ?>
</select>
</fieldset>
</form>

<?php if($validationMessage): ?>
<div class="validation"><?= h($validationMessage) ?></div>
<?php endif; ?>

<form id="bulkDeleteForm">
<table class="users">
<thead>
<tr>
<th><input type="checkbox" onclick="toggleSelectAll(this)"></th>
<th>User ID</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Actions</th>
</tr>
</thead>
<tbody id="userList"></tbody>
</table>
<br>
<input type="button" value="Bulk Delete" onclick="bulkDelete()">
<input type="button" value="Export CSV" onclick="exportCSV()">
<input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.php'">
</form>

<form id="exportCSVForm" method="POST" action="" style="display:none;">
<input type="hidden" name="exportCSV" value="true">
<input type="hidden" name="roleFilter" value="<?= h($roleFilter) ?>">
</form>
</div>
</body>
</html>
