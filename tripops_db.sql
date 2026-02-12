-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 05:45 PM
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
-- Database: `tripops_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `country`, `slug`, `category`, `image`) VALUES
(1, 'Goa', 'India', 'goa', 'beach', 'goa.jpg'),
(2, 'Kerala', 'India', 'kerala', 'countryside', 'kerala.jpg'),
(3, 'Bali', 'Indonesia', 'bali', 'countryside', 'bali.jpg'),
(4, 'London', 'UK', 'london', 'city', 'london.jpg'),
(5, 'Paris', 'France', 'paris', 'city', 'paris.jpg'),
(6, 'Tokyo', 'Japan', 'tokyo', 'city', 'tokyo.jpg'),
(17, 'Zurich', 'Switzerland', 'zurich', 'city', 'zurich.jpg'),
(18, 'Rome', 'Italy', 'rome', 'city', 'rome.jpg'),
(19, 'Hallstatt', 'Austria', 'hallstatt', 'village', 'hallstatt.jpg'),
(20, 'New York', 'USA', 'newyork', 'city', 'newyork.jpg'),
(21, 'Toronto', 'Canada', 'toronto', 'city', 'toronto.jpg'),
(22, 'Sydney', 'Australia', 'sydney', 'city', 'sydney.jpg'),
(23, 'Queenstown', 'New Zealand', 'queenstown', 'mountains', 'queen.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `destination_attractions`
--

CREATE TABLE `destination_attractions` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `rating` tinyint(4) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_attractions`
--

