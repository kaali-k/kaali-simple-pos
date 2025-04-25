<?php
// reports.php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get all transactions
$transactions = getTransactions();

// Initialize report data
$report = [
    'total' => 0,
    'tax' => 0,
    'count' => 0,
    'daily' => [],
    'categories' => [],
    'payment_methods' => [],
    'start_date' => date('Y-m-01'), // Default to month start
    'end_date' => date('Y-m-d')
];

// Process report filters
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_date'] ?? $report['start_date'];
    $end = $_POST['end_date'] ?? $report['end_date'];
    
    // Validate dates
    if (strtotime($start) && strtotime($end)) {
        $report['start_date'] = date('Y-m-d', strtotime($start));
        $report['end_date'] = date('Y-m-d', strtotime($end));
    }
}

// Calculate report data
foreach ($transactions as $transaction) {
    $transactionDate = date('Y-m-d', strtotime($transaction['date']));
    
    if ($transactionDate >= $report['start_date'] && $transactionDate <= $report['end_date']) {
        // Daily totals
        if (!isset($report['daily'][$transactionDate])) {
            $report['daily'][$transactionDate] = 0;
        }
        $report['daily'][$transactionDate] += $transaction['total'];
        
        // Payment method breakdown
        $paymentMethod = $transaction['paymentMethod'];
        if (!isset($report['payment_methods'][$paymentMethod])) {
            $report['payment_methods'][$paymentMethod] = 0;
        }
        $report['payment_methods'][$paymentMethod] += $transaction['total'];
        
        // Category breakdown
        foreach ($transaction['items'] as $item) {
            // Get product category
            $products = getProducts();
            $category = 'Uncategorized';
            
            foreach ($products as $product) {
                if ($product['id'] === $item['id']) {
                    $category = $product['category'] ?? 'Uncategorized';
                    break;
                }
            }
            
            if (!isset($report['categories'][$category])) {
                $report['categories'][$category] = 0;
            }
            $report['categories'][$category] += ($item['price'] * $item['quantity']);
        }
        
        // Overall total
        $report['total'] += $transaction['total'];
        $report['tax'] += ($transaction['tax'] ?? 0);
        $report['count']++;
    }
}

// Sort daily sales by date
ksort($report['daily']);

// Sort categories by total
arsort($report['categories']);

// Sort payment methods by total
arsort($report['payment_methods']);
?>

<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 sl-primary-text">Sales Reports</h1>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Start Date</label>
                <input type="date" name="start_date" 
                    value="<?= $report['start_date'] ?>" 
                    class="w-full px-3 py-2 border rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">End Date</label>
                <input type="date" name="end_date" 
                    value="<?= $report['end_date'] ?>" 
                    class="w-full px-3 py-2 border rounded-md">
            </div>
            <div class="self-end">
                <button type="submit" 
                        class="w-full sl-primary text-white px-4 py-2 rounded-md hover:bg-green-700">
                    <i class="fas fa-chart-line mr-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Report Summary -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-sm text-blue-600 mb-1">Reporting Period</div>
                <div class="text-xl font-bold">
                    <?= date('M j, Y', strtotime($report['start_date'])) ?> - 
                    <?= date('M j, Y', strtotime($report['end_date'])) ?>
                </div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-sm text-green-600 mb-1">Total Revenue</div>
                <div class="text-xl font-bold">
                    <?= formatCurrency($report['total']) ?>
                </div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-sm text-purple-600 mb-1">Total Tax</div>
                <div class="text-xl font-bold">
                    <?= formatCurrency($report['tax']) ?>
                </div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="text-sm text-yellow-600 mb-1">Total Transactions</div>
                <div class="text-xl font-bold">
                    <?= $report['count'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Daily Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Daily Sales</h2>
            <canvas id="salesChart" class="max-h-80"></canvas>
        </div>
        
        <!-- Category Breakdown -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Sales by Category</h2>
            <canvas id="categoryChart" class="max-h-80"></canvas>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Payment Method Breakdown -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Payment Methods</h2>
            <canvas id="paymentChart" class="max-h-80"></canvas>
        </div>
        
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">Top Categories</h2>
            <div class="space-y-4">
                <?php 
                $i = 0;
                foreach ($report['categories'] as $category => $total): 
                    if ($i++ >= 5) break; // Show only top 5
                ?>
                <div class="flex items-center">
                    <div class="w-1/3 font-medium"><?= $category ?></div>
                    <div class="w-2/3">
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold inline-block text-blue-600">
                                        <?= formatCurrency($total) ?>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold inline-block text-blue-600">
                                        <?= round(($total / $report['total']) * 100) ?>%
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                                <div style="width:<?= ($total / $report['total']) * 100 ?>%" 
                                     class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Daily Sales Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold">Daily Sales Breakdown</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium">Date</th>
                    <th class="px-4 py-3 text-right text-sm font-medium">Total Sales</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($report['daily'])): ?>
                    <tr>
                        <td colspan="2" class="px-4 py-6 text-center text-gray-500">
                            No sales data in selected period
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($report['daily'] as $date => $total): ?>
                <tr>
                    <td class="px-4 py-3">
                        <?= date('M j, Y (D)', strtotime($date)) ?>
                    </td>
                    <td class="px-4 py-3 text-right font-medium">
                        <?= formatCurrency($total) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js for visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format dates for better display
    const formatDates = dates => {
        return dates.map(date => {
            const d = new Date(date);
            return `${d.getDate()}/${d.getMonth()+1}`;
        });
    };

    // Daily Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const dates = <?= json_encode(array_keys($report['daily'])) ?>;
    const totals = <?= json_encode(array_values($report['daily'])) ?>;

    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: formatDates(dates),
            datasets: [{
                label: 'Daily Sales',
                data: totals,
                backgroundColor: 'rgba(0, 146, 112, 0.5)',
                borderColor: 'rgb(0, 146, 112)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Sales: Rs. ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categories = <?= json_encode(array_keys($report['categories'])) ?>;
    const categoryTotals = <?= json_encode(array_values($report['categories'])) ?>;
    
    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: categories,
            datasets: [{
                data: categoryTotals,
                backgroundColor: [
                    'rgba(0, 146, 112, 0.7)',
                    'rgba(248, 195, 1, 0.7)',
                    'rgba(141, 21, 58, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': Rs. ' + context.parsed.toFixed(2);
                        }
                    }
                },
                legend: {
                    position: 'right'
                }
            }
        }
    });
    
    // Payment Method Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    const paymentMethods = <?= json_encode(array_keys($report['payment_methods'])) ?>;
    const paymentTotals = <?= json_encode(array_values($report['payment_methods'])) ?>;
    
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: paymentMethods,
            datasets: [{
                data: paymentTotals,
                backgroundColor: [
                    'rgba(0, 146, 112, 0.7)',
                    'rgba(248, 195, 1, 0.7)',
                    'rgba(141, 21, 58, 0.7)',
                    'rgba(54, 162, 235, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': Rs. ' + context.parsed.toFixed(2);
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>