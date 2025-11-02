
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบจัดการอุปกรณ์ดินถล่ม</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            'sans': ['Inter', 'sans-serif']
          }
        }
      }
    }
  </script>
  
  <style>
    /* Loading Overlay */
    .page-loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.95);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      backdrop-filter: blur(5px);
    }

    .page-loading-spinner {
      width: 50px;
      height: 50px;
      border: 4px solid #e3f2fd;
      border-top: 4px solid #2563eb;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 20px;
    }

    .page-loading-text {
      font-size: 18px;
      font-weight: 600;
      color: #2563eb;
      text-align: center;
      margin-bottom: 10px;
    }

    .page-loading-subtext {
      font-size: 14px;
      color: #6b7280;
      text-align: center;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Hidden class for loading overlay */
    .hidden-loading {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-50 font-sans">
  <!-- Page Loading Overlay -->
  <div id="pageLoadingOverlay" class="page-loading-overlay">
    <div class="page-loading-spinner"></div>
    <div class="page-loading-text">กำลังโหลดหน้าเว็บ</div>
    <div class="page-loading-subtext">กรุณารอสักครู่...</div>
  </div>

  <!-- Navigation Bar -->
  <nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        
        <!-- Logo -->
        <div class="flex items-center">
          <a href="/" class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-mountain text-white text-sm"></i>
            </div>
            <span class="text-lg font-semibold text-gray-900">ระบบดินถล่ม</span>
          </a>
        </div>
        
        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          
          <!-- รายการอุปกรณ์ตามพื้นที่ Dropdown -->
          <div class="relative">
            <button onclick="toggleDeviceDropdown()" 
                    class="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-list"></i>
              <span>รายการอุปกรณ์</span>
              <i id="deviceChevron" class="fas fa-chevron-down text-xs"></i>
            </button>
            <!-- Dropdown Menu -->
            <div id="deviceDropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
              <div class="py-1">
                <a href="/location_device.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <i class="fas fa-list w-4 mr-3 text-gray-400"></i>
                  <span>แผนที่อุปกรณ์</span>
                </a>
                <a href="/list-location" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <i class="fas fa-map-marker-alt w-4 mr-3 text-gray-400"></i>
                  <span>ตามพื้นที่</span>
                </a>
              </div>
            </div>
          </div>
          <div class="relative">
            <a href="/rain_day" class="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-cloud-rain"></i>
              <span>ข้อมูลปริมาณน้ำฝน</span>
            </a>
          </div>
          
          
          <!-- พยากรณ์อากาศ Dropdown -->
          <div class="relative">
            <button onclick="toggleWeatherDropdown()" 
                    class="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-cloud-sun"></i>
              <span>พยากรณ์อากาศ</span>
              <i id="weatherChevron" class="fas fa-chevron-down text-xs"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div id="weatherDropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
              <div class="py-1">
                <a href="/weather_day" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <i class="fas fa-calendar-day w-4 mr-3 text-gray-400"></i>
                  <span>พยากรณ์รายวัน</span>
                </a>
                <a href="/weather_hour" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <i class="fas fa-clock w-4 mr-3 text-gray-400"></i>
                  <span>พยากรณ์รายชั่วโมง</span>
                </a>
                <a href="/radar_weather" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <i class="fas fa-satellite w-4 mr-3 text-gray-400"></i>
                  <span>เรดาร์ฝน</span>
                </a>
              </div>
            </div>
          </div>

          <!-- เกี่ยวกับเรา -->
          <a href="/about.php" class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
            <i class="fas fa-info-circle"></i>
            <span>เกี่ยวกับเรา</span>
          </a>
          
          <!-- Authentication Buttons -->
          <div class="flex items-center space-x-3 border-l border-gray-300 pl-6">
            <a href="/login" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 border border-gray-300 hover:border-blue-600 rounded-md transition-colors">
              <i class="fas fa-sign-in-alt"></i>
              <span>เข้าสู่ระบบ</span>
            </a>
            <a href="/register" class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
              <i class="fas fa-user-plus"></i>
              <span>สมัครสมาชิก</span>
            </a>
          </div>
        </div>
        
        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button onclick="toggleMobileMenu()" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
            <i id="mobileMenuIcon" class="fas fa-bars text-lg"></i>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Mobile Navigation Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-gray-200">
      <div class="px-2 pt-2 pb-3 space-y-1">
        
        <!-- รายการอุปกรณ์ Dropdown -->
        <div>
          <button onclick="toggleMobileDeviceMenu()" 
                  class="flex items-center justify-between w-full px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
            <div class="flex items-center space-x-2">
              <i class="fas fa-list"></i>
              <span>รายการอุปกรณ์</span>
            </div>
            <i id="mobileDeviceChevron" class="fas fa-chevron-down text-xs"></i>
          </button>
          <div id="mobileDeviceMenu" class="hidden ml-6 mt-1 space-y-1">
            <a href="/location_device.php" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-list w-4 mr-3 text-gray-400"></i>
              <span>แผนที่อุปกรณ์</span>
            </a>
            <a href="/list-location" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-map-marker-alt w-4 mr-3 text-gray-400"></i>
              <span>ตามพื้นที่</span>
            </a>
          </div>
        </div>
        
        <!-- ข้อมูลปริมาณน้ำฝน (Mobile) -->
        <a href="/rain_day" class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
          <i class="fas fa-cloud-rain mr-3"></i>
          <span>ข้อมูลปริมาณน้ำฝน</span>
        </a>
        
        <!-- พยากรณ์อากาศ Section -->
        <div>
          <button onclick="toggleMobileWeatherMenu()" 
                  class="flex items-center justify-between w-full px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
            <div class="flex items-center space-x-2">
              <i class="fas fa-cloud-sun"></i>
              <span>พยากรณ์อากาศ</span>
            </div>
            <i id="mobileWeatherChevron" class="fas fa-chevron-down text-xs"></i>
          </button>
          
          <div id="mobileWeatherMenu" class="hidden ml-6 mt-1 space-y-1">
            <a href="/weather_day" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-calendar-day w-4 mr-3 text-gray-400"></i>
              <span>พยากรณ์รายวัน</span>
            </a>
            <a href="/weather_hour" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-clock w-4 mr-3 text-gray-400"></i>
              <span>พยากรณ์รายชั่วโมง</span>
            </a>
            <a href="/radar_weather" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-md">
              <i class="fas fa-satellite w-4 mr-3 text-gray-400"></i>
              <span>เรดาร์ฝน</span>
            </a>
          </div>
        </div>

        <!-- เกี่ยวกับเรา -->
        <a href="/about.php" class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
          <i class="fas fa-info-circle mr-3"></i>
          <span>เกี่ยวกับเรา</span>
        </a>
        
        <!-- Mobile Authentication Buttons -->
        <div class="border-t border-gray-200 pt-3 mt-3 space-y-2">
          <a href="/login" class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md">
            <i class="fas fa-sign-in-alt mr-3"></i>
            <span>เข้าสู่ระบบ</span>
          </a>
          <a href="/register" class="flex items-center px-3 py-2 text-base font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
            <i class="fas fa-user-plus mr-3"></i>
            <span>สมัครสมาชิก</span>
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content Container -->
  <main class="min-h-screen">

  <!-- Cookie Consent Banner -->
  <div id="cookieConsent" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
    <div class="max-w-6xl mx-auto p-4">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 mt-1">
              <i class="fas fa-cookie-bite text-2xl text-blue-600"></i>
            </div>
            <div>
              <h3 class="text-sm font-semibold mb-1 text-gray-900">การใช้คุกกี้ในเว็บไซต์</h3>
              <p class="text-xs leading-relaxed text-gray-600">
                เราใช้คุกกี้เพื่อพัฒนาประสิทธิภาพและประสบการณ์ที่ดีในการใช้เว็บไซต์ของคุณ 
                คุณสามารถศึกษารายละเอียดได้ที่ <a href="/policy.php" class="text-blue-600 hover:underline">นโยบายความเป็นส่วนตัว</a>
              </p>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button id="cookieAccept" onclick="acceptCookies()" 
                  class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
            รับทราบ
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Simple Dropdown Functions
    function toggleWeatherDropdown() {
      const dropdown = document.getElementById('weatherDropdown');
      const chevron = document.getElementById('weatherChevron');
      
      // Toggle weather dropdown
      dropdown.classList.toggle('hidden');
      chevron.classList.toggle('rotate-180');
    }

    function toggleDeviceDropdown() {
      const dropdown = document.getElementById('deviceDropdown');
      const chevron = document.getElementById('deviceChevron');
      
      // Toggle device dropdown
      dropdown.classList.toggle('hidden');
      chevron.classList.toggle('rotate-180');
    }

    // Mobile Menu Functions
    function toggleMobileMenu() {
      const mobileMenu = document.getElementById('mobileMenu');
      const icon = document.getElementById('mobileMenuIcon');
      
      mobileMenu.classList.toggle('hidden');
      
      if (mobileMenu.classList.contains('hidden')) {
        icon.className = 'fas fa-bars text-lg';
      } else {
        icon.className = 'fas fa-times text-lg';
      }
    }

    function toggleMobileWeatherMenu() {
      const menu = document.getElementById('mobileWeatherMenu');
      const chevron = document.getElementById('mobileWeatherChevron');
      
      menu.classList.toggle('hidden');
      chevron.classList.toggle('rotate-180');
    }

    function toggleMobileDeviceMenu() {
      const menu = document.getElementById('mobileDeviceMenu');
      const chevron = document.getElementById('mobileDeviceChevron');
      
      menu.classList.toggle('hidden');
      chevron.classList.toggle('rotate-180');
    }

    // Cookie Functions
    function acceptCookies() {
      localStorage.setItem('cookieConsent', 'accepted');
      const banner = document.getElementById('cookieConsent');
      banner.classList.add('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
      const weatherDropdown = document.getElementById('weatherDropdown');
      const weatherChevron = document.getElementById('weatherChevron');
      const reportsDropdown = document.getElementById('reportsDropdown');
      const reportsChevron = document.getElementById('reportsChevron');
      
      if (!event.target.closest('.relative')) {
        if (!weatherDropdown.classList.contains('hidden')) {
          weatherDropdown.classList.add('hidden');
          weatherChevron.classList.remove('rotate-180');
        }
        if (!reportsDropdown.classList.contains('hidden')) {
          reportsDropdown.classList.add('hidden');
          reportsChevron.classList.remove('rotate-180');
        }
      }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
      // Hide page loading overlay when DOM is ready
      setTimeout(function() {
        const pageLoadingOverlay = document.getElementById('pageLoadingOverlay');
        if (pageLoadingOverlay) {
          pageLoadingOverlay.classList.add('hidden-loading');
        }
      }, 800); // Hide after 0.8 seconds
      
      // Show cookie banner if not accepted
      if (!localStorage.getItem('cookieConsent')) {
        setTimeout(() => {
          const banner = document.getElementById('cookieConsent');
          banner.classList.remove('hidden');
        }, 1000);
      }
    });
  </script>
