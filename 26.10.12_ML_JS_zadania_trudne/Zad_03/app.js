const transactions = [ 
    { id: 1, user: "Anna", category: "RTV", amount: 1200, status: "completed" }, 
    { id: 2, user: "Piotr", category: "AGD", amount: 450, status: "pending" }, 
    { id: 3, user: "Anna", category: "RTV", amount: 300, status: "completed" }, 
    { id: 4, user: "Jan", category: "AGD", amount: 2100, status: "completed" }, 
    { id: 5, user: "Piotr", category: "RTV", amount: 150, status: "cancelled" } 
];

function analyzeTransactions(transactions) {
    transactions = transactions.filter(t => t.status === "completed");

    // total
    const totalAmount = transactions.reduce((sum, t) => {
        return sum + t.amount;
    }, 0);

    // sum up users
    const usersSummary = transactions.reduce((summary, record) => {
        summary[record.user] = (summary[record.user] || 0) + record.amount;
        return summary;
    }, {});

    // sum up categories
    const categorySummary = transactions.reduce((summary, record) => {
        summary[record.category] = (summary[record.category] || 0) + record.amount;
        return summary;
    }, {});
    
    // get top category
    const topCategory = Object.keys(categorySummary).reduce((top, category) => {
        if (categorySummary[category] > categorySummary[top] || !top) { // higher || null
            top = category; 
        }
        return top;
    }, null);

    return {
        totalCompletedAmount: totalAmount,
        usersSummary: usersSummary,
        topCategory: topCategory
    };
}

// test
const result = analyzeTransactions(transactions);
console.log(result);