// Updated functions without hardcoded values

function extractRatingValue(ratings, ratingType, participantType) {
    try {
        if (!ratings) return 0;
        
        // Log for debugging
        console.log(`Extracting rating: ${ratingType}, participant: ${participantType}`, ratings);
        
        // Convert rating type to proper case format
        const ratingMap = {
            'excellent': 'Excellent',
            'very_satisfactory': 'Very Satisfactory',
            'satisfactory': 'Satisfactory',
            'fair': 'Fair',
            'poor': 'Poor'
        };

        const properRatingType = ratingMap[ratingType] || ratingType;
        const properParticipantType = participantType === 'batstateu' ? 'BatStateU' : 'Others';
        
        // First try standard format
        if (ratings[properRatingType] && ratings[properRatingType][properParticipantType] !== undefined) {
            return parseInt(ratings[properRatingType][properParticipantType]) || 0;
        }
        
        // Try lowercase keys
        if (ratings[ratingType] && ratings[ratingType][participantType] !== undefined) {
            return parseInt(ratings[ratingType][participantType]) || 0;
        }
        
        // Try direct access (flat structure)
        const flatKey = `${ratingType}_${participantType}`;
        if (ratings[flatKey] !== undefined) {
            return parseInt(ratings[flatKey]) || 0;
        }
        
        // Check if ratings has lowercase version of properRatingType
        const lowerCaseRatingType = properRatingType.toLowerCase();
        if (ratings[lowerCaseRatingType] && ratings[lowerCaseRatingType][participantType] !== undefined) {
            return parseInt(ratings[lowerCaseRatingType][participantType]) || 0;
        }
        
        // Replace spaces with underscores for alternative format
        const underscoreRatingType = ratingType.replace(/ /g, '_').toLowerCase();
        if (ratings[underscoreRatingType] && ratings[underscoreRatingType][participantType] !== undefined) {
            return parseInt(ratings[underscoreRatingType][participantType]) || 0;
        }

        // Return 0 if no rating found
        return 0;
    } catch (e) {
        console.error('Error extracting rating value:', e);
        return 0;
    }
}

function calculateRatingTotal(ratings, ratingType) {
    try {
        if (!ratings) return 0; // Default total is 0 if no ratings
        
        // Convert rating type to proper case format
        const ratingMap = {
            'excellent': 'Excellent',
            'very_satisfactory': 'Very Satisfactory',
            'satisfactory': 'Satisfactory',
            'fair': 'Fair',
            'poor': 'Poor'
        };

        const properRatingType = ratingMap[ratingType] || ratingType;
        
        // Check if the ratings object has the expected structure
        if (ratings[properRatingType]) {
            const batStateU = parseInt(ratings[properRatingType].BatStateU) || 0;
            const others = parseInt(ratings[properRatingType].Others) || 0;
            return batStateU + others;
        }

        // Try lowercase keys
        if (ratings[ratingType]) {
            const batStateU = parseInt(ratings[ratingType].batstateu) || 0;
            const others = parseInt(ratings[ratingType].other) || 0;
            return batStateU + others;
        }
        
        // Try flat structure
        const batStateUKey = `${ratingType}_batstateu`;
        const othersKey = `${ratingType}_other`;
        if (ratings[batStateUKey] !== undefined && ratings[othersKey] !== undefined) {
            return (parseInt(ratings[batStateUKey]) || 0) + (parseInt(ratings[othersKey]) || 0);
        }
        
        // Check if ratings has lowercase version of properRatingType
        const lowerCaseRatingType = properRatingType.toLowerCase();
        if (ratings[lowerCaseRatingType]) {
            const batStateU = parseInt(ratings[lowerCaseRatingType].batstateu) || 0;
            const others = parseInt(ratings[lowerCaseRatingType].other) || 0;
            return batStateU + others;
        }

        // Return 0 if no rating found
        return 0;
    } catch (e) {
        console.error('Error calculating rating total:', e);
        return 0;
    }
}

