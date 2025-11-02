<?php
include_once 'plugin/header.php';

// Role-based page guard: only admin (role=1) can access device management
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
  header('Location: dashbroad');
  exit;
}
?>
 <main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">

<div class="mb-6">
          <h1 class="text-2xl font-bold">จัดการอุปกรณ์</h1>
          <p class="text-muted">เพิ่ม แก้ไข ลบ และจัดการอุปกรณ์ตรวจสอบดินถล่ม</p>
        </div>

        <!-- Action Buttons -->
  <div class="flex flex-wrap gap-3 mb-6">
          <button onclick="openAddDeviceModal()" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>เพิ่มอุปกรณ์ใหม่
          </button>
          <button onclick="refreshDeviceList()" class="btn btn-secondary">
            <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
          </button>
          <button onclick="toggleDeletedDevices()" class="btn btn-warning" id="deleted-toggle-btn">
            <i class="fas fa-trash mr-2"></i>ดูอุปกรณ์ที่ลบ
          </button>
          <button onclick="exportDeviceData()" class="btn btn-info">
            <i class="fas fa-download mr-2"></i>ส่งออกข้อมูล
          </button>
        </div>

        <!-- Search and Filter Section -->
  <div class="card rounded-lg p-5 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">ค้นหา</label>
              <div class="relative">
                <input type="text" id="search-input" placeholder="ค้นหาอุปกรณ์..." 
                       class="w-full px-4 py-2 border rounded-lg pl-10" onkeyup="searchDevices()">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">ตำแหน่ง</label>
              <select id="location-filter" class="w-full px-4 py-2 border rounded-lg" onchange="filterDevices()">
                <option value="">ทุกตำแหน่ง</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">สถานะ</label>
              <select id="status-filter" class="w-full px-4 py-2 border rounded-lg" onchange="filterDevices()">
                <option value="">ทุกสถานะ</option>
                <option value="active">ใช้งาน</option>
                <option value="deleted">ลบแล้ว</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">เรียงตาม</label>
              <select id="sort-order" class="w-full px-4 py-2 border rounded-lg" onchange="sortDevices()">
                <option value="device_id">รหัสอุปกรณ์</option>
                <option value="device_name">ชื่ออุปกรณ์</option>
                <option value="location_id">ตำแหน่ง</option>
                <option value="serialno">หมายเลขซีเรียล</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="card rounded-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-muted">อุปกรณ์ทั้งหมด</p>
                <p id="total-devices-stat" class="text-2xl font-bold">-</p>
              </div>
              <div class="icon-bg-primary p-3 rounded-full">
                <i class="fas fa-microchip text-white"></i>
              </div>
            </div>
          </div>
          <div class="card rounded-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-muted">ใช้งานอยู่</p>
                <p id="active-devices-stat" class="text-2xl font-bold text-green-600">-</p>
              </div>
              <div class="icon-bg-success p-3 rounded-full">
                <i class="fas fa-check-circle text-white"></i>
              </div>
            </div>
          </div>
          <div class="card rounded-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-muted">ลบแล้ว</p>
                <p id="deleted-devices-stat" class="text-2xl font-bold text-red-600">-</p>
              </div>
              <div class="icon-bg-danger p-3 rounded-full">
                <i class="fas fa-trash text-white"></i>
              </div>
            </div>
          </div>
          <div class="card rounded-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-muted">ตำแหน่งทั้งหมด</p>
                <p id="locations-count-stat" class="text-2xl font-bold text-blue-600">-</p>
              </div>
              <div class="icon-bg-info p-3 rounded-full">
                <i class="fas fa-map-marker-alt text-white"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Devices Table -->
  <div class="card rounded-lg p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">รายการอุปกรณ์</h3>
            <div class="flex items-center space-x-2">
              <span class="text-sm text-muted">แสดง</span>
              <select id="items-per-page" class="text-sm border rounded px-2 py-1" onchange="changeItemsPerPage()">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="text-sm text-muted">รายการ</span>
            </div>
          </div>

          <!-- Loading indicator -->
          <div id="devices-loading" class="text-center py-8" style="display: none;">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-500"></i>
            <p class="text-muted mt-2">กำลังโหลดข้อมูล...</p>
          </div>

          <!-- Devices table -->
          <div id="devices-table-container" class="overflow-x-auto">
            <table class="w-full text-sm table-theme table-zebra">
              <thead class="sticky top-0" style="background-color: var(--bg-card);">
                <tr class="border-b" style="border-color: var(--border-color);">
                  <th class="text-left py-3 px-4 font-medium">
                    <input type="checkbox" id="select-all-devices" onchange="toggleSelectAllDevices()">
                  </th>
                  <th class="text-left py-3 px-4 font-medium">รหัสอุปกรณ์</th>
                  <th class="text-left py-3 px-4 font-medium">ชื่ออุปกรณ์</th>
                  <th class="text-left py-3 px-4 font-medium">ตำแหน่ง</th>
                  <th class="text-left py-3 px-4 font-medium">หมายเลขซีเรียล</th>
                  <th class="text-left py-3 px-4 font-medium">สถานะ</th>
                  <th class="text-left py-3 px-4 font-medium">การจัดการ</th>
                </tr>
              </thead>
              <tbody id="devices-table-body">
                <!-- Dynamic content will be loaded here -->
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div id="pagination-container" class="flex items-center justify-between mt-4">
            <div class="text-sm text-muted">
              แสดง <span id="showing-range">-</span> จาก <span id="total-items">-</span> รายการ
            </div>
            <div id="pagination-buttons" class="flex space-x-1">
              <!-- Pagination buttons will be generated here -->
            </div>
          </div>
        </div>

        <!-- Bulk Actions (appears when devices are selected) -->
  <div id="bulk-actions-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 shadow-lg rounded-lg p-4 border" style="display: none; background-color: var(--bg-card); border-color: var(--border-color); box-shadow: 0 10px 25px var(--shadow-color); color: var(--text-primary);">
          <div class="flex items-center space-x-4">
            <span id="selected-count" class="text-sm font-medium">เลือก 0 รายการ</span>
            <button onclick="bulkDeleteDevices()" class="btn btn-danger btn-sm">
              <i class="fas fa-trash mr-1"></i>ลบที่เลือก
            </button>
            <button onclick="bulkRestoreDevices()" class="btn btn-success btn-sm" id="bulk-restore-btn" style="display: none;">
              <i class="fas fa-undo mr-1"></i>กู้คืนที่เลือก
            </button>
            <button onclick="clearSelection()" class="btn btn-secondary btn-sm">
              <i class="fas fa-times mr-1"></i>ยกเลิก
            </button>
          </div>
        </div>

        <!-- Add/Edit Device Modal -->
        <div id="device-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
          <div class="rounded-lg p-6 w-full max-w-md mx-4" style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color); color: var(--text-primary);">
            <div class="flex items-center justify-between mb-4">
              <h3 id="modal-title" class="text-lg font-bold" style="color: var(--text-primary);">เพิ่มอุปกรณ์ใหม่</h3>
              <button onclick="closeDeviceModal()" class="hover:opacity-80" style="color: var(--text-secondary);">
                <i class="fas fa-times"></i>
              </button>
            </div>
            
            <form id="device-form" onsubmit="saveDevice(event)">
              <div class="space-y-4">
                <!-- Hidden field for device ID in edit mode -->
                <input type="hidden" id="hidden-device-id" name="device_id">
                
                <div>
                  <label class="block text-sm font-medium mb-2">รหัสอุปกรณ์ <span class="text-red-500">*</span></label>
                  <input type="text" id="device-id" maxlength="4" 
                         class="w-full px-3 py-2 border rounded-lg" 
                         placeholder="เช่น D001" required>
                  <small class="text-muted">ต้องมี 4 ตัวอักษร</small>
                </div>
                
                <div>
                  <label class="block text-sm font-medium mb-2">ชื่ออุปกรณ์ <span class="text-red-500">*</span></label>
                  <input type="text" id="device-name" name="device_name" maxlength="50" 
                         class="w-full px-3 py-2 border rounded-lg" 
                         placeholder="เช่น เซนเซอร์ดินถล่ม 1" required>
                </div>
                
                <div>
                  <label class="block text-sm font-medium mb-2">ตำแหน่ง <span class="text-red-500">*</span></label>
                  <select id="device-location" name="location_id" class="w-full px-3 py-2 border rounded-lg" required>
                    <option value="">เลือกตำแหน่ง</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium mb-2">หมายเลขซีเรียล <span class="text-red-500">*</span></label>
                  <input type="text" id="device-serial" name="serialno" maxlength="5" 
                         class="w-full px-3 py-2 border rounded-lg" 
                         placeholder="เช่น S0001" required>
                  <small class="text-muted">ต้องมี 5 ตัวอักษร</small>
                </div>
              </div>
              
              <div class="flex space-x-3 mt-6">
                <button type="submit" class="btn btn-primary flex-1">
                  <i class="fas fa-save mr-2"></i>บันทึก
                </button>
                <button type="button" onclick="closeDeviceModal()" class="btn btn-secondary flex-1">
                  <i class="fas fa-times mr-2"></i>ยกเลิก
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
          <div class="rounded-lg p-6 w-full max-w-md mx-4" style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color); color: var(--text-primary);">
            <div class="text-center">
              <div id="confirm-icon" class="mx-auto mb-4 w-12 h-12 rounded-full flex items-center justify-center">
                <i class="fas fa-question-circle text-2xl"></i>
              </div>
              <h3 id="confirm-title" class="text-lg font-bold mb-2" style="color: var(--text-primary);">ยืนยันการดำเนินการ</h3>
              <p id="confirm-message" class="mb-6" style="color: var(--text-secondary);">คุณต้องการดำเนินการนี้หรือไม่?</p>
              <div class="flex space-x-3">
                <button id="confirm-yes" class="btn btn-primary flex-1">ยืนยัน</button>
                <button onclick="closeConfirmModal()" class="btn btn-secondary flex-1">ยกเลิก</button>
              </div>
            </div>
          </div>
        </div>
 </main>
        

        <script>
          // Global variables
          let allDevices = [];
          let filteredDevices = [];
          let deletedDevices = [];
          let locations = [];
          let currentPage = 1;
          let itemsPerPage = 25;
          let isEditMode = false;
          let editingDeviceId = null;
          let selectedDevices = new Set();
          let showingDeleted = false;

          // API endpoints
          const API_ENDPOINTS = {
            deviceInsert: '/api/device/insert',
            deviceGet: '/api/device/get',
            deviceUpdate: '/api/device/update',
            deviceDelete: '/api/device/delete',
            deviceRestore: '/api/device/restore',
            deviceStats: '/api/device/stats',
            locationGet: '/api/location/get'
          };

          // Device API class
          class DeviceAPI {
            static async fetchData(endpoint, options = {}) {
              try {
                const response = await fetch(endpoint, options);
                const data = await response.json();
                if (!response.ok) {
                  throw new Error(data.error || 'API request failed');
                }
                return data;
              } catch (error) {
                console.error(`Error with ${endpoint}:`, error);
                throw error;
              }
            }

            static async getAllDevices() {
              return await this.fetchData(API_ENDPOINTS.deviceGet);
            }

            static async getDeviceById(deviceId) {
              const devices = await this.getAllDevices();
              if (devices.success && devices.data) {
                const device = devices.data.find(d => d.device_id === deviceId);
                return { success: true, data: device };
              }
              return { success: false };
            }

            static async searchDevices(keyword) {
              return await this.fetchData(`${API_ENDPOINTS.deviceGet}?search=${encodeURIComponent(keyword)}`);
            }

            static async addDevice(deviceData) {
              const formData = new URLSearchParams();
              Object.keys(deviceData).forEach(key => {
                formData.append(key, deviceData[key]);
              });
              
              return await this.fetchData(API_ENDPOINTS.deviceInsert, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async updateDevice(deviceData) {
              const formData = new URLSearchParams();
              Object.keys(deviceData).forEach(key => {
                formData.append(key, deviceData[key]);
              });
              
              return await this.fetchData(API_ENDPOINTS.deviceUpdate, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async deleteDevice(deviceId, hardDelete = false) {
              const formData = new URLSearchParams();
              formData.append('device_id', deviceId);
              if (hardDelete) formData.append('hard_delete', 'true');
              
              return await this.fetchData(API_ENDPOINTS.deviceDelete, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async restoreDevice(deviceId) {
              const formData = new URLSearchParams();
              formData.append('device_id', deviceId);
              
              return await this.fetchData(API_ENDPOINTS.deviceRestore, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async getDeviceStats() {
              return await this.fetchData(API_ENDPOINTS.deviceStats);
            }

            static async getLocations() {
              return await this.fetchData(API_ENDPOINTS.locationGet);
            }
          }

          // Initialize page
          async function initializePage() {
            try {
              showLoading(true);
              
              // Load all necessary data
              await Promise.all([
                loadDevices(),
                loadLocations(),
                loadDeviceStats()
              ]);
              
              // Setup initial display
              filterDevices();
              populateLocationFilter();
              
              showLoading(false);
            } catch (error) {
              console.error('Error initializing page:', error);
              showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
              showLoading(false);
            }
          }

          // Load devices data
          async function loadDevices() {
            try {
              const response = await DeviceAPI.getAllDevices();
              if (response.success) {
                allDevices = response.data || [];
                
                // Separate active and deleted devices
                const activeDevices = allDevices.filter(device => device.void == 0);
                deletedDevices = allDevices.filter(device => device.void == 1);
                allDevices = activeDevices;
              }
            } catch (error) {
              console.error('Error loading devices:', error);
              allDevices = [];
            }
          }

          // Load locations for dropdown
          async function loadLocations() {
            try {
              const response = await DeviceAPI.getLocations();
              if (response.success) {
                locations = response.data || [];
              }
            } catch (error) {
              console.error('Error loading locations:', error);
              locations = [];
            }
          }

          // Load device statistics
          async function loadDeviceStats() {
            try {
              const response = await DeviceAPI.getDeviceStats();
              if (response.success) {
                const stats = response.data;
                document.getElementById('total-devices-stat').textContent = stats.total_devices || 0;
                document.getElementById('active-devices-stat').textContent = stats.active_devices || 0;
                document.getElementById('deleted-devices-stat').textContent = stats.deleted_devices || 0;
                document.getElementById('locations-count-stat').textContent = Object.keys(stats.devices_by_location || {}).length;
              }
            } catch (error) {
              console.error('Error loading device stats:', error);
            }
          }

          // Populate location filters and dropdown
          function populateLocationFilter() {
            const locationFilter = document.getElementById('location-filter');
            const deviceLocation = document.getElementById('device-location');
            
            // Clear existing options
            locationFilter.innerHTML = '<option value="">ทุกตำแหน่ง</option>';
            deviceLocation.innerHTML = '<option value="">เลือกตำแหน่ง</option>';
            
            locations.forEach(location => {
              // For filter
              const filterOption = document.createElement('option');
              filterOption.value = location.location_id;
              filterOption.textContent = `${location.location_id} - ${location.location_name}`;
              locationFilter.appendChild(filterOption);
              
              // For device form
              const formOption = document.createElement('option');
              formOption.value = location.location_id;
              formOption.textContent = `${location.location_id} - ${location.location_name}`;
              deviceLocation.appendChild(formOption);
            });
          }

          // Filter and search devices
          function filterDevices() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const locationFilter = document.getElementById('location-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            
            let devicesToFilter = showingDeleted ? deletedDevices : allDevices;
            
            filteredDevices = devicesToFilter.filter(device => {
              const dId = String(device.device_id || '').toLowerCase();
              const dName = String(device.device_name || '').toLowerCase();
              const dLoc = String(device.location_id || '').toLowerCase();
              const dSerial = String(device.serialno || '').toLowerCase();
              const matchesSearch = !searchTerm || 
                dId.includes(searchTerm) ||
                dName.includes(searchTerm) ||
                dLoc.includes(searchTerm) ||
                dSerial.includes(searchTerm);
              
              const matchesLocation = !locationFilter || String(device.location_id) === String(locationFilter);
              
              const matchesStatus = !statusFilter || 
                (statusFilter === 'active' && device.void == 0) ||
                (statusFilter === 'deleted' && device.void == 1);
              
              return matchesSearch && matchesLocation && matchesStatus;
            });
            
            currentPage = 1;
            displayDevices();
            updatePagination();
          }

          // Search devices
          function searchDevices() {
            filterDevices();
          }

          // Sort devices
          function sortDevices() {
            const sortBy = document.getElementById('sort-order').value;
            
            filteredDevices.sort((a, b) => {
              const aVal = a[sortBy] || '';
              const bVal = b[sortBy] || '';
              return aVal.localeCompare(bVal);
            });
            
            displayDevices();
          }

          // Display devices in table
          function displayDevices() {
            const tableBody = document.getElementById('devices-table-body');
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const devicesToShow = filteredDevices.slice(startIndex, endIndex);
            
            tableBody.innerHTML = '';
            
            if (devicesToShow.length === 0) {
              tableBody.innerHTML = `
                <tr>
                  <td colspan="7" class="text-center py-8 text-muted">
                    ${filteredDevices.length === 0 ? 'ไม่พบข้อมูลอุปกรณ์' : 'ไม่มีข้อมูลในหน้านี้'}
                  </td>
                </tr>
              `;
              return;
            }
            
            devicesToShow.forEach(device => {
              const row = document.createElement('tr');
              row.className = 'border-b hover:bg-gray-50';
              row.style.borderColor = 'var(--border-color)';
              
              const isDeleted = device.void == 1;
              const statusClass = isDeleted ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
              const statusText = isDeleted ? 'ลบแล้ว' : 'ใช้งาน';
              
              row.innerHTML = `
                <td class="py-3 px-4">
                  <input type="checkbox" class="device-checkbox" value="${device.device_id}" 
                         onchange="toggleDeviceSelection('${device.device_id}')">
                </td>
                <td class="py-3 px-4 font-medium">${device.device_id}</td>
                <td class="py-3 px-4">${device.device_name}</td>
                <td class="py-3 px-4">${device.location_id}</td>
                <td class="py-3 px-4">${device.serialno}</td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusClass}">
                    ${statusText}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="flex space-x-2">
                    ${isDeleted ? `
                      <button onclick="restoreDevice('${device.device_id}')" 
                              class="text-green-600 hover:text-green-800" title="กู้คืน">
                        <i class="fas fa-undo"></i>
                      </button>
                      <button onclick="deleteDevice('${device.device_id}', true)" 
                              class="text-red-600 hover:text-red-800" title="ลบถาวร">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    ` : `
                      <button onclick="editDevice('${device.device_id}')" 
                              class="text-blue-600 hover:text-blue-800" title="แก้ไข">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button onclick="deleteDevice('${device.device_id}')" 
                              class="text-red-600 hover:text-red-800" title="ลบ">
                        <i class="fas fa-trash"></i>
                      </button>
                     
                    `}
                  </div>
                </td>
              `;
              
              tableBody.appendChild(row);
            });
            
            updateShowingRange();
          }

          // Update pagination
          function updatePagination() {
            const totalPages = Math.ceil(filteredDevices.length / itemsPerPage);
            const paginationContainer = document.getElementById('pagination-buttons');
            
            paginationContainer.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = `px-3 py-1 border rounded ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}`;
            prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => changePage(currentPage - 1);
            paginationContainer.appendChild(prevBtn);
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            for (let i = startPage; i <= endPage; i++) {
              const pageBtn = document.createElement('button');
              pageBtn.className = `px-3 py-1 border rounded ${i === currentPage ? 'bg-blue-500 text-white' : 'hover:bg-gray-50'}`;
              pageBtn.textContent = i;
              pageBtn.onclick = () => changePage(i);
              paginationContainer.appendChild(pageBtn);
            }
            
            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = `px-3 py-1 border rounded ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}`;
            nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => changePage(currentPage + 1);
            paginationContainer.appendChild(nextBtn);
          }

          // Change page
          function changePage(page) {
            const totalPages = Math.ceil(filteredDevices.length / itemsPerPage);
            if (page >= 1 && page <= totalPages) {
              currentPage = page;
              displayDevices();
              updatePagination();
            }
          }

          // Change items per page
          function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('items-per-page').value);
            currentPage = 1;
            displayDevices();
            updatePagination();
          }

          // Update showing range
          function updateShowingRange() {
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = Math.min(currentPage * itemsPerPage, filteredDevices.length);
            
            document.getElementById('showing-range').textContent = filteredDevices.length ? `${startIndex}-${endIndex}` : '0-0';
            document.getElementById('total-items').textContent = filteredDevices.length;
          }

          // Toggle between active and deleted devices
          function toggleDeletedDevices() {
            showingDeleted = !showingDeleted;
            const toggleBtn = document.getElementById('deleted-toggle-btn');
            
            if (showingDeleted) {
              toggleBtn.innerHTML = '<i class="fas fa-list mr-2"></i>ดูอุปกรณ์ปกติ';
              toggleBtn.className = 'btn btn-success';
              document.getElementById('bulk-restore-btn').style.display = 'inline-block';
            } else {
              toggleBtn.innerHTML = '<i class="fas fa-trash mr-2"></i>ดูอุปกรณ์ที่ลบ';
              toggleBtn.className = 'btn btn-warning';
              document.getElementById('bulk-restore-btn').style.display = 'none';
            }
            
            clearSelection();
            filterDevices();
          }

          // Device selection functions
          function toggleDeviceSelection(deviceId) {
            if (selectedDevices.has(deviceId)) {
              selectedDevices.delete(deviceId);
            } else {
              selectedDevices.add(deviceId);
            }
            updateBulkActions();
          }

          function toggleSelectAllDevices() {
            const checkbox = document.getElementById('select-all-devices');
            const deviceCheckboxes = document.querySelectorAll('.device-checkbox');
            
            if (checkbox.checked) {
              deviceCheckboxes.forEach(cb => {
                cb.checked = true;
                selectedDevices.add(cb.value);
              });
            } else {
              deviceCheckboxes.forEach(cb => {
                cb.checked = false;
                selectedDevices.delete(cb.value);
              });
            }
            updateBulkActions();
          }

          function clearSelection() {
            selectedDevices.clear();
            document.getElementById('select-all-devices').checked = false;
            document.querySelectorAll('.device-checkbox').forEach(cb => cb.checked = false);
            updateBulkActions();
          }

          function updateBulkActions() {
            const bulkActionsBar = document.getElementById('bulk-actions-bar');
            const selectedCount = document.getElementById('selected-count');
            
            if (selectedDevices.size > 0) {
              bulkActionsBar.style.display = 'block';
              selectedCount.textContent = `เลือก ${selectedDevices.size} รายการ`;
            } else {
              bulkActionsBar.style.display = 'none';
            }
          }

          // Modal functions
          function openAddDeviceModal() {
            isEditMode = false;
            editingDeviceId = null;
            document.getElementById('modal-title').textContent = 'เพิ่มอุปกรณ์ใหม่';
            document.getElementById('device-form').reset();
            document.getElementById('device-id').disabled = false;
            document.getElementById('hidden-device-id').value = ''; // Clear hidden field
            document.getElementById('device-modal').style.display = 'flex';
          }

          function closeDeviceModal() {
            document.getElementById('device-modal').style.display = 'none';
            document.getElementById('device-form').reset();
          }

          // Edit device
          async function editDevice(deviceId) {
            try {
              const response = await DeviceAPI.getDeviceById(deviceId);
              if (response.success) {
                const device = response.data;
                
                isEditMode = true;
                editingDeviceId = deviceId;
                document.getElementById('modal-title').textContent = 'แก้ไขอุปกรณ์';
                document.getElementById('device-id').value = device.device_id;
                document.getElementById('device-id').disabled = true;
                document.getElementById('hidden-device-id').value = device.device_id; // Set hidden field
                document.getElementById('device-name').value = device.device_name;
                document.getElementById('device-location').value = device.location_id;
                document.getElementById('device-serial').value = device.serialno;
                document.getElementById('device-modal').style.display = 'flex';
              }
            } catch (error) {
              showNotification('เกิดข้อผิดพลาดในการดึงข้อมูลอุปกรณ์', 'error');
            }
          }

          // Save device (add or update)
          async function saveDevice(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const deviceData = Object.fromEntries(formData.entries());
            
            // In add mode, get device_id from visible field
            if (!isEditMode) {
              deviceData.device_id = document.getElementById('device-id').value;
            }
            
            try {
              let response;
              if (isEditMode) {
                response = await DeviceAPI.updateDevice(deviceData);
              } else {
                response = await DeviceAPI.addDevice(deviceData);
              }
              
              if (response.success) {
                showNotification(response.message, 'success');
                closeDeviceModal();
                await refreshDeviceList();
              }
            } catch (error) {
              showNotification(error.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
            }
          }

          // Delete device
          function deleteDevice(deviceId, hardDelete = false) {
            const title = hardDelete ? 'ลบอุปกรณ์ถาวร' : 'ลบอุปกรณ์';
            const message = hardDelete ? 
              'คุณต้องการลบอุปกรณ์นี้ออกจากระบบถาวรหรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้' :
              'คุณต้องการลบอุปกรณ์นี้หรือไม่? คุณสามารถกู้คืนได้ภายหลัง';
            
            showConfirmDialog(title, message, async () => {
              try {
                const response = await DeviceAPI.deleteDevice(deviceId, hardDelete);
                if (response.success) {
                  showNotification(response.message, 'success');
                  await refreshDeviceList();
                }
              } catch (error) {
                showNotification(error.message || 'เกิดข้อผิดพลาดในการลบอุปกรณ์', 'error');
              }
            }, hardDelete ? 'danger' : 'warning');
          }

          // Restore device
          async function restoreDevice(deviceId) {
            showConfirmDialog('กู้คืนอุปกรณ์', 'คุณต้องการกู้คืนอุปกรณ์นี้หรือไม่?', async () => {
              try {
                const response = await DeviceAPI.restoreDevice(deviceId);
                if (response.success) {
                  showNotification(response.message, 'success');
                  await refreshDeviceList();
                }
              } catch (error) {
                showNotification(error.message || 'เกิดข้อผิดพลาดในการกู้คืนอุปกรณ์', 'error');
              }
            }, 'success');
          }

          // Bulk operations
          function bulkDeleteDevices() {
            if (selectedDevices.size === 0) return;
            
            const isHardDelete = showingDeleted;
            const title = isHardDelete ? 'ลบอุปกรณ์ถาวร' : 'ลบอุปกรณ์';
            const message = `คุณต้องการ${isHardDelete ? 'ลบถาวร' : 'ลบ'} ${selectedDevices.size} อุปกรณ์ที่เลือกหรือไม่?`;
            
            showConfirmDialog(title, message, async () => {
              const promises = Array.from(selectedDevices).map(deviceId => 
                DeviceAPI.deleteDevice(deviceId, isHardDelete)
              );
              
              try {
                await Promise.all(promises);
                showNotification(`${isHardDelete ? 'ลบถาวร' : 'ลบ'}อุปกรณ์สำเร็จ`, 'success');
                clearSelection();
                await refreshDeviceList();
              } catch (error) {
                showNotification('เกิดข้อผิดพลาดในการลบอุปกรณ์บางรายการ', 'error');
                clearSelection();
                await refreshDeviceList();
              }
            }, isHardDelete ? 'danger' : 'warning');
          }

          function bulkRestoreDevices() {
            if (selectedDevices.size === 0 || !showingDeleted) return;
            
            showConfirmDialog('กู้คืนอุปกรณ์', `คุณต้องการกู้คืน ${selectedDevices.size} อุปกรณ์ที่เลือกหรือไม่?`, async () => {
              const promises = Array.from(selectedDevices).map(deviceId => 
                DeviceAPI.restoreDevice(deviceId)
              );
              
              try {
                await Promise.all(promises);
                showNotification('กู้คืนอุปกรณ์สำเร็จ', 'success');
                clearSelection();
                await refreshDeviceList();
              } catch (error) {
                showNotification('เกิดข้อผิดพลาดในการกู้คืนอุปกรณ์บางรายการ', 'error');
                clearSelection();
                await refreshDeviceList();
              }
            }, 'success');
          }

          // View device details
          function viewDeviceDetails(deviceId) {
            // Redirect to device details page or show modal with details
            window.location.href = `/device/details/${deviceId}`;
          }

          // Refresh device list
          async function refreshDeviceList() {
            try {
              showLoading(true);
              await Promise.all([
                loadDevices(),
                loadDeviceStats()
              ]);
              filterDevices();
              clearSelection();
              showLoading(false);
              showNotification('รีเฟรชข้อมูลสำเร็จ', 'success');
            } catch (error) {
              showLoading(false);
              showNotification('เกิดข้อผิดพลาดในการรีเฟรชข้อมูล', 'error');
            }
          }

          // Export device data
          function exportDeviceData() {
            // Create CSV content
            const headers = ['รหัสอุปกรณ์', 'ชื่ออุปกรณ์', 'ตำแหน่ง', 'หมายเลขซีเรียล', 'สถานะ'];
            const csvContent = [
              headers.join(','),
              ...filteredDevices.map(device => [
                device.device_id,
                `"${device.device_name}"`,
                device.location_id,
                device.serialno,
                device.void == 1 ? 'ลบแล้ว' : 'ใช้งาน'
              ].join(','))
            ].join('\n');
            
            // Download file
            const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `devices_${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
          }

          // Utility functions
          function showLoading(show) {
            document.getElementById('devices-loading').style.display = show ? 'block' : 'none';
            document.getElementById('devices-table-container').style.display = show ? 'none' : 'block';
          }

          function showNotification(message, type = 'info') {
            if (window.notify) window.notify(message, type);
          }

          async function showConfirmDialog(title, message, onConfirm, type = 'warning') {
            if (window.confirmAction) {
              const icon = type === 'danger' ? 'error' : (type === 'success' ? 'success' : 'warning');
              const res = await window.confirmAction(title, message, { icon, confirmText: 'ยืนยัน' });
              if (res.isConfirmed && typeof onConfirm === 'function') onConfirm();
            }
          }

          function closeConfirmModal() {
            document.getElementById('confirm-modal').style.display = 'none';
          }

          // Initialize page when DOM is loaded
          document.addEventListener('DOMContentLoaded', initializePage);
        </script>


<?php
include_once 'plugin/footer.php';
?>