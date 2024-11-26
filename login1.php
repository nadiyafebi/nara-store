<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Aesthetic Lucu</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        body {
            background-image: url('images/111.jpg'); /* Ganti dengan URL gambar yang sesuai */
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }

        /* Card Styling */
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            animation: bounceIn 0.8s ease;
        }

        /* Title & Labels Styling */
        h3 {
            font-weight: bold;
            color: #d9534f;
            margin-bottom: 20px;
        }
        .form-label {
            color: #d9534f;
            font-weight: bold;
        }

        /* Button Styling */
        .btn-primary {
            background-color: #d9534f;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            transition: background 0.3s ease;
            margin-top: 10px;
        }
        .btn-primary:hover {
            background-color: #c53b3a;
        }

        /* Form Control Styling */
        .form-control {
            border-radius: 20px;
            box-shadow: none;
        }

        /* Bounce Animation */
        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body>

    <div class="card mt-4">
        <div class="cute-icon">🌻</div>
        <h3>Selamat Datang!</h3>

        <form method="post" action="ceklogin.php">
            <!-- Username input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control form-control-lg"
                    placeholder="Masukkan username kamu" required />
            </div>

            <!-- Password input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control form-control-lg"
                    placeholder="Masukkan password kamu" required />
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="rememberMe" checked />
                    <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                </div>
                <a href="#!" style="color: #d9534f;">Lupa Password?</a>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary btn-lg w-100">Masuk</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-OPCPHOdFkb0yKJmWW3roZroMR19GCLi+oME4Z4kRg8FFKSh4IOBZBhw5HJ6u8FJT" crossorigin="anonymous"></script>
</body>

</html>
