
<?php
require_once __DIR__ . '/plugin/header_user.php';
?>
<style>
.weather-eadar-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.weather-eadar-title {
  text-align: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #0ea5e9, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.radar-header-bar {
  position: relative;
  width: 100%;
  background: linear-gradient(135deg, #0ea5e9, #10b981);
  color: #fff;
  font-size: 1.2rem;
  font-weight: 700;
  text-align: center;
  padding: 1rem 0.5rem 0.7rem 0.5rem;
  border-radius: 16px 16px 0 0;
  box-shadow: 0 2px 12px var(--shadow-color);
  z-index: 1100;
  letter-spacing: 1px;
}

.radar-map-container {
  position: relative;
  background: var(--bg-card);
  border-radius: 0 0 16px 16px;
  box-shadow: 0 8px 25px var(--shadow-color);
  border: 1px solid var(--border-color);
  border-top: none;
  overflow: hidden;
}

#radar-map {
  width: 100%;
  height: 60vh;
  min-height: 320px;
  max-height: 70vh;
  border-radius: 0 0 16px 16px;
}

.radar-gps-btn {
  position: absolute;
  top: 70px;
  right: 18px;
  z-index: 1200;
  background: var(--bg-card);
  border: 2px solid var(--color-primary);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  box-shadow: 0 4px 16px var(--shadow-color);
  cursor: pointer;
  transition: all 0.3s ease;
  outline: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.radar-gps-btn:hover { 
  background: var(--color-primary);
  transform: scale(1.08);
  box-shadow: 0 6px 24px var(--shadow-color);
}

.radar-gps-btn i {
  font-size: 1.25rem;
  color: var(--color-primary);
  transition: color 0.3s ease;
}

.radar-gps-btn:hover i {
  color: white;
}

.radar-info-box {
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 8px 25px var(--shadow-color);
  padding: 1.5rem;
  font-size: 1rem;
  color: var(--text-primary);
  line-height: 1.7;
  border: 1px solid var(--border-color);
  margin: 1.5rem 0;
  position: relative;
  overflow: hidden;
}

.radar-info-box::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #0ea5e9, #10b981);
}

.radar-ctrl-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.radar-ctrl-row button {
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #0ea5e9, #10b981);
  color: white;
  font-size: 1rem;
  font-family: 'Prompt', Arial, sans-serif;
  font-weight: 600;
  box-shadow: 0 4px 12px var(--shadow-color);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.radar-ctrl-row button:hover { 
  background: linear-gradient(135deg, #0284c7, #059669);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px var(--shadow-color);
}

.radar-ctrl-row button:active { 
  transform: translateY(0);
}

#radar-time { 
  font-size: 1.1rem; 
  color: var(--color-primary); 
  font-weight: bold;
  padding: 0.5rem 1rem;
  background: var(--bg-card);
  border-radius: 8px;
  border: 1px solid var(--border-color);
  flex: 1;
  text-align: center;
}

.radar-legend {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
  margin: 1.5rem 0;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.radar-legend-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  background: var(--bg-card);
  border-radius: 8px;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.radar-legend-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px var(--shadow-color);
}

.radar-legend-color {
  width: 24px;
  height: 16px;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  display: inline-block;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.radar-legend-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.radar-description {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(16, 185, 129, 0.05));
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 1rem;
}

.radar-description h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.radar-description p {
  font-size: 0.9rem;
  color: var(--text-secondary);
  line-height: 1.6;
  margin-bottom: 0.5rem;
}

.location-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--bg-card);
  border-radius: 8px;
  border: 1px solid var(--border-color);
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.location-display i {
  color: var(--color-primary);
}

/* Time Range Display */
.time-range-display {
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(16, 185, 129, 0.05));
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1rem;
  margin: 1rem 0;
  text-align: center;
}

.time-range-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.time-range-header i {
  color: var(--color-primary);
}

.time-range-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--bg-card);
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  display: inline-block;
  min-width: 200px;
}

