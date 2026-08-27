-- Schema SQL for SEO-Hub Database

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `config_key` VARCHAR(100) UNIQUE,
  `config_value` TEXT
);

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(150) NOT NULL,
  `client_email` VARCHAR(150) NOT NULL,
  `project_name` VARCHAR(150) NOT NULL,
  `website_url` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `duration` ENUM('Monthly', 'Quarterly', 'Half Yearly', 'Annually') NOT NULL,
  `payment_status` ENUM('Pending', 'Paid') DEFAULT 'Pending',
  `next_due_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `seo_steps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `step_order` INT NOT NULL,
  `step_title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS `project_steps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `step_id` INT DEFAULT NULL,
  `step_title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `step_order` INT NOT NULL,
  `status` ENUM('Pending', 'Completed', 'Client Notified') DEFAULT 'Pending',
  `completed_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
);

-- Seed Initial 15 Granular Steps
INSERT INTO `seo_steps` (`step_order`, `step_title`, `description`) VALUES
(1, 'Client Intake & Goal Alignment', 'Gather target audience insights, primary business goals, target locations, and competitor lists.'),
(2, 'Google Search Console Setup', 'Verify domain/URL property ownership in Google Search Console and submit the sitemap XML.'),
(3, 'Google Analytics (GA4) Integration', 'Install Google Analytics tracking tags, verify real-time data streaming, and set up key conversion tracking events.'),
(4, 'Social Media Profiles Audit & Connection', 'Connect, cross-link, or optimize active social channels for brand consistency (NAP sync).'),
(5, 'Comprehensive Technical SEO Audit', 'Check site speed performance, crawl errors, mobile responsiveness, SSL validity, and fix broken links.'),
(6, 'Robots.txt & XML Sitemap Optimization', 'Configure robots.txt to block unwanted directories and ensure clean XML sitemaps are indexed.'),
(7, 'In-Depth Keyword Research & Mapping', 'Identify high-intent short-tail and long-tail target keywords and map them to appropriate landing pages.'),
(8, 'On-Page Metadata Optimization', 'Craft optimized, compelling Title Tags and Meta Descriptions for core pages based on target keywords.'),
(9, 'Heading Tags (H1, H2, H3) Structuring', 'Ensure proper semantic layout and hierarchy of heading tags across main landing pages.'),
(10, 'Image Alt Text & Compression Audit', 'Optimize oversized images, implement next-gen formats, and add descriptive keyword-relevant alt attributes.'),
(11, 'Content Gap & Structure Analysis', 'Review existing content depth, identify missing topical coverage, and plan new content opportunities.'),
(12, 'Local SEO & Google Business Profile (GBP)', 'Create, verify, or fully optimize the Google Business Profile with exact NAP details and categories.'),
(13, 'Core Local Citations Building', 'Build high-authority local business citations and directory listings to boost local authority.'),
(14, 'White-Hat Backlink Outreach & Link Building', 'Initiate outreach campaigns, guest blogging, or digital PR to secure authoritative, relevant backlinks.'),
(15, 'Monthly Performance & Ranking Report', 'Compile organic traffic, keyword movement, and conversion analytics data into a comprehensive monthly report for the client.');