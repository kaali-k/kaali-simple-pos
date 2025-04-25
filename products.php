<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $products = getProducts();
    
    $new_product = [
        'id' => uniqid(),
        'name' => sanitizeInput($_POST['name']),
        'price' => (float)$_POST['price'],
        'quantity' => (int)($_POST['quantity'] ?? 0),
        'category' => sanitizeInput($_POST['category'] ?? 'Uncategorized'),
        'barcode' => sanitizeInput($_POST['barcode'] ?? ''),
        'description' => sanitizeInput($_POST['description'] ?? '')
    ];
    
    $products[] = $new_product;
    saveProducts($products);
    
    // Redirect to prevent form resubmission
    header('Location: products.php?success=1');
    exit;
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $products = array_filter(getProducts(), 
        fn($p) => $p['id'] !== $_GET['delete']);
    saveProducts($products);
    
    // Redirect to prevent repeated deletions
    header('Location: products.php?deleted=1');
    exit;
}

// Handle product update
if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $products = getProducts();
    $editId = $_GET['edit'];
    
    foreach ($products as &$product) {
        if ($product['id'] === $editId) {
            $product['name'] = sanitizeInput($_POST['name']);
            $product['price'] = (float)$_POST['price'];
            $product['quantity'] = (int)$_POST['quantity'];
            $product['category'] = sanitizeInput($_POST['category']);
            $product['barcode'] = sanitizeInput($_POST['barcode'] ?? '');
            $product['description'] = sanitizeInput($_POST['description'] ?? '');
            break;
        }
    }
    
    saveProducts($products);
    
    // Redirect to prevent form resubmission
    header('Location: products.php?updated=1');
    exit;
}

$products = getProducts();

// Get product to edit if in edit mode
$editProduct = null;
if (isset($_GET['edit'])) {
    foreach ($products as $product) {
        if ($product['id'] === $_GET['edit']) {
            $editProduct = $product;
            break;
        }
    }
}

// Get all unique categories
$categories = array_unique(array_column($products, 'category'));
sort($categories);
?>

<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold sl-primary-text">Product Management</h1>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            Product added successfully!
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
            Product deleted successfully!
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['updated'])): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-2 rounded">
            Product updated successfully!
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Add/Edit Product Form -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium mb-4">
                    <?= $editProduct ? 'Edit Product' : 'Add New Product' ?>
                </h2>
                
                <form method="POST" action="<?= $editProduct ? "?edit={$editProduct['id']}" : '' ?>" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Product Name</label>
                        <input type="text" name="name" required 
                               value="<?= $editProduct['name'] ?? '' ?>"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Price (Rs.)</label>
                        <input type="number" step="0.01" name="price" required 
                               value="<?= $editProduct['price'] ?? '' ?>"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input type="number" name="quantity" 
                               value="<?= $editProduct['quantity'] ?? '0' ?>"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <input type="text" name="category" list="categories"
                               value="<?= $editProduct['category'] ?? '' ?>"
                               class="w-full px-3 py-2 border rounded-md">
                        <datalist id="categories">
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Barcode (Optional)</label>
                        <input type="text" name="barcode" 
                               value="<?= $editProduct['barcode'] ?? '' ?>"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border rounded-md"><?= $editProduct['description'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="pt-2">
                        <?php if ($editProduct): ?>
                            <div class="flex space-x-2">
                                <button type="submit" 
                                        class="flex-1 sl-primary text-white px-4 py-2 rounded-md hover:bg-green-700">
                                    <i class="fas fa-save mr-1"></i> Update Product
                                </button>
                                <a href="products.php" 
                                   class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-center">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                            </div>
                        <?php else: ?>
                            <button type="submit" 
                                    class="w-full sl-primary text-white px-4 py-2 rounded-md hover:bg-green-700">
                                <i class="fas fa-plus-circle mr-1"></i> Add Product
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Product List -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium">Product Inventory</h2>
                        <div class="relative">
                            <input type="text" id="searchInventory" placeholder="Search products..." 
                                   class="px-3 py-2 border rounded-md pl-8">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Price</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Quantity</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" id="productTableBody">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        No products found. Add your first product using the form.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php foreach ($products as $product): ?>
                            <tr class="product-row">
                                <td class="px-4 py-3">
                                    <div class="font-medium"><?= $product['name'] ?></div>
                                    <?php if (!empty($product['barcode'])): ?>
                                        <div class="text-xs text-gray-500">Barcode: <?= $product['barcode'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3"><?= formatCurrency($product['price']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="<?= $product['quantity'] <= 5 ? 'text-red-600 font-bold' : '' ?>">
                                        <?= $product['quantity'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?= $product['category'] ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <a href="?edit=<?= $product['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= $product['id'] ?>" 
                                           class="text-red-600 hover:text-red-800"
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality for product inventory
document.getElementById('searchInventory').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? 'table-row' : 'none';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>