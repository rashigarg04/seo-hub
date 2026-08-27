<?php
// steps-manage.php - Global Master Template Step Manager
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_step'])) {
    $title = $_POST['step_title'];
    $desc = $_POST['description'];
    $order = $_POST['step_order'];
    $pdo->prepare("INSERT INTO seo_steps (step_order, step_title, description) VALUES (?, ?, ?)")->execute([$order, $title, $desc]);
    header("Location: steps-manage.php");
    exit;
}

$steps = $pdo->query("SELECT * FROM seo_steps ORDER BY step_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage SEO Master Template</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs">
        <a href="index.php" class="text-sm text-slate-600 hover:text-indigo-600"><i class="fa-solid fa-arrow-left mr-1"></i> Dashboard</a>
        <span class="text-sm font-semibold text-slate-800">Global Master Template Manager</span>
    </nav>
    <main class="max-w-5xl mx-auto px-6 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-slate-200 h-fit shadow-xs">
            <h3 class="font-semibold text-sm text-slate-800 mb-4">Add Global Master Step</h3>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="add_step" value="1">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Step Order Position</label>
                    <input type="number" name="step_order" value="<?= count($steps)+1 ?>" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Step Title</label>
                    <input type="text" name="step_title" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Description / Guidelines</label>
                    <textarea name="description" rows="3" required class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Add to Template</button>
            </form>
        </div>
        <div class="md:col-span-2 space-y-3">
            <?php foreach($steps as $s): ?>
            <div class="bg-white p-4 rounded-xl border border-slate-200 flex justify-between items-center shadow-xs">
                <div>
                    <span class="text-xs font-bold text-indigo-600">Step <?= $s['step_order'] ?></span>
                    <h4 class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($s['step_title']) ?></h4>
                    <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($s['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>