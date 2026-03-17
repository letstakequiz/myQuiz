-- Updevix Quiz Platform Database Schema
-- Version: 1.0
-- Compatible with MySQL 5.7+ / MariaDB 10.3+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `updevix_quiz` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `updevix_quiz`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: admin
-- --------------------------------------------------------
CREATE TABLE `admin` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: Admin@123)
INSERT INTO `admin` (`username`, `email`, `password`, `full_name`) VALUES
('admin', 'admin@updevix.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin');

-- --------------------------------------------------------
-- Table: quizzes
-- --------------------------------------------------------
CREATE TABLE `quizzes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `duration_minutes` INT(11) NOT NULL DEFAULT 30,
  `total_marks` INT(11) NOT NULL DEFAULT 0,
  `passing_marks` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_randomized` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_created_by` (`created_by`),
  CONSTRAINT `fk_quiz_admin` FOREIGN KEY (`created_by`) REFERENCES `admin`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: questions
-- --------------------------------------------------------
CREATE TABLE `questions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` INT(11) NOT NULL,
  `question_text` TEXT NOT NULL,
  `question_type` ENUM('mcq', 'aptitude', 'coding') NOT NULL DEFAULT 'mcq',
  `option_a` VARCHAR(500) DEFAULT NULL,
  `option_b` VARCHAR(500) DEFAULT NULL,
  `option_c` VARCHAR(500) DEFAULT NULL,
  `option_d` VARCHAR(500) DEFAULT NULL,
  `correct_answer` VARCHAR(500) NOT NULL,
  `marks` INT(11) NOT NULL DEFAULT 1,
  `coding_language` VARCHAR(50) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_quiz_id` (`quiz_id`),
  INDEX `idx_question_type` (`question_type`),
  CONSTRAINT `fk_question_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: results
-- --------------------------------------------------------
CREATE TABLE `results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `quiz_id` INT(11) NOT NULL,
  `total_questions` INT(11) NOT NULL DEFAULT 0,
  `correct_answers` INT(11) NOT NULL DEFAULT 0,
  `wrong_answers` INT(11) NOT NULL DEFAULT 0,
  `skipped_questions` INT(11) NOT NULL DEFAULT 0,
  `total_marks` INT(11) NOT NULL DEFAULT 0,
  `obtained_marks` INT(11) NOT NULL DEFAULT 0,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `time_taken_seconds` INT(11) DEFAULT NULL,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_quiz_id` (`quiz_id`),
  CONSTRAINT `fk_result_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_result_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: answers (individual question answers per attempt)
-- --------------------------------------------------------
CREATE TABLE `answers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `result_id` INT(11) NOT NULL,
  `question_id` INT(11) NOT NULL,
  `user_answer` TEXT DEFAULT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_result_id` (`result_id`),
  INDEX `idx_question_id` (`question_id`),
  CONSTRAINT `fk_answer_result` FOREIGN KEY (`result_id`) REFERENCES `results`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_answer_question` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: otp_verifications
-- --------------------------------------------------------
CREATE TABLE `otp_verifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `purpose` ENUM('password_reset', 'login_verify') NOT NULL DEFAULT 'password_reset',
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_otp_code` (`otp_code`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Sample Data: Insert a sample quiz with questions
-- --------------------------------------------------------
INSERT INTO `quizzes` (`title`, `description`, `duration_minutes`, `total_marks`, `passing_marks`, `is_active`, `is_randomized`, `created_by`) VALUES
('General Aptitude Test', 'Test your aptitude skills with this comprehensive quiz covering logical reasoning, quantitative analysis, and verbal ability.', 30, 10, 5, 1, 1, 1),
('Java Programming Fundamentals', 'Assess your knowledge of Java programming concepts including OOP, data structures, and core Java features.', 45, 15, 8, 1, 0, 1),
('Python Basics Quiz', 'Evaluate your understanding of Python programming fundamentals, syntax, and basic problem solving.', 20, 10, 5, 1, 1, 1);

-- Sample MCQ Questions for General Aptitude Test (quiz_id = 1)
INSERT INTO `questions` (`quiz_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `marks`, `sort_order`) VALUES
(1, 'If 2x + 3 = 15, what is the value of x?', 'mcq', '4', '5', '6', '7', 'C', 1, 1),
(1, 'Which number comes next in the series: 2, 6, 12, 20, ?', 'mcq', '28', '30', '32', '24', 'B', 1, 2),
(1, 'A train travels 360 km in 4 hours. What is its speed in km/h?', 'aptitude', '80', '90', '100', '70', 'B', 1, 3),
(1, 'If the ratio of boys to girls in a class is 3:2 and there are 30 boys, how many girls are there?', 'mcq', '15', '20', '25', '10', 'B', 1, 4),
(1, 'Complete the analogy: Book is to Reading as Fork is to ___', 'mcq', 'Writing', 'Eating', 'Drawing', 'Playing', 'B', 1, 5),
(1, 'What is the next prime number after 13?', 'mcq', '15', '17', '19', '21', 'B', 1, 6),
(1, 'A shopkeeper sells an item for Rs.450 at 10% profit. What was the cost price?', 'aptitude', 'Rs.400', 'Rs.405', 'Rs.409.09', 'Rs.410', 'C', 1, 7),
(1, 'Find the odd one out: 3, 5, 11, 14, 17, 21', 'mcq', '__(3)', '14', '__(17)', '21', 'B', 1, 8),
(1, 'If ROSE is coded as 6821, how is EARS coded?', 'mcq', '__(1862)', '__(2168)', '1286', '__(2186)', 'C', 1, 9),
(1, 'What percentage of 250 is 45?', 'aptitude', '__(16%)', '18%', '20%', '22%', 'B', 1, 10);