INSERT INTO `destination_attractions` (`id`, `destination_id`, `category`, `name`, `image`, `description`, `display_order`, `rating`) VALUES
(1, 1, 'nature', 'Baga Beach', 'baga.jpg', 'Popular beach known for nightlife and water sports', 1, 5),
(2, 1, 'nature', 'Calangute Beach', 'calangute.jpg', 'Largest and busiest beach in North Goa', 3, 5),
(3, 1, 'nature', 'Anjuna Beach', 'anjuna.jpg', 'Famous for flea market and relaxed vibe', 7, 5),
(4, 1, 'nature', 'Palolem Beach', 'palolem.jpg', 'Peaceful crescent-shaped beach in South Goa', 9, 5),
(5, 1, 'nature', 'Dudhsagar Waterfalls', 'dudhsagar.jpg', 'One of India’s tallest waterfalls', 10, 5),
(6, 1, 'heritage', 'Fort Aguada', 'aguada.jpg', '17th century Portuguese fort with lighthouse', 2, 5),
(7, 1, 'heritage', 'Chapora Fort', 'chapora.jpg', 'Hilltop fort with panoramic views', 2, 5),
(8, 1, 'religious', 'Basilica of Bom Jesus', 'bom_jesus.jpg', 'UNESCO World Heritage church', 1, 5),
(9, 1, 'religious', 'Se Cathedral', 'se_cathedral.jpg', 'One of the largest churches in Asia', 2, 5),
(10, 1, 'urban', 'Anjuna Flea Market', 'anjuna_market.jpg', 'Weekly flea market with local shopping', 1, 5),
(11, 1, 'urban', 'Fontainhas', 'fontainhas.jpg', 'Latin quarter with colorful streets', 2, 5),
(12, 1, 'experience', 'Mandovi River Cruise', 'mandovi_cruise.jpg', 'Evening cruise with music and views', 2, 5),
(13, 1, 'experience', 'Tito’s Lane', 'titos.jpg', 'Famous nightlife street in Baga', 2, 5),
(14, 1, 'wildlife', 'Salim Ali Bird Sanctuary', 'salim_ali.jpg', 'Mangrove forest with rich birdlife, perfect for nature lovers and birdwatching.', 1, 5),
(15, 1, 'heritage', 'Reis Magos Fort', 'reis_magos.jpg', 'Restored historic fort with museum and beautiful river views.', 2, 5),
(16, 1, 'experience', 'Spice Plantation', 'spice_plantation.jpg', 'Explore Goa’s famous spices, tropical gardens, and enjoy guided tours.', 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `destination_details`
--

CREATE TABLE `destination_details` (
  `id` int(11) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `best_time` varchar(100) DEFAULT NULL,
  `highlights` varchar(255) DEFAULT NULL,
  `currency_name` varchar(50) DEFAULT NULL,
  `currency_code` varchar(10) DEFAULT NULL,
  `currency_symbol` varchar(5) DEFAULT NULL,
  `common_gestures` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_details`
--

INSERT INTO `destination_details` (`id`, `slug`, `name`, `country`, `best_time`, `highlights`, `currency_name`, `currency_code`, `currency_symbol`, `common_gestures`, `category`, `main_image`, `description`) VALUES
(1, 'goa', 'Goa', 'India', 'Nov – Feb', 'Beaches, Nightlife, Portuguese Heritage', 'Indian Rupee', 'INR', '₹', 'People in Goa are generally warm, relaxed, and welcoming, and their common gestures and daily practices reflect this easygoing coastal lifestyle. A friendly smile, casual wave, or a simple nod is often used to greet others, even strangers, especially in villages and local neighborhoods. Goans usually speak politely and softly, mixing Konkani, English, and sometimes Hindi, and it’s common to hear friendly small talk in shops or cafés. Handshakes are common in formal or first-time meetings, while close friends may greet each other with a light hug or a pat on the shoulder. Respect for elders is important, shown through polite language and attentive listening. Daily life often revolves around community, food, music, and festivals, with people taking time to socialize, enjoy long meals, and celebrate traditions together, reflecting Goa’s calm, inclusive, and joyful way of living.', 'beach', 'goa.jpg', 'Goa, located along the Western coastline of India, is one of the country’s most loved and internationally recognized travel destinations, offering a perfect blend of history, culture, nature, and modern tourism. Known for its stunning beaches that stretch along the Arabian Sea, Goa attracts travelers with everything from lively beach parties and water sports to quiet shores ideal for relaxation. The state’s unique character is deeply influenced by its history as a former Portuguese colony for over 450 years, which is reflected in its UNESCO-listed churches, charming colonial architecture, old forts, and vibrant traditions. Beyond the beaches, Goa is rich in natural beauty, featuring lush green landscapes, spice plantations, rivers, and wildlife sanctuaries that appeal to nature lovers and adventure seekers alike. Goa is also a food lover’s paradise, famous for its distinctive cuisine that combines Indian spices with Portuguese flavors, offering fresh seafood, local curries, and street food experiences. Throughout the year, the state comes alive with festivals, music events, and cultural celebrations that attract visitors from across the globe, adding to its energetic and welcoming atmosphere. With its warm climate, friendly locals, diverse experiences, and relaxed coastal lifestyle, Goa continues to be a favorite destination for families, solo travelers, couples, and backpackers, making it an essential stop on any travel itinerary in India.\n\n');

-- --------------------------------------------------------

--
-- Table structure for table `destination_events`
--

CREATE TABLE `destination_events` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_events`
--

INSERT INTO `destination_events` (`id`, `destination_id`, `name`, `image`, `description`, `start_date`, `end_date`) VALUES
(3, 1, 'Goa Carnival', 'goa_carnival.jpg', 'The Goa Carnival is a spectacular celebration that transforms the streets of Goa into a vibrant festival of color, music, and dance. Originating from Portuguese traditions, this multi-day event features parades with floats, folk performances, street dances, and masquerade balls. People from all over the world come to witness the joy, costumes, and cultural extravaganza, making it one of the most famous festivals in Goa.', '2026-02-15', '2026-02-18'),
(4, 1, 'Shigmo Festival', 'shigmo.jpg', 'Shigmo is a traditional spring festival celebrated across Goa, especially in villages. It is marked by colorful folk dances, musical performances, and processions depicting stories from Goan folklore and mythology. The festival brings communities together to celebrate the harvest season, vibrant culture, and local traditions, making it an immersive experience of Goan life.', '2026-03-01', '2026-03-10'),
(5, 1, 'Sunburn Festival', 'sunburn.jpg', 'Sunburn Festival is one of Asia’s biggest electronic dance music (EDM) festivals, hosted on the beautiful beaches of Goa. Featuring top international and Indian DJs, spectacular stage setups, laser shows, and beach parties, it draws thousands of party enthusiasts from across the globe. The festival blends music, dance, and Goan nightlife into a high-energy, unforgettable experience.', '2026-12-27', '2026-12-30'),
(6, 1, 'Sao Joao Festival', 'sao_joao.jpg', 'Sao Joao Festival, dedicated to St. John the Baptist, is celebrated in June with people jumping into rivers and wells fully dressed, a fun-filled tradition in Goa. Locals play traditional games, sing folk songs, and enjoy community festivities, making it one of the most joyous monsoon festivals in the region.', '2026-06-24', '2026-06-24'),
(7, 1, 'Festa de Dons de Goa', 'festa_dons.jpg', 'Festa de Dons is a vibrant celebration held in Old Goa, commemorating the rich cultural and religious heritage of the region. This event includes grand processions, performances, and local feasts that attract devotees and tourists alike, providing an authentic glimpse into Goan traditions.', '2026-08-10', '2026-08-12'),
(8, 1, 'Feast of St. Francis Xavier', 'st_francis.jpg', 'The Feast of St. Francis Xavier is one of the most important religious events in Goa, attracting thousands of pilgrims to the Basilica of Bom Jesus. Celebrated with solemn ceremonies, prayers, and processions, it honors the life and legacy of the revered saint and showcases the deep-rooted Christian heritage of Goa.', '2026-12-03', '2026-12-03'),
(9, 1, 'Goa Food and Cultural Festival', 'food_culture.jpg', 'This festival celebrates Goan cuisine, art, and cultural heritage. Visitors can enjoy traditional Goan dishes, music performances, and art exhibitions. It is a perfect opportunity to experience the flavors, traditions, and creative spirit of Goa in a lively festival environment.', '2026-11-15', '2026-11-20'),
(10, 1, 'International Film Festival of India (IFFI)', 'iffi.jpg', 'The International Film Festival of India, held annually in Goa, showcases films from around the world, providing a platform for cultural exchange and cinematic excellence. Attendees can watch premieres, interact with filmmakers, and attend workshops, making it one of Asia’s premier film festivals.', '2026-11-20', '2026-11-30');

-- --------------------------------------------------------

--
-- Table structure for table `destination_experiences`
--

CREATE TABLE `destination_experiences` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_experiences`
--

INSERT INTO `destination_experiences` (`id`, `destination_id`, `name`, `image`, `description`) VALUES
(2, 1, 'Adventure & Watersports', 'adventure.jpg', 'Goa is a paradise for adventure enthusiasts. From parasailing over turquoise waters to jet skiing, windsurfing, scuba diving, and banana boat rides, you can experience adrenaline-pumping activities at popular beaches like Baga, Calangute, and Anjuna. Adventure isn’t limited to water — try ATV rides, trekking, and cycling through scenic trails for a full dose of excitement.'),
(3, 1, 'Cultural Heritage', 'cultural.jpg', 'Goa has a rich cultural heritage that reflects Portuguese influence and local traditions. Explore historic forts like Aguada and Reis Magos, visit magnificent churches such as Basilica of Bom Jesus, and stroll through colorful colonial streets. Discover spice plantations, traditional villages, and local festivals to fully immerse yourself in Goa\'s history and culture.'),
(4, 1, 'Wildlife & Nature', 'wildlife.jpg', 'Escape the hustle and bustle by visiting Goa\'s serene natural spots. The Salim Ali Bird Sanctuary offers a glimpse of exotic birds and lush mangroves. Explore wildlife sanctuaries, nature trails, and peaceful backwaters. Goa\'s countryside, waterfalls, and forests provide ample opportunities for nature walks, photography, and relaxation amidst greenery.'),
(5, 1, 'Nightlife & Music', 'nightlife.jpg', 'Goa is famous for its lively nightlife. Enjoy beach parties with world-class DJs, experience vibrant nightclubs, and attend live music performances that blend electronic, rock, and traditional sounds. Popular areas like Baga, Anjuna, and Vagator are buzzing with energy after sunset, offering unforgettable nights of fun, dance, and socializing.'),
(6, 1, 'Relaxation & Wellness', 'relaxation.jpg', 'Goa isn’t just about partying and adventure. Find your inner peace with wellness retreats, yoga sessions on the beach, and rejuvenating spa treatments. Relax under the sun on quiet beaches, enjoy Ayurvedic massages, or take leisurely strolls at sunset. Perfect for travelers seeking a blend of serenity, comfort, and natural beauty.'),
(7, 1, 'Unique Local Experiences', 'local.jpg', 'Discover the authentic side of Goa by exploring local markets, attending traditional festivals, and interacting with Goan communities. Taste local cuisine, learn about handicrafts, visit hidden beaches, and experience village life. These unique experiences give travelers a deeper connection to the culture, people, and spirit of Goa.'),
(8, 1, 'Food & Culinary Tours', 'food.jpg', 'Goa offers a culinary journey like no other. From fresh seafood and traditional Goan curries to Portuguese-inspired sweets, the food scene is vibrant and diverse. Join guided food tours, explore street food markets, and taste local delicacies like bebinca, vindaloo, and xacuti. Foodies can enjoy cooking classes and learn authentic Goan recipes.'),
(9, 1, 'Boating & Backwaters', 'boating.jpg', 'Explore Goa\'s scenic backwaters and rivers with boating and river cruises. Enjoy a relaxing sunset cruise along Mandovi River, spot rare birds, and experience the tranquility of rural Goa. Houseboat experiences, kayaking, and paddleboarding are perfect ways to connect with nature while enjoying the peaceful waterways.');

-- --------------------------------------------------------

--
-- Table structure for table `destination_gallery`
--

CREATE TABLE `destination_gallery` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_gallery`
--

INSERT INTO `destination_gallery` (`id`, `destination_id`, `image`) VALUES
(1, 1, 'goa.jpg'),
(2, 1, 'goa.jpg'),
(3, 1, 'goa.jpg'),
(4, 1, 'goa.jpg'),
(5, 1, 'goa.jpg'),
(6, 1, 'goa.jpg'),
(7, 1, 'goa.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `destination_restaurants`
--

CREATE TABLE `destination_restaurants` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `cuisine` varchar(100) DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_restaurants`
--

INSERT INTO `destination_restaurants` (`id`, `destination_id`, `name`, `image`, `cuisine`, `rating`, `description`) VALUES
(2, 1, 'Fisherman\'s Wharf', 'fishermans_wharf.jpg', 'Seafood', 5, 'Popular seafood restaurant by the river with lively ambience.'),
(3, 1, 'Vinayak Restaurant', 'vinayak.jpg', 'Indian', 4, 'Famous for authentic Goan thalis and local dishes.'),
(5, 1, 'Thalassa', 'thalassa.jpg', 'Mediterranean', 4, 'Greek-inspired restaurant with sunset views and live performances.'),
(6, 1, 'Suzy\'s Restaurant', 'suzys.jpg', 'Continental', 4, 'Casual dining with international and Goan fusion dishes.'),
(7, 1, 'Martin\'s Corner', 'martins_corner.jpg', 'Seafood & Goan', 5, 'Iconic spot for fresh seafood and Goan cuisine.'),
(8, 1, 'Pousada by the Beach', 'pousada_beach.jpg', 'Continental', 4, 'Beachfront restaurant with relaxed vibes and cocktails.'),
(10, 1, 'The Black Sheep Bistro', 'black_sheep.jpg', 'European & Asian', 5, 'Trendy bistro offering fusion and continental dishes.'),
(11, 1, 'Cafe Chocolatti', 'cafe_chocolatti.jpg', 'Cafe & Desserts', 4, 'Popular cafe for desserts, waffles, and light meals.'),
(13, 1, 'La Plage', 'la_plage.jpg', 'French & Seafood', 4, 'Beachside French cuisine with fresh seafood and cocktails.'),
(14, 1, 'Baba Au Rhum', 'baba_au_rhum.jpg', 'French & Bakery', 5, 'Authentic French bakery and bistro experience.'),
(15, 1, 'Bomra\'s', 'bomras.jpg', 'Burmese & Asian', 5, 'Highly rated Burmese dishes with Asian fusion flavors.'),
(17, 1, 'La Plage Beach Shack', 'la_plage_shack.jpg', 'Continental & Seafood', 4, 'Relaxed beach shack with drinks and seafood specials.');

-- --------------------------------------------------------

--
-- Table structure for table `itineraries`
--

CREATE TABLE `itineraries` (
  `id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `user_name`, `rating`, `review`, `created_at`) VALUES
(22, 15, '', 4, '', '2026-02-09 09:51:19');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `destination_slug` varchar(255) DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `trip_type` enum('solo','group') DEFAULT NULL,
  `invite_code` varchar(20) DEFAULT NULL,
  `travel_month` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ongoing','finished') DEFAULT 'ongoing',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `host_id`, `destination_slug`, `days`, `trip_type`, `invite_code`, `travel_month`, `created_at`, `status`, `start_date`, `end_date`) VALUES
(51, 15, 'goa', NULL, 'solo', NULL, NULL, '2026-02-12 13:08:51', '', '2026-02-16', '2026-02-27');

-- --------------------------------------------------------

--
-- Table structure for table `trip_members`
--

CREATE TABLE `trip_members` (
  `id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` enum('host','member') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trip_messages`
--

CREATE TABLE `trip_messages` (
  `id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `profile_pic`) VALUES
(15, 'harsha', 'harshasujays@gmail.com', '$2y$10$BNi8f6UWaYfgERu31DoaKO3QsAy7pVYLWcwbn7aa4zC0OZc8nfdsO', 'uploads/profile_15.jpg'),
(16, 'Niveditha', 'nivedithaoffi@gmail.com', '$2y$10$2vRDlBLuOchjGNUA0G2ad.DySpU.UaBy0FMWYjoLeLM7zgyHRfA.O', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `destination_attractions`
--
ALTER TABLE `destination_attractions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destination_details`
--
ALTER TABLE `destination_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `destination_events`
--
ALTER TABLE `destination_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destination_experiences`
--
ALTER TABLE `destination_experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destination_gallery`
--
ALTER TABLE `destination_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `destination_restaurants`
--
ALTER TABLE `destination_restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `itineraries`
--
ALTER TABLE `itineraries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_members`
--
ALTER TABLE `trip_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_messages`
--
ALTER TABLE `trip_messages`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `destination_attractions`
--
ALTER TABLE `destination_attractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `destination_details`
--
ALTER TABLE `destination_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `destination_events`
--
ALTER TABLE `destination_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `destination_experiences`
--
ALTER TABLE `destination_experiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `destination_gallery`
--
ALTER TABLE `destination_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `destination_restaurants`
--
ALTER TABLE `destination_restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `itineraries`
--
ALTER TABLE `itineraries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `trip_members`
--
ALTER TABLE `trip_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `trip_messages`
--
ALTER TABLE `trip_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `destination_attractions`
--
ALTER TABLE `destination_attractions`
  ADD CONSTRAINT `destination_attractions_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destination_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `destination_events`
--
ALTER TABLE `destination_events`
  ADD CONSTRAINT `destination_events_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destination_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `destination_experiences`
--
ALTER TABLE `destination_experiences`
  ADD CONSTRAINT `destination_experiences_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destination_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `destination_gallery`
--
ALTER TABLE `destination_gallery`
  ADD CONSTRAINT `destination_gallery_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destination_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `destination_restaurants`
--
ALTER TABLE `destination_restaurants`
  ADD CONSTRAINT `destination_restaurants_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destination_details` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
