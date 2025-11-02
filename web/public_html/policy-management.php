<?php
include_once 'plugin/header.php';

// Role-based page guard: only admin (role=1) can access policy management
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
  header('Location: dashbroad');
  exit;
}
?>

<style>
/* Policy Management Styles */
.policy-container {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  box-shadow: 0 2px 4px var(--shadow-color);
  transition: all 0.3s ease;
}

.policy-container:hover {
  box-shadow: 0 4px 8px var(--shadow-color);
}

.policy-header {
  background: linear-gradient(135deg, var(--color-accent-light), var(--color-accent));
  color: white;
  padding: 1rem;
  border-radius: 8px 8px 0 0;
  font-weight: 600;
}

.policy-content {
  padding: 1.5rem;
}

.quill-container {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  transition: border-color 0.3s ease;
}

.quill-container:focus-within {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.ql-toolbar {
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
  border-radius: 6px 6px 0 0;
}

.ql-editor {
  background: var(--bg-input);
  color: var(--text-primary);
  min-height: 200px;
  border-radius: 0 0 6px 6px;
}

.ql-editor::before {
  color: var(--text-muted);
}

.save-btn {
  background: linear-gradient(135deg, var(--color-accent), var(--color-accent-hover));
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.save-btn .spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.alert {
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 1rem;
  font-weight: 500;
}

.alert-success {
  background: rgba(16, 185, 129, 0.1);
  color: var(--color-success);
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  color: var(--color-danger);
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.tab-container {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.tab-btn {
  background: none;
  border: none;
  padding: 12px 24px;
  color: var(--text-secondary);
  font-weight: 500;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
}

.tab-btn.active {
  color: var(--color-accent);
  border-bottom-color: var(--color-accent);
}

.tab-btn:hover {
  color: var(--text-primary);
}

.policy-section {
  display: none;
}

.policy-section.active {
  display: block;
}

/* Dark mode adjustments */
[data-theme="dark"] .ql-toolbar {
  background: var(--bg-primary);
  border-bottom-color: var(--border-color);
}

[data-theme="dark"] .ql-editor {
  background: var(--bg-input);
  color: var(--text-primary);
}

[data-theme="dark"] .ql-stroke {
  stroke: var(--text-primary);
}

[data-theme="dark"] .ql-fill {
  fill: var(--text-primary);
}

[data-theme="dark"] .ql-picker-label {
  color: var(--text-primary);
}
</style>

<!-- Quill.js CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6" style="background-color: var(--bg-primary);">
  <div class="mb-6">
    <h1 class="text-2xl font-bold" style="color: var(--text-primary);">จัดการนโยบายและเงื่อนไข</h1>
    <p class="text-muted mt-1" style="color: var(--text-muted);">แก้ไขเนื้อหานโยบายความเป็นส่วนตัวและเงื่อนไขการใช้บริการ</p>
  </div>

  <!-- Alert Messages -->
  <div id="alertContainer"></div>

  <!-- Tab Navigation -->
  <div class="tab-container">
    <button class="tab-btn active" data-tab="term">
      <i class="fas fa-file-contract mr-2"></i>
      เงื่อนไขการใช้บริการ
    </button>
    <button class="tab-btn" data-tab="policy">
      <i class="fas fa-shield-alt mr-2"></i>
      นโยบายความเป็นส่วนตัว
    </button>
    <button class="tab-btn" data-tab="cookie">
      <i class="fas fa-cookie-bite mr-2"></i>
      นโยบายคุกกี้
    </button>
    <button class="tab-btn" data-tab="about">
      <i class="fas fa-info-circle mr-2"></i>
      เกี่ยวกับเรา
    </button>
  </div>

  <!-- Terms Section -->
  <div class="policy-section active" id="term-section">
    <div class="policy-container">
      <div class="policy-header">
        <h3 class="flex items-center">
          <i class="fas fa-file-contract mr-2"></i>
          เงื่อนไขการใช้บริการ
        </h3>
      </div>
      <div class="policy-content">
        <div class="quill-container">
          <div id="term-editor"></div>
        </div>
        <div class="mt-4 flex justify-end">
          <button class="save-btn" id="save-term-btn" onclick="savePolicyContent('term')">
            <i class="fas fa-save"></i>
            <span class="btn-text">บันทึกเงื่อนไข</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Policy Section -->
  <div class="policy-section" id="policy-section">
    <div class="policy-container">
      <div class="policy-header">
        <h3 class="flex items-center">
          <i class="fas fa-shield-alt mr-2"></i>
          นโยบายความเป็นส่วนตัว
        </h3>
      </div>
      <div class="policy-content">
        <div class="quill-container">
          <div id="policy-editor"></div>
        </div>
        <div class="mt-4 flex justify-end">
          <button class="save-btn" id="save-policy-btn" onclick="savePolicyContent('policy')">
            <i class="fas fa-save"></i>
            <span class="btn-text">บันทึกนโยบาย</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cookie Section -->
  <div class="policy-section" id="cookie-section">
    <div class="policy-container">
      <div class="policy-header">
        <h3 class="flex items-center">
          <i class="fas fa-cookie-bite mr-2"></i>
          นโยบายคุกกี้
        </h3>
      </div>
      <div class="policy-content">
        <div class="quill-container">
          <div id="cookie-editor"></div>
        </div>
        <div class="mt-4 flex justify-end">
          <button class="save-btn" id="save-cookie-btn" onclick="savePolicyContent('cookie')">
            <i class="fas fa-save"></i>
            <span class="btn-text">บันทึกนโยบายคุกกี้</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- About Section -->
  <div class="policy-section" id="about-section">
    <div class="policy-container">
      <div class="policy-header">
        <h3 class="flex items-center">
          <i class="fas fa-info-circle mr-2"></i>
          เกี่ยวกับเรา
        </h3>
      </div>
      <div class="policy-content">
        <div class="quill-container">
          <div id="about-editor"></div>
        </div>
        <div class="mt-4 flex justify-end">
          <button class="save-btn" id="save-about-btn" onclick="savePolicyContent('about')">
            <i class="fas fa-save"></i>
            <span class="btn-text">บันทึกข้อมูลเกี่ยวกับเรา</span>
          </button>
        </div>
      </div>
    </div>
  </div>

</main>

<!-- Quill.js -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
// Global variables
let termEditor, policyEditor, cookieEditor, aboutEditor;
let currentData = { term: '', policy: '', cookie: '', about: '' };

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.themeObserver === 'function') {
    window.themeObserver();
  }
  
  initializeEditors();
  setupTabNavigation();
  loadPolicyData();
});

// Initialize Quill editors
function initializeEditors() {
  const toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ 'header': 1 }, { 'header': 2 }],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    [{ 'script': 'sub'}, { 'script': 'super' }],
    [{ 'indent': '-1'}, { 'indent': '+1' }],
    [{ 'direction': 'rtl' }],
    [{ 'size': ['small', false, 'large', 'huge'] }],
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'font': [] }],
    [{ 'align': [] }],
    ['clean'],
    ['link', 'image']
  ];

  // Term editor
  termEditor = new Quill('#term-editor', {
    theme: 'snow',
    modules: {
      toolbar: toolbarOptions
    },
    placeholder: 'กรุณากรอกเงื่อนไขการใช้บริการ...'
  });

  // Policy editor
  policyEditor = new Quill('#policy-editor', {
    theme: 'snow',
    modules: {
      toolbar: toolbarOptions
    },
    placeholder: 'กรุณากรอกนโยบายความเป็นส่วนตัว...'
  });

  // Cookie editor
  cookieEditor = new Quill('#cookie-editor', {
    theme: 'snow',
    modules: {
      toolbar: toolbarOptions
    },
    placeholder: 'กรุณากรอกนโยบายคุกกี้...'
  });

  // About editor
  aboutEditor = new Quill('#about-editor', {
    theme: 'snow',
    modules: {
      toolbar: toolbarOptions
    },
    placeholder: 'กรุณากรอกข้อมูลเกี่ยวกับเรา...'
  });
}

