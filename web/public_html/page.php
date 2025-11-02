<?php
include_once 'plugin/header.php';
?>

<div class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <!-- Header -->
  <div class="mb-6">
    <h1 class="text-3xl font-bold" style="color: var(--text-primary);">แดชบอร์ดระบบจัดการอุปกรณ์ดินถล่ม</h1>
    <p class="text-sm mt-1" style="color: var(--text-secondary);">ภาพรวมข้อมูลและสถิติล่าสุด</p>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Devices -->
    <div class="rounded-lg p-6 border" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary);">อุปกรณ์ทั้งหมด</p>
          <p class="text-2xl font-bold" style="color: var(--text-primary);" id="total-devices">-</p>
        </div>
        <div class="rounded-full p-3" style="background-color: var(--color-info-light);">
          <i class="fas fa-sitemap text-xl" style="color: var(--color-info);"></i>
        </div>
      </div>
    </div>

    <!-- Active Devices -->
    <div class="rounded-lg p-6 border" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary);">อุปกรณ์ที่ใช้งาน</p>
          <p class="text-2xl font-bold" style="color: var(--text-primary);" id="active-devices">-</p>
        </div>
        <div class="rounded-full p-3" style="background-color: var(--color-success-light);">
          <i class="fas fa-check-circle text-xl" style="color: var(--color-success);"></i>
        </div>
      </div>
    </div>

    <!-- Locations -->
    <div class="rounded-lg p-6 border" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary);">ตำแหน่งทั้งหมด</p>
          <p class="text-2xl font-bold" style="color: var(--text-primary);" id="total-locations">-</p>
        </div>
        <div class="rounded-full p-3" style="background-color: var(--color-accent-light);">
          <i class="fas fa-map-marker-alt text-xl" style="color: var(--color-accent);"></i>
        </div>
      </div>
    </div>

    <!-- Critical Alerts -->
    <div class="rounded-lg p-6 border" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary);">เตือนภัยวิกฤต</p>
          <p class="text-2xl font-bold" style="color: var(--text-primary);" id="critical-alerts">-</p>
        </div>
        <div class="rounded-full p-3" style="background-color: var(--color-danger-light);">
          <i class="fas fa-exclamation-triangle text-xl" style="color: var(--color-danger);"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Links Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Device Management -->
    <div class="rounded-lg border p-6 hover:shadow-lg transition-shadow cursor-pointer" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);" onclick="window.location.href='device.php'">
      <div class="flex items-center space-x-4">
        <div class="rounded-full p-4" style="background-color: var(--color-info-light);">
          <i class="fas fa-sitemap text-2xl" style="color: var(--color-info);"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">จัดการอุปกรณ์</h3>
          <p class="text-sm" style="color: var(--text-secondary);">เพิ่ม แก้ไข ลบ และดูสถานะอุปกรณ์</p>
        </div>
      </div>
      <div class="mt-4 text-right">
        <i class="fas fa-arrow-right" style="color: var(--color-info);"></i>
      </div>
    </div>

    <!-- Location Management -->
    <div class="rounded-lg border p-6 hover:shadow-lg transition-shadow cursor-pointer" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);" onclick="window.location.href='location.php'">
      <div class="flex items-center space-x-4">
        <div class="rounded-full p-4" style="background-color: var(--color-accent-light);">
          <i class="fas fa-map-marker-alt text-2xl" style="color: var(--color-accent);"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">จัดการตำแหน่ง</h3>
          <p class="text-sm" style="color: var(--text-secondary);">เพิ่ม แก้ไข ลบ ตำแหน่งติดตั้ง</p>
        </div>
      </div>
      <div class="mt-4 text-right">
        <i class="fas fa-arrow-right" style="color: var(--color-accent);"></i>
      </div>
    </div>

    <!-- User Management -->
    <div class="rounded-lg border p-6 hover:shadow-lg transition-shadow cursor-pointer" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);" onclick="window.location.href='users.php'">
      <div class="flex items-center space-x-4">
        <div class="rounded-full p-4" style="background-color: var(--color-success-light);">
          <i class="fas fa-users text-2xl" style="color: var(--color-success);"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">จัดการผู้ใช้</h3>
          <p class="text-sm" style="color: var(--text-secondary);">เพิ่ม แก้ไข ลบ บัญชีผู้ใช้</p>
        </div>
      </div>
      <div class="mt-4 text-right">
        <i class="fas fa-arrow-right" style="color: var(--color-success);"></i>
      </div>
    </div>

    <!-- Environment Data -->
    <div class="rounded-lg border p-6 hover:shadow-lg transition-shadow cursor-pointer" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);" onclick="window.location.href='dashbroad.php'">
      <div class="flex items-center space-x-4">
        <div class="rounded-full p-4" style="background-color: var(--color-warning-light);">
          <i class="fas fa-chart-line text-2xl" style="color: var(--color-warning);"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">ข้อมูลสิ่งแวดล้อม</h3>
          <p class="text-sm" style="color: var(--text-secondary);">ดูข้อมูลอุณหภูมิ ความชื้น และฝน</p>
        </div>
      </div>
      <div class="mt-4 text-right">
        <i class="fas fa-arrow-right" style="color: var(--color-warning);"></i>
      </div>
    </div>

    <!-- Weather Data -->
    <div class="rounded-lg border p-6 hover:shadow-lg transition-shadow cursor-pointer" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);" onclick="window.location.href='weather_day.php'">
      <div class="flex items-center space-x-4">
        <div class="rounded-full p-4" style="background-color: var(--color-info-light);">
          <i class="fas fa-cloud-sun text-2xl" style="color: var(--color-info);"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">ข้อมูลสภาพอากาศ</h3>
          <p class="text-sm" style="color: var(--text-secondary);">ดูพยากรณ์อากาศและเรดาร์</p>
        </div>
      </div>
      <div class="mt-4 text-right">
        <i class="fas fa-arrow-right" style="color: var(--color-info);"></i>
      </div>
    </div>

    <!-- Profile Settings -->
    <div class="rounded-lg border p-6 hover:shadow-lg transition-shadow cursor-pointer" style="background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 2px 4px var(--shadow-color);" onclick="window.location.href='profile.php'">
      <div class="flex items-center space-x-4">
        <div class="rounded-full p-4" style="background-color: var(--color-primary-light);">
          <i class="fas fa-user-cog text-2xl" style="color: var(--color-primary);"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text-primary);">จัดการโปรไฟล์</h3>
          <p class="text-sm" style="color: var(--text-secondary);">แก้ไขข้อมูลส่วนตัวและรหัสผ่าน</p>
        </div>
      </div>
      <div class="mt-4 text-right">
        <i class="fas fa-arrow-right" style="color: var(--color-primary);"></i>
      </div>
    </div>
  </div>
