<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "config/db.php";
    require_once "config/mail_config.php"; // This must contain sendEmail()

    $name    = $_POST["name"] ?? '';
    $email   = $_POST["email"] ?? '';
    $phone   = $_POST["phone"] ?? '';
    $subject = $_POST["subject"] ?? 'New Contact Message';
    $message = $_POST["message"] ?? '';

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $subject, $message]);

    // Email content
    $body = "
        <h3>New contact message received:</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong><br>$message</p>
    ";

    $emailSubject = "📨 New Message from $name";

    $recipients = [
        "suzy@zidalco.com",
        "dalila@zidalco.com",
        "frank@zidalco.com",
        "info@zidalco.com"
    ];

    $allSent = true;
    foreach ($recipients as $recipient) {
        if (!sendEmail($recipient, 'Zidalco Website', $emailSubject, $body)) {
            $allSent = false;
        }
    }

    if ($allSent) {
        echo "✅ Message sent and stored successfully!";
    } else {
        echo "⚠️ Message saved, but email failed to send.";
    }
}
?>

