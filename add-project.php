<?php
require_once 'config.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name  = $_POST['client_name'];
    $client_email = $_POST['client_email'];
    $project_name = $_POST['project_name'];
    $website_url  = $_POST['website_url'];
    $start_date   = $_POST['start_date'];
    $price        = $_POST['price'];
    $duration     = $_POST['duration'];
    
    // Compute next due date based on duration
    $due_date = date('Y-m-d', strtotime($start_date . ' + 30 days'));
    if($duration == 'Quarterly') $due_date = date('Y-m-d', strtotime($start_date . ' + 90 days'));
    if($duration == 'Half Yearly') $due_date = date('Y-m-d', strtotime($start_date . ' + 180 days'));
    if($duration == 'Annually') $due_date = date('Y-m-d', strtotime($start_date . ' + 365 days'));

    $stmt = $pdo->prepare("INSERT INTO projects (client_name, client_email, project_name, website_url, start_date, price, duration, next_due_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$client_name, $client_email, $project_name, $website_url, $start_date, $price, $duration, $due_date]);
    $project_id = $pdo->lastInsertId();

    // Populate project steps from global template
    $steps = $pdo->query("SELECT id FROM seo_steps ORDER BY step_order ASC")->fetchAll();
    $ins_step = $pdo->prepare("INSERT INTO project_steps (project_id, step_id, status) VALUES (?, ?, 'Pending')");
    foreach($steps as $s) {
        $ins_step->execute([$project_id, $s['id']]);
    }

    // Send Welcome Email via PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Configure SMTP (Update credentials with your SMTP details or local mail setup)
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@example.com';
        $mail->Password   = 'your_smtp_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('no-reply@seohub.local', 'SEO Hub');
        $mail->addAddress($client_email, $client_name);
        $mail->isHTML(true);
        $mail->Subject = "Your SEO Project Has Started: {$project_name}";
        $mail->Body    = "Dear {$client_name},<br><br>We are excited to announce that your SEO campaign for <b>{$website_url}</b> has officially commenced.<br>You will receive live update emails as our team complete milestones on your campaign.<br><br>Best Regards,<br>SEO Management Team";

        $mail->send();
    } catch (Exception $e) {
        // Log mail error if needed, but project is created successfully
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New SEO Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-xl w-full bg-white rounded-xl shadow-lg p-8 border border-slate-100">
        <h2 class="text-xl font-bold text-slate-800 mb-6">Create New Project</h2>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Client Name</label>
                    <input type="text" name="client_name" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Client Email ID</label>
                    <input type="email" name="client_email" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Project Name</label>
                <input type="text" name="project_name" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="e.g. E-Commerce Organic Growth">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Website URL</label>
                <input type="url" name="website_url" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="https://example.com">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Price (₹)</label>
                    <input type="number" step="0.01" name="price" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="4999">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Billing Duration</label>
                    <select name="duration" class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Half Yearly">Half Yearly</option>
                        <option value="Annually">Annually</option>
                    </select>
                </div>
            </div>
            <div class="pt-4 flex justify-end space-x-3">
                <a href="index.php" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Create & Notify Client</button>
            </div>
        </form>
    </div>
</body>
</html>