<?php
require_once 'includes/functions.php';

// Get transaction ID from URL
$transactionId = $_GET['id'] ?? '';

if (empty($transactionId)) {
    die('Transaction ID is required');
}

// Find the transaction
$transactions = getTransactions();
$transaction = null;

foreach ($transactions as $t) {
    if ($t['id'] === $transactionId) {
        $transaction = $t;
        break;
    }
}

if (!$transaction) {
    die('Transaction not found');
}

// Format date
$date = date('d/m/Y h:i A', strtotime($transaction['date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $transaction['invoiceNumber'] ?? $transaction['id'] ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
        }
        .info {
            margin-bottom: 10px;
        }
        .info div {
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 3px 0;
        }
        .amount {
            text-align: right;
        }
        .total {
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">SRI LANKAN POS SYSTEM</div>
        <div>123 Main Street, Colombo</div>
        <div>Tel: +94 11 234 5678</div>
        <div>VAT Reg: 123456789</div>
    </div>
    
    <div class="divider"></div>
    
    <div class="info">
        <div>
            <span>Receipt #:</span>
            <span><?= $transaction['invoiceNumber'] ?? $transaction['id'] ?></span>
        </div>
        <div>
            <span>Date:</span>
            <span><?= $date ?></span>
        </div>
        <div>
            <span>Payment:</span>
            <span><?= $transaction['paymentMethod'] ?></span>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th class="amount">Price</th>
                <th class="amount">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transaction['items'] as $item): ?>
            <tr>
                <td><?= $item['name'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td class="amount"><?= number_format($item['price'], 2) ?></td>
                <td class="amount"><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="divider"></div>
    
    <div class="info">
        <div>
            <span>Subtotal:</span>
            <span class="amount"><?= number_format($transaction['subtotal'], 2) ?></span>
        </div>
        <?php if (isset($transaction['tax']) && $transaction['tax'] > 0): ?>
        <div>
            <span>Tax (8%):</span>
            <span class="amount"><?= number_format($transaction['tax'], 2) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($transaction['discount'] > 0): ?>
        <div>
            <span>Discount:</span>
            <span class="amount">-<?= number_format($transaction['discount'], 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="total">
            <span>TOTAL:</span>
            <span class="amount">Rs. <?= number_format($transaction['total'], 2) ?></span>
        </div>
        <?php if (isset($transaction['amountTendered']) && $transaction['amountTendered'] > 0): ?>
        <div>
            <span>Amount Tendered:</span>
            <span class="amount"><?= number_format($transaction['amountTendered'], 2) ?></span>
        </div>
        <div>
            <span>Change:</span>
            <span class="amount"><?= number_format($transaction['change'], 2) ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="divider"></div>
    
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Please come again</p>
        <p>Powered by Sri Lankan POS System</p>
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print();" style="padding: 10px 20px; background: #009270; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Receipt
        </button>
        <button onclick="window.close();" style="padding: 10px 20px; background: #8d153a; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Uncomment the line below to automatically print when the page loads
            // window.print();
        };
    </script>
</body>
</html>