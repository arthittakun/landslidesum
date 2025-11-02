<?php
// Include header
include 'plugin/header_user.php';
?>

<style>
  .policy-container {
    max-width: 4xl;
  }
  
  .policy-section {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    padding: 2rem;
    margin-bottom: 2rem;
  }
  
  .policy-content {
    line-height: 1.8;
    color: #374151;
  }
  
  .policy-content h1, .policy-content h2, .policy-content h3 {
    color: #1f2937;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
  }
  
  .policy-content h1 {
    font-size: 1.875rem;
    font-weight: 700;
  }
  
  .policy-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
  }
  
  .policy-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
  }
  
  .policy-content p {
    margin-bottom: 1rem;
  }
  
  .policy-content ul, .policy-content ol {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
  }
  
  .policy-content li {
    margin-bottom: 0.5rem;
  }
</style>

<div class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Header Section -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">นโยบายความเป็นส่วนตัว</h1>
      <p class="text-lg text-gray-600">นโยบายการใช้งานและการปกป้องข้อมูลส่วนบุคคล</p>
      <div class="mt-4 h-1 w-20 bg-blue-600 mx-auto rounded"></div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-8">
      <nav class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
        <button onclick="showTab('privacy')" id="privacyTab" 
                class="tab-button active flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-colors">
          นโยบายความเป็นส่วนตัว
        </button>
        <button onclick="showTab('terms')" id="termsTab" 
                class="tab-button flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-colors">
          เงื่อนไขการใช้งาน
        </button>
        <button onclick="showTab('cookie')" id="cookieTab" 
                class="tab-button flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-colors">
          นโยบายคุกกี้
        </button>
      </nav>
    </div>

    <!-- Privacy Policy Tab -->
    <div id="privacyContent" class="tab-content">
      <div class="policy-section">
        <div class="policy-content" id="privacyPolicyContent">
          <!-- Content will be loaded here -->
          <div class="flex items-center justify-center py-12">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mr-3"></i>
            <span class="text-gray-500">กำลังโหลดนโยบายความเป็นส่วนตัว...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Terms of Service Tab -->
    <div id="termsContent" class="tab-content hidden">
      <div class="policy-section">
        <div class="policy-content" id="termsContent">
          <!-- Content will be loaded here -->
          <div class="flex items-center justify-center py-12">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mr-3"></i>
            <span class="text-gray-500">กำลังโหลดเงื่อนไขการใช้งาน...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Cookie Policy Tab -->
    <div id="cookieContent" class="tab-content hidden">
      <div class="policy-section">
        <div class="policy-content" id="cookiePolicyContent">
          <!-- Content will be loaded here -->
          <div class="flex items-center justify-center py-12">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mr-3"></i>
            <span class="text-gray-500">กำลังโหลดนโยบายคุกกี้...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Last Updated -->
    <div class="text-center text-sm text-gray-500 mt-8 pt-8 border-t border-gray-200">
      <p>ปรับปรุงล่าสุด: <span id="lastUpdated"><?php echo date('d/m/Y'); ?></span></p>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  loadPolicyContent();
});

let policyData = {};

async function loadPolicyContent() {
  try {
    const response = await fetch('/api/policy/get.php');
    const result = await response.json();
    
    if (result.success && result.data) {
      // Store policy data
      result.data.forEach(policy => {
        policyData[policy.policy_type] = policy.policy_text;
      });
      
      // Load initial content
      showTab('privacy');
    } else {
      showError('ไม่สามารถโหลดเนื้อหานโยบายได้');
    }
  } catch (error) {
    console.error('Error loading policy content:', error);
    showError('เกิดข้อผิดพลาดในการโหลดเนื้อหา');
  }
}

function showTab(tabName) {
  // Hide all tab contents
  document.querySelectorAll('.tab-content').forEach(content => {
    content.classList.add('hidden');
  });
  
  // Remove active class from all tabs
  document.querySelectorAll('.tab-button').forEach(button => {
    button.classList.remove('active');
    button.classList.add('bg-transparent', 'text-gray-700', 'hover:text-gray-900');
    button.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
  });
  
  // Show selected tab content
  let contentId, buttonId, policyType;
  
  switch(tabName) {
    case 'privacy':
      contentId = 'privacyContent';
      buttonId = 'privacyTab';
      policyType = 'policy';
      break;
    case 'terms':
      contentId = 'termsContent';
      buttonId = 'termsTab';
      policyType = 'term';
      break;
    case 'cookie':
      contentId = 'cookieContent';
      buttonId = 'cookieTab';
      policyType = 'cookie';
      break;
  }
  
  document.getElementById(contentId).classList.remove('hidden');
  
  const activeButton = document.getElementById(buttonId);
  activeButton.classList.add('active', 'bg-white', 'text-blue-600', 'shadow-sm');
  activeButton.classList.remove('bg-transparent', 'text-gray-700', 'hover:text-gray-900');
  
  // Load content
  loadTabContent(tabName, policyType);
}

function loadTabContent(tabName, policyType) {
  let contentElement;
  
  switch(tabName) {
    case 'privacy':
      contentElement = document.getElementById('privacyPolicyContent');
      break;
    case 'terms':
      contentElement = document.getElementById('termsContent');
      break;
    case 'cookie':
      contentElement = document.getElementById('cookiePolicyContent');
      break;
  }
  
  if (policyData[policyType]) {
    contentElement.innerHTML = policyData[policyType];
  } else {
    contentElement.innerHTML = '<div class="text-center py-8"><p class="text-gray-500">ยังไม่มีเนื้อหาสำหรับส่วนนี้</p></div>';
  }
}

function showError(message) {
  const errorHtml = `
    <div class="text-center py-12">
      <i class="fas fa-exclamation-circle text-3xl text-red-400 mb-4"></i>
      <p class="text-gray-600">${message}</p>
      <button onclick="loadPolicyContent()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        ลองใหม่
      </button>
    </div>
  `;
  
  document.getElementById('privacyPolicyContent').innerHTML = errorHtml;
  document.getElementById('termsContent').innerHTML = errorHtml;
  document.getElementById('cookiePolicyContent').innerHTML = errorHtml;
}
</script>

<?php include 'plugin/footer_user.php'; ?>