@media (max-width: 640px) {
  .time-range-display {
    padding: 0.75rem;
    margin: 0.75rem 0;
  }
  
  .time-range-header {
    font-size: 0.8rem;
  }
  
  .time-range-value {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
    min-width: auto;
    width: 100%;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .weather-eadar-container {
    padding: 0.5rem;
  }
  
  .weather-eadar-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
  }
  
  .radar-header-bar { 
    font-size: 1.05rem; 
    padding: 0.75rem 0.5rem;
    border-radius: 12px 12px 0 0;
  }
  
  .radar-map-container {
    border-radius: 0 0 12px 12px;
  }
  
  #radar-map { 
    min-height: 280px; 
    height: 50vh;
    border-radius: 0 0 12px 12px;
  }
  
  .radar-gps-btn { 
    width: 40px; 
    height: 40px; 
    top: 60px; 
    right: 12px;
  }
  
  .radar-gps-btn i {
    font-size: 1.1rem;
  }
  
  .radar-info-box { 
    padding: 1rem;
    margin: 1rem 0;
  }
  
  .radar-ctrl-row {
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.75rem;
  }
  
  .radar-ctrl-row button {
    width: 100%;
    justify-content: center;
    padding: 0.75rem;
  }
  
  #radar-time {
    width: 100%;
    font-size: 1rem;
  }
  
  .radar-legend { 
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.5rem;
    padding: 0.75rem;
  }
  
  .radar-legend-item {
    padding: 0.375rem;
  }
  
  .radar-legend-color { 
    width: 20px; 
    height: 12px;
  }
  
  .radar-legend-text {
    font-size: 0.8rem;
  }
  
  .radar-description {
    padding: 1rem;
  }
  
  .radar-description h4 {
    font-size: 1rem;
  }
  
  .radar-description p {
    font-size: 0.85rem;
  }
}

@media (max-width: 480px) {
  .radar-legend { 
    grid-template-columns: 1fr;
  }
  
  .radar-ctrl-row {
    padding: 0.5rem;
  }
  
  .radar-info-box {
    padding: 0.75rem;
  }
  
  .weather-eadar-title {
    font-size: 1.1rem;
  }
}

/* Loading state */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--text-secondary);
}

