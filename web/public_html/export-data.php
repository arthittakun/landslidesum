<?php
include_once 'plugin/header.php';
?>

<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2" style="color: var(--text-primary);">
            <i class="fas fa-download mr-2 text-blue-600"></i>ส่งออกข้อมูล
        </h1>
        <p class="text-muted">ส่งออกข้อมูลในรูปแบบต่างๆ เพื่อนำไปใช้งานต่อ</p>
    </div>

    <!-- Export Form -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Main Export Panel -->
        <div class="xl:col-span-3">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-cog mr-2 text-blue-600"></i>
                        ตัวเลือกการส่งออก
                    </h3>
                </div>
                <div class="card-body p-6">
                    <form id="exportForm" class="space-y-8">
                        <!-- Data Type Selection -->
                        <div>
                            <label class="block text-sm font-semibold mb-4" style="color: var(--text-primary);">
                                <i class="fas fa-database mr-2 text-blue-600"></i>เลือกประเภทข้อมูล
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="export-option border-2 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer text-center selected" data-type="environment">
                                    <div class="text-3xl text-green-500 mb-3">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <h4 class="font-semibold mb-1" style="color: var(--text-primary);">ข้อมูลสิ่งแวดล้อม</h4>
                                    <p class="text-xs text-muted">อุณหภูมิ, ความชื้น, ฝน</p>
                                </div>
                                <div class="export-option border-2 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer text-center" data-type="alerts">
                                    <div class="text-3xl text-orange-500 mb-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h4 class="font-semibold mb-1" style="color: var(--text-primary);">การแจ้งเตือน</h4>
                                    <p class="text-xs text-muted">ดินถล่ม, น้ำท่วม</p>
                                </div>
                                <div class="export-option border-2 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer text-center" data-type="devices">
                                    <div class="text-3xl text-purple-500 mb-3">
                                        <i class="fas fa-microchip"></i>
                                    </div>
                                    <h4 class="font-semibold mb-1" style="color: var(--text-primary);">อุปกรณ์</h4>
                                    <p class="text-xs text-muted">รายการเซ็นเซอร์</p>
                                </div>
                                <div class="export-option border-2 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer text-center" data-type="summary">
                                    <div class="text-3xl text-blue-500 mb-3">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <h4 class="font-semibold mb-1" style="color: var(--text-primary);">สรุปทั้งหมด</h4>
                                    <p class="text-xs text-muted">รายงานครบถ้วน</p>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range Filters -->
                        <div class="border-t pt-6" style="border-color: var(--border-color);">
                            <label class="block text-sm font-semibold mb-4" style="color: var(--text-primary);">
                                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>ช่วงวันที่
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm mb-2 text-muted">วันที่เริ่มต้น</label>
                                    <input type="date" id="startDate" name="start_date" class="form-input w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm mb-2 text-muted">วันที่สิ้นสุด</label>
                                    <input type="date" id="endDate" name="end_date" class="form-input w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Filters -->
                        <div class="border-t pt-6" style="border-color: var(--border-color);">
                            <label class="block text-sm font-semibold mb-4" style="color: var(--text-primary);">
                                <i class="fas fa-filter mr-2 text-blue-600"></i>ตัวกรองเพิ่มเติม
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm mb-2 text-muted">อุปกรณ์</label>
                                    <select id="deviceFilter" name="device_id" class="form-select w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">เลือกทั้งหมด</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm mb-2 text-muted">สถานที่</label>
                                    <select id="locationFilter" name="location_id" class="form-select w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">เลือกทั้งหมด</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm mb-2 text-muted">จำนวนแถว</label>
                                    <select id="limitFilter" name="limit" class="form-select w-full px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">ทั้งหมด</option>
                                        <option value="100">100 แถว</option>
                                        <option value="500">500 แถว</option>
                                        <option value="1000">1,000 แถว</option>
                                        <option value="5000">5,000 แถว</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Format Selection -->
                        <div class="border-t pt-6" style="border-color: var(--border-color);">
                            <label class="block text-sm font-semibold mb-4" style="color: var(--text-primary);">
                                <i class="fas fa-file-export mr-2 text-blue-600"></i>รูปแบบไฟล์
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="export-option border-2 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer text-center" data-format="csv">
                                    <div class="text-2xl text-green-500 mb-2">
                                        <i class="fas fa-file-csv"></i>
                                    </div>
                                    <h4 class="font-semibold mb-1" style="color: var(--text-primary);">CSV</h4>
                                    <p class="text-xs text-muted">Excel, Google Sheets</p>
                                </div>
                                <div class="export-option border-2 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer text-center" data-format="excel">
                                    <div class="text-2xl text-orange-500 mb-2">
                                        <i class="fas fa-file-excel"></i>
                                    </div>
                                    <h4 class="font-semibold mb-1" style="color: var(--text-primary);">Excel</h4>
                                    <p class="text-xs text-muted">Microsoft Excel</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-t pt-6" style="border-color: var(--border-color);">
                            <div class="flex flex-wrap gap-3">
                                <button type="button" id="exportBtn" class="btn btn-success flex items-center px-6 py-3">
                                    <i class="fas fa-download mr-2"></i>ส่งออกข้อมูล
                                </button>
                                <button type="button" id="resetBtn" class="btn btn-secondary flex items-center px-6 py-3">
                                    <i class="fas fa-redo mr-2"></i>รีเซ็ต
                                </button>
                            </div>
                            
                            <!-- Loading Spinner -->
                            <div id="loadingSpinner" class="hidden mt-4 flex items-center text-blue-600">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                                <span>กำลังประมวลผล...</span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div id="downloadProgress" class="hidden mt-4">
                                <div class="w-full rounded-full h-2.5" style="background-color: var(--bg-secondary);">
                                    <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                                </div>
                                <p class="text-sm text-muted mt-2">กำลังเตรียมไฟล์...</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Export Sidebar -->
        <div class="xl:col-span-1 space-y-6">
            <!-- Quick Export Card -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i>ส่งออกด่วน
                    </h4>
                </div>
                <div class="card-body p-4 space-y-3">
                    <button type="button" class="w-full btn bg-green-50 text-green-700 hover:bg-green-100 quick-export" 
                            data-type="environment" data-format="csv" 
                            style="border: 1px solid var(--border-color); background-color: rgba(34, 197, 94, 0.1);">
                        <i class="fas fa-leaf mr-2"></i>ข้อมูลสิ่งแวดล้อม
                        <span class="text-xs bg-green-100 px-2 py-1 rounded-full ml-2">CSV</span>
                    </button>
                    <button type="button" class="w-full btn bg-orange-50 text-orange-700 hover:bg-orange-100 quick-export" 
                            data-type="alerts" data-format="excel"
                            style="border: 1px solid var(--border-color); background-color: rgba(249, 115, 22, 0.1);">
                        <i class="fas fa-exclamation-triangle mr-2"></i>การแจ้งเตือน
                        <span class="text-xs bg-orange-100 px-2 py-1 rounded-full ml-2">Excel</span>
                    </button>
                    <button type="button" class="w-full btn bg-blue-50 text-blue-700 hover:bg-blue-100 quick-export" 
                            data-type="devices" data-format="csv"
                            style="border: 1px solid var(--border-color); background-color: rgba(59, 130, 246, 0.1);">
                        <i class="fas fa-microchip mr-2"></i>อุปกรณ์
                        <span class="text-xs bg-blue-100 px-2 py-1 rounded-full ml-2">CSV</span>
                    </button>
                </div>
            </div>

            <!-- Export Tips -->
            <div class="card" style="background-color: rgba(251, 191, 36, 0.1); border-color: rgba(251, 191, 36, 0.3);">
                <div class="card-body p-4">
                    <h4 class="text-sm font-semibold mb-3 flex items-center" style="color: #d97706;">
                        <i class="fas fa-lightbulb mr-2"></i>เคล็ดลับ
                    </h4>
                    <ul class="text-xs space-y-2" style="color: #a16207;">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-yellow-600"></i>
                            เลือกประเภทข้อมูลและรูปแบบไฟล์ก่อนส่งออก
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-yellow-600"></i>
                            CSV เหมาะสำหรับ Excel และ Google Sheets
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-yellow-600"></i>
                            Excel รองรับการจัดรูปแบบข้อมูลขั้นสูง
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-yellow-600"></i>
                            ใช้ตัวกรองเพื่อจำกัดข้อมูลตามที่ต้องการ
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Area -->
    <div id="alertArea" class="mt-6"></div>

    <!-- Custom Styles -->
    <style>
        .export-option.selected {
            border-color: #3b82f6 !important;
            background-color: rgb(59 130 246 / 0.1) !important;
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .alert-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        /* Theme support */
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px var(--shadow-color);
            color: var(--text-primary);
        }
        
        .card-header {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .card-body {
            background-color: var(--bg-card);
            color: var(--text-primary);
        }
        
        .text-muted {
            color: var(--text-secondary) !important;
        }
        
        .form-input, .form-select {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .form-input:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .export-option {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .export-option:hover {
            border-color: var(--primary-color);
            background-color: var(--bg-hover);
        }
        
        .quick-export {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .quick-export:hover {
            background-color: var(--bg-hover);
        }
        
        .bg-gradient-to-br {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .border-gray-200 {
            border-color: var(--border-color) !important;
        }
        
        .bg-gray-50 {
            background-color: var(--bg-secondary) !important;
        }
        
        .text-gray-900 {
            color: var(--text-primary) !important;
        }
        
        .text-gray-600 {
            color: var(--text-secondary) !important;
        }
        
        .text-gray-500 {
            color: var(--text-muted) !important;
        }
        
        .bg-white {
            background-color: var(--bg-card) !important;
        }
        
        .shadow-sm {
            box-shadow: 0 1px 2px var(--shadow-color) !important;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        class ExportDataManager {
            constructor() {
                console.log('ExportDataManager constructor called');
                this.selectedType = 'environment';
                this.selectedFormat = 'csv';
                this.init();
            }

            init() {
                console.log('Initializing ExportDataManager...');
                try {
                    this.bindEvents();
                    this.setDefaults();
                    this.loadFilterOptions();
                    console.log('ExportDataManager initialization complete');
                } catch (error) {
                    console.error('Error in init():', error);
                }
            }

            setDefaults() {
                // Set default selections
                document.querySelector('[data-type="environment"]')?.classList.add('selected');
                document.querySelector('[data-format="csv"]')?.classList.add('selected');
                console.log('Default selections set:', this.selectedType, this.selectedFormat);
            }

            bindEvents() {
                // Type selection
                document.querySelectorAll('[data-type]').forEach(option => {
                    option.addEventListener('click', (e) => {
                        document.querySelectorAll('[data-type]').forEach(el => el.classList.remove('selected'));
                        e.currentTarget.classList.add('selected');
                        this.selectedType = e.currentTarget.dataset.type;
                    });
                });

                // Format selection
                document.querySelectorAll('[data-format]').forEach(option => {
                    option.addEventListener('click', (e) => {
                        document.querySelectorAll('[data-format]').forEach(el => el.classList.remove('selected'));
                        e.currentTarget.classList.add('selected');
                        this.selectedFormat = e.currentTarget.dataset.format;
                    });
                });

                // Buttons
                document.getElementById('exportBtn').addEventListener('click', () => this.exportData());
                document.getElementById('resetBtn').addEventListener('click', () => this.resetForm());

                // Quick export buttons
                document.querySelectorAll('.quick-export').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const type = e.currentTarget.dataset.type;
                        const format = e.currentTarget.dataset.format;
                        this.quickExport(type, format);
                    });
                });
            }

            async loadFilterOptions() {
                try {
                    // Load devices
                    const deviceResponse = await fetch('api/device/get.php');
                    const deviceResult = await deviceResponse.json();
                    
                    if (deviceResult.success) {
                        const deviceSelect = document.getElementById('deviceFilter');
                        deviceResult.data.forEach(device => {
                            const option = document.createElement('option');
                            option.value = device.device_id;
                            option.textContent = `${device.device_id} - ${device.device_name || device.name || 'Device'}`;
                            deviceSelect.appendChild(option);
                        });
                    }

                    // Load locations
                    const locationResponse = await fetch('api/location/get.php');
                    const locationResult = await locationResponse.json();
                    
                    if (locationResult.success) {
                        const locationSelect = document.getElementById('locationFilter');
                        locationResult.data.forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.location_id;
                            option.textContent = `${location.location_id} - ${location.location_name || location.name || 'Location'}`;
                            locationSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error loading filter options:', error);
                }
            }

            async exportData() {
                const params = this.getFormParams();
                params.set('format', this.selectedFormat);
                
                console.log('Export params:', params.toString());
                console.log('Selected format:', this.selectedFormat);
                
                this.showLoading(true);
                this.showProgress(true);
                
                try {
                    // For CSV/Excel, direct download
                    console.log('Exporting as', this.selectedFormat);
                    window.open(`api/export/data.php?${params.toString()}`, '_blank');
                    this.showAlert('success', 'กำลังเตรียมไฟล์สำหรับดาวน์โหลด...');
                } catch (error) {
                    console.error('Export error:', error);
                    this.showAlert('error', 'ไม่สามารถส่งออกข้อมูลได้: ' + error.message);
                } finally {
                    this.showLoading(false);
                    this.showProgress(false);
                }
            }

            async quickExport(type, format) {
                const url = `api/export/data.php?type=${type}&format=${format}`;
                window.open(url, '_blank');
            }

            getFormParams() {
                const params = new URLSearchParams();
                params.set('type', this.selectedType);
                
                const form = document.getElementById('exportForm');
                const formData = new FormData(form);
                
                for (const [key, value] of formData.entries()) {
                    if (value) {
                        params.set(key, value);
                    }
                }
                
                return params;
            }

            downloadFile(content, filename, contentType) {
                const blob = new Blob([content], { type: contentType });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            }

            showLoading(show) {
                const spinner = document.getElementById('loadingSpinner');
                if (spinner) {
                    if (show) {
                        spinner.classList.remove('hidden');
                    } else {
                        spinner.classList.add('hidden');
                    }
                }
            }

            showProgress(show) {
                const progress = document.getElementById('downloadProgress');
                if (progress) {
                    if (show) {
                        progress.classList.remove('hidden');
                        let width = 0;
                        const progressBar = document.getElementById('progressBar');
                        const interval = setInterval(() => {
                            width += Math.random() * 20;
                            if (width >= 90) {
                                width = 90;
                                clearInterval(interval);
                            }
                            if (progressBar) {
                                progressBar.style.width = width + '%';
                            }
                        }, 300);
                    } else {
                        progress.classList.add('hidden');
                        const progressBar = document.getElementById('progressBar');
                        if (progressBar) {
                            progressBar.style.width = '0%';
                        }
                    }
                }
            }

            showAlert(type, message) {
                const bgColor = type === 'success' ? 'bg-green-100 text-green-700 border-green-300' : 
                              type === 'error' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-blue-100 text-blue-700 border-blue-300';
                const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
                
                const alertHtml = `
                    <div class="border-l-4 p-4 ${bgColor} rounded-r-md mb-4 alert-fade-in">
                        <div class="flex items-center">
                            <i class="fas fa-${icon} mr-3"></i>
                            <span>${message}</span>
                            <button type="button" class="ml-auto text-gray-500 hover:text-gray-700" onclick="this.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                document.getElementById('alertArea').innerHTML = alertHtml;
                document.getElementById('alertArea').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            resetForm() {
                document.getElementById('exportForm').reset();
                document.querySelectorAll('[data-type]').forEach(el => el.classList.remove('selected'));
                document.querySelectorAll('[data-format]').forEach(el => el.classList.remove('selected'));
                
                // Set defaults
                document.querySelector('[data-type="environment"]').classList.add('selected');
                document.querySelector('[data-format="csv"]').classList.add('selected');
                this.selectedType = 'environment';
                this.selectedFormat = 'csv';
                
                document.getElementById('alertArea').innerHTML = '';
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing ExportDataManager...');
            try {
                new ExportDataManager();
                console.log('ExportDataManager initialized successfully');
            } catch (error) {
                console.error('Error initializing ExportDataManager:', error);
            }
        });
    </script>

</main>

<?php include 'plugin/footer.php'; ?>
