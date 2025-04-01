<?php
session_start();
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure user is "logged in" for testing
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'test_user';
    $_SESSION['user_id'] = 1;
    // We won't set campus_id to test that our fixes work without it
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAD Proposals Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .container { margin-bottom: 30px; }
        .btn { padding: 8px 16px; background: #4CAF50; color: white; border: none; cursor: pointer; margin-right: 10px; }
        .btn:hover { background: #45a049; }
        .result { border: 1px solid #ddd; padding: 15px; margin-top: 15px; min-height: 200px; background: #f9f9f9; }
        pre { white-space: pre-wrap; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>GAD Proposals Testing</h1>
    
    <div class="container">
        <h2>Session Information</h2>
        <pre id="session-info">
<?php
    echo "Session Variables:\n";
    foreach ($_SESSION as $key => $value) {
        echo "$key: " . (is_array($value) ? 'Array' : $value) . "\n";
    }
?>
        </pre>
        <button class="btn" onclick="clearSession()">Clear Session</button>
    </div>
    
    <div class="container">
        <h2>Fetch All Proposals</h2>
        <button class="btn" onclick="fetchProposals()">Fetch All Proposals</button>
        <div class="result" id="proposals-result"></div>
    </div>
    
    <div class="container">
        <h2>Fetch Single Proposal</h2>
        <input type="number" id="proposal-id" placeholder="Enter Proposal ID" min="1" value="1">
        <button class="btn" onclick="fetchSingleProposal()">Fetch Proposal</button>
        <div class="result" id="single-proposal-result"></div>
    </div>
    
    <div class="container">
        <h2>Create Test Proposal</h2>
        <button class="btn" onclick="createTestProposal()">Create Test Proposal</button>
        <div class="result" id="create-result"></div>
    </div>
    
    <script>
        // Fetch all proposals
        function fetchProposals() {
            const resultDiv = document.getElementById('proposals-result');
            resultDiv.innerHTML = '<p>Loading...</p>';
            
            fetch('get_gad_proposals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resultDiv.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                        if (data.file && data.line) {
                            resultDiv.innerHTML += `<p>File: ${data.file}, Line: ${data.line}</p>`;
                        }
                    } else {
                        const count = data.length || 0;
                        resultDiv.innerHTML = `<p class="success">Successfully fetched ${count} proposals</p>`;
                        resultDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<p class="error">Fetch error: ${error.message}</p>`;
                });
        }
        
        // Fetch single proposal
        function fetchSingleProposal() {
            const id = document.getElementById('proposal-id').value;
            const resultDiv = document.getElementById('single-proposal-result');
            
            if (!id) {
                resultDiv.innerHTML = '<p class="error">Please enter a proposal ID</p>';
                return;
            }
            
            resultDiv.innerHTML = '<p>Loading...</p>';
            
            fetch(`get_gad_proposal.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resultDiv.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                        if (data.file && data.line) {
                            resultDiv.innerHTML += `<p>File: ${data.file}, Line: ${data.line}</p>`;
                        }
                    } else {
                        resultDiv.innerHTML = `<p class="success">Successfully fetched proposal</p>`;
                        resultDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<p class="error">Fetch error: ${error.message}</p>`;
                });
        }
        
        // Create test proposal
        function createTestProposal() {
            const resultDiv = document.getElementById('create-result');
            resultDiv.innerHTML = '<p>Creating test proposal...</p>';
            
            const formData = new FormData();
            formData.append('year', new Date().getFullYear());
            formData.append('quarter', '1');
            formData.append('activityTitle', 'Test Proposal ' + new Date().toISOString());
            formData.append('startDate', '2023-01-01');
            formData.append('endDate', '2023-12-31');
            formData.append('fundSource', 'Test Fund');
            formData.append('gad_budget', '10000');
            formData.append('total_budget', '20000');
            
            fetch('save_gad_proposal.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    resultDiv.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                    if (data.file && data.line) {
                        resultDiv.innerHTML += `<p>File: ${data.file}, Line: ${data.line}</p>`;
                    }
                } else {
                    resultDiv.innerHTML = `<p class="success">Successfully created proposal</p>`;
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    // Refresh the proposals list
                    fetchProposals();
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">Fetch error: ${error.message}</p>`;
            });
        }
        
        // Clear session
        function clearSession() {
            fetch('clear_session.php')
                .then(response => response.text())
                .then(() => {
                    location.reload();
                });
        }
    </script>
</body>
</html> 