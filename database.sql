CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price INT NOT NULL DEFAULT 0,
  cost_price INT NOT NULL DEFAULT 0,
  status ENUM('active','deleted') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  address TEXT NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  product_price INT NOT NULL DEFAULT 0,
  product_cost INT NOT NULL DEFAULT 0,
  profit INT NOT NULL DEFAULT 0,
  shipping INT NOT NULL DEFAULT 0,
  total INT NOT NULL DEFAULT 0,
  note TEXT NULL,
  payment ENUM('Belum Dipilih','Cash','QRIS','Transfer') NOT NULL DEFAULT 'Belum Dipilih',
  delivery ENUM('Belum Dikirim','Sedang Dikirim','Sudah Dikirim') NOT NULL DEFAULT 'Belum Dikirim',
  acc_status TINYINT(1) NOT NULL DEFAULT 0,
  deleted_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO users (username, full_name, role) VALUES
('admin','Admin Utama','admin'),
('andi','Andi','user'),
('sinta','Sinta','user');

INSERT INTO products (name, price, cost_price) VALUES
('Donat',5000,3000),
('Roti',7000,4500),
('Es Teh',3000,1500);
