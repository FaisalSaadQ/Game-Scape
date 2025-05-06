<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Prodect page</title>
        <link rel="stylesheet" href="css/header-footer.css">
        <link rel="stylesheet" href="css/prodectpage.css">
    </head>

    <body>

        <?php
            include "includes/header.php"
        ?>

        <?php
            // connect to the database
            include "includes/db.php";

            // get category type from query string
            $category = isset($_GET["category"]) ? $_GET["category"] : "";

            // query
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
            JOIN categories c ON p.category_id = c.category_id
            JOIN product_variants v ON p.product_id = v.product_id
            LEFT JOIN product_images i ON p.product_id = i.product_id AND i.position = 0
            WHERE c.name = '$category'
            ";

            // execute the query
            $result = mysqli_query($con, $query);
            mysqli_close($con);

            echo '<div class="hold-cards">';

            // fetch and display each product
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="prodct-card">';
                $product_id = $row['product_id'];
                echo '<a href="details.php?id='.$product_id.'" class="product-link" onclick="return true;">';
                echo '<div class="prodct-photo">';
                echo '<img class="background" src="assets/b-prodct.png" alt="Background">';
                echo '<img class="prodect" src="assets/prodects/' . htmlspecialchars(trim($row['Image_Path'])) . '" alt="' . htmlspecialchars($row['alt_text']) . '">';
                echo '</div>';

                echo '<hr class="green-line">';
                echo '<h6 class="prodect-name">' . htmlspecialchars($row['name']) . '</h6>';

                // shorten description if long
                $description = $row['description'];
                $shortText = mb_strlen($description) > 55 ? mb_substr($description, 0, 55) . "..." : $description;
                echo '<p class="prodct-detiles">' . htmlspecialchars($shortText) . '</p>';

                echo '<div class="stock">';
                echo '<p>' . ($row['stock_qty'] > 0 ? 'In Stock' : 'Out of Stock') . '</p>';
                echo '</div>';

                echo '<hr class="white-line">';

                echo '<div class="prodict-price">';
                echo '<img class="R-symbil" src="assets/RS.png" alt="Riyal Symbol">';
                echo '<p class="price">' . number_format($row['price'], 2) . '</p>';
                echo '</div>';

                echo '<p class="pay-detiles">(VAT inclusive)</p>';
                echo '</a>';
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

            echo '</div>'; 
            
        ?>

        



        <?php
            include "includes/footer.html"
        ?>






    </body>
</html>