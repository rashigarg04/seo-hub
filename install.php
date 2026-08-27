<?php
// install.php
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'];
    $db   = $_POST['db'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create Database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `$db`;");
        
        // Create Tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(100) UNIQUE,
                config_value TEXT
            );
            
            CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_name VARCHAR(150),
                client_email VARCHAR(150),
                project_name VARCHAR(150),
                website_url VARCHAR(255),
                start_date DATE,
                price DECIMAL(10,2),
                duration ENUM('Monthly', 'Quarterly', 'Half Yearly', 'Annually'),
                payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
                next_due_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS seo_steps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                step_order INT,
                step_title VARCHAR(255),
                description TEXT
            );

            CREATE TABLE IF NOT EXISTS project_steps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT,
                step_id INT,
                status ENUM('Pending', 'Completed', 'Client Notified') DEFAULT 'Pending',
                completed_at DATETIME NULL,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            );
        ");
        
        // Insert Default SEO Steps if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM seo_steps");
        if ($stmt->fetchColumn() ==  0) {
            $default_steps = [
    [1, 'Client Intake & Goal Alignment', 'Gather target audience insights, primary business goals, target locations, and competitor lists.'],
    [2, 'Google Search Console Setup', 'Verify domain/URL property ownership in Google Search Console and submit the sitemap XML.'],
    [3, 'Google Analytics (GA4) Integration', 'Install Google Analytics tracking tags, verify real-time data streaming, and set up key conversion tracking events.'],
    [4, 'Social Media Profiles Audit & Connection', 'Connect, cross-link, or optimize active social channels for brand consistency (NAP sync).'],
    [5, 'Comprehensive Technical SEO Audit', 'Check site speed performance, crawl errors, mobile responsiveness, SSL validity, and fix broken links.'],
    [6, 'Robots.txt & XML Sitemap Optimization', 'Configure robots.txt to block unwanted directories and ensure clean XML sitemaps are indexed.'],
    [7, 'In-Depth Keyword Research & Mapping', 'Identify high-intent short-tail and long-tail target keywords and map them to appropriate landing pages.'],
    [8, 'On-Page Metadata Optimization', 'Craft optimized, compelling Title Tags and Meta Descriptions for core pages based on target keywords.'],
    [9, 'Heading Tags (H1, H2, H3) Structuring', 'Ensure proper semantic layout and hierarchy of heading tags across main landing pages.'],
    [10, 'Image Alt Text & Compression Audit', 'Optimize oversized images, implement next-gen formats, and add descriptive keyword-relevant alt attributes.'],
    [11, 'Content Gap & Structure Analysis', 'Review existing content depth, identify missing topical coverage, and plan new content opportunities.'],
    [12, 'Local SEO & Google Business Profile (GBP)', 'Create, verify, or fully optimize the Google Business Profile with exact NAP details and categories.'],
    [13, 'Core Local Citations Building', 'Build high-authority local business citations and directory listings to boost local authority.'],
    [14, 'White-Hat Backlink Outreach & Link Building', 'Initiate outreach campaigns, guest blogging, or digital PR to secure authoritative, relevant backlinks.'],
    [15, 'Monthly Performance & Ranking Report', 'Compile organic traffic, keyword movement, and conversion analytics data into a comprehensive monthly report for the client.']
];
            $ins = $pdo->prepare("INSERT INTO seo_steps (step_order, step_title, description) VALUES (?, ?, ?)");
            foreach ($default_steps as $step) {
                $ins->execute($step);
            }
        }

        // Save config.php
        $config_content = "<?php\ndefine('DB_HOST', '$host');\ndefine('DB_USER', '$user');\ndefine('DB_PASS', '$pass');\ndefine('DB_NAME', '$db');\n\ntry {\n    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\", DB_USER, DB_PASS, [\n        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC\n    ]);\n} catch (PDOException \$e) {\n    die('Database connection failed: ' . \$e->getMessage());\n}\n?>";
        file_put_contents('config.php', $config_content);
        
        header("Location: index.php");
        exit;
    } catch (\Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SEO Hub - Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 border border-slate-100">
        <h2 class="text-2xl font-bold text-slate-800 mb-2">SEO Hub Setup Wizard</h2>
        <p class="text-sm text-slate-500 mb-6">Enter your MySQL database details to install the app.</p>
        <?php if($message): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Database Host</label>
                <input type="text" name="host" value="localhost" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Database Name</label>
                <input type="text" name="db" placeholder="seo_hub_db" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Database User</label>
                <input type="text" name="user" placeholder="root" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Database Password</label>
                <input type="password" name="pass" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-medium py-2 rounded-lg hover:bg-indigo-700 transition">Complete Installation</button>
        </form>
    </div>
</body>
</html>