<?php
    session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Game scape stor</title>
    <link rel="stylesheet" href="css/header-footer.css">
    <link rel="stylesheet" href="css/home-style.css">
</head>

<body>

    <?php
    include "includes/header.php"
    ?>


    <div class="ad-paner">
    </div>


    <!-- if there is old Purchases print it  -->
    <?php
    if (isset($_COOKIE['pastPurchases']) && (!empty($_COOKIE['pastPurchases']))) {

        include "includes/db.php";

        $ids = explode(',', $_COOKIE['pastPurchases']);
        $ids = array_reverse($ids);
        $ids = array_slice($ids, 0, 3);
        $ids_str = implode(',', array_map('intval', $ids));

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
                WHERE p.product_id IN ($ids_str)
                ";

        $result = mysqli_query($con, $query);
        mysqli_close($con);
    ?>

        <!-- label for past purchases -->
        <header class="for-brodact">
            <h2>Your Recent Purchases</h2>
        </header>

        <section class="show-some-old-prodect">
            <div class="hold-cards">

                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="prodct-card">';

                    echo '<div class="prodct-photo">';
                    $product_id = $row['product_id'];
                    echo '<a href="details.php?id=' . $product_id . '" class="product-link" onclick="return true;">';
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
                    echo '    <img src="assets/carticon.png" alt="Add to cart">';
                    echo '    <p>Add to cart</p>';
                    echo '  </button>';
                    echo '</form>';

                    echo '</div>';
                }

                ?>

            </div>
        </section>

    <?php } ?>

    <!-- label for new arrivals -->
    <header class="for-brodact">
        <h2>New Arrivals</h2>
    </header>

    <section class="show-some-prodect">



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
                echo '<a href="details.php?id=' . $product_id . '" class="product-link" onclick="return true;">';
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
                echo '  <input type="hidden" name="id" value=' . $product_id . '>';
                echo '  <input type="hidden" name="action" value="add">';
                echo '  <button type="submit" class="cart-butten">';
                echo '    <img src="assets/carticon.png" alt="Add to cart">';
                echo '    <p>Add to cart</p>';
                echo '  </button>';
                echo '</form>';

                echo '</div>';
            }
            ?>

        </div>


    </section>

    <?php
    include "includes/footer.html"
    ?>

</body>

</html>