</div>

<script>
// Initialize theme observer when page loads
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.themeObserver === 'function') {
    window.themeObserver();
  }
  
  // Load page statistics
  loadPageStats();
});

async function loadPageStats() {
  try {
    const response = await fetch('api/reports/page-summary.php');
    const result = await response.json();
    
    if (result.success) {
      updateStatsCards(result.data);
    } else {
      console.error('Failed to load stats:', result.message);
      setDefaultStats();
    }
  } catch (error) {
    console.error('Error loading page stats:', error);
    setDefaultStats();
  }
}

function updateStatsCards(data) {
  // Update device stats
  document.getElementById('total-devices').textContent = data.device_counts.total || '0';
  document.getElementById('active-devices').textContent = data.device_counts.active || '0';
  
  // Update location stats
  document.getElementById('total-locations').textContent = data.location_counts.total || '0';
  
  // Update critical alerts
  document.getElementById('critical-alerts').textContent = data.alert_counts.total_alerts || '0';
}

function setDefaultStats() {
  document.getElementById('total-devices').textContent = '0';
  document.getElementById('active-devices').textContent = '0';
  document.getElementById('total-locations').textContent = '0';
  document.getElementById('critical-alerts').textContent = '0';
}
</script>

<?php
include_once 'plugin/footer.php';
?>