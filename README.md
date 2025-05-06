# Game Scape

**Game Scape** is an online shopping platform designed for gamers to browse, search, and purchase gaming products. It also includes an admin dashboard to manage products and orders.

---

## Features
- User sign up and login
- Product browsing and search
- View product details
- Add items to cart and checkout
- Admin dashboard to:
  - Add / Edit / Delete products
  - View orders

---

## Technologies Used
- PHP
- MySQL
- HTML / CSS / JavaScript

---

## How to Run
1. Download or clone the repository
2. Place the project folder inside your XAMPP `htdocs` directory
3. Import the provided `.sql` file into your MySQL server
4. Update your database connection settings in `config.php`
5. Open your browser and go to:  
   `http://localhost/Game-Scape`

---

## Admin Account Setup
You can manually create an admin user by inserting into the database.  
In the `users` table, set the `role` column to `'admin'`.

---

## License
This project is licensed under the **MIT License**.
