<?php
require_once __DIR__ . '/includes/functions.php';

// Ensure we only accept JSON requests
header('Content-Type: application/json');

try {
    // Get and validate input
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('No data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }

    // Validate required fields
    $requiredFields = ['items', 'subtotal', 'total', 'paymentMethod', 'invoiceNumber'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Save transaction
    $transactions = getTransactions();
    $transactionId = uniqid();
    
    $newTransaction = [
        'id' => $transactionId,
        'date' => date('Y-m-d H:i:s'),
        'invoiceNumber' => sanitizeInput($data['invoiceNumber']),
        'items' => $data['items'],
        'subtotal' => (float)$data['subtotal'],
        'tax' => (float)($data['tax'] ?? 0),
        'discount' => (float)($data['discount'] ?? 0),
        'total' => (float)$data['total'],
        'paymentMethod' => sanitizeInput($data['paymentMethod']),
        'amountTendered' => (float)($data['amountTendered'] ?? 0),
        'change' => (float)($data['change'] ?? 0)
    ];
    
    $transactions[] = $newTransaction;
    
    file_put_contents(__DIR__ . '/data/transactions.json', 
        json_encode($transactions, JSON_PRETTY_PRINT));

    // Update product stock
    updateProductStock($data['items']);

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully',
        'transactionId' => $transactionId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}