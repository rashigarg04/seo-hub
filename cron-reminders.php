<?php
// cron-reminders.php
require_once 'config.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$today = date('Y-m-d');
$five_days_later = date('Y-m-d', strtotime('+5 days'));
$five_days_ago = date('Y-m-d', strtotime('-5 days'));

// Fetch pending projects where due date matches criteria
$stmt = $pdo->prepare("SELECT * FROM projects WHERE payment_status = 'Pending' AND (next_due_date = ? OR next_due_date = ? OR next_due_date <= ?)");
$stmt->execute([$five_days_later, $today, $five_days_ago]);
$projects = $stmt->fetchAll();

foreach ($projects as $p) {
    $due_date = $p['next_due_date'];
    $subject = "";
    $body = "";

    if ($due_date === $five_days_later) {
        $subject = "Payment Reminder: Due on {$due_date} for {$p['project_name']}";
        $body = "Dear {$p['client_name']},<br><br>This is a friendly reminder that your SEO subscription payment of ₹{$p['price']} is due on <b>{$due_date}</b>.<br>Please clear the dues to ensure uninterrupted service.";
    } elseif ($due_date === $today) {
        $subject = "Payment Due TODAY: {$p['project_name']}";
        $body = "Dear {$p['client_name']},<br><br>Your SEO subscription payment of ₹{$p['price']} is due <b>today</b> ({$due_date}). Please process the payment immediately.";
    } elseif ($due_date < $today) {
        $subject = "URGENT: Payment was due on {$due_date} for {$p['project_name']}";
        $body = "Dear {$p['client_name']},<br><br>Your SEO subscription payment of ₹{$p['price']} was due on <b>{$due_date}</b> and is now past due. Please clear your balance immediately to keep your campaign active.";
    }

    if ($subject) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_email@example.com';
            $mail->Password   = 'your_smtp_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('billing@seohub.local', 'SEO Hub Billing');
            $mail->addAddress($p['client_email'], $p['client_name']);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
        } catch (Exception $e) {}
    }
}
echo "Reminder cron executed successfully.";
?>