<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle book addition form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $available = $_POST['available_copies'];
    $returned = $_POST['returned_copies'];

    $sql = "INSERT INTO books (title, author, available_copies, returned_copies)
            VALUES ('$title', '$author', $available, $returned)";

    if ($conn->query($sql) === TRUE) {
        $message = "✅ Book added successfully!";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}

// Handle book return by admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_book'])) {
  $roll_no = $_POST['student_roll_no'];
  $book_id = $_POST['book_id'];

  $updateQuery = "UPDATE issued_books 
                  SET return_date = NOW() 
                  WHERE student_roll_no = '$roll_no' 
                    AND book_id = '$book_id' 
                    AND return_date IS NULL";

  if ($conn->query($updateQuery) === TRUE) {
      $message = "✅ Book return recorded successfully!";
  } else {
      $message = "❌ Error updating return date: " . $conn->error;
  }
}

#Handle Posting New Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_notice'])) {
  $notice_title = mysqli_real_escape_string($conn, $_POST['title']);
  $notice_description = mysqli_real_escape_string($conn, $_POST['description']);

  $sql = "INSERT INTO notice (title, description) VALUES ('$notice_title', '$notice_description')";

  if (mysqli_query($conn, $sql)) {
      echo "<script>alert('Notice posted successfully.'); window.location.href='admin.php';</script>";
      exit;
  } else {
      echo "Error posting notice: " . mysqli_error($conn);
  }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_notice'])) {
  $notice_id = intval($_POST['notice_id']); // Get notice id safely

  $delete_sql = "DELETE FROM notice WHERE id = $notice_id";

  if (mysqli_query($conn, $delete_sql)) {
      echo "<script>alert('Notice deleted successfully.'); window.location.href='admin.php';</script>";
      exit;
  } else {
      echo "Error deleting notice: " . mysqli_error($conn);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 0;
    }
    a {
      color: #550000;
    }

    a:hover {
      color: #800000;
    }

    form input:focus {
      border: 2px solid #550000;
      outline: none;
    }

    button:hover {
      background-color: #800000;
    }

    .dashboard {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    header {
      background-color: #550000;
      color: white;
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    header .logo img {
      height: 50px;
    }

    header h1 {
      margin: 0;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 15px;
    }

    nav ul li a {
      color: white;
      text-decoration: none;
      font-weight: bold;
    }

    .sections-container {
      display: flex;
      flex-direction: column;
      gap: 40px;
      margin-top: 30px;
    }

    section {
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table th, table td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }

    table th {
      background-color: #550000;
      color: white;
    }

    table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    form input, form button {
      margin: 8px 0;
      padding: 8px;
      width: 100%;
    }

    button {
      background-color: #550000;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 10px;
      cursor: pointer;
    }

    .issued-book-records-container {
      padding: 20px;
      margin-top: 20px;
      background-color: #f9f9f9;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .issued-book-records-container h3 {
      margin-bottom: 15px;
      font-size: 20px;
    }
    
  body {
    background-color: #fff;
    color: #000;
    transition: background-color 0.4s;
  }

  body.dark-mode {
    background-color: #121212; /* Outer background becomes dark */
    color: #000; /* Text stays dark */
  }

  .dashboard {
    background-color: #fff; /* Inside dashboard frame stays white always */
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transition: background-color 0.4s;
    max-width: 1200px;
    margin: 20px auto;
  }
  
  .student-card, table, form, input, select, button {
    transition: background-color 0.4s, color 0.4s, border-color 0.4s;
  }

  /* Keep inner elements normal, no changes needed inside dashboard */


  </style>
</head>
<body>
  <div class="dashboard">
    <header>
      <div class="logo"><img src="logo.png" alt="Library Logo"></div>
      <h1>Admin Dashboard</h1>
      <nav>
        <ul>
          <li><a href="homepage/homepage.html">Home</a></li>
          <li>Manage Books</li>
          <li>Search Books</li>
          <li>Issued Books</li>
          <li style="display: flex; align-items: center; gap: 5px;">
            <button id="toggle-theme" style="background:none; border:none; font-size:20px; cursor:default;">🌙</button>
            
        </li>
          <li><a href="#">Logout</a></li>
        </ul>
      </nav>
    </header>

    <main>
      <div class="sections-container">
        <!-- Library Activities -->
        <section class="activity">
        <p>Total Books Issued: <strong id="issued-count">0</strong></p>

<script>
  let count = 0;
  const target = 11;
  const interval = setInterval(() => {
    count++;
    document.getElementById('issued-count').textContent = count;
    if (count >= target) {
      clearInterval(interval);
    }
  }, 50);


  const toggleBtn = document.getElementById('toggle-theme');
  toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    toggleBtn.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
  });

