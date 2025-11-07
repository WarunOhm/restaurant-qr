# Restaurant QR Code Ordering System 🍽️

ระบบร้านอาหารที่สามารถสั่งออนไลน์ผ่าน QR Code บนโต๊ะ พัฒนาด้วย PHP 8 + MySQL

## ✨ ฟีเจอร์หลัก

### 👨‍💼 Admin
- จัดการเมนูอาหารและหมวดหมู่
- จัดการโต๊ะและสร้าง QR Code
- จัดการพนักงาน
- ดูบิลและตรวจสอบยอดขาย
- ตั้งค่าร้านและปรับแต่งใบเสร็จ

### 👨‍🍳 Staff
- ดูเมนูอาหารที่แต่ละโต๊ะสั่ง
- เช็คบิลและออกใบเสร็จ

### 👥 Customer
- สแกน QR Code เข้าสู่ระบบ (ไม่ต้องล็อกอิน)
- เลือกดูรายการอาหาร
- เพิ่มรายการเข้าตะกร้า
- สั่งอาหารเข้าครัว
- กดปุ่มเรียกพนักงานและเติมน้ำซุป
- ดูรายการอาหารที่สั่งแล้ว

## 🛠️ เทคโนโลยีที่ใช้

- **Backend:** PHP 8.x
- **Database:** MySQL (MySQLi - ไม่ใช้ PDO)
- **Frontend:** Bootstrap 5 + TailwindCSS (CDN)
- **JavaScript:** Vanilla JS (ไม่มี jQuery)
- **Fonts:** Google Fonts - Prompt
- **Alerts:** SweetAlert2 Toast
- **Icons:** Font Awesome 6
- **QR Code:** QRCode.js

## 📋 ความต้องการของระบบ

- PHP 8.0 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Apache/Nginx Web Server
- Extension ที่ต้องมี: mysqli, gd

## 🚀 การติดตั้ง

### ขั้นตอนที่ 1: Clone โปรเจค

```bash
git clone <repository-url>
cd restaurant-qr
```

### ขั้นตอนที่ 2: สร้างฐานข้อมูล

1. เปิด phpMyAdmin หรือ MySQL Client
2. สร้างฐานข้อมูลใหม่ชื่อ `restaurant_qr`
3. Import ไฟล์ `database.sql`

```sql
mysql -u root -p restaurant_qr < database.sql
```

### ขั้นตอนที่ 3: ตั้งค่าการเชื่อมต่อฐานข้อมูล

แก้ไขไฟล์ `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // แก้เป็นชื่อผู้ใช้ของคุณ
define('DB_PASS', '');            // แก้เป็นรหัสผ่านของคุณ
define('DB_NAME', 'restaurant_qr');
```

### ขั้นตอนที่ 4: ตั้งค่า Permissions

```bash
chmod 755 uploads/menus
chmod 755 uploads/qrcodes
```

### ขั้นตอนที่ 5: เข้าใช้งานระบบ

เปิดเบราว์เซอร์และเข้า: `http://localhost/restaurant-qr`

**ข้อมูลล็อกอิน Admin เริ่มต้น:**
- Username: `admin`
- Password: `admin123`

## 📁 โครงสร้างโฟลเดอร์

```
restaurant-qr/
├── admin/              # หน้าจัดการสำหรับ Admin
│   ├── includes/       # Header & Footer
│   ├── ajax/          # API Endpoints
│   ├── index.php      # Dashboard
│   ├── menus.php      # จัดการเมนู
│   ├── tables.php     # จัดการโต๊ะ & QR
│   ├── staff.php      # จัดการพนักงาน
│   ├── settings.php   # ตั้งค่าร้าน
│   └── orders.php     # ดูบิล
├── staff/             # หน้าสำหรับ Staff
├── customer/          # หน้าสำหรับลูกค้า
├── config/            # ไฟล์ Config
├── includes/          # Functions & Helpers
├── uploads/           # รูปภาพที่อัพโหลด
│   ├── menus/
│   └── qrcodes/
├── assets/            # CSS, JS, Images
├── database.sql       # SQL Database Schema
├── login.php          # หน้าล็อกอิน
└── index.php          # หน้าแรก
```

## 🎨 การออกแบบ

- **สี:** ใช้โทนสี Modern (ไม่มีสีม่วง)
- **Typography:** ใช้ฟอนต์ Prompt (ภาษาไทย)
- **Responsive:** รองรับมือถือ 100%
- **UI/UX:** Clean, Modern, Minimal

## 🔐 ความปลอดภัย

- Password Hashing ด้วย PHP password_hash()
- XSS Protection ด้วย htmlspecialchars()
- SQL Injection Protection ด้วย mysqli_real_escape_string()
- Session Management
- Role-Based Access Control

## 📱 การใช้งาน

1. **Admin** - ล็อกอินเพื่อจัดการระบบ
2. **Staff** - ล็อกอินเพื่อดูออเดอร์และเช็คบิล
3. **Customer** - สแกน QR Code บนโต๊ะเพื่อสั่งอาหาร

## 🐛 การแก้ไขปัญหา

### ปัญหา: ไม่สามารถอัพโหลดรูปภาพได้

```bash
# ตรวจสอบ permissions
chmod 755 uploads/menus
chmod 755 uploads/qrcodes
```

### ปัญหา: เชื่อมต่อฐานข้อมูลไม่ได้

- ตรวจสอบ username/password ใน `config/database.php`
- ตรวจสอบว่า MySQL Service ทำงานอยู่
- ตรวจสอบว่ามี Extension mysqli ติดตั้งแล้ว

### ปัญหา: QR Code ไม่แสดง

- ตรวจสอบ Internet Connection (ใช้ CDN)
- ตรวจสอบว่า JavaScript ทำงานปกติ

## 📝 To-Do (สิ่งที่ยังต้องพัฒนา)

- [ ] ระบบแจ้งเตือนแบบ Real-time (WebSocket/Pusher)
- [ ] ระบบรายงานและสถิติ
- [ ] Export ข้อมูลเป็น PDF/Excel
- [ ] Multi-language Support
- [ ] ระบบจองโต๊ะล่วงหน้า
- [ ] Payment Gateway Integration

## 👨‍💻 ผู้พัฒนา

พัฒนาโดย Claude Code Assistant

## 📄 License

MIT License - ใช้งานได้อย่างอิสระ

## 📞 ติดต่อและสนับสนุน

หากพบปัญหาการใช้งาน กรุณาเปิด Issue ใน GitHub Repository
