<?php
session_start();
require_once "conn.php";

header('Content-Type: application/json'); // Return JSON

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $full_name = trim($_POST['full_name']);
        $course = trim($_POST['course']);
        $year_level = trim($_POST['year_level']);
        $section = trim($_POST['section']);
        $profile_pic = $user['profile_pic']; // Default to existing image

        // Handle image upload if provided
        if (!empty($_FILES['profile_pic']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $file_name = basename($_FILES["profile_pic"]["name"]);
            $target_file = $target_dir . time() . "_" . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg','jpeg','png','gif'];

            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    $profile_pic = $target_file;
                } else {
                    echo json_encode(['error' => 'Error uploading file']);
                    exit;
                }
            } else {
                echo json_encode(['error' => 'Invalid file type']);
                exit;
            }
        }

        // Handle optional password update
        $password = trim($_POST['password'] ?? '');
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users 
                SET full_name = :full_name, course = :course, year_level = :year_level, 
                    section = :section, profile_pic = :profile_pic, password_hash = :password_hash
                WHERE id = :id");
            $update->bindParam(':password_hash', $hashedPassword);
        } else {
            $update = $conn->prepare("UPDATE users 
                SET full_name = :full_name, course = :course, year_level = :year_level, 
                    section = :section, profile_pic = :profile_pic
                WHERE id = :id");
        }

        // Bind remaining parameters
        $update->bindParam(':full_name', $full_name);
        $update->bindParam(':course', $course);
        $update->bindParam(':year_level', $year_level);
        $update->bindParam(':section', $section);
        $update->bindParam(':profile_pic', $profile_pic);
        $update->bindParam(':id', $user_id);

        if ($update->execute()) {
            // Return updated user info (without password)
            echo json_encode([
                'success' => true,
                'user' => [
                    'full_name' => $full_name,
                    'course' => $course,
                    'year_level' => $year_level,
                    'section' => $section,
                    'profile_pic' => $profile_pic
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Error updating profile']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
