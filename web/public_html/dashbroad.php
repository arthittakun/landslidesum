<?php
include_once 'plugin/header.php';
?>

<style>
/* Chart container styling */
.chart-container {
  background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-secondary) 100%);
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.chart-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--color-accent), var(--color-accent-light), var(--color-accent));
  opacity: 0.8;
}

.chart-container:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Chart header styling */
.chart-header {
  background: linear-gradient(135deg, var(--bg-card) 0%, var(--color-accent-light) 100%);
  border-radius: 8px;
  padding: 1rem;
  margin: -1.25rem -1.25rem 1rem -1.25rem;
  border-bottom: 1px solid var(--border-color);
}

.chart-title {
  background: linear-gradient(135deg, var(--color-accent), var(--color-accent-hover));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
  font-size: 1.25rem;
}

/* Summary cards */
.summary-card {
  background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-secondary) 100%);
  border: 1px solid var(--border-color);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.summary-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--color-accent);
  opacity: 0.8;
}

.summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Date picker styling */
.date-picker {
  background-color: var(--bg-input);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.date-picker:hover {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.date-picker:focus {
  outline: none;
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Loading animation */
.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--color-accent);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .chart-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .chart-container {
    margin: 0 -0.5rem;
  }
}

/* Dark mode adjustments */
[data-theme="dark"] .chart-container {
  background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-primary) 100%);
}

[data-theme="dark"] .chart-header {
  background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
}

[data-theme="dark"] input[type="date"] {
  color-scheme: dark;
}
</style>

