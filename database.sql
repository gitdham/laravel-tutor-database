CREATE TABLE categories(
  id VARCHAR(100) not NULL primary key,
  name VARCHAR(100) not NULL,
  description text,
  created_at timestamp
);
desc categories;
