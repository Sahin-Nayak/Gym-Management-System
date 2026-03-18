-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 11:28 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `athlete_facts`
--

CREATE TABLE `athlete_facts` (
  `id` int(11) NOT NULL,
  `icon` varchar(10) DEFAULT '?',
  `title` varchar(120) NOT NULL,
  `fact_text` text NOT NULL,
  `category` varchar(60) DEFAULT 'General',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `athlete_facts`
--

INSERT INTO `athlete_facts` (`id`, `icon`, `title`, `fact_text`, `category`, `sort_order`, `is_active`, `created_at`) VALUES
(1, '💪', 'Muscle Memory', 'Muscles can remember movement patterns for years, making it easier to regain fitness after a break.', 'Training', 1, 1, '2026-03-13 15:13:27'),
(2, '🏃', 'Cardio Burns Fat', 'Just 30 minutes of moderate cardio can burn up to 300 calories depending on body weight.', 'Cardio', 2, 1, '2026-03-13 15:13:27'),
(3, '💧', 'Hydration Matters', 'Even 2% dehydration can reduce athletic performance by up to 10%.', 'Nutrition', 3, 1, '2026-03-13 15:13:27'),
(4, '😴', 'Sleep & Recovery', 'Muscles grow during rest, not during workouts. 7–9 hours of sleep is essential for athletes.', 'Recovery', 4, 1, '2026-03-13 15:13:27'),
(5, '🥗', 'Protein Timing', 'Consuming protein within 30 minutes post-workout maximizes muscle protein synthesis.', 'Nutrition', 5, 1, '2026-03-13 15:13:27'),
(6, '🔥', 'Afterburn Effect', 'High-intensity workouts can boost metabolism for up to 24 hours after training.', 'Training', 6, 1, '2026-03-13 15:13:27'),
(7, '🧠', 'Exercise & Brain', 'Regular exercise increases hippocampus size, improving memory and cognitive function.', 'Health', 7, 1, '2026-03-13 15:13:27'),
(8, '❤️', 'Heart Health', 'Athletes have up to 40% larger heart ventricles, pumping more blood per beat.', 'Health', 8, 1, '2026-03-13 15:13:27'),
(9, '⚡', 'Fast-Twitch Fibers', 'Sprinters have up to 80% fast-twitch muscle fibers, enabling explosive power.', 'Science', 9, 1, '2026-03-13 15:13:27'),
(10, '🫁', 'Lungs Can Hold 6 Litres of Air', 'The average human lung capacity is 6 litres of air. Athletes who train consistently develop stronger respiratory muscles, increasing their oxygen efficiency by up to 40%.', 'Science', 0, 1, '2026-03-14 07:32:17');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `member_id`, `check_in`, `check_out`, `date`, `created_at`) VALUES
(2, 1, '2026-03-14 12:29:02', '2026-03-14 12:33:21', '2026-03-14', '2026-03-14 06:59:02');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `cover_emoji` varchar(10) DEFAULT '?',
  `category` varchar(80) DEFAULT 'General',
  `tags` varchar(255) DEFAULT NULL,
  `author_name` varchar(100) DEFAULT 'Admin',
  `is_published` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `slug`, `excerpt`, `content`, `cover_emoji`, `category`, `tags`, `author_name`, `is_published`, `views`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Top 5 Tips for Building Muscle Fast', 'top-5-tips-building-muscle-fast', 'Discover the proven strategies that elite athletes use to pack on lean muscle in the shortest time possible.', '<p>Building muscle requires a combination of proper training, nutrition, and recovery. Here are the top 5 tips:</p><h3>1. Progressive Overload</h3><p>Consistently increase the weight or reps over time to keep challenging your muscles.</p><h3>2. Eat Enough Protein</h3><p>Aim for 1.6–2.2g of protein per kg of bodyweight daily.</p><h3>3. Prioritize Sleep</h3><p>Growth hormone is released during deep sleep — aim for 8 hours.</p><h3>4. Compound Movements</h3><p>Squats, deadlifts, and bench press recruit the most muscle fibers.</p><h3>5. Track Your Progress</h3><p>Keep a training log to ensure you are making consistent gains.</p>', '💪', 'Training', 'muscle,training,tips', 'Admin', 1, 47, '2026-03-13 20:43:27', '2026-03-13 15:13:27', '2026-03-14 07:15:06'),
