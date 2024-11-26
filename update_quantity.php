<?php
// Menampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Periksa apakah id_cart dan kuantitas ada
if (isset($_POST['id_cart'], $_POST['quantity'])) {
    $id_cart = (int)$_POST['id_cart'];
    $quantity = (int)$_POST['quantity'];

    // Update kuantitas di database
    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id_cart = ?");
        $stmt->bind_param('ii', $quantity, $id_cart);
    } else {
        // Hapus item jika kuantitas <= 0
        $stmt = $conn->prepare("DELETE FROM cart WHERE id_cart = ?");
        $stmt->bind_param('i', $id_cart);
    }

    $stmt->execute();
    $stmt->close();
}
$conn->close();
