# 🥬 Fresh Rescue

> **Save Food. Save Money. Save the Planet.**

Fresh Rescue is a **Mini E-Commerce Platform** designed to reduce food waste by helping supermarkets sell products that are close to their expiration date at discounted prices.

Instead of throwing away food, stores can list these products on the platform, allowing customers to purchase them at lower prices while contributing to environmental sustainability.

---

# 📖 Table of Contents

- Project Overview
- Problem Statement
- Solution
- Features
- Technologies Used
- System Architecture
- Database Design
- Project Structure
- Installation
- Configuration
- Screenshots
- Admin Dashboard
- User Workflow
- Security
- Future Improvements
- Team Members
- License

---

# 📌 Project Overview

Fresh Rescue is a web-based grocery e-commerce platform that focuses on reducing food waste.

The platform connects:

- 🏪 Grocery Stores
- 👤 Customers

Stores can publish products approaching their expiration date with special discounts.

Customers can purchase these products at affordable prices.

This creates a win-win solution:

- Reduce Food Waste
- Increase Store Revenue
- Save Customer Money
- Support Sustainability

---

# ❗ Problem Statement

Every year, supermarkets throw away thousands of products because they are close to their expiration dates.

This causes:

- Financial losses
- Food waste
- Environmental pollution
- Missed savings opportunities

---

# 💡 Solution

Fresh Rescue provides a dedicated marketplace where stores can:

- List near-expiration products
- Apply discounts
- Manage inventory

Customers can:

- Discover discounted products
- Save money
- Reduce waste

---

# ✨ Features

## Customer Features

- User Registration
- Secure Login
- Browse Products
- Categories
- Product Search
- Product Details
- Wishlist
- Shopping Cart
- Checkout
- Orders History
- User Profile
- Address Management

---

## Admin Features

- Admin Dashboard
- Product Management
- Category Management
- Customer Management
- Order Management
- Payment Monitoring
- Reports
- Inventory Tracking

### ⭐ Expiration Center

The core feature of the system.

Allows administrators to:

- View products expiring today
- View products expiring in 3 days
- View products expiring in 7 days
- View expired products

Quick actions:

- Increase Discount
- Hide Product
- Delete Product
- Mark as Expired

---

# 🛠 Technologies Used

## Frontend

- HTML5
- CSS3
- Bootstrap 5
- JavaScript

## Backend

- Native PHP 8

## Database

- MySQL

## Development Tools

- XAMPP
- VS Code
- Git
- GitHub

---

# 🏗 System Architecture

```
Customer

↓

Register

↓

Login

↓

Browse Products

↓

Add To Cart

↓

Checkout

↓

Payment

↓

Orders
```

---

# 🗄 Database Design

Main Tables

- Users
- Categories
- Products
- Cart
- Cart_Items
- Orders
- Order_Items
- Payments
- Addresses

---

## Relationships

### One-to-One

Users → Cart

---

### One-to-Many

Category → Products

User → Orders

User → Addresses

---

### Many-to-Many

Orders ↔ Products

Implemented using:

Order_Items

---

# 📁 Project Structure

```
FreshRescue/

│

├── admin/

├── assets/

│   ├── css/

│   ├── js/

│   └── images/

│

├── auth/

├── cart/

├── checkout/

├── config/

│      database.php

├── includes/

│      navbar.php

│      footer.php

│      head.php

│      functions.php

│      session.php

│

├── orders/

├── products/

├── profile/

├── index.php

├── login.php

├── register.php

└── README.md
```

---

# ⚙ Installation

Clone the repository

```bash
git clone https://github.com/yourusername/FreshRescue.git
```

Move the project to:

```
xampp/htdocs/
```

Create Database

```
fresh_rescue
```

Import

```
fresh_rescue.sql
```

Update

```
config/database.php
```

Start

- Apache
- MySQL

Open

```
http://localhost/FreshRescue
```

---

# 🔐 Authentication

The system implements secure authentication using:

- password_hash()
- password_verify()
- PHP Sessions

Two Roles:

- Admin
- Customer

Admin pages are protected.

---

# 📊 Admin Dashboard

Dashboard includes:

- Products Statistics
- Orders
- Customers
- Revenue
- Reports
- Expiration Center
- Inventory Status
- Charts

---

# 🚨 Expiration Center

The main feature of Fresh Rescue.

Displays:

🟢 Safe Products

🟡 Near Expiration

🟠 Urgent

🔴 Expired

Provides quick management actions.

---

# 📱 Responsive Design

Supports

- Desktop
- Laptop
- Tablet
- Mobile

Built with Bootstrap Grid System.

---

# 🔒 Security

Implemented using

- Prepared Statements
- Password Hashing
- Input Validation
- Output Escaping
- Session Authentication
- Role Authorization

---

# 🚀 Future Improvements

- Coupons
- Product Reviews
- Ratings
- Email Notifications
- AI Discount Prediction
- Mobile Application
- Analytics Dashboard
- Payment Gateway Integration

---

# 👨‍💻 Team Members

- Moamen Sayed
- Mokhtar Wael
- Ahmed Momtaz
- Marwa Fawzy

---

# 📄 License

This project was developed for educational purposes.

---

# ❤️ Thank You

**Fresh Rescue**

Helping Stores.

Helping Customers.

Helping the Environment.