(2, 'The Ultimate Guide to Pre-Workout Nutrition', 'ultimate-guide-pre-workout-nutrition', 'What you eat before training can make or break your performance. Learn exactly what to eat and when.', '<p>Pre-workout nutrition is crucial for maximizing performance and recovery.</p><h3>Timing</h3><p>Eat a balanced meal 2–3 hours before training, or a small snack 30–60 minutes before.</p><h3>Carbohydrates</h3><p>Your primary fuel source — opt for oats, bananas, or rice for sustained energy.</p><h3>Protein</h3><p>25–40g of protein pre-workout helps prevent muscle breakdown.</p><h3>Hydration</h3><p>Drink 500ml of water 2 hours before your session.</p>', '🥗', 'Nutrition', 'nutrition,pre-workout,diet', 'Admin', 1, 28, '2026-03-13 20:43:27', '2026-03-13 15:13:27', '2026-03-13 15:13:27'),
(3, '5 Recovery Techniques Every Athlete Should Know', '5-recovery-techniques-athletes', 'Recovery is where the real gains happen. Master these five techniques to come back stronger every session.', '<p>Without proper recovery, your hard work in the gym goes to waste.</p><h3>1. Active Recovery</h3><p>Light movement on rest days — walking or swimming — promotes blood flow.</p><h3>2. Foam Rolling</h3><p>Self-myofascial release reduces muscle soreness and improves flexibility.</p><h3>3. Cold/Hot Contrast</h3><p>Alternating cold and hot showers reduces inflammation and boosts circulation.</p><h3>4. Stretch Daily</h3><p>10 minutes of stretching post-workout prevents injury and speeds recovery.</p><h3>5. Sleep</h3><p>Non-negotiable — 7–9 hours of quality sleep every night.</p>', '😴', 'Recovery', 'recovery,sleep,stretching', 'Admin', 1, 19, '2026-03-13 20:43:27', '2026-03-13 15:13:27', '2026-03-13 15:13:27'),
(5, 'Science-Backed Tips to Build Muscle Faster', 'cience-acked-ips-to-uild-uscle-aster-5', 'Building muscle isn&#039;t just about lifting heavy. Here are 5 proven, science-backed strategies that will accelerate your gains and keep you progressing week after week.', '<p>Building muscle is a science — and when you follow the right principles consistently, results are inevitable. Whether you\'re a beginner or an experienced lifter, these 5 tips will help you break through plateaus and build the physique you want.</p>\r\n\r\n<h2>1. Progressive Overload Is Everything</h2>\r\n<p>The single most important principle in muscle building is <strong>progressive overload</strong> — continuously increasing the demand on your muscles over time. This can mean:</p>\r\n<ul>\r\n  <li>Adding more weight to the bar</li>\r\n  <li>Performing more reps with the same weight</li>\r\n  <li>Reducing rest time between sets</li>\r\n  <li>Improving your range of motion</li>\r\n</ul>\r\n<p>If you\'re lifting the same weight for the same reps every week, your muscles have no reason to grow. <strong>Track your workouts</strong> and aim to improve at least one variable every session.</p>\r\n\r\n<h2>2. Protein Is Your Best Friend</h2>\r\n<p>Muscle is built from protein. Research consistently shows that consuming <strong>1.6–2.2g of protein per kg of bodyweight</strong> daily maximises muscle protein synthesis. For a 75kg person, that\'s 120–165g of protein every day.</p>\r\n<p>Best high-protein foods for muscle building:</p>\r\n<ul>\r\n  <li>🥩 Chicken breast — 31g protein per 100g</li>\r\n  <li>🥚 Eggs — 6g protein per egg</li>\r\n  <li>🐟 Tuna — 30g protein per 100g</li>\r\n  <li>🫘 Lentils — 18g protein per 100g (great for vegetarians)</li>\r\n  <li>🥛 Greek yogurt — 10g protein per 100g</li>\r\n</ul>\r\n<p>Spread your protein intake across 4–5 meals throughout the day to keep muscle protein synthesis elevated.</p>\r\n\r\n<h2>3. Prioritise Compound Movements</h2>\r\n<p>Isolation exercises like bicep curls have their place — but <strong>compound movements</strong> give you the most muscle-building bang for your buck. These exercises recruit multiple muscle groups simultaneously, triggering a greater hormonal response.</p>\r\n<p>The <strong>Big 5 compound lifts</strong> every serious lifter should master:</p>\r\n<ul>\r\n  <li><strong>Squat</strong> — Quads, glutes, hamstrings, core</li>\r\n  <li><strong>Deadlift</strong> — Entire posterior chain, traps, core</li>\r\n  <li><strong>Bench Press</strong> — Chest, shoulders, triceps</li>\r\n  <li><strong>Overhead Press</strong> — Shoulders, triceps, upper chest</li>\r\n  <li><strong>Barbell Row</strong> — Back, biceps, rear delts</li>\r\n</ul>\r\n<p>Build your training program around these movements and add isolation work as a supplement.</p>\r\n\r\n<h2>4. Sleep Is When You Actually Grow</h2>\r\n<p>Here\'s something most people overlook — <strong>you don\'t build muscle in the gym</strong>. You break it down. The real growth happens when you rest, especially during deep sleep.</p>\r\n<p>During sleep, your body releases <strong>Growth Hormone (GH)</strong> — the primary driver of muscle repair and growth. Studies show that people who sleep less than 6 hours per night have significantly lower testosterone levels and slower recovery rates.</p>\r\n<p><strong>Sleep optimisation tips:</strong></p>\r\n<ul>\r\n  <li>Aim for 7–9 hours of quality sleep every night</li>\r\n  <li>Keep a consistent sleep schedule — even on weekends</li>\r\n  <li>Avoid screens 1 hour before bed</li>\r\n  <li>Keep your room cool (18–20°C is optimal)</li>\r\n</ul>\r\n\r\n<h2>5. Train Each Muscle Twice Per Week</h2>\r\n<p>Research shows that training each muscle group <strong>twice per week</strong> produces significantly more growth than once per week. Muscle protein synthesis peaks around 24–36 hours after training and returns to baseline by 48–72 hours — so waiting a full week between sessions means leaving gains on the table.</p>\r\n<p><strong>Recommended training splits:</strong></p>\r\n<ul>\r\n  <li><strong>Upper/Lower Split</strong> — Train 4 days/week, upper and lower body alternating</li>\r\n  <li><strong>Push/Pull/Legs</strong> — Train 6 days/week, each muscle hit twice</li>\r\n  <li><strong>Full Body</strong> — Train 3 days/week, all muscles each session</li>\r\n</ul>\r\n<p>At FitZone, our certified trainers can design a personalised program based on your schedule and goals.</p>\r\n\r\n<h2>The Bottom Line</h2>\r\n<p>Muscle building isn\'t complicated — but it does require <strong>consistency, patience, and smart training</strong>. Follow these five principles, stay consistent for 6–12 months, and the results will speak for themselves.</p>\r\n<p>Ready to take your training to the next level? <strong>Join FitZone today</strong> and get access to world-class equipment, expert trainers, and a community that pushes you to be your best every single day.</p>', '📝', 'Training', 'muscle,strength,training tips,gains,bodybuilding', 'Somil', 1, 3, '2026-03-14 12:55:57', '2026-03-14 07:17:42', '2026-03-14 07:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `class_enrollments`
--

CREATE TABLE `class_enrollments` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `gym_classes`
--

CREATE TABLE `gym_classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `schedule_day` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_capacity` int(11) DEFAULT 20,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gym_classes`
--

INSERT INTO `gym_classes` (`id`, `class_name`, `description`, `trainer_id`, `schedule_day`, `start_time`, `end_time`, `max_capacity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Morning Yoga', 'Start your day with energizing yoga', 2, 'monday', '06:00:00', '07:00:00', 15, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(2, 'Zumba Fitness', 'High energy dance workout', 2, 'wednesday', '18:00:00', '19:00:00', 20, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(3, 'CrossFit', 'Intense functional training', 1, 'tuesday', '07:00:00', '08:00:00', 12, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(4, 'Cardio Blast', 'Heart-pumping cardio session', 1, 'thursday', '17:00:00', '18:00:00', 20, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(5, 'Power Lifting', 'Advanced weight training', 1, 'friday', '08:00:00', '09:00:00', 10, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(6, 'Running Training', '', 1, 'monday', '10:30:00', '11:30:00', 20, 1, '2026-03-14 07:00:07', '2026-03-14 07:01:10');

-- --------------------------------------------------------

--
-- Table structure for table `gym_enquiries`
--

CREATE TABLE `gym_enquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gym_enquiries`
--

INSERT INTO `gym_enquiries` (`id`, `name`, `email`, `phone`, `message`, `status`, `ip_address`, `created_at`) VALUES
(1, 'Gunjan', 'gunjan@gmail.com', 'Verma', 'I am Gunjan Vermaa', 'unread', '::1', '2026-03-14 07:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `gym_gallery`
--

CREATE TABLE `gym_gallery` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gym_gallery`
--

INSERT INTO `gym_gallery` (`id`, `image_path`, `caption`, `sort_order`, `is_active`, `created_at`) VALUES
(4, 'gallery_1773473839_0.jpg', 'Gym Area', 0, 1, '2026-03-14 07:37:19'),
(6, 'gallery_1773473902_0.jpg', 'Gym Interior', 1, 1, '2026-03-14 07:38:22');

-- --------------------------------------------------------

--
-- Table structure for table `gym_inventory`
--

CREATE TABLE `gym_inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `category` varchar(80) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `status` enum('working','not_working','under_maintenance') DEFAULT 'working',
  `notes` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gym_inventory`
--

INSERT INTO `gym_inventory` (`id`, `name`, `serial_number`, `category`, `quantity`, `purchase_date`, `purchase_price`, `status`, `notes`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Dumbble', 'DUM-2026-01', 'Strength', 5, '2026-02-10', '5000.00', 'working', '', 'equip_1773469113.jpg', '2026-03-14 06:18:33', '2026-03-14 06:18:33');

-- --------------------------------------------------------

--
-- Table structure for table `gym_videos`
--

CREATE TABLE `gym_videos` (
  `id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `youtube_url` varchar(300) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gym_videos`
--

INSERT INTO `gym_videos` (`id`, `title`, `youtube_url`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Gym', 'https://youtu.be/eAlNvWgTDZQ?si=tuNx91ypQaLPz1wi', 0, 1, '2026-03-13 15:15:11'),
(2, 'Gym Video', 'https://youtu.be/7Nwn2nLBqEU?si=xH7GZ3lNTPu_2Gdk', 1, 1, '2026-03-14 07:35:09');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'male',
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `join_date` date NOT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `assigned_trainer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `email`, `address`, `age`, `gender`, `weight`, `height`, `photo`, `emergency_contact_name`, `emergency_contact_phone`, `join_date`, `status`, `assigned_trainer_id`, `created_at`, `updated_at`) VALUES
(1, 6, 'Araya', 'Bhardwaj', '8990986787', 'arya@gmail.com', 'Delhi', 21, 'male', '78.00', '170.00', 'member_6.webp', '', '', '2026-03-04', 'active', 3, '2026-03-14 05:41:03', '2026-03-14 05:41:03'),
(2, 7, 'Nihal', 'Verma', '83368901710', 'vermanihal@gmail.com', 'Delhi NCR', 21, 'male', '67.00', '190.00', 'member_7.webp', '', '', '2026-02-01', 'active', 4, '2026-03-14 05:47:18', '2026-03-14 05:47:18');

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `duration_months` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `plan_name`, `duration_months`, `price`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 1, '1000.00', 'Monthly basic gym access with standard equipment', 1, '2026-03-13 15:13:25', '2026-03-14 05:43:56'),
(2, 'Standard', 3, '2000.00', 'Quarterly access with all equipment and 1 class/week', 1, '2026-03-13 15:13:25', '2026-03-13 15:13:25'),
(3, 'Premium', 12, '7000.00', 'Yearly access with all equipment, unlimited classes, and personal trainer', 1, '2026-03-13 15:13:25', '2026-03-13 15:13:25');

