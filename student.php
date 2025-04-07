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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="#">Home</a></li>
                <li><a href="#">My Books</a></li>
                <li><a href="#">Search Books</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

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
            <h3>Issued Books</h3>
            <table>
                <thead>
                    <tr><th>Book Title</th><th>Issue Date</th><th>Due Date</th></tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT books.title AS book_title, issued_books.issue_date, issued_books.due_date FROM issued_books JOIN books ON books.id = issued_books.book_id WHERE student_id = ?");
                    $stmt->bind_param("i", $student_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . htmlspecialchars($row['book_title']) . "</td>
                                   <td>" . htmlspecialchars($row['issue_date']) . "</td>
                                   <td>" . htmlspecialchars($row['due_date']) . "</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </section>

        <section class="book-action">
            <h3>Return Book</h3>
            <form action="return_book.php" method="POST">
                <label for="return_book_id">Select Book to Return:</label>
                <select name="return_book_id" required>
                    <?php
                    $stmt = $conn->prepare("SELECT books.id, books.title FROM issued_books 
                    JOIN books ON books.id = issued_books.book_id 
                    WHERE issued_books.student_id = ?");

                    $stmt->bind_param("i", $student_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['title']) . "</option>";
                    }
                    $stmt->close();
                    ?>
                </select>
                <button type="submit">Return Book</button>
            </form>
        </section>

        <section class="fine-payment">
            <h3>Fine Payment</h3>
            <div class="fine-info">
                <?php
                $fine_query = $conn->prepare("SELECT SUM(amount) AS total_fine FROM fines WHERE student_id = ? AND is_paid = 0");
                $fine_query->bind_param("i", $student_id);
                $fine_query->execute();
                $fine_result = $fine_query->get_result();
                $fine_data = $fine_result->fetch_assoc();
                $fine_amount = $fine_data['total_fine'] ?? 0;
                ?>

                <p>Overdue Fines: <strong>₹<?= number_format($fine_amount, 2) ?></strong></p>

                <?php if ($fine_amount > 0): ?>
                    <img src="gpay_qr.png" alt="Scan to Pay" style="width: 150px;">
                    <a href="upi://pay?pa=yourupi@okaxis&pn=RAIT%20Library&am=<?= $fine_amount ?>&cu=INR&tn=Library%20Fine%20Payment" target="_blank">
                        <button type="button">Pay via UPI</button>
                    </a>
                    <form action="pay_fine.php" method="POST" onsubmit="return confirm('Are you sure you have paid the fine?');">
                        <input type="hidden" name="student_id" value="<?= $student_id ?>">
                        <button type="submit">Mark as Paid</button>
                    </form>
                <?php else: ?>
                    <p style="color: green;">No fines due 🎉</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="due-dates">
            <h3>Due Date Reminders</h3>
            <ul>
                <?php
                $reminder_stmt = $conn->prepare("SELECT books.title, issued_books.due_date FROM issued_books JOIN books ON books.id = issued_books.book_id WHERE student_id = ?");
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
                    echo "<li>No books issued or no due dates found.</li>";
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

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('bookSearch');
        const booksTable = document.getElementById('booksTable').getElementsByTagName('tbody')[0];

        searchInput.addEventListener('input', function () {
            const filter = searchInput.value.toLowerCase();
            const rows = booksTable.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const title = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const author = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();

                if (title.includes(filter) || author.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    });
</script>

</body>
</html>
