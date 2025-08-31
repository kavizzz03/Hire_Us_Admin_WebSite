<?php
$conn = new mysqli("localhost", "u569550465_math_rakusa", "Sithija2025#", "u569550465_hireme");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
$message = $_GET['msg'] ?? '';
$alertClass = $_GET['status'] === 'success' ? 'alert-success' : 'alert-danger';

$row = $conn->query("SELECT * FROM worker_ratings WHERE id = $id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Worker Rating</title>
     <link rel="icon" type="image/png" href="icon2.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@500;600&display=swap" rel="stylesheet">

    <!-- Custom Style -->
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Urbanist', sans-serif;
        }
        .letterhead {
            background: #343a40;
            color: white;
            padding: 20px 30px;
            border-radius: 0 0 12px 12px;
            text-align: center;
        }
        .letterhead h2 {
            margin: 0;
            font-weight: 600;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            margin-top: 40px;
        }
        .btn-primary {
            background-color: #1e88e5;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1565c0;
        }
     footer.footer {
    margin-top: 50px;
    padding: 15px 0;
    background: #fff;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.05);
    font-size: 0.9rem;
    color: #6c757d;
    text-align: center;
    border-radius: 0 0 12px 12px;
  }
    </style>
</head>
<body>

    <div class="letterhead">
        <h2>Hire Me Platform - Worker Rating Editor</h2>
        <p class="mb-0">Improving Trust & Transparency</p>
    </div>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert <?= $alertClass ?> mt-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h4 class="mb-4 text-primary">Update Rating Details</h4>
            <form action="update_rating.php" method="post">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Rated By</label>
                    <input type="text" name="rated_by" class="form-control" value="<?= htmlspecialchars($row['rated_by']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating (1 to 5)</label>
                    <input type="number" name="rating" min="1" max="5" class="form-control" value="<?= $row['rating'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Feedback</label>
                    <textarea name="feedback" rows="4" class="form-control" required><?= htmlspecialchars($row['feedback']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4">Update Rating</button>
            </form>
        </div>
    </div>

    <footer class="footer">
  &copy; <?= date('Y') ?> Hire Us System. All rights reserved.
</footer>

</body>
</html>
