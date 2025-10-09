<?php
session_start();
include 'config.php';

// Only logged in users can add PG
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $city = $_POST['city'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $amenities = $_POST['amenities'];
    $image = $_POST['image']; // for simplicity, just URL
    $created_by = $_SESSION['username'];

    $sql = "INSERT INTO pgs (title, city, price, type, amenities, image, created_by) 
            VALUES ('$title', '$city', '$price', '$type', '$amenities', '$image', '$created_by')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../index.php?pg=success");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add PG</title>
  <link rel="stylesheet" href="../auth.css">
</head>
<body>
  <div class="auth-card">
    <h2>Add Your PG</h2>
    <form method="post">
        <label>Title</label>
        <input type="text" name="title" required>

        <label>City</label>
        <input type="text" name="city" required>

        <label>Price</label>
        <input type="number" name="price" required>

        <label>Type</label>
        <select name="type" required>
            <option value="single">Single</option>
            <option value="sharing">Sharing</option>
        </select>

        <label>Amenities (comma separated)</label>
        <input type="text" name="amenities" placeholder="wifi,ac,attached">

        <label>Image URL</label>
        <input type="text" name="image" placeholder="https://example.com/img.jpg">

        <button type="submit">Add PG</button>
    </form>
  </div>
</body>
</html>
