ALTER TABLE products ADD COLUMN cost_price INT NOT NULL DEFAULT 0 AFTER price;
ALTER TABLE orders ADD COLUMN product_cost INT NOT NULL DEFAULT 0 AFTER product_price;
ALTER TABLE orders ADD COLUMN profit INT NOT NULL DEFAULT 0 AFTER product_cost;
UPDATE orders SET profit = (product_price - product_cost) * qty WHERE profit = 0;
