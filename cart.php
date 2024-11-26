<?php
session_start();
// Menampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data kategori dari database
$category_result = $conn->query("SELECT DISTINCT category FROM products");

if (!$category_result) {
    die("Query Error: " . $conn->error);
}

// Ambil data keranjang
$cart_items = [];
$cart_stmt = $conn->query("SELECT c.id_cart, p.product_id, p.name, p.price, p.image_url, c.quantity FROM cart c JOIN products p ON c.product_id = p.product_id");

if ($cart_stmt) {
    while ($row = $cart_stmt->fetch_assoc()) {
        $cart_items[] = $row;
    }
} else {
    die("Query Error: " . $conn->error);
}

// Hapus item dari keranjang jika diminta
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id_cart = ?");
    $delete_stmt->bind_param('i', $remove_id);
    $delete_stmt->execute();
    header("Location: cart.php");
    exit;
}

// Proses checkout jika ada produk yang dipilih
if (isset($_POST['checkout'])) {
    $selected_products = $_POST['selected_products'] ?? [];
    header("Location: checkout.php?products=" . implode(',', $selected_products));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Tambahkan latar belakang gambar di sini */
        body {
            background-image: url('images/111.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            color: #333;
        }

        /* Gaya untuk kontainer tabel */
        .cart-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .cart-img {
            width: 100px;
            height: auto;
        }
        .quantity-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        .btn-secondary {
            min-width: 30px;
            height: 38px;
        }
        .form-control {
            padding: 5px;
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


    <div class="container my-5 cart-container">
        <h1 class="text-center">Keranjang Belanja</h1>
        <?php if (empty($cart_items)): ?>
            <p class="text-center">Keranjang Anda kosong.</p>
        <?php else: ?>
            <form method="post">
                <table class="table table-bordered">
                    <thead>
                        <tr class="text-center">
                            <th>Pilih</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Kuantitas</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr data-id="<?php echo $item['id_cart']; ?>">
                                <td class="text-center">
                                    <input type="checkbox" name="selected_products[]" value="<?php echo $item['id_cart']; ?>">
                                </td>
                                <td class="text-center">
                                    <img src="images/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-img">
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-center product-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <div class="quantity-container">
                                        <input type="hidden" name="id_cart" value="<?php echo $item['id_cart']; ?>">
                                        <button type="button" class="btn btn-secondary mx-1" onclick="this.nextElementSibling.stepDown(); updateQuantity(this.nextElementSibling)">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="form-control quantity-input mx-1" onchange="updateQuantity(this)" />
                                        <button type="button" class="btn btn-secondary mx-1" onclick="this.previousElementSibling.stepUp(); updateQuantity(this.previousElementSibling)">+</button>
                                    </div>
                                </td>
                                <td class="text-center total-price">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <a href="?remove=<?php echo $item['id_cart']; ?>" class="btn btn-danger">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="text-center mt-4">
                    <button type="submit" name="checkout" class="btn btn-primary">Checkout</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function updateQuantity(input) {
            const row = input.closest('tr');
            const id_cart = row.getAttribute('data-id');
            const quantity = input.value;

            if (quantity >= 0) {
                $.ajax({
                    url: 'update_quantity.php',
                    method: 'POST',
                    data: {
                        id_cart: id_cart,
                        quantity: quantity
                    },
                    success: function() {
                        updateTotalPrice(input);
                    }
                });
            }
        }

        function updateTotalPrice(input) {
            const row = input.closest('tr');
            const price = parseFloat(row.querySelector('.product-price').innerText.replace('Rp ', '').replace('.', '').replace(',', '.'));
            const quantity = parseInt(input.value);
            const totalPrice = row.querySelector('.total-price');
            
            if (quantity > 0) {
                totalPrice.innerText = 'Rp ' + (price * quantity).toLocaleString('id-ID');
            } else {
                totalPrice.innerText = 'Rp 0';
            }
        }
    </script>
</body>
</html>
