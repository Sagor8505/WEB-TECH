<?php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('location: ../view/login.php?error=not_logged_in');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs</title>
    <link rel="stylesheet" type="text/css" href="../asset/activitylog.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f7f7f7; }
        .log-container { max-width: 1000px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; }
        .filters { margin-bottom: 20px; }
        .filters input, .filters select { margin-right: 10px; padding: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #f2f2f2; }
        table tr:nth-child(even){ background: #fafafa; }
        input[type="submit"], input[type="button"] { padding: 8px 12px; cursor: pointer; }
    </style>
</head>
<body>

<div class="log-container">
    <h2>Activity Logs</h2>

    <div class="filters">
        <form onsubmit="return fetchLogs()">
            <label for="dateFrom">Date From:</label>
            <input type="date" id="dateFrom" value="2025-01-01">

            <label for="dateTo">Date To:</label>
            <input type="date" id="dateTo" value="<?php echo date('Y-m-d'); ?>">

            <label for="user">User:</label>
            <input type="text" id="user" placeholder="Enter username">

            <input type="submit" value="Filter">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'" />
        </form>
    </div>

    <table id="logTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4">No data loaded</td></tr>
        </tbody>
    </table>
</div>

<script>
function fetchLogs() {
    let fromDate = document.getElementById('dateFrom').value;
    let toDate = document.getElementById('dateTo').value;
    let user = document.getElementById('user').value;

    if (new Date(fromDate) > new Date(toDate)) {
        alert("The 'From Date' cannot be later than the 'To Date'.");
        return false;
    }

    let filter = {
        'fromDate': fromDate,
        'toDate': toDate,
        'user': user
    };

    let data = JSON.stringify(filter);

    let xhttp = new XMLHttpRequest();
    xhttp.open('POST', '../controller/fetch_logs.php', true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send('filter=' + encodeURIComponent(data));

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let tbody = document.querySelector("#logTable tbody");
            tbody.innerHTML = "";

            try {
                let res = JSON.parse(this.responseText);

                if (res.error) {
                    tbody.innerHTML = `<tr><td colspan="4">${res.error}</td></tr>`;
                    return;
                }

                if (res.length === 0) {
                    tbody.innerHTML = "<tr><td colspan='4'>No logs found</td></tr>";
                    return;
                }

                res.forEach(log => {
                    let row = `<tr>
                        <td>${new Date(log.date).toLocaleString()}</td>
                        <td>${log.user}</td>
                        <td>${log.action}</td>
                        <td>${log.details}</td>
                    </tr>`;
                    tbody.innerHTML += row;
                });
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4">Error loading logs</td></tr>`;
            }
        }
    };

    return false;
}

window.onload = fetchLogs;
</script>

</body>
</html>
