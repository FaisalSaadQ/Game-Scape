<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    include "db.php";

    $sql = "SELECT product_id, name FROM products";
    $result = mysqli_query($con , $sql);

    $products = [];

    if (mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $products[] = [
                'id' => $row['product_id'],
                'name' => $row['name']
            ];
        }
    }

    mysqli_close($con);

    echo json_encode($products);
?>