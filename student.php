<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['name']) || !isset($_SESSION['roll_number'])) {
    header("Location: login.html");
    exit();
}

$name = $_SESSION['name'];
$roll = $_SESSION['roll_number'];
$student_id = $_SESSION['student_id'];


$sql = "SELECT * FROM notice ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js">  </script>
    <style>
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

  section#notice {
    width: 100%;
    overflow: hidden;
    background:rgb(238, 201, 201); /* Light pink background */
    color:rgb(114, 18, 23); /* Dark red text */
    padding: 10px 0;
    position: relative;
    white-space: nowrap;
   
    margin-bottom: 20px;
    font-family: Arial, sans-serif;
    font-size: 18px;
}

.notice-content {
    display: inline-block;
    animation: slideLeft 20s linear infinite;
    padding-left: 100%;
}

.notice-content:hover {
    animation-play-state: paused; /* Pause on hover */
}

.notice-item {
    display: inline-block;
    margin-right: 50px;
    font-weight: bold;
}

/* Animation for sliding left */
@keyframes slideLeft {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

  /* Keep inner elements normal, no changes needed inside dashboard */
</style>



</head>
<body>

<div class="dashboard">
    <header>
    

        <div class="logo">
            <img src="logo.png" alt="Library Logo">
        </div>
        <h1>Student Dashboard</h1>
        <nav>
    <ul>
        <li><a href="homepage/homepage.html">Home Page</a></li>
        <li><a href="logout.php">Logout</a></li>
        <li style="display: flex; align-items: center; gap: 5px;">
            <button id="toggle-theme" style="background:none; border:none; font-size:20px; cursor:default;">🌙</button>
            <span style="font-size:16px;"></span>
        </li>
    </ul>
</nav>




    </header>
    <br>

    <section id="notice">
    <div class="notice-content">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="notice-item">';
                echo htmlspecialchars($row['title']) . " - " . htmlspecialchars($row['description']);
                echo '</div>';
            }
        } else {
            echo '<div class="notice-item">No notices posted yet.</div>';
        }
        ?>
    </div>
</section>
  

    <?php if (isset($_GET['payment']) && $_GET['payment'] == 'success'): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 10px 20px; margin: 20px auto; width: 80%; border-radius: 5px; border: 1px solid #c3e6cb;">
        ✅ Fine marked as paid successfully!
    </div>
    <?php endif; ?>

