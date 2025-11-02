<?php include 'plugin/header.php'; ?>

<style>
  .chart-container {
    position: relative;
    height: 400px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
  }
  
  .stats-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px var(--shadow-color);
    transition: transform 0.2s ease;
  }
  
  .stats-card:hover {
    transform: translateY(-2px);
  }
  
  .filter-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    box-shadow: 0 2px 4px var(--shadow-color);
  }

  .data-table {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
  }

  .critical-alert {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-left: 4px solid #ef4444;
  }

  .warning-alert {
    background: linear-gradient(135deg, #fef3c7, #fed7aa);
    border-left: 4px solid #f59e0b;
  }

  .normal-status {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-left: 4px solid #10b981;
  }
</style>

<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <div class="container mx-auto">
    
    <!-- Header Section -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
        <i class="fas fa-leaf text-green-600 mr-3"></i>
        รายงานสภาพแวดล้อม
      </h1>
      <p class="text-muted">วิเคราะห์และติดตามข้อมูลสภาพแวดล้อมจากเซ็นเซอร์</p>
    </div>

    <!-- Filter Section -->
    <div class="mb-6 filter-card rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">วันที่เริ่มต้น</label>
          <input type="date" id="startDate" class="w-full px-3 py-2 border rounded-lg" 
                 style="background-color: var(--bg-input); color: var(--text-primary); border-color: var(--border-color);">
        </div>
        <div>
          <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">วันที่สิ้นสุด</label>
          <input type="date" id="endDate" class="w-full px-3 py-2 border rounded-lg"
                 style="background-color: var(--bg-input); color: var(--text-primary); border-color: var(--border-color);">
        </div>
        <div>
          <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">โลเคชัน</label>
          <select id="locationFilter" class="w-full px-3 py-2 border rounded-lg"
                  style="background-color: var(--bg-input); color: var(--text-primary); border-color: var(--border-color);">
            <option value="">ทุกโลเคชัน</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">อุปกรณ์</label>
          <select id="deviceFilter" class="w-full px-3 py-2 border rounded-lg"
                  style="background-color: var(--bg-input); color: var(--text-primary); border-color: var(--border-color);">
            <option value="">ทุกอุปกรณ์</option>
          </select>
        </div>
        <div class="flex items-end">
          <button id="applyFilter" class="btn btn-primary w-full">
            <i class="fas fa-search mr-2"></i>ค้นหา
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-8 hidden">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Statistics Cards -->
    <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 hidden">
      <!-- Stats cards will be inserted here -->
    </div>

    <!-- Charts Section -->
    <div id="chartsContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 hidden">
      <!-- Chart 1: Temperature & Humidity -->
      <div class="chart-container">
        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">อุณหภูมิและความชื้น</h3>
        <canvas id="tempHumidChart"></canvas>
      </div>
      
      <!-- Chart 2: Rain & Vibration -->
      <div class="chart-container">
        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">ฝนและการสั่นสะเทือน</h3>
        <canvas id="rainVibrationChart"></canvas>
      </div>
      
      <!-- Chart 3: Soil Moisture -->
      <div class="chart-container">
        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">ความชื้นดิน</h3>
        <canvas id="soilChart"></canvas>
      </div>
      
      <!-- Chart 4: Critical Events -->
      <div class="chart-container">
        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">เหตุการณ์วิกฤต</h3>
        <canvas id="criticalChart"></canvas>
      </div>
    </div>

    <!-- Critical Alerts -->
    <div id="criticalSection" class="mb-6 hidden">
      <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
        เหตุการณ์วิกฤต
      </h3>
      <div id="criticalAlerts" class="space-y-3">
        <!-- Critical alerts will be inserted here -->
      </div>
    </div>

    <!-- Data Table -->
    <div id="dataTableContainer" class="data-table hidden">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">ข้อมูลสภาพแวดล้อม</h3>
          <button id="exportData" class="btn btn-secondary">
            <i class="fas fa-download mr-2"></i>ส่งออก CSV
          </button>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="sticky top-0" style="background-color: var(--bg-card);">
              <tr class="border-b" style="border-color: var(--border-color);">
                <th class="text-left p-3" style="color: var(--text-primary);">วันที่/เวลา</th>
                <th class="text-left p-3" style="color: var(--text-primary);">โลเคชัน</th>
                <th class="text-left p-3" style="color: var(--text-primary);">อุปกรณ์</th>
                <th class="text-center p-3" style="color: var(--text-primary);">อุณหภูมิ</th>
                <th class="text-center p-3" style="color: var(--text-primary);">ความชื้น</th>
                <th class="text-center p-3" style="color: var(--text-primary);">ฝน</th>
                <th class="text-center p-3" style="color: var(--text-primary);">การสั่นสะเทือน</th>
                <th class="text-center p-3" style="color: var(--text-primary);">ความชื้นดิน</th>
                <th class="text-center p-3" style="color: var(--text-primary);">สถานะ</th>
              </tr>
            </thead>
            <tbody id="dataTableBody">
              <!-- Table data will be inserted here -->
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="flex justify-between items-center mt-4">
          <div class="text-sm text-muted">
            แสดง <span id="showingRange">-</span> จาก <span id="totalRecords">-</span> รายการ
          </div>
          <div id="paginationButtons" class="flex space-x-1">
            <!-- Pagination buttons will be inserted here -->
          </div>
        </div>
      </div>
    </div>

    <!-- No Data Message -->
    <div id="noDataMessage" class="hidden text-center py-12">
      <i class="fas fa-chart-line text-6xl text-gray-400 mb-4"></i>
      <h3 class="text-xl font-semibold mb-2" style="color: var(--text-primary);">ไม่พบข้อมูล</h3>
      <p class="text-muted">ไม่มีข้อมูลสำหรับช่วงเวลาและเงื่อนไขที่เลือก</p>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
class EnvironmentAnalysis {
  constructor() {
    this.currentData = [];
    this.currentStats = {};
    this.charts = {};
    this.currentPage = 1;
    this.itemsPerPage = 20;
    this.init();
  }

  init() {
    this.setupDateFilters();
    this.loadFilters();
    this.bindEvents();
    this.loadData();
  }

  setupDateFilters() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 30);
    
    document.getElementById('startDate').value = this.formatDate(startDate);
    document.getElementById('endDate').value = this.formatDate(endDate);
  }

  formatDate(date) {
    return date.toISOString().split('T')[0];
  }

  bindEvents() {
    document.getElementById('applyFilter').addEventListener('click', () => {
      this.currentPage = 1;
      this.loadData();
    });
    
    document.getElementById('exportData').addEventListener('click', () => {
      this.exportToCSV();
    });

    // Auto refresh when filters change
    document.getElementById('locationFilter').addEventListener('change', () => {
      this.loadDevicesByLocation();
      // Auto refresh when location changes
      setTimeout(() => {
        this.currentPage = 1;
        this.loadData();
      }, 100);
    });

    document.getElementById('deviceFilter').addEventListener('change', () => {
      // Auto refresh when device changes
      setTimeout(() => {
        this.currentPage = 1;
        this.loadData();
      }, 100);
    });

    // Date change auto refresh
    document.getElementById('startDate').addEventListener('change', () => {
      setTimeout(() => {
        this.currentPage = 1;
        this.loadData();
      }, 100);
    });

    document.getElementById('endDate').addEventListener('change', () => {
      setTimeout(() => {
        this.currentPage = 1;
        this.loadData();
      }, 100);
    });
    
    // Auto-refresh every 5 minutes
    setInterval(() => {
      this.loadData();
    }, 300000);
  }

  async loadFilters() {
    try {
      // Load locations
      const locationResponse = await fetch('api/location/list.php');
      const locationResult = await locationResponse.json();
      
      if (locationResult.success) {
        const select = document.getElementById('locationFilter');
        locationResult.data.forEach(location => {
          const option = document.createElement('option');
          option.value = location.location_id;
          option.textContent = location.location_name;
          select.appendChild(option);
        });
      }
      
      // Load devices based on selected location
      document.getElementById('locationFilter').addEventListener('change', (e) => {
        this.loadDevices(e.target.value);
      });
      
    } catch (error) {
      console.error('Error loading filters:', error);
    }
  }

  async loadDevices(locationId) {
    try {
      const deviceSelect = document.getElementById('deviceFilter');
      deviceSelect.innerHTML = '<option value="">ทุกอุปกรณ์</option>';
      
      if (!locationId) return;
      
      const response = await fetch(`api/device/by-location.php?location_id=${locationId}`);
      const result = await response.json();
      
      if (result.success) {
        result.data.forEach(device => {
          const option = document.createElement('option');
          option.value = device.device_id;
          option.textContent = `${device.device_name} (${device.device_id})`;
          deviceSelect.appendChild(option);
        });
      }
    } catch (error) {
      console.error('Error loading devices:', error);
    }
  }

  async loadData() {
    this.showLoading(true);
    
    try {
      const params = this.getFilterParams();
      
      // Load main data
      const dataUrl = `api/environment/analysis.php?${params}&type=analysis`;
      const dataResponse = await fetch(dataUrl);
      const dataResult = await dataResponse.json();
      
      // Load statistics separately to ensure they're filtered correctly
      const statsUrl = `api/environment/analysis.php?${params}&type=stats`;
      const statsResponse = await fetch(statsUrl);
      const statsResult = await statsResponse.json();
      
      if (dataResult.success && statsResult.success) {
        this.currentData = dataResult.data || [];
        this.currentStats = statsResult.data || {};
        
        console.log('Stats data:', this.currentStats); // Debug log
        
        this.renderStats();
        this.renderCharts();
        this.renderTable();
        this.loadCriticalData();
        
        this.showSections(true);
      } else {
        console.error('API Error:', dataResult.message || statsResult.message);
        this.showNoData(true);
      }
      
    } catch (error) {
      console.error('Error loading data:', error);
      this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
      this.showNoData(true);
    } finally {
      this.showLoading(false);
    }
  }

  getFilterParams() {
    const params = new URLSearchParams();
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    params.append('start_date', startDate);
    params.append('end_date', endDate);
    
    const locationId = document.getElementById('locationFilter').value;
    if (locationId && locationId !== '') {
      params.append('location_id', locationId);
    }
    
    const deviceId = document.getElementById('deviceFilter').value;
    if (deviceId && deviceId !== '') {
      params.append('device_id', deviceId);
    }
    
    console.log('Filter params:', {
      start_date: startDate,
      end_date: endDate,
      location_id: locationId,
      device_id: deviceId
    }); // Debug log
    
    return params.toString();
  }

  renderStats() {
    const container = document.getElementById('statsContainer');
    const stats = this.currentStats;
    
    console.log('Rendering stats:', stats); // Debug log
    
    // Handle case where stats might be undefined or empty
    const safeStats = stats || {};
    
    container.innerHTML = `
      <div class="stats-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">จำนวนข้อมูลทั้งหมด</p>
            <p class="text-2xl font-bold" style="color: var(--text-primary);">${parseInt(safeStats.total_records || 0).toLocaleString()}</p>
          </div>
          <i class="fas fa-database text-3xl text-blue-500"></i>
        </div>
      </div>
      
      <div class="stats-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">อุปกรณ์ที่ใช้งาน</p>
            <p class="text-2xl font-bold" style="color: var(--text-primary);">${parseInt(safeStats.active_devices || 0)}</p>
            <p class="text-xs text-muted">${parseInt(safeStats.locations || 0)} โลเคชัน</p>
          </div>
          <i class="fas fa-microchip text-3xl text-green-500"></i>
        </div>
      </div>
      
      <div class="stats-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">เหตุการณ์วิกฤต</p>
            <p class="text-2xl font-bold text-red-600">${parseInt(safeStats.total_landslide || 0) + parseInt(safeStats.total_flood || 0)}</p>
            <p class="text-xs text-muted">ดินถล่ม: ${parseInt(safeStats.total_landslide || 0)} | น้ำท่วม: ${parseInt(safeStats.total_flood || 0)}</p>
          </div>
          <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
        </div>
      </div>
      
      <div class="stats-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">อุณหภูมิเฉลี่ย</p>
            <p class="text-2xl font-bold" style="color: var(--text-primary);">${parseFloat(safeStats.avg_temp || 0).toFixed(1)}°C</p>
            <p class="text-xs text-muted">ช่วง: ${parseFloat(safeStats.min_temp || 0).toFixed(1)}°C - ${parseFloat(safeStats.max_temp || 0).toFixed(1)}°C</p>
          </div>
          <i class="fas fa-thermometer-half text-3xl text-orange-500"></i>
        </div>
      </div>
    `;
  }

  async renderCharts() {
    // Prepare data for charts
    const chartData = this.prepareChartData();
    
    // Temperature & Humidity Chart
    this.createChart('tempHumidChart', {
      type: 'line',
      data: {
        labels: chartData.dates,
        datasets: [{
          label: 'อุณหภูมิ (°C)',
          data: chartData.temp,
          borderColor: 'rgb(239, 68, 68)',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          yAxisID: 'y'
        }, {
          label: 'ความชื้น (%)',
          data: chartData.humid,
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          yAxisID: 'y1'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left'
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right'
          }
        }
      }
    });

    // Rain & Vibration Chart
    this.createChart('rainVibrationChart', {
      type: 'bar',
      data: {
        labels: chartData.dates,
        datasets: [{
          label: 'ปริมาณฝน (mm)',
          data: chartData.rain,
          backgroundColor: 'rgba(59, 130, 246, 0.6)',
          yAxisID: 'y'
        }, {
          label: 'การสั่นสะเทือน (Hz)',
          data: chartData.vibration,
          backgroundColor: 'rgba(239, 68, 68, 0.6)',
          yAxisID: 'y1'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left'
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right'
          }
        }
      }
    });

    // Soil Moisture Chart
    this.createChart('soilChart', {
      type: 'line',
      data: {
        labels: chartData.dates,
        datasets: [{
          label: 'ความชื้นดิน (%)',
          data: chartData.soil,
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)'
        }, {
          label: 'ความชื้นดิน (สูง) (%)',
          data: chartData.soil_high,
          borderColor: 'rgb(168, 85, 247)',
          backgroundColor: 'rgba(168, 85, 247, 0.1)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  }

  prepareChartData() {
    const grouped = {};
    
    // Group data by date
    this.currentData.forEach(item => {
      const date = item.datekey;
      if (!grouped[date]) {
        grouped[date] = {
          temp: [],
          humid: [],
          rain: [],
          vibration: [],
          soil: [],
          soil_high: []
        };
      }
      
      grouped[date].temp.push(parseFloat(item.temp || 0));
      grouped[date].humid.push(parseFloat(item.humid || 0));
      grouped[date].rain.push(parseFloat(item.rain || 0));
      grouped[date].vibration.push(parseFloat(item.vibration || 0));
      grouped[date].soil.push(parseFloat(item.soil || 0));
      grouped[date].soil_high.push(parseFloat(item.soil_high || 0));
    });
    
    // Calculate averages
    const dates = Object.keys(grouped).sort();
    const result = {
      dates: dates,
      temp: [],
      humid: [],
      rain: [],
      vibration: [],
      soil: [],
      soil_high: []
    };
    
    dates.forEach(date => {
      const data = grouped[date];
      result.temp.push(this.average(data.temp));
      result.humid.push(this.average(data.humid));
      result.rain.push(this.average(data.rain));
      result.vibration.push(this.average(data.vibration));
      result.soil.push(this.average(data.soil));
      result.soil_high.push(this.average(data.soil_high));
    });
    
    return result;
  }

  average(arr) {
    return arr.length > 0 ? arr.reduce((a, b) => a + b, 0) / arr.length : 0;
  }

  createChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (this.charts[canvasId]) {
      this.charts[canvasId].destroy();
    }
    this.charts[canvasId] = new Chart(canvas, config);
  }

  async loadCriticalData() {
    try {
      const params = this.getFilterParams();
      const url = `api/environment/analysis.php?type=critical&${params}`;
      
      const response = await fetch(url);
      const result = await response.json();
      
      if (result.success && result.data.length > 0) {
        this.renderCriticalAlerts(result.data);
        document.getElementById('criticalSection').classList.remove('hidden');
      } else {
        document.getElementById('criticalSection').classList.add('hidden');
      }
    } catch (error) {
      console.error('Error loading critical data:', error);
    }
  }

  renderCriticalAlerts(criticalData) {
    const container = document.getElementById('criticalAlerts');
    container.innerHTML = '';
    
    criticalData.slice(0, 5).forEach(item => {
      const alertClass = item.landslide == 1 && item.floot == 1 ? 'critical-alert' :
                       item.landslide == 1 ? 'critical-alert' : 'warning-alert';
      
      const alertDiv = document.createElement('div');
      alertDiv.className = `p-4 rounded-lg ${alertClass}`;
      alertDiv.innerHTML = `
        <div class="flex items-start justify-between">
          <div>
            <h4 class="font-semibold text-gray-800">${item.alert_type}</h4>
            <p class="text-sm text-gray-600 mt-1">
              <i class="fas fa-map-marker-alt mr-1"></i>
              ${item.location_name} - ${item.device_name}
            </p>
            <p class="text-sm text-gray-600">
              <i class="fas fa-clock mr-1"></i>
              ${item.datekey} ${item.timekey}
            </p>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-600">
              อุณหภูมิ: ${parseFloat(item.temp).toFixed(1)}°C<br>
              ความชื้น: ${parseFloat(item.humid).toFixed(1)}%<br>
              ฝน: ${parseFloat(item.rain).toFixed(1)}mm
            </div>
          </div>
        </div>
      `;
      container.appendChild(alertDiv);
    });
  }

  renderTable() {
    const tbody = document.getElementById('dataTableBody');
    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    const pageData = this.currentData.slice(start, end);
    
    tbody.innerHTML = '';
    
    pageData.forEach(item => {
      const row = document.createElement('tr');
      row.className = 'border-b hover:bg-opacity-50 hover:bg-gray-100';
      row.style.borderColor = 'var(--border-color)';
      
      const statusClass = item.landslide == 1 || item.floot == 1 ? 'text-red-600' : 'text-green-600';
      const statusText = item.landslide == 1 && item.floot == 1 ? 'วิกฤต' :
                        item.landslide == 1 ? 'ดินถล่ม' :
                        item.floot == 1 ? 'น้ำท่วม' : 'ปกติ';
      
      row.innerHTML = `
        <td class="p-3" style="color: var(--text-primary);">${item.datekey} ${item.timekey}</td>
        <td class="p-3" style="color: var(--text-primary);">${item.location_name || '-'}</td>
        <td class="p-3" style="color: var(--text-primary);">${item.device_name || item.device_id}</td>
        <td class="p-3 text-center" style="color: var(--text-primary);">${parseFloat(item.temp).toFixed(1)}°C</td>
        <td class="p-3 text-center" style="color: var(--text-primary);">${parseFloat(item.humid).toFixed(1)}%</td>
        <td class="p-3 text-center" style="color: var(--text-primary);">${parseFloat(item.rain).toFixed(1)}mm</td>
        <td class="p-3 text-center" style="color: var(--text-primary);">${parseFloat(item.vibration).toFixed(2)}Hz</td>
        <td class="p-3 text-center" style="color: var(--text-primary);">${parseFloat(item.soil).toFixed(1)}%</td>
        <td class="p-3 text-center ${statusClass}">${statusText}</td>
      `;
      tbody.appendChild(row);
    });
    
    this.updatePagination();
  }

  updatePagination() {
    const total = this.currentData.length;
    const totalPages = Math.ceil(total / this.itemsPerPage);
    const start = (this.currentPage - 1) * this.itemsPerPage + 1;
    const end = Math.min(this.currentPage * this.itemsPerPage, total);
    
    document.getElementById('showingRange').textContent = total > 0 ? `${start}-${end}` : '0';
    document.getElementById('totalRecords').textContent = total.toLocaleString();
    
    const container = document.getElementById('paginationButtons');
    container.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = `px-3 py-1 border rounded ${this.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}`;
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.disabled = this.currentPage === 1;
    prevBtn.onclick = () => this.changePage(this.currentPage - 1);
    container.appendChild(prevBtn);
    
    // Page numbers
    const startPage = Math.max(1, this.currentPage - 2);
    const endPage = Math.min(totalPages, this.currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 border rounded ${i === this.currentPage ? 'bg-blue-500 text-white' : 'hover:bg-gray-100'}`;
      pageBtn.textContent = i;
      pageBtn.onclick = () => this.changePage(i);
      container.appendChild(pageBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = `px-3 py-1 border rounded ${this.currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}`;
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = this.currentPage === totalPages;
    nextBtn.onclick = () => this.changePage(this.currentPage + 1);
    container.appendChild(nextBtn);
  }

  changePage(page) {
    const totalPages = Math.ceil(this.currentData.length / this.itemsPerPage);
    if (page >= 1 && page <= totalPages) {
      this.currentPage = page;
      this.renderTable();
    }
  }

  exportToCSV() {
    if (this.currentData.length === 0) {
      this.showNotification('ไม่มีข้อมูลสำหรับส่งออก', 'warning');
      return;
    }
    
    const headers = ['วันที่', 'เวลา', 'โลเคชัน', 'อุปกรณ์', 'อุณหภูมิ', 'ความชื้น', 'ฝน', 'การสั่นสะเทือน', 'ระยะทาง', 'ความชื้นดิน', 'ความชื้นดิน(สูง)', 'ดินถล่ม', 'น้ำท่วม'];
    const csvContent = [
      headers.join(','),
      ...this.currentData.map(row => [
        row.datekey, row.timekey, row.location_name || '', row.device_name || row.device_id,
        row.temp, row.humid, row.rain, row.vibration, row.distance, row.soil, row.soil_high,
        row.landslide, row.floot
      ].join(','))
    ].join('\n');
    
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `environment_analysis_${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
  }

  showLoading(show) {
    document.getElementById('loadingIndicator').classList.toggle('hidden', !show);
  }

  showSections(show) {
    document.getElementById('statsContainer').classList.toggle('hidden', !show);
    document.getElementById('chartsContainer').classList.toggle('hidden', !show);
    document.getElementById('dataTableContainer').classList.toggle('hidden', !show);
    document.getElementById('noDataMessage').classList.toggle('hidden', show);
  }

  showNoData(show) {
    document.getElementById('noDataMessage').classList.toggle('hidden', !show);
    this.showSections(!show);
  }

  showNotification(message, type = 'info') {
    if (window.notify) {
      window.notify(message, type);
    } else {
      console.log(`${type.toUpperCase()}: ${message}`);
    }
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Set active navigation
  const currentPath = window.location.pathname;
  const navLinks = document.querySelectorAll('.nav-link[data-page]');
  navLinks.forEach(link => {
    if (currentPath.includes('environment-analysis') && link.getAttribute('data-page') === 'environment-analysis') {
      link.classList.add('active');
    }
  });

  // Initialize the analysis page
  new EnvironmentAnalysis();
});
</script>

<?php include 'plugin/footer.php'; ?>
