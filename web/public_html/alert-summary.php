<?php include 'plugin/header.php'; ?>

<style>
  .alert-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px var(--shadow-color);
    transition: transform 0.2s ease;
  }
  
  .alert-card:hover {
    transform: translateY(-2px);
  }
  
  .filter-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    box-shadow: 0 2px 4px var(--shadow-color);
  }

  .alert-table {
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

  .landslide-alert {
    background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
    border-left: 4px solid #8b5cf6;
  }

  .flood-alert {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-left: 4px solid #3b82f6;
  }

  .chart-container {
    position: relative;
    height: 350px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
  }

  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
  }

  .pagination button {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .pagination button:hover:not(:disabled) {
    background: var(--color-accent);
    color: white;
  }

  .pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
</style>

<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <div class="mb-6">
    <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary);">สรุปการแจ้งเตือน</h1>
    <p class="text-muted">ดูสรุปการแจ้งเตือนเหตุการณ์วิกฤต ดินถล่ม และน้ำท่วม</p>
  </div>

  <!-- Filter Section -->
  <div class="filter-card rounded-lg p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">ตัวกรองข้อมูล</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">วันที่เริ่มต้น</label>
        <input type="date" id="startDate" class="w-full p-2 rounded-md" 
               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">วันที่สิ้นสุด</label>
        <input type="date" id="endDate" class="w-full p-2 rounded-md" 
               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">โลเคชัน</label>
        <select id="locationFilter" class="w-full p-2 rounded-md" 
                style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
          <option value="">ทั้งหมด</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">อุปกรณ์</label>
        <select id="deviceFilter" class="w-full p-2 rounded-md" 
                style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
          <option value="">ทั้งหมด</option>
        </select>
      </div>
    </div>
    <div class="mt-4 flex gap-2">
      <button id="applyFilter" class="px-4 py-2 text-white rounded-md transition-colors" 
              style="background: var(--color-accent);">
        <i class="fas fa-search mr-2"></i>ค้นหา
      </button>
      <button id="exportData" class="px-4 py-2 text-white rounded-md transition-colors" 
              style="background: var(--color-info);">
        <i class="fas fa-download mr-2"></i>ส่งออก CSV
      </button>
    </div>
  </div>

  <!-- Loading -->
  <div id="loadingSection" class="text-center py-8 hidden">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
    <p style="color: var(--text-muted);">กำลังโหลดข้อมูล...</p>
  </div>

  <!-- Stats Cards -->
  <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Stats will be rendered here -->
  </div>

  <!-- Charts Section -->
  <div id="chartsSection" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 hidden">
    <div class="chart-container">
      <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">การแจ้งเตือนตามประเภท</h3>
      <canvas id="alertTypeChart"></canvas>
    </div>
    <div class="chart-container">
      <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">การแจ้งเตือนรายวัน</h3>
      <canvas id="dailyAlertsChart"></canvas>
    </div>
  </div>

  <!-- Alerts Table -->
  <div id="alertsSection" class="alert-table rounded-lg hidden">
    <div class="p-6 border-b" style="border-color: var(--border-color);">
      <h3 class="text-lg font-semibold" style="color: var(--text-primary);">รายการแจ้งเตือน</h3>
      <p class="text-sm text-muted">รายละเอียดการแจ้งเตือนทั้งหมด</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead style="background: var(--bg-secondary);">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">วันที่/เวลา</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">ประเภท</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">อุปกรณ์</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">โลเคชัน</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">ข้อมูลเซนเซอร์</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">สถานะ</th>
          </tr>
        </thead>
        <tbody id="alertsTableBody">
          <!-- Table data will be rendered here -->
        </tbody>
      </table>
    </div>
    <div id="paginationContainer" class="pagination p-4">
      <!-- Pagination will be rendered here -->
    </div>
  </div>

  <!-- No Data -->
  <div id="noDataSection" class="text-center py-12 hidden">
    <i class="fas fa-exclamation-circle text-6xl text-gray-400 mb-4"></i>
    <h3 class="text-lg font-medium mb-2" style="color: var(--text-secondary);">ไม่พบข้อมูลการแจ้งเตือน</h3>
    <p style="color: var(--text-muted);">ไม่มีเหตุการณ์แจ้งเตือนในช่วงเวลาที่เลือก</p>
  </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
