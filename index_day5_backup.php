<?php

$file = "contacts.json";
$message = "";

if (file_exists($file)) {
    $contacts = json_decode(file_get_contents($file), true);

    if (!is_array($contacts)) {
        $contacts = [];
    }
} else {
    $contacts = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = htmlspecialchars($_POST["name"] ?? "");
    $phone = htmlspecialchars($_POST["phone"] ?? "");
    $email = htmlspecialchars($_POST["email"] ?? "");

    $contacts[] = [
        "name" => $name,
        "phone" => $phone,
        "email" => $email
    ];

    file_put_contents($file, json_encode($contacts, JSON_PRETTY_PRINT));

    $message = "Contact Added Successfully!";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Management System</title>
</head>

<body>

<h1>Contact Management System</h1>

<?php if ($message): ?>
    <h2><?php echo $message; ?></h2>
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

<?php if (count($contacts) > 0): ?>

    <?php foreach ($contacts as $contact): ?>

        <p>
            <strong>Name:</strong>
            <?php echo $contact["name"]; ?><br>

            <strong>Phone:</strong>
            <?php echo $contact["phone"]; ?><br>

            <strong>Email:</strong>
            <?php echo $contact["email"]; ?>
        </p>

        <hr>

    <?php endforeach; ?>

<?php else: ?>

    <p>No contacts added yet.</p>

<?php endif; ?>

</body>
</html>