</script>

</section>

<section>
    <h2>Post New Notice</h2>
    <form action="admin.php" method="POST">
        <input type="text" name="title" placeholder="Notice Title" required><br><br>
        <textarea name="description" placeholder="Notice Description" required></textarea><br><br>
        <button type="submit" name="post_notice">Post Notice</button>
    </form>
</section>
<section id="notice">
    <h2>All Notices</h2>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM notice ORDER BY id DESC");
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='notice-item'>";
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            echo "<p>" . nl2br(htmlspecialchars($row['description'])) . "</p>";

            // Delete button form
            echo "<form method='POST' action='admin.php' onsubmit='return confirm(\"Are you sure you want to delete this notice?\");' style='display:inline;'>";
            echo "<input type='hidden' name='notice_id' value='" . $row['id'] . "'>";
            echo "<button type='submit' name='delete_notice' style='background-color: maroon; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;'>Delete</button>";
            echo "</form>";

            echo "<hr>";
            echo "</div>";
        }
    } else {
        echo "<p>No notices posted yet.</p>";
    }
    ?>
</section>



        <!-- Manage Books -->
        <section class="add-books">
          <h3>Add New Books</h3>

          <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

          <form method="POST" action="">
            <label>Title:</label>
            <input type="text" name="title" required><br>

            <label>Author:</label>
            <input type="text" name="author" required><br>

            <label>Available Copies:</label>
            <input type="number" name="available_copies" required><br>

            <label>Returned Copies:</label>
            <input type="number" name="returned_copies" required><br>

            <button type="submit" name="add_book">➕ Add Book</button>
          </form>

          <h4>📚 Existing Books</h4>
          <table>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Author</th>
              <th>Available</th>
              <th>Returned</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM books");
            while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['title']}</td>
                      <td>{$row['author']}</td>
                      <td>{$row['available_copies']}</td>
                      <td>{$row['returned_copies']}</td>
                    </tr>";
            }
            ?>
          </table>
        </section>

                <!-- Section for viewing students -->
                <section class="view-students">
          <h3>View Student Information</h3>

          <table>
            <tr>
              <th>Student ID</th>
              <th>Roll Number</th>
              <th>Name</th>
            </tr>
            <?php
            $student_result = $conn->query("SELECT id, roll_number, name FROM users");
            while ($row = $student_result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['roll_number']}</td>
                      <td>{$row['name']}</td>
                    </tr>";
            }
            ?>
          </table>
        </section>


        <section class="return-book">
  <h3>Mark Book as Returned</h3>

  <?php
  if (isset($_POST['mark_returned'])) {
      // Step 1: Get the form data
      $student_id = $_POST['student_id'];
      $book_id = $_POST['book_id'];
      $return_date = $_POST['return_date'];

      // Step 2: Get the current timestamp
      $created_at = date('Y-m-d H:i:s');

      // Step 3: Connect to Database
      $conn = new mysqli("localhost", "root", "", "library");

      if ($conn->connect_error) {
          die("❌ Connection failed: " . $conn->connect_error);
      }

      // Step 4: Update the issued_books table to set the return_date
      $update_stmt = $conn->prepare("UPDATE issued_books 
                                     SET return_date = ? 
                                     WHERE student_id = ? AND book_id = ? AND return_date IS NULL");
      $update_stmt->bind_param("sii", $return_date, $student_id, $book_id);

      // Step 5: Execute the update query
      if ($update_stmt->execute()) {
          // Step 6: Insert the return information into the returned_books table
          $insert_stmt = $conn->prepare("INSERT INTO returned_books (student_id, book_id, return_date, returned_by_admin, created_at) 
                                         VALUES (?, ?, ?, 1, ?)");
          $insert_stmt->bind_param("iiss", $student_id, $book_id, $return_date, $created_at);

          // Step 7: Execute and Check Insert Query
          if ($insert_stmt->execute()) {
              echo "<p style='color:green;'>✅ Book marked as returned successfully!</p>";
          } else {
              echo "<p style='color:red;'>❌ Error inserting into returned_books: " . htmlspecialchars($insert_stmt->error) . "</p>";
          }

          // Close the insert statement
          $insert_stmt->close();
      } else {
          echo "<p style='color:red;'>❌ Error updating issued_books table: " . htmlspecialchars($update_stmt->error) . "</p>";
      }

      // Step 8: Close connection
      $update_stmt->close();
      $conn->close();
  }
  ?>

  <form method="POST" action="">
      <label>Student ID:</label>
      <input type="number" name="student_id" required><br><br>

      <label>Book ID:</label>
      <input type="number" name="book_id" required><br><br>

      <label>Return Date:</label>
      <input type="date" name="return_date" required><br><br>

      <button type="submit" name="mark_returned">Mark as Returned</button>
  </form>

</section>


          <<div class="issued-book-records-container">
    <h3>Issued Book Records</h3>
    <table>
        <tr>
            <th>Student ID</th>
            <th>Book ID</th>
            <th>Book Title</th>
            <th>Issued Date</th>
            <th>Return Date</th>
        </tr>
        <?php
        // Fetch issued books with no return date (i.e., books that are still issued)
        $result = $conn->query("SELECT * FROM issued_books WHERE return_date IS NULL");
        while ($row = $result->fetch_assoc()) {
            $book_id = $row['book_id'];
            
            // Fetch book title using the book_id
            $book_result = $conn->query("SELECT title FROM books WHERE id = $book_id");
            $book_data = $book_result->fetch_assoc();

            // Display the row, showing the return date (which will be NULL if not returned yet)
            echo "<tr>
                    <td>{$row['student_id']}</td>
                    <td>{$row['book_id']}</td>
                    <td>{$book_data['title']}</td>
                    <td>{$row['issue_date']}</td>
                    <td>" . ($row['return_date'] ? $row['return_date'] : 'Not Returned Yet') . "</td>
                  </tr>";
        }
        ?>
    </table>
</div>

          <section class="returned-books" style="margin-top: 40px;">
  <h3>📚 Returned Book History</h3>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>Student ID</th>
      <th>Book ID</th>
      <th>Book Title</th>
      <th>Return Date</th>
      <th>Created At</th>
    </tr>
    <?php
    // Database connection
    $conn = new mysqli("localhost", "root", "", "library");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get returned books data
    $result = $conn->query("SELECT * FROM returned_books ORDER BY return_date DESC");

    // Loop through the returned books and display
    while ($row = $result->fetch_assoc()) {
        $book_id = $row['book_id'];

        // Fetch the book title using book_id
        $book_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
        $book_stmt->bind_param("i", $book_id);
        $book_stmt->execute();
        $book_result = $book_stmt->get_result();
        $book_data = $book_result->fetch_assoc();

        // Output the returned book data in table rows
        echo "<tr>
                <td>{$row['student_id']}</td>
                <td>{$row['book_id']}</td>
                <td>{$book_data['title']}</td>
                <td>{$row['return_date']}</td>
                <td>{$row['created_at']}</td>
              </tr>";
    }

    // Close the connection
    $conn->close();
    ?>
  </table>
</section>
<section class="manage-fines">
    <h2>Manage Fines</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Book Title</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Fine Amount (₹)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $fine_query = $conn->prepare("
                SELECT 
                    students.name AS student_name, 
                    books.title AS book_title,
                    issued_books.due_date,
                    issued_books.return_date,
                    issued_books.id,
                    issued_books.fine_paid
                FROM issued_books
                JOIN students ON issued_books.student_id = students.id
                JOIN books ON issued_books.book_id = books.id
                WHERE issued_books.due_date IS NOT NULL
            ");
            $fine_query->execute();
            $fine_result = $fine_query->get_result();

            while ($row = $fine_result->fetch_assoc()) {
                $due_date = new DateTime($row['due_date']);
                $return_date = $row['return_date'] ? new DateTime($row['return_date']) : new DateTime(); // if not returned, take today
                $days_late = $return_date > $due_date ? $due_date->diff($return_date)->days : 0;
                $fine_amount = $days_late * 5; // ₹5 fine per day

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['book_title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['due_date']) . "</td>";
                echo "<td>" . ($row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned') . "</td>";

                if ($fine_amount > 0) {
                    echo "<td>₹" . $fine_amount . "</td>";
                    echo "<td>" . ($row['fine_paid'] ? "<span style='color:green;'>Paid ✅</span>" : "<span style='color:red;'>Pending ❌</span>") . "</td>";

                    if (!$row['fine_paid']) {
                        echo "<td>
                            <form method='POST' action='mark_fine_paid.php' style='display:inline;'>
                                <input type='hidden' name='issued_book_id' value='" . $row['id'] . "'>
                                <button type='submit' style='padding:5px 10px;'>Mark as Paid</button>
                            </form>
                        </td>";
                    } else {
                        echo "<td>---</td>";
                    }
                } else {
                    echo "<td>No Fine</td>";
                    echo "<td colspan='2' style='text-align:center;'>---</td>";
                }

                echo "</tr>";
            }
            $fine_query->close();
            ?>
        </tbody>
    </table>
</section>




       
      </div>
    </main>
  </div>
</body>
</html>
