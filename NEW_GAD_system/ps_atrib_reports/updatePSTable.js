/**
 * PS Attribution Table Update Script
 * This file contains functions to handle updating the PS attribution tables
 * with data from the backend.
 */

// Function to update the PS attribution table for a specific quarter
function updatePSTable(quarter, academicRanks) {
    console.log(`Updating PS table for quarter ${quarter}`);
    
    const table = document.querySelector(`#psTable${quarter}`);
    if (!table) {
        console.error(`Table element for quarter ${quarter} not found`);
        return;
    }
    
    table.innerHTML = '';
    let totalPS = 0;
    let totalParticipants = 0;

    // Check if there are any academic ranks to display
    if (!Array.isArray(academicRanks) || academicRanks.length === 0) {
        console.log('No academic ranks to display');
        table.innerHTML = '<tr><td colspan="6" class="text-center">No PS attribution found for this quarter</td></tr>';
        
        // Disable buttons
        const printBtn = document.getElementById(`printBtn${quarter}`);
        const exportBtn = document.getElementById(`exportBtn${quarter}`);
        if (printBtn) {
            printBtn.disabled = true;
            printBtn.style.pointerEvents = 'none';
            printBtn.style.opacity = '0.65';
        }
        if (exportBtn) {
            exportBtn.disabled = true;
            exportBtn.style.pointerEvents = 'none';
            exportBtn.style.opacity = '0.65';
        }
        return;
    }

    // Get PPA details from the currently selected PPA
    const ppaDetails = selectedPPAs[quarter];
    
    // Get total duration from PPA details, default to 8 hours if not available
    const totalDuration = ppaDetails && ppaDetails.total_duration ? 
        parseFloat(ppaDetails.total_duration) : 8;
    
    // Define the custom order for academic ranks
    const rankOrder = {
        'Instructor I': 1,
        'Instructor II': 2,
        'Instructor III': 3,
        'College Lecturer': 4,
        'Senior Lecturer': 5,
        'Master Lecturer': 6,
        'Assistant Professor I': 7,
        'Assistant Professor II': 8,
        'Assistant Professor III': 9,
        'Assistant Professor IV': 10,
        'Associate Professor I': 11,
        'Associate Professor II': 12,
        'Associate Professor III': 13,
        'Associate Professor IV': 14,
        'Associate Professor V': 15,
        'Professor I': 16,
        'Professor II': 17,
        'Professor III': 18,
        'Professor IV': 19,
        'Professor V': 20,
        'Professor VI': 21,
        'Admin Aide 1': 22,
        'Admin Aide 2': 23,
        'Admin Aide 3': 24,
        'Admin Aide 4': 25,
        'Admin Aide 5': 26,
        'Admin Aide 6': 27,
        'Admin Asst 1': 28,
        'Admin Asst 2': 29,
        'Admin Asst 3': 30
    };

    // Sort academic ranks according to the predefined order
    academicRanks.sort((a, b) => {
        // Get the order values for the ranks
        const orderA = rankOrder[a.rank_name] || 999; // Default high value for unknown ranks
        const orderB = rankOrder[b.rank_name] || 999;

        // Sort by the predefined order
        return orderA - orderB;
    });

    // Process each academic rank
    academicRanks.forEach(rank => {
        // Skip ranks without monthly salary
        if (!rank.monthly_salary) {
            console.warn(`Missing monthly salary for rank: ${rank.rank_name}`);
            return;
        }

        // Calculate rate per hour and PS attribution
        const ratePerHour = rank.monthly_salary / 176; // Standard divisor
        const ps = ratePerHour * totalDuration * rank.personnel_count;
        
        // Add to totals
        totalPS += ps;
        totalParticipants += rank.personnel_count;

        // Generate row HTML
        const row = `
            <tr>
                <td>${rank.rank_name}</td>
                <td class="text-center">${rank.personnel_count === 0 ? '-' : rank.personnel_count}</td>
                <td class="text-end">${rank.monthly_salary === 0 ? '-' : '₱' + rank.monthly_salary.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end">${ratePerHour === 0 ? '-' : '₱' + ratePerHour.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-center">${rank.personnel_count === 0 ? '-' : totalDuration}</td>
                <td class="text-end">${ps === 0 ? '-' : '₱' + ps.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `;
        table.innerHTML += row;
    });

    // Add total row
    const totalRow = `
        <tr class="table-active">
            <td colspan="2" class="text-end"><strong>Total Number of Participants:</strong></td>
            <td class="text-end"><strong>${totalParticipants === 0 ? '-' : totalParticipants}</strong></td>
            <td colspan="2" class="text-end"><strong>Total PS:</strong></td>
            <td class="text-end"><strong>${totalPS === 0 ? '-' : '₱' + totalPS.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
        </tr>
    `;
    table.innerHTML += totalRow;

    // Update total PS field
    document.getElementById(`totalPS${quarter}`).value = totalPS === 0 ? '-' : `₱${totalPS.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

    // Enable buttons
    const printBtn = document.getElementById(`printBtn${quarter}`);
    const exportBtn = document.getElementById(`exportBtn${quarter}`);

    if (printBtn) {
        printBtn.disabled = false;
        printBtn.style.pointerEvents = '';
        printBtn.style.opacity = '';
        console.log('Print button enabled');
    }
    if (exportBtn) {
        exportBtn.disabled = false;
        exportBtn.style.pointerEvents = '';
        exportBtn.style.opacity = '';
        console.log('Export button enabled');
    }
}

// Function to export PS table to Excel
function exportToExcel(quarter) {
    const ppaDetails = selectedPPAs[quarter];
    if (!ppaDetails) {
        Swal.fire({
            icon: 'info',
            title: 'Error',
            text: 'No PPA selected. Please select a PPA first.',
        });
        return;
    }

    // Create a new workbook
    const wb = XLSX.utils.book_new();
    
    // Get table data
    const table = document.querySelector(`#psTable${quarter}`).closest('table');
    const ws = XLSX.utils.table_to_sheet(table);
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'PS Attribution');
    
    // Generate Excel file
    const filename = `PS_Attribution_${ppaDetails.title.replace(/[^a-z0-9]/gi, '_')}_Q${quarter}.xlsx`;
    XLSX.writeFile(wb, filename);
}

