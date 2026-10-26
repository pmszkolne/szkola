const transactions = [ 
    { id: 1, user: "Anna", category: "RTV", amount: 1200, status: "completed" }, 
    { id: 2, user: "Piotr", category: "AGD", amount: 450, status: "pending" }, 
    { id: 3, user: "Anna", category: "RTV", amount: 300, status: "completed" }, 
    { id: 4, user: "Jan", category: "AGD", amount: 2100, status: "completed" }, 
    { id: 5, user: "Piotr", category: "RTV", amount: 150, status: "cancelled" } 
]; // rtv = 1500; agd = 2100

// only completed,
// - shows total amount
// - shows unique users and their sums
// - shows top category (sort -> first index)

function analyzeTransactions(transactions) {
    transactions = transactions.filter(t => t.status === "completed");

    const totalAmount = transactions.reduce((sum, t) => {
        return sum + t.amount;
    }, 0);

    const usersSummary = transactions.reduce((summary, record) => {
        summary[record.user] = (summary[record.user] || 0) + record.amount;
        return summary;
    }, {});
    // console.log(totalAmount, usersSummary);

    const categorySummary = transactions.reduce((summary, record) => {
        summary[record.category] = (summary[record.category] || 0) + record.amount;
        return summary;
    }, {});
    // keeps a top, and a sum
    // if sum[record.category] > sum[top] -> top = record.category

    const topCategory = Object.keys(categorySummary).reduce((top, category) => {
        if (categorySummary[category] > categorySummary[top] || !top) { top = category; }
        return top;
    }, null);

    return {
        totalCompletedAmount: totalAmount,
        usersSummary: usersSummary,
        topCategory: topCategory
    };
}

const result = analyzeTransactions(transactions);
console.log(result);