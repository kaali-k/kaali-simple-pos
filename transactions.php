<?php
// transactions.php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Handle transaction deletion
if (isset($_GET['delete'])) {
    $targetId = sanitizeInput($_GET['delete']);
    $transactions = array_filter(
        getTransactions(),
        fn($t) => $t['id'] !== $targetId
    );
    
    file_put_contents(__DIR__ . '/data/transactions.json', 
        json_encode($transactions, JSON_PRETTY_PRINT));
        
    // Redirect to prevent repeated deletions
    header('Location: transactions.php?deleted=1');
    exit;
}

$transactions = getTransactions();

// Sort transactions by date (newest first)
usort($transactions, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Filter transactions by date range if requested
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if ($startDate && $endDate) {
    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate) + 86399; // End of day
    
    $transactions = array_filter($transactions, function($transaction) use ($startTimestamp, $endTimestamp) {
        $transactionTimestamp = strtotime($transaction['date']);
        return $transactionTimestamp >= $startTimestamp && $transactionTimestamp <= $endTimestamp;
    });
}
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold sl-primary-text">Transaction History</h1>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
            Transaction deleted successfully!
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter Controls -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?= $startDate ?>" 
                       class="px-3 py-2 border rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">End Date</label>
                <input type="date" name="end_date" value="<?= $endDate ?>" 
                       class="px-3 py-2 border rounded-md">
            </div>
            <div>
                <button type="submit" 
                        class="sl-primary text-white px-4 py-2 rounded-md hover:bg-green-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <?php if ($startDate || $endDate): ?>
            <div>
                <a href="transactions.php" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 inline-block">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
            <?php endif; ?>
            <div class="ml-auto">
                <input type="text" id="searchTransactions" placeholder="Search transactions..." 
                       class="px-3 py-2 border rounded-md">
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium">Invoice #</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Date</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Items</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Total</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Payment</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y" id="transactionTableBody">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No transactions found
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($transactions as $transaction): ?>
                <tr class="transaction-row cursor-pointer hover:bg-gray-50" 
                    data-transaction-id="<?= $transaction['id'] ?>">
                    <td class="px-4 py-3">
                        <?= $transaction['invoiceNumber'] ?? 'N/A' ?>
                    </td>
                    <td class="px-4 py-3">
                        <?= date('M j, Y H:i', strtotime($transaction['date'])) ?>
                    </td>
                    <td class="px-4 py-3">
                        <?= count($transaction['items']) ?> item(s)
                    </td>
                    <td class="px-4 py-3 font-medium">
                        <?= formatCurrency($transaction['total']) ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            <?= $transaction['paymentMethod'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-2">
                            <a href="print_receipt.php?id=<?= $transaction['id'] ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="?delete=<?= $transaction['id'] ?>" 
                               class="text-red-600 hover:text-red-800"
                               onclick="return confirm('Permanently delete this transaction?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <tr class="details-row hidden">
                    <td colspan="6" class="px-4 py-3 bg-gray-50 border-t">
                        <div class="ml-4 mb-2 text-sm font-medium">Items:</div>
                        <div class="ml-4 bg-white rounded-lg shadow-inner p-3">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-xs text-gray-600">
                                        <th class="pb-1 text-left">Product</th>
                                        <th class="pb-1 text-right">Price</th>
                                        <th class="pb-1 text-right">Qty</th>
                                        <th class="pb-1 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaction['items'] as $item): ?>
                                    <tr class="text-sm">
                                        <td class="py-1"><?= $item['name'] ?></td>
                                        <td class="py-1 text-right"><?= formatCurrency($item['price']) ?></td>
                                        <td class="py-1 text-right"><?= $item['quantity'] ?></td>
                                        <td class="py-1 text-right font-medium">
                                            <?= formatCurrency($item['price'] * $item['quantity']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t">
                                        <td colspan="3" class="py-1 text-right font-medium">Subtotal:</td>
                                        <td class="py-1 text-right"><?= formatCurrency($transaction['subtotal']) ?></td>
                                    </tr>
                                    <?php if (isset($transaction['tax'])): ?>
                                    <tr>
                                        <td colspan="3" class="py-1 text-right font-medium">Tax:</td>
                                        <td class="py-1 text-right"><?= formatCurrency($transaction['tax']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($transaction['discount'] > 0): ?>
                                    <tr>
                                        <td colspan="3" class="py-1 text-right font-medium">Discount:</td>
                                        <td class="py-1 text-right text-red-600">
                                            -<?= formatCurrency($transaction['discount']) ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="font-bold">
                                        <td colspan="3" class="py-1 text-right">Total:</td>
                                        <td class="py-1 text-right"><?= formatCurrency($transaction['total']) ?></td>
                                    </tr>
                                    <?php if (isset($transaction['amountTendered'])): ?>
                                    <tr>
                                        <td colspan="3" class="py-1 text-right font-medium">Amount Tendered:</td>
                                        <td class="py-1 text-right"><?= formatCurrency($transaction['amountTendered']) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="py-1 text-right font-medium">Change:</td>
                                        <td class="py-1 text-right"><?= formatCurrency($transaction['change']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tfoot>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Toggle transaction details
document.querySelectorAll('.transaction-row').forEach(row => {
    row.addEventListener('click', (e) => {
        // Skip if clicking on action links
        if (e.target.tagName === 'A' || e.target.tagName === 'I') return;
        
        const detailsRow = row.nextElementSibling;
        detailsRow.classList.toggle('hidden');
        row.classList.toggle('bg-gray-50');
    });
});

// Search functionality
document.getElementById('searchTransactions').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.transaction-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const detailsRow = row.nextElementSibling;
        
        if (text.includes(searchTerm)) {
            row.style.display = 'table-row';
            if (!detailsRow.classList.contains('hidden')) {
                detailsRow.style.display = 'table-row';
            }
        } else {
            row.style.display = 'none';
            detailsRow.style.display = 'none';
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>