// Function to print PS attribution report
function printPSAttribution(quarter) {
    const ppaDetails = selectedPPAs[quarter];
    if (!ppaDetails) {
        Swal.fire({
            icon: 'info',
            title: 'Error',
            text: 'No PPA selected. Please select a PPA first.',
        });
        return;
    }

    // Create a new window for printing
    const printWindow = window.open('', '_blank');

    // Get the table content
    const table = document.querySelector(`#psTable${quarter}`).closest('.table-responsive');

    // Create the print content with proper styling
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>PS Attribution Report</title>
            <style>
                @media print {
                    body {
                        padding: 20px;
                        font-family: Arial, sans-serif;
                    }
                    .print-header {
                        margin-bottom: 30px;
                    }
                    .header-row {
                        display: flex;
                        align-items: flex-start;
                        margin-bottom: 10px;
                        font-size: 16px;
                    }
                    .header-label {
                        width: 120px;
                        text-align: left;
                        font-weight: normal;
                    }
                    .header-content {
                        flex: 1;
                        text-align: left;
                        font-weight: normal;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    .text-end {
                        text-align: right;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .table-active {
                        background-color: #f8f9fa;
                    }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <div class="header-row">
                    <div class="header-label">Activity Title:</div>
                    <div class="header-content">${ppaDetails.title}</div>
                </div>
                <div class="header-row">
                    <div class="header-label">Campus:</div>
                    <div class="header-content">${document.getElementById(`ppasCampus${quarter}`).value || 'N/A'}</div>
                </div>
                <div class="header-row">
                    <div class="header-label">Date:</div>
                    <div class="header-content">${document.getElementById(`ppasDate${quarter}`).value}</div>
                </div>
            </div>
            ${table.outerHTML}
        </body>
        </html>
    `;

    // Write the content to the new window
    printWindow.document.write(printContent);
    printWindow.document.close();

    // Add print trigger after a short delay to ensure content is loaded
    setTimeout(() => {
        printWindow.print();
        // Close the window after printing
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    }, 250);
} 