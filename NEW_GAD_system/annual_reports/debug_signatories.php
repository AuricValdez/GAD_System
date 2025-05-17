<?php
session_start();

// Check if user is logged in and is Central
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'Central') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatories Debugging</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .campus-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Campus Signatories Debugging</h1>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Test Specific Campus</h5>
                    </div>
                    <div class="card-body">
                        <form id="testForm">
                            <div class="mb-3">
                                <label for="campusSelect" class="form-label">Select Campus</label>
                                <select class="form-control" id="campusSelect">
                                    <option value="">Loading campuses...</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Signatories</button>
                        </form>
                        
                        <div class="mt-4">
                            <h6>API Response:</h6>
                            <pre id="apiResponse">Test a campus to see the API response</pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Database Structure</h5>
                    </div>
                    <div class="card-body">
                        <button id="checkStructureBtn" class="btn btn-secondary mb-3">Check DB Structure</button>
                        <pre id="dbStructure">Click button to check database structure</pre>
                    </div>
                </div>
            </div>
        </div>
        
        <h2 class="mb-3">All Campus Signatories</h2>
        <button id="loadAllBtn" class="btn btn-info mb-4">Load All Signatories</button>
        
        <div id="allSignatories" class="row">
            <div class="col-12">
                <p class="text-muted">Click the button above to load all signatories</p>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load campuses for select dropdown
            loadCampuses();
            
            // Test form submission
            $('#testForm').on('submit', function(e) {
                e.preventDefault();
                const campus = $('#campusSelect').val();
                if (!campus) {
                    alert('Please select a campus');
                    return;
                }
                
                testSignatories(campus);
            });
            
            // Check DB structure button
            $('#checkStructureBtn').on('click', function() {
                checkDbStructure();
            });
            
            // Load all signatories button
            $('#loadAllBtn').on('click', function() {
                loadAllSignatories();
            });
        });
        
        function loadCampuses() {
            $.ajax({
                url: '../dashboard/api/get_campuses.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const select = $('#campusSelect');
                    select.empty();
                    
                    if (response.status === 'success' && response.data) {
                        select.append('<option value="">Select Campus</option>');
                        
                        response.data.forEach(function(campus) {
                            select.append(`<option value="${campus.name}">${campus.name}</option>`);
                        });
                    } else {
                        select.append('<option value="">Error loading campuses</option>');
                    }
                },
                error: function() {
                    $('#campusSelect').html('<option value="">Error loading campuses</option>');
                }
            });
        }
        
        function testSignatories(campus) {
            $('#apiResponse').text('Loading...');
            
            $.ajax({
                url: 'api/get_signatories.php',
                method: 'GET',
                data: { campus: campus },
                dataType: 'json',
                success: function(response) {
                    $('#apiResponse').text(JSON.stringify(response, null, 2));
                },
                error: function(xhr) {
                    $('#apiResponse').text('Error: ' + xhr.responseText);
                }
            });
        }
        
        function checkDbStructure() {
            $('#dbStructure').text('Loading...');
            
            $.ajax({
                url: 'api/check_signatories_table.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#dbStructure').text(JSON.stringify(response, null, 2));
                },
                error: function(xhr) {
                    $('#dbStructure').text('Error: ' + xhr.responseText);
                }
            });
        }
        
        function loadAllSignatories() {
            $('#allSignatories').html('<div class="col-12"><p>Loading signatories...</p></div>');
            
            $.ajax({
                url: 'api/list_all_signatories.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        response.data.forEach(function(signatory) {
                            html += `
                                <div class="col-md-6">
                                    <div class="card campus-card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">${signatory.campus || 'Unknown Campus'}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>ID:</strong> ${signatory.id || 'N/A'}</p>
                                            <h6>Signatory Names:</h6>
                                            <ul>
                                                <li><strong>Name 1 (Prepared By):</strong> ${signatory.name1 || signatory.prepared_by_name || 'N/A'}</li>
                                                <li><strong>Name 2:</strong> ${signatory.name2 || 'N/A'}</li>
                                                <li><strong>Name 3 (Approved By):</strong> ${signatory.name3 || signatory.approved_by_name || 'N/A'}</li>
                                                <li><strong>Name 4 (Asst Director):</strong> ${signatory.name4 || signatory.asst_director_name || 'N/A'}</li>
                                                <li><strong>Name 5:</strong> ${signatory.name5 || 'N/A'}</li>
                                            </ul>
                                            <h6>Positions:</h6>
                                            <ul>
                                                <li><strong>GAD Head Secretariat:</strong> ${signatory.gad_head_secretariat || signatory.prepared_by_position || 'N/A'}</li>
                                                <li><strong>Vice Chancellor RDE:</strong> ${signatory.vice_chancellor_rde || 'N/A'}</li>
                                                <li><strong>Chancellor:</strong> ${signatory.chancellor || signatory.approved_by_position || 'N/A'}</li>
                                                <li><strong>Asst Director GAD:</strong> ${signatory.asst_director_gad || signatory.asst_director_position || 'N/A'}</li>
                                                <li><strong>Head Extension Services:</strong> ${signatory.head_extension_services || 'N/A'}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = `<div class="col-12"><div class="alert alert-warning">No signatories found in the database.</div></div>`;
                    }
                    
                    $('#allSignatories').html(html);
                },
                error: function(xhr) {
                    $('#allSignatories').html(`<div class="col-12"><div class="alert alert-danger">Error: ${xhr.responseText}</div></div>`);
                }
            });
        }
    </script>
</body>
</html> 