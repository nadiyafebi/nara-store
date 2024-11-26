<?php
session_start();
// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data kategori dari database
$category_result = $conn->query("SELECT DISTINCT category FROM products");

// Ambil data produk yang dipilih dari parameter URL
$selected_products = isset($_GET['products']) ? explode(',', $_GET['products']) : [];

// Pastikan $selected_products adalah array
if (!is_array($selected_products)) {
    $selected_products = [$selected_products];
}

$cart_items = [];
$total_amount = 0;

if (!empty($selected_products)) {
    // Buat query untuk mengambil produk yang dipilih beserta kuantitasnya
    $ids = implode(',', array_map('intval', $selected_products));
    $query = "SELECT c.id_cart, p.product_id, p.name, p.price, c.quantity 
              FROM cart c 
              JOIN products p ON c.product_id = p.product_id 
              WHERE c.id_cart IN ($ids)";
    
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
            $total_amount += $row['price'] * $row['quantity'];
        }
    } else {
        die("Query Error: " . $conn->error);
    }
}

// Proses form pembayaran
$success_message = $error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];

    // Validasi input sederhana
    if (empty($name) || empty($phone) || empty($address) || empty($payment_method)) {
        $error_message = "Semua kolom harus diisi!";
    } else {
        // Simpan data ke tabel orders
        $stmt = $conn->prepare("INSERT INTO orders (name, phone, address, payment_method, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $name, $phone, $address, $payment_method, $total_amount);
        
        if ($stmt->execute()) {
            $success_message = "Pesanan Anda berhasil diproses!";
        } else {
            $error_message = "Terjadi kesalahan saat memproses pesanan.";
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/111.jpg'); 
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            color: #333;
        }
        .co-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table {
            background-color: rgba(255, 255, 255, 0.9);
        }
        .total-payment {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .btn-checkout {
            margin-top: 20px;
        }
        .header-icons a {
            margin-left: 15px;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            color: #d9534f;
        }
        .navbar-nav .nav-link {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .footer {
            background-color: #343a40;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="#">NARA STORE</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <!-- Ambil kategori dari database -->
                    <?php if ($category_result->num_rows > 0): ?>
                        <?php while ($row = $category_result->fetch_assoc()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?category=<?php echo urlencode($row['category']); ?>">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </ul>
                <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                    <input class="form-control mr-sm-2" type="search" name="query" placeholder="Cari produk..." value="<?php echo isset($query) ? htmlspecialchars($query) : ''; ?>">
                    <button class="btn btn-outline-danger my-2 my-sm-0" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-link">Hai, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="btn btn-danger ml-3">Keluar</a>
                <?php else: ?>
                    <a href="#" class="btn btn-danger ml-3" data-toggle="modal" data-target="#authModal">Masuk</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-dark ml-3"><i class="fas fa-shopping-bag"></i></a>
            </div>
        </div>
    </nav>


    <div class="container my-5 co-container">
        <h1 class="text-center">Checkout</h1>

        <?php if (empty($cart_items)): ?>
            <p class="text-center">Keranjang Anda kosong, tidak ada yang dapat dibeli.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Kuantitas</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4 class="text-right">Total Pembayaran: Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></h4>

            <!-- Form Pembayaran -->
            <form method="POST" class="mt-4">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="phone">No Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="payment_method">Metode Pembayaran</label>
                    <select class="form-control" id="payment_method" name="payment_method" required>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="COD">Cash on Delivery (COD)</option>
                        <option value="Kartu Kredit">Kartu Kredit</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Proses Pembayaran</button>
            </form>
        <?php endif; ?>
    </div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($success_message)): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Pesanan Anda berhasil diproses!',
        text: 'Terima kasih telah berbelanja di NARA STORE.',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php';
        }
    });
</script>
<?php elseif (!empty($error_message)): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '<?php echo $error_message; ?>'
    });
</script>
<?php endif; ?>

</body>
</html>