class AlertSummary {
  constructor() {
    this.currentData = [];
    this.currentStats = {};
    this.currentPage = 1;
    this.itemsPerPage = 10;
    this.charts = {};
    
    this.init();
  }

  init() {
    this.setupDateFilters();
    this.bindEvents();
    this.loadFilters();
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
      setTimeout(() => {
        this.currentPage = 1;
        this.loadData();
      }, 100);
    });

    document.getElementById('deviceFilter').addEventListener('change', () => {
      setTimeout(() => {
        this.currentPage = 1;
        this.loadData();
      }, 100);
    });

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
        const locationSelect = document.getElementById('locationFilter');
        locationResult.data.forEach(location => {
          const option = document.createElement('option');
          option.value = location.location_id;
          option.textContent = location.location_name;
          locationSelect.appendChild(option);
        });
      }
    } catch (error) {
      console.error('Error loading filters:', error);
    }
  }

  async loadDevicesByLocation() {
    try {
      const locationId = document.getElementById('locationFilter').value;
      const deviceSelect = document.getElementById('deviceFilter');
      
      // Clear current devices
      deviceSelect.innerHTML = '<option value="">ทั้งหมด</option>';
      
      if (locationId) {
        const response = await fetch(`api/device/by-location.php?location_id=${locationId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
          result.data.forEach(device => {
            const option = document.createElement('option');
            option.value = device.device_id;
            option.textContent = device.device_name;
            deviceSelect.appendChild(option);
          });
        }
      }
    } catch (error) {
      console.error('Error loading devices:', error);
    }
  }

  async loadData() {
    this.showLoading(true);
    
    try {
      const params = this.getFilterParams();
      
      // Load alert data
      const dataUrl = `api/alert/summary.php?${params}&type=summary`;
      const dataResponse = await fetch(dataUrl);
      const dataResult = await dataResponse.json();
      
      // Load statistics separately
      const statsUrl = `api/alert/summary.php?${params}&type=stats`;
      const statsResponse = await fetch(statsUrl);
      const statsResult = await statsResponse.json();
      
      if (dataResult.success && statsResult.success) {
        this.currentData = dataResult.data || [];
        this.currentStats = statsResult.data || {};
        
        console.log('Alert data:', this.currentData);
        console.log('Alert stats:', this.currentStats);
        console.log('Data filters:', dataResult.filters);
        console.log('Stats filters:', statsResult.filters);
        console.log('Data debug info:', dataResult.debug);
        console.log('Stats debug info:', statsResult.debug);
        
        if (this.currentData.length === 0) {
          console.log('No alert data found for current filters');
          this.showNoData(true);
        } else {
          this.renderStats();
          this.renderCharts();
          this.renderTable();
          this.showSections(true);
        }
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
    });
    
    return params.toString();
  }

  renderStats() {
    const container = document.getElementById('statsContainer');
    const stats = this.currentStats;
    const safeStats = stats || {};
    
    container.innerHTML = `
      <div class="alert-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">การแจ้งเตือนทั้งหมด</p>
            <p class="text-2xl font-bold text-red-600">${parseInt(safeStats.total_alerts || 0).toLocaleString()}</p>
          </div>
          <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
        </div>
      </div>
      
      <div class="alert-card landslide-alert">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">เหตุการณ์ดินถล่ม</p>
            <p class="text-2xl font-bold text-purple-600">${parseInt(safeStats.total_landslide || 0)}</p>
          </div>
          <i class="fas fa-mountain text-3xl text-purple-500"></i>
        </div>
      </div>
      
      <div class="alert-card flood-alert">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">เหตุการณ์น้ำท่วม</p>
            <p class="text-2xl font-bold text-blue-600">${parseInt(safeStats.total_flood || 0)}</p>
          </div>
          <i class="fas fa-water text-3xl text-blue-500"></i>
        </div>
      </div>
      
      <div class="alert-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted">พื้นที่ที่ได้รับผลกระทบ</p>
            <p class="text-2xl font-bold" style="color: var(--text-primary);">${parseInt(safeStats.affected_locations || 0)}</p>
            <p class="text-xs text-muted">${parseInt(safeStats.affected_devices || 0)} อุปกรณ์</p>
          </div>
          <i class="fas fa-map-marked-alt text-3xl text-green-500"></i>
        </div>
      </div>
    `;
  }

  renderCharts() {
    if (this.currentData.length === 0) return;
    
    this.renderAlertTypeChart();
    this.renderDailyAlertsChart();
  }

  renderAlertTypeChart() {
    const stats = this.currentStats;
    const landslideCount = parseInt(stats.total_landslide || 0);
    const floodCount = parseInt(stats.total_flood || 0);
    const combinedCount = parseInt(stats.combined_alerts || 0);
    
    const ctx = document.getElementById('alertTypeChart').getContext('2d');
    
    if (this.charts.alertType) {
      this.charts.alertType.destroy();
    }
    
    this.charts.alertType = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['ดินถล่ม', 'น้ำท่วม', 'ทั้งสองอย่าง'],
        datasets: [{
          data: [landslideCount, floodCount, combinedCount],
          backgroundColor: ['#8b5cf6', '#3b82f6', '#ef4444'],
          borderColor: ['#7c3aed', '#2563eb', '#dc2626'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim()
            }
          }
        }
      }
    });
  }

  renderDailyAlertsChart() {
    // Group alerts by date
    const dailyData = {};
    this.currentData.forEach(alert => {
      const date = alert.datekey;
      if (!dailyData[date]) {
        dailyData[date] = { landslide: 0, flood: 0, total: 0 };
      }
      if (alert.landslide == 1) dailyData[date].landslide++;
      if (alert.floot == 1) dailyData[date].flood++;
      dailyData[date].total++;
    });
    
    const dates = Object.keys(dailyData).sort();
    const landslideData = dates.map(date => dailyData[date].landslide);
    const floodData = dates.map(date => dailyData[date].flood);
    
    const ctx = document.getElementById('dailyAlertsChart').getContext('2d');
    
    if (this.charts.dailyAlerts) {
      this.charts.dailyAlerts.destroy();
    }
    
    this.charts.dailyAlerts = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: dates.map(date => new Date(date).toLocaleDateString('th-TH')),
        datasets: [
          {
            label: 'ดินถล่ม',
            data: landslideData,
            backgroundColor: '#8b5cf6',
            borderColor: '#7c3aed',
            borderWidth: 1
          },
          {
            label: 'น้ำท่วม',
            data: floodData,
            backgroundColor: '#3b82f6',
            borderColor: '#2563eb',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim()
            }
          },
          x: {
            ticks: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim()
            }
          }
        },
        plugins: {
          legend: {
            labels: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim()
            }
          }
        }
      }
    });
  }

  renderTable() {
    const tbody = document.getElementById('alertsTableBody');
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    const pageData = this.currentData.slice(startIndex, endIndex);
    
    tbody.innerHTML = '';
    
    pageData.forEach(alert => {
      const row = document.createElement('tr');
      row.className = 'border-b hover:bg-gray-50 transition-colors';
      row.style.borderColor = 'var(--border-color)';
      
      const alertTypeClass = alert.landslide == 1 && alert.floot == 1 ? 'critical-alert' :
                             alert.landslide == 1 ? 'landslide-alert' : 'flood-alert';
      
      row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm font-medium" style="color: var(--text-primary);">${alert.datekey}</div>
          <div class="text-sm text-muted">${alert.timekey}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full ${alertTypeClass}">
            ${alert.alert_type}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm font-medium" style="color: var(--text-primary);">${alert.device_name || 'N/A'}</div>
          <div class="text-sm text-muted">${alert.device_type || ''}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm font-medium" style="color: var(--text-primary);">${alert.location_name || 'N/A'}</div>
          <div class="text-sm text-muted">${alert.district || ''}, ${alert.province || ''}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted">
          <div>อุณหภูมิ: ${parseFloat(alert.temp || 0).toFixed(1)}°C</div>
          <div>ความชื้น: ${parseFloat(alert.humid || 0).toFixed(1)}%</div>
          <div>ฝน: ${parseFloat(alert.rain || 0).toFixed(2)}mm</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
            ${alert.severity_level}
          </span>
        </td>
      `;
      
      tbody.appendChild(row);
    });
    
    this.renderPagination();
  }

  renderPagination() {
    const container = document.getElementById('paginationContainer');
    const totalPages = Math.ceil(this.currentData.length / this.itemsPerPage);
    
    if (totalPages <= 1) {
      container.innerHTML = '';
      return;
    }
    
    let paginationHTML = `
      <button ${this.currentPage === 1 ? 'disabled' : ''} onclick="alertSummary.goToPage(${this.currentPage - 1})">
        <i class="fas fa-chevron-left"></i>
      </button>
      <span style="color: var(--text-primary);">หน้า ${this.currentPage} จาก ${totalPages}</span>
      <button ${this.currentPage === totalPages ? 'disabled' : ''} onclick="alertSummary.goToPage(${this.currentPage + 1})">
        <i class="fas fa-chevron-right"></i>
      </button>
    `;
    
    container.innerHTML = paginationHTML;
  }

  goToPage(page) {
    this.currentPage = page;
    this.renderTable();
  }

  async exportToCSV() {
    try {
      const params = this.getFilterParams();
      const url = `api/alert/summary.php?${params}&type=summary`;
      
      const response = await fetch(url);
      const result = await response.json();
      
      if (result.success && result.data) {
        const csvContent = this.convertToCSV(result.data);
        this.downloadCSV(csvContent, 'alert-summary.csv');
        this.showNotification('ส่งออกข้อมูลสำเร็จ', 'success');
      }
    } catch (error) {
      console.error('Export error:', error);
      this.showNotification('เกิดข้อผิดพลาดในการส่งออกข้อมูล', 'error');
    }
  }

  convertToCSV(data) {
    const headers = ['วันที่', 'เวลา', 'ประเภทการแจ้งเตือน', 'อุปกรณ์', 'โลเคชัน', 'อุณหภูมิ', 'ความชื้น', 'ฝน', 'การสั่นสะเทือน'];
    
    const rows = data.map(alert => [
      alert.datekey,
      alert.timekey,
      alert.alert_type,
      alert.device_name || 'N/A',
      alert.location_name || 'N/A',
      parseFloat(alert.temp || 0).toFixed(1),
      parseFloat(alert.humid || 0).toFixed(1),
      parseFloat(alert.rain || 0).toFixed(2),
      parseFloat(alert.vibration || 0).toFixed(3)
    ]);
    
    const csvContent = [headers, ...rows]
      .map(row => row.map(field => `"${field}"`).join(','))
      .join('\n');
    
    return '\uFEFF' + csvContent; // Add BOM for Excel
  }

  downloadCSV(content, filename) {
    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
  }

  showLoading(show) {
    document.getElementById('loadingSection').classList.toggle('hidden', !show);
  }

  showSections(show) {
    document.getElementById('statsContainer').classList.toggle('hidden', !show);
    document.getElementById('chartsSection').classList.toggle('hidden', !show);
    document.getElementById('alertsSection').classList.toggle('hidden', !show);
    document.getElementById('noDataSection').classList.add('hidden');
  }

  showNoData(show) {
    document.getElementById('noDataSection').classList.toggle('hidden', !show);
    document.getElementById('statsContainer').classList.add('hidden');
    document.getElementById('chartsSection').classList.add('hidden');
    document.getElementById('alertsSection').classList.add('hidden');
  }

  showNotification(message, type) {
    if (window.notify) {
      window.notify(message, type);
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  window.alertSummary = new AlertSummary();
});
</script>

<?php include 'plugin/footer.php'; ?>
