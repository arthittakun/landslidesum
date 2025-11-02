<?php
require_once __DIR__ . '/plugin/header_user.php';
?>
<style>
.weather-hour-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.5rem;
}

.weather-hour-title {
  text-align: center;
  font-size: 1.25rem;
  font-weight: bold;
  margin-bottom: 0.75rem;
  background: linear-gradient(135deg, #0ea5e9, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

#hourStatus {
  text-align: center;
  margin-bottom: 1rem;
  color: var(--text-secondary, #555);
  font-size: 0.875rem;
  font-weight: 500;
  padding: 0.5rem;
  background: var(--bg-secondary);
  border-radius: 8px;
}

/* Current Weather Card - แบบโทรศัพท์ */
.current-weather-container {
  background: var(--bg-card);
  border-radius: 12px;
  box-shadow: 0 3px 12px var(--shadow-color);
  padding: 1rem;
  border: 1px solid var(--border-color);
  margin-bottom: 1rem;
  position: relative;
  overflow: hidden;
}

.current-weather-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #0ea5e9, #10b981);
}

.current-weather-main {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.current-weather-left {
  flex: 1;
}

.current-weather-temp {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #0ea5e9, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.current-weather-location {
  font-size: 0.875rem;
  color: var(--text-primary);
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.current-weather-desc {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.current-weather-icon-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.current-weather-icon-large {
  font-size: 2.5rem;
  color: var(--color-primary);
}

.current-weather-time {
  font-size: 0.7rem;
  color: var(--text-muted);
  text-align: center;
}

.current-weather-details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--border-color);
}

.current-weather-item {
  text-align: center;
  padding: 0.5rem 0.25rem;
  background: var(--bg-secondary);
  border-radius: 6px;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.current-weather-item:hover {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(16, 185, 129, 0.1));
  transform: translateY(-1px);
}

.current-weather-item i {
  font-size: 1rem;
  margin-bottom: 0.25rem;
  color: var(--color-primary);
}

.current-weather-label {
  font-size: 0.65rem;
  color: var(--text-muted);
  margin-bottom: 0.125rem;
  font-weight: 500;
}

.current-weather-value {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-primary);
  line-height: 1;
}

/* Hourly Cards - แบบแนวนอนเหมือนใน App */
.weather-hour-cards {
  display: flex;
  overflow-x: auto;
  gap: 0.75rem;
  padding: 0.5rem 0;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.weather-hour-cards::-webkit-scrollbar {
  display: none;
}

.weather-hour-card {
  background: var(--bg-card);
  border-radius: 8px;
  box-shadow: 0 2px 8px var(--shadow-color);
  border: 1px solid var(--border-color);
  padding: 0.75rem 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 110px;
  max-width: 110px;
  text-align: center;
  position: relative;
  scroll-snap-align: start;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.weather-hour-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px var(--shadow-color);
}

.weather-hour-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #0ea5e9, #10b981);
}

/* Weather type specific colors */
.weather-hour-card.sunny::before {
  background: linear-gradient(90deg, #fbbf24, #f59e0b);
}

.weather-hour-card.cloudy::before {
  background: linear-gradient(90deg, #9ca3af, #6b7280);
}

.weather-hour-card.rainy::before {
  background: linear-gradient(90deg, #3b82f6, #1d4ed8);
}

.weather-hour-card.thunderstorm::before {
  background: linear-gradient(90deg, #7c3aed, #5b21b6);
}

.weather-hour-card.foggy::before {
  background: linear-gradient(90deg, #d1d5db, #9ca3af);
}

.weather-hour-time {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
  white-space: nowrap;
}

.weather-hour-icon {
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
  color: var(--color-primary);
}

.weather-hour-temp {
  font-size: 1rem;
  font-weight: 700;
  background: linear-gradient(135deg, #0ea5e9, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.375rem;
  line-height: 1;
}

.weather-hour-details-mini {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  width: 100%;
}

.weather-hour-detail-mini {
  font-size: 0.6rem;
  color: var(--text-muted);
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.125rem;
}

.weather-hour-detail-mini i {
  font-size: 0.55rem;
  width: 8px;
  text-align: center;
}

.weather-hour-detail-value-mini {
  font-weight: 600;
  color: var(--text-primary);
}

/* Section Title */
.section-title {
  font-size: 1rem;
  font-weight: 600;
  margin: 1.5rem 0 0.75rem 0;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: var(--bg-secondary);
  border-radius: 6px;
  border-left: 3px solid var(--color-primary);
}

/* Scroll indicator */
.scroll-indicator {
  text-align: center;
  font-size: 0.7rem;
  color: var(--text-muted);
  margin-top: 0.5rem;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
}

.scroll-indicator i {
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateX(0);
  }
  40% {
    transform: translateX(-3px);
  }
  60% {
    transform: translateX(3px);
  }
}

/* Icon colors based on weather conditions */
.weather-hour-card.sunny .weather-hour-icon { color: #fbbf24; }
.weather-hour-card.cloudy .weather-hour-icon { color: #9ca3af; }
.weather-hour-card.rainy .weather-hour-icon { color: #3b82f6; }
.weather-hour-card.thunderstorm .weather-hour-icon { color: #7c3aed; }
.weather-hour-card.foggy .weather-hour-icon { color: #d1d5db; }

/* Dark theme icon adjustments */
[data-theme="dark"] .weather-hour-card.sunny .weather-hour-icon { color: #fde047; }
[data-theme="dark"] .weather-hour-card.cloudy .weather-hour-icon { color: #d1d5db; }
[data-theme="dark"] .weather-hour-card.rainy .weather-hour-icon { color: #60a5fa; }
[data-theme="dark"] .weather-hour-card.thunderstorm .weather-hour-icon { color: #a78bfa; }
[data-theme="dark"] .weather-hour-card.foggy .weather-hour-icon { color: #f3f4f6; }

/* Enhanced Responsive Design */
@media (max-width: 480px) {
  .weather-hour-container {
    padding: 0.25rem;
  }
  
  .weather-hour-title {
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
  }
  
  .current-weather-container {
    padding: 0.75rem;
    margin-bottom: 0.75rem;
  }
  
  .current-weather-temp {
    font-size: 1.75rem;
  }
  
  .current-weather-icon-large {
    font-size: 2rem;
  }
  
  .current-weather-details {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.375rem;
  }
  
  .current-weather-item {
    padding: 0.375rem 0.25rem;
  }
  
  .weather-hour-card {
    min-width: 95px;
    max-width: 95px;
    padding: 0.625rem 0.375rem;
  }
  
  .weather-hour-time {
    font-size: 0.65rem;
  }
  
  .weather-hour-icon {
    font-size: 1.5rem;
  }
  
  .weather-hour-temp {
    font-size: 0.9rem;
  }
  
  .weather-hour-detail-mini {
    font-size: 0.55rem;
  }
  
  .weather-hour-detail-mini i {
    font-size: 0.5rem;
  }
  
  .current-weather-details {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.375rem;
  }
}

@media (min-width: 481px) and (max-width: 768px) {
  .current-weather-details {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .weather-hour-card {
    min-width: 100px;
    max-width: 100px;
  }
}

@media (min-width: 769px) {
  .current-weather-details {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .weather-hour-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
    gap: 0.75rem;
    overflow: visible;
  }
  
  .weather-hour-card {
    scroll-snap-align: none;
    min-width: auto;
    max-width: none;
  }
  
  .scroll-indicator {
    display: none;
  }
}

/* Special loading state */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--color-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 0.75rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<div class="weather-hour-container">
  <div class="weather-hour-title">🌦️ พยากรณ์อากาศรายชั่วโมง</div>
  <div id="hourStatus">📍 กำลังค้นหาตำแหน่งของคุณ...</div>
  <div id="loadingHour" class="loading-container" style="display:none;">
    <div class="loading-spinner"></div>
    <p>กำลังโหลดข้อมูลพยากรณ์อากาศ...</p>
  </div>
  <!-- Current Weather Section -->
  <div id="currentWeather"></div>
  <!-- Hourly Forecast Section -->
  <div class="section-title">
    <i class="fas fa-clock"></i>
    24 ชั่วโมงข้างหน้า
  </div>
  <div class="weather-hour-cards" id="hourCards"></div>
  <div class="scroll-indicator">
    <i class="fas fa-arrows-alt-h"></i>
    <span>เลื่อนเพื่อดูเพิ่มเติม</span>
  </div>
</div>

<script>
// Weather code mapping
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
  return weatherCodes[code] || { desc: `รหัส ${code}`, icon: "fas fa-question-circle", class: "unknown" };
}

// ฟังก์ชันแปลงพิกัดเป็นจังหวัด (เพิ่มความแม่นยำ: ใช้ Haversine + ถ้าห่างเกิน 40 กม. ให้แสดง "ใกล้จังหวัด..." หรือ "ตำแหน่งของคุณ")
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

  // ถ้าห่างจากศูนย์กลางจังหวัดมากกว่า 40 กม. ให้แสดง "ใกล้จังหวัด..." เพื่อความแม่นยำ
  if (minDistance > 40) {
    return `ใกล้${closestProvince.name}`;
  }
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

// Helper function to format numbers with decimals
function formatNumber(value, decimals = 1) {
  if (value === null || value === undefined || value === '') return '-';
  const num = parseFloat(value);
  if (isNaN(num)) return '-';
  return num.toFixed(decimals);
}

// Helper function to get wind direction
function getWindDirection(degrees) {
  if (!degrees && degrees !== 0) return '-';
  const directions = ['เหนือ', 'เหนือ-ตะวันออก', 'ตะวันออก', 'ตะวันออก-ใต้', 'ใต้', 'ใต้-ตะวันตก', 'ตะวันตก', 'ตะวันตก-เหนือ'];
  return directions[Math.round(degrees / 45) % 8];
}

async function fetchHourForecast(lat, lon) {
  const province = await getProvinceFromCoords(lat, lon);
  const url = `/api/weather/weather-hour?lat=${lat}&lon=${lon}`;

  document.getElementById('hourStatus').innerHTML = `📍 กำลังโหลดข้อมูลสำหรับ <strong>${province}</strong> ...`;
  document.getElementById('loadingHour').style.display = 'flex';
  document.getElementById('currentWeather').innerHTML = '';
  document.getElementById('hourCards').innerHTML = '';

  fetch(url)
    .then(res => {
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
      return res.json();
    })
    .then(data => {
      if (!data || !data.hourly || !Array.isArray(data.hourly.time)) throw new Error('Invalid data structure');

      // --- Current Weather ---
      const cw = data.current_weather;
      let currentHtml = '';
      if (cw && cw.temperature !== undefined) {
        const weather = getWeatherInfo(cw.weathercode);
        const windDir = getWindDirection(cw.winddirection);
        currentHtml = `
          <div class="current-weather-container ${weather.class}">
            <div class="current-weather-main">
              <div class="current-weather-left">
                <div class="current-weather-location">${province}</div>
                <div class="current-weather-temp">${formatNumber(cw.temperature, 1)}°</div>
                <div class="current-weather-desc">${weather.desc}</div>
              </div>
              <div class="current-weather-icon-container">
                <i class="${weather.icon} current-weather-icon-large"></i>
                <div class="current-weather-time">
                  ${cw.time ? new Date(cw.time).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) : '-'}
                </div>
              </div>
            </div>
            <div class="current-weather-details">
              <div class="current-weather-item">
                <i class="fas fa-wind"></i>
                <div class="current-weather-label">ลม</div>
                <div class="current-weather-value">${formatNumber(cw.windspeed, 1)} km/h</div>
              </div>
              <div class="current-weather-item">
                <i class="fas fa-compass"></i>
                <div class="current-weather-label">ทิศทาง</div>
                <div class="current-weather-value">${windDir}</div>
              </div>
              <div class="current-weather-item">
                <i class="fas ${cw.is_day === 1 ? 'fa-sun' : 'fa-moon'}"></i>
                <div class="current-weather-label">เวลา</div>
                <div class="current-weather-value">${cw.is_day === 1 ? 'วัน' : 'คืน'}</div>
              </div>
              <div class="current-weather-item">
                <i class="fas fa-thermometer-half"></i>
                <div class="current-weather-label">รู้สึก</div>
                <div class="current-weather-value">${formatNumber(cw.temperature, 1)}°C</div>
              </div>
            </div>
          </div>
        `;
      } else {
        currentHtml = `
          <div class="current-weather-container">
            <div class="loading-container">
              <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--color-warning);"></i>
              <p>ไม่พบข้อมูลสภาพอากาศปัจจุบัน</p>
            </div>
          </div>
        `;
      }
      document.getElementById('currentWeather').innerHTML = currentHtml;

      // --- Hourly Cards ---
      const t = data.hourly;
      const cardsDiv = document.getElementById('hourCards');
      document.getElementById('hourStatus').innerHTML = `📍 พยากรณ์สำหรับพื้นที่ <strong>${province}</strong>`;
      cardsDiv.innerHTML = '';
      for (let i = 0; i < 24 && i < t.time.length; i++) {
        const date = new Date(t.time[i]);
        const time = date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
        const weather = getWeatherInfo(t.weathercode?.[i]);
        const cardElement = document.createElement('div');
        cardElement.className = `weather-hour-card ${weather.class}`;
        cardElement.innerHTML = `
          <div class="weather-hour-time">${time}</div>
          <i class="${weather.icon} weather-hour-icon"></i>
          <div class="weather-hour-temp">${formatNumber(t.temperature_2m?.[i], 1)}°</div>
          <div class="weather-hour-details-mini">
            <div class="weather-hour-detail-mini">
              <i class="fas fa-tint"></i>
              <span class="weather-hour-detail-value-mini">${formatNumber(t.precipitation?.[i], 1)}mm</span>
            </div>
            <div class="weather-hour-detail-mini">
              <i class="fas fa-wind"></i>
              <span class="weather-hour-detail-value-mini">${formatNumber(t.windspeed_10m?.[i], 1)}</span>
            </div>
            <div class="weather-hour-detail-mini">
              <i class="fas fa-cloud"></i>
              <span class="weather-hour-detail-value-mini">${t.cloudcover?.[i] ?? '-'}%</span>
            </div>
            <div class="weather-hour-detail-mini">
              <i class="fas fa-weight-hanging"></i>
              <span class="weather-hour-detail-value-mini">${t.pressure_msl?.[i] ?? '-'}</span>
            </div>
            <div class="weather-hour-detail-mini">
              <i class="${weather.icon}"></i>
              <span class="weather-hour-detail-value-mini">${weather.desc}</span>
            </div>
            
          </div>
        `;
        cardsDiv.appendChild(cardElement);
      }
      if (cardsDiv.innerHTML.trim() === '') {
        cardsDiv.innerHTML = `
          <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
            <p>ไม่พบข้อมูลพยากรณ์อากาศ</p>
          </div>
        `;
      }
    })
    .catch(error => {
      document.getElementById('hourStatus').textContent = '❌ ไม่สามารถโหลดข้อมูลพยากรณ์อากาศได้';
      document.getElementById('hourCards').innerHTML = `
        <div style="text-align: center; padding: 2rem; color: var(--color-danger);">
          <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
          <p>เกิดข้อผิดพลาด: ${error.message}</p>
        </div>
      `;
      document.getElementById('currentWeather').innerHTML = `
        <div class="current-weather-container">
          <div class="loading-container">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--color-danger);"></i>
            <p style="color: var(--color-danger);">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
          </div>
        </div>
      `;
    })
    .finally(() => {
      document.getElementById('loadingHour').style.display = 'none';
    });
}

// Detect location and fetch forecast on page load (เหมือน weather_day.php)
window.addEventListener('load', function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      position => {
        fetchHourForecast(position.coords.latitude, position.coords.longitude);
      },
      error => {
        // Default to Bangkok if location not allowed
        document.getElementById('hourStatus').innerHTML = '📍 ไม่สามารถระบุตำแหน่งของคุณได้ - แสดงข้อมูลกรุงเทพฯ';
        fetchHourForecast(13.7563, 100.5018);
      }
    );
  } else {
    document.getElementById('hourStatus').innerHTML = '📍 แสดงข้อมูลกรุงเทพฯ (ไม่รองรับการระบุตำแหน่ง)';
    fetchHourForecast(13.7563, 100.5018);
  }
});
</script>

<?php
require_once __DIR__ . '/plugin/footer_user.php';
?>