<!-- Content Area -->
<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6" style="background-color: var(--bg-primary); color: var(--text-primary);">
  <div class="mb-6">
    <h1 class="text-2xl font-bold" style="color: var(--text-primary);">แดชบอร์ด</h1>
    <p class="text-muted" style="color: var(--text-muted);">ภาพรวมข้อมูลและกราฟแนวโน้มของระบบ</p>
  </div>

  <!-- Summary Cards Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Devices Card -->
    <div class="summary-card rounded-lg p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium" style="color: var(--text-primary);">อุปกรณ์ทั้งหมด</h3>
        <div class="rounded-full p-3" style="background-color: var(--color-info-light);">
          <i class="fas fa-sitemap text-xl" style="color: var(--color-info);"></i>
        </div>
      </div>
      <p id="total-devices" class="text-3xl font-bold" style="color: var(--text-primary);">-</p>
      <p class="text-sm mt-1" style="color: var(--text-secondary);">
        <span id="active-devices" style="color: var(--color-success);">-</span> ใช้งาน | 
        <span id="inactive-devices" style="color: var(--color-danger);">-</span> ไม่ใช้งาน
      </p>
    </div>
    
    <!-- Total Locations Card -->
    <div class="summary-card rounded-lg p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium" style="color: var(--text-primary);">ตำแหน่งติดตั้ง</h3>
        <div class="rounded-full p-3" style="background-color: var(--color-accent-light);">
          <i class="fas fa-map-marker-alt text-xl" style="color: var(--color-accent);"></i>
        </div>
      </div>
      <p id="total-locations" class="text-3xl font-bold" style="color: var(--text-primary);">-</p>
      <p class="text-sm mt-1" style="color: var(--text-secondary);">จุดติดตั้งทั้งหมด</p>
    </div>

    <!-- Environment Readings Card -->
    <div class="summary-card rounded-lg p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium" style="color: var(--text-primary);">ข้อมูลสิ่งแวดล้อม</h3>
        <div class="rounded-full p-3" style="background-color: var(--color-success-light);">
          <i class="fas fa-leaf text-xl" style="color: var(--color-success);"></i>
        </div>
      </div>
      <p id="total-readings" class="text-3xl font-bold" style="color: var(--text-primary);">-</p>
      <p class="text-sm mt-1" style="color: var(--text-secondary);">บันทึกทั้งหมด</p>
    </div>

    <!-- Critical Alerts Card -->
    <div class="summary-card rounded-lg p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium" style="color: var(--text-primary);">เตือนภัยวิกฤต</h3>
        <div class="rounded-full p-3" style="background-color: var(--color-warning-light);">
          <i class="fas fa-exclamation-triangle text-xl" style="color: var(--color-warning);"></i>
        </div>
      </div>
      <p id="critical-alerts" class="text-3xl font-bold" style="color: var(--text-primary);">-</p>
      <p class="text-sm mt-1" style="color: var(--text-secondary);">
        <span id="landslide-alerts" style="color: var(--color-danger);">-</span> ดินถล่ม | 
        <span id="flood-alerts" style="color: var(--color-warning);">-</span> น้ำท่วม
      </p>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <!-- Environment Trends Chart -->
    <div class="chart-container rounded-lg p-5">
      <div class="chart-header flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
        <div>
          <h3 class="chart-title">แนวโน้มสิ่งแวดล้อม</h3>
          <p class="text-sm" style="color: var(--text-secondary);">อุณหภูมิ ความชื้น และฝน (24 ชั่วโมงล่าสุด)</p>
        </div>
        <div class="flex gap-2 mt-2 md:mt-0">
          <input type="date" id="env-start-date" class="date-picker">
          <input type="date" id="env-end-date" class="date-picker">
        </div>
      </div>
      <div class="relative">
        <canvas id="environmentChart" height="300"></canvas>
        <div id="env-chart-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
          <div class="loading-spinner"></div>
        </div>
      </div>
    </div>

    <!-- Device Status Chart -->
    <div class="chart-container rounded-lg p-5">
      <div class="chart-header">
        <h3 class="chart-title">สถานะอุปกรณ์ตามตำแหน่ง</h3>
        <p class="text-sm" style="color: var(--text-secondary);">การกระจายและสถานะของอุปกรณ์</p>
      </div>
      <div class="relative">
        <canvas id="deviceStatusChart" height="300"></canvas>
        <div id="device-chart-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
          <div class="loading-spinner"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Hourly Averages Chart -->
  <div class="chart-container rounded-lg p-5 mb-8">
    <div class="chart-header">
      <h3 class="chart-title">ค่าเฉลี่ยรายชั่วโมง</h3>
      <p class="text-sm" style="color: var(--text-secondary);">ค่าเฉลี่ยของข้อมูลสิ่งแวดล้อมในแต่ละชั่วโมง</p>
    </div>
    <div class="relative">
      <canvas id="hourlyAveragesChart" height="200"></canvas>
      <div id="hourly-chart-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
        <div class="loading-spinner"></div>
      </div>
    </div>
  </div>

  

</main>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// API endpoints
const API = {
  DASHBOARD_SUMMARY: 'api/reports/dashboard-summary.php',
  ENVIRONMENT_DATA: 'api/reports/environment-data.php'
};

// Chart instances
let environmentChart = null;
let deviceStatusChart = null;
let hourlyAveragesChart = null;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.themeObserver === 'function') {
    window.themeObserver();
  }
  
  // Set default dates
  const endDate = new Date();
  const startDate = new Date();
  startDate.setDate(endDate.getDate() - 7);
  
  document.getElementById('env-start-date').value = startDate.toISOString().split('T')[0];
  document.getElementById('env-end-date').value = endDate.toISOString().split('T')[0];
  
  // Load dashboard data
  loadDashboardData();
  
  // Set up event listeners
  document.getElementById('env-start-date').addEventListener('change', updateEnvironmentChart);
  document.getElementById('env-end-date').addEventListener('change', updateEnvironmentChart);
});

