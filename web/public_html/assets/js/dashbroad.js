let dashboardData = null;

// Separate function to fetch device count
async function fetchDeviceCount() {
  try {
    const response = await fetch("/api/device/count");
    const data = await response.json();

    if (data.status === "success") {
      return data.data;
    } else {
      console.error("Device Count API Error:", data);
      return null;
    }
  } catch (error) {
    console.error("Fetch device count error:", error);
    return null;
  }
}

// Separate function to fetch critical environment count
async function fetchCriticalEnvironmentCount() {
  try {
    const response = await fetch("/api/environment/count-critical");
    const data = await response.json();

    if (data.status === "success") {
      return data.data;
    } else {
      console.error("API Error:", data);
      return null;
    }
  } catch (error) {
    console.error("Fetch critical count error:", error);
    return null;
  }
}

// Load dashboard data
async function loadDashboardData() {
  try {
    const [deviceCount, criticalCount] = await Promise.all([
      fetchDeviceCount(),
      fetchCriticalEnvironmentCount(),
    ]);

    // Update summary cards
    updateSummaryCards(deviceCount, criticalCount);
  } catch (error) {
    console.error("Error loading dashboard data:", error);
    showErrorMessage("เกิดข้อผิดพลาดในการโหลดข้อมูล");
  }
}

// Update summary cards
function updateSummaryCards(deviceData, criticalData) {
  // Log data for debugging
  console.log("Device Data:", deviceData);
  console.log("Critical Data:", criticalData);

  // Device counts from dedicated API
  if (deviceData) {
    document.getElementById("total-devices").textContent =
      deviceData.total_devices || 0;
    document.getElementById("active-devices").textContent =
      deviceData.active_devices || 0;
    document.getElementById("deleted-devices").textContent =
      deviceData.deleted_devices || 0;
  } else {
    // Fallback values if API fails
    document.getElementById("total-devices").textContent = 0;
    document.getElementById("active-devices").textContent = 0;
    document.getElementById("deleted-devices").textContent = 0;
  }

  // Critical alerts from dedicated API
  if (criticalData) {
    document.getElementById("landslide-alerts").textContent =
      criticalData.landslide || 0;
    document.getElementById("flood-alerts").textContent =
      criticalData.flood || 0;
    document.getElementById("landslide-critical").textContent =
      criticalData.landslide || 0;
    document.getElementById("flood-critical").textContent =
      criticalData.flood || 0;
    document.getElementById("total-readings").textContent =
      criticalData.total_critical || 0;
  } else {
    // Fallback values if API fails
    document.getElementById("landslide-alerts").textContent = 0;
    document.getElementById("flood-alerts").textContent = 0;
    document.getElementById("landslide-critical").textContent = 0;
    document.getElementById("flood-critical").textContent = 0;
    document.getElementById("total-readings").textContent = 0;
  }
}

function showErrorMessage(message) {
  document.getElementById("loading-indicator").innerHTML = `
              <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
              <p class="text-red-500 mt-2">${message}</p>
              <button onclick="initializeDashboard()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                ลองใหม่
              </button>
            `;
}

// Initialize dashboard
async function initializeDashboard() {
  try {
    // Show loading
    document.getElementById("loading-indicator").style.display = "block";
    document.getElementById("dashboard-content").style.display = "none";

    // Load dashboard data
    await loadDashboardData();

    // Hide loading and show content
    document.getElementById("loading-indicator").style.display = "none";
    document.getElementById("dashboard-content").style.display = "block";
  } catch (error) {
    console.error("Dashboard initialization error:", error);
    showErrorMessage("เกิดข้อผิดพลาดในการเริ่มต้นแดชบอร์ด");
  }
}

// Fetch environmental statistics based on the selected date range
async function fetchEnvironmentalStatistics(days) {
  try {
    const response = await fetch(
      `/api/environment/data-by-time-range?days=${days}`
    );
    const data = await response.json();

    if (data.status === "success") {
      updateEnvironmentalStatistics(data.data);
    } else {
      console.error("Environmental Statistics API Error:", data);
    }
  } catch (error) {
    console.error("Fetch environmental statistics error:", error);
  }
}

// Update environmental statistics cards
function updateEnvironmentalStatistics(stats) {
  document.getElementById("avg-temperature").textContent = `${
    stats.avg_temp?.toFixed(1) || 0
  }°C`;
  document.getElementById("temp-range").textContent = `${
    stats.min_temp || 0
  }°C ถึง ${stats.max_temp || 0}°C`;

  document.getElementById("avg-humidity").textContent = `${
    stats.avg_humid?.toFixed(1) || 0
  }%`;
  document.getElementById("humidity-range").textContent = `${
    stats.min_humid || 0
  }% ถึง ${stats.max_humid || 0}%`;

  document.getElementById("avg-rainfall").textContent = `${
    stats.avg_rain?.toFixed(1) || 0
  } mm`;
  document.getElementById("rainfall-max").textContent = `สูงสุด ${
    stats.max_rain || 0
  } mm`;

  document.getElementById("avg-vibration").textContent =
    stats.avg_vibration?.toFixed(2) || 0;
  document.getElementById("vibration-max").textContent = `สูงสุด ${
    stats.max_vibration || 0
  }`;

  document.getElementById("avg-soil").textContent =
    stats.avg_soil?.toFixed(0) || 0;
  document.getElementById("soil-max").textContent = `สูงสุด ${
    stats.max_soil || 0
  }`;
}

