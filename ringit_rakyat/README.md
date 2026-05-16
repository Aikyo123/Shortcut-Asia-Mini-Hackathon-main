# 💰 RingitRakyat — Student Finance Web App
> Built for students, by students. Track income, expenses, daily goals & more.

---

## 🛠 SETUP GUIDE (XAMPP + VSCode)

### Step 1: Install XAMPP
Download from https://www.apachefriends.org (Free!)
- Windows: Run the .exe installer
- Mac: Run the .dmg installer

### Step 2: Copy Project
Copy the entire `ringit_rakyat` folder into:
- **Windows:** `C:\xampp\htdocs\ringit_rakyat\`
- **Mac:** `/Applications/XAMPP/htdocs/ringit_rakyat/`

### Step 3: Start XAMPP
Open XAMPP Control Panel → Start **Apache** and **MySQL**

### Step 4: Create the Database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Name it `ringit_rakyat` → click Create
4. Click **"Import"** tab
5. Choose the file `database.sql` from this project
6. Click **"Go"** ✅

### Step 5: Open the App
Go to: **http://localhost/ringit_rakyat/login.html**

### Step 6: Register & Login
- Click "Register here" to create an account
- Or use demo: `demo@student.edu.my` / password: `password`

---

## 📁 FILE STRUCTURE

```
ringit_rakyat/
├── login.html          ← Login page
├── register.html       ← Registration page
├── forgot.html         ← Forgot password + OTP + Reset (3-in-1)
├── home.html           ← Dashboard / Home
├── finance.html        ← Finance tracker
├── calculator.html     ← Calculator (standard + finance tools)
├── news.html           ← Real-time news page
├── profile.html        ← User profile
├── database.sql        ← Run this in phpMyAdmin
│
├── css/
│   └── style.css       ← All styles
│
├── js/
│   └── app.js          ← Shared JavaScript
│
└── php/
    ├── config.php      ← Database connection
    ├── auth.php        ← Login, Register, OTP, Reset Password
    └── finance.php     ← Finance entries, goals, profile
```

---

## 🌟 FEATURES

| Page | Features |
|------|----------|
| 🔐 Login | Email + password login, session management |
| 📝 Register | Name, email, password with validation |
| 🔑 Forgot Password | OTP system → verify → reset password |
| 🏠 Home | Daily tracker, stats, quick actions, recent transactions |
| 💳 Finance | Add income/expense, categories, monthly view, daily goal tracker, smart tips |
| 🧮 Calculator | Standard calc + Tip/Split calc + Savings goal + 50/30/20 budget rule |
| 📰 News | Curated student finance news + live NewsAPI integration |
| 👤 Profile | Edit name, daily goal, avatar color picker, stats |

---

## 📰 NEWS PAGE — Live News Setup (Optional)

To enable real-time news:
1. Go to https://newsapi.org → Sign up FREE
2. Copy your API key
3. In the News page, paste the key and click "Save & Load"

Without a key, you get curated student-friendly articles automatically.

---

## 🔧 TROUBLESHOOTING

**"Database connection failed"**
→ Make sure MySQL is running in XAMPP
→ Check `php/config.php` — set your MySQL username/password

**"Page not loading"**
→ Make sure Apache is running in XAMPP
→ URL must be `http://localhost/ringit_rakyat/...`

**OTP not sending to email**
→ In demo mode, the OTP is shown on screen (this is intentional for hackathon)
→ For real email sending, use PHPMailer with Gmail SMTP

---

## 💡 TECH STACK

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | PHP 8.x |
| Database | MySQL (via XAMPP) |
| Fonts | Google Fonts (Plus Jakarta Sans + Space Mono) |
| News API | NewsAPI.org (optional, free tier) |

> Note: Java was not used — PHP handles all backend logic more easily for a student web project running on XAMPP.

---

## 👥 TEAM

Built with ❤️ for the Internship Hackathon
**RingitRakyat** — Empowering Malaysian Students to Earn Smarter 🇲🇾
