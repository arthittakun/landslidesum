<?php
// Include header
include 'plugin/header_user.php';
?>

<style>
  .about-section {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    padding: 2rem;
    margin-bottom: 2rem;
  }
  
  .about-content {
    line-height: 1.8;
    color: #374151;
  }
  
  .about-content h1, .about-content h2, .about-content h3 {
    color: #1f2937;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
  }
  
  .about-content h1 {
    font-size: 1.875rem;
    font-weight: 700;
  }
  
  .about-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
  }
  
  .about-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
  }
  
  .about-content p {
    margin-bottom: 1rem;
  }
  
  .about-content ul, .about-content ol {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
  }
  
  .about-content li {
    margin-bottom: 0.5rem;
  }
  
  .feature-card {
    transition: transform 0.2s ease-in-out;
  }
  
  .feature-card:hover {
    transform: translateY(-2px);
  }
</style>

<div class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Header Section -->
    <div class="text-center mb-12">
      <div class="flex items-center justify-center space-x-3 mb-6">
        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
          <i class="fas fa-mountain text-white text-xl"></i>
        </div>
        <div>
          <h1 class="text-4xl font-bold text-gray-900">เกี่ยวกับเรา</h1>
          <p class="text-sm text-gray-500">About Our Landslide Alert System</p>
        </div>
      </div>
      <div class="h-1 w-24 bg-blue-600 mx-auto rounded"></div>
    </div>

    <!-- About Content Section -->
    <div class="about-section">
      <div class="about-content" id="aboutContent">
        <!-- Content will be loaded here -->
        <div class="flex items-center justify-center py-12">
          <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mr-3"></i>
          <span class="text-gray-500">กำลังโหลดข้อมูลเกี่ยวกับเรา...</span>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="grid md:grid-cols-3 gap-6 mb-12">
      <div class="feature-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
          <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">ความปลอดภัย</h3>
        <p class="text-gray-600 text-sm">ระบบเตือนภัยที่มีประสิทธิภาพสูงเพื่อปกป้องชุมชนจากภัยธรรมชาติ</p>
      </div>

      <div class="feature-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
          <i class="fas fa-satellite-dish text-green-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">เทคโนโลยีทันสมัย</h3>
        <p class="text-gray-600 text-sm">ใช้เทคโนโลจี IoT และระบบตรวจสอบอัตโนมัติแบบเรียลไทม์</p>
      </div>

      <div class="feature-card bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
          <i class="fas fa-users text-orange-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">เพื่อชุมชน</h3>
        <p class="text-gray-600 text-sm">พัฒนาเพื่อให้บริการชุมชนและรักษาความปลอดภัยของประชาชน</p>
      </div>
    </div>

    <!-- Contact Section -->
    <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
      <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">ติดต่อเรา</h2>
      
      <div class="grid md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
          </div>
          <h3 class="font-semibold text-gray-900 mb-2">ที่อยู่</h3>
          <p class="text-gray-600 text-sm">มหาวิทยาลัยราชภัฏเชียงราย
80 หมู่ 9 ต.บ้านดู่ อ.เมือง จ.เชียงราย 57100</p>
        </div>

        <div class="text-center">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-phone text-green-600 text-xl"></i>
          </div>
          <h3 class="font-semibold text-gray-900 mb-2">โทรศัพท์</h3>
          <p class="text-gray-600 text-sm">053-776-118<br>089-558-8329</p>
        </div>

        <div class="text-center">
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-envelope text-orange-600 text-xl"></i>
          </div>
          <h3 class="font-semibold text-gray-900 mb-2">อีเมล</h3>
          <p class="text-gray-600 text-sm">info@landslide-alert.com<br>support@landslide-alert.com</p>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  loadAboutContent();
});

async function loadAboutContent() {
  try {
    const response = await fetch('/api/policy/get.php');
    const result = await response.json();
    
    if (result.success && result.data) {
      const aboutData = result.data.find(policy => policy.policy_type === 'about');
      
      if (aboutData && aboutData.policy_text) {
        document.getElementById('aboutContent').innerHTML = aboutData.policy_text;
      } else {
        showDefaultContent();
      }
    } else {
      showDefaultContent();
    }
  } catch (error) {
    console.error('Error loading about content:', error);
    showDefaultContent();
  }
}

function showDefaultContent() {
  const defaultContent = `
    <div class="text-center">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">ระบบจัดการและติดตามอุปกรณ์เตือนภัยดินถล่ม</h2>
      
      <div class="text-left max-w-4xl mx-auto space-y-4">
        <p>ระบบของเราได้รับการพัฒนาขึ้นเพื่อตอบสนองต่อความต้องการในการป้องกันและเตือนภัยจากดินถล่ม 
           ซึ่งเป็นภัยธรรมชาติที่สำคัญในพื้นที่ภูเขาและที่มีความลาดชัน</p>
        
        <p>ด้วยเทคโนโลยี Internet of Things (IoT) และระบบการตรวจสอบแบบเรียลไทม์ 
           ทำให้สามารถติดตามสถานการณ์และแจ้งเตือนได้อย่างรวดเร็วและแม่นยำ</p>
        
        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">วัตถุประสงค์หลัก</h3>
        <ul class="list-disc list-inside space-y-2 text-gray-700">
          <li>เฝ้าระวังและติดตามพื้นที่เสี่ยงต่อการเกิดดินถล่ม</li>
          <li>แจ้งเตือนประชาชนในพื้นที่เสี่ยงได้อย่างทันท่วงที</li>
          <li>จัดการข้อมูลและรายงานสถานการณ์อย่างมีประสิทธิภาพ</li>
          <li>สนับสนุนการตัดสินใจของหน่วยงานที่เกี่ยวข้อง</li>
        </ul>
        
        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">คุณสมบัติเด่น</h3>
        <ul class="list-disc list-inside space-y-2 text-gray-700">
          <li>ระบบตรวจสอบแบบเรียลไทม์ตลอด 24 ชั่วโมง</li>
          <li>การแจ้งเตือนผ่านหลายช่องทาง</li>
          <li>ระบบจัดการอุปกรณ์และตำแหน่งที่ติดตั้ง</li>
          <li>รายงานและวิเคราะห์ข้อมูลทางสถิติ</li>
        </ul>
      </div>
    </div>
  `;
  
  document.getElementById('aboutContent').innerHTML = defaultContent;
}
</script>

<?php include 'plugin/footer_user.php'; ?>