// Event listener for date range selector
document
  .getElementById("date-range-selector")
  .addEventListener("change", (event) => {
    const days = event.target.value;
    fetchEnvironmentalStatistics(days);
  });

// Event listeners
document.addEventListener("DOMContentLoaded", function () {
  initializeDashboard();
  fetchEnvironmentalStatistics(7); // Default to 7 days
});

// Global functions for onclick handlers
window.initializeDashboard = initializeDashboard;

// Fetch hourly averages data
async function fetchHourlyAverages(deviceId = "") {
  try {
    const response = await fetch("/api/environment/hourly-averages", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ device_id: deviceId }),
    });
    const data = await response.json();
    console.log("Hourly Averages Data:", data);
    if (data.status === "success") {
      updateHourlyTrendsChart(data.data || []);
    } else {
      console.error("Hourly Averages API Error:", data.message);
      updateHourlyTrendsChart([]);
    }
  } catch (error) {
    console.error("Fetch hourly averages error:", error);
    updateHourlyTrendsChart([]);
  }
}

// Update hourly trends chart
function updateHourlyTrendsChart(data) {
  let labels, temperatures, humidities, rainfalls, vibrations, soilMoistures;

  if (data.length === 0) {
    labels = ["00:00", "06:00", "12:00", "18:00"];
    temperatures = [0, 0, 0, 0];
    humidities = [0, 0, 0, 0];
    rainfalls = [0, 0, 0, 0];
    vibrations = [0, 0, 0, 0];
    soilMoistures = [0, 0, 0, 0];
  } else {
    labels = data.map((item) => `${String(item.hour).padStart(2, "0")}:00`);
    temperatures = data.map((item) => parseFloat(item.avg_temp) || 0);
    // Fix: use avg_humid, not avg_temp, for humidity
    humidities = data.map((item) => parseFloat(item.avg_humid) || 0);
    rainfalls = data.map((item) => parseFloat(item.avg_rain) || 0);
    vibrations = data.map((item) => parseFloat(item.avg_vibration) || 0);
    soilMoistures = data.map((item) => parseFloat(item.avg_soil) || 0);
  }

  const ctx = document.getElementById("hourlyTrendsChart");
  if (!ctx) return;

  const context = ctx.getContext("2d");

  if (
    window.hourlyTrendsChart &&
    typeof window.hourlyTrendsChart.destroy === "function"
  ) {
    window.hourlyTrendsChart.destroy();
  }

  window.hourlyTrendsChart = new Chart(context, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "อุณหภูมิ (°C)",
          data: temperatures,
          borderColor: "rgba(255, 99, 132, 1)",
          backgroundColor: "rgba(255, 99, 132, 0.2)",
          fill: false,
          tension: 0.1,
        },
        {
          label: "ความชื้น (%)",
          data: humidities,
          borderColor: "rgba(54, 162, 235, 1)",
          backgroundColor: "rgba(54, 162, 235, 0.2)",
          fill: false,
          tension: 0.1,
        },
        {
          label: "ปริมาณฝน (mm)",
          data: rainfalls,
          borderColor: "rgba(75, 192, 192, 1)",
          backgroundColor: "rgba(75, 192, 192, 0.2)",
          fill: false,
          tension: 0.1,
        },
        {
          label: "การสั่นสะเทือน",
          data: vibrations,
          borderColor: "rgba(255, 206, 86, 1)",
          backgroundColor: "rgba(255, 206, 86, 0.2)",
          fill: false,
          tension: 0.1,
        },
        {
          label: "ความชื้นดิน",
          data: soilMoistures,
          borderColor: "rgba(153, 102, 255, 1)",
          backgroundColor: "rgba(153, 102, 255, 0.2)",
          fill: false,
          tension: 0.1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
        },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: "ชั่วโมง",
          },
        },
        y: {
          title: {
            display: true,
            text: "ค่าเฉลี่ย",
          },
          beginAtZero: true,
        },
      },
    },
  });
}

// Fetch locations for the device selector
async function populateDeviceSelector() {
  try {
    const response = await fetch("/api/device/get");
    const data = await response.json();

    if (data.status === "success" && Array.isArray(data.data)) {
      const selector = document.getElementById("trend-device-selector");
      if (selector) {
        selector.innerHTML = '<option value="">ทุกอุปกรณ์</option>';
        data.data.forEach((device) => {
          const option = document.createElement("option");
          option.value = device.device_id || "";
          option.textContent =
            device.device_name || device.device_id || "ไม่ระบุชื่อ";
          selector.appendChild(option);
        });
      }
    } else {
      console.error(
        "Location API Error:",
        data.message || "Invalid response format"
      );
    }
  } catch (error) {
    console.error("Fetch locations error:", error);
  }
}

