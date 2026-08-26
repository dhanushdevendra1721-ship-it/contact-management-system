
<?php



$db = new SQLite3("contacts.db");

$message = "";

if (isset($_POST["delete_id"])) {
    $delete_id = (int) $_POST["delete_id"];

    $stmt = $db->prepare("DELETE FROM contacts WHERE id = :id");
    $stmt->bindValue(":id", $delete_id, SQLITE3_INTEGER);
    $stmt->execute();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    if ($name !== "" && $phone !== "") {

        $stmt = $db->prepare(
            "INSERT INTO contacts (name, phone, email)
             VALUES (:name, :phone, :email)"
        );

        $stmt->bindValue(":name", $name, SQLITE3_TEXT);
        $stmt->bindValue(":phone", $phone, SQLITE3_TEXT);
        $stmt->bindValue(":email", $email, SQLITE3_TEXT);

        $stmt->execute();

        $message = "Contact Added Successfully!";
    }
}

$result = $db->query("SELECT * FROM contacts ORDER BY id DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Management System</title>
</head>

<body>

<h1>Contact Management System</h1>

<?php if ($message !== ""): ?>
    <h2><?php echo htmlspecialchars($message); ?></h2>
<?php endif; ?>

<h2>Add Contact</h2>

<form method="post">

    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <button type="submit">Add Contact</button>

</form>

<h2>Contacts</h2>

<?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>

    <p>
        <strong>Name:</strong>
        <?php echo htmlspecialchars($row["name"]); ?><br>

        <strong>Phone:</strong>
        <?php echo htmlspecialchars($row["phone"]); ?><br>

        <strong>Email:</strong>
    </p>

    <hr>
<form method="post">
    <input type="hidden" name="delete_id" value="<?php echo $row["id"]; ?>">
    <button type="submit">Delete</button>
</form>

<?php endwhile; ?>
</body>
</html>
