-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2024 at 11:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Laptops', 'High-performance laptops for gaming, business, and everyday use.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(2, 'Desktops/PC', 'Powerful desktop computers for gaming and professional tasks.', '2024-11-22 23:38:40', '2024-11-23 01:18:11'),
(3, 'Smartphones', 'Latest smartphones with cutting-edge technology.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(4, 'Tablets', 'Portable tablets for work and entertainment.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(5, 'Accessories', 'Various accessories including cases, chargers, and peripherals.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(6, 'Wearables', 'Smartwatches and fitness trackers to keep you connected.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(7, 'Networking', 'Routers, switches, and networking equipment for home and office.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(8, 'Gaming', 'Gaming consoles, games, and accessories for gamers.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(9, 'Home Appliances', 'Smart home devices and appliances for modern living.', '2024-11-22 23:38:40', '2024-11-22 23:38:40'),
(10, 'Software', 'Essential software solutions for productivity and creativity.', '2024-11-22 23:38:40', '2024-11-22 23:38:40');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT NULL CHECK (`discount_percentage` between 0 and 100),
  `expiration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percentage`, `expiration_date`) VALUES
(1, 'TECHPLUS', 10, '2024-11-07'),
(2, 'BLACKFRD', 12, '2024-11-30');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Processing','Shipped','Delivered','Canceled') DEFAULT 'Processing',
  `coupon_id` int(11) DEFAULT NULL,
  `original_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `shipping_address`, `payment_method`, `order_date`, `status`, `coupon_id`, `original_amount`) VALUES
(17, 4, 450.00, 'prishtine', 'Credit Card', '2024-11-22 23:05:10', 'Processing', NULL, NULL),
(18, 4, 2464.00, 'Prizren', 'Credit Card', '2024-11-22 23:11:07', 'Processing', 2, 2800.00),
(19, 4, 52.80, 'Ferizaj', 'Bank Transfer', '2024-11-22 23:11:45', 'Processing', 2, 60.00),
(20, 4, 1284.80, 'Prishtine', 'Credit Card', '2024-11-23 01:42:07', 'Processing', 2, 1460.00),
(21, 4, 1400.00, 'ferizaj', 'Cash', '2024-11-23 02:26:08', 'Processing', NULL, 1400.00),
(22, 4, 1400.00, 'Shkup', 'PayPal', '2024-11-23 02:27:55', 'Processing', NULL, 1400.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES
(14, 17, 2, 1, NULL),
(15, 18, 1, 2, 1400.00),
(16, 19, 3, 1, 60.00),
(17, 20, 1, 1, 1400.00),
(18, 20, 3, 1, 60.00),
(19, 21, 1, 1, 1400.00),
(20, 22, 1, 1, 1400.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `description`, `category_id`, `stock`) VALUES
(1, 'LENOVO GAMING PC', 1400.00, 'img/1.3.png', 'Lenovo 2023 IdeaCentre Gaming 5 Desktop', 2, 8),
(2, 'LAPTOP HP', 450.00, 'img/2.png', 'HP Elitebook 650 G10 15.6 FHD Business Laptop Computer', 1, 10),
(3, 'GAMING HEADSET', 60.00, 'img/3.png', 'EKSA E900 Pro USB Gaming Headset for PC', 8, 10),
(4, 'IPHONE 14 PRO MAX', 650.00, 'img/4.png', 'The iPhone 14 Pro Max with 128GB storage', 3, 10),
(5, 'MOUSE GAMING', 39.99, 'img/5.png', 'ASUS ROG Spatha X Wireless Gaming Mouse', 8, 10),
(7, 'Benq', 670.00, 'img/6.png', 'Benq PC, programming ', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `review_date`) VALUES
(1, 4, 1, 5, 'dd', '2024-11-06 00:33:36'),
(2, 4, 2, 5, 'ss', '2024-11-10 22:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(4, 'test', 'test1@gmail.com', '1234', 'user', '2024-11-10 22:40:46'),
(5, 'admin', 'admin@example.com', '1234', 'admin', '2024-11-10 22:40:46'),
(6, 'tech', 'tech@gmail.com', 'tech123@', 'user', '2024-11-10 22:59:32'),
(7, 'test3', 'test3@test.com', 'shkolla123@', 'user', '2024-11-12 20:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`) VALUES
(1, 4, NULL),
(2, 4, NULL),
(3, 4, NULL),
(4, 4, NULL),
(5, 4, NULL),
(6, 4, NULL),
(7, 4, NULL),
(8, 4, NULL),
(9, 4, NULL),
(10, 4, NULL),
(11, 4, NULL),
(12, 4, NULL),
(13, 4, NULL),
(14, 4, NULL),
(15, 4, NULL),
(16, 4, NULL),
(17, 4, NULL),
(18, 4, NULL),
(19, 4, NULL),
(20, 4, NULL),
(21, 4, NULL),
(22, 4, NULL),
(23, 4, NULL),
(27, 4, 2),
(28, 4, 1),
(29, 4, 5),
(30, 4, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_orders_coupon` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
