<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "config/db.php";

    $name = $_POST["name"];
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $message = $_POST["message"];

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    // Send email to 4 addresses
    $recipients = [
        "suzy@zidalco.com",
        "dalila@zidalco.com",
        "frank@zidalco.com",
        "info@zidalco.com"
    ];

    $headers = "From: $email\r\n";
    $fullMessage = "From: $name <$email>\n\nSubject: $subject\n\n$message";

    foreach ($recipients as $to) {
        mail($to, "New Contact Message", $fullMessage, $headers);
    }

    echo "Message sent successfully!";
}

require_once '../config/mail_config.php';

$name    = $_POST['name'];
$email   = $_POST['email'];
$phone   = $_POST['phone'];
$message = $_POST['message'];

$subject = "📨 New Message from $name";
$body    = "
    <h3>New message received:</h3>
    <p><strong>Name:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Phone:</strong> $phone</p>
    <p><strong>Message:</strong><br>$message</p>
";

if (sendEmail($email, $name, $subject, $body)) {
    echo "✅ Message sent successfully!";
} else {
    echo "❌ Failed to send message.";
}
