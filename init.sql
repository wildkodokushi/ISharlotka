-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Авг 27 2026 г., 23:13
-- Версия сервера: 5.7.39
-- Версия PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cases_shop`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cases_catalog`
--

CREATE TABLE `cases_catalog` (
  `id_case` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `count` int(11) DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `inscription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sticker` tinyint(1) DEFAULT '0',
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `has_3d` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cases_catalog`
--

INSERT INTO `cases_catalog` (`id_case`, `title`, `description`, `price`, `count`, `image`, `collection_id`, `inscription`, `sticker`, `color`, `material_id`, `model_id`, `has_3d`, `created_at`) VALUES
(1, 'Цветочный рай', 'Нежный чехол с акварельным цветочным принтом ручной работы.', '890.00', 15, NULL, 1, NULL, 1, 'Розовый', 1, 3, 0, '2026-06-21 13:28:16'),
(2, 'Ночной минимализм', 'Строгий и лаконичный дизайн для тех, кто ценит стиль.', '750.00', 18, NULL, 3, NULL, 0, 'Чёрный', 2, 4, 0, '2026-06-21 13:28:16'),
(3, 'Звёздное небо', 'Глубокое ночное небо с мерцающими звёздами.', '1290.00', 8, NULL, 2, 'Ad astra per aspera', 1, 'Тёмно-синий', 1, 2, 1, '2026-06-21 13:28:16'),
(4, 'Городские огни', 'Вдохновлён панорамой ночного города.', '990.00', 12, NULL, 7, NULL, 0, 'Серый', 2, 5, 0, '2026-06-21 13:28:16'),
(5, 'Акварельный закат', 'Тёплые переходы оранжевого и алого.', '850.00', 18, NULL, 1, NULL, 1, 'Оранжевый', 1, 6, 0, '2026-06-21 13:28:16'),
(6, 'Мраморный шик', 'Благородный мраморный узор с золотыми прожилками.', '1450.00', 7, NULL, 4, NULL, 0, 'Белый', 3, 1, 0, '2026-06-21 13:28:16'),
(7, 'Бохо-цветы', 'Богемный стиль с полевыми цветами и листьями.', '920.00', 14, NULL, 6, NULL, 1, 'Зелёный', 1, 7, 0, '2026-06-21 13:28:16'),
(8, 'Абстракция', 'Смелые геометрические формы в духе современного искусства.', '1100.00', 0, NULL, 5, 'Art is life', 0, 'Фиолетовый', 2, 8, 0, '2026-06-21 13:28:16'),
(9, 'Чехол для крутых парней', 'Этот чехол предназначен только для крутых парней, не более!', '750.00', 5, NULL, 4, '', 1, '', 1, 6, 0, '2026-06-21 20:07:36');

-- --------------------------------------------------------

--
-- Структура таблицы `collections`
--

CREATE TABLE `collections` (
  `id_collection` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `collections`
--

INSERT INTO `collections` (`id_collection`, `name`, `description`, `cover_image`, `created_at`) VALUES
(1, 'Природа', 'Акварельные принты с цветами, листьями и пейзажами', NULL, '2026-06-21 13:28:16'),
(2, 'Космос', 'Звёзды, галактики и вселенная на твоём чехле', NULL, '2026-06-21 13:28:16'),
(3, 'Минимализм', 'Чистые линии и лаконичные формы', NULL, '2026-06-21 13:28:16'),
(4, 'Люкс', 'Благородные фактуры: мрамор, кожа, золото', NULL, '2026-06-21 13:28:16'),
(5, 'Арт', 'Смелые абстракции и современное искусство', NULL, '2026-06-21 13:28:16'),
(6, 'Бохо', 'Богемный стиль с этническими мотивами', NULL, '2026-06-21 13:28:16'),
(7, 'Город', 'Панорамы мегаполисов и городские огни', NULL, '2026-06-21 13:28:16');

-- --------------------------------------------------------

--
-- Структура таблицы `device_models`
--

CREATE TABLE `device_models` (
  `id_model` int(11) NOT NULL,
  `firm` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `device_models`
--

INSERT INTO `device_models` (`id_model`, `firm`, `model_name`) VALUES
(1, 'Apple', 'iPhone 14'),
(2, 'Apple', 'iPhone 14 Pro'),
(3, 'Apple', 'iPhone 15'),
(4, 'Apple', 'iPhone 15 Pro'),
(5, 'Samsung', 'Galaxy S23'),
(6, 'Samsung', 'Galaxy S24'),
(7, 'Samsung', 'Galaxy A54'),
(8, 'Xiaomi', 'Redmi Note 12'),
(9, 'Xiaomi', 'Mi 13 Pro');

-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

CREATE TABLE `favorites` (
  `id_fav` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `favorites`
--

INSERT INTO `favorites` (`id_fav`, `user_id`, `case_id`, `created_at`) VALUES
(1, 2, 8, '2026-06-21 20:02:13'),
(3, 4, 8, '2026-06-21 23:20:48');

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE `materials` (
  `id_material` int(11) NOT NULL,
  `material_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `materials`
--

INSERT INTO `materials` (`id_material`, `material_name`) VALUES
(1, 'Мягкий силикон'),
(2, 'Жёсткий пластик'),
(3, 'Натуральная кожа'),
(4, 'Эко-кожа'),
(5, 'Прозрачный TPU');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `date`, `price`, `status`) VALUES
(1, 2, '2026-06-21 20:01:59', '11000.00', 'completed'),
(2, 4, '2026-06-21 20:49:22', '750.00', 'cancelled'),
(3, 4, '2026-06-21 23:27:08', '750.00', 'cancelled');

