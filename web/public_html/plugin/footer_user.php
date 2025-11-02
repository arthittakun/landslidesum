</main>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Main Footer Content -->
      <div class="py-8 lg:py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          
          <!-- Logo and Description -->
          <div class="col-span-1 sm:col-span-2 lg:col-span-2">
            <div class="flex items-center space-x-3 mb-4">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-mountain text-white text-lg"></i>
              </div>
              <div>
                <span class="text-xl font-bold text-gray-900 block">ระบบดินถล่ม</span>
                <p class="text-sm text-gray-500">Landslide Alert System</p>
              </div>
            </div>
            
            <p class="text-sm text-gray-600 leading-relaxed mb-6 max-w-md">
              ระบบจัดการและติดตามอุปกรณ์เตือนภัยดินถล่มเพื่อความปลอดภัยของชุมชน ด้วยเทคโนโลยีที่ทันสมัยและมีประสิทธิภาพ
            </p>
            
            <!-- Contact Info -->
            <div class="mb-6 space-y-2">
              <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-envelope w-5 mr-3 text-gray-400"></i>
                <span>info@landslidealert.com</span>
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-phone w-5 mr-3 text-gray-400"></i>
                <span>053-776-118</span>
              </div>
            </div>
            
            <!-- Social Media -->
            <div class="flex space-x-3">
              <a href="#" class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white hover:bg-blue-700 transition-colors shadow-md">
                <i class="fab fa-facebook-f text-sm"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-sky-500 flex items-center justify-center text-white hover:bg-sky-600 transition-colors shadow-md">
                <i class="fab fa-twitter text-sm"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white hover:bg-gray-800 transition-colors shadow-md">
                <i class="fab fa-github text-sm"></i>
              </a>
              <a href="#" class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white hover:bg-green-700 transition-colors shadow-md">
                <i class="fab fa-line text-sm"></i>
              </a>
            </div>
          </div>
          
          <!-- Equipment Links -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">อุปกรณ์</h3>
            <div class="space-y-3">
              <a href="/location_device.php" class="block text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-list w-4 mr-2 text-gray-400"></i>
                แผนที่อุปกรณ์
              </a>
              <a href="/" class="block text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-map-marker-alt w-4 mr-2 text-gray-400"></i>
                รายการตามพื้นที่
              </a>
            </div>
          </div>

    
          
          <!-- Weather Links -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">พยากรณ์อากาศ</h3>
            <div class="space-y-3">
              <a href="/weather_day" class="block text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-calendar-day w-4 mr-2 text-gray-400"></i>
                พยากรณ์รายวัน
              </a>
              <a href="/weather_hour" class="block text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-clock w-4 mr-2 text-gray-400"></i>
                พยากรณ์รายชั่วโมง
              </a>
              <a href="/radar_weather" class="block text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-satellite w-4 mr-2 text-gray-400"></i>
                เรดาร์ฝน
              </a>
                <a href="/rain_day" class="block text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-cloud-rain w-4 mr-2 text-gray-400"></i>
                ข้อมูลปริมาณน้ำฝน
              </a>
        
            </div>
          </div>
        </div>
      </div>
      
      <!-- Copyright -->
      <div class="border-t border-gray-200 py-6 lg:py-8">
        <div class="flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0">
          <div class="text-sm text-gray-500 order-2 lg:order-1">
            &copy; <?php echo date('Y'); ?> ระบบจัดการอุปกรณ์ดินถล่ม สงวนลิขสิทธิ์ทั้งหมด
          </div>
          <div class="flex flex-wrap items-center justify-center gap-4 lg:gap-6 text-sm text-gray-500 order-1 lg:order-2">
            <a href="/policy.php" class="hover:text-blue-600 transition-colors">นโยบายความเป็นส่วนตัว</a>
            <a href="/policy.php" class="hover:text-blue-600 transition-colors">เงื่อนไขการใช้งาน</a>
            <a href="/about.php" class="hover:text-blue-600 transition-colors">เกี่ยวกับเรา</a>
            <a href="/rain_day" class="hover:text-blue-600 transition-colors">ข้อมูลฝน</a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Scroll to Top Button -->
    <button onclick="scrollToTop()" 
            class="fixed bottom-6 right-6 w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 opacity-0 invisible"
            id="scrollToTopBtn">
      <i class="fas fa-chevron-up"></i>
    </button>
  </footer>

  <script>
    // Enhanced scroll to top function with show/hide button
    function scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Show/hide scroll to top button
    window.addEventListener('scroll', function() {
      const scrollToTopBtn = document.getElementById('scrollToTopBtn');
      if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.remove('opacity-0', 'invisible');
        scrollToTopBtn.classList.add('opacity-100', 'visible');
      } else {
        scrollToTopBtn.classList.add('opacity-0', 'invisible');
        scrollToTopBtn.classList.remove('opacity-100', 'visible');
      }
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
      const mobileMenu = document.getElementById('mobileMenu');
      const mobileMenuBtn = event.target.closest('[onclick="toggleMobileMenu()"]');
      
      if (!mobileMenuBtn && mobileMenu && !mobileMenu.contains(event.target) && !mobileMenu.classList.contains('hidden')) {
        toggleMobileMenu();
      }
      
      // Close dropdowns when clicking outside
      const weatherDropdown = document.getElementById('weatherDropdown');
      const deviceDropdown = document.getElementById('deviceDropdown');
      const weatherBtn = event.target.closest('[onclick="toggleWeatherDropdown()"]');
      const deviceBtn = event.target.closest('[onclick="toggleDeviceDropdown()"]');
      
      if (!weatherBtn && weatherDropdown && !weatherDropdown.contains(event.target) && !weatherDropdown.classList.contains('hidden')) {
        toggleWeatherDropdown();
      }
      
      if (!deviceBtn && deviceDropdown && !deviceDropdown.contains(event.target) && !deviceDropdown.classList.contains('hidden')) {
        toggleDeviceDropdown();
      }
    });

    // Close mobile menu on window resize
    window.addEventListener('resize', function() {
      const mobileMenu = document.getElementById('mobileMenu');
      if (window.innerWidth >= 768 && mobileMenu && !mobileMenu.classList.contains('hidden')) {
        toggleMobileMenu();
      }
    });
  </script>
</body>
</html>