function calculateTotalRespondents(ratings, participantType) {
    try {
        if (!ratings) return '0';
        
        // Handle JSON string format
        if (typeof ratings === 'string') {
            try {
                ratings = JSON.parse(ratings);
            } catch (e) {
                console.error('Error parsing ratings JSON:', e);
                return '0';
            }
        }
        
        // Map participant types
        const participantMap = {
            'batstateu': 'BatStateU',
            'other': 'Others'
        };
        
        // Get the correct participant key
        const participantKey = participantMap[participantType] || participantType;
        
        console.log('Calculating total respondents for participant type:', participantKey);
        
        // Use the proper rating categories
        const ratingCategories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
        
        let total = 0;
        
        // Sum up the values for this participant type across all rating categories
        ratingCategories.forEach(category => {
            if (ratings[category] && typeof ratings[category] === 'object' && 
                ratings[category][participantKey] !== undefined) {
                const count = parseInt(ratings[category][participantKey] || 0);
                console.log(`${category} ${participantKey}: ${count}`);
                total += count;
            }
        });
        
        // If total is still 0, try alternative structure
        if (total === 0) {
            // Try alternative structure with lowercase keys
            const lowerCaseCategories = ['excellent', 'very_satisfactory', 'satisfactory', 'fair', 'poor'];
            const lowerCaseParticipant = participantType;
            
            lowerCaseCategories.forEach(category => {
                if (ratings[category] && typeof ratings[category] === 'object' && 
                    ratings[category][lowerCaseParticipant] !== undefined) {
                    const count = parseInt(ratings[category][lowerCaseParticipant] || 0);
                    console.log(`${category} ${lowerCaseParticipant}: ${count}`);
                    total += count;
                }
            });
        }
        
        console.log(`Total ${participantKey} respondents: ${total}`);
        return total.toString();
    } catch (e) {
        console.error('Error in calculateTotalRespondents:', e);
        return '0';
    }
}

function calculateTotalParticipants(ratings) {
    try {
        if (!ratings) return '0';
        
        // Handle JSON string format
        if (typeof ratings === 'string') {
            try {
                ratings = JSON.parse(ratings);
            } catch (e) {
                console.error('Error parsing ratings JSON:', e);
                return '0';
            }
        }
        
        console.log('Calculating total participants');
        
        // Use the proper rating categories
        const ratingCategories = ['Excellent', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'];
        
        // Participant types
        const participantTypes = ['BatStateU', 'Others'];
        
        let total = 0;
        
        // Sum up all values across all rating categories and participant types
        ratingCategories.forEach(category => {
            if (ratings[category] && typeof ratings[category] === 'object') {
                participantTypes.forEach(participantType => {
                    if (ratings[category][participantType] !== undefined) {
                        const count = parseInt(ratings[category][participantType]) || 0;
                        console.log(`${category} ${participantType}: ${count}`);
                        total += count;
                    }
                });
            }
        });
        
        // If total is still 0, try alternative structure
        if (total === 0) {
            // Try alternative structure with lowercase keys
            const lowerCaseCategories = ['excellent', 'very_satisfactory', 'satisfactory', 'fair', 'poor'];
            const lowerCaseParticipants = ['batstateu', 'other'];
            
            lowerCaseCategories.forEach(category => {
                if (ratings[category] && typeof ratings[category] === 'object') {
                    lowerCaseParticipants.forEach(participantType => {
                        if (ratings[category][participantType] !== undefined) {
                            const count = parseInt(ratings[category][participantType]) || 0;
                            console.log(`${category} ${participantType}: ${count}`);
                            total += count;
                        }
                    });
                }
            });
        }
        
        console.log(`Total participants: ${total}`);
        return total.toString();
    } catch (e) {
        console.error('Error in calculateTotalParticipants:', e);
        return '0';
    }
} 