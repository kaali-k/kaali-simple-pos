<?php
// index.php
require_once 'includes/header.php';
require_once 'includes/functions.php';

$products = getProducts();
?>

<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 sl-primary-text">Point of Sale</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Selection -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Search Product</label>
                        <input type="text" id="searchProduct" 
                               class="w-full px-3 py-2 border rounded-md" 
                               placeholder="Start typing to search...">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Select Product</label>
                            <select id="productSelect" class="w-full px-3 py-2 border rounded-md">
                                <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" 
                                        data-price="<?= $product['price'] ?>"
                                        data-stock="<?= $product['quantity'] ?>">
                                    <?= $product['name'] ?> (<?= formatCurrency($product['price']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Category Filter</label>
                            <select id="categoryFilter" class="w-full px-3 py-2 border rounded-md">
                                <option value="">All Categories</option>
                                <?php 
                                $categories = array_unique(array_column($products, 'category'));
                                foreach ($categories as $category): 
                                ?>
                                <option value="<?= $category ?>"><?= $category ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Quantity</label>
                            <input type="number" id="quantity" value="1" min="1" 
                                   class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Stock Available</label>
                            <div id="stockDisplay" class="px-3 py-2 bg-gray-50 rounded-md">
                                <?= $products[0]['quantity'] ?? 0 ?>
                            </div>
                        </div>
                    </div>

                    <button onclick="addToCart()"
                            class="w-full sl-primary text-white px-4 py-2 rounded-md hover:bg-green-700">
                        <i class="fas fa-plus-circle mr-1"></i> Add to Transaction
                    </button>
                </div>
            </div>
            
            <!-- Product Quick Select Grid -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium mb-4">Quick Select Products</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="productGrid">
                    <?php foreach (array_slice($products, 0, 8) as $product): ?>
                    <div class="border rounded-lg p-3 text-center hover:bg-gray-50 cursor-pointer product-card"
                         data-id="<?= $product['id'] ?>"
                         data-name="<?= $product['name'] ?>"
                         data-price="<?= $product['price'] ?>"
                         data-stock="<?= $product['quantity'] ?>">
                        <div class="font-medium truncate"><?= $product['name'] ?></div>
                        <div class="text-sm text-gray-500"><?= $product['category'] ?></div>
                        <div class="mt-2 font-bold sl-primary-text"><?= formatCurrency($product['price']) ?></div>
                        <div class="mt-1 text-xs">Stock: <?= $product['quantity'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Transaction Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="space-y-4">
                <h2 class="text-xl font-bold mb-4">Current Transaction</h2>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Invoice #:</span>
                    <span id="invoiceNumber" class="text-sm"><?= generateInvoiceNumber() ?></span>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <table class="w-full mb-4" id="cartTable">
                        <thead>
                            <tr class="text-sm font-medium">
                                <th class="pb-2 text-left">Product</th>
                                <th class="pb-2 text-right">Price</th>
                                <th class="pb-2 text-right">Qty</th>
                                <th class="pb-2 text-right">Total</th>
                                <th class="pb-2 text-right"></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <!-- Cart items will be inserted here -->
                        </tbody>
                    </table>
                    
                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Subtotal:</span>
                            <span id="subtotal">Rs. 0.00</span>
                        </div>
                        
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Tax (8%):</span>
                            <span id="tax">Rs. 0.00</span>
                        </div>
                        
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex-1 mr-2">
                                <label class="block text-sm font-medium mb-1">Discount</label>
                                <input type="number" id="discount" step="0.01" min="0" 
                                       class="w-full px-2 py-1 border rounded-md">
                            </div>
                            <div>
                                <span class="font-medium">Total:</span>
                                <span id="totalAmount" class="text-xl font-bold">Rs. 0.00</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Payment Method</label>
                            <select id="paymentMethod" class="w-full px-3 py-2 border rounded-md">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="QR">QR Payment</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Amount Tendered</label>
                                <input type="number" id="amountTendered" step="0.01" min="0" 
                                       class="w-full px-2 py-1 border rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Change</label>
                                <div id="changeAmount" class="px-3 py-1 bg-gray-100 rounded-md font-bold">
                                    Rs. 0.00
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="clearCart()"
                                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                                <i class="fas fa-trash mr-1"></i> Clear
                            </button>
                            <button onclick="completeSale()"
                                    class="sl-primary text-white px-4 py-2 rounded-md hover:bg-green-700">
                                <i class="fas fa-check-circle mr-1"></i> Complete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
const TAX_RATE = 0.08; // 8% tax rate

// Product search functionality
document.getElementById('searchProduct').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const options = document.querySelectorAll('#productSelect option');
    
    options.forEach(option => {
        const text = option.textContent.toLowerCase();
        option.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
    
    // Also filter the product grid
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        const productName = card.dataset.name.toLowerCase();
        card.style.display = productName.includes(searchTerm) ? 'block' : 'none';
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const category = this.value.toLowerCase();
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        if (!category || card.querySelector('.text-gray-500').textContent.toLowerCase() === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Update stock display when product selection changes
document.getElementById('productSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('stockDisplay').textContent = selectedOption.dataset.stock;
});

// Quick select product cards
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function() {
        const productId = this.dataset.id;
        const productName = this.dataset.name;
        const productPrice = parseFloat(this.dataset.price);
        const productStock = parseInt(this.dataset.stock);
        
        if (productStock > 0) {
            addProductToCart(productId, productName, productPrice, 1);
        } else {
            alert('This product is out of stock!');
        }
    });
});

// Calculate change when amount tendered changes
document.getElementById('amountTendered').addEventListener('input', function() {
    const amountTendered = parseFloat(this.value) || 0;
    const total = parseFloat(document.getElementById('totalAmount').textContent.replace('Rs. ', '')) || 0;
    const change = Math.max(amountTendered - total, 0);
    
    document.getElementById('changeAmount').textContent = 'Rs. ' + change.toFixed(2);
});

function addToCart() {
    const productSelect = document.getElementById('productSelect');
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const quantity = parseInt(document.getElementById('quantity').value);
    const stock = parseInt(selectedOption.dataset.stock);

    if (quantity > stock) {
        alert('Not enough stock available!');
        return;
    }

    addProductToCart(
        selectedOption.value,
        selectedOption.text.split(' (')[0],
        parseFloat(selectedOption.dataset.price),
        quantity
    );
}

function addProductToCart(id, name, price, quantity) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: quantity
        });
    }

    updateCartDisplay();
}

