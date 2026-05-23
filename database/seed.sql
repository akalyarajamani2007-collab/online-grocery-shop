USE grocery_db;

INSERT IGNORE INTO admins (name, email, password) VALUES
('Admin User', 'admin@example.com', '$2y$10$gCZ5tYW5eD9Dl5bJ5vc6W.vskehIrHmb33e2J7RdXU49htTflSMH2');

INSERT IGNORE INTO users (name, email, mobile, address, password) VALUES
('Anu', 'anu@example.com', '9876543210', '123 Green Lane, City', '$2y$10$KbQ9bMzRzKf7hQ1Kx9bZKuZL5P8rN2XI4s6fG7wTo7w9uYvG93dS6');

INSERT IGNORE INTO categories (name, description, status) VALUES
('Oils', 'Cooking oils and edible oils.', 'active'),
('Fruits', 'Fresh fruits and berries.', 'active'),
('Vegetables', 'Daily vegetables and greens.', 'active');

INSERT IGNORE INTO items (category_id, name, slug, description, image, price, stock_qty, status) VALUES
(1, 'Cooking Oil', 'cooking-oil', 'Premium edible cooking oil for everyday use.', 'default.svg', 179.99, 25, 'available'),
(2, 'Fresh Apples', 'fresh-apples', 'Crisp and juicy apples sourced locally.', 'default.svg', 119.50, 40, 'available'),
(3, 'Organic Spinach', 'organic-spinach', 'Healthy organic spinach full of vitamins.', 'default.svg', 49.00, 60, 'available'),
(3, 'Red Onion', 'red-onion', 'Fresh red onions for your everyday cooking.', 'default.svg', 39.50, 55, 'available'),
(2, 'Banana Bunch', 'banana-bunch', 'Sweet bananas perfect for breakfast and snacks.', 'default.svg', 59.99, 50, 'available');
