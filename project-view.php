<?php
require_once 'config.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$project_id = $_GET['id'] ?? 0;

// Handle step status update or mail trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_step'])) {
        $step_row_id = $_POST['step_row_id'];
        $new_status  = $_POST['status'];
        $completed_at = ($new_status === 'Completed') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("UPDATE project_steps SET status = ?, completed_at = ? WHERE id = ?");
        $stmt->execute([$new_status, $completed_at, $step_row_id]);
    }
    
    if (isset($_POST['notify_step'])) {
        $step_row_id = $_POST['step_row_id'];
        
        // Fetch project and step details
        $stmt = $pdo->prepare("SELECT p.*, s.step_title, ps.id as ps_id FROM project_steps ps JOIN projects p ON ps.project_id = p.id JOIN seo_steps s ON ps.step_id = s.id WHERE ps.id = ?");
        $stmt->execute([$step_row_id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $pdo->prepare("UPDATE project_steps SET status = 'Client Notified' WHERE id = ?")->execute([$step_row_id]);
            
            // Dispatch notification email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.example.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your_email@example.com';
                $mail->Password   = 'your_smtp_password';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('no-reply@seohub.local', 'SEO Hub');
                $mail->addAddress($data['client_email'], $data['client_name']);
                $mail->isHTML(true);
                $mail->Subject = "Milestone Completed: {$data['step_title']} for {$data['project_name']}";
                $mail->Body    = "Dear {$data['client_name']},<br><br>We are pleased to inform you that the milestone <b>{$data['step_title']}</b> has been successfully completed for your project.<br><br>Thank you for trusting us.<br><br>Best Regards,<br>SEO Team";
                $mail->send();
            } catch (Exception $e) {}
        }
    }

    if (isset($_POST['toggle_payment'])) {
        $pay_status = $_POST['payment_status'];
        $pdo->prepare("UPDATE projects SET payment_status = ? WHERE id = ?")->execute([$pay_status, $project_id]);
    }

    header("Location: project-view.php?id=" . $project_id);
    exit;
}

$proj_stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$proj_stmt->execute([$project_id]);
$project = $proj_stmt->fetch();
if(!$project) { header("Location: index.php"); exit; }

$steps_stmt = $pdo->prepare("SELECT ps.*, s.step_title, s.description FROM project_steps ps JOIN seo_steps s ON ps.step_id = s.id WHERE ps.project_id = ? ORDER BY s.step_order ASC");
$steps_stmt->execute([$project_id]);
$steps = $steps_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage: <?= htmlspecialchars($project['project_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs">
        <a href="index.php" class="text-sm text-slate-600 hover:text-indigo-600"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard</a>
        <span class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($project['project_name']) ?></span>
    </nav>
    
    <main class="max-w-5xl mx-auto px-6 py-8">
        <!-- Project Info & Payment Bar -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold"><?= htmlspecialchars($project['client_name']) ?> (<?= htmlspecialchars($project['client_email']) ?>)</span>
                <h2 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($project['project_name']) ?></h2>
                <a href="<?= htmlspecialchars($project['website_url']) ?>" target="_blank" class="text-xs text-indigo-600 hover:underline"><?= htmlspecialchars($project['website_url']) ?></a>
            </div>
            <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-lg border border-slate-100">
                <div>
                    <div class="text-xs text-slate-400 font-medium">Billing (₹<?= number_format($project['price'], 2) ?>)</div>
                    <div class="text-sm font-semibold text-slate-700"><?= $project['duration'] ?> • Due: <?= $project['next_due_date'] ?></div>
                </div>
                <form method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="toggle_payment" value="1">
                    <select name="payment_status" onchange="this.form.submit()" class="text-xs px-2.5 py-1.5 rounded-lg border font-medium <?= $project['payment_status'] == 'Paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
                        <option value="Pending" <?= $project['payment_status']=='Pending'?'selected':'' ?>>Payment Pending</option>
                        <option value="Paid" <?= $project['payment_status']=='Paid'?'selected':'' ?>>Payment Received</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- SEO Steps Workflow Checklist -->
        <h3 class="text-md font-semibold text-slate-800 mb-4"><i class="fa-solid fa-list-check mr-2"></i>SEO Optimization Checklist</h3>
        <div class="space-y-4">
            <?php foreach($steps as $s): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="max-w-xl">
                    <h4 class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($s['step_title']) ?></h4>
                    <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($s['description']) ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <?php if($s['status'] === 'Pending'): ?>
                        <form method="POST">
                            <input type="hidden" name="update_step" value="1">
                            <input type="hidden" name="step_row_id" value="<?= $s['id'] ?>">
                            <input type="hidden" name="status" value="Completed">
                            <button type="submit" class="bg-slate-100 text-slate-700 text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-emerald-600 hover:text-white transition">Mark Completed</button>
                        </form>
                    <?php elseif($s['status'] === 'Completed'): ?>
                        <span class="text-xs bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full font-medium">Completed</span>
                        <form method="POST">
                            <input type="hidden" name="notify_step" value="1">
                            <input type="hidden" name="step_row_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-indigo-700 animate-pulse"><i class="fa-solid fa-paper-plane mr-1"></i> Notify Client via Email</button>
                        </form>
                    <?php else: ?>
                        <span class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full font-medium"><i class="fa-solid fa-check-double mr-1"></i> Client Notified</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>