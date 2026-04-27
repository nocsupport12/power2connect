<?php
session_start();
include("../components/db.php");
require '../vendor/autoload.php';
use Dompdf\Dompdf;

// Protect admin page
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get admin name
$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->query("SELECT fname, lname FROM usr_tbl WHERE id = $admin_id");
$adminData = $adminQuery->fetch_assoc();
$adminName = $adminData['fname'] . ' ' . $adminData['lname'];

// ===== DATE RANGE FILTER =====
$start_date = $_GET['from'] ?? '';
$end_date   = $_GET['to'] ?? '';

// Build WHERE clause for completed queries
$date_query = "WHERE cr.status = 'completed'";
if (!empty($start_date) && !empty($end_date)) {
    $date_query .= " AND cr.requested_at >= '$start_date 00:00:00' AND cr.requested_at <= '$end_date 23:59:59'";
} elseif (!empty($start_date)) {
    $date_query .= " AND cr.requested_at >= '$start_date 00:00:00'";
} elseif (!empty($end_date)) {
    $date_query .= " AND cr.requested_at <= '$end_date 23:59:59'";
}

// ===== EXPORT HANDLERS (must be before ANY output) =====
if (isset($_GET['export'])) {
    $export_type = $_GET['export']; // 'csv' or 'pdf'
    $export_query = $conn->query("
        SELECT cr.*, u.name as user_name 
        FROM call_requests cr
        LEFT JOIN users u ON cr.user_id = u.id
        $date_query
        ORDER BY cr.requested_at DESC
    ");
    
    if ($export_type === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=completed_queries_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Customer', 'Mobile', 'Email', 'Reason', 'Type', 'Date', 'Time']);
        if ($export_query && $export_query->num_rows > 0) {
            while ($row = $export_query->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'] ?: $row['user_name'] ?: 'Anonymous',
                    $row['mobile_number'],
                    $row['email'] ?: 'N/A',
                    $row['reason'] ?: 'No reason',
                    $row['contact_type'],
                    date('Y-m-d', strtotime($row['requested_at'])),
                    date('H:i:s', strtotime($row['requested_at']))
                ]);
            }
        } else {
            fputcsv($output, ['No completed queries found for the selected period']);
        }
        fclose($output);
        exit;
    }
    
    if ($export_type === 'pdf') {
        $html = '<html><head><meta charset="UTF-8"><style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th { background: #f0f0f0; }
        </style></head><body>';
        $html .= '<h2>Completed Queries Report</h2>';
        $html .= '<p>Period: ' . ($start_date ?: 'All') . ' to ' . ($end_date ?: 'All') . '</p>';
        $html .= '<table><thead><tr><th>ID</th><th>Customer</th><th>Mobile</th><th>Reason</th><th>Type</th><th>Date</th></tr></thead><tbody>';
        if ($export_query && $export_query->num_rows > 0) {
            while ($row = $export_query->fetch_assoc()) {
                $html .= '<tr>
                    <td>' . $row['id'] . '</td>
                    <td>' . htmlspecialchars($row['name'] ?: $row['user_name'] ?: 'Anonymous') . '</td>
                    <td>' . htmlspecialchars($row['mobile_number']) . '</td>
                    <td>' . htmlspecialchars(substr($row['reason'] ?: '', 0, 60)) . '</td>
                    <td>' . ucfirst($row['contact_type']) . '</td>
                    <td>' . date('Y-m-d H:i', strtotime($row['requested_at'])) . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="6">No data found</td></tr>';
        }
        $html .= '</tbody></table></body></html>';
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('completed_queries_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
        exit;
    }
}

// ===== PAGINATION =====
$limit = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch completed queries
$completed_queries = $conn->query("
    SELECT cr.*, u.name as user_name 
    FROM call_requests cr
    LEFT JOIN users u ON cr.user_id = u.id
    $date_query
    ORDER BY cr.requested_at DESC
    LIMIT $limit OFFSET $offset
");

// Total rows
$total_result = $conn->query("SELECT COUNT(*) as total FROM call_requests cr $date_query");
$total_rows = $total_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_rows / $limit);

// Statistics
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total_completed,
        SUM(CASE WHEN contact_type = 'call' THEN 1 ELSE 0 END) as call_requests
    FROM call_requests cr
    $date_query
");
$stats = $stats_query->fetch_assoc();
$email_requests = ($stats['total_completed'] ?? 0) - ($stats['call_requests'] ?? 0);

// Delete handler
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("DELETE FROM call_requests WHERE id = $id");
    header('Content-Type: application/json');
    echo json_encode(['success' => $result ? true : false]);
    exit;
}

