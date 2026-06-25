# CloudCush 👶

A luxury babycare brand website designed for modern parenting comfort. CloudCush combines a beautiful, editorial-grade frontend experience with a robust admin control panel for dynamic content management.

## 🌟 Key Features

* **Premium Frontend:** Editorial typography, custom GSAP entrance animations, dynamic sizing calculator, and smooth Lenis scrolling.
* **Admin Dashboard:** Control panel to update homepage sections, diaper guides, blog posts, testimonials, products, and inventory.
* **Authentication:** Secure login and registration portals for both administrators and customers.

## 🛠️ Technology Stack

* **Backend:** PHP (OOP & PDO)
* **Database:** MySQL
* **Frontend:** HTML5, Vanilla CSS3 (Custom design system & fluid typography)
* **Libraries:** GSAP 3 (ScrollTrigger), Lenis Kinetic Smooth Scroll, SweetAlert2, Remix Icons

## 🚀 Setup & Local Installation

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/your-username/cloudcush.git
   ```
2. **Move to Server Directory:**
   Place the `cloudcush` folder in your local server's document root (e.g., `C:\xampp\htdocs\`).
3. **Set Up the Database:**
   * Open phpMyAdmin (`http://localhost/phpmyadmin`).
   * Create a database named `cloudcush_db`.
   * Import the `cloudcush_db.sql` database file.
4. **Configure Connection:**
   Verify database credentials inside [admin/config/database.php](file:///c:/xampp/htdocs/cloudcush/admin/config/database.php).
5. **Start Web Server:**
   Start Apache & MySQL from the XAMPP Control Panel and visit `http://localhost/cloudcush`.

## 🔐 Administrative Control Panel

The admin dashboard is located at `/admin` (e.g., `http://localhost/cloudcush/admin`). It allows you to:
* **Manage Content:** Dynamically add or edit products, blogs, FAQs, and testimonials.
* **Update Assets:** Upload and configure banners, images, and media assets.
* **Database Synchronization:** Real-time updates directly reflected on the customer-facing frontend.