// Setup tab navigation
function setupTabNavigation() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  const sections = document.querySelectorAll('.policy-section');

  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      const tabType = button.dataset.tab;

      // Update active tab
      tabButtons.forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');

      // Update active section
      sections.forEach(section => {
        section.classList.remove('active');
        if (section.id === `${tabType}-section`) {
          section.classList.add('active');
        }
      });
    });
  });
}

// Load policy data from API
async function loadPolicyData() {
  try {
    showAlert('กำลังโหลดข้อมูล...', 'info');
    
    const response = await fetch('api/policy/get.php');
    const result = await response.json();
    
    if (result.success) {
      // Clear info alert
      clearAlerts();
      
      // Set editor content
      if (result.data && Array.isArray(result.data)) {
        result.data.forEach(policy => {
          if (policy.policy_type === 'term') {
            termEditor.root.innerHTML = policy.policy_text || '';
            currentData.term = policy.policy_text || '';
          } else if (policy.policy_type === 'policy') {
            policyEditor.root.innerHTML = policy.policy_text || '';
            currentData.policy = policy.policy_text || '';
          } else if (policy.policy_type === 'cookie') {
            cookieEditor.root.innerHTML = policy.policy_text || '';
            currentData.cookie = policy.policy_text || '';
          } else if (policy.policy_type === 'about') {
            aboutEditor.root.innerHTML = policy.policy_text || '';
            currentData.about = policy.policy_text || '';
          }
        });
      }
    } else {
      throw new Error(result.message || 'ไม่สามารถโหลดข้อมูลได้');
    }
  } catch (error) {
    console.error('Load error:', error);
    showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message, 'error');
  }
}

