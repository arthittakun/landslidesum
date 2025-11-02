<?php
require_once __DIR__ . '/plugin/header_user.php';
?>
<style>
  /* Weather Page Custom Styling */
  .weather-container {
    background: var(--bg-card);
    border-radius: 16px;
    box-shadow: 0 8px 25px var(--shadow-color);
    padding: 2rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    margin-bottom: 2rem;
  }
  
  .page-title {
    background: linear-gradient(135deg, #0ea5e9, #10b981);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    text-align: center;
    margin-bottom: 1rem;
  }
  
  .status-text {
    color: var(--text-secondary);
    text-align: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 0.75rem;
    font-weight: 500;
  }
  
  .weather-card {
    background: var(--bg-card);
    border-radius: 16px;
    box-shadow: 0 8px 25px var(--shadow-color);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
  }
  
  .weather-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0ea5e9, #10b981);
  }
  
  .weather-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px var(--shadow-color);
  }
  
  /* Weather type specific colors */
  .weather-card.sunny::before {
    background: linear-gradient(90deg, #fbbf24, #f59e0b);
  }
  
  .weather-card.cloudy::before {
    background: linear-gradient(90deg, #9ca3af, #6b7280);
  }
  
  .weather-card.rainy::before {
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
  }
  
  .weather-card.stormy::before {
    background: linear-gradient(90deg, #7c3aed, #5b21b6);
  }
  
  .weather-card.foggy::before {
    background: linear-gradient(90deg, #d1d5db, #9ca3af);
  }
  
  .weather-header {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(16, 185, 129, 0.05));
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px solid var(--border-color);
  }
  
  .weather-date {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }
  
  .weather-content {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .weather-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
  }
  
  .weather-item:hover {
    background: var(--bg-secondary);
    transform: translateX(4px);
  }
  
  .weather-icon {
    /* ลบ background ออก ไม่ต้องใส่พื้นหลังให้ icon */
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    background: none !important;
  }
  
  .weather-icon i {
    z-index: 2;
    position: relative;
    background: none !important;
  }
  
  /* สี icon ตาม Font Awesome และธีม */
  .icon-temp i { color: #f59e0b; } /* สีเหลือง-ส้ม สำหรับอุณหภูมิ */
  .icon-rain i { color: #3b82f6; } /* สีน้ำเงิน สำหรับฝน */
  .icon-uv i { color: #eab308; } /* สีเหลือง สำหรับ UV */
  .icon-wind i { color: #06b6d4; } /* สีฟ้า สำหรับลม */
  .icon-gust i { color: #64748b; } /* สีเทา สำหรับลมกระโชก */
  .icon-sunrise i { color: #fb923c; } /* สีส้ม สำหรับพระอาทิตย์ขึ้น */
  .icon-sunset i { color: #f472b6; } /* สีชมพู สำหรับพระอาทิตย์ตก */
  
  /* Dark theme adjustments */
  [data-theme="dark"] .icon-temp i { color: #fbbf24; }
  [data-theme="dark"] .icon-rain i { color: #60a5fa; }
  [data-theme="dark"] .icon-uv i { color: #facc15; }
  [data-theme="dark"] .icon-wind i { color: #22d3ee; }
  [data-theme="dark"] .icon-gust i { color: #94a3b8; }
  [data-theme="dark"] .icon-sunrise i { color: #fb923c; }
  [data-theme="dark"] .icon-sunset i { color: #f472b6; }
  
  /* Weather code icon colors */
  .weather-code-icon {
    font-size: 1.25rem;
    margin-right: 0.5rem;
  }
  
  /* Sunny icons */
  .weather-card.sunny .weather-code-icon.fa-sun { color: #fbbf24; }
  .weather-card.sunny .weather-code-icon.fa-cloud-sun { color: #f59e0b; }
  
  /* Cloudy icons */
  .weather-card.cloudy .weather-code-icon.fa-cloud { color: #9ca3af; }
  .weather-card.cloudy .weather-code-icon.fa-cloud-sun { color: #6b7280; }
  
  /* Rainy icons */
  .weather-card.rainy .weather-code-icon.fa-cloud-rain,
  .weather-card.rainy .weather-code-icon.fa-cloud-drizzle,
  .weather-card.rainy .weather-code-icon.fa-cloud-showers-heavy { color: #3b82f6; }
  
  /* Stormy icons */
  .weather-card.stormy .weather-code-icon.fa-bolt { color: #7c3aed; }
  
  /* Foggy icons */
  .weather-card.foggy .weather-code-icon.fa-smog { color: #d1d5db; }
  .weather-card.foggy .weather-code-icon.fa-snowflake { color: #e5e7eb; }
  
  /* Dark theme weather code icons */
  [data-theme="dark"] .weather-card.sunny .weather-code-icon.fa-sun { color: #fde047; }
  [data-theme="dark"] .weather-card.sunny .weather-code-icon.fa-cloud-sun { color: #fbbf24; }
  [data-theme="dark"] .weather-card.cloudy .weather-code-icon.fa-cloud,
  [data-theme="dark"] .weather-card.cloudy .weather-code-icon.fa-cloud-sun { color: #d1d5db; }
  [data-theme="dark"] .weather-card.rainy .weather-code-icon.fa-cloud-rain,
  [data-theme="dark"] .weather-card.rainy .weather-code-icon.fa-cloud-drizzle,
  [data-theme="dark"] .weather-card.rainy .weather-code-icon.fa-cloud-showers-heavy { color: #60a5fa; }
  [data-theme="dark"] .weather-card.stormy .weather-code-icon.fa-bolt { color: #a78bfa; }
  [data-theme="dark"] .weather-card.foggy .weather-code-icon.fa-smog,
  [data-theme="dark"] .weather-card.foggy .weather-code-icon.fa-snowflake { color: #f3f4f6; }
  
  /* Hover effects for icons */
  .weather-item:hover .weather-icon i {
    transform: scale(1.1);
    transition: transform 0.2s ease;
  }
  
  .weather-code-badge:hover .weather-code-icon {
    transform: scale(1.1);
    transition: transform 0.2s ease;
  }
  
  /* Loading Spinner */
  .loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: var(--text-secondary);
  }
  
  .spinner {
    width: 3rem;
    height: 3rem;
    border: 4px solid var(--border-color);
    border-top: 4px solid #0ea5e9;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  
  /* Error State */
  .error-state {
    text-align: center;
    padding: 3rem;
    color: var(--color-danger);
  }
  
  .error-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.7;
  }
  
  /* Responsive Grid */
  .forecast-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
  }
  
  /* Weather code badge */
  .weather-code-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: var(--bg-secondary);
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
  }
  
  .weather-code-badge:hover {
    background: var(--color-primary);
    color: white;
    transform: scale(1.05);
  }
  
  .weather-code-icon {
    font-size: 1.25rem;
    margin-right: 0.5rem;
  }
  
  /* Responsive Design */
  @media (max-width: 640px) {
    .weather-container {
      padding: 1rem;
      margin-bottom: 1rem;
    }
    
    .page-title {
      font-size: 1.5rem !important;
    }
    
    .forecast-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }
    
    .weather-header {
      padding: 1rem;
    }
    
    .weather-content {
      padding: 1rem;
    }
    
    .weather-item {
      padding: 0.375rem;
    }
    
    .weather-icon {
      width: 1.75rem;
      height: 1.75rem;
      font-size: 0.75rem;
    }
    
    .weather-label,
    .weather-value {
      font-size: 0.8rem;
    }
  }
  
  @media (min-width: 641px) and (max-width: 768px) {
    .forecast-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .page-title {
      font-size: 2rem !important;
    }
  }
  
  @media (min-width: 769px) and (max-width: 1024px) {
    .forecast-grid {
      grid-template-columns: repeat(3, 1fr);
    }
    
    .page-title {
      font-size: 2.25rem !important;
    }
  }
  
  @media (min-width: 1025px) {
    .page-title {
      font-size: 2.5rem !important;
    }
  }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="weather-container">
    <div class="flex justify-center items-center mb-6">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold">🌤️ </h1>
        <h1 class="page-title text-2xl sm:text-3xl lg:text-4xl font-bold">
      พยากรณ์อากาศ 7 วัน
    </h1>
    </div>
    
    <div id="status" class="status-text">
      📍 กำลังค้นหาตำแหน่งของคุณ...
    </div>
    
    <div id="loading" class="loading-spinner" style="display: none;">
      <div class="spinner"></div>
      <p>กำลังโหลดข้อมูลพยากรณ์อากาศ...</p>
    </div>
    
    <div id="forecast" class="forecast-grid"></div>
  </div>
</div>

<script>
// ฟังก์ชันแปลงพิกัดเป็นจังหวัด
function getProvinceFromCoords(lat, lon) {
 const provinces = [
  { name: 'กรุงเทพมหานคร', lat: 13.7278956, lon: 100.5241235 },
  { name: 'กระบี่', lat: 8.0862997, lon: 98.9062835 },
  { name: 'กาญจนบุรี', lat: 14.0227797, lon: 99.5328115 },
  { name: 'กาฬสินธุ์', lat: 16.4314078, lon: 103.5058755 },
  { name: 'กำแพงเพชร', lat: 16.4827798, lon: 99.5226618 },
  { name: 'ขอนแก่น', lat: 16.4419355, lon: 102.8359921 },
  { name: 'จันทบุรี', lat: 12.61134, lon: 102.1038546 },
  { name: 'ฉะเชิงเทรา', lat: 13.6904194, lon: 101.0779596 },
  { name: 'ชลบุรี', lat: 13.3611431, lon: 100.9846717 },
  { name: 'ชัยนาท', lat: 15.1851971, lon: 100.125125 },
  { name: 'ชัยภูมิ', lat: 15.8068173, lon: 102.0315027 },
  { name: 'ชุมพร', lat: 10.4930496, lon: 99.1800199 },
  { name: 'เชียงราย', lat: 19.9071656, lon: 99.830955 },
  { name: 'เชียงใหม่', lat: 18.7877477, lon: 98.9931311 },
  { name: 'ตรัง', lat: 7.5593851, lon: 99.6110065 },
  { name: 'ตราด', lat: 12.2427563, lon: 102.5174734 },
  { name: 'ตาก', lat: 16.8839901, lon: 99.1258498 },
  { name: 'นครนายก', lat: 14.2069466, lon: 101.2130511 },
  { name: 'นครปฐม', lat: 13.8199206, lon: 100.0621676 },
  { name: 'นครพนม', lat: 17.392039, lon: 104.7695508 },
  { name: 'นครราชสีมา', lat: 14.9798997, lon: 102.0977693 },
  { name: 'นครศรีธรรมราช', lat: 8.4303975, lon: 99.9631219 },
  { name: 'นครสวรรค์', lat: 15.6930072, lon: 100.1225595 },
  { name: 'นนทบุรี', lat: 13.8621125, lon: 100.5143528 },
  { name: 'นราธิวาส', lat: 6.4254607, lon: 101.8253143 },
  { name: 'น่าน', lat: 18.7756318, lon: 100.7730417 },
  { name: 'บุรีรัมย์', lat: 14.9930017, lon: 103.1029191 },
  { name: 'ปทุมธานี', lat: 14.0208391, lon: 100.5250276 },
  { name: 'ประจวบคีรีขันธ์', lat: 11.812367, lon: 99.7973271 },
  { name: 'ปราจีนบุรี', lat: 14.0509704, lon: 101.3727439 },
  { name: 'ปัตตานี', lat: 6.8694844, lon: 101.2504826 },
  { name: 'พระนครศรีอยุธยา', lat: 14.3532128, lon: 100.5689599 },
  { name: 'พะเยา', lat: 19.1664789, lon: 99.9019419 },
  { name: 'พังงา', lat: 8.4407456, lon: 98.5193032 },
  { name: 'พัทลุง', lat: 7.6166823, lon: 100.0740231 },
  { name: 'พิจิตร', lat: 16.4429516, lon: 100.3482329 },
  { name: 'พิษณุโลก', lat: 16.8298048, lon: 100.2614915 },
  { name: 'เพชรบุรี', lat: 13.1111601, lon: 99.9391307 },
  { name: 'เพชรบูรณ์', lat: 16.4189807, lon: 101.1550926 },
  { name: 'แพร่', lat: 18.1445774, lon: 100.1402831 },
  { name: 'ภูเก็ต', lat: 7.9810496, lon: 98.3638824 },
  { name: 'มหาสารคาม', lat: 16.1850896, lon: 103.3026461 },
  { name: 'มุกดาหาร', lat: 16.542443, lon: 104.7209151 },
  { name: 'แม่ฮ่องสอน', lat: 19.2990643, lon: 97.9656226 },
  { name: 'ยโสธร', lat: 15.792641, lon: 104.1452827 },
  { name: 'ยะลา', lat: 6.541147, lon: 101.2803947 },
  { name: 'ร้อยเอ็ด', lat: 16.0538196, lon: 103.6520036 },
  { name: 'ระนอง', lat: 9.9528702, lon: 98.6084641 },
  { name: 'ระยอง', lat: 12.6833115, lon: 101.2374295 },
  { name: 'ราชบุรี', lat: 13.5282893, lon: 99.8134211 },
  { name: 'ลพบุรี', lat: 14.7995081, lon: 100.6533706 },
  { name: 'ลำปาง', lat: 18.2888404, lon: 99.490874 },
  { name: 'ลำพูน', lat: 18.5744606, lon: 99.0087221 },
  { name: 'เลย', lat: 17.4860232, lon: 101.7223002 },
  { name: 'ศรีสะเกษ', lat: 15.1186009, lon: 104.3220095 },
  { name: 'สกลนคร', lat: 17.1545995, lon: 104.1348365 },
  { name: 'สงขลา', lat: 7.1756004, lon: 100.614347 },
  { name: 'สตูล', lat: 6.6238158, lon: 100.0673744 },
  { name: 'สมุทรปราการ', lat: 13.5990961, lon: 100.5998319 },
  { name: 'สมุทรสงคราม', lat: 13.4098217, lon: 100.0022645 },
  { name: 'สมุทรสาคร', lat: 13.5475216, lon: 100.2743956 },
  { name: 'สระแก้ว', lat: 13.824038, lon: 102.0645839 },
  { name: 'สระบุรี', lat: 14.5289154, lon: 100.9101421 },
  { name: 'สิงห์บุรี', lat: 14.8936253, lon: 100.3967314 },
  { name: 'สุโขทัย', lat: 17.0055573, lon: 99.8263712 },
  { name: 'สุพรรณบุรี', lat: 14.4744892, lon: 100.1177128 },
  { name: 'สุราษฎร์ธานี', lat: 9.1382389, lon: 99.3217483 },
  { name: 'สุรินทร์', lat: 14.882905, lon: 103.4937107 },
  { name: 'หนองคาย', lat: 17.8782803, lon: 102.7412638 },
  { name: 'หนองบัวลำภู', lat: 17.2218247, lon: 102.4260368 },
  { name: 'อ่างทอง', lat: 14.5896054, lon: 100.455052 },
  { name: 'อำนาจเจริญ', lat: 15.8656783, lon: 104.6257774 },
  { name: 'อุดรธานี', lat: 17.4138413, lon: 102.7872325 },
  { name: 'อุตรดิตถ์', lat: 17.6200886, lon: 100.0992942 },
  { name: 'อุทัยธานี', lat: 15.3835001, lon: 100.0245527 },
  { name: 'อุบลราชธานี', lat: 15.2286861, lon: 104.8564217 },
  { name: 'บึงกาฬ', lat: 18.3609104, lon: 103.6464463 }
];

  
  let closestProvince = provinces[0];
  let minDistance = getDistance(lat, lon, closestProvince.lat, closestProvince.lon);
  
  provinces.forEach(province => {
    const distance = getDistance(lat, lon, province.lat, province.lon);
    if (distance < minDistance) {
      minDistance = distance;
      closestProvince = province;
    }
  });
  
  return closestProvince.name;
}

function getDistance(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return R * c;
}

// ฟังก์ชันแปลง weather code เป็นคำอธิบายและไอคอน
function getWeatherInfo(code) {
  const weatherCodes = {
    0: { desc: "ท้องฟ้าแจ่มใส", icon: "fas fa-sun", class: "sunny" },
    1: { desc: "เมฆบางเบา", icon: "fas fa-cloud-sun", class: "partly-cloudy" },
    2: { desc: "เมฆเป็นส่วนมาก", icon: "fas fa-cloud", class: "cloudy" },
    3: { desc: "เมฆปิดท้องฟ้า", icon: "fas fa-cloud-meatball", class: "overcast" },
    45: { desc: "หมอกลง", icon: "fas fa-eye-slash", class: "foggy" },
    48: { desc: "หมอกน้ำแข็งเกิดขึ้น", icon: "fas fa-icicles", class: "frost" },
    51: { desc: "ฝนฟอยเบา", icon: "fas fa-cloud-drizzle", class: "drizzle" },
    53: { desc: "ฝนฟอยปานกลาง", icon: "fas fa-cloud-rain", class: "light-rain" },
    55: { desc: "ฝนฟอยหนัก", icon: "fas fa-cloud-showers-heavy", class: "heavy-drizzle" },
    61: { desc: "ฝนเบา", icon: "fas fa-tint", class: "light-rain" },
    63: { desc: "ฝนปานกลาง", icon: "fas fa-umbrella", class: "moderate-rain" },
    65: { desc: "ฝนหนัก", icon: "fas fa-poo-storm", class: "heavy-rain" },
    80: { desc: "ฝนฟ้าแลบเบา", icon: "fas fa-cloud-sun-rain", class: "shower-light" },
    81: { desc: "ฝนฟ้าแลบปานกลาง", icon: "fas fa-cloud-rain", class: "shower-moderate" },
    82: { desc: "ฝนฟ้าแลบรุนแรง", icon: "fas fa-cloud-showers-heavy", class: "shower-heavy" },
    95: { desc: "พายุฝนฟ้าคะนอง", icon: "fas fa-bolt", class: "thunderstorm" },
    96: { desc: "ฟ้าร้องอาจจะลูกมีเห็บเบา", icon: "fas fa-cloud-bolt", class: "thunder-hail" },
    99: { desc: "ฟ้าร้องอาจจะลูกเห็บรุนแรง", icon: "fas fa-meteor", class: "severe-storm" }
  };
  
  return weatherCodes[code] || { 
    desc: `สภาพอากาศไม่ทราบ (รหัส ${code})`, 
    icon: "fas fa-question-circle", 
    class: "unknown" 
  };
}

function fetchForecast(lat, lon) {
  document.getElementById('loading').style.display = 'flex';
  document.getElementById('forecast').innerHTML = '';
  
  const province = getProvinceFromCoords(lat, lon);
  const url = `/api/weather/weather-day?lat=${lat}&lon=${lon}`;
  
  fetch(url)
    .then(res => res.json())
    .then(data => {
      const status = document.getElementById('status');
      status.innerHTML = `📍 พยากรสำหรับพื้นที่ <strong>${province}</strong>`;
      status.className = 'status-text';

      const d = data.daily;
      const forecastDiv = document.getElementById('forecast');
      forecastDiv.innerHTML = '';
      
      for (let i = 0; i < 7; i++) {
        const date = new Date(d.time[i]);
        const dateStr = date.toLocaleDateString('th-TH', {
          weekday: 'long', 
          day: 'numeric', 
          month: 'long'
        });
        
        const sunrise = new Date(d.sunrise[i]).toLocaleTimeString('th-TH', { 
          hour: '2-digit', 
          minute: '2-digit' 
        });
        
        const sunset = new Date(d.sunset[i]).toLocaleTimeString('th-TH', { 
          hour: '2-digit', 
          minute: '2-digit' 
        });

        const weatherInfo = getWeatherInfo(d.weathercode[i]);
        
        const card = document.createElement('div');
        card.className = `weather-card ${weatherInfo.class}`;
        card.innerHTML = `
          <div class="weather-header">
            <div class="weather-date">${dateStr}</div>
            <div class="weather-code-badge">
              <i class="${weatherInfo.icon} weather-code-icon"></i>
              <span>${weatherInfo.desc}</span>
            </div>
          </div>
          <div class="weather-content">
            <div class="weather-item">
              <div class="weather-icon icon-temp">
                <i class="fas fa-thermometer-half"></i>
              </div>
              <div class="weather-label">อุณหภูมิ:</div>
              <div class="weather-value">${d.temperature_2m_min[i]}°C - ${d.temperature_2m_max[i]}°C</div>
            </div>
            <div class="weather-item">
              <div class="weather-icon icon-rain">
                <i class="fas fa-cloud-rain"></i>
              </div>
              <div class="weather-label">ปริมาณฝน:</div>
              <div class="weather-value">${d.precipitation_sum[i]} mm</div>
            </div>
            <div class="weather-item">
              <div class="weather-icon icon-uv">
                <i class="fas fa-sun"></i>
              </div>
              <div class="weather-label">ดัชนี UV:</div>
              <div class="weather-value">${d.uv_index_max[i]}</div>
            </div>
            <div class="weather-item">
              <div class="weather-icon icon-wind">
                <i class="fas fa-wind"></i>
              </div>
              <div class="weather-label">ลมสูงสุด:</div>
              <div class="weather-value">${d.windspeed_10m_max[i]} km/h</div>
            </div>
            <div class="weather-item">
              <div class="weather-icon icon-gust">
                <i class="fas fa-hurricane"></i>
              </div>
              <div class="weather-label">ลมกระโชก:</div>
              <div class="weather-value">${d.windgusts_10m_max[i]} km/h</div>
            </div>
            <div class="weather-item">
              <div class="weather-icon icon-sunrise">
                <i class="fas fa-sun"></i>
              </div>
              <div class="weather-label">พระอาทิตย์ขึ้น:</div>
              <div class="weather-value">${sunrise}</div>
            </div>
            <div class="weather-item">
              <div class="weather-icon icon-sunset">
                <i class="fas fa-moon"></i>
              </div>
              <div class="weather-label">พระอาทิตย์ตก:</div>
              <div class="weather-value">${sunset}</div>
            </div>
          </div>
        `;
        forecastDiv.appendChild(card);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('forecast').innerHTML = `
        <div class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <h3>ไม่สามารถโหลดข้อมูลพยากรณ์อากาศได้</h3>
          <p>กรุณาลองใหม่อีกครั้ง</p>
        </div>
      `;
    })
    .finally(() => {
      document.getElementById('loading').style.display = 'none';
    });
}

// Auto-detect location on page load
window.addEventListener('load', function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      position => {
        fetchForecast(position.coords.latitude, position.coords.longitude);
      },
      error => {
        const status = document.getElementById('status');
        let errorMsg = 'ไม่สามารถระบุตำแหน่งของคุณได้ - แสดงข้อมูลกรุงเทพฯ';
        
        // Default to Bangkok coordinates
        fetchForecast(13.7563, 100.5018);
        status.innerHTML = `📍 ${errorMsg}`;
        status.className = 'status-text';
      }
    );
  } else {
    // Default to Bangkok if geolocation not supported
    fetchForecast(13.7563, 100.5018);
    document.getElementById('status').innerHTML = '📍 แสดงข้อมูลกรุงเทพฯ (ไม่รองรับการระบุตำแหน่ง)';
  }
});
</script>


<?php
require_once __DIR__ . '/plugin/footer_user.php';
?>