-- --------------------------------------------------------

--
-- Структура таблицы `order_composition`
--

CREATE TABLE `order_composition` (
  `compound_id` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_case` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '1',
  `custom_design` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_composition`
--

INSERT INTO `order_composition` (`compound_id`, `id_order`, `id_case`, `count`, `custom_design`) VALUES
(1, 1, 8, 10, ''),
(2, 2, 2, 1, 'Устройство: Apple iPhone 15 Pro, Материал: Жёсткий пластик, Авторский дизайн «Мой дизайн», текст: вфцвфцв, со стикерами'),
(3, 3, 2, 1, '');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id_review` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id_review`, `user_id`, `case_id`, `rating`, `text`, `status`, `created_at`) VALUES
(1, 1, 1, 5, 'Очень крутой чехол, качество отличное! Пришёл быстро, упаковка аккуратная.', 'rejected', '2026-06-21 13:28:16'),
(2, 1, 3, 4, 'Выглядит ровно как на картинке. Силикон мягкий, не скользит. Доволен покупкой.', 'rejected', '2026-06-21 13:28:16'),
(3, 2, 3, 5, 'Спасибо за чехол', 'approved', '2026-06-21 20:03:04'),
(4, 3, 5, 5, 'Чехол просто миу-миу!!!', 'approved', '2026-06-21 20:03:41'),
(5, 4, 1, 5, 'ПРО100ТОП! СПАСИБО', 'approved', '2026-06-21 20:04:46'),
(6, 4, 8, 4, 'Отзыв крутой', 'pending', '2026-06-21 23:22:12');

-- --------------------------------------------------------

--
-- Структура таблицы `saved_designs`
--

