<?php

$db = new SQLite3("contacts.db");

$db->exec("
    CREATE TABLE IF NOT EXISTS contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT
    )
");

$message = "";
$edit_contact = null;

/* ADD CONTACT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_contact"])) {

    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name !== "" && preg_match("/^[0-9]{10}$/", $phone)) {

        $check = $db->prepare("SELECT id FROM contacts WHERE phone = :phone");
        $check->bindValue(":phone", $phone, SQLITE3_TEXT);
        $result = $check->execute();
        if ($result->fetchArray(SQLITE3_ASSOC)) {
            $message = "Phone number already exists!";
        } else {
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
}

/* UPDATE CONTACT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_contact"])) {

    $id = (int)$_POST["update_id"];
    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name !== "" && preg_match("/^[0-9]{10}$/", $phone)) {
    $stmt = $db->prepare(
        "UPDATE contacts
         SET name = :name, phone = :phone, email = :email
         WHERE id = :id"
    );

    $stmt->bindValue(":name", $name, SQLITE3_TEXT);
    $stmt->bindValue(":phone", $phone, SQLITE3_TEXT);
    $stmt->bindValue(":email", $email, SQLITE3_TEXT);
    $stmt->bindValue(":id", $id, SQLITE3_INTEGER);
    $stmt->execute();
    $message = "Contact Updated Successfully!";

    }
    else {
        $message = "Invalid name or phone number!";
    }
    }

/* DELETE CONTACT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {

    $id = (int)$_POST["delete_id"];

    $stmt = $db->prepare(
        "DELETE FROM contacts WHERE id = :id"
    );

    $stmt->bindValue(":id", $id, SQLITE3_INTEGER);
    $stmt->execute();

    $message = "Contact Deleted Successfully!";
}

/* EDIT CONTACT */
if (isset($_GET["edit_id"])) {

    $id = (int)$_GET["edit_id"];

    $stmt = $db->prepare(
        "SELECT * FROM contacts WHERE id = :id"
    );

    $stmt->bindValue(":id", $id, SQLITE3_INTEGER);

    $result = $stmt->execute();
    $edit_contact = $result->fetchArray(SQLITE3_ASSOC);
}

/* SEARCH */
$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $stmt = $db->prepare(
        "SELECT * FROM contacts
         WHERE name LIKE :search
         OR phone LIKE :search
         OR email LIKE :search
         ORDER BY id DESC"
    );

    $stmt->bindValue(":search", "%" . $search . "%", SQLITE3_TEXT);

    $result = $stmt->execute();

} else {

    $result = $db->query(
        "SELECT * FROM contacts ORDER BY id DESC"
    );
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Contact Management System</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
    padding: 15px;
}

h1 {
    background: #2864e6;
    color: white;
    text-align: center;
    padding: 18px;
    border-radius: 8px;
}

h2 {
    color: #23447d;
}

.box {
    background: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}

input {
    padding: 10px;
    width: 280px;
    max-width: 90%;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

button {
    background: #2864e6;
    color: white;
    border: none;
    padding: 9px 14px;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    opacity: 0.85;
}

.contact {
    background: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 5px;
}

.message {
    color: #23447d;
    font-weight: bold;
}

</style>

</head>

<body>

<h1>Contact Management System</h1>

<?php if ($message !== ""): ?>

<p class="message">
    <?= htmlspecialchars($message) ?>
</p>

<?php endif; ?>


<h2>Add Contact</h2>

<div class="box">

<form method="post">

    <label>Name:</label><br>
    <input type="text" name="name" required><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" required pattern="[0-9]{10}" maxlength="10"><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br>

    <input type="hidden" name="add_contact" value="1">

    <button type="submit">
        Add Contact
    </button>

</form>

</div>


<?php if ($edit_contact): ?>

<h2>Edit Contact</h2>

<div class="box">

<form method="post">

    <label>Name:</label><br>

    <input
        type="text"
        name="name"
        value="<?= htmlspecialchars($edit_contact["name"]) ?>"
        required
    ><br>

    <label>Phone:</label><br>

    <input
        type="text"
        name="phone"
        value="<?= htmlspecialchars($edit_contact["phone"]) ?>"
        required pattern="[0-9]{10}" maxlength="10"
    ><br>

    <label>Email:</label><br>

    <input
        type="email"
        name="email"
        value="<?= htmlspecialchars($edit_contact["email"]) ?>"
    ><br>

    <input
        type="hidden"
        name="update_id"
        value="<?= $edit_contact["id"] ?>"
    >

    <input
        type="hidden"
        name="update_contact"
        value="1"
    >

    <button type="submit">
        Update Contact
    </button>

</form>

</div>

<?php endif; ?>


<h2>Search Contacts</h2>

<div class="box">

<form method="get">

    <input
        type="text"
        name="search"
        placeholder="Search by name, phone or email"
        value="<?= htmlspecialchars($search) ?>"
    >

    <button type="submit">
        Search
    </button>

</form>

</div>


<h2>Contacts</h2>

<?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>

<div class="contact">

    <strong>Name:</strong>
    <?= htmlspecialchars($row["name"]) ?><br>

    <strong>Phone:</strong>
    <?= htmlspecialchars($row["phone"]) ?><br>

    <strong>Email:</strong>
    <?= htmlspecialchars($row["email"]) ?><br>

</div>

<form method="get" style="display:inline;">

    <input
        type="hidden"
        name="edit_id"
        value="<?= $row["id"] ?>"
    >

    <button type="submit">
        Edit
    </button>

</form>


<form
    method="post"
    style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this contact?');"
>

    <input
        type="hidden"
        name="delete_id"
        value="<?= $row["id"] ?>"
    >

    <button type="submit">
        Delete
    </button>

</form>

<hr>

<?php endwhile; ?>

</body>
</html>
