/**
 * Display Extension Proposal
 * This script handles the display of the extension proposal form with the Roman numeral structure.
 */

function displayExtensionProposal(data) {
    if (!data || !data.sections) {
        return '<div class="alert alert-warning">No proposal data available</div>';
    }

    const sections = data.sections;
    const now = new Date();
    
    // Create HTML for the form
    let html = `
    <div class="proposal-container">
        <!-- Header Section - Structured as a table matching exactly the reference image -->
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
            <tr>
                <td style="width: 15%; text-align: center; border: 1px solid black; padding: 10px;">
                    <img src="../images/BatStateU-NEU-Logo.png" alt="Batangas State Logo" style="max-width: 80px;">
                </td>
                <td style="width: 30%; text-align: center; border: 1px solid black; padding: 10px;">
                    <div style="font-size: 10pt;">Reference No.: BatStateU-FO-ESO-01</div>
                </td>
                <td style="width: 30%; text-align: center; border: 1px solid black; padding: 10px;">
                    <div style="font-size: 10pt;">Effectivity Date: August 25, 2023</div>
                </td>
                <td style="width: 25%; text-align: center; border: 1px solid black; padding: 10px;">
                    <div style="font-size: 10pt;">Revision No.: 03</div>
                </td>
            </tr>
        </table>

        <!-- Title Section -->
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
            <tr>
                <td style="text-align: center; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 10px;">
                    <strong>EXTENSION PROGRAM PLAN / PROPOSAL</strong>
                </td>
            </tr>
        </table>

        <!-- Request Type Checkboxes - Exact format from reference image -->
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
            <tr>
                <td style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 10px;">
                    <div>☒ Extension Service Program/Project/Activity is requested by clients.</div>
                    <div>☐ Extension Service Program/Project/Activity is Department's initiative.</div>
                </td>
            </tr>
        </table>
        
        <!-- Program/Project/Activity Checkboxes - Exact format from reference image -->
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
            <tr>
                <td style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 10px;">
                    <div style="display: flex; justify-content: center;">
                        <span style="margin-right: 30px;">☐ Program</span>
                        <span style="margin-right: 30px;">☒ Project</span>
                        <span>☐ Activity</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Content -->
        <div style="padding: 0 10px;">
            <!-- I. Title -->
            <div style="margin-bottom: 15px;">
                <div><strong>I. Title</strong></div>
                <div style="margin-left: 20px;">${sections.I_title || 'Extension Activity Title'}</div>
            </div>
            
            <!-- II. Location -->
            <div style="margin-bottom: 15px;">
                <div><strong>II. Location</strong></div>
                <div style="margin-left: 20px;">${sections.II_location || 'Activity Location'}</div>
            </div>
            
            <!-- III. Duration (Date and Time) -->
            <div style="margin-bottom: 15px;">
                <div><strong>III. Duration (Date and Time)</strong></div>
                <div style="margin-left: 20px; white-space: pre-line;">${sections.III_duration || 'Date and time of the activity'}</div>
            </div>
            
            <!-- IV. Type of Extension Service Agenda -->
            <div style="margin-bottom: 15px;">
                <div><strong>IV. Type of Extension Service Agenda</strong></div>
                <div style="margin-left: 20px; font-style: italic;">Choose the MOST (only one) applicable Extension Agenda from the following:</div>
                <div style="margin-left: 20px;">
                    ${(() => {
                        // Define all possible extension service agenda options
                        const allAgendaOptions = [
                            'BatStateU Inclusive Social Innovation for Regional Growth (BISIG) Program',
                            'Community Development Program',
                            'Technology Transfer, Utilization, and Commercialization',
                            'Technical Assistance and Advisory Services',
                            'Livelihood Development Program',
                            'Educational and Cultural Exchange'
                        ];
                        
                        // Get selected agenda from data
                        let selectedAgendas = [];
                        
                        // Check if we have data in the database format
                        if (sections.IV_extension_service_agenda && sections.IV_extension_service_agenda.options) {
                            // Use the options object directly if available
                            const options = sections.IV_extension_service_agenda.options;
                            selectedAgendas = Object.keys(options).filter(key => options[key] === true);
                        } else if (Array.isArray(sections.IV_extension_service_agenda)) {
                            // Or use the array if provided in that format
                            selectedAgendas = sections.IV_extension_service_agenda;
                        } else if (typeof sections.IV_extension_service_agenda === 'string') {
                            // Or use a single string value
                            selectedAgendas = [sections.IV_extension_service_agenda];
                        }
                        
                        // Generate checkboxes for all options, marking only the selected ones
                        return allAgendaOptions.map(option => {
                            const isChecked = selectedAgendas.includes(option);
                            return `<div>${isChecked ? '☒' : '☐'} ${option}</div>`;
                        }).join('');
                    })()}
                </div>
            </div>
            
            <!-- V. Sustainable Development Goals (SDG) -->
            <div style="margin-bottom: 15px;">
                <div><strong>V. Sustainable Development Goals (SDG)</strong></div>
                <div style="margin-left: 20px; font-style: italic;">Choose the applicable SDG to your extension project:</div>
                <div style="margin-left: 20px; display: grid; grid-template-columns: 1fr 1fr;">
                    ${(() => {
                        // Define all possible SDG options
                        const allSDGOptions = [
                            'SDG 1: No Poverty',
                            'SDG 2: Zero Hunger',
                            'SDG 3: Good Health and Well-Being',
                            'SDG 4: Quality Education',
                            'SDG 5: Gender Equality',
                            'SDG 6: Clean Water and Sanitation',
                            'SDG 7: Affordable and Clean Energy',
                            'SDG 8: Decent Work and Economic Growth',
                            'SDG 9: Industry, Innovation, and Infrastructure',
                            'SDG 10: Reduced Inequality',
                            'SDG 11: Sustainable Cities and Communities',
                            'SDG 12: Responsible Consumption and Production',
                            'SDG 13: Climate Action',
                            'SDG 14: Life Below Water',
                            'SDG 15: Life on Land',
                            'SDG 16: Peace, Justice, and Strong Institutions',
                            'SDG 17: Partnerships for the Goals'
                        ];
                        
                        // Get selected SDGs from data
                        let selectedSDGs = [];
                        
                        // Check if we have data in the database format
                        if (sections.V_sustainable_development_goals && sections.V_sustainable_development_goals.options) {
                            // Use the options object directly if available
                            const options = sections.V_sustainable_development_goals.options;
                            selectedSDGs = Object.keys(options).filter(key => options[key] === true);
                        } else if (Array.isArray(sections.V_sustainable_development_goals)) {
                            // Or use the array if provided in that format
                            selectedSDGs = sections.V_sustainable_development_goals;
                        } else if (typeof sections.V_sustainable_development_goals === 'string') {
                            // Or use a single string value
                            selectedSDGs = [sections.V_sustainable_development_goals];
                        }
                        
                        // Generate checkboxes for all options, marking only the selected ones
                        return allSDGOptions.map(option => {
                            // Check if this SDG is selected either by exact match or by SDG number (e.g., "SDG 1" matches "SDG 1: No Poverty")
                            const sdgNumber = option.split(':')[0].trim(); // Extract "SDG X"
                            const isChecked = selectedSDGs.some(sdg => 
                                sdg === option || // Exact match
                                sdg === sdgNumber || // Just "SDG X"
                                option.includes(sdg) // SDG description contains the value
                            );
                            
                            return `<div>${isChecked ? '☒' : '☐'} ${option}</div>`;
                        }).join('');
                    })()}
                </div>
            </div>
            
            <!-- VI. Offices / Colleges / Organizations Involved -->
            <div style="margin-bottom: 15px;">
                <div><strong>VI. Offices / Colleges / Organizations Involved</strong></div>
                <div style="margin-left: 20px;">
                    ${Array.isArray(sections.VI_offices_involved) 
                        ? sections.VI_offices_involved.map(office => `<div>${office}</div>`).join('') 
                        : sections.VI_offices_involved || 'No offices specified'}
                </div>
            </div>
            
            <!-- VII. Programs Involved -->
            <div style="margin-bottom: 15px;">
                <div><strong>VII. Programs Involved</strong></div>
                <div style="margin-left: 20px;">
                    ${Array.isArray(sections.VII_programs_involved) 
                        ? sections.VII_programs_involved.map(program => `<div>${program}</div>`).join('') 
                        : sections.VII_programs_involved || 'No programs specified'}
                </div>
            </div>
            
            <!-- VIII. Project Leader, Assistant Project Leader and Coordinators -->
            <div style="margin-bottom: 15px;">
                <div><strong>VIII. Project Leader, Assistant Project Leader and Coordinators</strong></div>
                <div style="margin-left: 20px;">
                    <div><strong>Project Leader:</strong> 
                        ${Array.isArray(sections.VIII_project_leaders.project_leader) 
                            ? sections.VIII_project_leaders.project_leader.join(', ') 
                            : sections.VIII_project_leaders.project_leader || 'No project leader specified'}
                    </div>
                    <div><strong>Assistant Project Leader:</strong> 
                        ${Array.isArray(sections.VIII_project_leaders.assistant_project_leader) 
                            ? sections.VIII_project_leaders.assistant_project_leader.join(', ') 
                            : sections.VIII_project_leaders.assistant_project_leader || 'No assistant project leader specified'}
                    </div>
                    <div><strong>Coordinators:</strong> 
                        ${Array.isArray(sections.VIII_project_leaders.coordinators) 
                            ? sections.VIII_project_leaders.coordinators.join(', ') 
                            : sections.VIII_project_leaders.coordinators || 'No coordinators specified'}
                    </div>
                </div>
            </div>
            
            <!-- IX. Assigned Tasks -->
            <div style="margin-bottom: 15px;">
                <div><strong>IX. Assigned Tasks</strong></div>
                <div style="margin-left: 20px;">
                    ${Array.isArray(sections.IX_assigned_tasks) 
                        ? sections.IX_assigned_tasks.map(task => `
                            <div><strong>${task.name || 'Person'}:</strong></div>
                            <ul style="margin-top: 0; margin-bottom: 10px;">
                                ${Array.isArray(task.responsibilities) 
                                    ? task.responsibilities.map(resp => `<li>${resp}</li>`).join('') 
                                    : `<li>${task.responsibilities || 'No responsibilities specified'}</li>`}
                            </ul>
                        `).join('')
                        : '<div>No assigned tasks specified</div>'}
                </div>
            </div>
            
            <!-- X. Partner Agencies -->
            <div style="margin-bottom: 15px;">
                <div><strong>X. Partner Agencies</strong></div>
                <div style="margin-left: 20px;">
                    ${Array.isArray(sections.X_partner_agencies) 
                        ? sections.X_partner_agencies.join(', ') 
                        : sections.X_partner_agencies || 'No partner agencies specified'}
                </div>
            </div>
            
            <!-- XI. Beneficiaries (Type and Number of Male and Female) -->
            <div style="margin-bottom: 15px;">
                <div><strong>XI. Beneficiaries (Type and Number of Male and Female)</strong></div>
                <div style="margin-left: 20px;">
                    <div><strong>Type:</strong> ${sections.XI_beneficiaries.type || 'Beneficiary Type'}</div>
                    <!-- Keep table for gender data as it's actually tabular data -->
                    <table style="width: 200px; border-collapse: collapse; margin-top: 5px;">
                        <tr>
                            <th style="border: 1px solid black; padding: 3px; text-align: left;">Male</th>
                            <td style="border: 1px solid black; padding: 3px; text-align: center;">${sections.XI_beneficiaries.male || '0'}</td>
                        </tr>
                        <tr>
                            <th style="border: 1px solid black; padding: 3px; text-align: left;">Female</th>
                            <td style="border: 1px solid black; padding: 3px; text-align: center;">${sections.XI_beneficiaries.female || '0'}</td>
                        </tr>
                        <tr>
                            <th style="border: 1px solid black; padding: 3px; text-align: left;">Total</th>
                            <td style="border: 1px solid black; padding: 3px; text-align: center;">${sections.XI_beneficiaries.total || '0'}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- XII. Total Cost -->
            <div style="margin-bottom: 15px;">
                <div><strong>XII. Total Cost</strong></div>
                <div style="margin-left: 20px;">₱ ${parseFloat(sections.XII_total_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) || '0.00'}</div>
            </div>
            
            <!-- XIII. Source of fund -->
            <div style="margin-bottom: 15px;">
                <div><strong>XIII. Source of fund</strong></div>
                <div style="margin-left: 20px;">${sections.XIII_source_of_fund || 'Not specified'}</div>
            </div>
            
            <!-- XIV. Rationale -->
            <div style="margin-bottom: 15px;">
                <div><strong>XIV. Rationale</strong></div>
                <div style="margin-left: 20px; text-align: justify;">${sections.XIV_rationale || 'No rationale provided'}</div>
            </div>
            
            <!-- XV. Objectives (General and Specific) -->
            <div style="margin-bottom: 15px;">
                <div><strong>XV. Objectives (General and Specific)</strong></div>
                <div style="margin-left: 20px;">
                    <div><strong>General Objective:</strong></div>
                    <div style="margin-left: 20px; text-align: justify; margin-bottom: 10px;">${sections.XV_objectives.general || 'No general objective provided'}</div>
                    
                    <div><strong>Specific Objectives:</strong></div>
                    <ol style="margin-left: 20px;">
                        ${Array.isArray(sections.XV_objectives.specific) 
                            ? sections.XV_objectives.specific.map(obj => `<li>${obj}</li>`).join('') 
                            : `<li>${sections.XV_objectives.specific || 'No specific objectives provided'}</li>`}
                    </ol>
                </div>
            </div>
            
            <!-- XVI. Program/Project Expected Output -->
            <div style="margin-bottom: 15px;">
                <div><strong>XVI. Program/Project Expected Output</strong></div>
                <div style="margin-left: 20px; text-align: justify;">
                    ${sections.XVI_expected_output || 'No expected output provided'}
                </div>
            </div>
            
            <!-- XVII. Description, Strategies and Methods -->
            <div style="margin-bottom: 15px;">
                <div><strong>XVII. Description, Strategies and Methods (Activities / Schedule)</strong></div>
                <div style="margin-left: 20px;">
                    <div><strong>Description:</strong></div>
                    <div style="margin-left: 20px; text-align: justify; margin-bottom: 10px;">
                        ${sections.XVII_description.description || 'No description provided'}
                    </div>
                    
                    <div><strong>Strategies:</strong></div>
                    <ul style="margin-left: 20px;">
                        ${Array.isArray(sections.XVII_description.strategies) 
                            ? sections.XVII_description.strategies.map(strategy => `<li>${strategy}</li>`).join('') 
                            : `<li>${sections.XVII_description.strategies || 'No strategies provided'}</li>`}
                    </ul>
                    
                    <div><strong>Methods/Activities:</strong></div>
                    <ul style="margin-left: 20px;">
                        ${Array.isArray(sections.XVII_description.methods) 
                            ? sections.XVII_description.methods.map(method => `<li>${method}</li>`).join('') 
                            : `<li>${sections.XVII_description.methods || 'No methods provided'}</li>`}
                    </ul>
                    
                    <div><strong>Schedule:</strong></div>
                    <ul style="margin-left: 20px;">
                        ${Array.isArray(sections.XVII_description.schedule) 
                            ? sections.XVII_description.schedule.map(schedule => `<li>${schedule}</li>`).join('') 
                            : `<li>${sections.XVII_description.schedule || 'No schedule provided'}</li>`}
                    </ul>
                </div>
            </div>
            
            <!-- XVIII. Financial Plan -->
            <div style="margin-bottom: 15px;">
                <div><strong>XVIII. Financial Plan</strong></div>
                <div style="margin-left: 20px;">
                    ${Array.isArray(sections.XVIII_financial_plan.items) && sections.XVIII_financial_plan.items.length > 0 ? `
                    <!-- Keep table for financial plan as it's actually tabular data -->
                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                        <tr>
                            <th style="border: 1px solid black; padding: 5px; width: 40%;">Item Description</th>
                            <th style="border: 1px solid black; padding: 5px; width: 15%;">Unit</th>
                            <th style="border: 1px solid black; padding: 5px; width: 15%;">Quantity</th>
                            <th style="border: 1px solid black; padding: 5px; width: 15%;">Unit Cost</th>
                            <th style="border: 1px solid black; padding: 5px; width: 15%;">Total Cost</th>
                        </tr>
                        ${sections.XVIII_financial_plan.items.map((item, i) => {
                            const quantity = sections.XVIII_financial_plan.quantities && sections.XVIII_financial_plan.quantities[i] ? sections.XVIII_financial_plan.quantities[i] : '';
                            const unit = sections.XVIII_financial_plan.units && sections.XVIII_financial_plan.units[i] ? sections.XVIII_financial_plan.units[i] : '';
                            const unitCost = sections.XVIII_financial_plan.unit_costs && sections.XVIII_financial_plan.unit_costs[i] ? sections.XVIII_financial_plan.unit_costs[i] : '0';
                            // Calculate total cost for this row
                            const rowTotal = (parseFloat(quantity) * parseFloat(unitCost)).toFixed(2);
                            
                            return `
                            <tr>
                                <td style="border: 1px solid black; padding: 5px;">${item}</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">${unit}</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: center;">${quantity}</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: right;">₱ ${parseFloat(unitCost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td style="border: 1px solid black; padding: 5px; text-align: right;">₱ ${parseFloat(rowTotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                            `;
                        }).join('')}
                        <tr>
                            <td colspan="4" style="border: 1px solid black; padding: 5px; text-align: right;"><strong>Total</strong></td>
                            <td style="border: 1px solid black; padding: 5px; text-align: right;"><strong>₱ ${parseFloat(sections.XVIII_financial_plan.total_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                        </tr>
                    </table>
                    ` : '<div>No financial plan items specified</div>'}
                </div>
            </div>
            
            <!-- XIX. Monitoring and Evaluation Mechanics / Plan -->
            <div style="margin-bottom: 15px;">
                <div><strong>XIX. Monitoring and Evaluation Mechanics / Plan</strong></div>
                <div style="margin-left: 20px;">
                    <!-- Keep table for monitoring and evaluation as it's actually tabular data -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                        <tr>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 13%;">Objectives</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Indicators</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 12%;">Baseline Data</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Target</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 12%;">Data Source</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 13%;">Collection Method</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 10%;">Frequency</th>
                            <th style="border: 1px solid black; padding: 3px; word-break: break-word; width: 14%;">Responsible</th>
                        </tr>
                        ${Array.isArray(sections.XIX_monitoring_evaluation) && sections.XIX_monitoring_evaluation.length > 0 
                            ? sections.XIX_monitoring_evaluation.map(item => `
                                <tr>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.objective || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.performance_indicator || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.baseline_data || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.performance_target || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.data_source || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.collection_method || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.frequency || ''}</td>
                                    <td style="border: 1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item.responsible || ''}</td>
                                </tr>
                            `).join('')
                            : `<tr><td colspan="8" style="border: 1px solid black; padding: 5px; text-align: center;">No monitoring and evaluation plan specified</td></tr>`
                        }
                    </table>
                </div>
            </div>
            
            <!-- XX. Sustainability Plan -->
            <div style="margin-bottom: 15px;">
                <div><strong>XX. Sustainability Plan</strong></div>
                <div style="margin-left: 20px;">
                    <div style="text-align: justify; margin-bottom: 10px;" class="sustainability-plan">${sections.XX_sustainability_plan.plan || 'No sustainability plan provided'}</div>
                    <ol class="sustainability-plan">
                        ${Array.isArray(sections.XX_sustainability_plan.steps) 
                            ? sections.XX_sustainability_plan.steps.map(step => `<li>${step}</li>`).join('') 
                            : `<li>${sections.XX_sustainability_plan.steps || 'No sustainability steps provided'}</li>`}
                    </ol>
                </div>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- Signatures section -->
        <div style="margin-top: 20px;">
            <div style="display: flex; margin-bottom: 20px;">
                <div style="width: 50%; padding: 10px; border: 0.5px solid black;">
                    <p style="margin: 0; font-weight: bold; text-align: center;">Prepared by:</p>
                    <br><br><br>
                    <p style="margin: 0; text-align: center;"><strong>DR. JOHN DOE</strong></p>
                    <p style="margin: 0; text-align: center;">Extension Coordinator</p>
                    <p style="margin: 0; text-align: center;">Date Signed: _______________</p>
                </div>
                <div style="width: 50%; padding: 10px; border-top: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black;">
                    <p style="margin: 0; font-weight: bold; text-align: center;">Reviewed by:</p>
                    <br><br><br>
                    <p style="margin: 0; text-align: center;"><strong>MS. JANE SMITH</strong></p>
                    <p style="margin: 0; text-align: center;">Head, Extension Services</p>
                    <p style="margin: 0; text-align: center;">Date Signed: _______________</p>
                </div>
            </div>
            
            <div style="display: flex; margin-bottom: 20px;">
                <div style="width: 50%; padding: 10px; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black;">
                    <p style="margin: 0; font-weight: bold; text-align: center;">Recommending Approval:</p>
                    <br><br><br>
                    <p style="margin: 0; text-align: center;"><strong>DR. MARIA GONZALES</strong></p>
                    <p style="margin: 0; text-align: center;">Vice Chancellor for Research, Development and Extension Services</p>
                    <p style="margin: 0; text-align: center;">Date Signed: _______________</p>
                </div>
                <div style="width: 50%; padding: 10px; border-right: 0.5px solid black; border-bottom: 0.5px solid black;">
                    <p style="margin: 0; text-align: center;">N/A</p>
                </div>
            </div>
            
            <div style="padding: 10px; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black; text-align: center;">
                <p style="margin: 0; font-weight: bold; text-align: center;">Approved by:</p>
                <br><br><br>
                <p style="margin: 0; text-align: center;"><strong>ATTY. ROBERT WILLIAMS</strong></p>
                <p style="margin: 0; text-align: center;">Chancellor</p>
                <p style="margin: 0; text-align: center;">Date Signed: _______________</p>
            </div>
        </div>
        
        <p style="font-size: 9pt; margin-top: 10px;">Required Attachment: If Extension Service Program/Project/Activity is requested by clients, attach the letter of request with endorsement from the University President</p>
        <p style="font-size: 9pt;">Cc: (1) Office of the College Dean/Head, Academic Affairs for CCIC</p>
        
        <!-- Page numbers -->
        <div class="page-footer">
            <span>Page <span class="page-number">1</span> of <span class="total-pages">2</span></span>
        </div>
    </div>`;

    return html;
}

// Call this function to generate the proposal HTML when data is available
function generateReport() {
    const selectedCampus = $('#campus').val();
    const selectedYear = $('#year').val();
    const selectedPosition = $('#prepared_by').val();
    const selectedProposalId = $('#proposal_id').val();
    
    if (!selectedCampus || !selectedYear || !selectedPosition || !selectedProposalId) {
        Swal.fire({
            icon: 'warning',
            title: 'Selection Required',
            text: 'Please select all required fields to generate the proposal.'
        });
        return;
    }
    
    // Show loading state
    $('#reportPreview').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading extension proposal...</p>
        </div>
    `);
    
    // Fetch the proposal data using the extension API
    $.ajax({
        url: 'api/get_extension_proposal_details.php',
        method: 'GET',
        data: {
            campus: selectedCampus,
            year: selectedYear,
            proposal_id: selectedProposalId,
            position: selectedPosition
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                // Display the proposal
                const html = displayExtensionProposal(response.data);
                $('#reportPreview').html(html);
                
                // Update page numbers
                updatePageNumbers();
            } else {
                $('#reportPreview').html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${response.message || 'Failed to load proposal data'}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            $('#reportPreview').html(`
                <div class="alert alert-danger">
                    <strong>Error:</strong> Could not load proposal. ${error}
                </div>
            `);
        }
    });
}

// Function to update page numbers
function updatePageNumbers() {
    // Count the number of pages based on page-break elements
    const pageBreaks = document.querySelectorAll('.page-break').length;
    const totalPages = pageBreaks + 1;
    
    // Update all total-pages elements
    document.querySelectorAll('.total-pages').forEach(el => {
        el.textContent = totalPages;
    });
    
    // Update page numbers sequentially
    document.querySelectorAll('.page-number').forEach((el, index) => {
        el.textContent = index + 1;
    });
}

// Print function for the proposal
function printReport() {
    const printWindow = window.open('', '_blank', 'width=1200,height=800');
    
    printWindow.document.open();
    printWindow.document.title = "Extension Proposal";
    
    const reportContent = $('#reportPreview').html();
    
    // Add print-specific styles
    const printStyles = `
        <style>
            @page {
                size: 8.5in 13in;
                margin-top: 1.52cm;
                margin-bottom: 2cm;
                margin-left: 1.78cm;
                margin-right: 2.03cm;
                border-top: 1px solid black !important;
                border-bottom: 1px solid black !important;
            }
            
            body {
                background-color: white !important;
                color: black !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 12pt !important;
                line-height: 1.2 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .proposal-container {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 12pt !important;
                background-color: #fff !important;
                color: #000 !important;
            }
            
            .header-table, .data-table, .signatures-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 10px !important;
            }
            
            .header-table td, .data-table th, .data-table td, .signatures-table td {
                border: 0.5px solid #000 !important;
                padding: 5px !important;
            }
            
            .page-break {
                page-break-before: always !important;
            }
            
            /* Hide page numbers in print mode - they will be added by the browser */
            .page-footer {
                display: none !important;
            }
            
            /* Force blue text for sustainability plan */
            .sustainability-plan, .sustainability-plan * {
                color: blue !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            @media print {
                @page {
                    border-top: 1px solid black !important;
                    border-bottom: 1px solid black !important;
                }
                
                @page:last {
                    border-bottom: none !important;
                }
            }
        </style>
    `;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Extension Proposal</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            ${printStyles}
        </head>
        <body>
            ${reportContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.addEventListener('afterprint', function() {
            printWindow.close();
        });
    }, 500);
}

// Initialize the form when document is ready
$(document).ready(function() {
    // Set up event handlers for form elements
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
    
    // Add print button event handler
    $('#printBtn').on('click', function() {
        printReport();
    });
}); 