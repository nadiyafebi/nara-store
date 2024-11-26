<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ecommerce');
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$category = isset($_GET['category']) ? $_GET['category'] : '';
$size = isset($_GET['size']) ? $_GET['size'] : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Ambil semua kategori unik dari tabel products
$category_query = "SELECT DISTINCT category FROM products";
$category_result = $conn->query($category_query);

// Query untuk produk
$sql = "SELECT p.*, GROUP_CONCAT(ps.size) AS sizes 
        FROM products p 
        LEFT JOIN product_sizes ps ON p.product_id = ps.product_id 
        WHERE 1=1";

$params = [];
$types = '';

if ($query) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
    $types .= 'ss';
}

if ($category) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
    $types .= 's';
}

$sql .= " GROUP BY p.product_id";
switch ($sort) {
    case 'new':
        $sql .= " ORDER BY p.product_id DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    default:
        $sql .= " ORDER BY p.product_id DESC";
        break;
}

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NARA STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f7f7f7;
            color: #333;
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
                <form class="d-flex" action="" method="GET">
                    <input class="form-control me-2" type="search" name="query" placeholder="Cari produk..." value="<?php echo htmlspecialchars($query); ?>">
                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-link ms-3">Hai, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="btn btn-danger ms-3">Keluar</a>
                <?php else: ?>
                    <a href="login1.php" class="btn btn-danger ms-3">Masuk</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-dark ms-3"><i class="fas fa-shopping-bag"></i></a>
            </div>
        </div>
    </nav>

    <!-- Produk -->
    <div class="container my-5">
        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <img src="images/<?php echo $row['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></p>
                                <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-danger">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Tidak ada produk yang ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; 2024 NARA STORE. All rights reserved.
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.querySelectorAll('.category-radio').forEach(radio => {
            radio.addEventListener('change', () => {
                const selectedCategory = radio.value;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('category', selectedCategory);
                window.location.href = currentUrl.toString();
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