<main>
    <div class="sections-container">
        

        <section class="student-info">
            <h3>Student Information</h3>
            <div class="student-card">
                <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
                <p><strong>Roll Number:</strong> <?= htmlspecialchars($roll) ?></p>
                <p><strong>Status:</strong> Active</p>
            </div>
        </section>

        <section class="book-action">
            <h3>Issue Book</h3>
            <form action="issue_book.php" method="POST">
                <label for="book_id">Select Book:</label>
                <select name="book_id" id="book_id" required>
                    <?php
                    $result = $conn->query("SELECT id, title FROM books WHERE available_copies > 0");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>" . htmlspecialchars($row['title']) . "</option>";
                    }
                    ?>
                </select>
                <button type="submit">Issue Book</button>
            </form>
        </section>

        <section class="available-books">
            <h3>Search Books</h3>
            <input type="text" id="bookSearch" placeholder="Search by title or author...">
            <table id="booksTable">
                <thead>
                    <tr><th>Title</th><th>Author</th><th>Available Copies</th></tr>
                </thead>
                <tbody>
                    <?php
                    $books_result = $conn->query("SELECT title, author, available_copies FROM books");
                    while ($book = $books_result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($book['title']) . "</td>
                                <td>" . htmlspecialchars($book['author']) . "</td>
                                <td>" . htmlspecialchars($book['available_copies']) . "</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section class="issued-books">
    <h3>All Time Issued Books</h3>
    <table>
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Issue Date</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to get all issued books, including the return date from returned_books if available
            $stmt = $conn->prepare("SELECT books.title AS book_title, issued_books.issue_date, 
                                           COALESCE(returned_books.return_date, 'Not Returned') AS return_date 
                                    FROM issued_books 
                                    JOIN books ON books.id = issued_books.book_id 
                                    LEFT JOIN returned_books ON returned_books.book_id = issued_books.book_id 
                                    AND returned_books.student_id = issued_books.student_id
                                    WHERE issued_books.student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Check if there are any results
            if ($result->num_rows > 0) {
                // Loop through all the issued books
                while ($row = $result->fetch_assoc()) {
                    // Display the book details
                    echo "<tr>
                            <td>" . htmlspecialchars($row['book_title']) . "</td>
                            <td>" . htmlspecialchars($row['issue_date']) . "</td>
                            <td>" . htmlspecialchars($row['return_date']) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No books found.</td></tr>";
            }
            
            $stmt->close();
            ?>
        </tbody>
    </table>
</section>



<section class="currently-issued-books">
    <h3>Currently Issued Books</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr>
                <th style="padding: 10px; text-align: left; background-color: #f8adad;">Book Title</th>
                <th style="padding: 10px; text-align: left; background-color: #f8adad;">Issue Date</th>
                <th style="padding: 10px; text-align: left; background-color: #f8adad;">Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Assuming $student_id is available (from session or otherwise)
            // Query to get books that are currently issued (i.e., where return_date is NULL)
            $stmt = $conn->prepare("SELECT books.title AS book_title, issued_books.issue_date, issued_books.due_date 
                                    FROM issued_books 
                                    JOIN books ON books.id = issued_books.book_id 
                                    WHERE student_id = ? AND issued_books.return_date IS NULL");
            $stmt->bind_param("i", $student_id);  // Binding the student_id dynamically
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if there are any issued books
            if ($result->num_rows > 0) {
                // Loop through the results and display each book
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td style='padding: 10px;'>" . htmlspecialchars($row['book_title']) . "</td>
                            <td style='padding: 10px;'>" . htmlspecialchars($row['issue_date']) . "</td>
                            <td style='padding: 10px;'>" . htmlspecialchars($row['due_date']) . "</td>
                          </tr>";
                }
            } else {
                // Display message when no books are currently issued
                echo "<tr><td colspan='3' style='text-align: center; padding: 10px;'>No books currently issued.</td></tr>";
            }

            $stmt->close();
            ?>
        </tbody>
    </table>
</section>




        <!-- Removed the Return Book Section -->

        <section class="returned-books">
    <h3>Returned Books</h3>
    <table>
        <thead>
            <tr><th>Book Title</th><th>Issue Date</th><th>Return Date</th></tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("
                (SELECT books.title AS book_title, issued_books.issue_date, issued_books.return_date
                 FROM issued_books 
                 JOIN books ON books.id = issued_books.book_id
                 WHERE issued_books.student_id = ? AND issued_books.return_date IS NOT NULL)
                
                UNION
                
                (SELECT books.title AS book_title, issued_books.issue_date, returned_books.return_date
                 FROM returned_books
                 JOIN books ON books.id = returned_books.book_id
                 JOIN issued_books ON issued_books.book_id = books.id
                 WHERE returned_books.student_id = ?)
            ");
            $stmt->bind_param("ii", $student_id, $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['book_title']) . "</td>
                            <td>" . ($row['issue_date'] ? htmlspecialchars($row['issue_date']) : '-') . "</td>
                            <td>" . htmlspecialchars($row['return_date']) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No books have been returned yet.</td></tr>";
            }

            $stmt->close();
            ?>
        </tbody>
    </table>
</section>


        <section class="fine-payment">
    <h3>Fine Payment</h3>
    <div class="fine-info">
        <?php
        // First, calculate the fine based on issued_books table
        $fine_query = $conn->prepare("
            SELECT 
                issued_books.due_date,
                issued_books.return_date
            FROM issued_books
            WHERE issued_books.student_id = ? AND (issued_books.fine_paid = 0 OR issued_books.fine_paid IS NULL)
        ");
        $fine_query->bind_param("i", $student_id);
        $fine_query->execute();
        $fine_result = $fine_query->get_result();

        $fine_amount = 0;
        $today = new DateTime();

        while ($row = $fine_result->fetch_assoc()) {
            $due_date = new DateTime($row['due_date']);
            $return_date = $row['return_date'] ? new DateTime($row['return_date']) : $today;

            if ($return_date > $due_date) {
                $days_late = $due_date->diff($return_date)->days;
                $fine_amount += $days_late * 5; // ₹5 per day
            }
        }
        ?>

        <p>Overdue Fines: <strong>₹<?= number_format($fine_amount, 2) ?></strong></p>

        <?php if ($fine_amount > 0): ?>
            <img src="gpay_qr.png" alt="Scan to Pay" style="width: 150px;">
            <a href="upi://pay?pa=yourupi@okaxis&pn=RAIT%20Library&am=<?= $fine_amount ?>&cu=INR&tn=Library%20Fine%20Payment" target="_blank">
                <button type="button">Pay via UPI</button>
            </a>
            <p style="margin-top: 10px; font-weight: bold;">Once payment is made, please wait for Admin to verify and update your fine status ✅</p>
        <?php else: ?>
            <p style="color: green;">No fines due 🎉</p>
        <?php endif; ?>
    </div>
</section>


        <section class="due-dates">
    <h3>Due Date Reminders</h3>
    <ul>
        <?php
        // Query to get due dates only for books that are still issued (return_date IS NULL)
        $reminder_stmt = $conn->prepare("SELECT books.title, issued_books.due_date 
                                         FROM issued_books 
                                         JOIN books ON books.id = issued_books.book_id 
                                         LEFT JOIN returned_books ON returned_books.book_id = issued_books.book_id 
                                         AND returned_books.student_id = issued_books.student_id
                                         WHERE issued_books.student_id = ? AND returned_books.return_date IS NULL");
        $reminder_stmt->bind_param("i", $student_id);
        $reminder_stmt->execute();
        $reminder_result = $reminder_stmt->get_result();

        $today = new DateTime();
        $count = 0;

        while ($row = $reminder_result->fetch_assoc()) {
            $count++;
            $due_date = new DateTime($row['due_date']);
            $interval = $today->diff($due_date)->days;
            $status = $due_date >= $today ? "Due in $interval days" : "Overdue by $interval days";
            $color = $due_date >= $today ? "black" : "red";
            echo "<li style='color:$color;'>" . htmlspecialchars($row['title']) . ": $status</li>";
        }

        if ($count === 0) {
            echo "<li>No currently issued books found.</li>";
        }

        $reminder_stmt->close();
        ?>
    </ul>
</section>


        <section class="activity-graph">
            <h3>Activity Overview</h3>
            <canvas id="activityChart"></canvas>
        </section>

    </div>
</main>

    <footer>
        <p>&copy; 2025 RAIT Library. All rights reserved.</p>
    </footer>
</div>

<script>
        // Create the chart
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Books Borrowed',
                    data: [5, 7, 3, 6, 8, 10],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Monthly Book Borrowing Overview'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Implement search functionality for books
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('bookSearch');
            const booksTable = document.getElementById('booksTable').getElementsByTagName('tbody')[0];

            searchInput.addEventListener('input', function () {
                const filter = searchInput.value.toLowerCase();
                const rows = booksTable.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    const title = cells[0].textContent.toLowerCase();
                    const author = cells[1].textContent.toLowerCase();
                    
                    if (title.includes(filter) || author.includes(filter)) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            });
        });



     
  const toggleBtn = document.getElementById('toggle-theme');
  toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    toggleBtn.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
  });



  


    </script>