// Helper formatting functions for narrative reports

// Function to format team members data for display
function formatSimpleTeamMember(tasks, personnel) {
    console.log('Formatting team member:', tasks, personnel);
    
    if (!tasks && !personnel) return 'N/A';
    
    let result = '';
    
    // If we have personnel data with names
    if (personnel && Array.isArray(personnel)) {
        result = personnel.map(person => {
            if (typeof person === 'string') {
                return person;
            } else if (person && person.name) {
                return person.name;
            } else if (person && person.full_name) {
                return person.full_name;
            } else {
                return JSON.stringify(person);
            }
        }).join(', ');
    }
    
    // If we don't have personnel data but have tasks data with names
    if (!result && tasks) {
        if (Array.isArray(tasks)) {
            // Extract names from task assignments if possible
            const names = tasks
                .map(task => {
                    if (typeof task === 'string') return task;
                    if (task && task.name) return task.name;
                    if (task && task.assigned_to) return task.assigned_to;
                    return '';
                })
                .filter(name => name); // Remove empty strings
                
            if (names.length > 0) {
                result = names.join(', ');
            }
        } else if (typeof tasks === 'object' && tasks !== null) {
            // Handle object with name property
            if (tasks.name) result = tasks.name;
            else if (tasks.assigned_to) result = tasks.assigned_to;
            else result = JSON.stringify(tasks);
        } else if (typeof tasks === 'string') {
            // If it's just a string, use it directly
            result = tasks;
        }
    }
    
    return result || 'N/A';
}

// Function to format implementing office data
function formatImplementingOffice(office) {
    if (!office) return 'N/A';
    
    if (typeof office === 'string') {
        return office;
    } else if (Array.isArray(office)) {
        return office.join(', ');
    } else if (typeof office === 'object') {
        return Object.values(office).filter(v => v).join(', ');
    }
    
    return String(office);
}

// Function to format extension agenda
function formatExtensionAgenda(agenda, singleOnly = false) {
    if (!agenda) return 'N/A';
    
    // If it's already a string, return it
    if (typeof agenda === 'string') {
        return agenda;
    }
    
    // If it's an array, convert to comma-separated list
    if (Array.isArray(agenda)) {
        if (singleOnly && agenda.length > 0) {
            return agenda[0]; // Return only the first one if singleOnly is true
        }
        return agenda.join(', ');
    }
    
    // If it's an object, check for specific properties
    if (typeof agenda === 'object' && agenda !== null) {
        // Try to extract a selected agenda
        for (const key in agenda) {
            if (agenda[key] === true || agenda[key] === 1 || agenda[key] === '1') {
                return key;
            }
        }
        
        // If no selected found, return all keys
        return Object.keys(agenda).join(', ');
    }
    
    return String(agenda);
}

// Function to format SDGs
function formatSDGs(sdgs) {
    if (!sdgs) return 'N/A';
    
    // If it's already a string, return it
    if (typeof sdgs === 'string') {
        return sdgs;
    }
    
    // If it's an array, convert to comma-separated list
    if (Array.isArray(sdgs)) {
        return sdgs.join(', ');
    }
    
    // If it's an object, check for true/1 values
    if (typeof sdgs === 'object' && sdgs !== null) {
        const selectedSDGs = [];
        
        // Common SDG descriptions
        const sdgDescriptions = {
            '1': 'No Poverty',
            '2': 'Zero Hunger',
            '3': 'Good Health and Well-being',
            '4': 'Quality Education',
            '5': 'Gender Equality',
            '6': 'Clean Water and Sanitation',
            '7': 'Affordable and Clean Energy',
            '8': 'Decent Work and Economic Growth',
            '9': 'Industry, Innovation and Infrastructure',
            '10': 'Reduced Inequalities',
            '11': 'Sustainable Cities and Communities',
            '12': 'Responsible Consumption and Production',
            '13': 'Climate Action',
            '14': 'Life Below Water',
            '15': 'Life on Land',
            '16': 'Peace, Justice and Strong Institutions',
            '17': 'Partnerships for the Goals'
        };
        
        for (const key in sdgs) {
            if (sdgs[key] === true || sdgs[key] === 1 || sdgs[key] === '1') {
                // Try to match with a description
                const numKey = key.replace(/\D/g, '');
                const description = sdgDescriptions[numKey] || key;
                selectedSDGs.push(description);
            }
        }
        
        return selectedSDGs.length > 0 ? selectedSDGs.join(', ') : 'N/A';
    }
    
    return String(sdgs);
}

