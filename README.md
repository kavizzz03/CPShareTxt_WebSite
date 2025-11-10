# 🌐 CPShareTXT — Smart Content Sharing Platform

**CPShareTXT** is a lightweight and modern web platform that allows users to **create, share, and manage text snippets, code blocks, and short notes** easily.  
It’s designed for speed, simplicity, and real-time collaboration — like Pastebin, but smarter and more personal.

---

## 🚀 Features

✅ Share text, links, or code instantly  
✅ Auto-generated shareable URLs  
✅ Real-time content view counter  
✅ Copy-to-clipboard support  
✅ Beautiful responsive UI  
✅ Dark/light mode support  
✅ API endpoints for programmatic sharing  
✅ Fast hosting with PHP or Node.js backend  
✅ SEO-friendly public pages

---

## 🏗️ Project Structure

CPShareTXT/
│
├── api/ # PHP or Node.js API backend
│ ├── db.php # Database connection file
│ ├── create_text.php # Create and save new shared text
│ ├── get_text.php # Fetch text by unique ID
│ └── delete_text.php # Optional endpoint to delete entries
│
├── public/ # Frontend public files
│ ├── index.html
│ ├── main.js # Frontend logic
│ └── styles.css
│
├── uploads/ # Optional file upload folder
├── .env # Environment variables (API keys, DB credentials)
├── README.md # This file
└── .gitignore

---

## ⚙️ Tech Stack

| Layer | Technology |
|-------|-------------|
| Frontend | Html / Css |
| Backend | PHP (Hostinger)  |
| Database | MySQL |
| Hosting | Hostinger |
| Version Control | Git & GitHub |

---

## 🧩 Database Structure

**Table: `shared_texts`**

| Field | Type | Description |
|--------|------|-------------|
| id | INT (Primary Key) | Auto Increment |
| text_id | VARCHAR(255) | Unique ID for public link |
| content | TEXT | Shared text content |
| views | INT | Number of times opened |
| created_at | TIMESTAMP | Created date/time |

---

## ⚡ Installation & Setup

### 🪄 1. Clone the Repository
```bash
git clone https://github.com/kavizzz03/CPShareTxt_WebSite.git
cd CPShareTXT