include("../components/admin_nav.php");
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Completed Queries - Power2Connect</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Light color palette - matching original UI */
    :root {
        --bg-light: #f8fafc;
        --card-white: #ffffff;
        --border-light: #e2e8f0;
        --text-dark: #334155;
        --text-light: #64748b;
        --primary-light: #e0f2fe;
        --secondary-light: #ede9fe;
        --hover-light: #f1f5f9;
        --danger-light: #fee2e2;
        --danger-dark: #b91c1c;
        --success-light: #dcfce7;
        --success-dark: #166534;
    }
    
    body {
        margin: 0;
        height: 100vh;
        background: linear-gradient(to right, #b3cde0, #e0f7fa);
        background-attachment: fixed;
    }
    
    .main-content {
        margin-left: 134px;
        padding: 30px;
        transition: margin-left 0.3s;
        display: flex;
        justify-content: center;
        min-height: 100vh;
        width: calc(100% - 250px);
        box-sizing: border-box;
    }
    
    .container {
        max-width: 1400px;
        width: 100%;
        margin: 0 auto;
    }
    
    .page-header {
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0 0 5px 0;
    }
    
    .page-subtitle {
        color: var(--text-light);
        margin: 0;
        font-size: 15px;
    }
    
    .print-btn {
        padding: 8px 16px;
        border: 1px solid var(--border-light);
        border-radius: 10px;
        background: white;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
        transition: all 0.2s;
    }
    
    .print-btn:hover {
        background: var(--hover-light);
        border-color: #cbd5e1;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
        width: 100%;
    }
    
    .stat-card {
        background: var(--card-white);
        border: 1px solid var(--border-light);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #cbd5e1;
    }
    
    .stat-info h3 {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-light);
        margin: 0 0 8px 0;
        letter-spacing: 0.3px;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.2;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .stat-icon.total { background: var(--primary-light); color: #0284c7; }
    .stat-icon.calls { background: var(--secondary-light); color: #7c3aed; }
    .stat-icon.emails { background: #fef9c3; color: #ca8a04; }
    
    .filter-section {
        background: var(--card-white);
        border: 1px solid var(--border-light);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .filter-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: flex-start;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .filter-group label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .filter-group input {
        padding: 10px 14px;
        border: 1px solid var(--border-light);
        border-radius: 10px;
        min-width: 200px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .filter-group input:focus {
        outline: none;
        border-color: #94a3b8;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.1);
    }
    
    .btn {
        padding: 10px 20px;
        border: 1px solid var(--border-light);
        border-radius: 10px;
        background: white;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .btn:hover {
        background: var(--hover-light);
        border-color: #cbd5e1;
    }
    
    .btn-pdf {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }
    
    .btn-pdf:hover {
        background: #fecaca;
    }
    
    .btn-csv {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }
    
    .btn-csv:hover {
        background: #bbf7d0;
    }
    
    .btn-danger {
        color: var(--danger-dark);
    }
    
    .btn-danger:hover {
        background: var(--danger-light);
    }
    
    .table-container {
        background: var(--card-white);
        border: 1px solid var(--border-light);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        width: 100%;
        box-sizing: border-box;
    }
    
    .table-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-light);
        width: 100%;
        box-sizing: border-box;
    }
    
    .table-header h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-container th {
        background: var(--bg-light);
        text-align: left;
        padding: 16px 20px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid var(--border-light);
    }
    
    .table-container td {
        padding: 16px 20px;
        font-size: 14px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-dark);
    }
    
    .table-container tr:last-child td {
        border-bottom: none;
    }
    
    .table-container tr:hover td {
        background: var(--hover-light);
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .badge-call { 
        background: var(--primary-light); 
        color: #0369a1; 
    }
    
    .badge-email { 
        background: var(--secondary-light); 
        color: #6d28d9; 
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        padding: 20px;
        border-top: 1px solid var(--border-light);
    }
    
    .pagination a, .pagination span {
        min-width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-dark);
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .pagination a:hover {
        background: var(--hover-light);
        border-color: #cbd5e1;
    }
    
    .pagination .active {
        background: var(--text-dark);
        color: white;
        border-color: var(--text-dark);
    }
    
    .table-footer {
        padding: 16px 20px;
        background: var(--bg-light);
        border-top: 1px solid var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: var(--text-light);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px;
        color: var(--text-light);
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.3;
    }
    
    .empty-state .main-message {
        font-size: 16px;
    }
    
    .empty-state .sub-message {
        font-size: 13px;
    }
    
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
            width: 100%;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-group input {
            width: 100%;
        }
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0;
            width: 100%;
        }
        body {
            background: white;
        }
    }
</style>
</head>
<body>
    <!-- admin_nav.php is included above -->
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Completed Queries</h1>
                    <p class="page-subtitle">Welcome back, <?= htmlspecialchars(explode(' ', $adminName)[0]) ?>! View all completed inquiries</p>
                </div>
                <button onclick="window.print()" class="print-btn no-print">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid no-print">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Completed</h3>
                        <div class="stat-number"><?= number_format($stats['total_completed'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon total">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Call Requests</h3>
                        <div class="stat-number"><?= number_format($stats['call_requests'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon calls">
                        <i class="fas fa-phone"></i>
                    </div>
                </div>  
            </div>

            <!-- Filter Section -->
            <div class="filter-section no-print">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label>From Date</label>
                        <input type="date" name="from" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="filter-group">
                        <label>To Date</label>
                        <input type="date" name="to" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <a href="?" class="btn">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <!-- Export links (preserve filters) -->
                    <a href="?export=csv&from=<?= urlencode($start_date) ?>&to=<?= urlencode($end_date) ?>" class="btn btn-csv">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
                    <a href="?export=pdf&from=<?= urlencode($start_date) ?>&to=<?= urlencode($end_date) ?>" class="btn btn-pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </form>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="table-header no-print">
                    <h3>
                        <i class="fas fa-list"></i>
                        Completed Queries List
                    </h3>
                    <span>Total: <?= $total_rows ?> entries</span>
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Reason</th>
                                <th>Type</th>
                                <th>Date & Time</th>
                                <th class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($completed_queries && $completed_queries->num_rows > 0): ?>
                                <?php while($q = $completed_queries->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 500;">#<?= str_pad($q['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($q['name'] ?: $q['user_name'] ?: 'Anonymous') ?></td>
                                    <td style="font-family: monospace;"><?= htmlspecialchars($q['mobile_number']) ?></td>
                                    <td><?= htmlspecialchars($q['email'] ?: 'N/A') ?></td>
                                    <td style="max-width: 250px;">
                                        <?= htmlspecialchars(substr($q['reason'] ?: 'No reason provided', 0, 50)) ?>
                                        <?= strlen($q['reason'] ?? '') > 50 ? '...' : '' ?>
                                    </td>
                                    <td>
                                        <?php if ($q['contact_type'] == 'call'): ?>
                                            <span class="badge badge-call">
                                                <i class="fas fa-phone"></i> Call
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-email">
                                                <i class="fas fa-envelope"></i> Email
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($q['requested_at'])) ?><br>
                                        <span style="font-size: 11px; color: var(--text-light);"><?= date('h:i A', strtotime($q['requested_at'])) ?></span>
                                    </td>
                                    <td class="no-print">
                                        <button onclick="confirmDelete(<?= $q['id'] ?>)" class="btn btn-danger" style="padding: 6px 12px;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p class="main-message">No completed queries found</p>
                                        <p class="sub-message">Try changing the date range or reset the filter</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="pagination no-print">
                    <?php 
                    $query_params = http_build_query(array_filter(['from' => $start_date, 'to' => $end_date]));
                    $base_url = '?' . ($query_params ? $query_params . '&' : '');
                    
                    $adjacents = 2;
                    $start = max(1, $page - $adjacents);
                    $end = min($total_pages, $page + $adjacents);
                    
                    if($page > 1) echo '<a href="'.$base_url.'page='.($page-1).'"><i class="fas fa-chevron-left"></i></a>';
                    if($start > 1) { 
                        echo '<a href="'.$base_url.'page=1">1</a>'; 
                        if($start > 2) echo '<span>...</span>'; 
                    }
                    for($i=$start; $i<=$end; $i++) {
                        $activeClass = ($i == $page) ? 'active' : '';
                        echo '<a href="'.$base_url.'page='.$i.'" class="'.$activeClass.'">'.$i.'</a>';
                    }
                    if($end < $total_pages) { 
                        if($end < $total_pages - 1) echo '<span>...</span>'; 
                        echo '<a href="'.$base_url.'page='.$total_pages.'">'.$total_pages.'</a>'; 
                    }
                    if($page < $total_pages) echo '<a href="'.$base_url.'page='.($page+1).'"><i class="fas fa-chevron-right"></i></a>';
                    ?>
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="table-footer no-print">
                    <span>Showing <?= $completed_queries ? $completed_queries->num_rows : 0 ?> of <?= $total_rows ?></span>
                    <span>Page <?= $page ?> of <?= $total_pages ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id) {
        const urlParams = new URLSearchParams(window.location.search);
        const fromDate = urlParams.get('from') || '';
        const toDate = urlParams.get('to') || '';
        
        Swal.fire({
            title: 'Delete Query?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                let deleteUrl = window.location.pathname + '?delete=' + id;
                if (fromDate) deleteUrl += '&from=' + encodeURIComponent(fromDate);
                if (toDate) deleteUrl += '&to=' + encodeURIComponent(toDate);
                
                fetch(deleteUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The query has been deleted.',
                                confirmButtonColor: '#334155'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to delete the query.',
                                confirmButtonColor: '#334155'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting.',
                            confirmButtonColor: '#334155'
                        });
                    });
            }
        });
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>