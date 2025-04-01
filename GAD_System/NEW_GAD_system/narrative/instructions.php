<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Narrative Form Instructions - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #6a1b9a;
            margin-bottom: 30px;
            text-align: center;
        }
        h2 {
            color: #6a1b9a;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .step {
            margin-bottom: 25px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #6a1b9a;
            color: white;
            text-align: center;
            border-radius: 50%;
            margin-right: 10px;
            font-weight: bold;
            line-height: 30px;
        }
        .step-title {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #6a1b9a;
        }
        .troubleshooting {
            margin-top: 40px;
            padding: 20px;
            background-color: #ffe0e0;
            border-radius: 5px;
        }
        .troubleshooting h2 {
            color: #c62828;
        }
        .troubleshooting-item {
            margin-bottom: 15px;
        }
        .btn-back {
            margin-top: 20px;
        }
        .note {
            padding: 15px;
            background-color: #e0f7fa;
            border-left: 4px solid #00bcd4;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Narrative Form Instructions</h1>
        
        <div class="note">
            <p><strong>Note:</strong> Before using the narrative form, ensure that you have already created at least one PPAS form. Narrative forms are always linked to an existing PPAS form.</p>
        </div>
        
        <h2>Creating a Narrative Form</h2>
        
        <div class="step">
            <div class="step-title"><span class="step-number">1</span> Select a PPAS Form</div>
            <p>From the "Select PPAS Form" dropdown, choose the PPAS form for which you want to create a narrative. This will automatically populate some fields like Title, Location, and Duration.</p>
            <p>If no PPAS forms appear in the dropdown, you need to create a PPAS form first.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">2</span> Fill in Activity Details</div>
            <p>Enter the Implementing Office and Partner Agency (if applicable). These provide information about who conducted the activity.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">3</span> Select Extension Service Types</div>
            <p>Check all applicable types of extension service that your activity provided:</p>
            <ul>
                <li>Education and Training</li>
                <li>Technical Assistance</li>
                <li>Advisory Services</li>
                <li>Information Services</li>
                <li>Community Outreach</li>
            </ul>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">4</span> Select Sustainable Development Goals</div>
            <p>Choose all SDGs that your activity addresses. Select as many as applicable to show alignment with global development priorities.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">5</span> Enter Beneficiary Information</div>
            <p>Record the number of beneficiaries by:</p>
            <ul>
                <li>Type of participant (e.g., Student, Faculty, Community Member)</li>
                <li>Internal vs External participation</li>
                <li>Gender distribution (Male/Female)</li>
            </ul>
            <p>Use the "Add Row" button to add different categories of participants.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">6</span> Review Project Team</div>
            <p>The project team information is automatically pulled from the PPAS form. This shows who was responsible for the activity.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">7</span> Assign Tasks</div>
            <p>Specify the tasks assigned to each team member. Use the "Add Row" button to add more task assignments.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">8</span> Define Objectives</div>
            <p>Enter both the general objective (overall aim) and specific objectives (concrete goals) of the activity.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">9</span> Write Activity Narrative</div>
            <p>Provide a title for your narrative and write a detailed description of how the activity was conducted. Include key events, methodology, and timeline.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">10</span> Document Evaluation Results</div>
            <p>Record both the evaluation results and survey results to show the impact and feedback from the activity.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">11</span> Upload Photos</div>
            <p>Upload at least 6 photos documenting the activity. These provide visual evidence of the work conducted.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">12</span> Save the Form</div>
            <p>Click the <i class="fas fa-plus"></i> button to save your narrative form.</p>
        </div>
        
        <h2>Managing Narrative Forms</h2>
        
        <div class="step">
            <div class="step-title"><span class="step-number">1</span> View a Narrative</div>
            <p>After selecting a PPAS form with an existing narrative, you can click the <i class="fas fa-eye"></i> button to view a printable version of the narrative report.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">2</span> Edit a Narrative</div>
            <p>To modify an existing narrative, select its corresponding PPAS form, then click the <i class="fas fa-edit"></i> button to enable editing. Make your changes and save.</p>
        </div>
        
        <div class="step">
            <div class="step-title"><span class="step-number">3</span> Delete a Narrative</div>
            <p>If you need to remove a narrative, select its PPAS form and click the <i class="fas fa-trash-alt"></i> button. Confirm the deletion when prompted.</p>
        </div>
        
        <div class="troubleshooting">
            <h2>Troubleshooting</h2>
            
            <div class="troubleshooting-item">
                <h4>No PPAS Forms in Dropdown</h4>
                <p>If no PPAS forms appear in the dropdown:</p>
                <ul>
                    <li>Create a PPAS form first through the PPAs Form menu</li>
                    <li>Check that the database is properly set up (contact your administrator)</li>
                    <li>Verify you have the correct permissions to view PPAS forms</li>
                </ul>
            </div>
            
            <div class="troubleshooting-item">
                <h4>Error Saving Narrative</h4>
                <p>If you encounter errors when saving:</p>
                <ul>
                    <li>Ensure all required fields are filled in</li>
                    <li>Check your internet connection</li>
                    <li>Try refreshing the page and filling the form again</li>
                    <li>Contact your administrator if the issue persists</li>
                </ul>
            </div>
            
            <div class="troubleshooting-item">
                <h4>Database Connection Issues</h4>
                <p>If you see database connection errors:</p>
                <ul>
                    <li>Contact your system administrator</li>
                    <li>Try again later as the database might be temporarily down</li>
                </ul>
            </div>
        </div>
        
        <a href="narrative.php" class="btn btn-primary btn-back">
            <i class="fas fa-arrow-left"></i> Back to Narrative Form
        </a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 