// Initialize AOS
    AOS.init({
      once: true,
      duration: 500
    });
    
    // Improved mobile menu handling with touch support
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const bodyContainer = document.getElementById('body-container');
    const htmlElement = document.documentElement;
    
    // Function to handle mobile sidebar open
    function openMobileSidebar() {
      sidebar.classList.add('open');
      overlay.classList.add('active');
      bodyContainer.classList.add('sidebar-open');
      document.body.style.overflow = 'hidden';
    }
    
    // Function to handle mobile sidebar close
    function closeMobileSidebar() {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
      bodyContainer.classList.remove('sidebar-open');
      document.body.style.overflow = '';
    }
    
    // Mobile menu button event with improved touch handling
    mobileMenuButton.addEventListener('click', function(e) {
      e.preventDefault(); // Prevent any default behavior
      e.stopPropagation(); // Stop event bubbling
      
      if (sidebar.classList.contains('open')) {
        closeMobileSidebar();
      } else {
        overlay.style.display = 'block';
        // Small delay to ensure display change takes effect before animation
        setTimeout(function() {
          openMobileSidebar();
        }, 10);
      }
    });
    
    // More reliable overlay handling
    overlay.addEventListener('click', function(e) {
      e.preventDefault();
      closeMobileSidebar();
    });
    
    // Add touch event for better mobile experience
    overlay.addEventListener('touchend', function(e) {
      e.preventDefault();
      closeMobileSidebar();
    });
    
    // Close sidebar when tapping outside (for iOS)
    document.addEventListener('touchend', function(e) {
      if (sidebar.classList.contains('open') && 
          !sidebar.contains(e.target) && 
          e.target !== mobileMenuButton) {
        closeMobileSidebar();
      }
    });
    
    // Ensure clicking nav links on mobile closes the sidebar
    const mobileNavLinks = document.querySelectorAll('.sidebar .nav-link');
    mobileNavLinks.forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth < 768) {
          closeMobileSidebar();
        }
      });
    });
    
    // Sidebar collapse toggle (desktop) - fixed version
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    
    // Check for saved sidebar state
    const isSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isSidebarCollapsed) {
      bodyContainer.classList.add('sidebar-collapse');
      sidebar.style.overflowY = 'hidden';
    } else {
      sidebar.style.overflowY = 'auto';
    }
    
    // Single event listener for toggle button
    sidebarToggleBtn.addEventListener('click', () => {
      // Temporarily disable scrollbar during transition
      sidebar.style.overflowY = 'hidden';
      bodyContainer.classList.add('sidebar-transitioning');
      htmlElement.classList.add('sidebar-transitioning');
      
      // Toggle sidebar state
      bodyContainer.classList.toggle('sidebar-collapse');
      
      // Save state to localStorage
      const isNowCollapsed = bodyContainer.classList.contains('sidebar-collapse');
      localStorage.setItem('sidebar-collapsed', isNowCollapsed);
      
      // Remove transition classes after animation completes
      setTimeout(() => {
        bodyContainer.classList.remove('sidebar-transitioning');
        htmlElement.classList.remove('sidebar-transitioning');
        
        // Restore appropriate overflow setting
        sidebar.style.overflowY = isNowCollapsed ? 'hidden' : 'auto';
      }, 350);
    });
    
    // Theme toggle
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
    
    // Navigation active state management
    function setActiveNavigation() {
      // Get current path
      const currentPath = window.location.pathname;
      
      // Remove active class from all nav links
      document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        const icon = link.querySelector('.nav-icon');
        if (icon) {
          icon.style.color = 'var(--text-muted)';
        }
      });
      
      // Determine which page we're on and set active state
      let activeLink = null;
      
      // Map paths to nav items
      const pathMap = {
        '/': 'home',
        '/dashbroad': 'dashboard',
        '/dashboard': 'dashboard',
        '/device': 'device',
        '/location': 'location',
        '/docs': 'docs',
        '/profile': 'profile'
      };
      
      // Find matching page
      const pageName = pathMap[currentPath];
      
      if (pageName) {
        activeLink = document.querySelector(`[data-page="${pageName}"]`);
      } else {
        // Handle sub-pages or partial matches
        if (currentPath.includes('/device')) {
          activeLink = document.querySelector('[data-page="device"]');
        } else if (currentPath.includes('/location')) {
          activeLink = document.querySelector('[data-page="location"]');
        } else if (currentPath.includes('/dashboard') || currentPath.includes('/dashbroad')) {
          activeLink = document.querySelector('[data-page="dashboard"]');
        } else if (currentPath.includes('/docs')) {
          activeLink = document.querySelector('[data-page="docs"]');
        } else if (currentPath.includes('/profile')) {
          activeLink = document.querySelector('[data-page="profile"]');
        }
      }
      
      // Set active state
      if (activeLink) {
        activeLink.classList.add('active');
        const icon = activeLink.querySelector('.nav-icon');
        if (icon) {
          icon.style.color = 'var(--color-accent)';
        }
      }
    }

    // Call setActiveNavigation when page loads
    document.addEventListener('DOMContentLoaded', setActiveNavigation);
    
    // Update active navigation on page change (for SPA behavior)
    window.addEventListener('popstate', setActiveNavigation);
    
    // If using pushState for navigation, call setActiveNavigation after URL changes
    const originalPushState = history.pushState;
    history.pushState = function() {
      originalPushState.apply(history, arguments);
      setTimeout(setActiveNavigation, 0);
    };