async function loadDashboardData() {
  try {
    // Load dashboard summary data
    const response = await fetch(API.DASHBOARD_SUMMARY);
    const result = await response.json();
    
    if (result.success) {
      updateSummaryCards(result.data);
      loadDeviceStatusChart(result.data);
      loadHourlyAveragesChart(result.data);
    } else {
      throw new Error(result.message || 'Failed to load dashboard data');
    }
    
    // Load environment chart
    await loadEnvironmentChart();

  } catch (error) {
    console.error('Error loading dashboard data:', error);
    if (typeof notify === 'function') notify('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
  }
}

function updateSummaryCards(data) {
  // Device stats
  document.getElementById('total-devices').textContent = data.device_counts.total || 0;
  document.getElementById('active-devices').textContent = data.device_counts.active || 0;
  document.getElementById('inactive-devices').textContent = data.device_counts.inactive || 0;
  
  // Location stats
  document.getElementById('total-locations').textContent = data.location_counts.total || 0;
  
  // Environment stats
  document.getElementById('total-readings').textContent = data.environment_counts.total_readings || 0;
  document.getElementById('critical-alerts').textContent = data.alert_counts.total_alerts || 0;
  document.getElementById('landslide-alerts').textContent = data.alert_counts.landslide_alerts || 0;
  document.getElementById('flood-alerts').textContent = data.alert_counts.flood_alerts || 0;
}

async function loadEnvironmentChart() {
  const startDate = document.getElementById('env-start-date').value;
  const endDate = document.getElementById('env-end-date').value;
  
  if (!startDate || !endDate) return;
  
  try {
    document.getElementById('env-chart-loading').classList.remove('hidden');
    
    const response = await fetch(`${API.ENVIRONMENT_DATA}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.daily_readings) {
      renderEnvironmentChart(result.data.daily_readings);
    } else {
      throw new Error(result.message || 'Failed to load environment data');
    }
  } catch (error) {
    console.error('Error loading environment chart:', error);
  } finally {
    document.getElementById('env-chart-loading').classList.add('hidden');
  }
}

function renderEnvironmentChart(data) {
  const ctx = document.getElementById('environmentChart').getContext('2d');
  
  if (environmentChart) {
    environmentChart.destroy();
  }
  
  const labels = data.map(item => item.date);
  
  environmentChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'อุณหภูมิ (°C)',
          data: data.map(item => item.avg_temp),
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4
        },
        {
          label: 'ความชื้น (%)',
          data: data.map(item => item.avg_humid),
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4
        },
        {
          label: 'ฝน (mm)',
          data: data.map(item => item.total_rain),
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

function loadDeviceStatusChart(data) {
  const ctx = document.getElementById('deviceStatusChart').getContext('2d');
  
  if (deviceStatusChart) {
    deviceStatusChart.destroy();
  }
  
  if (!data || !data.device_counts) return;
  
  const deviceCounts = data.device_counts;
  
  deviceStatusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['ใช้งาน', 'ไม่ใช้งาน'],
      datasets: [{
        data: [deviceCounts.active, deviceCounts.inactive],
        backgroundColor: ['#10b981', '#ef4444'],
        borderWidth: 2,
        borderColor: '#ffffff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
}

function loadHourlyAveragesChart(data) {
  const ctx = document.getElementById('hourlyAveragesChart').getContext('2d');
  
  if (hourlyAveragesChart) {
    hourlyAveragesChart.destroy();
  }
  
  if (!data || !data.hourly_averages) return;
  
  const hourlyData = data.hourly_averages;
  
  hourlyAveragesChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: hourlyData.map(item => `${item.hour}:00`),
      datasets: [
        {
          label: 'อุณหภูมิเฉลี่ย (°C)',
          data: hourlyData.map(item => item.avg_temp),
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: '#ef4444',
          borderWidth: 1
        },
        {
          label: 'ความชื้นเฉลี่ย (%)',
          data: hourlyData.map(item => item.avg_humid),
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: '#3b82f6',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

function updateEnvironmentChart() {
  loadEnvironmentChart();
}
</script>

<?php
include_once 'plugin/footer.php';
?>