CREATE TABLE `saved_designs` (
  `id_design` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT 'Мой дизайн',
  `design_data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_token` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `saved_designs`
--

INSERT INTO `saved_designs` (`id_design`, `user_id`, `case_id`, `title`, `design_data`, `share_token`, `created_at`) VALUES
(1, 2, 2, 'Мой дизайн', '{\"bgColor\":null,\"bgGradient\":\"linear-gradient(135deg,#f7971e,#ffd200)\",\"hasImage\":false,\"imageDataUrl\":null,\"texts\":[{\"val\":\"Владислав\",\"color\":\"rgb(255, 123, 0)\",\"size\":40,\"bold\":true,\"italic\":false,\"top\":4.45,\"left\":66.37},{\"val\":\"Крутой\",\"color\":\"rgb(120, 102, 255)\",\"size\":40,\"bold\":false,\"italic\":true,\"top\":36.81,\"left\":21.44},{\"val\":\"ПЕРЕЦ\",\"color\":\"rgb(102, 255, 209)\",\"size\":40,\"bold\":true,\"italic\":false,\"top\":74.21,\"left\":71.2}],\"stickers\":[{\"emoji\":\"🏆\",\"top\":12.91,\"left\":89.66},{\"emoji\":\"❤️\",\"top\":45.42,\"left\":8.05},{\"emoji\":\"🎯\",\"top\":86.98,\"left\":86.32}],\"modelId\":\"4\",\"materialId\":\"2\"}', '145882bfb29582124f4b5c7661aafd16', '2026-06-21 20:01:26'),
(2, 4, 2, 'Мой дизайн', '{\"bgColor\":\"#7B3B8C\",\"bgGradient\":null,\"hasImage\":false,\"imageDataUrl\":null,\"texts\":[{\"val\":\"вфцвфцв\",\"color\":\"rgb(255, 255, 255)\",\"size\":40,\"bold\":true,\"italic\":false,\"top\":6.09,\"left\":70.49}],\"stickers\":[{\"emoji\":\"🦋\",\"top\":45.13,\"left\":85.31},{\"emoji\":\"💎\",\"top\":94.57,\"left\":12.03}],\"modelId\":\"4\",\"materialId\":\"2\"}', '84cecefb8826134587d69a39f52a31a7', '2026-06-21 20:45:39'),
(3, 4, 8, 'Мой дизайн', '{\"bgColor\":null,\"bgGradient\":\"linear-gradient(135deg,#0f0c29,#302b63,#24243e)\",\"hasImage\":false,\"imageDataUrl\":null,\"texts\":[{\"val\":\"ВВВ\",\"color\":\"rgb(255, 0, 0)\",\"size\":40,\"bold\":true,\"italic\":false,\"top\":96.06,\"left\":13.15}],\"stickers\":[{\"emoji\":\"⭐\",\"top\":5.2,\"left\":93.24}],\"modelId\":\"7\",\"materialId\":\"5\"}', '71123c403e0202018f73b9df21fc117b', '2026-06-21 23:25:01');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id_user`, `login`, `email`, `password`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@isharlotka.ru', '$2y$10$lYT4JCBzubs00V9u9duao.G4N.gZqpL8uYcEPImHL3lnoqSfDjQ4i', 'Администратор', 'admin', '2026-06-21 13:28:16'),
(2, 'vlad123', 'bronnikovvd@gmail.com', '$2y$10$SPomKLpgVQPRvtjhJleyf.VwLOYA.VprGQm7B50Bj4ckCinVAIbrS', 'Владислав', 'user', '2026-06-21 19:56:36'),
(3, 'Nadya', 'Nadya@gmail.com', '$2y$10$ziUUlG2KI44bi8SBDmU6cu91FuXlsfqo85ZTXjguh7alc1WRPygue', 'Надежда', 'user', '2026-06-21 20:03:25'),
(4, 'Dima909', 'dima@gmail.com', '$2y$10$LWs9LEn/Et7tEVkU5bT39.CagJleSsfQ6.Mv1peunrNJtSnjw/wEW', 'Дмитрий', 'user', '2026-06-21 20:04:09');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cases_catalog`
--
ALTER TABLE `cases_catalog`
  ADD PRIMARY KEY (`id_case`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `collection_id` (`collection_id`);

--
-- Индексы таблицы `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id_collection`);

--
-- Индексы таблицы `device_models`
--
ALTER TABLE `device_models`
  ADD PRIMARY KEY (`id_model`);

--
-- Индексы таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id_fav`),
  ADD UNIQUE KEY `uniq_fav` (`user_id`,`case_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Индексы таблицы `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id_material`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `order_composition`
--
ALTER TABLE `order_composition`
  ADD PRIMARY KEY (`compound_id`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_case` (`id_case`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Индексы таблицы `saved_designs`
--
ALTER TABLE `saved_designs`
  ADD PRIMARY KEY (`id_design`),
  ADD UNIQUE KEY `share_token` (`share_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cases_catalog`
--
ALTER TABLE `cases_catalog`
  MODIFY `id_case` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `collections`
--
ALTER TABLE `collections`
  MODIFY `id_collection` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `device_models`
--
ALTER TABLE `device_models`
  MODIFY `id_model` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id_fav` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `materials`
--
ALTER TABLE `materials`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `order_composition`
--
ALTER TABLE `order_composition`
  MODIFY `compound_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `saved_designs`
--
ALTER TABLE `saved_designs`
  MODIFY `id_design` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cases_catalog`
--
ALTER TABLE `cases_catalog`
  ADD CONSTRAINT `cases_catalog_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id_material`) ON DELETE SET NULL,
  ADD CONSTRAINT `cases_catalog_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `device_models` (`id_model`) ON DELETE SET NULL,
  ADD CONSTRAINT `cases_catalog_ibfk_3` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id_collection`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases_catalog` (`id_case`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_composition`
--
ALTER TABLE `order_composition`
  ADD CONSTRAINT `order_composition_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_composition_ibfk_2` FOREIGN KEY (`id_case`) REFERENCES `cases_catalog` (`id_case`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases_catalog` (`id_case`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `saved_designs`
--
ALTER TABLE `saved_designs`
  ADD CONSTRAINT `saved_designs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_designs_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases_catalog` (`id_case`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