-- --------------------------------------------------------

--
-- Table structure for table `member_memberships`
--

CREATE TABLE `member_memberships` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `member_memberships`
--

INSERT INTO `member_memberships` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 1, 2, '2026-03-04', '2026-06-04', 'active', '2026-03-14 05:41:04'),
(2, 2, 1, '2026-02-01', '2026-03-01', 'active', '2026-03-14 05:48:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('payment_reminder','expiry_alert','class_schedule','general') DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 7, 'Renew', 'Renew', 'expiry_alert', 0, '2026-03-14 05:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `membership_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_mode` enum('cash','upi','card','bank_transfer') NOT NULL DEFAULT 'cash',
  `status` enum('paid','pending','failed') DEFAULT 'paid',
  `invoice_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `member_id`, `membership_id`, `amount`, `payment_date`, `payment_mode`, `status`, `invoice_number`, `notes`, `created_at`) VALUES
(1, 1, NULL, '2000.00', '2026-03-01', 'upi', 'paid', 'INV-20260314-B9F57', '', '2026-03-14 05:42:35');

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `email`, `specialization`, `experience_years`, `photo`, `bio`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Sahin', 'Nayak', '9876543210', 'rahul@gym.com', 'Weight Training &amp; Bodybuilding', 5, 'trainer_1.jpg', 'Certified fitness trainer with 5 years of experience.', 'active', '2026-03-13 15:13:26', '2026-03-14 05:34:07'),
(2, 3, 'Priya', 'Patel', '9876543211', 'priya@gym.com', 'Yoga &amp; Cardio', 3, 'trainer_2.webp', 'Yoga certified trainer specializing in weight loss programs.', 'active', '2026-03-13 15:13:26', '2026-03-14 05:26:31'),
(3, 4, 'Somil', 'Shinde', '9087897896', 'shindesomil@gmail.com', 'Weight Training &amp; Bodybuilding', 2, 'trainer_3.jpg', 'Somil Shinde is an weight trainer and also an body builder who have 2 years of experience and have enough knowledge to traine you', 'active', '2026-03-14 05:30:42', '2026-03-14 05:30:43'),
(4, 5, 'Lyra', 'Moonshade', '9098765423', 'lyramoonshade@gmail.com', 'Swimming', 2, 'trainer_4.webp', '', 'active', '2026-03-14 05:33:29', '2026-03-14 05:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','trainer','member') NOT NULL DEFAULT 'member',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `reset_token`, `reset_expiry`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gym.com', '$2y$10$TAJNDJSJglaHPVbI2nyduuV.8Kt0oOWjEavBopRiNkauvayGPr2Du', 'admin', NULL, NULL, 1, '2026-03-13 15:13:25', '2026-03-13 15:13:25'),
(2, 'trainer_rahul', 'rahul@gym.com', '$2y$10$TAJNDJSJglaHPVbI2nyduuV.8Kt0oOWjEavBopRiNkauvayGPr2Du', 'trainer', NULL, NULL, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(3, 'trainer_priya', 'priya@gym.com', '$2y$10$TAJNDJSJglaHPVbI2nyduuV.8Kt0oOWjEavBopRiNkauvayGPr2Du', 'trainer', NULL, NULL, 1, '2026-03-13 15:13:26', '2026-03-13 15:13:26'),
(4, 'trainer_somil_120', 'shindesomil@gmail.com', '$2y$10$dmFi9b.oOLIIrkf.DjoiyObOt6DFX/nF6/604qZ9NyhgGd4Z1PgdG', 'trainer', NULL, NULL, 1, '2026-03-14 05:30:42', '2026-03-14 05:30:42'),
(5, 'trainer_lyra_f09', 'lyramoonshade@gmail.com', '$2y$10$4e8qsFd1qFETRpe.KuuqjOyOXRepJIKQe/BJV2gnRfSYECht6ctKG', 'trainer', NULL, NULL, 1, '2026-03-14 05:33:26', '2026-03-14 05:33:26'),
(6, 'araya_8982', 'arya@gmail.com', '$2y$10$ZfUWitDSaQu5xQVWvfpaIuB/eoplVuHX0iYLAl1EY4VdMchLuj4tC', 'member', NULL, NULL, 1, '2026-03-14 05:40:58', '2026-03-14 05:40:58'),
(7, 'nihal_228c', 'vermanihal@gmail.com', '$2y$10$RfWaH.b6LBmwuXnaMzEYjOFf6UAFJyvsQ3IXSrCrEUTUvMLfwNiWC', 'member', NULL, NULL, 1, '2026-03-14 05:47:03', '2026-03-14 05:47:03');

-- --------------------------------------------------------

--
-- Table structure for table `website_content`
--

CREATE TABLE `website_content` (
  `id` int(11) NOT NULL,
  `section_key` varchar(80) NOT NULL,
  `label` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('text','textarea','url','email','phone','toggle') DEFAULT 'text',
  `section_group` varchar(60) DEFAULT 'general',
  `sort_order` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `website_content`
--

INSERT INTO `website_content` (`id`, `section_key`, `label`, `value`, `type`, `section_group`, `sort_order`, `updated_at`) VALUES
(1, 'gym_name', 'Gym Name', 'FITZONE', 'text', 'general', 1, '2026-03-13 15:13:27'),
(2, 'tagline', 'Tagline', 'Premium Fitness Center', 'text', 'general', 2, '2026-03-13 15:13:27'),
(3, 'phone', 'Phone Number', '+91 98765 43210', 'phone', 'general', 3, '2026-03-13 15:13:27'),
(4, 'email', 'Email Address', 'info@fitzone.com', 'email', 'general', 4, '2026-03-13 15:13:27'),
(5, 'address', 'Address', '123 Fitness Street, Mumbai, Maharashtra 400001', 'textarea', 'general', 5, '2026-03-13 15:13:27'),
(6, 'working_hours', 'Working Hours', 'Mon–Sat: 5:00 AM – 10:00 PM  |  Sun: 6:00 AM – 8:00 PM', 'text', 'general', 6, '2026-03-13 15:13:27'),
(7, 'hero_headline', 'Hero Headline', 'Transform Your Body Transform Your Legecy', 'text', 'hero', 1, '2026-03-14 08:31:57'),
(8, 'hero_headline_highlight', 'Headline Highlight', 'Legecy', 'text', 'hero', 2, '2026-03-14 08:32:03'),
(9, 'hero_subheadline', 'Hero Sub-headline', 'Where champions are made. Transform your body, elevate your mind, and unlock your true potential with world-class training.', 'textarea', 'hero', 3, '2026-03-13 15:13:27'),
(10, 'hero_cta_primary', 'Primary CTA Button', 'Start Free Trial', 'text', 'hero', 4, '2026-03-13 15:13:27'),
(11, 'hero_cta_secondary', 'Secondary CTA Button', 'Explore Plans', 'text', 'hero', 5, '2026-03-13 15:13:27'),
(12, 'stats_members_count', 'Members Count', '2,500+', 'text', 'stats', 1, '2026-03-13 15:13:27'),
(13, 'stats_members_label', 'Members Label', 'Active Members', 'text', 'stats', 2, '2026-03-13 15:13:27'),
(14, 'stats_trainers_count', 'Trainers Count', '15+', 'text', 'stats', 3, '2026-03-13 15:13:27'),
(15, 'stats_trainers_label', 'Trainers Label', 'Expert Trainers', 'text', 'stats', 4, '2026-03-13 15:13:27'),
(16, 'stats_classes_count', 'Classes Count', '50+', 'text', 'stats', 5, '2026-03-13 15:13:27'),
(17, 'stats_classes_label', 'Classes Label', 'Weekly Classes', 'text', 'stats', 6, '2026-03-13 15:13:27'),
(18, 'stats_experience_count', 'Experience Count', '10+', 'text', 'stats', 7, '2026-03-13 15:13:27'),
(19, 'stats_experience_label', 'Experience Label', 'Years Experience', 'text', 'stats', 8, '2026-03-13 15:13:27'),
(20, 'about_title', 'About Title', 'More Than Just a Gym', 'text', 'about', 1, '2026-03-13 15:13:27'),
(21, 'about_subtitle', 'About Subtitle', 'We are a community of passionate athletes and fitness enthusiasts dedicated to helping you reach your peak performance.', 'textarea', 'about', 2, '2026-03-13 15:13:27'),
(22, 'about_body', 'About Body', 'Founded in 2014, FITZONE has been the cornerstone of fitness excellence in Mumbai. Our state-of-the-art facility combines cutting-edge equipment with expert coaching to deliver results that speak for themselves.', 'textarea', 'about', 3, '2026-03-13 15:13:27'),
(23, 'about_feature_1', 'About Feature 1', '10,000 sq ft of premium training space', 'text', 'about', 4, '2026-03-13 15:13:27'),
(24, 'about_feature_2', 'About Feature 2', 'Latest equipment from top brands', 'text', 'about', 5, '2026-03-13 15:13:27'),
(25, 'about_feature_3', 'About Feature 3', 'Certified expert trainers', 'text', 'about', 6, '2026-03-13 15:13:27'),
(26, 'about_feature_4', 'About Feature 4', 'Nutrition & wellness programs', 'text', 'about', 7, '2026-03-13 15:13:27'),
(27, 'services_title', 'Services Title', 'World-Class Facilities', 'text', 'services', 1, '2026-03-13 15:13:27'),
(28, 'services_subtitle', 'Services Subtitle', 'Everything you need to achieve your fitness goals under one roof.', 'textarea', 'services', 2, '2026-03-14 08:37:34'),
(29, 'plans_title', 'Plans Title', 'Simple, Transparent Pricing', 'text', 'plans', 1, '2026-03-13 15:13:27'),
(30, 'plans_subtitle', 'Plans Subtitle', 'Choose the plan that fits your goals. No hidden fees, no surprises.', 'textarea', 'plans', 2, '2026-03-14 08:48:02'),
(31, 'plans_featured_id', 'Featured Plan ID', '2', 'text', 'plans', 3, '2026-03-13 15:13:27'),
(32, 'trainers_title', 'Trainers Title', 'Meet Our Expert Trainers', 'text', 'trainers', 1, '2026-03-13 15:13:27'),
(33, 'trainers_subtitle', 'Trainers Subtitle', 'World-class coaches dedicated to your transformation', 'textarea', 'trainers', 2, '2026-03-13 15:13:27'),
(34, 'classes_title', 'Classes Title', 'Dynamic Group Classes', 'text', 'classes', 1, '2026-03-13 15:13:27'),
(35, 'classes_subtitle', 'Classes Subtitle', 'Energizing sessions led by certified instructors', 'textarea', 'classes', 2, '2026-03-14 08:18:31'),
(36, 'contact_title', 'Contact Title', 'Get In Touch', 'text', 'contact', 1, '2026-03-13 15:13:27'),
(37, 'contact_subtitle', 'Contact Subtitle', 'Ready to start your fitness journey? Contact us today and take the first step.', 'textarea', 'contact', 2, '2026-03-13 15:13:27'),
(38, 'contact_map_url', 'Map Embed URL', 'https://www.google.com/maps/place/Mumbai,+Maharashtra/@19.0814342,72.7135446,11z/data=!3m1!4b1!4m6!3m5!1s0x3be7c6306644edc1:0x5da4ed8f8d648c69!8m2!3d18.9582347!4d72.8319514!16zL20vMDR2bXA?entry=ttu&g_ep=EgoyMDI2MDMxMS4wIKXMDSoASAFQAw%3D%3D', 'url', 'contact', 3, '2026-03-14 09:20:54'),
(39, 'social_facebook', 'Facebook URL', 'https://facebook.com', 'url', 'social', 1, '2026-03-13 15:13:27'),
(40, 'social_instagram', 'Instagram URL', 'https://instagram.com', 'url', 'social', 2, '2026-03-13 15:13:27'),
(41, 'social_twitter', 'Twitter URL', 'https://twitter.com', 'url', 'social', 3, '2026-03-13 15:13:27'),
(42, 'social_youtube', 'YouTube URL', 'https://youtube.com', 'url', 'social', 4, '2026-03-13 15:13:27'),
(43, 'footer_about', 'Footer About Text', 'Your ultimate destination for fitness transformation. Join our community and start your journey today.', 'textarea', 'footer', 1, '2026-03-13 15:13:27'),
(44, 'footer_copyright', 'Copyright Text', '© 2026 FITZONE. All rights reserved by Somil Shinde.', 'text', 'footer', 2, '2026-03-14 07:43:08'),
(45, 'about_image', 'About: Section Image URL', 'https://tse4.mm.bing.net/th/id/OIP.FZS2pKNcjoQOEP90FucOOwHaJQ?w=4310&h=5387&rs=1&pid=ImgDetMain&o=7&rm=3', 'url', 'about', 9, '2026-03-13 15:26:01'),
(46, 'whatsapp_number', 'WhatsApp Number', '', 'phone', 'general', 7, '2026-03-13 15:20:47'),
(47, 'videos_show', 'Show Videos Section', '1', 'toggle', 'videos', 1, '2026-03-13 15:20:47'),
(48, 'videos_title', 'Videos: Section Title', 'Inside FitZone', 'text', 'videos', 2, '2026-03-13 15:20:47'),
(49, 'videos_subtitle', 'Videos: Subtitle', 'See our world-class facilities and training sessions in action.', 'textarea', 'videos', 3, '2026-03-14 07:45:02'),
(50, 'gallery_show', 'Show Gallery Section', '1', 'toggle', 'gallery', 1, '2026-03-13 15:20:47'),
(51, 'gallery_title', 'Gallery: Section Title', 'Gym Gallery', 'text', 'gallery', 2, '2026-03-13 15:20:47'),
(52, 'gallery_subtitle', 'Gallery: Subtitle', 'A glimpse into the FitZone experience.', 'textarea', 'gallery', 3, '2026-03-14 07:44:43'),
(53, 'location_show', 'Show Location Section', '1', 'toggle', 'location', 1, '2026-03-14 07:44:05'),
(54, 'location_map_embed', 'Location: Google Maps Embed (paste full iframe code)', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7545.496961650513!2d73.12988449604367!3d18.986712278798176!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7e863dad7b899%3A0x56221708d420be50!2sVichumbe%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1773421475598!5m2!1sen!2sin\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'textarea', 'location', 2, '2026-03-13 17:05:03'),
(55, 'location_title', 'Location: Section Title', 'Location', 'text', 'location', 3, '2026-03-13 15:20:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `athlete_facts`
--
ALTER TABLE `athlete_facts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`class_id`,`member_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `gym_classes`
--
ALTER TABLE `gym_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Indexes for table `gym_enquiries`
--
ALTER TABLE `gym_enquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gym_gallery`
--
ALTER TABLE `gym_gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gym_inventory`
--
ALTER TABLE `gym_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gym_videos`
--
ALTER TABLE `gym_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `member_memberships`
--
ALTER TABLE `member_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `membership_id` (`membership_id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `website_content`
--
ALTER TABLE `website_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `athlete_facts`
--
ALTER TABLE `athlete_facts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gym_classes`
--
ALTER TABLE `gym_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `gym_enquiries`
--
ALTER TABLE `gym_enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gym_gallery`
--
ALTER TABLE `gym_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `gym_inventory`
--
ALTER TABLE `gym_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gym_videos`
--
ALTER TABLE `gym_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `member_memberships`
--
ALTER TABLE `member_memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `website_content`
--
ALTER TABLE `website_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=410;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `gym_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gym_classes`
--
ALTER TABLE `gym_classes`
  ADD CONSTRAINT `gym_classes_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `member_memberships`
--
ALTER TABLE `member_memberships`
  ADD CONSTRAINT `member_memberships_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `member_memberships_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `member_memberships` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `trainers`
--
ALTER TABLE `trainers`
  ADD CONSTRAINT `trainers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
