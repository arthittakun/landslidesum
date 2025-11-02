<?php
session_start();
if (!isset($_SESSION['username']) || !$_SESSION['username']) {
    header('Location: login');
    exit;
}

// Fetch current user role and store in session for permission checks
try {
    if (!isset($_SESSION['role'])) {
        require_once __DIR__ . '/../../database/table_user.php';
        $__tableUserForHeader = new Table_user();
        $__currentHeaderUser = null;
        if (!empty($_SESSION['username'])) {
            $__currentHeaderUser = $__tableUserForHeader->getUserByUsername($_SESSION['username']);
        }
        if (!$__currentHeaderUser && !empty($_SESSION['email'])) {
            $__currentHeaderUser = $__tableUserForHeader->getUserByEmail($_SESSION['email']);
        }
        if ($__currentHeaderUser && isset($__currentHeaderUser['role'])) {
            $_SESSION['role'] = (int)$__currentHeaderUser['role'];
        }
    }
} catch (Throwable $__e) { /* ignore */ }
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>แดชบอร์ด - ระบบจัดการอุปกรณ์ดินถล่ม</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Kanit', sans-serif;
    }
    /* Comprehensive theme color system */
    :root {
      /* Main backgrounds */
      --bg-primary: #f9fafb;
      --bg-secondary: #f3f4f6;
      --bg-sidebar: #ffffff;
      --bg-navbar: #ffffff;
      --bg-card: #ffffff;
      --bg-input: #ffffff;
      --bg-dropdown: #ffffff;
      --bg-tooltip: #374151;
      
      /* Text colors */
      --text-primary: #111827;
      --text-secondary: #4b5563;
      --text-muted: #6b7280;
      --text-on-accent: #ffffff;
      --text-on-danger: #ffffff;
      
      /* Borders & Dividers */
      --border-color: #e5e7eb;
      --border-dark-color: #d1d5db;
      --divider-color: #e5e7eb;
      
      /* Status colors */
      --color-success: #10b981;
      --color-success-light: #d1fae5;
      --color-warning: #f59e0b;
      --color-warning-light: #fef3c7;
      --color-danger: #ef4444;
      --color-danger-light: #fee2e2;
      --color-info: #3b82f6;
      --color-info-light: #dbeafe;
      
      /* Accent/brand colors */
      --color-accent: #10b981; /* Main brand color - green */
      --color-accent-hover: #059669;
      --color-accent-light: #a7f3d0;
      
      /* Other UI elements */
      --shadow-color: rgba(0, 0, 0, 0.1);
      --ring-color: rgba(16, 185, 129, 0.5);
      --focus-ring: 0 0 0 3px var(--ring-color);
      
      /* Specific component colors */
      --toggle-bg: rgba(255, 255, 255, 0.2);
      --toggle-border: rgba(0, 0, 0, 0.1);
      --toggle-hover: rgba(255, 255, 255, 0.3);
    }
    
    /* Dark mode color system */
    [data-theme="dark"] {
      /* Main backgrounds */
      --bg-primary: #111827;
      --bg-secondary: #1f2937;
      --bg-sidebar: #1f2937;
      --bg-navbar: #1f2937;
      --bg-card: #1f2937;
      --bg-input: #374151;
      --bg-dropdown: #374151;
      --bg-tooltip: #6b7280;
      
      /* Text colors */
      --text-primary: #f9fafb;
      --text-secondary: #d1d5db;
      --text-muted: #9ca3af;
      --text-on-accent: #ffffff;
      --text-on-danger: #ffffff;
      
      /* Borders & Dividers */
      --border-color: #374151;
      --border-dark-color: #4b5563;
      --divider-color: #374151;
      
      /* Status colors */
      --color-success: #10b981;
      --color-success-light: #064e3b;
      --color-warning: #f59e0b;
      --color-warning-light: #78350f;
      --color-danger: #ef4444;
      --color-danger-light: #7f1d1d;
      --color-info: #3b82f6;
      --color-info-light: #1e3a8a;
      
      /* Accent/brand colors - keep the main green but adjust the light version */
      --color-accent: #10b981;
      --color-accent-hover: #059669;
      --color-accent-light: #064e3b;
      
      /* Other UI elements */
      --shadow-color: rgba(0, 0, 0, 0.3);
      --ring-color: rgba(16, 185, 129, 0.7);
      
      /* Specific component colors */
      --toggle-bg: rgba(0, 0, 0, 0.2);
      --toggle-border: rgba(255, 255, 255, 0.1);
      --toggle-hover: rgba(0, 0, 0, 0.3);
    }
    
    /* Apply theme colors to base elements */
    body {
      background-color: var(--bg-primary);
      color: var(--text-primary);
      transition: all 0.3s ease;
    }
    
    /* Fix for layout container and sidebar/content positioning */
    .layout-container {
      display: flex;
      width: 100%;
      position: relative;
      overflow: hidden;
      flex: 1;
      height: 100%;
    }
    
    /* Sidebar */
    .sidebar {
      background-color: var(--bg-sidebar);
      border-right: 1px solid var(--border-color);
      width: 240px; /* Fixed width for expanded state - adjusted from 16rem */
      height: 100%;
      z-index: 30;
      left: 0;
      top: 0;
      flex-shrink: 0;
      transition: width 0.3s ease;
      overflow-x: hidden; /* Always hide horizontal scrollbar */
      scrollbar-width: thin; /* For Firefox */
      will-change: width;
      position: relative;
    }
    
    @media (min-width: 768px) {
      .sidebar {
        position: relative;
      }
    }
    
    /* Content area */
    .content-area {
      background-color: var(--bg-primary);
    }
    
    /* Cards */
    .card {
      background-color: var(--bg-card);
      border: 1px solid var(--border-color);
      box-shadow: 0 1px 3px var(--shadow-color);
    }
    
    /* Status indicators */
    .status-success { 
      background-color: var(--color-success-light); 
      color: var(--color-success);
    }
    .status-warning { 
      background-color: var(--color-warning-light); 
      color: var(--color-warning);
    }
    .status-danger { 
      background-color: var(--color-danger-light); 
      color: var(--color-danger);
    }
    .status-info { 
      background-color: var(--color-info-light); 
      color: var(--color-info);
    }
    
    /* Theme toggle button */
    .theme-toggle-login {
        position: relative;
        width: 36px;
        height: 36px;
        background: var(--bg-secondary);
        border-radius: 50%;
        border: 1px solid var(--border-color);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease, border-color 0.3s ease;
        padding: 0;
    }

    .theme-toggle-login:hover {
        background: var(--bg-dropdown);
        border-color: var(--border-dark-color);
    }

    .theme-toggle-login i {
        font-size: 16px;
        position: absolute;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .theme-toggle-login .fa-sun { color: var(--color-warning); }
    .theme-toggle-login .fa-moon { color: var(--color-info); }

    .theme-toggle-login .fa-sun {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
    .theme-toggle-login .fa-moon {
        opacity: 0;
        transform: scale(0.5) rotate(-90deg);
    }

    .theme-toggle-login.active .fa-sun {
        opacity: 0;
        transform: scale(0.5) rotate(90deg);
    }
    .theme-toggle-login.active .fa-moon {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
    
    /* Navigation links */
    .nav-link {
      transition: all 0.2s ease;
      color: var(--text-secondary);
    }
    
    .nav-link:hover {
      background-color: var(--color-accent);
      color: var(--text-on-accent) !important;
    }
    
    .sidebar .nav-link.active {
      background-color: var(--color-accent) !important;
      color: var(--text-on-accent) !important;
      font-weight: 500 !important;
      border-left: 3px solid var(--color-accent-hover) !important;
    }
    
    /* Active state for submenu items */
    .sidebar #manage-submenu .nav-link.active,
    .sidebar #reports-submenu .nav-link.active {
      background-color: var(--color-accent) !important;
      color: var(--text-on-accent) !important;
      font-weight: 500 !important;
      border-left: 3px solid var(--color-accent-hover) !important;
      margin-left: -3px !important;
    }
    
    /* Force active colors */
    .nav-link.active .nav-icon {
      color: var(--text-on-accent) !important;
    }
    
    /* Headings and text */
    h1, h2, h3, h4, h5, h6 {
      color: var(--text-primary);
    }
    
    .text-muted {
      color: var(--text-muted);
    }
    
    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.5rem 0.9rem;
      border-radius: 0.6rem; /* smoother corners */
      border: 1px solid var(--border-color);
      background-color: var(--bg-card);
      color: var(--text-primary);
      transition: transform 0.05s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
      box-shadow: 0 1px 2px var(--shadow-color);
      cursor: pointer;
      line-height: 1.25rem;
    }

    .btn:hover { box-shadow: 0 2px 6px var(--shadow-color); }
    .btn:active { transform: translateY(1px); }
    .btn:disabled { opacity: 0.6; cursor: not-allowed; }

    .btn-sm { padding: 0.35rem 0.6rem; border-radius: 0.5rem; font-size: 0.85rem; }

    .btn-primary {
      background-color: var(--color-accent);
      border-color: var(--color-accent);
      color: var(--text-on-accent);
    }
    .btn-primary:hover { background-color: var(--color-accent-hover); }

    .btn-secondary {
      background-color: var(--bg-secondary);
      color: var(--text-primary);
      border: 1px solid var(--border-color);
    }
    .btn-secondary:hover { 
      filter: brightness(0.98); 
      background-color: var(--bg-dropdown);
    }
    .btn-secondary i {
      color: var(--text-primary);
    }

    .btn-warning {
      background-color: var(--color-warning);
      border-color: var(--color-warning);
      color: var(--text-on-accent);
    }
    .btn-warning:hover { filter: brightness(0.95); }

    .btn-info {
      background-color: var(--color-info);
      border-color: var(--color-info);
      color: var(--text-on-accent);
    }
    .btn-info:hover { filter: brightness(0.95); }

    .btn-success {
      background-color: var(--color-success);
      border-color: var(--color-success);
      color: var(--text-on-accent);
    }
    .btn-success:hover { filter: brightness(0.95); }

    .btn-danger {
      background-color: var(--color-danger);
      border-color: var(--color-danger);
      color: var(--text-on-danger);
    }
    .btn-danger:hover { filter: brightness(0.95); }

    /* Theme-aware table styling */
    .table-theme thead {
      position: sticky;
      top: 0;
      background-color: var(--bg-card);
      z-index: 1;
    }
    .table-theme thead th {
      color: var(--text-secondary);
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-bottom: 1px solid var(--border-color);
      white-space: nowrap;
    }
    .table-theme tbody td {
      border-top: 1px solid var(--border-color);
    }
    .table-theme tbody tr:hover {
      background-color: var(--bg-secondary);
    }
    .table-zebra tbody tr:nth-child(even) {
      background-color: var(--bg-secondary);
    }
    
    /* Form elements */
    input, select, textarea {
      background-color: var(--bg-input);
      border-color: var(--border-color);
      color: var(--text-primary);
    }
    
    input:focus, select:focus, textarea:focus {
      border-color: var(--color-accent);
      box-shadow: var(--focus-ring);
    }
    
    /* Status icon backgrounds */
    .icon-bg-success {
      background-color: var(--color-success-light);
      color: var(--color-success);
    }
    
    .icon-bg-warning {
      background-color: var(--color-warning-light);
      color: var(--color-warning);
    }
    
    .icon-bg-danger {
      background-color: var(--color-danger-light);
      color: var(--color-danger);
    }
    
    .icon-bg-info {
      background-color: var(--color-info-light);
      color: var(--color-info);
    }
    
    /* Collapsible sidebar styles */
    body.sidebar-collapse .sidebar {
      width: 72px; /* Adjusted from 4.5rem to match the image */
    }
    
    body.sidebar-collapse .sidebar .brand-text,
    body.sidebar-collapse .sidebar .nav-text,
    body.sidebar-collapse .sidebar .menu-header,
    body.sidebar-collapse .sidebar .menu-description {
      visibility: hidden;
      opacity: 0;
      position: absolute;
      transition: visibility 0s 0.3s, opacity 0.3s ease;
    }
    
    body.sidebar-collapse .sidebar .nav-icon {
      margin-right: 0;
      width: 100%;
      text-align: center;
      font-size: 1.25rem;
    }
    
    body.sidebar-collapse .sidebar .nav-link {
      padding: 0.75rem 0;
      justify-content: center;
    }
    
    /* Content wrapper margin adjustments */
    .content-wrapper {
      flex: 1;
      min-width: 0; /* Important for preventing flex item overflow */
      transition: margin-left 0.3s ease;
    }
    
    body.sidebar-collapse .content-wrapper {
      margin-left: 0;
    }
    
    /* Ensure proper content width in all states */
    @media (min-width: 768px) {
      .sidebar {
        position: relative;
      }
      
      .content-wrapper {
        width: calc(100% - 240px);
      }
      
      body.sidebar-collapse .content-wrapper {
        width: calc(100% - 72px);
      }
    }
    
    /* Ensure no gap between sidebar and content in mobile view */
    @media (max-width: 767px) {
      .sidebar {
        position: fixed;
        transform: translateX(-100%);
        z-index: 40; /* Higher z-index to ensure it appears above other elements */
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh; /* Full viewport height */
        width: 85%; /* Slightly narrower on mobile for better usability */
        max-width: 280px; /* Maximum width */
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
      }
      
      .sidebar.open {
        transform: translateX(0);
      }
      
      .overlay {
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 35; /* Just below sidebar */
      }
      
      .overlay.active {
        opacity: 1;
      }
      
      /* Ensure content doesn't scroll when sidebar is open */
      body.sidebar-open {
        overflow: hidden;
      }
      
      /* Make sidebar toggle button more touch-friendly */
      #mobile-menu-button {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
      }
      
      #mobile-menu-button:active {
        background-color: var(--bg-secondary);
      }
    }
    
    /* Improved mobile touch target sizes */
    @media (max-width: 767px) {
      .nav-link {
        padding: 0.875rem 1rem; /* Larger padding for touch targets */
      }
      
      .nav-icon {
        font-size: 1.25rem; /* Larger icons on mobile */
      }
    }
    
    /* Enhanced mobile sidebar overlay */
    .overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 35;
      opacity: 0;
      transition: opacity 0.3s ease;
      touch-action: none; /* Prevent scroll on overlay */
    }
    
    .overlay.active {
      display: block;
      opacity: 1;
    }
    
    /* Ensure sidebar has proper z-index and touch handling */
    @media (max-width: 767px) {
      .sidebar {
        touch-action: pan-y; /* Allow vertical scrolling in sidebar */
      }
      
      /* Improved touch targets for mobile */
      .nav-link, #mobile-menu-button, .overlay {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent; /* Remove tap highlight on iOS */
      }
    }
    
    /* Mobile specific improvements */
    @media (max-width: 767px) {
      /* Fix for flickering colors on theme change */
      body, .sidebar, .card, header, footer, main {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
      }
      
      /* Ensure overlay covers entire screen */
      .overlay.active {
        display: block !important;
        opacity: 1 !important;
      }
      
      /* Fix gradient rendering issues for cards */
      .icon-bg-success, .icon-bg-warning, .icon-bg-danger, .icon-bg-info {
        background-image: none !important; /* Remove any gradients that might cause issues */
      }
      
      /* Ensure proper fixed positioning for sidebar */
      .sidebar {
        position: fixed !important;
        height: 100% !important;
      }
      
      /* Improve sidebar scroll handling */
      .sidebar.open {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
      }
      
      /* Force hardware acceleration for mobile animations */
      .sidebar.open, .overlay.active {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
      }
    }
    
    /* Adjust overlay to ensure it's visible on all devices */
    .overlay {
      background-color: rgba(0, 0, 0, 0.5) !important;
      opacity: 0;
      pointer-events: none;
    }
    
    .overlay.active {
      pointer-events: auto;
    }
    
    /* Custom scrollbar for main content area */
    .content-area {
      scrollbar-width: thin;
      scrollbar-color: var(--color-accent-light) var(--bg-secondary);
    }
    
    .content-area::-webkit-scrollbar {
      width: 8px;
    }
    
    .content-area::-webkit-scrollbar-track {
      background: var(--bg-secondary);
      border-radius: 4px;
      margin: 4px 0;
    }
    
    .content-area::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, var(--color-accent-light), var(--color-accent));
      border-radius: 4px;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .content-area::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, var(--color-accent), var(--color-accent-hover));
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
      transform: scaleX(1.1);
    }
    
    .content-area::-webkit-scrollbar-thumb:active {
      background: var(--color-accent-hover);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    /* Custom scrollbar corner */
    .content-area::-webkit-scrollbar-corner {
      background: var(--bg-secondary);
    }
    
    /* Smooth scroll behavior */
    .content-area {
      scroll-behavior: smooth;
    }
    
    /* Hide scrollbar on mobile for cleaner look */
    @media (max-width: 768px) {
      .content-area::-webkit-scrollbar {
        width: 4px;
      }
      
      .content-area::-webkit-scrollbar-thumb {
        background: var(--color-accent);
        border: none;
      }
    }

    .badge-dot { position: absolute; top: -2px; right: -2px; width: 10px; height: 10px; border-radius: 9999px; background: #ef4444; }
    .notify-badge { 
      position: absolute; 
      top: -6px; 
      right: -6px; 
      background: var(--color-danger); 
      color: var(--text-on-danger); 
      font-size: 10px; 
      line-height: 1; 
      padding: 2px 5px; 
      border-radius: 9999px;
      border: 1px solid var(--bg-navbar);
    }
    
    /* Notification panel styles */
    .notification-item {
      transition: background-color 0.2s ease;
    }
    .notification-item:hover {
      background-color: var(--bg-secondary) !important;
    }
    .notification-icon-warning {
      color: var(--color-warning);
    }
    .notification-icon-flood {
      color: var(--color-info);
    }
    .notification-icon-info {
      color: var(--text-muted);
    }
  </style>
</head>
<body class="h-screen flex flex-col" id="body-container">
  <!-- Global Loading overlay -->
  <div id="global-loading" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
      <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
      <span class="text-lg">กำลังประมวลผล...</span>
    </div>
  </div>
  <!-- Mobile menu overlay -->
  <div id="overlay" class="overlay"></div>
  
  <div class="layout-container">
    <!-- Sidebar - Fixed width, proper positioning -->
    <aside id="sidebar" class="sidebar overflow-y-auto">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700" style="border-color: var(--border-color);">
        <h1 class="text-xl font-bold brand-text" style="color: var(--color-accent);">ระบบแอดมิน</h1>
        <p class="text-sm text-muted menu-description">จัดการอุปกรณ์ดินถล่ม</p>
      </div>
      
      <nav class="mt-4">
        <div class="px-4 mb-3 menu-header">
          <p class="text-xs uppercase tracking-wider text-muted">เมนูหลัก</p>
        </div>
        <ul>
          <li class="px-2">
            <a href="/page" class="nav-link flex items-center px-4 py-3 rounded-md" data-page="home">
              <i class="fas fa-home nav-icon w-5 text-center mr-2 text-muted"></i>
              <span class="nav-text">หน้าแรก</span>
            </a>
          </li>
          <li class="px-2">
            <a href="/dashbroad" class="nav-link flex items-center px-4 py-3 rounded-md" data-page="dashboard">
              <i class="fas fa-tachometer-alt nav-icon w-5 text-center mr-2 text-muted"></i>
              <span class="nav-text">แดชบอร์ด</span>
            </a>
          </li>
          <li class="px-2">
            <a href="/gallery" class="nav-link flex items-center px-4 py-3 rounded-md" data-page="gallery">
              <i class="fas fa-images nav-icon w-5 text-center mr-2 text-muted"></i>
              <span class="nav-text">แกลเลอรี่</span>
            </a>
          </li>
          
          <!-- Reports dropdown -->
          <li class="px-2 mt-1">
            <button id="reports-toggle" class="nav-link w-full flex items-center px-4 py-3 rounded-md justify-between">
              <span class="flex items-center">
                <i class="fas fa-chart-bar nav-icon w-5 text-center mr-2 text-muted"></i>
                <span class="nav-text">รายงาน</span>
              </span>
              <i class="fas fa-chevron-down text-sm transition-transform duration-200" id="reports-caret"></i>
            </button>
            <ul id="reports-submenu" class="mt-1 ml-8 space-y-1 hidden">
              <li>
                <a href="/location_anlysis" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="location_anlysis">
                  <i class="fas fa-chart-line nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">วิเคราะห์ข้อมูลตามโลเคชัน</span>
                </a>
              </li>
              <li>
                <a href="/environment-analysis" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="environment-analysis">
                  <i class="fas fa-leaf nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">รายงานสภาพแวดล้อม</span>
                </a>
              </li>
              <li>
                <a href="/alert-summary" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="alert-summary">
                  <i class="fas fa-exclamation-triangle nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">สรุปการแจ้งเตือน</span>
                </a>
              </li>
              <li>
                <a href="/export-data" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="export-data">
                  <i class="fas fa-download nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">ส่งออกข้อมูล</span>
                </a>
              </li>
            </ul>
          </li>

          <?php if (isset($_SESSION['role']) && (int)$_SESSION['role'] === 1): ?>
          <!-- Management dropdown (admin only) -->
          <li class="px-2 mt-1">
            <button id="manage-toggle" class="nav-link w-full flex items-center px-4 py-3 rounded-md justify-between">
              <span class="flex items-center">
                <i class="fas fa-tools nav-icon w-5 text-center mr-2 text-muted"></i>
                <span class="nav-text">จัดการ</span>
              </span>
              <i class="fas fa-chevron-down text-sm transition-transform duration-200" id="manage-caret"></i>
            </button>
            <ul id="manage-submenu" class="mt-1 ml-8 space-y-1 hidden">
              <li>
                <a href="/device" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="device">
                  <i class="fas fa-sitemap nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">อุปกรณ์เซนเซอร์</span>
                </a>
              </li>
              <li>
                <a href="/location" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="location">
                  <i class="fas fa-map-location-dot nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">ตำแหน่ง (Location)</span>
                </a>
              </li>
              <li>
                <a href="/users" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="users">
                  <i class="fas fa-users nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">ผู้ใช้</span>
                </a>
              </li>
              <li>
                <a href="/policy-management" class="nav-link flex items-center px-3 py-2 rounded-md" data-page="policy-management">
                  <i class="fas fa-file-contract nav-icon w-5 text-center mr-2 text-muted"></i>
                  <span class="nav-text">จัดการข้อตกลง</span>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <li class="px-2">
            <a href="/profile" class="nav-link flex items-center px-4 py-3 rounded-md" data-page="profile">
              <i class="fas fa-user nav-icon w-5 text-center mr-2 text-muted"></i>
              <span class="nav-text">โปรไฟล์</span>
            </a>
          </li>
          <li class="px-2 mt-1">
            <a href="logout" class="nav-link flex items-center px-4 py-3 rounded-md hover:bg-red-500">
              <i class="fas fa-sign-out-alt nav-icon w-5 text-center mr-2 text-muted"></i>
              <span class="nav-text">ออกจากระบบ</span>
            </a>
          </li>
        </ul>
      </nav>
    </aside>
    
    <!-- Main content - flexbox to fill remaining space -->
    <div class="content-wrapper flex flex-col overflow-hidden">
      <!-- Navbar -->
      <header style="background-color: var(--bg-navbar); border-bottom: 1px solid var(--border-color);" class="shadow-sm">
        <div class="px-4 sm:px-6 py-3 flex justify-between items-center">
          <div class="flex items-center">
            <!-- Mobile menu button -->
            <button id="mobile-menu-button" class="md:hidden text-muted focus:outline-none">
              <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- Sidebar toggle button (desktop) - adjusted positioning and styling -->
            <button id="sidebar-toggle-btn" class="hidden md:flex sidebar-toggle">
              <i class="fas fa-bars"></i>
            </button>
            
            <!-- Page title - added to balance layout -->
            <h2 class="ml-3 font-medium hidden md:block">ระบบจัดการอุปกรณ์ดินถล่ม</h2>
          </div>
          
          <div class="flex items-center space-x-3 relative">
            <button id="theme-toggle-login-btn" class="theme-toggle-login">
              <i class="fas fa-sun"></i>
              <i class="fas fa-moon"></i>
            </button>
            
            <!-- Notification Bell -->
            <div class="relative">
              <button id="notify-bell" class="relative btn btn-secondary">
                <i class="fas fa-bell"></i>
                <span id="notify-badge" class="notify-badge hidden">0</span>
              </button>
              <div id="notify-panel" class="hidden absolute right-0 mt-2 w-80 rounded-lg shadow-lg z-50" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
                <div class="p-3 border-b" style="border-color: var(--border-color);">
                  <div class="flex items-center justify-between">
                    <span class="font-medium" style="color: var(--text-primary);">การแจ้งเตือน</span>
                    <button id="notify-mark-all" class="text-xs hover:underline" style="color: var(--color-info);">ทำเครื่องหมายว่าอ่านแล้ว</button>
                  </div>
                </div>
                <div id="notify-list" class="max-h-80 overflow-auto"></div>
                <div class="p-2 text-center text-xs" style="color: var(--text-muted);">อัปเดตอัตโนมัติ</div>
              </div>
            </div>

            <div class="relative">
              <button class="flex items-center space-x-2 focus:outline-none px-3 py-2">
                <span class="text-sm font-medium" style="color: var(--text-primary);"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'ผู้ใช้งาน'; ?></span>
                <img class="h-8 w-8 rounded-full object-cover flex-shrink-0" src="https://ui-avatars.com/api/?name=Admin&background=10b981&color=fff" alt="Profile">
              </button>
            </div>
          </div>
        </div>
      </header>
      <script>
        // Sidebar Manage dropdown behavior
        (function() {
          try {
            var toggle = document.getElementById('manage-toggle');
            var submenu = document.getElementById('manage-submenu');
            var caret = document.getElementById('manage-caret');
            if (toggle && submenu && caret) {
              toggle.addEventListener('click', function() {
                var isHidden = submenu.classList.contains('hidden');
                if (isHidden) {
                  submenu.classList.remove('hidden');
                  caret.style.transform = 'rotate(180deg)';
                } else {
                  submenu.classList.add('hidden');
                  caret.style.transform = '';
                }
              });

              // Auto open if current page is one of the submenu items
              var current = document.querySelector('.sidebar a.nav-link.active[data-page="device"], .sidebar a.nav-link.active[data-page="location"], .sidebar a.nav-link.active[data-page="users"], .sidebar a.nav-link.active[data-page="policy-management"]');
              if (current) {
                submenu.classList.remove('hidden');
                caret.style.transform = 'rotate(180deg)';
              }
            }
          } catch(e) { /* no-op */ }
        })();

        // Sidebar Reports dropdown behavior
        (function() {
          try {
            var toggle = document.getElementById('reports-toggle');
            var submenu = document.getElementById('reports-submenu');
            var caret = document.getElementById('reports-caret');
            if (toggle && submenu && caret) {
              toggle.addEventListener('click', function() {
                var isHidden = submenu.classList.contains('hidden');
                if (isHidden) {
                  submenu.classList.remove('hidden');
                  caret.style.transform = 'rotate(180deg)';
                } else {
                  submenu.classList.add('hidden');
                  caret.style.transform = '';
                }
              });

              // Auto open if current page is one of the reports submenu items
              var current = document.querySelector('.sidebar a.nav-link.active[data-page="location_anlysis"], .sidebar a.nav-link.active[data-page="environment-analysis"], .sidebar a.nav-link.active[data-page="device-performance"], .sidebar a.nav-link.active[data-page="alert-summary"], .sidebar a.nav-link.active[data-page="location-compare"], .sidebar a.nav-link.active[data-page="export-data"]');
              if (current) {
                submenu.classList.remove('hidden');
                caret.style.transform = 'rotate(180deg)';
              }
            }
          } catch(e) { /* no-op */ }
        })();
        
        // Global loading controller available to all pages
        window.showGlobalLoading = function(show) {
          var el = document.getElementById('global-loading');
          if (!el) return;
          if (show) {
            el.classList.remove('hidden');
            el.classList.add('flex');
          } else {
            el.classList.add('hidden');
            el.classList.remove('flex');
          }
        };

        // Notifications polling
        (function(){
          const bell = document.getElementById('notify-bell');
          const badge = document.getElementById('notify-badge');
          const panel = document.getElementById('notify-panel');
          const list = document.getElementById('notify-list');
          const markAllBtn = document.getElementById('notify-mark-all');
          const LS_KEY = 'notify_last_read_id';

          function fmtDate(ts){ try { return new Date(ts.replace(' ', 'T')).toLocaleString('th-TH'); } catch(e){ return ts; } }
          function typeIcon(t){ 
            t = parseInt(t||0,10); 
            if(t===1) return '<i class="fas fa-exclamation-triangle notification-icon-warning"></i>'; 
            if(t===2) return '<i class="fas fa-water notification-icon-flood"></i>'; 
            return '<i class="fas fa-info-circle notification-icon-info"></i>'; 
          }

          function render(items){
            list.innerHTML = '';
            if(!items || items.length===0){ list.innerHTML = '<div class="p-3 text-sm" style="color: var(--text-muted);">ไม่มีการแจ้งเตือน</div>'; return; }
            items.forEach(n => {
              const isUnread = (getLastReadId() < parseInt(n.notification_id,10));
              const el = document.createElement('div');
              el.className = `px-3 py-2 border-b notification-item`;
              el.style.borderColor = 'var(--border-color)';
              if (isUnread) {
                el.style.backgroundColor = 'var(--color-warning-light)';
              }
              el.innerHTML = `
                <div class="flex items-start gap-2">
                  <div class="mt-1">${typeIcon(n.type)}</div>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm" style="color: var(--text-primary);">${(n.text||'').replace(/</g,'&lt;')}</div>
                    <div class="text-xs" style="color: var(--text-muted);">อุปกรณ์: ${n.device_name||n.device_id||'-'} • พื้นที่: ${n.location_name||n.location_id||'-'} • ${fmtDate(n.create_at||'')}</div>
                  </div>
                </div>`;
              el.addEventListener('click', () => setLastReadId(Math.max(getLastReadId(), parseInt(n.notification_id,10))));
              list.appendChild(el);
            });
          }

          function getLastReadId(){ return parseInt(localStorage.getItem(LS_KEY)||'0',10); }
          function setLastReadId(id){ localStorage.setItem(LS_KEY, String(id)); updateBadge(lastData); }

          let lastData = [];
          function updateBadge(items){
            const maxId = items.reduce((m, n) => Math.max(m, parseInt(n.notification_id||0,10)), 0);
            const unread = Math.max(0, maxId - getLastReadId());
            if(unread > 0){ badge.textContent = unread > 99 ? '99+' : String(unread); badge.classList.remove('hidden'); } else { badge.classList.add('hidden'); }
          }

          async function fetchNotifications(){
            try {
              const res = await fetch('/api/notification/check.php');
              const data = await res.json();
              lastData = Array.isArray(data.data) ? data.data : [];
              render(lastData);
              updateBadge(lastData);
            } catch(e){ /* ignore */ }
          }

          if (bell) {
            bell.addEventListener('click', () => { panel.classList.toggle('hidden'); });
            document.addEventListener('click', (ev) => { if (!ev.target.closest('#notify-panel') && !ev.target.closest('#notify-bell')) { panel.classList.add('hidden'); } });
          }
          if (markAllBtn) { markAllBtn.addEventListener('click', () => { const maxId = lastData.reduce((m, n) => Math.max(m, parseInt(n.notification_id||0,10)), 0); setLastReadId(maxId); notify('ทำเครื่องหมายว่าอ่านแล้ว', 'success'); }); }
          fetchNotifications();
          setInterval(fetchNotifications, 15000);
        })();

        // SweetAlert2 Helper Functions
        window.SwalToast = Swal.mixin({
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

        window.notify = function(message, type = 'info') {
          const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
          };
          
          const icon = iconMap[type] || 'info';
          
          window.SwalToast.fire({
            icon: icon,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        };

        // Navigation active state handler
        document.addEventListener('DOMContentLoaded', function() {
          try {
            // Get current path from URL
            var currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');
            
            // Handle root path
            if (currentPath === '' || currentPath === 'index.php') {
              currentPath = 'page';
            }
            
            // Map of paths to data-page attributes
            var pathMap = {
              'page': 'home',
              'index.php': 'home',
              'dashbroad': 'dashboard',
              'dashbroad.php': 'dashboard',
              'gallery': 'gallery',
              'gallery.php': 'gallery',
              'location_anlysis': 'location_anlysis',
              'location_anlysis.php': 'location_anlysis',
              'environment-analysis': 'environment-analysis',
              'environment-analysis.php': 'environment-analysis',
              'alert-summary': 'alert-summary',
              'alert-summary.php': 'alert-summary',
              'export-data': 'export-data',
              'export-data.php': 'export-data',
              'device': 'device',
              'device.php': 'device',
              'location': 'location',
              'location.php': 'location',
              'users': 'users',
              'users.php': 'users',
              'policy-management': 'policy-management',
              'policy-management.php': 'policy-management',
              'profile': 'profile',
              'profile.php': 'profile'
            };

            console.log('Current path:', currentPath); // Debug log

            // Remove all active classes first
            var allLinks = document.querySelectorAll('.nav-link');
            allLinks.forEach(function(link) {
              link.classList.remove('active');
            });

            // Set active class for current page
            var dataPage = pathMap[currentPath];
            console.log('Data page:', dataPage); // Debug log
            
            if (dataPage) {
              var activeLink = document.querySelector('.nav-link[data-page="' + dataPage + '"]');
              console.log('Active link found:', activeLink); // Debug log
              
              if (activeLink) {
                activeLink.classList.add('active');
                console.log('Added active class to:', activeLink); // Debug log
                
                // If it's a submenu item, also open the parent dropdown
                var parentSubmenu = activeLink.closest('#manage-submenu, #reports-submenu');
                if (parentSubmenu) {
                  parentSubmenu.classList.remove('hidden');
                  
                  // Rotate the appropriate caret
                  if (parentSubmenu.id === 'manage-submenu') {
                    var manageCaret = document.getElementById('manage-caret');
                    if (manageCaret) manageCaret.style.transform = 'rotate(180deg)';
                  } else if (parentSubmenu.id === 'reports-submenu') {
                    var reportsCaret = document.getElementById('reports-caret');
                    if (reportsCaret) reportsCaret.style.transform = 'rotate(180deg)';
                  }
                }
              }
            }
          } catch(e) {
            console.log('Navigation active state error:', e);
          }
        });

        // Immediate active state handler (backup)
        setTimeout(function() {
          try {
            var currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');
            if (currentPath === '' || currentPath === 'index.php') currentPath = 'page';
            
            var pathMap = {
              'page': 'home', 'index.php': 'home',
              'dashbroad': 'dashboard', 'dashbroad.php': 'dashboard',
              'gallery': 'gallery', 'gallery.php': 'gallery',
              'device': 'device', 'device.php': 'device',
              'location': 'location', 'location.php': 'location',
              'users': 'users', 'users.php': 'users',
              'policy-management': 'policy-management', 'policy-management.php': 'policy-management',
              'profile': 'profile', 'profile.php': 'profile',
              'location_anlysis': 'location_anlysis', 'location_anlysis.php': 'location_anlysis',
              'environment-analysis': 'environment-analysis', 'environment-analysis.php': 'environment-analysis',
              'alert-summary': 'alert-summary', 'alert-summary.php': 'alert-summary',
              'export-data': 'export-data', 'export-data.php': 'export-data'
            };

            var dataPage = pathMap[currentPath];
            if (dataPage) {
              var activeLink = document.querySelector('.nav-link[data-page="' + dataPage + '"]');
              if (activeLink && !activeLink.classList.contains('active')) {
                // Remove all active first
                document.querySelectorAll('.nav-link.active').forEach(function(link) {
                  link.classList.remove('active');
                });
                
                activeLink.classList.add('active');
                
                // Handle submenu
                var parentSubmenu = activeLink.closest('#manage-submenu, #reports-submenu');
                if (parentSubmenu) {
                  parentSubmenu.classList.remove('hidden');
                  if (parentSubmenu.id === 'manage-submenu') {
                    var caret = document.getElementById('manage-caret');
                    if (caret) caret.style.transform = 'rotate(180deg)';
                  } else if (parentSubmenu.id === 'reports-submenu') {
                    var caret = document.getElementById('reports-caret');
                    if (caret) caret.style.transform = 'rotate(180deg)';
                  }
                }
              }
            }
          } catch(e) {}
        }, 100);
      </script>