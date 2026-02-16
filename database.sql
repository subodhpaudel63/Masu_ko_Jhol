

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



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


--
------

--
-- Table structure for table `gallery`
--
-- Dumping data for table `gallery`
--

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
-- 

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
--
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