.loading-spinner {
  width: 2.5rem;
  height: 2.5rem;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--color-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced animations */
.fade-in {
  animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.slide-up {
  animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>

<div class="weather-eadar-container">
  <h1 class="weather-eadar-title fade-in">🌧️ แผนที่เรดาร์ฝน (Rain Radar LIVE)</h1>
  
  <div class="radar-map-container slide-up">
    <div class="radar-header-bar">
      <i class="fas fa-satellite-dish mr-2"></i>
      ข้อมูลเรดาร์ฝนแบบเรียลไทม์
    </div>
    <div style="position:relative;">
      <div id="radar-map"></div>
      <button id="radar-gps-btn" class="radar-gps-btn" title="ดูตำแหน่งฉัน">
        <i class="fas fa-crosshairs"></i>
      </button>
    </div>
  </div>

  <div class="radar-info-box fade-in">
    <div class="radar-ctrl-row">
      <button id="radarPlayPauseBtn">
        <i class="fas fa-pause"></i>
        <span>หยุด</span>
      </button>
      <div id="radar-time">กำลังโหลด...</div>
    </div>
    
    <!-- เพิ่ม Time Range Display -->
    <div class="time-range-display">
      <div class="time-range-header">
        <i class="fas fa-history"></i>
        <span>ช่วงเวลาข้อมูลเรดาร์</span>
      </div>
      <div id="radar-time-range" class="time-range-value">
        กำลังคำนวณ...
      </div>
    </div>
    
    <div class="radar-legend">
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#00fa00"></span>
        <span class="radar-legend-text">ฝนเบามาก</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#00c3ff"></span>
        <span class="radar-legend-text">ฝนเบา</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#006aff"></span>
        <span class="radar-legend-text">ฝนปานกลาง</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#001fff"></span>
        <span class="radar-legend-text">ฝนค่อนข้างหนัก</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#fffa00"></span>
        <span class="radar-legend-text">ฝนหนัก</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#ff9600"></span>
        <span class="radar-legend-text">ฝนหนักมาก</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#ff0000"></span>
        <span class="radar-legend-text">ฝนรุนแรง</span>
      </div>
      <div class="radar-legend-item">
        <span class="radar-legend-color" style="background:#d600ff"></span>
        <span class="radar-legend-text">ฝนรุนแรงมาก</span>
      </div>
    </div>
    
    <div class="radar-description">
      <h4>
        <i class="fas fa-info-circle"></i>
        คำอธิบายสีเรดาร์ฝน
      </h4>
      <p><strong>สีเขียว/ฟ้า:</strong> ฝนเบา เหมาะสำหรับกิจกรรมกลางแจ้ง</p>
      <p><strong>สีน้ำเงิน:</strong> ฝนปานกลาง ควรเตรียมร่มและเสื้อกันฝน</p>
      <p><strong>สีเหลือง/ส้ม:</strong> ฝนหนัก อาจมีน้ำท่วมขัง หลีกเลี่ยงการเดินทาง</p>
      <p><strong>สีแดง/ม่วง:</strong> ฝนรุนแรง เสี่ยงต่อดินถล่มและน้ำท่วมฉับพลัน ควรหาที่หลบภัย</p>
      
      <div class="location-display" id="radar-location">
        <i class="fas fa-map-marker-alt"></i>
        <span>กำลังค้นหาตำแหน่งของคุณ...</span>
      </div>
    </div>
  </div>
</div>

<!-- Leaflet -->
<link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
let radarLayers = [];
let radarFrames = [];
let currentFrame = 0;
let playTimer = null;
let playing = true;
let userMarker = null;
let radarHost = '';
let loadingRadar = false;
let userLat = 13.75, userLon = 100.52;

// ใช้ scale ตาม API (RainViewer) เพื่อให้สีตรงกับ API จริง
function getTileScale(zoom) {
  if (zoom < 7) return 2;
  else if (zoom < 10) return 1;
  else return 0;
}

// Fetch radar data from API (real frames only)
async function fetchRadarData() {
  loadingRadar = true;
  try {
    const res = await fetch('/api/weather/rainviewer-radar');
    const data = await res.json();
    if (!data.host || !data.frames) throw new Error('Radar data missing');
    radarHost = data.host;
    radarFrames = data.frames;
  } catch (e) {
    document.getElementById('radar-time').innerText = 'ไม่พบข้อมูลเรดาร์';
    radarFrames = [];
  }
  loadingRadar = false;
}

// Render radar tile layers for all real frames
function renderRadarLayers(map) {
  radarLayers.forEach(layer => map.removeLayer(layer));
  radarLayers = [];
  const scale = getTileScale(map.getZoom());
  radarLayers = radarFrames.map(f =>
    L.tileLayer(`${radarHost}${f.path}/256/{z}/{x}/{y}/${scale}/1_0.png`, {
      opacity: 0,
      zIndex: 100
    }).addTo(map)
  );
}

// Calculate and display time range (current time - 30 minutes to current time)
function updateTimeRange() {
  const now = new Date();
  const thirtyMinutesAgo = new Date(now.getTime() - (30 * 60 * 1000));
  
  const formatTime = (date) => {
    return date.toLocaleTimeString('th-TH', { 
      hour: '2-digit', 
      minute: '2-digit',
      timeZone: 'Asia/Bangkok'
    });
  };
  
  const formatDate = (date) => {
    return date.toLocaleDateString('th-TH', { 
      day: '2-digit',
      month: '2-digit',
      year: '2-digit',
      timeZone: 'Asia/Bangkok'
    });
  };
  
  // Check if dates are the same
  const isSameDate = formatDate(thirtyMinutesAgo) === formatDate(now);
  
  let timeRangeText;
  if (isSameDate) {
    // Same date: show only times
    timeRangeText = `${formatTime(thirtyMinutesAgo)} - ${formatTime(now)}`;
  } else {
    // Different dates: show dates + times
    timeRangeText = `${formatDate(thirtyMinutesAgo)} ${formatTime(thirtyMinutesAgo)} - ${formatDate(now)} ${formatTime(now)}`;
  }
  
  document.getElementById('radar-time-range').textContent = timeRangeText;
}

// Show selected frame (for animation or manual) - แก้ไขให้แสดงเวลาปัจจุบัน
function showFrame(idx) {
  if (!radarLayers.length) return;
  radarLayers.forEach((layer, i) => layer.setOpacity(i === idx ? 0.69 : 0));
  
  // แสดงเวลาปัจจุบันแทนเวลาจาก API
  const now = new Date();
  const timeStr = now.toLocaleString('th-TH', { 
    dateStyle: 'short', 
    timeStyle: 'short',
    timeZone: 'Asia/Bangkok'
  });
  document.getElementById('radar-time').innerText = timeStr;
  
  // อัปเดต time range ด้วย
  updateTimeRange();
}


// Animation: Only plays real frames from current data (no repeat)
function playRadar() {
  if (playTimer) clearTimeout(playTimer);
  if (!playing) return;
  if (!radarLayers.length) return;
  currentFrame = (currentFrame + 1) % radarLayers.length;
  showFrame(currentFrame);
  playTimer = setTimeout(playRadar, 500);
}

// Reload all real frames and refresh layers
async function reloadRadarData(map) {
  await fetchRadarData();
  renderRadarLayers(map);
  if (currentFrame >= radarLayers.length) currentFrame = radarLayers.length - 1;
  showFrame(currentFrame);
}

// Detect user location and update map
function setUserLocation(map, lat, lon) {
  userLat = lat;
  userLon = lon;
  map.setView([lat, lon], 10);
  if (userMarker) map.removeLayer(userMarker);
  userMarker = L.circleMarker([lat, lon], {
    radius: 12, 
    color: '#0ea5e9', 
    fillColor: '#10b981', 
    fillOpacity: 0.8, 
    weight: 3
  }).addTo(map).bindPopup('<strong>คุณอยู่ที่นี่</strong>').openPopup();
  
  document.getElementById('radar-location').innerHTML = `
    <i class="fas fa-map-marker-alt"></i>
    <span>พิกัด: ${lat.toFixed(4)}, ${lon.toFixed(4)}</span>
  `;
}

window.addEventListener('DOMContentLoaded', async function() {
  // Initial Map
  const map = L.map('radar-map', { zoomControl: false }).setView([userLat, userLon], 6);
  L.control.zoom({ position: 'bottomright' }).addTo(map);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // GPS Button
  document.getElementById('radar-gps-btn').addEventListener('click', function() {
    if (!navigator.geolocation) {
      alert('เบราว์เซอร์ไม่รองรับ GPS');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      position => {
        setUserLocation(map, position.coords.latitude, position.coords.longitude);
      },
      () => alert('ไม่สามารถระบุตำแหน่งคุณได้')
    );
  });

  // Auto-detect location on load
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => setUserLocation(map, pos.coords.latitude, pos.coords.longitude),
      () => setUserLocation(map, userLat, userLon)
    );
  } else {
    setUserLocation(map, userLat, userLon);
  }

  // Radar animation controls
  document.getElementById('radarPlayPauseBtn').addEventListener('click', function() {
    playing = !playing;
    const icon = this.querySelector('i');
    const text = this.querySelector('span');
    if (playing) {
      icon.className = 'fas fa-pause';
      text.textContent = 'หยุด';
      playRadar();
    } else {
      icon.className = 'fas fa-play';
      text.textContent = 'เล่น';
      if (playTimer) clearTimeout(playTimer);
    }
  });

  // Change tile scale by zoom level
  map.on('zoomend', () => {
    renderRadarLayers(map);
    showFrame(currentFrame);
  });

  // Initial load and animate
  await reloadRadarData(map);
  playing = true;
  playRadar();

  // Auto reload every 1 minute (use only new frames, no repeat)
  setInterval(() => reloadRadarData(map), 60 * 1000);
});
</script>



<?php
require_once __DIR__ . '/plugin/footer_user.php';
?>