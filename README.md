# DRFIS — Durian Research & Farm Intelligence System

ระบบบริหารข้อมูลวิจัยและการจัดการสวนทุเรียนอัจฉริยะ พัฒนาด้วย **Laravel 11 + MySQL**
อ้างอิงจาก `DRFIS-Blueprint-v2.md` (โปรเจกต์ "ระบบจัดการสวนทุเรียน")

## สถานะปัจจุบัน: Sprint 1 — Foundation

ชุดนี้คือโครง Laravel เริ่มต้น (ยังไม่มีโฟลเดอร์ `vendor/`) พร้อม:

- **Auth** พื้นฐาน (login/logout, session guard) — `app/Http/Controllers/Auth`
- **RBAC**: ตั้งค่า `spatie/laravel-permission` + Role 9 บทบาทตาม Blueprint หัวข้อ 4
  (Super Admin, Project Admin, Researcher, Field Officer, District Officer,
  Innovator, Farmer, Evaluator, Viewer)
- **Area Scope**: ตาราง `user_area_assignments` + `AreaScopeService` โครงเริ่มต้น (หัวข้อ 4.1)
- **Location hierarchy**: provinces → districts → tambons → villages → farmer_groups → households
- **crop_seasons** (ทุกตารางข้อมูลรายฤดูในอนาคตต้องอ้างอิงตารางนี้ ตามหัวข้อ 3.1)
- **audit_logs**, **notifications** (มาตรฐาน Laravel)
- Dashboard เปล่าเริ่มต้น (การ์ดจำนวนครัวเรือน/ฤดูผลิตปัจจุบัน)
- Seeder ข้อมูลตัวอย่าง: Role ทั้งหมด, จังหวัดจันทบุรี (ตัวอย่าง), ฤดูผลิต 2569,
  ผู้ใช้ Super Admin เริ่มต้น

**ยังไม่ได้ทำ** (รอ Sprint ถัดไปตาม Roadmap หัวข้อ 22): Farm/Plot, F01–F15 ทุกโมดูล,
Workflow State Machine service, Evidence upload, Dashboard/Report จริง

> **หมายเหตุสำคัญ**: โฟลเดอร์นี้ไม่มี `vendor/` (ไลบรารีของ Composer) เพราะเป็นแนวปฏิบัติ
> มาตรฐาน (ไม่ควร commit `vendor/` ไว้ในโปรเจกต์) — ต้องรัน `composer install`
> บนเครื่องคุณเองตามขั้นตอนด้านล่างก่อนใช้งานได้จริง

## ติดตั้งบนเครื่อง (XAMPP + Windows)

### สิ่งที่ต้องมี
- XAMPP (Apache + MySQL) — มีอยู่แล้วที่ `C:\xampp`
- PHP 8.2 ขึ้นไป (มากับ XAMPP อยู่แล้ว ตรวจสอบเวอร์ชันด้วย `php -v`)
- [Composer](https://getcomposer.org/download/) — ถ้ายังไม่มีให้ติดตั้งก่อน
- (ถ้าต้องการ build asset ภายหลัง) Node.js LTS

### ขั้นตอน

1. เปิด Command Prompt / Terminal ที่โฟลเดอร์โปรเจกต์:
   ```
   cd C:\xampp\htdocs\durian
   ```

2. ติดตั้ง PHP dependencies:
   ```
   composer install
   ```

3. สร้างไฟล์ `.env` จากตัวอย่าง แล้ว generate key:
   ```
   copy .env.example .env
   php artisan key:generate
   ```

4. เปิด XAMPP Control Panel → Start **Apache** และ **MySQL**

5. สร้างฐานข้อมูลชื่อ `drfis` ผ่าน phpMyAdmin (`http://localhost/phpmyadmin`)
   — คลิก New → ตั้งชื่อ `drfis` → Collation `utf8mb4_unicode_ci` → Create
   (ค่า `.env` ตั้งไว้ล่วงหน้าให้ตรงกับค่าเริ่มต้นของ XAMPP: host `127.0.0.1`,
   user `root`, password ว่าง — แก้ไขได้ถ้าคุณตั้งรหัสผ่าน MySQL ไว้)

6. รัน Migration + Seeder:
   ```
   php artisan migrate --seed
   ```

7. เปิดเว็บไซต์:
   - ผ่าน Apache: `http://localhost/durian/public`
   - หรือใช้ dev server ในตัว: `php artisan serve` แล้วเปิด `http://127.0.0.1:8000`

8. เข้าสู่ระบบด้วยบัญชีทดสอบ:
   - Email: `admin@drfis.local`
   - Password: `password`
   - **เปลี่ยนรหัสผ่านทันทีก่อนใช้งานจริง**

### (ทางเลือก) ตั้งค่า Auth UI ให้สมบูรณ์ขึ้นด้วย Breeze

หน้า login ปัจจุบันเขียนมือแบบเรียบง่าย ถ้าต้องการ UI ชุดเต็ม (register, forgot
password, email verification) ให้รัน:
```
php artisan breeze:install blade
npm install && npm run build
```
แล้วค่อยปรับ routes/views ให้เข้ากับ `layouts/app.blade.php` ที่มีอยู่

## โครงสร้างที่ควรรู้

| ที่อยู่ | อธิบาย |
|---|---|
| `database/migrations/` | Sprint 1 schema ทั้งหมด อ้างอิง Blueprint ภาคผนวก ค. |
| `app/Models/` | Eloquent model คู่กับตารางข้างต้น |
| `app/Services/AreaScopeService.php` | จุดเดียวที่ควรขยายเพื่อกรองข้อมูลตามพื้นที่ (หัวข้อ 4.1/21.1) |
| `config/permission.php` | ตั้งค่า Spatie Permission (role/permission) |
| `docs/DRFIS_Blueprint_v2.docx` | เอกสาร Blueprint ต้นฉบับ |

## แผนถัดไป (Sprint 2 ตาม Roadmap)

- F01 Household Baseline, F02 Production & Cost, F13 Farm Activity Log
- F10 Evidence (polymorphic `evidences` table + upload/compress pipeline)
- Offline draft (Local Storage/IndexedDB) สำหรับฟอร์มภาคสนามหลัก (หัวข้อ 3.2)
- WorkflowService กลาง (Draft → Submitted → Verified → Approved ตามภาคผนวก ง.)

## หมายเหตุสภาพแวดล้อมตอนสร้างชุดนี้

ชุดไฟล์นี้ถูกสร้างจากสภาพแวดล้อมคลาวด์ที่ไม่มีสิทธิ์เข้าถึง Packagist และไม่สามารถ
เชื่อมต่อ MySQL ของ XAMPP บนเครื่องคุณได้โดยตรง (ปัญหา mount โฟลเดอร์จาก Windows
update วันที่ 8 ก.ย. ที่ทีมงานกำลังตรวจสอบอยู่) จึงยังไม่ได้รัน `composer install`
หรือ `php artisan migrate` จริงจากฝั่งนั้น — ไฟล์ทั้งหมดตรวจสอบไวยากรณ์ PHP แล้ว
(`php -l`) และเขียนตามโครงสร้างมาตรฐานของ Laravel 11 แต่แนะนำให้รัน
`php artisan migrate --seed` ตามขั้นตอนด้านบนเพื่อยืนยันอีกครั้งบนเครื่องจริง