// Initialize hourly trends chart
document.addEventListener("DOMContentLoaded", function () {
  populateDeviceSelector();
  fetchHourlyAverages("");
});

// Function called by onchange attribute
function updateHourlyTrends() {
  const deviceId = document.getElementById("trend-device-selector").value;
  fetchHourlyAverages(deviceId);
}
async function updateRiskChart() {
  const riskType = document.getElementById("risk-type-selector").value;

  try {
    const response = await fetch(
      `/api/environment/critical-analyst?type=${riskType}`
    );
    const data = await response.json();

    if (data.status === "success") {
      renderRiskChart(data.data, riskType);
    } else {
      console.error("Risk Chart API Error:", data.message);
      renderRiskChart([], riskType);
    }
  } catch (error) {
    console.error("Fetch risk chart data error:", error);
    renderRiskChart([], riskType);
  }
}

// Render the risk chart
function renderRiskChart(data, riskType) {
  const ctx = document.getElementById("riskChart");
  if (!ctx) return;

  const labels = [];
  const values = [];

  if (riskType === "landslide" || riskType === "flood") {
    data.forEach((item) => {
      labels.push(item.device_id);
      values.push(
        parseInt(
          riskType === "landslide" ? item.total_landslides : item.total_floods,
          10
        )
      );
    });
  } else if (riskType === "overall") {
    const landslideData = data.landslide || [];
    const floodData = data.flood || [];
    const totals = {};

    // Combine landslide and flood data
    landslideData.forEach((item) => {
      const deviceId = item.device_id;
      totals[deviceId] = {
        landslides: parseInt(item.total_landslides || 0, 10),
        floods: 0,
      };
    });
    floodData.forEach((item) => {
      const deviceId = item.device_id;
      if (!totals[deviceId]) {
        totals[deviceId] = {
          landslides: 0,
          floods: parseInt(item.total_floods || 0, 10),
        };
      } else {
        totals[deviceId].floods = parseInt(item.total_floods || 0, 10);
      }
    });

    // Prepare labels and values
    for (const deviceId in totals) {
      labels.push(deviceId);
      values.push(totals[deviceId].landslides + totals[deviceId].floods);
    }
  }

  if (
    window.riskChartInstance &&
    typeof window.riskChartInstance.destroy === "function"
  ) {
    window.riskChartInstance.destroy();
  }

  window.riskChartInstance = new Chart(ctx.getContext("2d"), {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label:
            riskType === "landslide"
              ? "ดินถล่ม"
              : riskType === "flood"
              ? "น้ำท่วม"
              : "รวม",
          data: values,
          backgroundColor: "rgba(75, 192, 192, 0.2)",
          borderColor: "rgba(75, 192, 192, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          title: {
            display: true,
            text: "อุปกรณ์",
          },
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "จำนวน",
          },
        },
      },
      plugins: {
        legend: {
          display: true,
          position: "top",
        },
      },
    },
  });
}



