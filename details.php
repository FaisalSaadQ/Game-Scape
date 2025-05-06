<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';
include 'includes/db_conn.php';

// if (!isset($conn) || !($conn instanceof PDO)) {
//     die("Database connection failed. Please check db_connection.php");
// }

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

try {
    // Fetch all product data including stock quantity
    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.name AS title,
            p.description,
            p.spec,
            pi.filename AS image,
            v.price AS price_before,
            v.price_after,
            v.stock_qty
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        LEFT JOIN product_variants v ON p.product_id = v.product_id
        WHERE p.product_id = ?
        LIMIT 1
    ");
    
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        die("Product not found");
    }

    // Check stock quantity and update price_after if out of stock
    if (isset($product['stock_qty']) && $product['stock_qty'] <= 0) {
        $product['price_after'] = 'Out of Stock';
    }

    // Decode the JSON specs
    $main_specs = json_decode($product['spec'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $main_specs = []; // Fallback to empty array if JSON is invalid
    }

    // Fetch recommended products with stock check
    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.name AS title,
            p.description,
            pi.filename AS image,
            v.price AS price_before,
            v.price_after,
            v.stock_qty
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        LEFT JOIN product_variants v ON p.product_id = v.product_id
        WHERE p.product_id != ?
        GROUP BY p.product_id
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$product_id]);
    $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update price_after for recommended products if out of stock
    foreach ($recommended_products as &$recommended_product) {
        if (isset($recommended_product['stock_qty']) && $recommended_product['stock_qty'] <= 0) {
            $recommended_product['price_after'] = 'Out of Stock';
        }
    }
    unset($recommended_product); // Break the reference

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

function calculateDiscount($oldPrice, $newPrice) {
    if ($newPrice === 'Out of Stock') return 0;
    
    $old = (float) str_replace([',', ' SAR'], '', $oldPrice);
    $new = (float) str_replace([',', ' SAR'], '', $newPrice);
    
    if ($old <= 0 || $new >= $old) return 0;
    
    return round((($old - $new) / $old) * 100);
}
?>
<!-- update -->

<link rel="stylesheet" href="css/details.css">
<link rel="stylesheet" href="css/home-style.css">
<link rel="stylesheet" href="css/header-footer.css" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<?php include 'includes/header.php'; ?>

