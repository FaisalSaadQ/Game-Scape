<?php
require 'db.php';

if (!isset($_GET['variant_id'])) {
    echo json_encode(['error' => 'Variant ID missing']);
    exit;
}

$variant_id = intval($_GET['variant_id']);

$stmt = $conn->prepare("SELECT stock_qty FROM product_variants WHERE variant_id = ?");
$stmt->bind_param("i", $variant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['stock_qty' => $row['stock_qty']]);
} else {
    echo json_encode(['error' => 'Variant not found']);
}