// Initialize the risk chart
document.addEventListener("DOMContentLoaded", function () {
  updateRiskChart(); // Default to 'landslide'
});


 // Fetch devices by location and update the UI
    async function fetchDevicesByLocation() {
      try {
          const response = await fetch('/api/device/devices-by-location');
          const data = await response.json();

          if (data.status === 'success') {
              renderDevicesByLocation(data.data);
          } else {
              console.error('Devices by Location API Error:', data.message);
              renderDevicesByLocation([]);
          }
      } catch (error) {
          console.error('Fetch devices by location error:', error);
          renderDevicesByLocation([]);
      }
    }

    // Render devices by location
    function renderDevicesByLocation(locations) {
      const container = document.getElementById('devices-by-location');
      container.innerHTML = '';

      if (locations.length === 0) {
          container.innerHTML = '<p class="text-center text-muted py-8" style="color: var(--text-muted);">ไม่มีข้อมูลอุปกรณ์ในพื้นที่</p>';
          return;
      }

      locations.forEach((location, index) => {
          const locationElement = document.createElement('div');
          locationElement.classList.add('location-item', 'flex', 'items-center', 'justify-between', 'p-4', 'rounded-lg', 'transition-all', 'duration-200', 'hover:shadow-md', 'border');
          locationElement.style.cssText = `
              background-color: var(--bg-card);
              border-color: var(--border-color);
              animation: fadeInUp 0.3s ease-out ${index * 0.1}s both;
          `;
          
          // Create device count badge with conditional styling
          const deviceCount = parseInt(location.total_devices);
          let badgeClass = 'bg-gray-100 text-gray-600';
          let badgeStyle = 'background-color: var(--color-info-light); color: var(--color-info);';
          
          if (deviceCount === 0) {
              badgeStyle = 'background-color: var(--color-warning-light); color: var(--color-warning);';
          } else if (deviceCount >= 3) {
              badgeStyle = 'background-color: var(--color-success-light); color: var(--color-success);';
          }

          locationElement.innerHTML = `
              <div class="flex items-center flex-1">
                  <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3" style="background-color: var(--color-accent-light);">
                      <i class="fas fa-map-marker-alt" style="color: var(--color-accent); font-size: 1rem;"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                      <h4 class="font-semibold text-sm truncate" style="color: var(--text-primary);">${location.location_name || 'ไม่ระบุชื่อพื้นที่'}</h4>
                      <p class="text-xs mt-1" style="color: var(--text-muted);">รหัส: ${location.location_id}</p>
                  </div>
              </div>
              <div class="flex-shrink-0 ml-3">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="${badgeStyle}">
                      <i class="fas fa-microchip mr-1" style="font-size: 0.75rem;"></i>
                      ${location.total_devices} อุปกรณ์
                  </span>
              </div>
          `;
          
          // Add hover effects
          locationElement.addEventListener('mouseenter', function() {
              this.style.transform = 'translateY(-2px)';
              this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
          });
          
          locationElement.addEventListener('mouseleave', function() {
              this.style.transform = 'translateY(0)';
              this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
          });
          
          container.appendChild(locationElement);
      });
    }

    // Initialize devices by location section
    document.addEventListener('DOMContentLoaded', function () {
      fetchDevicesByLocation();
    });

 // Fetch devices by location and update the UI
    async function fetchDevicesByLocation() {
      try {
          const response = await fetch('/api/device/devices-by-location');
          const data = await response.json();

          if (data.status === 'success') {
              renderDevicesByLocation(data.data);
          } else {
              console.error('Devices by Location API Error:', data.message);
              renderDevicesByLocation([]);
          }
      } catch (error) {
          console.error('Fetch devices by location error:', error);
          renderDevicesByLocation([]);
      }
    }

    // Render devices by location
    function renderDevicesByLocation(locations) {
      const container = document.getElementById('devices-by-location');
      container.innerHTML = '';

      if (locations.length === 0) {
          container.innerHTML = '<p class="text-center text-muted py-8" style="color: var(--text-muted);">ไม่มีข้อมูลอุปกรณ์ในพื้นที่</p>';
          return;
      }

      locations.forEach((location, index) => {
          const locationElement = document.createElement('div');
          locationElement.classList.add('location-item', 'flex', 'items-center', 'justify-between', 'p-4', 'rounded-lg', 'transition-all', 'duration-200', 'hover:shadow-md', 'border');
          locationElement.style.cssText = `
              background-color: var(--bg-card);
              border-color: var(--border-color);
              animation: fadeInUp 0.3s ease-out ${index * 0.1}s both;
          `;
          
          // Create device count badge with conditional styling
          const deviceCount = parseInt(location.total_devices);
          let badgeClass = 'bg-gray-100 text-gray-600';
          let badgeStyle = 'background-color: var(--color-info-light); color: var(--color-info);';
          
          if (deviceCount === 0) {
              badgeStyle = 'background-color: var(--color-warning-light); color: var(--color-warning);';
          } else if (deviceCount >= 3) {
              badgeStyle = 'background-color: var(--color-success-light); color: var(--color-success);';
          }

          locationElement.innerHTML = `
              <div class="flex items-center flex-1">
                  <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3" style="background-color: var(--color-accent-light);">
                      <i class="fas fa-map-marker-alt" style="color: var(--color-accent); font-size: 1rem;"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                      <h4 class="font-semibold text-sm truncate" style="color: var(--text-primary);">${location.location_name || 'ไม่ระบุชื่อพื้นที่'}</h4>
                      <p class="text-xs mt-1" style="color: var(--text-muted);">รหัส: ${location.location_id}</p>
                  </div>
              </div>
              <div class="flex-shrink-0 ml-3">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="${badgeStyle}">
                      <i class="fas fa-microchip mr-1" style="font-size: 0.75rem;"></i>
                      ${location.total_devices} อุปกรณ์
                  </span>
              </div>
          `;
          
          // Add hover effects
          locationElement.addEventListener('mouseenter', function() {
              this.style.transform = 'translateY(-2px)';
              this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
          });
          
          locationElement.addEventListener('mouseleave', function() {
              this.style.transform = 'translateY(0)';
              this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
          });
          
          container.appendChild(locationElement);
      });
    }

    // Initialize devices by location section
    document.addEventListener('DOMContentLoaded', function () {
      fetchDevicesByLocation();
    });
  




     // แสดงข้อมูลล่าสุดของแต่ละอุปกรณ์ในตารางเปรียบเทียบ
    // ฟังก์ชันโหลดข้อมูลเปรียบเทียบอุปกรณ์ (ส่งช่วงวันที่ไป API)
    async function loadLatestDeviceTable() {
      const tableBody = document.getElementById('device-comparison-table');
      const startDate = document.getElementById('compare-date-start').value;
      const endDate = document.getElementById('compare-date-end').value;
      tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</td></tr>`;
      try {
        let url = '/api/environment/latest-by-device';
        if (startDate && endDate) {
          url += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }
        const res = await fetch(url);
        const json = await res.json();
        if (json.status !== 'success' || !Array.isArray(json.data)) {
          tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>`;
          return;
        }
        if (json.data.length === 0) {
          tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>`;
          return;
        }
        tableBody.innerHTML = '';
        json.data.forEach((row, index) => {
          // สีพื้นหลังสลับกัน
          const rowClass = index % 2 === 0 ? 'even-row' : 'odd-row';
          
          // กำหนดสถานะและสี
          let status = '';
          let statusClass = '';
          let statusIcon = '';
          if (row.landslide == 1 && row.floot == 1) {
            status = 'วิกฤตมาก';
            statusClass = 'bg-red-100 text-red-800 border border-red-200';
            statusIcon = '<i class="fas fa-exclamation-triangle mr-1"></i>';
          } else if (row.landslide == 1 || row.floot == 1) {
            status = 'วิกฤต';
            statusClass = 'bg-orange-100 text-orange-800 border border-orange-200';
            statusIcon = '<i class="fas fa-exclamation-circle mr-1"></i>';
          } else {
            status = 'ปกติ';
            statusClass = 'bg-green-100 text-green-800 border border-green-200';
            statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
          }

          // จัดรูปแบบวันที่และเวลา
          let updated = `${row.datekey || '-'} ${row.timekey || ''}`;
          if (row.datekey && row.timekey) {
            const date = new Date(row.datekey + ' ' + row.timekey);
            updated = `
              <div class="text-sm">
                <div class="font-medium">${row.datekey}</div>
                <div class="text-muted">${row.timekey}</div>
              </div>
            `;
          }

          // สร้างแถวข้อมูล
          tableBody.innerHTML += `
            <tr class="${rowClass} hover:bg-opacity-80 transition-colors duration-200" style="color: var(--text-primary);">
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-2" style="background-color: var(--color-accent);"></div>
                  <span class="font-medium">${row.device_id || '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-thermometer-half text-red-500 mr-2"></i>
                  <span>${row.temp ? parseFloat(row.temp).toFixed(1) + '°C' : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-tint text-blue-500 mr-2"></i>
                  <span>${row.humid ? parseFloat(row.humid).toFixed(1) + '%' : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-cloud-rain text-blue-600 mr-2"></i>
                  <span>${row.rain ? parseFloat(row.rain).toFixed(1) + ' mm' : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-wave-square text-yellow-600 mr-2"></i>
                  <span>${row.vibration ? parseFloat(row.vibration).toFixed(2) : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-seedling text-brown-600 mr-2"></i>
                  <span>${row.soil ? parseFloat(row.soil).toFixed(0) : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${row.landslide == 1 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'}">
                  ${row.landslide == 1 ? '<i class="fas fa-mountain mr-1"></i>เตือน' : '<i class="fas fa-check mr-1"></i>ปกติ'}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${row.floot == 1 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'}">
                  ${row.floot == 1 ? '<i class="fas fa-water mr-1"></i>เตือน' : '<i class="fas fa-check mr-1"></i>ปกติ'}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
                  ${statusIcon}${status}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                ${updated}
              </td>
            </tr>
          `;
        });
      } catch (e) {
        tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`;
      }
    }

    // เมื่อเลือกวันที่ ให้โหลดข้อมูลใหม่ทันที
    document.getElementById('compare-date-start').addEventListener('change', loadLatestDeviceTable);
    document.getElementById('compare-date-end').addEventListener('change', loadLatestDeviceTable);

    // เรียกใช้เมื่อโหลดหน้า หรือเมื่อกดปุ่มรีเฟรช
    document.addEventListener('DOMContentLoaded', function () {
      // ตั้งค่า default เป็นวันนี้
      const today = new Date().toISOString().slice(0, 10);
      document.getElementById('compare-date-start').value = today;
      document.getElementById('compare-date-end').value = today;
      loadLatestDeviceTable();
    });

    function refreshDeviceComparison() {
      loadLatestDeviceTable();
    }





// แสดงข้อมูลล่าสุดของแต่ละอุปกรณ์ในตารางเปรียบเทียบ
    // ฟังก์ชันโหลดข้อมูลเปรียบเทียบอุปกรณ์ (ส่งช่วงวันที่ไป API)
    async function loadLatestDeviceTable() {
      const tableBody = document.getElementById('device-comparison-table');
      const startDate = document.getElementById('compare-date-start').value;
      const endDate = document.getElementById('compare-date-end').value;
      tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">กำลังโหลดข้อมูล...</td></tr>`;
      try {
        let url = '/api/environment/latest-by-device';
        if (startDate && endDate) {
          url += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }
        const res = await fetch(url);
        const json = await res.json();
        if (json.status !== 'success' || !Array.isArray(json.data)) {
          tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>`;
          return;
        }
        if (json.data.length === 0) {
          tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">ไม่มีข้อมูล</td></tr>`;
          return;
        }
        tableBody.innerHTML = '';
        json.data.forEach((row, index) => {
          // สีพื้นหลังสลับกัน
          const rowClass = index % 2 === 0 ? 'even-row' : 'odd-row';
          
          // กำหนดสถานะและสี
          let status = '';
          let statusClass = '';
          let statusIcon = '';
          if (row.landslide == 1 && row.floot == 1) {
            status = 'วิกฤตมาก';
            statusClass = 'bg-red-100 text-red-800 border border-red-200';
            statusIcon = '<i class="fas fa-exclamation-triangle mr-1"></i>';
          } else if (row.landslide == 1 || row.floot == 1) {
            status = 'วิกฤต';
            statusClass = 'bg-orange-100 text-orange-800 border border-orange-200';
            statusIcon = '<i class="fas fa-exclamation-circle mr-1"></i>';
          } else {
            status = 'ปกติ';
            statusClass = 'bg-green-100 text-green-800 border border-green-200';
            statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
          }

          // จัดรูปแบบวันที่และเวลา
          let updated = `${row.datekey || '-'} ${row.timekey || ''}`;
          if (row.datekey && row.timekey) {
            const date = new Date(row.datekey + ' ' + row.timekey);
            updated = `
              <div class="text-sm">
                <div class="font-medium">${row.datekey}</div>
                <div class="text-muted">${row.timekey}</div>
              </div>
            `;
          }

          // สร้างแถวข้อมูล
          tableBody.innerHTML += `
            <tr class="${rowClass} hover:bg-opacity-80 transition-colors duration-200" style="color: var(--text-primary);">
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-2" style="background-color: var(--color-accent);"></div>
                  <span class="font-medium">${row.device_id || '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-thermometer-half text-red-500 mr-2"></i>
                  <span>${row.temp ? parseFloat(row.temp).toFixed(1) + '°C' : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-tint text-blue-500 mr-2"></i>
                  <span>${row.humid ? parseFloat(row.humid).toFixed(1) + '%' : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-cloud-rain text-blue-600 mr-2"></i>
                  <span>${row.rain ? parseFloat(row.rain).toFixed(1) + ' mm' : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-wave-square text-yellow-600 mr-2"></i>
                  <span>${row.vibration ? parseFloat(row.vibration).toFixed(2) : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <i class="fas fa-seedling text-brown-600 mr-2"></i>
                  <span>${row.soil ? parseFloat(row.soil).toFixed(0) : '-'}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${row.landslide == 1 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'}">
                  ${row.landslide == 1 ? '<i class="fas fa-mountain mr-1"></i>เตือน' : '<i class="fas fa-check mr-1"></i>ปกติ'}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${row.floot == 1 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'}">
                  ${row.floot == 1 ? '<i class="fas fa-water mr-1"></i>เตือน' : '<i class="fas fa-check mr-1"></i>ปกติ'}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
                  ${statusIcon}${status}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                ${updated}
              </td>
            </tr>
          `;
        });
      } catch (e) {
        tableBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`;
      }
    }

    // เมื่อเลือกวันที่ ให้โหลดข้อมูลใหม่ทันที
    document.getElementById('compare-date-start').addEventListener('change', loadLatestDeviceTable);
    document.getElementById('compare-date-end').addEventListener('change', loadLatestDeviceTable);

    // เรียกใช้เมื่อโหลดหน้า หรือเมื่อกดปุ่มรีเฟรช
    document.addEventListener('DOMContentLoaded', function () {
      // ตั้งค่า default เป็นวันนี้
      const today = new Date().toISOString().slice(0, 10);
      document.getElementById('compare-date-start').value = today;
      document.getElementById('compare-date-end').value = today;
      setActiveQuickDateBtn('today');
      loadLatestDeviceTable();
    });

    // ฟังก์ชันสำหรับ quick date selection
    function setQuickDate(period) {
      const today = new Date();
      const startInput = document.getElementById('compare-date-start');
      const endInput = document.getElementById('compare-date-end');
      let startDate, endDate;

      switch(period) {
        case 'today':
          startDate = endDate = today;
          break;
        case 'yesterday':
          const yesterday = new Date(today);
          yesterday.setDate(today.getDate() - 1);
          startDate = endDate = yesterday;
          break;
        case 'week':
          const weekAgo = new Date(today);
          weekAgo.setDate(today.getDate() - 7);
          startDate = weekAgo;
          endDate = today;
          break;
        case 'month':
          const monthAgo = new Date(today);
          monthAgo.setDate(today.getDate() - 30);
          startDate = monthAgo;
          endDate = today;
          break;
      }

      startInput.value = startDate.toISOString().slice(0, 10);
      endInput.value = endDate.toISOString().slice(0, 10);
      
      setActiveQuickDateBtn(period);
      loadLatestDeviceTable();
    }

    // ฟังก์ชันตั้งค่าปุ่ม active
    function setActiveQuickDateBtn(period) {
      document.querySelectorAll('.quick-date-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      const activeBtn = document.querySelector(`[onclick="setQuickDate('${period}')"]`);
      if (activeBtn) {
        activeBtn.classList.add('active');
      }
    }

    // ลบ active เมื่อเปลี่ยนวันที่ด้วยตนเอง
    document.getElementById('compare-date-start').addEventListener('change', function() {
      document.querySelectorAll('.quick-date-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      loadLatestDeviceTable();
    });

    document.getElementById('compare-date-end').addEventListener('change', function() {
      document.querySelectorAll('.quick-date-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      loadLatestDeviceTable();
    });

    // ฟังก์ชัน export ข้อมูลเปรียบเทียบอุปกรณ์เป็น Excel
    async function exportDeviceComparisonExcel() {
      const startDate = document.getElementById('compare-date-start').value;
      const endDate = document.getElementById('compare-date-end').value;
      let url = '/api/environment/latest-by-device';
      if (startDate && endDate) {
        url += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
      }
      try {
        const res = await fetch(url);
        const json = await res.json();
        if (json.status !== 'success' || !Array.isArray(json.data) || json.data.length === 0) {
          alert('ไม่พบข้อมูลสำหรับส่งออก');
          return;
        }
        // เตรียมข้อมูลสำหรับ Excel
        const wsData = [
          [
            'อุปกรณ์', 'อุณหภูมิ (°C)', 'ความชื้น (%)', 'ฝน (mm)', 'การสั่นสะเทือน',
            'ความชื้นดิน', 'ความเสี่ยงดินถล่ม', 'ความเสี่ยงน้ำท่วม', 'สถานะ', 'อัพเดทล่าสุด'
          ]
        ];
        json.data.forEach(row => {
          // สถานะรวม
          let status = '';
          if (row.landslide == 1 && row.floot == 1) status = 'วิกฤตมาก';
          else if (row.landslide == 1 || row.floot == 1) status = 'วิกฤต';
          else status = 'ปกติ';
          wsData.push([
            row.device_id || '-',
            row.temp !== undefined && row.temp !== null ? parseFloat(row.temp).toFixed(1) : '-',
            row.humid !== undefined && row.humid !== null ? parseFloat(row.humid).toFixed(1) : '-',
            row.rain !== undefined && row.rain !== null ? parseFloat(row.rain).toFixed(1) : '-',
            row.vibration !== undefined && row.vibration !== null ? parseFloat(row.vibration).toFixed(2) : '-',
            row.soil !== undefined && row.soil !== null ? parseFloat(row.soil).toFixed(0) : '-',
            row.landslide == 1 ? 'เตือน' : 'ปกติ',
            row.floot == 1 ? 'เตือน' : 'ปกติ',
            status,
            (row.datekey || '-') + ' ' + (row.timekey || '')
          ]);
        });
        // สร้างไฟล์ Excel
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'DeviceComparison');
        XLSX.writeFile(wb, 'device_comparison.xlsx');
      } catch (e) {
        alert('เกิดข้อผิดพลาดในการส่งออกข้อมูล');
      }
    }


// ฟังก์ชันแสดงข้อมูลสิ่งแวดล้อมล่าสุดแต่ละอุปกรณ์ (ตามเวลาล่าสุด)
    async function loadLatestEnvironmentPerDevice() {
      const container = document.getElementById('environment-data-container');
      
      // Enhanced loading state
      container.innerHTML = `
        <div class="environment-loading">
          <div class="loading-spinner"></div>
          <p class="text-lg font-medium">กำลังโหลดข้อมูล...</p>
          <p class="text-sm">กรุณารอสักครู่</p>
        </div>
      `;
      
      try {
        const res = await fetch('/api/environment/latest-by-device');
        const json = await res.json();
        
        if (json.status !== 'success' || !Array.isArray(json.data) || json.data.length === 0) {
          container.innerHTML = `
            <div class="environment-empty">
              <i class="fas fa-database"></i>
              <p class="text-lg font-medium">ไม่พบข้อมูล</p>
              <p class="text-sm">ยังไม่มีข้อมูลสิ่งแวดล้อมในระบบ</p>
            </div>
          `;
          return;
        }

        // Enhanced table with better styling
        let html = `
          <table class="environment-table w-full">
            <thead>
              <tr>
                <th><i class="fas fa-microchip mr-2"></i>อุปกรณ์</th>
                <th><i class="fas fa-thermometer-half mr-2"></i>อุณหภูมิ</th>
                <th><i class="fas fa-tint mr-2"></i>ความชื้น</th>
                <th><i class="fas fa-cloud-rain mr-2"></i>ฝน</th>
                <th><i class="fas fa-wave-square mr-2"></i>การสั่นสะเทือน</th>
                <th><i class="fas fa-seedling mr-2"></i>ความชื้นดิน</th>
                <th><i class="fas fa-mountain mr-2"></i>ดินถล่ม</th>
                <th><i class="fas fa-water mr-2"></i>น้ำท่วม</th>
                <th><i class="fas fa-clock mr-2"></i>อัพเดทล่าสุด</th>
              </tr>
            </thead>
            <tbody>
        `;
        
        json.data.forEach((row, idx) => {
          const landslideStatus = row.landslide == 1 
            ? '<span class="status-indicator status-critical"><i class="fas fa-exclamation-triangle"></i>เตือน</span>'
            : '<span class="status-indicator status-normal"><i class="fas fa-check"></i>ปกติ</span>';
            
          const floodStatus = row.floot == 1
            ? '<span class="status-indicator status-critical"><i class="fas fa-exclamation-triangle"></i>เตือน</span>'
            : '<span class="status-indicator status-normal"><i class="fas fa-check"></i>ปกติ</span>';
          
          html += `
            <tr class="${idx % 2 === 0 ? 'even-row' : 'odd-row'}">
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-gradient-to-r from-green-400 to-green-600"></div>
                  <span class="font-medium">${row.device_id || '-'}</span>
                </div>
              </td>
              <td class="font-mono">${row.temp !== undefined && row.temp !== null ? parseFloat(row.temp).toFixed(1) + '°C' : '-'}</td>
              <td class="font-mono">${row.humid !== undefined && row.humid !== null ? parseFloat(row.humid).toFixed(1) + '%' : '-'}</td>
              <td class="font-mono">${row.rain !== undefined && row.rain !== null ? parseFloat(row.rain).toFixed(1) + ' mm' : '-'}</td>
              <td class="font-mono">${row.vibration !== undefined && row.vibration !== null ? parseFloat(row.vibration).toFixed(2) : '-'}</td>
              <td class="font-mono">${row.soil !== undefined && row.soil !== null ? parseFloat(row.soil).toFixed(0) : '-'}</td>
              <td class="text-center">${landslideStatus}</td>
              <td class="text-center">${floodStatus}</td>
              <td>
                <div class="text-sm">
                  <div class="font-medium">${row.datekey || '-'}</div>
                  <div class="text-muted">${row.timekey || ''}</div>
                </div>
              </td>
            </tr>
          `;
        });
        
        html += `</tbody></table>`;
        container.innerHTML = html;
        
      } catch (e) {
        container.innerHTML = `
          <div class="environment-empty">
            <i class="fas fa-exclamation-triangle text-red-500"></i>
            <p class="text-lg font-medium text-red-600">เกิดข้อผิดพลาด</p>
            <p class="text-sm">ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง</p>
          </div>
        `;
      }
    }

    // เรียกใช้เมื่อโหลดหน้า (หรือจะเรียกเมื่อเลือก "ล่าสุด" ใน dropdown ก็ได้)
    document.addEventListener('DOMContentLoaded', function () {
      loadLatestEnvironmentPerDevice();
      // ...existing code...
    });

    // ฟังก์ชันส่งออกข้อมูลสิ่งแวดล้อมล่าสุดแต่ละอุปกรณ์เป็น Excel
    async function exportEnvironmentData() {
      try {
        const res = await fetch('/api/environment/latest-by-device');
        const json = await res.json();
        if (json.status !== 'success' || !Array.isArray(json.data) || json.data.length === 0) {
          alert('ไม่พบข้อมูลสำหรับส่งออก');
          return;
        }
        // เตรียมข้อมูลสำหรับ Excel
        const wsData = [
          [
            'อุปกรณ์', 'อุณหภูมิ (°C)', 'ความชื้น (%)', 'ฝน (mm)', 'การสั่นสะเทือน',
            'ความชื้นดิน', 'ดินถล่ม', 'น้ำท่วม', 'อัพเดทล่าสุด'
          ]
        ];
        json.data.forEach(row => {
          wsData.push([
            row.device_id || '-',
            row.temp !== undefined && row.temp !== null ? parseFloat(row.temp).toFixed(2) : '-',
            row.humid !== undefined && row.humid !== null ? parseFloat(row.humid).toFixed(2) : '-',
            row.rain !== undefined && row.rain !== null ? parseFloat(row.rain).toFixed(2) : '-',
            row.vibration !== undefined && row.vibration !== null ? parseFloat(row.vibration).toFixed(2) : '-',
            row.soil !== undefined && row.soil !== null ? parseFloat(row.soil).toFixed(2) : '-',
            row.landslide == 1 ? 'เตือน' : 'ปกติ',
            row.floot == 1 ? 'เตือน' : 'ปกติ',
            (row.datekey || '-') + ' ' + (row.timekey || '')
          ]);
        });
        // สร้างไฟล์ Excel
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Environment');
        XLSX.writeFile(wb, 'environment_latest.xlsx');
      } catch (e) {
        alert('เกิดข้อผิดพลาดในการส่งออกข้อมูล');
      }
    }


   