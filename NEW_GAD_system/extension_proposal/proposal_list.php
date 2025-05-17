<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get all GAD proposals
try {
    $sql = "SELECT gp.*, COUNT(gpa.id) as activity_count 
            FROM gad_proposals gp
            LEFT JOIN gad_proposal_activities gpa ON gp.id = gpa.proposal_id
            GROUP BY gp.id
            ORDER BY gp.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAD Proposals | Batangas State University</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #6a1b9a;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #6a1b9a;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        .btn-action {
            margin-right: 5px;
        }
        .btn-primary {
            background-color: #6a1b9a;
            border-color: #6a1b9a;
        }
        .btn-primary:hover {
            background-color: #5c1786;
            border-color: #5c1786;
        }
        .footer {
            background-color: #f2f2f2;
            padding: 20px 0;
            text-align: center;
            margin-top: 30px;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .no-proposals {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex align-items-center">
                <img src="../images/Batangas_State_Logo.png" alt="BatState Logo" height="60">
                <div class="ms-3">
                    <h4 class="mb-0">GAD Proposals</h4>
                    <p class="mb-0">Batangas State University</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h3>Gender and Development Proposals</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="gad_proposal.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Create New Proposal
                </a>
                <a href="../dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Proposal List
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($proposals) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Year/Quarter</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Activities</th>
                                    <th scope="col">Created</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proposals as $index => $proposal): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($proposal['activity_title']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($proposal['year']); ?> / 
                                            Q<?php echo htmlspecialchars($proposal['quarter']); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $start_date = date('M d, Y', strtotime($proposal['start_date']));
                                                $end_date = date('M d, Y', strtotime($proposal['end_date']));
                                                echo $start_date;
                                                if ($start_date != $end_date) {
                                                    echo " - " . $end_date;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info rounded-pill">
                                                <?php echo $proposal['activity_count']; ?> Activities
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($proposal['created_at'])); ?></td>
                                        <td>
                                            <a href="gad_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn btn-sm btn-primary btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="print_proposal.php?id=<?php echo $proposal['id']; ?>" target="_blank" class="btn btn-sm btn-secondary btn-action" title="Print PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="print_html.php?id=<?php echo $proposal['id']; ?>" target="_blank" class="btn btn-sm btn-info btn-action" title="Print HTML">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-proposals">
                        <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
                        <h5>No Proposals Found</h5>
                        <p class="text-muted">You haven't created any GAD proposals yet.</p>
                        <a href="gad_proposal.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Your First Proposal
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Batangas State University. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 