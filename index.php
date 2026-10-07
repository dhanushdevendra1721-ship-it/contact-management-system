<?php

function loadContacts(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    if ($json === false) {
        throw new RuntimeException("Unable to read the contacts file.");
    }

    $storedContacts = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($storedContacts)) {
        throw new UnexpectedValueException("The contacts file must contain a JSON array.");
    }

    $nextId = 1;
    foreach ($storedContacts as $contact) {
        if (is_array($contact) && isset($contact["id"]) && is_numeric($contact["id"])) {
            $nextId = max($nextId, (int)$contact["id"] + 1);
        }
    }

    $contacts = [];
    $usedIds = [];
    foreach ($storedContacts as $contact) {
        if (!is_array($contact) || !isset($contact["name"], $contact["phone"])) {
            throw new UnexpectedValueException("A contact in the contacts file is missing required data.");
        }

        $id = isset($contact["id"]) ? (int)$contact["id"] : $nextId++;
        if ($id < 1 || isset($usedIds[$id])) {
            throw new UnexpectedValueException("The contacts file contains an invalid or duplicate contact ID.");
        }

        $usedIds[$id] = true;
        $contacts[] = [
            "id" => $id,
            "name" => (string)$contact["name"],
            "phone" => (string)$contact["phone"],
            "email" => (string)($contact["email"] ?? ""),
        ];
    }

    return $contacts;
}

function saveContacts(string $file, array $contacts): void
{
    $json = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (file_put_contents($file, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException("Unable to save contacts. Check that the project folder is writable.");
    }
}

$contactsFile = __DIR__ . DIRECTORY_SEPARATOR . "contacts.json";
$contacts = loadContacts($contactsFile);
$nextId = 1;
foreach ($contacts as $contact) {
    $nextId = max($nextId, $contact["id"] + 1);
}

$message = "";
$edit_contact = null;

/* ADD CONTACT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_contact"])) {

    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name !== "" && preg_match("/^[0-9]{10}$/", $phone) && ($email === "" || filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $phoneExists = false;
        foreach ($contacts as $contact) {
            if ($contact["phone"] === $phone) {
                $phoneExists = true;
                break;
            }
        }

        if ($phoneExists) {
            $message = "Phone number already exists!";
        } else {
            $contacts[] = [
                "id" => $nextId,
                "name" => $name,
                "phone" => $phone,
                "email" => $email,
            ];
            saveContacts($contactsFile, $contacts);
            $message = "Contact Added Successfully!";
        }
    } else {
        $message = "Invalid name, phone number, or email address!";
    }
}

/* UPDATE CONTACT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_contact"])) {

    $id = (int)$_POST["update_id"];
    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $contactIndex = null;
    foreach ($contacts as $index => $contact) {
        if ($contact["id"] === $id) {
            $contactIndex = $index;
            break;
        }
    }

    if ($contactIndex === null) {
        $message = "Contact not found!";
    } elseif ($name === "" || !preg_match("/^[0-9]{10}$/", $phone) || ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $message = "Invalid name, phone number, or email address!";
    } else {
        $phoneExists = false;
        foreach ($contacts as $index => $contact) {
            if ($index !== $contactIndex && $contact["phone"] === $phone) {
                $phoneExists = true;
                break;
            }
        }

        if ($phoneExists) {
            $message = "Phone number already exists!";
        } else {
            $contacts[$contactIndex]["name"] = $name;
            $contacts[$contactIndex]["phone"] = $phone;
            $contacts[$contactIndex]["email"] = $email;
            saveContacts($contactsFile, $contacts);
            $message = "Contact Updated Successfully!";
        }
    }
}

/* DELETE CONTACT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $id = (int)$_POST["delete_id"];
    $remainingContacts = array_values(array_filter(
        $contacts,
        static function (array $contact) use ($id): bool {
            return $contact["id"] !== $id;
        }
    ));

    if (count($remainingContacts) === count($contacts)) {
        $message = "Contact not found!";
    } else {
        $contacts = $remainingContacts;
        saveContacts($contactsFile, $contacts);
        $message = "Contact Deleted Successfully!";
    }
}

/* EDIT CONTACT */
if (isset($_GET["edit_id"])) {
    $id = (int)$_GET["edit_id"];
    foreach ($contacts as $contact) {
        if ($contact["id"] === $id) {
            $edit_contact = $contact;
            break;
        }
    }
}

/* SEARCH */
$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $contacts = array_values(array_filter(
        $contacts,
        static function (array $contact) use ($search): bool {
            return stripos($contact["name"], $search) !== false
                || stripos($contact["phone"], $search) !== false
                || stripos($contact["email"], $search) !== false;
        }
    ));
}

usort($contacts, static function (array $first, array $second): int {
    return $second["id"] <=> $first["id"];
});

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

<?php foreach ($contacts as $row): ?>

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

<?php endforeach; ?>

</body>
</html>
