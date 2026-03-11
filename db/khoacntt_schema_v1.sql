CREATE TABLE `accounts` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) UNIQUE,
  `password_hash` varchar(500),
  `role` enum('student','teacher','admin'),
  `updated_at` datetime,
  `created_at` datetime,
  `deleted_at` datetime
);

CREATE TABLE `teachers` (
  `account_id` bigint PRIMARY KEY,
  `full_name` varchar(255),
  `gender` enum('male','female'),
  `dob` date,
  `phone` varchar(15),
  `degree` varchar(150),
  `title` varchar(150),
  `department` varchar(150),
  `start_date` date
);

CREATE TABLE `students` (
  `account_id` bigint PRIMARY KEY,
  `student_id` varchar(10) UNIQUE,
  `full_name` varchar(255),
  `gender` enum('male','female'),
  `dob` date,
  `phone` varchar(15),
  `classroom_id` bigint,
  `major` varchar(150),
  `birth_place` varchar(255)
);

CREATE TABLE `classrooms` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(50)
);

ALTER TABLE `students` ADD FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

ALTER TABLE `teachers` ADD FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

ALTER TABLE `students` ADD FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`);