function removeCartItem(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function clearCart() {
    if (confirm('Are you sure you want to clear the current transaction?')) {
        cart = [];
        updateCartDisplay();
        document.getElementById('discount').value = '';
        document.getElementById('amountTendered').value = '';
        document.getElementById('changeAmount').textContent = 'Rs. 0.00';
    }
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const subtotalElement = document.getElementById('subtotal');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('totalAmount');
    
    cartItems.innerHTML = '';
    let subtotal = 0;

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        const row = document.createElement('tr');
        row.className = 'text-sm border-t';
        row.innerHTML = `
            <td class="py-2">${item.name}</td>
            <td class="py-2 text-right">Rs. ${item.price.toFixed(2)}</td>
            <td class="py-2 text-right">${item.quantity}</td>
            <td class="py-2 text-right">Rs. ${itemTotal.toFixed(2)}</td>
            <td class="py-2 text-right">
                <button onclick="removeCartItem(${index})" 
                        class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        cartItems.appendChild(row);
    });

    const tax = subtotal * TAX_RATE;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const total = Math.max(subtotal + tax - discount, 0);

    subtotalElement.textContent = `Rs. ${subtotal.toFixed(2)}`;
    taxElement.textContent = `Rs. ${tax.toFixed(2)}`;
    totalElement.textContent = `Rs. ${total.toFixed(2)}`;
    
    // Update amount tendered and change if needed
    const amountTendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    if (amountTendered > 0) {
        const change = Math.max(amountTendered - total, 0);
        document.getElementById('changeAmount').textContent = 'Rs. ' + change.toFixed(2);
    }
}

async function completeSale() {
    if (cart.length === 0) {
        alert('Please add items to the transaction first!');
        return;
    }
    
    const total = parseFloat(document.getElementById('totalAmount').textContent.replace('Rs. ', ''));
    const amountTendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    
    if (amountTendered < total && document.getElementById('paymentMethod').value === 'Cash') {
        alert('Amount tendered must be at least equal to the total amount for cash payments!');
        return;
    }

    try {
        const response = await fetch('save_transaction.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                items: cart,
                subtotal: parseFloat(document.getElementById('subtotal').textContent.replace('Rs. ', '')),
                tax: parseFloat(document.getElementById('tax').textContent.replace('Rs. ', '')),
                discount: parseFloat(document.getElementById('discount').value) || 0,
                total: total,
                paymentMethod: document.getElementById('paymentMethod').value,
                amountTendered: amountTendered,
                change: parseFloat(document.getElementById('changeAmount').textContent.replace('Rs. ', '')),
                invoiceNumber: document.getElementById('invoiceNumber').textContent
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert('Transaction completed successfully!');
            
            // Print receipt option
            if (confirm('Do you want to print the receipt?')) {
                window.open(`print_receipt.php?id=${result.transactionId}`, '_blank');
            }
            
            // Clear the cart and reset the form
            cart = [];
            updateCartDisplay();
            document.getElementById('discount').value = '';
            document.getElementById('amountTendered').value = '';
            document.getElementById('changeAmount').textContent = 'Rs. 0.00';
            document.getElementById('invoiceNumber').textContent = generateInvoiceNumber();
            
            // Refresh product stock display
            document.getElementById('productSelect').dispatchEvent(new Event('change'));
        } else {
            throw new Error(result.message || 'Transaction failed');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
    }
}

// Initialize
document.getElementById('discount').addEventListener('input', updateCartDisplay);
document.getElementById('productSelect').dispatchEvent(new Event('change'));
</script>
<?php require_once 'includes/footer.php'; ?>