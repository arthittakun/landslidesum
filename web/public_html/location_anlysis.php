<?php include 'plugin/header.php'; ?>

<style>
  .gauge-container {
    position: relative;
    display: inline-block;
    width: 200px;
    height: 150px;
  }
  
  .gauge {
    width: 200px;
    height: 100px;
    overflow: hidden;
    position: relative;
  }
  
  .gauge::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: conic-gradient(
      from 0deg,
      #22c55e 0deg 60deg,
      #f59e0b 60deg 120deg,
      #ef4444 120deg 180deg
    );
  }
  
  .gauge-needle {
    position: absolute;
    top: 95px;
    left: 97px;
    width: 6px;
    height: 90px;
    background: var(--text-primary, #333);
    transform-origin: bottom center;
    transition: transform 0.5s ease;
    border-radius: 3px;
    z-index: 10;
  }
  
  .gauge-center {
    position: absolute;
    top: 85px;
    left: 85px;
    width: 30px;
    height: 30px;
    background: var(--text-primary, #333);
    border-radius: 50%;
    z-index: 11;
  }
  
  .gauge-value {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    font-weight: bold;
    font-size: 18px;
    color: var(--text-primary, #333);
  }
  
  .sensor-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px var(--shadow-color);
    transition: transform 0.3s ease;
  }
  
  .sensor-card:hover {
    transform: translateY(-2px);
  }

  .location-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px var(--shadow-color);
  }

  .filter-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px var(--shadow-color);
  }
</style>

<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
        <i class="fas fa-chart-line text-blue-600 mr-3"></i>
        วิเคราะห์ข้อมูลตามโลเคชัน
      </h1>
      <p class="text-muted">แสดงข้อมูลล่าสุดของเซ็นเซอร์แต่ละตำแหน่ง</p>
    </div>

    <!-- Location Filter -->
    <div class="mb-6 filter-card rounded-lg p-6">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <label class="font-medium" style="color: var(--text-primary);">เลือกโลเคชัน:</label>
        <select id="locationFilter" class="flex-1 md:flex-none md:w-64 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                style="background-color: var(--bg-input); color: var(--text-primary); border-color: var(--border-color);">
          <option value="all">แสดงทั้งหมด</option>
        </select>
        <button id="refreshData" class="btn btn-primary">
          <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
        </button>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Data Container -->
    <div id="dataContainer" class="hidden">
      <!-- Location cards will be inserted here -->
    </div>

    <!-- No Data Message -->
    <div id="noDataMessage" class="hidden text-center py-12">
      <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
      <h3 class="text-xl font-semibold mb-2" style="color: var(--text-primary);">ไม่พบข้อมูล</h3>
      <p class="text-muted">ไม่มีข้อมูลสำหรับโลเคชันที่เลือก</p>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
class LocationAnalysis {
  constructor() {
    this.currentData = [];
    this.init();
  }

  init() {
    this.loadLocations();
    this.loadData();
    this.bindEvents();
    
    // Auto refresh every 30 seconds
    setInterval(() => {
      this.loadData();
    }, 30000);
  }

  bindEvents() {
    document.getElementById('locationFilter').addEventListener('change', () => {
      this.loadData();
    });

    document.getElementById('refreshData').addEventListener('click', () => {
      this.loadData();
    });
  }

  async loadLocations() {
    try {
      const response = await fetch('api/location/list.php');
      const result = await response.json();
      
      if (result.success) {
        const select = document.getElementById('locationFilter');
        result.data.forEach(location => {
          const option = document.createElement('option');
          option.value = location.location_id;
          option.textContent = location.location_name;
          select.appendChild(option);
        });
      }
    } catch (error) {
      console.error('Error loading locations:', error);
    }
  }

  async loadData() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const dataContainer = document.getElementById('dataContainer');
    const noDataMessage = document.getElementById('noDataMessage');
    
    loadingIndicator.classList.remove('hidden');
    dataContainer.classList.add('hidden');
    noDataMessage.classList.add('hidden');