<section class="details">
    <div class="details--container">
        <div class="details--group">
            <img src="assets/prodects/<?php echo htmlspecialchars($product['image']); ?>" alt="Product image" class="details--img">
        </div>
        <div class="details--group">
            <h3 class="details--title"><?php echo htmlspecialchars($product['title']); ?></h3>
            <div class="details--price flex">
    <?php if (strtolower(trim($product['price_after'])) === 'out of stock'): ?>
        <span class="new--price out-of-stock">Out of Stock</span>
        <span class="old--price"><?php echo htmlspecialchars($product['price_before']); ?> </span>
    <?php elseif ($product['price_after'] === null || $product['price_after'] === $product['price_before']): ?>
        <!-- Show only original price when no discount -->
        <span class="new--price"><?php echo htmlspecialchars($product['price_before']); ?> </span>
    <?php else: ?>
        <!-- Show discounted price -->
        <span class="new--price"><?php echo htmlspecialchars($product['price_after']); ?> </span>
        <span class="old--price"><?php echo htmlspecialchars($product['price_before']); ?> </span>
        <?php 
            $discount = calculateDiscount($product['price_before'], $product['price_after']);
            if ($discount > 0): 
                // Store discount in discontinued_at column
                try {
                    $updateStmt = $conn->prepare("
                        UPDATE products 
                        SET discontinued_at = ?
                        WHERE product_id = ?
                    ");
                    // Store discount as string with % sign
                    $updateStmt->execute([$discount.'%', $product['product_id']]);
                } catch (PDOException $e) {
                    error_log("Failed to update discount: " . $e->getMessage());
                } 
        ?>
            <span class="save--price"><?php echo $discount; ?>% Off</span>
        <?php endif; ?>
    <?php endif; ?>
</div>


            <p class="short--description">
            <?php echo htmlspecialchars($product['description'] ?? 'Description not available'); ?>
            </p>

            <ul class="product--list">

   
                <li class="list--item flex">
                    <span class="material-symbols-outlined">crown</span>
                    1 Year AL Jazeera Brand Warranty
                </li>
                <li class="list--item flex">
                    <span class="material-symbols-outlined">sync</span>
                    30 Day Return Policy
                </li>
                <li class="list--item flex">
                    <span class="material-symbols-outlined">credit_card</span>
                    Cash on Delivery available
                </li>
    
             </ul>

        


  <div class="product-action-row">
    
    <input type="number" class="quantity" value="1" min="1" id="quantityInput">

    <form method="GET" action="add_to_cart.php" id="addToCartForm" style="display: contents;">
  <input type="hidden" name="id" value="<?php echo $product_id; ?>">
  <input type="hidden" name="action" value="add">
  <button type="submit" class="cart-butten-1">
    <img src="assets/carticon.png" alt="Add to cart">
    <span>Add to cart</span>
  </button>
</form>



    <button class="cart-butten-1" id="openModel">Help</button>

    <div class="model" id="model">
      <div class="model-inner">
        <h2 class="help-title">How to Buy This Product</h2>
        <div class="help-description">
          <p>
            1. Enter the quantity you want to purchase.<br>
            2. Click the "Add to Cart" button — this will move your selected product to the shopping cart.<br>
            3. Go to the Cart page to review your items.<br>
            4. Click "Checkout" to complete your purchase by providing shipping and payment details.
          </p>
        </div>
        <button class="closeBtn" id="closedModel">Close Help</button>
      </div>
    </div>

  </div>

       
           

    
    
   

</section>

<div class="container">
    <h2 class="details--title">Description Table</h2>
    <table class="specs-table">
        <?php if (!empty($main_specs)): ?>
            <?php foreach ($main_specs as $spec): ?>
                <tr>
                    <th><?= htmlspecialchars(strtoupper($spec['spec_key'])) ?></th>
                    <td><?= htmlspecialchars($spec['spec_value']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">No specifications available for this product</td>
            </tr>
        <?php endif; ?>
    </table>
   
    </div>
   

<section class="section section-products">
    <div class="container">
        <div class="section-header">
            <h2 class="section--title">Products You May Like</h2>
        </div>
        
        <section class="show-some-prodect-details">
            


            <div class="hold-cards">
                <?php
                    include "includes/db.php";

                    // Get latest 3 products order by created_at
                    $query = "
                    SELECT 
                        p.product_id,
                        p.name,
                        p.description,
                        v.price,
                        v.stock_qty,
                        i.filename AS Image_Path,
                        i.alt_text
                    FROM products p
                    JOIN product_variants v ON p.product_id = v.product_id
                    LEFT JOIN product_images i ON p.product_id = i.product_id AND i.position = 0
                    ORDER BY p.created_at DESC
                    LIMIT 3
                    ";

                    $result = mysqli_query($con, $query);
                    mysqli_close($con);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="prodct-card">';
                        // Check if the product is in stock
                        if ($row['stock_qty'] <= 0) {
                            echo '<div class="out-of-stock">Out of Stock</div>';
                        }
                        
                        echo '<div class="prodct-photo">';
                        $product_id = $row['product_id'];
                        echo '<a href="details.php?id='.$product_id.'" class="product-link" onclick="return true;">';
                        echo '<img class="background" src="assets/b-prodct.png" alt="Background">';
                        echo '<img class="prodect" src="assets/prodects/' . htmlspecialchars(trim($row['Image_Path'])) . '" alt="' . htmlspecialchars($row['alt_text']) . '">';
                        echo '</div>';

                        echo '<hr class="green-line">';
                        echo '<h6 class="prodect-name">' . htmlspecialchars($row['name']) . '</h6>';

                        $description = $row['description'];
                        $shortText = mb_strlen($description) > 55 ? mb_substr($description, 0, 55) . "..." : $description;
                        echo '<p class="prodct-detiles">' . htmlspecialchars($shortText) . '</p>';

                        echo '<div class="stock">';
                        echo '<p>' . ($row['stock_qty'] > 0 ? 'In Stock' : 'Out of Stock') . '</p>';
                        echo '</div>';

                        echo '<hr class="white-line">';

                        echo '<div class="prodict-price">';
                        echo '<img class="R-symbil" src="assets/RS.png" alt="SAR">';
                        echo '<p class="price">' . number_format($row['price'], 2) . '</p>';
                        echo '</div>';
                        echo '</a>';
                        echo '<p class="pay-detiles">(VAT inclusive)</p>';

                        
                        echo '<form method="GET" action="add_to_cart.php">';
                        echo '  <input type="hidden" name="id" value="' . $product_id . '">';
                        echo '  <input type="hidden" name="action" value="add">';
                        echo '  <button type="submit" class="cart-butten">';
                        echo '    <img src="assets/carticon.png" alt="Cart">';
                        echo '    <p>Add to cart</p>';
                        echo '  </button>';
                        echo '</form>';

                        echo '</div>';
                        
                    }
                ?>

            </div>


        </section>
        
    </div>
</section>

<?php include 'includes/footer.html'; ?>




<script >
   document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart, #addToCartBtn, .cart-butten, .cart-butten-1').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation();
            
            // Get product ID
            const productId = button.dataset.productId || 
                             button.closest('[data-product-id]')?.dataset.productId;
            
            if (!productId) {
                console.error("No product ID found");
                return;
            }

            // Determine which quantity to use based on button class
            let quantity;
            if (button.classList.contains('cart-butten-1')) {
                // For the main product button - use input value
                const quantityInput = document.getElementById('quantityInput');
                quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
            } else {
                // For all other buttons - always use quantity 1
                quantity = 1;
            }

            console.log(`Adding product ${productId}, quantity ${quantity}`);
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message || "Product added to cart");
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error adding to cart");
            });
        });
    });
});

const openBtn = document.getElementById("openModel");
const closeBtn = document.getElementById("closedModel");
const model = document.getElementById("model");

openBtn.addEventListener("click",()=>{
    model.classList.add("open");
})

closeBtn.addEventListener("click",()=>{
    model.classList.remove("open");
})
function closeDiscount() {
  document.getElementById("discountCard").style.display = "none";
}
window.onload = function() {
  document.getElementById("discountCard").style.display = "flex";
};

</script>