-- Sample Java Questions (quiz_id = 2)
INSERT INTO `questions` (`quiz_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `marks`, `coding_language`, `sort_order`) VALUES
(2, 'Which keyword is used to create a class in Java?', 'mcq', 'struct', 'class', 'object', 'define', 'B', 1, 'Java', 1),
(2, 'What is the default value of an int variable in Java?', 'mcq', '1', '-1', '0', 'null', 'C', 1, 'Java', 2),
(2, 'Which method is the entry point of a Java program?', 'mcq', 'start()', 'init()', 'main()', 'run()', 'C', 1, 'Java', 3),
(2, 'What does JVM stand for?', 'mcq', 'Java Variable Machine', 'Java Virtual Machine', 'Java Visual Machine', 'Java Verified Machine', 'B', 1, 'Java', 4),
(2, 'Which data type is used to store a single character in Java?', 'mcq', 'String', 'char', 'Character', 'chr', 'B', 1, 'Java', 5),
(2, 'Write a Java method that takes an integer array and returns the sum of all elements.', 'coding', NULL, NULL, NULL, NULL, 'public static int sumArray(int[] arr) { int sum = 0; for (int n : arr) sum += n; return sum; }', 2, 'Java', 6),
(2, 'What is the output of: System.out.println(10 + 20 + "Hello");', 'mcq', '1020Hello', '30Hello', 'Hello1020', 'Hello30', 'B', 1, 'Java', 7),
(2, 'Which OOP principle allows a class to inherit from another class?', 'mcq', 'Encapsulation', 'Polymorphism', 'Inheritance', 'Abstraction', 'C', 1, 'Java', 8),
(2, 'What is the size of int data type in Java?', 'mcq', '2 bytes', '4 bytes', '8 bytes', '16 bytes', 'B', 1, 'Java', 9),
(2, 'Write a Java program to check if a given string is a palindrome.', 'coding', NULL, NULL, NULL, NULL, 'public static boolean isPalindrome(String s) { return s.equals(new StringBuilder(s).reverse().toString()); }', 2, 'Java', 10),
(2, 'Which collection class in Java allows duplicate elements?', 'mcq', 'HashSet', 'TreeSet', 'ArrayList', 'LinkedHashSet', 'C', 1, 'Java', 11),
(2, 'What is method overloading in Java?', 'mcq', 'Same method name with different parameters', 'Same method name with same parameters', 'Different method name with same parameters', 'None of the above', 'A', 1, 'Java', 12),
(2, 'Which keyword is used to prevent method overriding?', 'mcq', 'static', 'final', 'private', 'abstract', 'B', 1, 'Java', 13),
(2, 'Write a Java method to find the factorial of a number using recursion.', 'coding', NULL, NULL, NULL, NULL, 'public static long factorial(int n) { if (n <= 1) return 1; return n * factorial(n - 1); }', 2, 'Java', 14),
(2, 'What is the parent class of all classes in Java?', 'mcq', 'Object', 'Class', 'Super', 'Base', 'A', 1, 'Java', 15);

-- Sample Python Questions (quiz_id = 3)
INSERT INTO `questions` (`quiz_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `marks`, `coding_language`, `sort_order`) VALUES
(3, 'Which keyword is used to define a function in Python?', 'mcq', 'function', 'def', 'func', 'define', 'B', 1, 'Python', 1),
(3, 'What is the output of print(type(5))?', 'mcq', '<class ''float''>', '<class ''int''>', '<class ''str''>', '<class ''number''>', 'B', 1, 'Python', 2),
(3, 'Which data structure in Python is immutable?', 'mcq', 'List', 'Dictionary', 'Set', 'Tuple', 'D', 1, 'Python', 3),
(3, 'What does len() function do?', 'mcq', 'Returns the length', 'Returns the type', 'Returns the sum', 'Returns the max', 'A', 1, 'Python', 4),
(3, 'Write a Python function to reverse a string.', 'coding', NULL, NULL, NULL, NULL, 'def reverse_string(s): return s[::-1]', 2, 'Python', 5),
(3, 'Which operator is used for floor division in Python?', 'mcq', '/', '//', '%', '**', 'B', 1, 'Python', 6),
(3, 'What is the output of print(2**3)?', 'mcq', '6', '8', '9', '5', 'B', 1, 'Python', 7),
(3, 'Which method is used to add an element to a list?', 'mcq', 'add()', 'insert()', 'append()', 'push()', 'C', 1, 'Python', 8),
(3, 'Write a Python function that checks if a number is even.', 'coding', NULL, NULL, NULL, NULL, 'def is_even(n): return n % 2 == 0', 2, 'Python', 9),
(3, 'What is PEP 8?', 'mcq', 'A Python library', 'A Python style guide', 'A Python IDE', 'A Python compiler', 'B', 1, 'Python', 10);
