-- =====================================================================
-- Lostly database (MySQL / MariaDB)
-- Import this file in phpMyAdmin: create a database called "lostly",
-- open it, choose Import, pick this file and press Go.
-- WARNING: it replaces the Lostly tables and their data.
-- Demo accounts: admin / admin123 and rana, mitu, Didar, sumaiya, tanvir / lostly123
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+06:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admin_log, contact_messages, flags, search_alerts, saved_items, notifications,
                     comments, messages, claims, item_images, items, users, settings;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Username varchar(100) NOT NULL,
  Email varchar(150) NOT NULL,
  Phone varchar(20) NOT NULL,
  Division varchar(50) NOT NULL,
  District varchar(100) NOT NULL,
  Password varchar(255) NOT NULL,
  Role enum('user','admin') NOT NULL DEFAULT 'user',
  IsBanned tinyint(1) NOT NULL DEFAULT 0,
  ShowContact tinyint(1) NOT NULL DEFAULT 1,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  UNIQUE KEY uq_users_username (Username),
  UNIQUE KEY uq_users_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE items (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL,
  Type enum('Lost','Found') NOT NULL,
  ItemName varchar(100) NOT NULL,
  Category varchar(50) NOT NULL,
  Description text DEFAULT NULL,
  Division varchar(50) NOT NULL,
  District varchar(50) NOT NULL,
  EventDate date NOT NULL,
  ImagePath varchar(255) DEFAULT NULL,
  Status enum('Active','Resolved') NOT NULL DEFAULT 'Active',
  Views int(11) NOT NULL DEFAULT 0,
  Reward int(10) unsigned DEFAULT NULL,
  ResolvedNote varchar(500) DEFAULT NULL,
  ResolvedAt datetime DEFAULT NULL,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  KEY idx_items_user (UserID),
  KEY idx_items_type_status (Type, Status),
  KEY idx_items_division (Division),
  CONSTRAINT fk_items_user FOREIGN KEY (UserID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Extra photos (the first photo is items.ImagePath)
CREATE TABLE item_images (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ItemID int(11) NOT NULL,
  Path varchar(255) NOT NULL,
  PRIMARY KEY (ID),
  KEY idx_images_item (ItemID),
  CONSTRAINT fk_images_item FOREIGN KEY (ItemID) REFERENCES items (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- "This is mine" / "I found it" requests
CREATE TABLE claims (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ItemID int(11) NOT NULL,
  ClaimantID int(11) NOT NULL,
  Message text NOT NULL,
  Status enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  UNIQUE KEY uq_claim_item_user (ItemID, ClaimantID),
  KEY idx_claims_user (ClaimantID),
  CONSTRAINT fk_claims_item FOREIGN KEY (ItemID) REFERENCES items (ID) ON DELETE CASCADE,
  CONSTRAINT fk_claims_user FOREIGN KEY (ClaimantID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Private chat between two members about one report
CREATE TABLE messages (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ItemID int(11) NOT NULL,
  SenderID int(11) NOT NULL,
  ReceiverID int(11) NOT NULL,
  Body text NOT NULL,
  IsRead tinyint(1) NOT NULL DEFAULT 0,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  KEY idx_messages_receiver (ReceiverID, IsRead),
  KEY idx_messages_item (ItemID),
  CONSTRAINT fk_messages_item FOREIGN KEY (ItemID) REFERENCES items (ID) ON DELETE CASCADE,
  CONSTRAINT fk_messages_sender FOREIGN KEY (SenderID) REFERENCES users (ID) ON DELETE CASCADE,
  CONSTRAINT fk_messages_receiver FOREIGN KEY (ReceiverID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Public sightings & tips on a report
CREATE TABLE comments (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ItemID int(11) NOT NULL,
  UserID int(11) NOT NULL,
  Body text NOT NULL,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  KEY idx_comments_item (ItemID),
  CONSTRAINT fk_comments_item FOREIGN KEY (ItemID) REFERENCES items (ID) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (UserID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE notifications (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL,
  Type varchar(20) NOT NULL,
  Body varchar(500) NOT NULL,
  Link varchar(255) NOT NULL,
  IsRead tinyint(1) NOT NULL DEFAULT 0,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  KEY idx_notifications_user (UserID, IsRead),
  CONSTRAINT fk_notifications_user FOREIGN KEY (UserID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE saved_items (
  UserID int(11) NOT NULL,
  ItemID int(11) NOT NULL,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (UserID, ItemID),
  KEY idx_saved_item (ItemID),
  CONSTRAINT fk_saved_user FOREIGN KEY (UserID) REFERENCES users (ID) ON DELETE CASCADE,
  CONSTRAINT fk_saved_item FOREIGN KEY (ItemID) REFERENCES items (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- "Alert me about new reports like this"
CREATE TABLE search_alerts (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL,
  Keyword varchar(100) NOT NULL DEFAULT '',
  Type varchar(10) NOT NULL DEFAULT '',
  Category varchar(50) NOT NULL DEFAULT '',
  Division varchar(50) NOT NULL DEFAULT '',
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  KEY idx_alerts_user (UserID),
  CONSTRAINT fk_alerts_user FOREIGN KEY (UserID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Posts reported to the admins
CREATE TABLE flags (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ItemID int(11) NOT NULL,
  UserID int(11) NOT NULL,
  Reason varchar(255) NOT NULL,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID),
  UNIQUE KEY uq_flag_item_user (ItemID, UserID),
  CONSTRAINT fk_flags_item FOREIGN KEY (ItemID) REFERENCES items (ID) ON DELETE CASCADE,
  CONSTRAINT fk_flags_user FOREIGN KEY (UserID) REFERENCES users (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE contact_messages (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Name varchar(100) NOT NULL,
  Email varchar(150) NOT NULL,
  Subject varchar(150) NOT NULL,
  Body text NOT NULL,
  IsRead tinyint(1) NOT NULL DEFAULT 0,
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE admin_log (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) DEFAULT NULL,
  Action varchar(100) NOT NULL,
  Detail varchar(500) NOT NULL DEFAULT '',
  CreatedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE settings (
  Name varchar(50) NOT NULL,
  Value text NOT NULL,
  PRIMARY KEY (Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- Demo data (dates are relative to the day you import, so it always looks fresh)
-- ---------------------------------------------------------------------

INSERT INTO users (ID, Username, Email, Phone, Division, District, Password, Role, CreatedAt) VALUES
(1, 'admin', 'admin@lostly.local', '01700000000', 'Dhaka', 'Dhaka', '$2y$10$EIny5G0bAPs6JvbAHiOJf.ugUYFvXkCDUHVYbwhF46HCIYDTsiGW6', 'admin', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 120 DAY), INTERVAL -10 HOUR)),
(2, 'rana', 'rana@example.com', '01711223344', 'Dhaka', 'Dhanmondi', '$2y$10$HHbxVp0p2gLCPFwI7dxZWuFI3D37znFFjTVfhzJEv.HvTQGqFAK3a', 'user', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 90 DAY), INTERVAL -10 HOUR)),
(3, 'mitu', 'mitu@example.com', '01811223344', 'Chattogram', 'Chattogram', '$2y$10$HHbxVp0p2gLCPFwI7dxZWuFI3D37znFFjTVfhzJEv.HvTQGqFAK3a', 'user', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 75 DAY), INTERVAL -10 HOUR)),
(4, 'Didar', 'didar@example.com', '01911031925', 'Dhaka', 'Mirpur', '$2y$10$HHbxVp0p2gLCPFwI7dxZWuFI3D37znFFjTVfhzJEv.HvTQGqFAK3a', 'user', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 60 DAY), INTERVAL -10 HOUR)),
(5, 'sumaiya', 'sumaiya@example.com', '01611223344', 'Sylhet', 'Sylhet', '$2y$10$HHbxVp0p2gLCPFwI7dxZWuFI3D37znFFjTVfhzJEv.HvTQGqFAK3a', 'user', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 40 DAY), INTERVAL -10 HOUR)),
(6, 'tanvir', 'tanvir@example.com', '01511223344', 'Rajshahi', 'Rajshahi', '$2y$10$HHbxVp0p2gLCPFwI7dxZWuFI3D37znFFjTVfhzJEv.HvTQGqFAK3a', 'user', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 30 DAY), INTERVAL -10 HOUR));

INSERT INTO items (ID, UserID, Type, ItemName, Category, Description, Division, District, EventDate, Status, Views, Reward, ResolvedNote, ResolvedAt, CreatedAt) VALUES
(1, 2, 'Lost', 'Black leather wallet', 'Bags & Wallets', 'Black leather wallet with my NID card, two bank cards and a small photo of my daughter. Lost near Dhanmondi Lake, road 8, in the evening.', 'Dhaka', 'Dhanmondi', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Active', 48, 2000, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL -12 HOUR)),
(2, 4, 'Found', 'Leather wallet, black', 'Bags & Wallets', 'Found a black wallet with an NID card inside on a bench by Dhanmondi Lake. Describe the photo inside to claim it.', 'Dhaka', 'Dhanmondi', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Active', 31, NULL, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -13 HOUR)),
(3, 4, 'Lost', 'Grey cat called Tom', 'Pets', 'Grey tabby cat with a blue collar and a small bell. Answers to "Tom". Ran out of the gate on road 10, Mirpur 2.', 'Dhaka', 'Mirpur', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Active', 112, 5000, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 4 DAY), INTERVAL -13 HOUR)),
(4, 3, 'Found', 'Key ring with 3 keys', 'Keys', 'Three keys on a red key ring with a small plastic tag, found near GEC Circle on the footpath.', 'Chattogram', 'Chattogram', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Active', 22, NULL, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL -13 HOUR)),
(5, 6, 'Lost', 'Blue Samsung Galaxy phone', 'Electronics', 'Blue Samsung Galaxy A54 with a cracked top corner and a clear case. Lost on a BRTC bus from Gazipur to Farmgate.', 'Dhaka', 'Gazipur', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Active', 64, 3000, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 5 DAY), INTERVAL -10 HOUR)),
(6, 5, 'Found', 'Bangladeshi passport', 'Documents', 'Green Bangladeshi passport found at Osmani airport arrivals hall. I will hand it over with proof of name.', 'Sylhet', 'Sylhet', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Active', 57, NULL, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 6 DAY), INTERVAL -12 HOUR)),
(7, 2, 'Lost', 'Gold jhumka earring', 'Jewelry', 'One gold jhumka earring with small red stones, lost at a wedding at Khulna Club on Friday night.', 'Khulna', 'Khulna', DATE_SUB(CURDATE(), INTERVAL 9 DAY), 'Active', 19, 1500, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 9 DAY), INTERVAL -10 HOUR)),
(8, 6, 'Found', 'Navy blue school bag', 'Bags & Wallets', 'Navy blue school bag with class 7 books and a pencil box, found at the Rajshahi University bus stop.', 'Rajshahi', 'Rajshahi', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Active', 15, NULL, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL -15 HOUR)),
(9, 4, 'Lost', 'Dell laptop charger', 'Electronics', 'Dell 65W laptop charger with a black cable, left in a CNG near Karwan Bazar.', 'Dhaka', 'Dhaka', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Active', 9, NULL, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -9 HOUR)),
(10, 3, 'Found', 'Blue denim jacket', 'Clothing', 'Blue denim jacket, size M, left on a chair at a café on Satmasjid Road.', 'Dhaka', 'Dhanmondi', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Active', 12, NULL, NULL, NULL, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL -12 HOUR)),
(11, 2, 'Lost', 'NID card', 'Documents', 'National ID card lost somewhere between Mirpur 10 and Kazipara.', 'Dhaka', 'Mirpur', DATE_SUB(CURDATE(), INTERVAL 16 DAY), 'Resolved', 88, NULL, 'A rickshaw puller found it and called me the same evening. Thank you, Lostly!', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 13 DAY), INTERVAL -18 HOUR), DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 16 DAY), INTERVAL -16 HOUR)),
(12, 3, 'Found', 'Brown puppy', 'Pets', 'Small brown puppy with a white patch on its chest, found on the beach road near Kolatoli.', 'Chattogram', 'Cox\'s Bazar', DATE_SUB(CURDATE(), INTERVAL 21 DAY), 'Resolved', 140, NULL, 'The owner\'s family came the next morning. He was so happy to see them again.', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 18 DAY), INTERVAL -18 HOUR), DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 21 DAY), INTERVAL -14 HOUR)),
(13, 6, 'Lost', 'Red Phoenix bicycle', 'Other', 'Red Phoenix bicycle with a black basket, taken from outside Barishal BM College.', 'Barishal', 'Barishal', DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Resolved', 73, 2500, 'Someone saw the report and spotted it outside a school. Got it back!', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 24 DAY), INTERVAL -18 HOUR), DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 30 DAY), INTERVAL -10 HOUR));

INSERT INTO claims (ID, ItemID, ClaimantID, Message, Status, CreatedAt) VALUES
(1, 1, 4, 'I found it by the lake. It has an NID card in the name of Rana and a photo of a little girl in a yellow dress.', 'Pending', DATE_SUB(NOW(), INTERVAL 144 MINUTE)),
(2, 11, 3, 'I have it. The card was inside a plastic sleeve with a bKash receipt.', 'Approved', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 14 DAY), INTERVAL -10 HOUR));

INSERT INTO comments (ID, ItemID, UserID, Body, CreatedAt) VALUES
(1, 3, 5, 'I saw a grey cat with a blue collar near the Mirpur 10 roundabout on Saturday evening.', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL -20 HOUR)),
(2, 3, 4, 'Thank you! I\'ll go and look there tonight.', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -20 HOUR)),
(3, 5, 2, 'Ask at the BRTC Farmgate counter. They keep a lost property box.', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL -20 HOUR));

INSERT INTO messages (ID, ItemID, SenderID, ReceiverID, Body, IsRead, CreatedAt) VALUES
(1, 1, 4, 2, 'Assalamu alaikum! I think I found your wallet near the lake. Can you tell me what the photo inside looks like?', 1, DATE_SUB(NOW(), INTERVAL 288 MINUTE)),
(2, 1, 2, 4, 'Walaikum assalam! It\'s my daughter in a yellow dress. Thank you so much!', 1, DATE_SUB(NOW(), INTERVAL 215 MINUTE)),
(3, 1, 4, 2, 'That\'s the one. I can meet you at the lake gate on road 8 tomorrow at 5pm.', 0, DATE_SUB(NOW(), INTERVAL 144 MINUTE));

INSERT INTO notifications (ID, UserID, Type, Body, Link, CreatedAt) VALUES
(1, 2, 'match', 'A found report “Leather wallet, black” may match your lost report “Black leather wallet”.', 'item_detail.php?id=2', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -10 HOUR)),
(2, 4, 'match', 'A lost report “Black leather wallet” may match your found report “Leather wallet, black”.', 'item_detail.php?id=1', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -10 HOUR)),
(3, 2, 'claim', 'Didar sent a claim on “Black leather wallet”.', 'item_detail.php?id=1#claims', DATE_SUB(NOW(), INTERVAL 144 MINUTE)),
(4, 4, 'tip', 'sumaiya left a tip on “Grey cat called Tom”.', 'item_detail.php?id=3#tips', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL -10 HOUR));

INSERT INTO saved_items (UserID, ItemID, CreatedAt) VALUES
(2, 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -10 HOUR));

INSERT INTO search_alerts (ID, UserID, Keyword, Type, Category, Division, CreatedAt) VALUES
(1, 2, 'earring', 'Found', 'Jewelry', '', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL -10 HOUR));

INSERT INTO flags (ID, ItemID, UserID, Reason, CreatedAt) VALUES
(1, 10, 6, 'Looks like the same jacket was posted twice this week.', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -15 HOUR));

INSERT INTO contact_messages (ID, Name, Email, Subject, Body, IsRead, CreatedAt) VALUES
(1, 'Nusrat', 'nusrat@example.com', 'Can I post a lost pet from Rangpur?', 'Hello, my parrot flew away in Rangpur town. Can I post it here even if I don\'t have a photo?', 0, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -11 HOUR));

INSERT INTO admin_log (ID, UserID, Action, Detail, CreatedAt) VALUES
(1, 1, 'Dismissed flag', 'Flag on “Brown puppy”', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 17 DAY), INTERVAL -10 HOUR));

INSERT INTO settings (Name, Value) VALUES ('announcement', '');
