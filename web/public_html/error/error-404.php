<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : '404 - ไม่พบหน้าที่คุณต้องการ'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      transition: background 0.3s ease;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Add subtle moving background particles */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 0;
      pointer-events: none;
    }
    
    .particle {
      position: absolute;
      width: 5px;
      height: 5px;
      background-color: rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      animation: float-up 15s linear infinite;
    }
    
    @keyframes float-up {
      0% {
        transform: translateY(100vh) translateX(0) scale(0);
        opacity: 0;
      }
      20% {
        opacity: 0.8;
      }
      80% {
        opacity: 0.8;
      }
      100% {
        transform: translateY(-20vh) translateX(10vw) scale(1);
        opacity: 0;
      }
    }
    
    /* Theme styles */
    [data-theme="light"] body {
      background: linear-gradient(135deg, rgb(95, 233, 160) 0%, #66a6ff 100%);
    }
    
    [data-theme="dark"] body {
      background: linear-gradient(135deg, #2a4b6c 0%, #1e3a57 100%);
    }
    
    .theme-toggle {
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
      transition: background 0.3s ease, border-color 0.3s ease;
      padding: 0;
    }
    
    [data-theme="dark"] .theme-toggle {
      background: rgba(0,0,0,0.2);
      border: 1px solid rgba(255,255,255,0.1);
    }
    
    .theme-toggle:hover {
      background: rgba(255,255,255,0.3);
    }
    
    [data-theme="dark"] .theme-toggle:hover {
      background: rgba(0,0,0,0.3);
    }
    
    .theme-toggle i {
      font-size: 16px;
      position: absolute;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    .theme-toggle .fa-sun { 
      color: #f59e0b; 
    }
    
    .theme-toggle .fa-moon { 
      color: #60a5fa; 
    }
    
    [data-theme="dark"] .theme-toggle .fa-sun { 
      color: #facc15; 
    }
    
    [data-theme="dark"] .theme-toggle .fa-moon { 
      color: #93c5fd; 
    }
    
    .theme-toggle .fa-sun {
      opacity: 1;
      transform: scale(1) rotate(0deg);
    }
    
    .theme-toggle .fa-moon {
      opacity: 0;
      transform: scale(0.5) rotate(-90deg);
    }
    
    .theme-toggle.active .fa-sun {
      opacity: 0;
      transform: scale(0.5) rotate(90deg);
    }
    
    .theme-toggle.active .fa-moon {
      opacity: 1;
      transform: scale(1) rotate(0deg);
    }
    
    .error-card {
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1), 0 1px 8px rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="dark"] .error-card {
      background-color: rgba(30, 41, 59, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(71, 85, 105, 0.5);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25), 0 1px 8px rgba(0, 0, 0, 0.2);
    }
    
    [data-theme="dark"] .text-gray-800 {
      color: #e2e8f0;
    }
    
    [data-theme="dark"] .text-gray-600 {
      color: #94a3b8;
    }
    
    [data-theme="dark"] .btn-primary {
      background: linear-gradient(to right, rgba(16, 185, 129, 0.8), rgba(59, 130, 246, 0.8));
    }
    
    /* Enhanced animations */
    @keyframes float {
      0% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-15px) rotate(1deg); }
      100% { transform: translateY(0px) rotate(0deg); }
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    .float-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    .cloud-animation {
      animation: float 8s ease-in-out infinite;
      animation-delay: 1s;
    }
    
    .pulse-animation {
      animation: pulse 3s ease-in-out infinite;
    }
    
    /* Enhance card styling */
    .error-card {
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1), 0 1px 8px rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="dark"] .error-card {
      background-color: rgba(30, 41, 59, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(71, 85, 105, 0.5);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25), 0 1px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Button enhancements */
    .btn-primary {
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
      z-index: 1;
    }
    
    .btn-primary::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0) 100%);
      transform: translateX(-100%);
      animation: btn-shine 3s infinite;
      z-index: -1;
    }
    
    @keyframes btn-shine {
      100% {
        transform: translateX(100%);
      }
    }
    
    /* Enhanced SVG styles */
    .mountain-text {
      filter: drop-shadow(0px 2px 3px rgba(0, 0, 0, 0.2));
    }
    
    [data-theme="dark"] .mountain-text {
      filter: drop-shadow(0px 2px 3px rgba(0, 0, 0, 0.4));
    }
    
    /* Responsive adjustments */
    @media (max-width: 640px) {
      .error-illustration {
        max-width: 250px;
        margin: 0 auto;
      }
      
      .card-content-wrapper {
        padding: 2rem 1.25rem;
      }
    }
    
    /* Nature elements - Fireflies for dark mode, butterflies for light mode */
    .nature-elements {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 1;
      pointer-events: none;
    }
    
    /* Firefly styling for dark mode */
    .firefly {
      position: absolute;
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background-color: rgba(255, 255, 150, 0.5);
      box-shadow: 0 0 10px 2px rgba(255, 255, 0, 0.7);
      animation: firefly 12s linear infinite;
      opacity: 0;
      display: none;
    }
    
    [data-theme="dark"] .firefly {
      display: block;
    }
    
    @keyframes firefly {
      0% {
        opacity: 0;
        transform: translateX(0) translateY(0) scale(0.5);
      }
      10% {
        opacity: 0.8;
      }
      50% {
        opacity: 0.4;
      }
      70% {
        opacity: 0.8;
      }
      100% {
        opacity: 0;
        transform: translateX(var(--x)) translateY(var(--y)) scale(1);
      }
    }
    
    /* Butterfly styling for light mode */
    .butterfly {
      position: absolute;
      width: 24px;
      height: 24px;
      background-repeat: no-repeat;
      background-size: contain;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%2366a6ff' d='M12,2c0,0,4,3,4,7c0,0-4-1-4,3c0-4-4-3-4-3C8,5,12,2,12,2z M15,11c0,0,5,2,5,7c0,0-4-1-5,2c-1-3-5-2-5-2C10,13,15,11,15,11z M9,11c0,0-5,2-5,7c0,0,4-1,5,2c1-3,5-2,5-2C14,13,9,11,9,11z'/%3E%3C/svg%3E");
      animation: butterfly 18s linear infinite;
      opacity: 0;
      display: none;
    }
    
    [data-theme="light"] .butterfly {
      display: block;
    }
    
    @keyframes butterfly {
      0% {
        opacity: 0;
        transform: translateX(-10vw) translateY(10vh) scale(0.5) rotate(0deg);
      }
      10% {
        opacity: 0.8;
      }
      25% {
        transform: translateX(10vw) translateY(5vh) scale(0.7) rotate(20deg);
      }
      50% {
        transform: translateX(30vw) translateY(15vh) scale(0.8) rotate(-10deg);
        opacity: 0.7;
      }
      75% {
        transform: translateX(50vw) translateY(5vh) scale(0.7) rotate(15deg);
      }
      90% {
        opacity: 0.5;
      }
      100% {
        opacity: 0;
        transform: translateX(70vw) translateY(10vh) scale(0.5) rotate(0deg);
      }
    }
    
    /* Tree background elements */
    .bg-trees {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 20vh;
      z-index: 0;
      background-repeat: repeat-x;
      background-position: bottom center;
      pointer-events: none;
      opacity: 0.2;
    }
    
    [data-theme="light"] .bg-trees {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%2310b981' d='M0,224L48,213.3C96,203,192,181,288,192C384,203,480,235,576,245.3C672,256,768,245,864,224C960,203,1056,171,1152,165.3C1248,160,1344,181,1392,192L1440,203L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
    }
    
    [data-theme="dark"] .bg-trees {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23064e3b' d='M0,224L48,213.3C96,203,192,181,288,192C384,203,480,235,576,245.3C672,256,768,245,864,224C960,203,1056,171,1152,165.3C1248,160,1344,181,1392,192L1440,203L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
    }
    
    /* Tree elements in foreground */
    .tree-element {
      position: absolute;
      bottom: 0;
      background-repeat: no-repeat;
      background-position: bottom center;
      background-size: contain;
      z-index: 1;
      pointer-events: none;
      opacity: 0.2;
    }
    
    .tree-left {
      left: 5%;
      height: 45vh;
      width: 15vw;
    }
    
    .tree-right {
      right: 5%;
      height: 40vh;
      width: 12vw;
    }
    
    [data-theme="light"] .tree-element {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%2310b981' d='M12,3c-0.5,0-4.5,3-4.5,3c0,0-0.5,0-0.5,2c0,2,1,2,1,2c0,0-1,0.5-1,2c0,1.5,1,1.5,1,1.5c0,0-1,0.5-1,2 c0,1.5,1,2,1,2h-3.5c0,0-0.5,0-0.5,1c0,1,0.5,1,0.5,1h14c0,0,0.5,0,0.5-1c0-1-0.5-1-0.5-1h-3.5c0,0,1-0.5,1-2c0-1.5-1-2-1-2 c0,0,1,0,1-1.5c0-1.5-1-2-1-2c0,0,1,0,1-2c0-2-0.5-2-0.5-2c0,0-4-3-4.5-3'/%3E%3C/svg%3E");
    }
    
    [data-theme="dark"] .tree-element {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23064e3b' d='M12,3c-0.5,0-4.5,3-4.5,3c0,0-0.5,0-0.5,2c0,2,1,2,1,2c0,0-1,0.5-1,2c0,1.5,1,1.5,1,1.5c0,0-1,0.5-1,2 c0,1.5,1,2,1,2h-3.5c0,0-0.5,0-0.5,1c0,1,0.5,1,0.5,1h14c0,0,0.5,0,0.5-1c0-1-0.5-1-0.5-1h-3.5c0,0,1-0.5,1-2c0-1.5-1-2-1-2 c0,0,1,0,1-1.5c0-1.5-1-2-1-2c0,0,1,0,1-2c0-2-0.5-2-0.5-2c0,0-4-3-4.5-3'/%3E%3C/svg%3E");
    }
    
    /* Enhanced responsive design - focusing on mobile optimization */
    @media (max-width: 480px) {
      .error-card {
        max-width: calc(100% - 60px); /* 30px margins on each side */
        margin: 0 auto;
        padding: 1rem !important;
      }
      
      .error-illustration {
        max-width: 180px;
        margin: 0 auto;
      }
      
      .card-content-wrapper h1 {
        font-size: 1.25rem; /* Smaller heading on mobile */
      }
      
      .card-content-wrapper p {
        font-size: 0.75rem; /* Smaller body text */
        margin-bottom: 0.75rem;
      }
      
      .space-y-3 a {
        font-size: 0.875rem; /* Smaller button text */
        padding: 0.5rem 1rem;
        min-height: 2.5rem;
      }
      
      .cloud-animation {
        display: none; /* Hide clouds on very small screens */
      }
      
      /* Smaller footer text */
      .mt-6 p, .mt-6 a {
        font-size: 0.7rem;
      }
      
      /* More compact SVG for mobile */
      .mountain-text text:first-child {
        font-size: 60px;
      }
      
      .mountain-text text:last-child {
        font-size: 14px;
      }
      
      /* Adjusted spacing */
      .p-3 {
        padding: 0.5rem !important;
      }
      
      /* Smaller theme toggle button */
      .theme-toggle {
        width: 32px;
        height: 32px;
      }
    }
    
    /* Extra small mobile screens */
    @media (max-width: 360px) {
      .error-card {
        max-width: calc(100% - 40px); /* Even smaller margins for tiny screens */
        padding: 0.75rem !important;
      }
      
      .error-illustration {
        max-width: 140px;
      }
      
      .card-content-wrapper h1 {
        font-size: 1.125rem;
      }
      
      .card-content-wrapper p {
        font-size: 0.7rem;
        margin-bottom: 0.5rem;
      }
      
      /* Even more compact buttons */
      .space-y-3 {
        margin-top: 0.5rem;
      }
      
      .space-y-3 a {
        padding: 0.4rem 0.75rem;
        min-height: 2.25rem;
      }
    }
    
    /* Ensure text remains readable on all screen sizes */
    .card-content-wrapper p {
      max-width: 100%;
      word-wrap: break-word;
    }
    
    /* Ensure buttons remain usable on small screens */
    .space-y-4 a {
      white-space: normal; /* Allow button text to wrap */
      height: auto;
      min-height: 3rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    /* Improved 404 text visibility */
    .mountain-text text {
      font-weight: 700;
      stroke-width: 0.5px;
      stroke: rgba(0, 0, 0, 0.1);
    }
    
    [data-theme="dark"] .mountain-text text {
      stroke: rgba(255, 255, 255, 0.1);
    }
  </style>
</head>
<body>
  <!-- Nature elements container -->
  <div class="nature-elements" id="nature-elements">
    <!-- Fireflies and butterflies will be added via JavaScript -->
  </div>
  
  <!-- Tree background elements -->
  <div class="bg-trees"></div>
  
  <!-- Tree elements in foreground -->
  <div class="tree-element tree-left"></div>
  <div class="tree-element tree-right"></div>
  
  <!-- Background particles for visual effect -->
  <div class="particles" id="particles">
    <!-- Particles will be added via JavaScript -->
  </div>

  <div class="flex items-center justify-center min-h-screen p-2 sm:p-3 md:p-5 relative z-10">
    <div class="absolute top-2 right-2 sm:top-4 sm:right-4">
      <button id="theme-toggle-btn" class="theme-toggle">
        <i class="fas fa-sun"></i>
        <i class="fas fa-moon"></i>
      </button>
    </div>
    
    <div class="error-card rounded-xl shadow-2xl overflow-hidden w-full transform hover:scale-[1.01] transition-transform duration-300">
      <div class="flex flex-col md:flex-row">
        <!-- Illustration Section with enhanced styling -->
        <div class="md:w-1/2 p-4 sm:p-6 md:p-8 flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 relative overflow-hidden">
          <!-- Background wave pattern -->
          <div class="absolute inset-0 opacity-10">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="absolute bottom-0">
              <path fill="currentColor" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,213.3C672,224,768,224,864,213.3C960,203,1056,171,1152,186.7C1248,192,1344,224,1392,240L1440,256L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
          </div>
          
          <div class="error-illustration relative">
            <!-- Mountains and 404 with enhanced animations -->
            <div class="relative float-animation">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" class="w-full h-auto">
                <!-- Mountains with enhanced styling -->
                <path fill="#10b981" d="M30,220 L120,120 L200,200 L280,110 L390,230 L30,230 Z" class="pulse-animation" />
                <path fill="#059669" d="M180,230 L240,160 L300,220 L370,150 L390,230 Z" />
                
                <!-- 404 Text with enhanced styling -->
                <g class="mountain-text">
                  <text x="200" y="100" font-size="70" font-weight="bold" fill="#1e3a57" text-anchor="middle" class="mountain-text">404</text>
                  <text x="200" y="130" font-size="16" font-weight="bold" fill="#1e3a57" text-anchor="middle">Page Not Found</text>
                </g>
                
                <!-- Warning sign on mountain with pulse animation -->
                <g class="pulse-animation">
                  <circle cx="180" cy="150" r="15" fill="#f59e0b" />
                  <text x="180" y="155" font-size="20" font-weight="bold" fill="white" text-anchor="middle">!</text>
                </g>
              </svg>
            </div>
            
            <!-- Multiple floating clouds for better effect -->
            <div class="absolute top-10 right-10 cloud-animation">
              <svg width="50" height="30" viewBox="0 0 50 30" xmlns="http://www.w3.org/2000/svg">
                <path d="M10,25 C2,25 2,15 10,15 C11,10 16,5 22,10 C24,5 32,5 35,15 C42,15 42,25 35,25 Z" fill="rgba(255,255,255,0.8)" />
              </svg>
            </div>
            
            <div class="absolute top-40 left-5 cloud-animation" style="animation-delay: 3s;">
              <svg width="40" height="25" viewBox="0 0 50 30" xmlns="http://www.w3.org/2000/svg">
                <path d="M10,25 C2,25 2,15 10,15 C11,10 16,5 22,10 C24,5 32,5 35,15 C42,15 42,25 35,25 Z" fill="rgba(255,255,255,0.6)" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Content Section with enhanced styling -->
        <div class="md:w-1/2 card-content-wrapper p-3 sm:p-4 md:p-6 lg:p-8 flex flex-col justify-center bg-opacity-95">
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-1 sm:mb-2 relative">
            <span class="inline-block transform hover:scale-105 transition-transform duration-300">
              <?php echo isset($errorMessage) ? htmlspecialchars($errorMessage) : 'ไม่พบหน้าที่ค้นหา'; ?>
            </span>

          </h1>
          
          <p class="text-xs sm:text-sm md:text-base text-gray-600 mb-3 sm:mb-4 md:mb-8 leading-relaxed">หน้าเว็บที่คุณต้องการเข้าถึงไม่มีอยู่หรืออาจถูกย้ายไปที่อื่น บางทีคุณอาจต้องการตรวจสอบ URL อีกครั้ง</p>
          
          <div class="space-y-2 sm:space-y-3 md:space-y-4">
            <a href="/" class="inline-block w-full bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-medium py-2 sm:py-2.5 md:py-3 px-3 sm:px-4 rounded-md text-center transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 btn-primary text-sm sm:text-base">
              <i class="fas fa-home mr-1 sm:mr-2"></i> กลับไปยังหน้าหลัก
            </a>
            
            <a href="javascript:history.back()" class="inline-block w-full bg-transparent border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white font-medium py-2 sm:py-2.5 md:py-3 px-3 sm:px-4 rounded-md text-center hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-300 transform hover:-translate-y-1 text-sm sm:text-base">
              <i class="fas fa-arrow-left mr-1 sm:mr-2"></i> ย้อนกลับไปหน้าก่อนหน้า
            </a>
          </div>
          
          <div class="mt-4 sm:mt-6 md:mt-8 text-center">
            <p class="text-xs sm:text-xs md:text-sm text-gray-500 mb-1 sm:mb-2">หากคุณเชื่อว่านี่เป็นข้อผิดพลาด โปรดติดต่อผู้ดูแล</p>
            <a href="mailto:support@landslide-alerts.com" class="inline-flex items-center text-xs sm:text-xs md:text-sm text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition duration-300">
              <i class="fas fa-envelope mr-1"></i> support@landslide-alerts.com
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Theme toggle functionality
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    
    function applyThemeAndButtonState(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      if (theme === 'dark') {
        themeToggleBtn.classList.add('active');
      } else {
        themeToggleBtn.classList.remove('active');
      }
    }
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyThemeAndButtonState(savedTheme);
    
    // Theme toggle event
    themeToggleBtn.addEventListener('click', () => {
      let currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
      
      applyThemeAndButtonState(newTheme);
      localStorage.setItem('theme', newTheme);
      
      // Create nature elements after theme change
      setTimeout(createNatureElements, 100);
    });

    // Create floating particles in the background
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = Math.min(window.innerWidth / 10, 50); // Responsive particle count
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random positioning and timing
        const size = Math.random() * 5 + 2;
        const left = Math.random() * 100;
        const delay = Math.random() * 15;
        const duration = Math.random() * 15 + 10;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${left}%`;
        particle.style.animationDelay = `${delay}s`;
        particle.style.animationDuration = `${duration}s`;
        particle.style.opacity = Math.random() * 0.5;
        
        particlesContainer.appendChild(particle);
      }
    }
    
    // Create responsive nature elements based on screen size
    function createNatureElements() {
      const natureContainer = document.getElementById('nature-elements');
      const theme = document.documentElement.getAttribute('data-theme') || 'light';
      const width = window.innerWidth;
      
      // Clear existing elements
      natureContainer.innerHTML = '';
      
      // Adjust element count based on screen size
      let fireflyCount, butterflyCount;
      
      if (width < 480) {
        // Very small screens (mobile)
        fireflyCount = 8;
        butterflyCount = 3;
      } else if (width < 768) {
        // Small screens (large mobile)
        fireflyCount = 15;
        butterflyCount = 5;
      } else if (width < 1024) {
        // Medium screens (tablet)
        fireflyCount = 20;
        butterflyCount = 6;
      } else {
        // Large screens (desktop)
        fireflyCount = 30;
        butterflyCount = 8;
      }
      
      if (theme === 'dark') {
        // Create fireflies for dark mode
        for (let i = 0; i < fireflyCount; i++) {
          const firefly = document.createElement('div');
          firefly.classList.add('firefly');
          
          // Random positioning and timing
          const x = Math.random() * 200 - 100;
          const y = Math.random() * -200;
          const delay = Math.random() * 10;
          const duration = Math.random() * 12 + 8;
          const size = Math.random() * 3 + 2;
          
          firefly.style.setProperty('--x', `${x}px`);
          firefly.style.setProperty('--y', `${y}px`);
          firefly.style.left = `${Math.random() * 100}%`;
          firefly.style.top = `${Math.random() * 100}%`;
          firefly.style.width = `${size}px`;
          firefly.style.height = `${size}px`;
          firefly.style.animationDelay = `${delay}s`;
          firefly.style.animationDuration = `${duration}s`;
          
          natureContainer.appendChild(firefly);
        }
      } else {
        // Create butterflies for light mode
        for (let i = 0; i < butterflyCount; i++) {
          const butterfly = document.createElement('div');
          butterfly.classList.add('butterfly');
          
          // Random positioning and timing
          const startX = Math.random() * 30;
          const startY = Math.random() * 70 + 10;
          const delay = Math.random() * 8;
          const duration = Math.random() * 15 + 15;
          const size = Math.random() * 10 + 20;
          
          butterfly.style.left = `${startX}%`;
          butterfly.style.top = `${startY}%`;
          butterfly.style.width = `${size}px`;
          butterfly.style.height = `${size}px`;
          butterfly.style.animationDelay = `${delay}s`;
          butterfly.style.animationDuration = `${duration}s`;
          
          natureContainer.appendChild(butterfly);
        }
      }
    }
    
    // Initialize particles and nature elements
    window.addEventListener('load', () => {
      createParticles();
      createNatureElements();
    });
    
    // Responsive handler for window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
      // Debounce the resize event for better performance
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(() => {
        const particlesContainer = document.getElementById('particles');
        particlesContainer.innerHTML = '';
        createParticles();
        createNatureElements();
      }, 250);
    });
    
    // Fix for iOS height issues
    function fixIOSHeight() {
      if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
        document.documentElement.style.height = '100%';
        document.body.style.height = '100%';
      }
    }
    
    window.addEventListener('load', fixIOSHeight);
  </script>
</body>
</html>

