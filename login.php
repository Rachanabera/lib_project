<?php
session_start();

// Debug helper (optional - for dev only)
function debug($data) {
    echo "<pre>"; print_r($data); echo "</pre>";
}

// Database connection
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];
    $user_type = $_POST['user_type'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // debug($user); // optional for dev
        
        if (password_verify($password_input, $user['password'])) {
            // ✅ Store session variables
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['roll_number'] = $user['roll_number'];

            // ✅ Redirect based on role
            if (strtolower($user_type) === 'student') {
                header("Location: student.php");
                exit();
            } elseif (strtolower($user_type) === 'admin') {
                header("Location: admin.php");
                exit();
            } else {
                echo "❌ Invalid user type selected!";
            }
        } else {
            echo "❌ Invalid password!";
        }
    } else {
        echo "❌ No user found!";
    }
}
$conn->close();
?>
