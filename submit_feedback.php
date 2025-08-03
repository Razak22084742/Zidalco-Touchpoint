<?php
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $phone = htmlspecialchars($_POST["phone"]);
    $message = htmlspecialchars($_POST["message"]);

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('❌ Invalid email address!'); window.history.back();</script>";
        exit();
    }

    // ✅ Validate phone number
    if (!preg_match("/^[0-9+\-\s]{7,20}$/", $phone)) {
        echo "<script>alert('❌ Invalid phone number!'); window.history.back();</script>";
        exit();
    }

    // ✅ Save to database
    $sql = "INSERT INTO client_feedback (name, email, phone, message) 
            VALUES (:name, :email, :phone, :message)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message
    ]);

    // ✅ Email to company
    $to = "zidalcoltd@gmail.com";
    $subject = "📩 New Client Feedback from $name";
    $company_body = "
        <h2>New Client Feedback</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Message:</strong><br>$message</p>
    ";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@zidalco.com\r\n";
    $headers .= "Reply-To: $email\r\n";

    mail($to, $subject, $company_body, $headers);

    // ✅ Styled email to client
    $client_subject = "✅ Thank you for contacting Zidalco Company Limited";
    $client_body = "
    <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
        <div style='max-width: 600px; background: #fff; margin: auto; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;'>
            <div style='background: #228B22; padding: 15px; text-align: center; color: #fff;'>
                <h2 style='margin: 0;'>Zidalco Company Limited</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Dear <strong>$name</strong>,</p>
                <p>Thank you for contacting <strong>Zidalco Company Limited</strong>. We have received your message and our team will get back to you shortly.</p>
                
                <div style='background:#f0f0f0; padding:10px; margin-top:15px; border-radius:5px;'>
                    <p><strong>Your Message:</strong></p>
                    <p style='color:#333;'>$message</p>
                </div>

                <p style='margin-top: 15px;'>Best regards,<br><strong>Zidalco Company Limited</strong></p>
            </div>
            <div style='background:#eee; text-align:center; padding:10px; font-size:12px; color:#555;'>
                © 2025 Zidalco Company Limited. All rights reserved.
            </div>
        </div>
    </div>
    ";
    $client_headers = "MIME-Version: 1.0" . "\r\n";
    $client_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $client_headers .= "From: zidalcoltd@gmail.com\r\n";

    mail($email, $client_subject, $client_body, $client_headers);

    echo "<script>
        alert('✅ Thank you, $name! Your message has been sent. A confirmation email has been sent to you.');
        window.location.href = '../contact.html';
    </script>";
}
?>
