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
  <title>เข้าสู่ระบบ</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-minimal@5/minimal.css">
  <script src="https://www.google.com/recaptcha/api.js?render=6LemoWIrAAAAAIKc3FoyaJh6IxEo_dtgpZLYbgXa"></script>
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    [data-theme="light"] body {
      background: linear-gradient(135deg, rgb(95, 233, 160) 0%, #66a6ff 100%);
    }
    
    [data-theme="dark"] body {
      background: linear-gradient(135deg, #2a4b6c 0%, #1e3a57 100%);
    }
    
    html, body {
        height: 100%;
    }
    .login-container {
      min-height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem;
      width: 100%;
    }

    .login-card {
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      width: 100%;
      max-width: 360px;
      margin: 0 auto;
      box-sizing: border-box;
      transform: scale(0.8);
      transform-origin: center;
    }
    [data-theme="dark"] .login-card {
      background-color: rgba(30, 41, 59, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(71, 85, 105, 0.5);
    }

    [data-theme="dark"] .login-card h2,
    [data-theme="dark"] .login-card label,
    [data-theme="dark"] .login-card a,
    [data-theme="dark"] .login-card p,
    [data-theme="dark"] .login-card input::placeholder {
        color: #cbd5e1;
    }
    [data-theme="dark"] .login-card input {
        background-color: #334155;
        color: #f1f5f9;
        border-color: #475569;
    }
    [data-theme="dark"] .login-card .text-gray-800 { color: #e2e8f0; }
    [data-theme="dark"] .login-card .text-gray-600 { color: #94a3b8; }
    [data-theme="dark"] .login-card .text-gray-700 { color: #cbd5e1; }
    [data-theme="dark"] .login-card .text-gray-900 { color: #f1f5f9; }

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

    .theme-toggle-login {
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
    [data-theme="dark"] .theme-toggle-login {
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.1);
    }

    .theme-toggle-login i {
        font-size: 16px;
        position: absolute;
    }
    .theme-toggle-login .fa-sun { color: #f59e0b; }
    .theme-toggle-login .fa-moon { color: #60a5fa; }
    [data-theme="dark"] .theme-toggle-login .fa-sun { color: #facc15; }
    [data-theme="dark"] .theme-toggle-login .fa-moon { color: #93c5fd; }

    .theme-toggle-login .fa-sun {
        opacity: 1;
    }
    .theme-toggle-login .fa-moon {
        opacity: 0;
    }

    .theme-toggle-login.active .fa-sun {
        opacity: 0;
    }
    .theme-toggle-login.active .fa-moon {
        opacity: 1;
    }

    @media (max-width: 380px) {
      .login-card {
        padding: 1rem !important;
        transform: scale(0.85); /* Slightly larger on mobile for usability */
      }
      
      .theme-toggle-login {
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
    
    #login-button {
      width: 100%;
      position: relative;
      height: 2.75rem;
    }
    
    #loading-spinner {
      position: absolute;
      right: 1rem;
    }
    
    .login-card * {
      max-width: 100%;
    }
    
    @supports (-webkit-touch-callout: none) {
      .login-card {
        backdrop-filter: saturate(180%) blur(10px);
        -webkit-backdrop-filter: saturate(180%) blur(10px);
      }
    }

    .grecaptcha-badge {
      visibility: hidden !important;
    }
    
    @media (max-width: 767px) {
      html, body {
        min-height: 100%;
        height: 100%;
      }
      
      body {
        background-attachment: fixed;
      }
      
      .login-container {
        min-height: 100%;
        padding-bottom: 80px;
      }
      
      .login-card {
        margin-bottom: 30px;
      }
    }
  </style>
</head>
<body > 
  <div class="login-container p-4" id="login-body-container">
    <div class="login-card px-4 py-6 sm:px-6 sm:py-8 md:px-8 md:py-10 rounded-xl shadow-2xl">
      
      <div class="absolute top-3 right-3 md:top-4 md:right-4">
        <button id="theme-toggle-login-btn" class="theme-toggle-login">
            <i class="fas fa-sun"></i>
            <i class="fas fa-moon"></i>
        </button>
      </div>

      <div class="mb-4 flex justify-start absolute left-4 top-4 z-10">
        <a href="/" class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium text-gray-700 focus:outline-none">
          <span class="mr-2">&#8592;</span>
          กลับไปหน้าแรก
        </a>
      </div>

      <div class="text-center mb-6 mt-8 md:mt-6">
        <svg class="mx-auto h-10 w-auto text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <h2 class="mt-4 text-2xl font-bold text-gray-800">เข้าสู่ระบบแอดมิน</h2>
        <p class="text-gray-600 mt-1 text-sm">ระบบจัดการอุปกรณ์ดินถล่ม</p>
      </div>
      
      <form id="login-form">
        <div class="mb-4">
          <label for="email" class="block text-xs font-medium text-gray-700 mb-1">อีเมลหรือชื่อผู้ใช้</label>
          <div class="input-with-icon">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </span>
            <input type="text" id="identifier" name="identifier" required
             class="input-field w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
             placeholder="กรอกอีเมล หรือ ชื่อผู้ใช้">
          </div>
        </div>
        
        <div class="mb-4">
          <label for="password" class="block text-xs font-medium text-gray-700 mb-1">รหัสผ่าน</label>
          <div class="input-with-icon">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </span>
            <input type="password" id="password" name="password" required
                   class="input-field w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                   placeholder="กรอกรหัสผ่าน">
          </div>
        </div>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-5 gap-2">
          <div class="text-xs text-center md:text-left">
            <a href="/auth/forgot-password" class="font-medium text-green-600 hover:text-green-700 transition-colors">
              ลืมรหัสผ่าน?
            </a>
          </div>
        </div>
        
        <div>
          <button type="submit" id="login-button"
                  class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-500 to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
            <span id="button-text">เข้าสู่ระบบ</span>
            <span id="loading-spinner" class="ml-2 hidden">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
          </button>
        </div>

        <div class="text-center mt-4">
          <p class="text-gray-600 text-sm">
            ยังไม่มีบัญชี? 
            <a href="/register" class="text-blue-600 font-medium">
              สมัครสมาชิก
            </a>
          </p>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    const RECAPTCHA_SITE_KEY = '6LemoWIrAAAAAIKc3FoyaJh6IxEo_dtgpZLYbgXa';
    const LOGIN_ENDPOINT = 'api/login';

    // Theme management
    const themeToggleLoginBtn = document.getElementById('theme-toggle-login-btn');

    function applyThemeAndButtonStateLogin(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      if (themeToggleLoginBtn) {
        if (theme === 'dark') {
          themeToggleLoginBtn.classList.add('active');
        } else {
          themeToggleLoginBtn.classList.remove('active');
        }
      }
    }

    // Load saved theme
    const savedThemeLogin = localStorage.getItem('theme') || 'light';
    applyThemeAndButtonStateLogin(savedThemeLogin);

    // Theme toggle event
    if (themeToggleLoginBtn) {
      themeToggleLoginBtn.addEventListener('click', () => {
        let currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
        
        applyThemeAndButtonStateLogin(newTheme);
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
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });
      
      Toast.fire({
        icon: type,
        title: message,
        background: '#ffffff',
        color: '#333333',
        iconColor: type === 'success' ? '#4ade80' : '#f87171'
      });
    }

    document.getElementById('login-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const loadingSpinner = document.getElementById('loading-spinner');
      const loginButton = document.getElementById('login-button');
      const buttonText = document.getElementById('button-text');
      
      loadingSpinner.classList.remove('hidden');
      buttonText.textContent = 'กำลังเข้าสู่ระบบ...';
      loginButton.disabled = true;
      
      const identifier = document.getElementById('identifier').value.trim();
      const password = document.getElementById('password').value;
      
      if (!identifier) {
        showToast('กรุณากรอกอีเมลหรือชื่อผู้ใช้', 'error');
        loadingSpinner.classList.add('hidden');
        buttonText.textContent = 'เข้าสู่ระบบ';
        loginButton.disabled = false;
        return;
      }
      
      if (!password) {
        showToast('กรุณากรอกรหัสผ่าน', 'error');
        loadingSpinner.classList.add('hidden');
        buttonText.textContent = 'เข้าสู่ระบบ';
        loginButton.disabled = false;
        return;
      }
      
      grecaptcha.ready(function() {
        grecaptcha.execute(RECAPTCHA_SITE_KEY, {action: 'login'})
          .then(function(token) {
            const formData = new FormData();
            formData.append('identifier', identifier);
            formData.append('password', password);
            formData.append('recaptcha_token', token);
            
            fetch(LOGIN_ENDPOINT, {
              method: 'POST',
              body: formData
            })
            .then(response => {
              console.log('Response status:', response.status);
              return response.json().catch(e => {
                throw new Error('Invalid JSON response from server');
              });
            })
            .then(data => {
              loadingSpinner.classList.add('hidden');
              buttonText.textContent = 'เข้าสู่ระบบ';
              loginButton.disabled = false;
              
              if (data.success) {
                showToast('เข้าสู่ระบบสำเร็จ!', 'success');
                setTimeout(() => {
                  window.location.href = 'dashbroad';
                }, 1000);
              } else {
                showToast(data.error || 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง', 'error');
              }
            })
            .catch(error => {
              console.error('Login Error:', error);
              loadingSpinner.classList.add('hidden');
              buttonText.textContent = 'เข้าสู่ระบบ';
              loginButton.disabled = false;
              showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง', 'error');
            });
          })
          .catch(function(error) {
            console.error('reCAPTCHA Error:', error);
            
            loadingSpinner.classList.add('hidden');
            buttonText.textContent = 'เข้าสู่ระบบ';
            loginButton.disabled = false;
            
            showToast('เกิดข้อผิดพลาดในการตรวจสอบความปลอดภัย', 'error');
          });
      });
    });
  </script>
</body>
</html>
  