// Function to format beneficiary data
function formatBeneficiaryData(beneficiaryData) {
    if (!beneficiaryData) return 'N/A';
    
    let result = '';
    
    // Handle string format
    if (typeof beneficiaryData === 'string') {
        return beneficiaryData;
    }
    
    // Handle object format with male, female, and type
    if (typeof beneficiaryData === 'object' && beneficiaryData !== null) {
        const maleCount = beneficiaryData.male || data.male_beneficiaries || 0;
        const femaleCount = beneficiaryData.female || data.female_beneficiaries || 0;
        const totalCount = parseInt(maleCount) + parseInt(femaleCount) || beneficiaryData.total || data.total_beneficiaries || 0;
        const type = beneficiaryData.type || data.beneficiary_type || 'N/A';
        
        result = `Male: ${maleCount}, Female: ${femaleCount}, Total: ${totalCount}, Type: ${type}`;
    }
    
    return result || 'N/A';
}

// Function to format assigned tasks table
function formatAssignedTasksTable(leaderTasks, assistantTasks, staffTasks, personnel) {
    let rows = '';
    
    // Process leader tasks
    if (leaderTasks) {
        if (Array.isArray(leaderTasks)) {
            leaderTasks.forEach(task => {
                if (typeof task === 'string') {
                    rows += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">Project Leader</td>
                        <td style="border: 1px solid black; padding: 5px;">${task}</td>
                    </tr>`;
                } else if (task && typeof task === 'object') {
                    const name = task.name || 'Project Leader';
                    const taskDescription = task.task || task.responsibility || task.description || 'N/A';
                    rows += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">${name}</td>
                        <td style="border: 1px solid black; padding: 5px;">${taskDescription}</td>
                    </tr>`;
                }
            });
        } else if (typeof leaderTasks === 'string') {
            rows += `
            <tr>
                <td style="border: 1px solid black; padding: 5px;">Project Leader</td>
                <td style="border: 1px solid black; padding: 5px;">${leaderTasks}</td>
            </tr>`;
        }
    }
    
    // Process assistant tasks
    if (assistantTasks) {
        if (Array.isArray(assistantTasks)) {
            assistantTasks.forEach(task => {
                if (typeof task === 'string') {
                    rows += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">Assistant Project Leader</td>
                        <td style="border: 1px solid black; padding: 5px;">${task}</td>
                    </tr>`;
                } else if (task && typeof task === 'object') {
                    const name = task.name || 'Assistant Project Leader';
                    const taskDescription = task.task || task.responsibility || task.description || 'N/A';
                    rows += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">${name}</td>
                        <td style="border: 1px solid black; padding: 5px;">${taskDescription}</td>
                    </tr>`;
                }
            });
        } else if (typeof assistantTasks === 'string') {
            rows += `
            <tr>
                <td style="border: 1px solid black; padding: 5px;">Assistant Project Leader</td>
                <td style="border: 1px solid black; padding: 5px;">${assistantTasks}</td>
            </tr>`;
        }
    }
    
    // Process staff tasks
    if (staffTasks) {
        if (Array.isArray(staffTasks)) {
            staffTasks.forEach(task => {
                if (typeof task === 'string') {
                    rows += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">Staff</td>
                        <td style="border: 1px solid black; padding: 5px;">${task}</td>
                    </tr>`;
                } else if (task && typeof task === 'object') {
                    const name = task.name || 'Staff';
                    const taskDescription = task.task || task.responsibility || task.description || 'N/A';
                    rows += `
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">${name}</td>
                        <td style="border: 1px solid black; padding: 5px;">${taskDescription}</td>
                    </tr>`;
                }
            });
        } else if (typeof staffTasks === 'string') {
            rows += `
            <tr>
                <td style="border: 1px solid black; padding: 5px;">Staff</td>
                <td style="border: 1px solid black; padding: 5px;">${staffTasks}</td>
            </tr>`;
        }
    }
    
    // If no tasks found, add placeholder row
    if (!rows) {
        rows = `
        <tr>
            <td style="border: 1px solid black; padding: 5px;" colspan="2">No tasks assigned</td>
        </tr>`;
    }
    
    return rows;
}

// Function to format specific objectives
function formatSpecificObjectives(objectives) {
    if (!objectives || objectives.length === 0) {
        return '<p>No specific objectives provided.</p>';
    }
    
    if (typeof objectives === 'string') {
        return `<p>${objectives}</p>`;
    }
    
    if (Array.isArray(objectives)) {
        return `<ol>${objectives.map(obj => `<li>${obj}</li>`).join('')}</ol>`;
    }
    
    return '<p>Invalid objectives format.</p>';
} 