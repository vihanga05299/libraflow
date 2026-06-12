# 📚 LibraFlow — Library Management System

A web-based Library Management System built with **HTML5, CSS3, PHP 8, and MySQL** as a practical implementation of Data Structures and Algorithms.

> NIBM Higher National Diploma in Software Engineering
> Module: Programming Data Structures and Algorithms (PDSA)
> Batch: 25.1F

---

## 🚀 Features

- 🔐 Admin authentication with bcrypt password hashing
- 📖 Books catalogue — Add, Edit, Delete, Search
- 👥 Member registration and management
- 🔄 Borrow & Return system using **Queue (FIFO)**
- 📌 Reservation system using **Stack (LIFO)**
- 🔍 Binary Search tracer in Reports page
- 📊 Dashboard with live DSA visualizer
- ⚠️ Overdue book detection and reporting

---

## 🧩 Data Structures Implemented

| Data Structure | Location | Operations |
|---|---|---|
| **Queue (FIFO)** | Borrow / Return module | Enqueue, Dequeue, Peek — O(1) |
| **Stack (LIFO)** | Reservations module | Push, Pop, Peek — O(1) |
| **Singly Linked List** | Categories (next_id) | Traverse, Insert — O(n) |
| **Binary Search** | Reports module | Search sorted array — O(log n) |

---

## 🛠️ Tech Stack

- **Frontend:** HTML5, CSS3, Google Fonts
- **Backend:** PHP 8
- **Database:** MySQL via XAMPP
- **Server:** Apache (XAMPP)

---

## ⚙️ Setup Instructions

### Requirements
- XAMPP (Apache + MySQL)
- PHP 8+
- Web browser

### Steps

1. **Clone the repository**
```bash
   git clone https://github.com/YOUR_USERNAME/libraflow.git
```

2. **Copy to XAMPP**
Copy the libraflow folder to: C:\xampp\htdocs\

3. **Import the database**
   - Start XAMPP — turn on Apache and MySQL
   - Open `http://localhost/phpmyadmin`
   - Create a new database named `library_db`
   - Click the `library_db` database → Import tab
   - Select `database.sql` and click **Go**

4. **Run the system**
http://localhost/libraflow/

5. **Login**
Username: admin

Password: password

---

## 📁 Project Structure
libraflow/

├── index.php               ← Entry point

├── login.php               ← Admin login

├── logout.php

├── dashboard.php           ← Stats + Queue visualizer

├── database.sql            ← Full database schema + seed data

├── css/

│   └── style.css           ← Main stylesheet

├── includes/

│   ├── config.php          ← DB connection + helpers

│   ├── dsa.php             ← Queue, Stack, Binary Search classes

│   └── sidebar.php         ← Navigation

├── books/                  ← Books CRUD

├── members/                ← Members CRUD

├── borrow/                 ← Borrow/Return (Queue)

├── reservations/           ← Reservations (Stack)

└── reports/                ← Analytics + Binary Search demo

---

## 📄 License

This project is submitted as academic coursework for NIBM HNDSE.
