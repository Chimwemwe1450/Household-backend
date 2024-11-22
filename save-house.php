<?php
require 'db.php'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Validate incoming data
if (isset($data['address']) && isset($data['description']) && isset($data['geolocation'])) {
    $address = $data['address'];
    $description = $data['description'];
    $latitude = $data['geolocation']['lat'];
    $longitude = $data['geolocation']['lng'];

    // Insert the house data
    $sql = "INSERT INTO houses (address, description, latitude, longitude) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$address, $description, $latitude, $longitude]);

    // Get the last inserted house ID
    $houseId = $pdo->lastInsertId();

    // Insert damage images
    if (isset($data['images'])) {
        foreach ($data['images'] as $image) {
            $imageUrl = $image['url'];  
            $imageDescription = $image['description'];

            $sql = "INSERT INTO damage_images (house_id, image_url, description) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$houseId, $imageUrl, $imageDescription]);
        }
    }

    echo json_encode(["message" => "House and images added successfully"]);
} else {
    echo json_encode(["error" => "Invalid data"]);
}
?>
