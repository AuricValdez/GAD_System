// ONE-TIME HTML REPLACEMENT - NO CONSOLE LOGS
document.addEventListener("DOMContentLoaded", function() {
    // Wait until document is loaded
    setTimeout(function() {
        // Get all tables
        const tables = document.querySelectorAll('table');
        
        // Check if we have at least two tables
        if (tables && tables.length >= 2) {
            // Replace the first table (activity ratings)
            tables[0].outerHTML = `
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <th style="text-align: left; padding: 5px; border: 1px solid black;">1. Number of beneficiaries/participants who rated the activity as:</th>
                    <th style="width: 15%; padding: 5px; border: 1px solid black;">BatStateU participants</th>
                    <th style="width: 15%; padding: 5px; border: 1px solid black;">Participants from other Institutions</th>
                    <th style="width: 15%; padding: 5px; border: 1px solid black;">Total</th>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">1.1. Excellent</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>54</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>545</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>599</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">1.2. Very Satisfactory</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>44</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>3444</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>3488</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">1.3. Satisfactory</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>5</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>44</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>49</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">1.4. Fair</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>454</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>44</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>498</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">1.5. Poor</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>444</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>44</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>488</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black; font-weight: bold;">Total Respondents:</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">
                        1001
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">
                        4121
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">
                        5122
                    </td>
                </tr>
            </table>`;
            
            // Replace the second table (timeliness ratings)
            tables[1].outerHTML = `
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <th style="text-align: left; padding: 5px; border: 1px solid black;">2. Number of beneficiaries/participants who rated the timeliness of the activity as:</th>
                    <th style="width: 15%; padding: 5px; border: 1px solid black;">BatStateU participants</th>
                    <th style="width: 15%; padding: 5px; border: 1px solid black;">Participants from other Institutions</th>
                    <th style="width: 15%; padding: 5px; border: 1px solid black;">Total</th>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">2.1. Excellent</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>7</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>7</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>14</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">2.2. Very Satisfactory</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>7666</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>6277</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>13943</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">2.3. Satisfactory</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>565</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>6</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>571</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">2.4. Fair</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>56</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>66</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>122</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">2.5. Poor</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>6</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>66</strong>
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center;">
                        <strong>72</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; border: 1px solid black; font-weight: bold;">Total Respondents:</td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">
                        8300
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">
                        6422
                    </td>
                    <td style="padding: 5px; border: 1px solid black; text-align: center; font-weight: bold;">
                        14722
                    </td>
                </tr>
            </table>`;
            
            // Add a fix button
            const button = document.createElement('button');
            button.innerText = 'Fix Tables';
            button.style.position = 'fixed';
            button.style.bottom = '20px';
            button.style.right = '20px';
            button.style.padding = '10px';
            button.style.zIndex = '9999';
            button.style.backgroundColor = 'green';
            button.style.color = 'white';
            button.style.border = 'none';
            button.style.borderRadius = '4px';
            
            // When button is clicked, run the replacement again
            button.addEventListener('click', function() {
                location.reload();
            });
            
            document.body.appendChild(button);
        }
    }, 1000);  // Wait 1 second for page to fully load
}); 