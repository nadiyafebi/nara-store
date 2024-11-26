<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ecommerce');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk memverifikasi username dan password
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username);
        $stmt->fetch();

        // Set sesi untuk pengguna yang berhasil login
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $username;

        // Redirect ke halaman utama setelah login sukses
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Username atau password salah. Silakan coba lagi.'); window.location.href = 'login.php';</script>";
    }
    $stmt->close();
}
