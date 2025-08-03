<?php
require_once "../config/db.php";
require_once "../vendor/autoload.php"; // ✅ PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $phone = htmlspecialchars($_POST["phone"]);
    $message = htmlspecialchars($_POST["message"]);

    // ✅ Save to Database with email_sent = 'no'
    $sql = "INSERT INTO contact_messages (name, email, phone, message, email_sent) 
            VALUES (:name, :email, :phone, :message, 'no')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message
    ]);
    $lastId = $conn->lastInsertId();

    

        
        // ✅ Confirmation to User
        $mail->clearAddresses();
        $mail->addAddress($email, $name);
        $mail->Subject = "✅ We received your message!";
        $mail->Body    = "Dear $name,\n\nThank you for contacting Zidalco Company Limited. 
We have received your message and will get back to you shortly.\n\nBest Regards,\nZidalco Team";
        $mail->send();

        // ✅ Mark email_sent as yes
        $conn->prepare("UPDATE contact_messages SET email_sent='yes' WHERE id=?")->execute([$lastId]);

    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
    }

    echo "✅ Thank you, $name! Your message has been sent.";
}
?>
