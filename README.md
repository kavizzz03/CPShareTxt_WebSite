# 🌐 CPShareTXT — Smart & Free Content Sharing Platform for Students

**CPShareTXT** is a lightweight, modern, and privacy-first web platform tailored for **university students, school children, and developers** to easily share **study notes, research documents, programming code snippets, and classroom assignments**.

Designed for speed, simplicity, and zero friction — like Pastebin, but tailored for learning communities with ephemeral self-destruction, 25MB file sharing, and robust vault locks.

---

## 👨‍💻 Creator & History
- **Original Designer & Developer:** **Kavindu Bogahawatte**
- **Background:** Created during his 2nd year as an undergraduate Software Engineering student.
- **Milestone:** His **1st deployed live project** in **December 2024**.
- **Current System Maintainer:** Officially maintained and scaled by **Vexel IT**, Colombo, Sri Lanka.

---

## 🚀 Key Features

- 🎓 **Targeted for Education:** 100% free note, document, code, and homework sharing without mandatory logins or ads.
- ⏱️ **Ephemeral Auto-Destruction:** Unprotected files and snippets automatically purge after 5 minutes for maximum privacy.
- 🔒 **Record Protection & Locking:** Option to protect revision notes for up to 30 days or lock files with a security password.
- 📁 **25 MB File Payloads:** Drag-and-drop document and image sharing with instant link generation.
- 🌓 **Synchronized Dark / Light Mode:** Cyber-glassmorphism visual theme with persistent theme preferences.
- 🗄️ **Centralized Database Architecture (`db.php`):** Unified, single-point MySQL connection with environment variables and host fallback support.
- ⚡ **Instant JSON API:** High-speed endpoints for loading saved records seamlessly.

---

## 🏗️ Project Architecture

```
CPShareTxt/
│
├── db.php                 # Centralized Database Connection (single source of truth)
├── index.html             # Homepage: Interactive sharing interface & payload generation
├── display_data.html      # Saved texts database viewer with search, copy, view & protect
├── backend.php            # Uploaded files vault manager with lock, download & delete controls
├── about.html             # About Us page: Mission, student target, creator background & Vexel IT
├── feedback_form.html     # User & student feedback submission interface
├── admin_panel.php        # Secure admin console for upload limits & auto-delete intervals
│
├── load_data_json.php     # High-performance JSON API for saved text records
├── load_data.php          # PHP table-view & fallback record provider
├── save_text.php          # Endpoint to process & store text/code snippets
├── save_file.php          # Endpoint to process & store uploaded file payloads
├── delete_data.php        # Endpoint to permanently delete saved texts
├── update_protection.php  # Endpoint to toggle 30-day protection status
├── verify_password.php    # Password validation handler for locked vault files
├── cleanup.php            # Cron/manual script to purge expired ephemeral snippets
├── download.php           # Secure file download stream helper
└── send_feedback.php      # Email notification handler for submitted feedback
```

---

## ⚙️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, Modern CSS3 (Glassmorphism & Radial Gradients), Bootstrap 5, Bootstrap Icons, AOS, SweetAlert2 |
| **Backend** | PHP (Clean modular scripts, UTF-8 charset, Centralized `db.php`) |
| **Database** | MySQL (Tables: `user_texts`, `user_files`, `admin_settings`) |
| **Hosting** | InfinityFree / Hostinger / Apache / Nginx |
| **Maintainer** | Vexel IT • Colombo, Sri Lanka |

---

## ⚡ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/kavizzz03/CPShareTxt_WebSite.git
   cd CPShareTxt
   ```

2. **Configure Database Connection in `db.php`:**
   Configure your MySQL credentials in `db.php` or set environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'your_database');
   ```

3. **Deploy on Apache / PHP Server:**
   Ensure the `uploads/` directory exists and has write permissions (`chmod 777 uploads`).

---

## 📄 License & Credits
© 2024–2026 **CP Share TXT**. All rights reserved.  
Designed & Developed by **Kavindu Bogahawatte** | Maintained by **Vexel IT**.
