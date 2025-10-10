-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Aug 16, 2025 at 06:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `darshanrestaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `people` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `name`, `email`, `phone`, `booking_date`, `booking_time`, `people`, `message`, `created_at`, `status`) VALUES
(4, 'pari', 'vaibhavgoswami055@gmail.com', '8799064890', '2025-08-15', '16:36:00', 10, 'hi i am coming at 4 50 pm', '2025-08-07 11:07:09', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `feedback_name` varchar(100) NOT NULL,
  `feedback_email` varchar(100) NOT NULL,
  `feedback_rating` int(11) DEFAULT NULL CHECK (`feedback_rating` between 1 and 5),
  `feedback_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `feedback_name`, `feedback_email`, `feedback_rating`, `feedback_message`, `created_at`) VALUES
(15, 'jaefini', 'alice.baker@example.com', 5, 'The pasta was cooked perfectly and the service was excellent!', '2025-06-01 17:21:58'),
(16, 'kndnv', 'brian.cook@example.com', 4, 'Great ambiance and tasty burgers, will visit again.', '2025-06-01 17:21:58'),
(17, 'nnn', 'carol.davis@example.com', 3, 'The food was okay, but the wait time was a bit long.', '2025-06-01 17:21:58'),
(18, 'cndnv', 'daniel.evans@example.com', 5, 'Loved the fresh ingredients and friendly staff. Highly recommend!', '2025-06-01 17:21:58'),
(19, 'cjd ', 'emma.foster@example.com', 2, 'The pizza was soggy and lacked flavor. Expected better.', '2025-06-01 17:21:58'),
(20, 'cjdncd', 'frank.garcia@example.com', 4, 'Good portion sizes and delicious desserts.', '2025-06-01 17:21:58'),
(21, 'nkrfnk', 'grace.hill@example.com', 5, 'Amazing experience! The chef really knows how to impress.', '2025-06-01 17:21:58'),
(22, 'djncd', 'henry.ian@example.com', 3, 'Decent food but the seating was uncomfortable.', '2025-06-01 17:21:58'),
(23, 'csnnc', 'isabel.jones@example.com', 1, 'The order was wrong and the staff was rude. Not coming back.', '2025-06-01 17:21:58'),
(24, 'ckcnek', 'jack.kelly@example.com', 4, 'Nice variety on the menu and great drinks selection.', '2025-06-01 17:21:58'),
(25, 'Caesar Salad', 'vaibhavgoswami055@gmail.com', 1, 'jdiejieo', '2025-06-02 20:18:33'),
(26, 'ninide', 'demo@gmail.com', 5, 'jennde', '2025-06-02 22:40:38'),
(27, 'knkscnkn', 'demo@gmail.com', 4, 'csmlslm', '2025-06-05 00:22:54'),
(28, 'Vaibhav', 'demo@gmail.com', 4, 'naixxna', '2025-07-06 19:33:10'),
(29, 'Vaibhav Pari', 'demo@gmail.com', 4, 'wxwiwis', '2025-07-06 20:30:55'),
(30, 'Vaibhav', 'example@gmail.com', 4, 'sksocje', '2025-07-09 13:03:26'),
(31, 'knkwnfwk', 'vaibhavgoswami055@gmail.com', 5, 'good cooking...', '2025-08-07 16:40:22');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `file_path`) VALUES
(7, '../assets/img/gallery/jay-wennington-N_Y88TWmGwA-unsplash.jpg'),
(8, '../assets/img/gallery/negley-stockman-8EPt1CSYLTQ-unsplash.jpg'),
(10, '../assets/img/gallery/andrius-budrikas-kGP7rp2gWbc-unsplash.jpg'),
(11, '../assets/img/gallery/jay-wennington-N_Y88TWmGwA-unsplash.jpg'),
(12, '../assets/img/gallery/amin-ramezani-afOvuzIgxPU-unsplash.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `menu_description` text NOT NULL,
  `menu_price` int(10) NOT NULL,
  `menu_category` enum('starter','dinner','lunch','breakfast') NOT NULL,
  `menu_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `menu_name`, `menu_description`, `menu_price`, `menu_category`, `menu_image`, `created_at`) VALUES
(4, 'Pancake Stackgff', 'Fluffy pancakes served with syrup, butter, and seasonal fruits.', 100, 'dinner', 'assets/img/menu/6.jpeg', '2025-05-27 11:38:48'),
(5, 'Tomato Soup', 'Rich and creamy tomato soup with fresh basil and a breadstick.', 50, 'dinner', 'assets/img/menu/panirtikka.jpeg', '2025-05-27 11:38:48'),
(6, 'Club Sandwich', 'Triple-layered sandwich with turkey, bacon, lettuce, and tomato.', 9, 'lunch', 'assets/img/menu/9.png', '2025-05-27 11:38:48'),
(32, 'khaman', 'mkdwl', 200, 'starter', 'assets/img/menu/menu_683b40b0b1c5f.jpeg', '2025-05-31 17:47:28'),
(49, 'ebbhw', 'cdvdyvcd', 70, 'breakfast', 'assets/img/menu/menu_683c27574d88a.png', '2025-06-01 10:11:35'),
(52, 'Masala Dosa', 'cskncskcis jncikscsn', 150, 'lunch', 'assets/img/menu/menu_683d4b43df29c.png', '2025-06-02 06:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(10) NOT NULL,
  `total_price` int(10) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `status` enum('Confirmed','Shipping','Ongoing','Delivering') NOT NULL DEFAULT 'Confirmed',
  `order_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_date` date NOT NULL DEFAULT curdate(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `menu_id`, `email`, `menu_name`, `quantity`, `price`, `total_price`, `mobile`, `address`, `status`, `order_time`, `order_date`, `created_at`) VALUES
(2, 102, 'jaysukh.rabari@yahoo.com', 'Ringna No Oro', 1, 0, 150, '9876512345', 'Bhavnagar, Gujarat', 'Shipping', '0000-00-00 00:00:00', '2025-06-07', '2025-06-07 14:40:00'),
(3, 103, 'kirit.solanki@gmail.com', 'Bajri Rotla & Lasan Chutney', 3, 0, 270, '9898989898', 'Amreli, Gujarat', 'Confirmed', '0000-00-00 00:00:00', '2025-06-06', '2025-06-06 11:30:00'),
(4, 104, 'mahesh.gohil@gmail.com', 'Undhiyu', 2, 0, 400, '9845123456', 'Junagadh, Gujarat', 'Delivering', '0000-00-00 00:00:00', '2025-06-05', '2025-06-05 13:20:00'),
(5, 105, 'haribhai.vala@yahoo.com', 'Kadhi Khichdi', 1, 0, 180, '9823456789', 'Jamnagar, Gujarat', 'Confirmed', '0000-00-00 00:00:00', '2025-06-04', '2025-06-04 10:45:00'),
(6, 106, 'bhupat.jadeja@rediffmail.com', 'Khichu & Chhas', 2, 0, 240, '9812345678', 'Morbi, Gujarat', 'Shipping', '0000-00-00 00:00:00', '2025-06-03', '2025-06-03 15:10:00'),
(8, 108, 'vallabh.zala@gmail.com', 'Bhindi Masala', 2, 0, 260, '9823123456', 'Surendranagar, Gujarat', 'Confirmed', '0000-00-00 00:00:00', '2025-06-01', '2025-06-01 12:30:00'),
(9, 109, 'karsan.koli@gmail.com', 'Gathiya Jalebi', 4, 0, 360, '9800000000', 'Porbandar, Gujarat', 'Delivering', '0000-00-00 00:00:00', '2025-05-31', '2025-05-31 14:00:00'),
(10, 110, 'devji.vekaria@gmail.com', 'Fafda & Chutney', 2, 0, 240, '9855555555', 'Bhuj, Gujarat', 'Delivering', '0000-00-00 00:00:00', '2025-05-30', '2025-05-30 13:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_img` varchar(255) NOT NULL DEFAULT '../assets/img/usersprofiles/profilepic.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `created_at`, `user_img`) VALUES
(4, 'parivaibhav@gmail.com', '$2y$10$kcByzBxnoFOt6B8KYh1xaeooZv4n9cpBnFyazW.JvyBvf0srlcOB.', 'admin', '2025-05-29 05:18:46', 'assets/img/usersprofiles/uid0-175233498917f6401c.jpeg'),
(9, 'demo@gmail.com', '$2y$10$Vqr6UyZuKDMzHK/y9ZLSrex6dPfICW7UEl1pnNyNR0UYW1SFXJoqO', 'user', '2025-06-01 09:24:25', 'assets/img/usersprofiles/uid0-175326867240317883.webp'),
(14, 'example@gmail.com', '$2y$10$.E8e9Rf/9JHsZzXthtn9FefOXM2Stm3Em0msHKdwRAAO9hU9omY8C', 'user', '2025-07-04 09:33:27', '../assets/img/usersprofiles/profilepic.jpg'),
(19, 'fake@gmail.com', '$2y$10$I5LVWlRA2BXMeZJSnDsyCerCsMSue1EChffupcxf.9ZCYc6aRevVy', 'user', '2025-07-23 10:10:07', 'assets/img/usersprofiles/uid0-1753265418f8b7a26c.jpeg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
