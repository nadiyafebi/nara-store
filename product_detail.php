<?php
session_start();
// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tangkap ID produk dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


// Ambil data produk berdasarkan ID
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "Produk tidak ditemukan.";
    exit;
}

// Ambil ukuran produk dari tabel product_sizes
$size_stmt = $conn->prepare("SELECT size FROM product_sizes WHERE product_id = ?");
$size_stmt->bind_param('i', $product_id);
$size_stmt->execute();
$size_result = $size_stmt->get_result();
$sizes = [];
while ($size_row = $size_result->fetch_assoc()) {
    $sizes[] = $size_row['size'];
}

// Ambil kategori produk
$category_query = "SELECT DISTINCT category FROM products";
$category_result = $conn->query($category_query);
if (!$category_result) {
    die("Error: " . $conn->error);
}

// Proses menambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $selected_size = isset($_POST['size']) ? $_POST['size'] : '';

    // Cek jika ukuran yang dipilih valid
    if (!empty($selected_size) && in_array($selected_size, $sizes)) {
        // Menambahkan produk ke keranjang
        $cart_stmt = $conn->prepare("INSERT INTO cart (product_id, quantity) VALUES (?, ?)");
        $cart_stmt->bind_param('ii', $product_id, $quantity);
        if ($cart_stmt->execute()) {
            echo "<script>alert('Produk berhasil ditambahkan ke keranjang!');</script>";
        } else {
            echo "<script>alert('Gagal menambahkan produk ke keranjang.');</script>";
        }
    } else {
        echo "<script>alert('Silakan pilih ukuran yang valid.');</script>";
    }
}
?>

<!-- Sisanya adalah kode HTML -->


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Tambahkan latar belakang foto di sini */
        body {
            background-image: url('images/111.jpg'); /* Ganti dengan path gambar Anda */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed; /* Agar gambar tetap saat di-scroll */
            background-position: center;
            color: #333; /* Warna teks jika perlu kontras lebih */
        }
        
        /* Gaya tambahan untuk elemen kontainer produk */
        .product-container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.85); /* Transparansi pada kontainer produk */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="#">NARA STORE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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
                <form class="d-flex" action="search.php" method="GET">
                    <input class="form-control me-2" type="search" name="query" placeholder="Cari produk..." value="<?php echo isset($query) ? htmlspecialchars($query) : ''; ?>">
                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-link ms-3">Hai, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="btn btn-danger ms-3">Keluar</a>
                <?php else: ?>
                    <a href="#" class="btn btn-danger ms-3" data-bs-toggle="modal" data-bs-target="#authModal">Masuk</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-dark ms-3"><i class="fas fa-shopping-bag"></i></a>
            </div>
        </div>
    </nav>

    <!-- Product Detail Container -->
    <div class="container my-5 product-container">
        <div class="row">
            <!-- Gambar Produk -->
            <div class="col-md-6 text-center">
                <img src="images/<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid card-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <!-- Informasi Produk -->
            <div class="col-md-6">
                <h1 class="display-5"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-primary h4">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>

                <!-- Formulir untuk input ukuran dan kuantitas -->
                <form method="POST">
                    <!-- Pilihan Ukuran -->
                    <div class="mt-3">
                        <h5>Ukuran:</h5>
                        <div class="btn-group" role="group" aria-label="Size selection">
                            <?php foreach ($sizes as $size): ?>
                                <button type="button" class="btn btn-outline-secondary size-btn" onclick="selectSize('<?php echo htmlspecialchars($size); ?>')"><?php echo htmlspecialchars($size); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="size" id="selectedSize">
                    </div>

                    <!-- Kuantitas -->
                    <div class="mt-3">
                        <h5>Jumlah:</h5>
                        <div class="input-group mb-3" style="max-width: 120px;">
                            <div class="input-group-prepend">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">-</button>
                            </div>
                            <input type="number" class="form-control text-center" name="quantity" value="1" min="1" id="quantityInput">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Tambah ke Keranjang -->
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg px-4">Tambah ke Keranjang</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deskripsi Produk dengan Collapse -->
        <div class="mt-5">
            <h4>Deskripsi Produk</h4>
            <button class="btn btn-outline-primary btn-collapse collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#productDescription" aria-expanded="false" aria-controls="productDescription">
                Tampilkan Deskripsi <i class="fas fa-chevron-down"></i>
            </button>
            <div class="collapse mt-3" id="productDescription">
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fungsi untuk memilih ukuran
        function selectSize(size) {
            document.getElementById('selectedSize').value = size;
            const buttons = document.querySelectorAll('.size-btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.trim() === size) {
                    btn.classList.add('active');
                }
            });
        }

        // Fungsi untuk mengubah kuantitas
        function changeQuantity(delta) {
            const quantityInput = document.getElementById('quantityInput');
            let currentQuantity = parseInt(quantityInput.value);
            currentQuantity += delta;
            if (currentQuantity < 1) currentQuantity = 1;
            quantityInput.value = currentQuantity;
        }
    </script>
</body>
</html>
