<?php

return [

    /*
    |--------------------------------------------------------------------
    | Default province (จังหวัด) for new registrations
    |--------------------------------------------------------------------
    |
    | DRFIS ตัวนี้ทำงานเฉพาะจังหวัดศรีสะเกษจังหวัดเดียวเท่านั้น (ยืนยันจากผู้ใช้แล้ว -
    | ดู "Follow-up Fixes" ใน Sprint log ตอนปรับ dashboard เป็นแยกตามอำเภอ/ตำบล
    | แทนจังหวัด เพราะแยกตามจังหวัดไม่มีความหมายเมื่อมีจังหวัดเดียว) ตั้งแต่รอบที่
    | ติดตั้งฐานข้อมูลที่อยู่ทางการครบ 77 จังหวัดแล้ว เจ้าหน้าที่ภาคสนามต้องไถหา
    | "ศรีสะเกษ" ในลิสต์ 77 จังหวัดทุกครั้งที่ลงทะเบียนครัวเรือน/สวนใหม่ - ค่านี้ทำให้
    | ฟอร์ม "เพิ่มครัวเรือน"/"เพิ่มสวน" เลือกจังหวัดนี้ไว้ล่วงหน้าให้อัตโนมัติ (ยังเปลี่ยน
    | เป็นจังหวัดอื่นเองได้ปกติ ไม่ได้ล็อก - เผื่อกรณีอนาคตขยายพื้นที่)
    |
    | id 22 = ศรีสะเกษ ตาม database/data/thailand/provinces.json (ชุดข้อมูลทางการ
    | จาก kongvut/thai-province-data)
    |
    */
    'default_province_id' => 22,

    /*
    |--------------------------------------------------------------------
    | Evidence EXIF GPS warning radius (กม.)
    |--------------------------------------------------------------------
    |
    | Blueprint section 9, 4th bullet: "อ่านค่า GPS จาก EXIF ของรูปภาพ (ถ้ามี)
    | และเปรียบเทียบกับพิกัดของแปลงที่เกี่ยวข้อง หากห่างเกินระยะที่กำหนดให้แสดงคำเตือน
    | แก่ผู้ตรวจสอบข้อมูล (ไม่ต้องปฏิเสธอัตโนมัติ เพราะ GPS มือถืออาจคลาดเคลื่อนได้)" -
    | see EvidenceController::store(). 2 กม. is a starting guess for "far
    | enough to be suspicious, close enough to allow normal GPS drift" -
    | adjust here if field testing shows too many false warnings.
    |
    */
    'evidence_gps_warning_km' => 2.0,

    /*
    |--------------------------------------------------------------------
    | F06 Economic Impact - Net Benefit target (บาท/ครัวเรือน/ปี)
    |--------------------------------------------------------------------
    |
    | Blueprint หัวข้อ 11: "เป้าหมาย: Net Benefit >= 60,000 บาท/ครัวเรือน/ปี" -
    | see App\Services\EconomicImpactService::calculateForHousehold(),
    | which sets economic_impacts.target_status to achieved/not_achieved
    | by comparing against this value.
    |
    */
    'economic_impact_target_baht' => 60000,

];
