<?php
session_start();
if (isset($_SESSION['username']) && $_SESSION['username']) {
    header('Location: dashbroad');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิก</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-minimal@5/minimal.css">
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    [data-theme="light"] .register-container {
      background: linear-gradient(135deg, rgb(95, 233, 160) 0%, #66a6ff 100%);
    }
    
    [data-theme="dark"] .register-container {
      background: linear-gradient(135deg, #2a4b6c 0%, #1e3a57 100%);
    }
    
    html, body {
        height: 100%;
    }
    .register-container {
      
      min-height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem;
      width: 100%;
     
    }

    .register-card {
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      width: 100%;
      max-width: 420px;
      margin: 0 auto;
      box-sizing: border-box;
      transform: scale(0.8);
      transform-origin: center;
    }
    [data-theme="dark"] .register-card {
      background-color: rgba(30, 41, 59, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(71, 85, 105, 0.5);
    }

    [data-theme="dark"] .register-card h2,
    [data-theme="dark"] .register-card label,
    [data-theme="dark"] .register-card a,
    [data-theme="dark"] .register-card p,
    [data-theme="dark"] .register-card input::placeholder {
        color: #cbd5e1;
    }
    [data-theme="dark"] .register-card input {
        background-color: #334155;
        color: #f1f5f9;
        border-color: #475569;
    }
    [data-theme="dark"] .register-card .text-gray-800 { color: #e2e8f0; }
    [data-theme="dark"] .register-card .text-gray-600 { color: #94a3b8; }
    [data-theme="dark"] .register-card .text-gray-700 { color: #cbd5e1; }
    [data-theme="dark"] .register-card .text-gray-900 { color: #f1f5f9; }

    .input-with-icon {
      position: relative;
    }
    .input-icon {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }
    [data-theme="dark"] .input-icon {
      color: #6b7280;
    }
    .input-field {
      padding-left: 2.5rem;
      width: 100%;
      box-sizing: border-box;
      height: 2.5rem;
    }

    .theme-toggle-register {
        position: relative;
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        border: 1px solid rgba(0,0,0,0.1);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    [data-theme="dark"] .theme-toggle-register {
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.1);
    }

    .theme-toggle-register i {
        font-size: 16px;
        position: absolute;
    }
    .theme-toggle-register .fa-sun { color: #f59e0b; }
    .theme-toggle-register .fa-moon { color: #60a5fa; }
    [data-theme="dark"] .theme-toggle-register .fa-sun { color: #facc15; }
    [data-theme="dark"] .theme-toggle-register .fa-moon { color: #93c5fd; }

    .theme-toggle-register .fa-sun {
        opacity: 1;
    }
    .theme-toggle-register .fa-moon {
        opacity: 0;
    }

    .theme-toggle-register.active .fa-sun {
        opacity: 0;
    }
    .theme-toggle-register.active .fa-moon {
        opacity: 1;
    }

    /* Better responsive padding for different screen sizes */
    @media (max-width: 380px) {
      .register-card {
        padding: 1rem !important;
        transform: scale(0.85); /* Slightly larger on mobile for usability */
      }
      
      .theme-toggle-register {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
      }
    }
    
    .input-field {
      padding-left: 2.5rem;
      width: 100%;
      box-sizing: border-box;
      height: 2.5rem;
    }
    
    #register-button {
      width: 100%;
      position: relative;
      height: 2.75rem;
    }
    
    #loading-spinner {
      position: absolute;
      right: 1rem;
    }
    
    @media (max-width: 480px) {
      [data-aos] {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
    
    .register-card * {
      max-width: 100%;
    }
    
    @supports (-webkit-touch-callout: none) {
      .register-card {
        backdrop-filter: saturate(180%) blur(10px);
        -webkit-backdrop-filter: saturate(180%) blur(10px);
      }
    }

    @media (max-width: 767px) {
      html, body {
        min-height: 100%;
        height: 100%;
      }
      
      body {
        background-attachment: fixed;
      }
      
      .register-container {
        min-height: 100%;
      }
    }
  </style>
</head>
<body> 
  <div class="register-container p-4" id="register-body-container">
    <div class="register-card px-4 py-6 sm:px-6 sm:py-8 md:px-8 md:py-10 rounded-xl shadow-2xl">
      
      <div class="absolute top-3 right-3 md:top-4 md:right-4">
        <button id="theme-toggle-register-btn" class="theme-toggle-register">
          <i class="fas fa-sun"></i>
          <i class="fas fa-moon"></i>
        </button>
      </div>

      <div class="mb-4 flex justify-start absolute left-4 top-4 z-10">
        <a href="/" class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium text-gray-700 hover:underline focus:outline-none">
          <span class="mr-2">&#8592;</span>
          กลับไปหน้าแรก
        </a>
      </div>
      <div class="text-center mb-6 mt-8 md:mt-6">
        <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg mb-4">
          <i class="fas fa-user-plus text-white text-2xl"></i>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">สมัครสมาชิก</h2>
        <p class="text-gray-600 text-sm">กรอกข้อมูลเพื่อสร้างบัญชีใหม่</p>
      </div>
      
      <form id="register-form">
        <div class="space-y-4">
          
          <!-- Username Input -->
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ใช้</label>
            <div class="input-with-icon">
              <i class="input-icon fas fa-user"></i>
              <input 
                type="text" 
                id="username" 
                name="username" 
                required 
                class="input-field px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="กรุณากรอกชื่อผู้ใช้"
              >
            </div>
          </div>

          <!-- Email Input -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
            <div class="input-with-icon">
              <i class="input-icon fas fa-envelope"></i>
              <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                class="input-field px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="กรุณากรอกอีเมล"
              >
            </div>
          </div>

          <!-- Password Input -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
            <div class="input-with-icon">
              <i class="input-icon fas fa-lock"></i>
              <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                class="input-field px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="กรุณากรอกรหัสผ่าน (อย่างน้อย 8 ตัวอักษร)"
              >
            </div>
          </div>

          <!-- Confirm Password Input -->
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่าน</label>
            <div class="input-with-icon">
              <i class="input-icon fas fa-lock"></i>
              <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required 
                class="input-field px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="กรุณายืนยันรหัสผ่าน"
              >
            </div>
          </div>

          <!-- Register Button -->
          <button 
            type="submit" 
            id="register-button"
            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-4 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <span id="button-text">สมัครสมาชิก</span>
            <div id="loading-spinner" class="hidden inline-block ml-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
          </button>

          <!-- Login Link -->
          <div class="text-center">
            <p class="text-gray-600 text-sm">
              มีบัญชีอยู่แล้ว? 
              <a href="/login" class="text-blue-600 font-medium">
                เข้าสู่ระบบ
              </a>
            </p>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    const REGISTER_ENDPOINT = 'api/register';

    // Theme management
    const themeToggleRegisterBtn = document.getElementById('theme-toggle-register-btn');

    function applyThemeAndButtonStateRegister(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      if (themeToggleRegisterBtn) {
        if (theme === 'dark') {
          themeToggleRegisterBtn.classList.add('active');
        } else {
          themeToggleRegisterBtn.classList.remove('active');
        }
      }
    }

    // Load saved theme
    const savedThemeRegister = localStorage.getItem('theme') || 'light';
    applyThemeAndButtonStateRegister(savedThemeRegister);

    // Theme toggle event
    if (themeToggleRegisterBtn) {
      themeToggleRegisterBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyThemeAndButtonStateRegister(newTheme);
        localStorage.setItem('theme', newTheme);
      });
    }

    // Toast notification function
    function showToast(message, type = 'success') {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });
      
      Toast.fire({
        icon: type,
        title: message,
        iconColor: type === 'success' ? '#4ade80' : '#f87171'
      });
    }

    document.getElementById('register-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const loadingSpinner = document.getElementById('loading-spinner');
      const registerButton = document.getElementById('register-button');
      const buttonText = document.getElementById('button-text');
      
      loadingSpinner.classList.remove('hidden');
      buttonText.textContent = 'กำลังสมัครสมาชิก...';
      registerButton.disabled = true;
      
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      // Client-side validation
      if (!username) {
        showToast('กรุณากรอกชื่อผู้ใช้', 'error');
        resetButton();
        return;
      }
      
      if (!email) {
        showToast('กรุณากรอกอีเมล', 'error');
        resetButton();
        return;
      }
      
      if (!password) {
        showToast('กรุณากรอกรหัสผ่าน', 'error');
        resetButton();
        return;
      }
      
      if (password.length < 8) {
        showToast('รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร', 'error');
        resetButton();
        return;
      }
      
      if (password !== confirmPassword) {
        showToast('รหัสผ่านไม่ตรงกัน', 'error');
        resetButton();
        return;
      }
      
      // Submit form data
      const formData = new FormData();
      formData.append('username', username);
      formData.append('email', email);
      formData.append('password', password);
      formData.append('confirm_password', confirmPassword);
      
      fetch(REGISTER_ENDPOINT, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('สมัครสมาชิกสำเร็จ! กำลังนำคุณไปยังหน้าเข้าสู่ระบบ', 'success');
          setTimeout(() => {
            window.location.href = '/login';
          }, 1500);
        } else {
          showToast(data.error || 'เกิดข้อผิดพลาดในการสมัครสมาชิก', 'error');
          resetButton();
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง', 'error');
        resetButton();
      });
    });
    
    function resetButton() {
      const loadingSpinner = document.getElementById('loading-spinner');
      const registerButton = document.getElementById('register-button');
      const buttonText = document.getElementById('button-text');
      
      loadingSpinner.classList.add('hidden');
      buttonText.textContent = 'สมัครสมาชิก';
      registerButton.disabled = false;
    }
    
    // Fix for mobile devices when virtual keyboard appears
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
      input.addEventListener('focus', () => {
        setTimeout(() => {
          input.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }, 300);
      });
    });
    
    // Fix mobile color display issues
    function fixMobileViewport() {
      if (window.innerWidth <= 767) {
        const bodyContainer = document.getElementById('register-body-container');
        if (bodyContainer) {
          bodyContainer.style.minHeight = window.innerHeight + 'px';
        }
      }
    }
    
    // Call on page load
    window.addEventListener('load', fixMobileViewport);
    window.addEventListener('resize', fixMobileViewport);
  </script>
</body>
</html>
