-- Hand2Hand: Community Aid Request & Donation Management System
-- Database: hand2hand

CREATE DATABASE IF NOT EXISTS hand2hand;
USE hand2hand;

-- USER table
CREATE TABLE IF NOT EXISTS USER (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Requester', 'Donor', 'Admin') NOT NULL
);

-- ITEM table
CREATE TABLE IF NOT EXISTS ITEM (
    item_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT
);

-- DONATIONEVENT table
CREATE TABLE IF NOT EXISTS DONATIONEVENT (
    event_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    location VARCHAR(200) NOT NULL,
    status ENUM('Active', 'Completed', 'Cancelled') NOT NULL
);

-- TARGET table
CREATE TABLE IF NOT EXISTS TARGET (
    target_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    item_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES DONATIONEVENT(event_id),
    FOREIGN KEY (item_id) REFERENCES ITEM(item_id)
);

-- REQUEST table
CREATE TABLE IF NOT EXISTS REQUEST (
    request_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    description TEXT,
    user_id INT(11) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES USER(user_id)
);

-- INVENTORY table
CREATE TABLE IF NOT EXISTS INVENTORY (
    inventory_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    item_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 0,
    FOREIGN KEY (item_id) REFERENCES ITEM(item_id)
);

-- DONATION table
CREATE TABLE IF NOT EXISTS DONATION (
    donation_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    date DATE NOT NULL,
    status ENUM('Pending', 'Received', 'Cancelled') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (event_id) REFERENCES DONATIONEVENT(event_id),
    FOREIGN KEY (user_id) REFERENCES USER(user_id)
);

-- DONATION_ITEM table
CREATE TABLE IF NOT EXISTS DONATION_ITEM (
    donation_id INT(11) NOT NULL,
    item_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (donation_id, item_id),
    FOREIGN KEY (donation_id) REFERENCES DONATION(donation_id),
    FOREIGN KEY (item_id) REFERENCES ITEM(item_id)
);

-- DISTRIBUTION table
CREATE TABLE IF NOT EXISTS DISTRIBUTION (
    distribution_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    request_id INT(11) NOT NULL,
    item_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (request_id) REFERENCES REQUEST(request_id),
    FOREIGN KEY (item_id) REFERENCES ITEM(item_id)
);

-- Default Admin account (password: admin123)
INSERT INTO USER (email, username, password, role) VALUES
('admin@hand2hand.com', 'Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');

-- Sample items
INSERT INTO ITEM (name, category, description) VALUES
('Rice (5kg)', 'Food', 'White rice, 5kg bag'),
('Cooking Oil (1L)', 'Food', '1 litre cooking oil'),
('Sugar (1kg)', 'Food', '1kg white sugar'),
('School Bag', 'School Supplies', 'Standard school backpack'),
('Exercise Book', 'School Supplies', 'Pack of 10 exercise books'),
('T-Shirt', 'Clothing', 'General clothing item'),
('Pants', 'Clothing', 'General clothing item'),
('Toothpaste', 'Toiletries', 'Standard toothpaste'),
('Shampoo', 'Toiletries', 'Standard shampoo bottle'),
('Baby Diapers', 'Baby Items', 'Pack of diapers');

-- Sample inventory
INSERT INTO INVENTORY (item_id, quantity) VALUES
(1, 50), (2, 30), (3, 40), (4, 10), (5, 20),
(6, 25), (7, 15), (8, 35), (9, 20), (10, 12);

ALTER TABLE DONATIONEVENT
    ADD COLUMN start_date DATE NOT NULL AFTER name,
    ADD COLUMN end_date DATE NOT NULL AFTER start_date,
    DROP COLUMN date,
    DROP COLUMN location;

INSERT INTO DONATIONEVENT (name, start_date, end_date, status) VALUES
('Food Bank', '2026-05-01', '2026-06-30', 'Active'),
('Back To School', '2026-05-01', '2026-06-30', 'Active'),
('Baby Care', '2026-05-01', '2026-06-30', 'Active'),
('Her Essentials', '2026-05-01', '2026-06-30', 'Active'),
('Medical Aid', '2026-05-01', '2026-06-30', 'Active'),
('Wear & Share', '2026-05-01', '2026-06-30', 'Active');

INSERT INTO TARGET (event_id, item_id, quantity) VALUES
(1, 1, 100), 
(1, 2, 60),  
(1, 3, 80),
(2, 4, 20),  
(2, 5, 40), 
(3, 10, 10), 
(4, 9, 3),  
(5, 8, 70),
(6, 6, 98), 
(6, 7, 40);  

ALTER TABLE DONATION_ITEM 
ADD quantity INT(11) NOT NULL DEFAULT 1;

INSERT INTO donation (event_id, user_id, date, status) VALUES
(1, 1, '2026-05-10', 'Received'),
(1, 1, '2026-05-15', 'Received'),
(2, 1, '2026-05-20', 'Received');

INSERT INTO donation_item (donation_id, item_id, quantity) VALUES
(1, 1, 40),
(1, 2, 20),
(2, 1, 25),
(3, 4, 10);

ALTER TABLE user ADD COLUMN family_size INT DEFAULT NULL;
ALTER TABLE user ADD COLUMN priority_level ENUM('Low','Medium','High') DEFAULT NULL;

UPDATE user SET family_size = 4, priority_level = 'High' WHERE user_id = 3;

ALTER TABLE DONATIONEVENT
MODIFY COLUMN status ENUM('Active', 'Completed', 'Scheduled') NOT NULL;

ALTER TABLE DONATIONEVENT ADD COLUMN image_path VARCHAR(255) DEFAULT NULL;

UPDATE DONATIONEVENT SET image_path = 'food.webp' WHERE name = 'Food Bank';
UPDATE DONATIONEVENT SET image_path = 'school.webp' WHERE name = 'Back To School';
UPDATE DONATIONEVENT SET image_path = 'baby.jpg' WHERE name = 'Baby Care';
UPDATE DONATIONEVENT SET image_path = 'women.jpg' WHERE name = 'Her Essentials';
UPDATE DONATIONEVENT SET image_path = 'medical.jpg' WHERE name = 'Medical Aid';
UPDATE DONATIONEVENT SET image_path = 'clothes.jpg' WHERE name = 'Wear & Share';