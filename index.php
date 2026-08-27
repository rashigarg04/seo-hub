<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SEO Hub - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-xs">
        <h1 class="text-xl font-bold text-indigo-600"><i class="fa-solid fa-chart-line mr-2"></i>SEO Hub</h1>
        <div class="space-x-4">
            <a href="steps-manage.php" class="text-sm font-medium text-slate-600 hover:text-indigo-600"><i class="fa-solid fa-list-check mr-1"></i> Manage SEO Steps</a>
            <a href="add-project.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition"><i class="fa-solid fa-plus mr-1"></i> New Project</a>
        </div>
    </nav>
    
    <main class="max-w-7xl mx-auto px-6 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-slate-800">Active Client Projects</h2>
            <span class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full font-medium"><?= count($projects) ?> Total Projects</span>
        </div>

        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs uppercase font-semibold border-b border-slate-200">
                        <th class="p-4">Client / Project</th>
                        <th class="p-4">Website</th>
                        <th class="p-4">Start Date</th>
                        <th class="p-4">Price & Plan</th>
                        <th class="p-4">Payment</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    <?php if(empty($projects)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-slate-400">No projects found. Click 'New Project' to start.</td></tr>
                    <?php else: ?>
                        <?php foreach($projects as $p): ?>
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-4">
                                <div class="font-medium text-slate-900"><?= htmlspecialchars($p['project_name']) ?></div>
                                <div class="text-xs text-slate-400"><?= htmlspecialchars($p['client_name']) ?> (<?= htmlspecialchars($p['client_email']) ?>)</div>
                            </td>
                            <td class="p-4"><a href="<?= htmlspecialchars($p['website_url']) ?>" target="_blank" class="text-indigo-600 hover:underline"><?= htmlspecialchars($p['website_url']) ?> <i class="fa-solid fa-external-link-alt text-xs"></i></a></td>
                            <td class="p-4"><?= date('d M Y', strtotime($p['start_date'])) ?></td>
                            <td class="p-4">₹<?= number_format($p['price'], 2) ?> <span class="text-xs text-slate-400 block"><?= $p['duration'] ?></span></td>
                            <td class="p-4">
                                <?php if($p['payment_status'] == 'Paid'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Paid</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Due: <?= $p['next_due_date'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <a href="project-view.php?id=<?= $p['id'] ?>" class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-600 hover:text-white transition">Manage SEO <i class="fa-solid fa-arrow-right ml-1"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>