    try {
      const locationId = document.getElementById('locationFilter').value;
      const url = `api/location/latest-data.php${locationId !== 'all' ? `?location_id=${locationId}` : ''}`;
      
      const response = await fetch(url);
      const result = await response.json();
      
      if (result.success && result.data.length > 0) {
        this.currentData = result.data;
        this.renderData();
        dataContainer.classList.remove('hidden');
      } else {
        noDataMessage.classList.remove('hidden');
      }
    } catch (error) {
      console.error('Error loading data:', error);
      this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
      noDataMessage.classList.remove('hidden');
    } finally {
      loadingIndicator.classList.add('hidden');
    }
  }

  showNotification(message, type = 'info') {
    if (window.notify) {
      window.notify(message, type);
    } else {
      console.log(`${type.toUpperCase()}: ${message}`);
    }
  }

  renderData() {
    const container = document.getElementById('dataContainer');
    container.innerHTML = '';

    this.currentData.forEach(location => {
      const locationCard = this.createLocationCard(location);
      container.appendChild(locationCard);
    });
  }

  createLocationCard(location) {
    const card = document.createElement('div');
    card.className = 'mb-8 location-card rounded-lg shadow-lg overflow-hidden';
    
    const devicesHTML = location.devices.map(device => {
      return this.createDeviceSection(device);
    }).join('');

    card.innerHTML = `
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
        <h2 class="text-2xl font-bold mb-2">
          <i class="fas fa-map-marker-alt mr-2"></i>
          ${location.location_name}
        </h2>
        <p class="opacity-90">
          <i class="fas fa-info-circle mr-1"></i>
          รหัส: ${location.location_id} | 
          พิกัด: ${location.latitude}, ${location.longtitude}
        </p>
        <p class="text-sm opacity-80 mt-2">
          <i class="fas fa-microchip mr-1"></i>
          อุปกรณ์: ${location.devices.length} เครื่อง
        </p>
      </div>
      <div class="p-6">
        ${devicesHTML || '<p class="text-muted text-center py-8">ไม่มีข้อมูลอุปกรณ์</p>'}
      </div>
    `;

    return card;
  }

  createDeviceSection(device) {
    const data = device.latest_data;
    
    return `
      <div class="mb-8 border-l-4 border-blue-500 pl-4">
        <h3 class="text-xl font-semibold mb-4" style="color: var(--text-primary);">
          <i class="fas fa-desktop mr-2 text-blue-600"></i>
          ${device.device_name}
          <span class="text-sm font-normal text-muted">(${device.device_id})</span>
        </h3>
        
        <div class="mb-4 p-3 rounded-lg" style="background-color: var(--bg-secondary);">
          <p class="text-sm text-muted">
            <i class="fas fa-clock mr-1"></i>
            ข้อมูลล่าสุด: ${data.datetime || 'ไม่มีข้อมูล'}
          </p>
          ${this.getStatusBadges(data)}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          ${this.createGaugeCard('อุณหภูมิ', data.temp, '°C', 0, 50, 'temperature')}
          ${this.createGaugeCard('ความชื้น', data.humid, '%', 0, 100, 'humidity')}
          ${this.createGaugeCard('ปริมาณฝน', data.rain, 'mm', 0, 100, 'rain')}
          ${this.createGaugeCard('การสั่นสะเทือน', data.vibration, 'Hz', 0, 10, 'vibration')}
          ${this.createGaugeCard('ระยะทาง', data.distance, 'cm', 0, 400, 'distance')}
          ${this.createGaugeCard('ความชื้นดิน', data.soil, '%', 0, 100, 'soil')}
          ${this.createGaugeCard('ความชื้นดิน (สูง)', data.soil_high, '%', 0, 100, 'soil_high')}
        </div>
      </div>
    `;
  }

  getStatusBadges(data) {
    let badges = '';
    
    if (data.landslide === 1) {
      badges += '<span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm mr-2 mt-2"><i class="fas fa-mountain mr-1"></i>เตือนดินถล่ม</span>';
    }
    
    if (data.flood === 1) {
      badges += '<span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm mr-2 mt-2"><i class="fas fa-water mr-1"></i>เตือนน้ำท่วม</span>';
    }

    return badges ? `<div class="mt-2">${badges}</div>` : '';
  }

  createGaugeCard(title, value, unit, min, max, type) {
    const percentage = ((value - min) / (max - min)) * 100;
    const rotation = (percentage / 100) * 180 - 90;
    const color = this.getGaugeColor(percentage);
    const gaugeId = `gauge-${type}-${Math.random().toString(36).substr(2, 9)}`;

    return `
      <div class="sensor-card text-center">
        <h4 class="font-semibold mb-3" style="color: var(--text-primary);">${title}</h4>
        <div class="gauge-container mx-auto mb-3">
          <div class="gauge">
            <div class="gauge-needle" id="${gaugeId}" style="transform: rotate(${rotation}deg); background: ${color};"></div>
            <div class="gauge-center"></div>
          </div>
          <div class="gauge-value" style="color: ${color};">
            ${value}${unit}
          </div>
        </div>
        <div class="text-xs text-muted">
          ช่วง: ${min} - ${max}${unit}
        </div>
        ${this.getValueStatus(type, value)}
      </div>
    `;
  }

  getGaugeColor(percentage) {
    if (percentage <= 33) return '#22c55e';
    if (percentage <= 66) return '#f59e0b';
    return '#ef4444';
  }

  getValueStatus(type, value) {
    let status = '';
    let statusClass = 'bg-green-100 text-green-800';

    switch (type) {
      case 'temperature':
        if (value > 35) {
          status = 'ร้อนจัด';
          statusClass = 'bg-red-100 text-red-800';
        } else if (value > 25) {
          status = 'ปกติ';
        } else {
          status = 'เย็น';
          statusClass = 'bg-blue-100 text-blue-800';
        }
        break;
      case 'humidity':
        if (value > 80) {
          status = 'ชื้นสูง';
          statusClass = 'bg-yellow-100 text-yellow-800';
        } else if (value > 40) {
          status = 'ปกติ';
        } else {
          status = 'แห้ง';
          statusClass = 'bg-orange-100 text-orange-800';
        }
        break;
      case 'rain':
        if (value > 50) {
          status = 'ฝนหนัก';
          statusClass = 'bg-red-100 text-red-800';
        } else if (value > 10) {
          status = 'ฝนปานกลาง';
          statusClass = 'bg-yellow-100 text-yellow-800';
        } else if (value > 0) {
          status = 'ฝนเบา';
        } else {
          status = 'ไม่มีฝน';
        }
        break;
      case 'vibration':
        if (value > 5) {
          status = 'สั่นสะเทือนสูง';
          statusClass = 'bg-red-100 text-red-800';
        } else if (value > 2) {
          status = 'สั่นสะเทือนปานกลาง';
          statusClass = 'bg-yellow-100 text-yellow-800';
        } else {
          status = 'ปกติ';
        }
        break;
      case 'distance':
        if (value < 50) {
          status = 'ระยะใกล้มาก';
          statusClass = 'bg-red-100 text-red-800';
        } else if (value < 150) {
          status = 'ระยะใกล้';
          statusClass = 'bg-yellow-100 text-yellow-800';
        } else {
          status = 'ระยะปลอดภัย';
        }
        break;
      case 'soil':
      case 'soil_high':
        if (value > 80) {
          status = 'ชื้นมาก';
          statusClass = 'bg-blue-100 text-blue-800';
        } else if (value > 60) {
          status = 'ชื้นปานกลาง';
          statusClass = 'bg-yellow-100 text-yellow-800';
        } else if (value > 30) {
          status = 'ปกติ';
        } else {
          status = 'แห้ง';
          statusClass = 'bg-orange-100 text-orange-800';
        }
        break;
      default:
        status = 'ปกติ';
    }

    return `<div class="mt-2 px-2 py-1 rounded-full text-xs ${statusClass}">${status}</div>`;
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Set active navigation
  const currentPath = window.location.pathname;
  const navLinks = document.querySelectorAll('.nav-link[data-page]');
  navLinks.forEach(link => {
    if (currentPath.includes('location_anlysis') && link.getAttribute('data-page') === 'location_anlysis') {
      link.classList.add('active');
    }
  });

  // Initialize the analysis page
  new LocationAnalysis();
});
</script>

<?php include 'plugin/footer.php'; ?>