// Save policy content
async function savePolicyContent(type) {
  const button = document.getElementById(`save-${type}-btn`);
  const buttonText = button.querySelector('.btn-text');
  let editor;
  
  // Get appropriate editor
  if (type === 'term') {
    editor = termEditor;
  } else if (type === 'policy') {
    editor = policyEditor;
  } else if (type === 'cookie') {
    editor = cookieEditor;
  } else if (type === 'about') {
    editor = aboutEditor;
  }
  
  try {
    // Show loading state
    button.disabled = true;
    buttonText.innerHTML = '<div class="spinner"></div> บันทึก...';
    
    const content = editor.root.innerHTML;
    
    // Validate content
    if (!content || content.trim() === '<p><br></p>' || content.trim() === '') {
      // Show warning toast for empty content
      if (window.SwalToast) {
        window.SwalToast.fire({
          icon: 'warning',
          title: 'กรุณากรอกเนื้อหา',
          text: `กรุณากรอก${type === 'term' ? 'เงื่อนไขการใช้บริการ' : type === 'policy' ? 'นโยบายความเป็นส่วนตัว' : type === 'cookie' ? 'นโยบายคุกกี้' : 'ข้อมูลเกี่ยวกับเรา'}`,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
      throw new Error('กรุณากรอกเนื้อหา');
    }
    
    // Send update request
    const response = await fetch('api/policy/update.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        policy_type: type,
        policy_text: content
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      currentData[type] = content;
      showAlert(`บันทึก${type === 'term' ? 'เงื่อนไข' : type === 'policy' ? 'นโยบาย' : type === 'cookie' ? 'นโยบายคุกกี้' : 'ข้อมูลเกี่ยวกับเรา'}เรียบร้อยแล้ว`, 'success');
      
      // Show toast notification
      if (window.SwalToast) {
        window.SwalToast.fire({
          icon: 'success',
          title: `บันทึก${type === 'term' ? 'เงื่อนไขการใช้บริการ' : type === 'policy' ? 'นโยบายความเป็นส่วนตัว' : type === 'cookie' ? 'นโยบายคุกกี้' : 'ข้อมูลเกี่ยวกับเรา'}เรียบร้อยแล้ว`,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
    } else {
      throw new Error(result.message || 'ไม่สามารถบันทึกได้');
    }
    
  } catch (error) {
    console.error('Save error:', error);
    showAlert('เกิดข้อผิดพลาดในการบันทึก: ' + error.message, 'error');
    
    // Show error toast
    if (window.SwalToast) {
      window.SwalToast.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาดในการบันทึก',
        text: error.message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
      });
    }
  } finally {
    // Reset button state
    button.disabled = false;
    buttonText.innerHTML = `บันทึก${type === 'term' ? 'เงื่อนไข' : type === 'policy' ? 'นโยบาย' : type === 'cookie' ? 'นโยบายคุกกี้' : 'ข้อมูลเกี่ยวกับเรา'}`;
  }
}

// Show alert message
function showAlert(message, type = 'info') {
  const container = document.getElementById('alertContainer');
  const alertDiv = document.createElement('div');
  
  let icon = '';
  let alertClass = '';
  
  switch (type) {
    case 'success':
      icon = 'fas fa-check-circle';
      alertClass = 'alert-success';
      break;
    case 'error':
      icon = 'fas fa-exclamation-circle';
      alertClass = 'alert-error';
      break;
    default:
      icon = 'fas fa-info-circle';
      alertClass = 'alert-info';
  }
  
  alertDiv.className = `alert ${alertClass}`;
  alertDiv.innerHTML = `
    <div class="flex items-center">
      <i class="${icon} mr-2"></i>
      <span>${message}</span>
    </div>
  `;
  
  container.appendChild(alertDiv);
  
  // Auto remove success and info alerts
  if (type === 'success' || type === 'info') {
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, type === 'success' ? 3000 : 1000);
  }
}

// Clear all alerts
function clearAlerts() {
  const container = document.getElementById('alertContainer');
  container.innerHTML = '';
}
</script>

<?php
include_once 'plugin/footer.php';
?>
