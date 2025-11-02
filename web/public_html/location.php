<?php
include_once 'plugin/header.php';

// Role-based page guard: only admin (role=1) can access location management
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
  header('Location: dashbroad');
  exit;
}
?>
<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
        <div class="mb-6">
          <h1 class="text-2xl font-bold">จัดการตำแหน่ง</h1>
          <p class="text-muted">เพิ่ม แก้ไข ลบ และจัดการตำแหน่งติดตั้งอุปกรณ์ตรวจสอบดินถล่ม</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
          <button onclick="openAddLocationModal()" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>เพิ่มตำแหน่งใหม่
          </button>
          <button onclick="refreshLocationList()" class="btn btn-secondary">
            <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
          </button>
          <button onclick="toggleDeletedLocations()" class="btn btn-warning" id="deleted-toggle-btn">
            <i class="fas fa-trash mr-2"></i>ดูตำแหน่งที่ลบ
          </button>
          <button onclick="exportLocationData()" class="btn btn-info">
            <i class="fas fa-download mr-2"></i>ส่งออกข้อมูล
          </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="card rounded-lg p-5 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">ค้นหา</label>
              <div class="relative">
                <input type="text" id="search-input" placeholder="ค้นหาตำแหน่ง..." 
                       class="w-full px-4 py-2 border rounded-lg pl-10" onkeyup="filterLocations()">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">สถานะ</label>
              <select id="status-filter" class="w-full px-4 py-2 border rounded-lg" onchange="filterLocations()">
                <option value="all">ทุกสถานะ</option>
                <option value="active">ใช้งาน</option>
                <option value="deleted">ลบแล้ว</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">เรียงตาม</label>
              <select id="sort-order" class="w-full px-4 py-2 border rounded-lg" onchange="sortLocations()">
                <option value="location_id">รหัสตำแหน่ง</option>
                <option value="location_name">ชื่อตำแหน่ง</option>
                <option value="latitude">ละติจูด</option>
                <option value="longtitude">ลองจิจูด</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">จำนวนต่อหน้า</label>
              <select id="items-per-page" class="w-full px-4 py-2 border rounded-lg" onchange="changeItemsPerPage()">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="card rounded-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-muted">ตำแหน่งทั้งหมด</p>
                <p id="total-locations-stat" class="text-2xl font-bold">-</p>
              </div>
              <div class="icon-bg-primary p-3 rounded-full">
                <i class="fas fa-map-marker-alt text-white"></i>
              </div>
            </div>
          </div>
          <div class="card rounded-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-muted">ใช้งานอยู่</p>
                <p id="active-locations-stat" class="text-2xl font-bold text-green-600">-</p>
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
                <p id="deleted-locations-stat" class="text-2xl font-bold text-red-600">-</p>
              </div>
              <div class="icon-bg-danger p-3 rounded-full">
                <i class="fas fa-trash text-white"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Locations Table -->
        <div class="card rounded-lg p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">รายการตำแหน่ง</h3>
            <div class="flex items-center space-x-2">
              <span class="text-sm text-muted">แสดง</span>
              <select id="items-per-page-select" class="text-sm border rounded px-2 py-1" onchange="changeItemsPerPage()">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="text-sm text-muted">รายการ</span>
            </div>
          </div>

          <!-- Loading indicator -->
          <div id="locations-loading" class="text-center py-8" style="display: none;">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-500"></i>
            <p class="text-muted mt-2">กำลังโหลดข้อมูล...</p>
          </div>

          <!-- Global loading overlay moved to header.php -->

          <!-- Locations table -->
          <div id="locations-table-container" class="overflow-x-auto">
            <table class="w-full text-sm table-theme table-zebra">
              <thead class="sticky top-0" style="background-color: var(--bg-card);">
                <tr class="border-b" style="border-color: var(--border-color);">
                  <th class="text-left py-3 px-4 font-medium">
                    <input type="checkbox" id="select-all-locations" onchange="toggleSelectAllLocations()" class="mr-2">
                    ลำดับ
                  </th>
                  <th class="text-left py-3 px-4 font-medium">รหัส</th>
                  <th class="text-left py-3 px-4 font-medium">ชื่อตำแหน่ง</th>
                  <th class="text-left py-3 px-4 font-medium">ละติจูด</th>
                  <th class="text-left py-3 px-4 font-medium">ลองจิจูด</th>
                  <th class="text-left py-3 px-4 font-medium">สถานะ</th>
                  <th class="text-left py-3 px-4 font-medium">การจัดการ</th>
                </tr>
              </thead>
              <tbody id="locations-table-body">
                <!-- Dynamic content will be loaded here -->
              </tbody>
            </table>
          </div>

          <!-- No data message -->
          <div id="no-data-message" class="text-center py-8" style="display: none;">
            <i class="fas fa-map-marker-alt text-4xl text-gray-400 mb-4"></i>
            <p class="text-lg font-medium text-gray-600 mb-2">ไม่พบข้อมูลตำแหน่ง</p>
            <p class="text-gray-500">เริ่มต้นโดยการเพิ่มตำแหน่งใหม่</p>
            <button onclick="openAddLocationModal()" class="btn btn-primary mt-4">
              <i class="fas fa-plus mr-2"></i>เพิ่มตำแหน่งใหม่
            </button>
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

        <!-- Add/Edit Modal -->
    <div id="location-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="rounded-lg w-full max-w-md" style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color); color: var(--text-primary);">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid var(--border-color);">
          <h3 id="modal-title" class="text-lg font-semibold" style="color: var(--text-primary);">เพิ่มตำแหน่งใหม่</h3>
          <button onclick="closeModal()" class="hover:opacity-80" style="color: var(--text-secondary);">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="locationForm" onsubmit="saveLocation(event)">
                    <div class="px-6 py-4 space-y-4">
                        <!-- Hidden field for location ID in edit mode -->
                        <input type="hidden" id="hidden-location-id" name="location_id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">รหัสตำแหน่ง *</label>
                            <input type="text" id="location-id" maxlength="3" 
                                   class="form-input" placeholder="เช่น L01" required>
                            <p class="text-xs text-gray-500 mt-1">ต้องมี 3 ตัวอักษร</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อตำแหน่ง *</label>
                            <input type="text" id="location-name" name="location_name" maxlength="50" 
                                   class="form-input" placeholder="เช่น หน้าบ้านนายกำนัน" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ละติจูด *</label>
                                <input type="number" id="location-latitude" name="latitude" step="0.000001" 
                                       class="form-input" placeholder="13.736717" min="-90" max="90" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ลองจิจูด *</label>
                                <input type="number" id="location-longitude" name="longtitude" step="0.000001" 
                                       class="form-input" placeholder="100.523186" min="-180" max="180" required>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" onclick="getCurrentLocationCoordinates()" class="btn btn-outline flex-1">
                                <i class="fas fa-crosshairs mr-2"></i>ตำแหน่งปัจจุบัน
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-4 flex space-x-3" style="border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary flex-1">
                            <i class="fas fa-save mr-2"></i>บันทึก
                        </button>
                        <button type="button" onclick="closeModal()" class="btn btn-secondary flex-1">
                            ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirm Modal -->
        <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
          <div class="rounded-lg w-full max-w-md" style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color); color: var(--text-primary);">
            <div class="p-6 text-center">
              <div id="confirm-icon" class="mx-auto mb-4 w-12 h-12 rounded-full flex items-center justify-center" style="background-color: var(--color-warning-light); color: #92400e;">
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

        <!-- Bulk Actions Bar -->
  <div id="bulk-actions-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 shadow-lg rounded-lg p-4 flex items-center space-x-4" style="display: none; background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color); color: var(--text-primary);">
          <span id="selected-count" class="text-sm font-medium">เลือก 0 รายการ</span>
          <div class="flex space-x-2">
            <button onclick="bulkDeleteLocations()" class="btn btn-danger btn-sm">
              <i class="fas fa-trash mr-1"></i>ลบที่เลือก
            </button>
            <button id="bulk-restore-btn" onclick="bulkRestoreLocations()" class="btn btn-success btn-sm" style="display: none;">
              <i class="fas fa-undo mr-1"></i>กู้คืนที่เลือก
            </button>
            <button onclick="clearSelection()" class="btn btn-secondary btn-sm">ยกเลิก</button>
          </div>
        </div>

        
        <script>
          // Global variables
          let allLocations = [];
          let filteredLocations = [];
          let deletedLocations = [];
          let currentPage = 1;
          let itemsPerPage = 25;
          let isEditMode = false;
          let editingLocationId = null;
          let selectedLocations = new Set();
          let showingDeleted = false;

          // API endpoints
          const API_ENDPOINTS = {
            locationInsert: '/api/location/insert.php',
            locationGet: '/api/location/get.php',
            locationUpdate: '/api/location/update.php',
            locationDelete: '/api/location/delete.php',
            locationRestore: '/api/location/restore.php',
            locationStats: '/api/location/stats.php'
          };

          // Location API class
          class LocationAPI {
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

            static async getAllLocations() {
              return await this.fetchData(API_ENDPOINTS.locationGet + '?include_deleted=true');
            }

            static async getLocationById(locationId) {
              return await this.fetchData(`${API_ENDPOINTS.locationGet}?location_id=${locationId}`);
            }

            static async searchLocations(keyword) {
              return await this.fetchData(`${API_ENDPOINTS.locationGet}?search=${encodeURIComponent(keyword)}`);
            }

            static async addLocation(locationData) {
              const formData = new URLSearchParams();
              Object.keys(locationData).forEach(key => {
                formData.append(key, locationData[key]);
              });
              
              return await this.fetchData(API_ENDPOINTS.locationInsert, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async updateLocation(locationData) {
              const formData = new URLSearchParams();
              Object.keys(locationData).forEach(key => {
                formData.append(key, locationData[key]);
              });
              
              return await this.fetchData(API_ENDPOINTS.locationUpdate, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async deleteLocation(locationId, hardDelete = false) {
              const formData = new URLSearchParams();
              formData.append('location_id', locationId);
              if (hardDelete) formData.append('hard_delete', 'true');
              
              return await this.fetchData(API_ENDPOINTS.locationDelete, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async restoreLocation(locationId) {
              const formData = new URLSearchParams();
              formData.append('location_id', locationId);
              
              return await this.fetchData(API_ENDPOINTS.locationRestore, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
              });
            }

            static async getLocationStats() {
              return await this.fetchData(API_ENDPOINTS.locationStats);
            }

            static async getLocationById(locationId) {
              return await this.fetchData(`${API_ENDPOINTS.locationGet}?location_id=${locationId}`);
            }
          }

          // Initialize page
          async function initializePage() {
            try {
              if (window.showGlobalLoading) window.showGlobalLoading(true);
              await Promise.all([
                loadLocations(),
                loadLocationStats()
              ]);
              filterLocations();
            } catch (error) {
              console.error('Error initializing page:', error);
              showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
            } finally {
              if (window.showGlobalLoading) window.showGlobalLoading(false);
            }
          }

          // Load locations data
          async function loadLocations() {
            try {
              const response = await LocationAPI.getAllLocations();
              if (response.success) {
                const allData = response.data || [];
                allLocations = allData.filter(location => location.void == 0);
                deletedLocations = allData.filter(location => location.void == 1);
              }
            } catch (error) {
              console.error('Error loading locations:', error);
              allLocations = [];
              deletedLocations = [];
            }
          }

          // Load location statistics
          async function loadLocationStats() {
            try {
              const response = await LocationAPI.getLocationStats();
              if (response.success) {
                const stats = response.data;
                document.getElementById('total-locations-stat').textContent = stats.total_locations || 0;
                document.getElementById('active-locations-stat').textContent = stats.active_locations || 0;
                document.getElementById('deleted-locations-stat').textContent = stats.deleted_locations || 0;
              }
            } catch (error) {
              console.error('Error loading location stats:', error);
            }
          }

          // Filter and search locations
          function filterLocations() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            
            let locationsToFilter = showingDeleted ? deletedLocations : allLocations;
            
            filteredLocations = locationsToFilter.filter(location => {
              const matchesSearch = !searchTerm ||
                location.location_id.toLowerCase().includes(searchTerm) ||
                location.location_name.toLowerCase().includes(searchTerm);              const matchesStatus = !statusFilter || statusFilter === 'all' ||
                (statusFilter === 'active' && location.void == 0) ||
                (statusFilter === 'deleted' && location.void == 1);
              
              return matchesSearch && matchesStatus;
            });
            
            currentPage = 1;
            displayLocations();
            updatePagination();
          }

          // Sort locations
          function sortLocations() {
            const sortBy = document.getElementById('sort-order').value;
            
            filteredLocations.sort((a, b) => {
              let aVal = a[sortBy] || '';
              let bVal = b[sortBy] || '';
              
              if (sortBy === 'latitude' || sortBy === 'longtitude') {
                aVal = parseFloat(aVal);
                bVal = parseFloat(bVal);
                return aVal - bVal;
              }
              
              return aVal.localeCompare(bVal);
            });
            
            displayLocations();
          }

          // Display locations in table
          function displayLocations() {
            const tableBody = document.getElementById('locations-table-body');
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const locationsToShow = filteredLocations.slice(startIndex, endIndex);
            
            if (filteredLocations.length === 0) {
              document.getElementById('locations-table-container').style.display = 'none';
              document.getElementById('no-data-message').style.display = 'block';
              document.getElementById('pagination-container').style.display = 'none';
              return;
            }
            
            document.getElementById('locations-table-container').style.display = 'block';
            document.getElementById('no-data-message').style.display = 'none';
            document.getElementById('pagination-container').style.display = 'flex';
            
            tableBody.innerHTML = '';
            
            locationsToShow.forEach((location, index) => {
              const row = document.createElement('tr');
              row.className = 'hover:bg-gray-50 border-b border-gray-200';
              row.dataset.locationId = location.location_id;
              
              const isDeleted = location.void == 1;
              const displayIndex = startIndex + index + 1;
              
              row.innerHTML = `
                <td class="py-3 px-4">
                  <input type="checkbox" class="location-checkbox mr-2" value="${location.location_id}" 
                         onchange="toggleLocationSelection('${location.location_id}')">
                  ${displayIndex}
                </td>
                <td class="py-3 px-4 font-medium">${location.location_id}</td>
                <td class="py-3 px-4">${location.location_name || 'N/A'}</td>
                <td class="py-3 px-4">${parseFloat(location.latitude || 0).toFixed(6)}</td>
                <td class="py-3 px-4">${parseFloat(location.longtitude || 0).toFixed(6)}</td>
                <td class="py-3 px-4">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                    isDeleted ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                  }">
                    ${isDeleted ? 'ถูกลบ' : 'ใช้งาน'}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="flex space-x-2">
                    ${isDeleted ? `
                      <button onclick="restoreLocation('${location.location_id}')" 
                              class="text-green-600 hover:text-green-900" title="กู้คืนตำแหน่ง">
                        <i class="fas fa-undo"></i>
                      </button>
                      <button onclick="deleteLocation('${location.location_id}', true)" 
                              class="text-red-600 hover:text-red-900" title="ลบถาวร">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    ` : `
                      <button onclick="editLocation('${location.location_id}')" 
                              class="text-indigo-600 hover:text-indigo-900" title="แก้ไขตำแหน่ง">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button onclick="deleteLocation('${location.location_id}')" 
                              class="text-red-600 hover:text-red-900" title="ลบตำแหน่ง">
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
            const totalPages = Math.ceil(filteredLocations.length / itemsPerPage);
            const paginationButtons = document.getElementById('pagination-buttons');
            
            paginationButtons.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            // Previous button
            if (currentPage > 1) {
              const prevButton = document.createElement('button');
              prevButton.textContent = 'ก่อนหน้า';
              prevButton.className = 'px-3 py-1 text-sm border rounded text-blue-600 hover:bg-blue-50';
              prevButton.onclick = () => {
                currentPage--;
                displayLocations();
                updatePagination();
              };
              paginationButtons.appendChild(prevButton);
            }
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            for (let i = startPage; i <= endPage; i++) {
              const pageButton = document.createElement('button');
              pageButton.textContent = i;
              pageButton.className = `px-3 py-1 text-sm border rounded ${
                i === currentPage ? 'bg-blue-500 text-white' : 'text-blue-600 hover:bg-blue-50'
              }`;
              pageButton.onclick = () => {
                currentPage = i;
                displayLocations();
                updatePagination();
              };
              paginationButtons.appendChild(pageButton);
            }
            
            // Next button
            if (currentPage < totalPages) {
              const nextButton = document.createElement('button');
              nextButton.textContent = 'ถัดไป';
              nextButton.className = 'px-3 py-1 text-sm border rounded text-blue-600 hover:bg-blue-50';
              nextButton.onclick = () => {
                currentPage++;
                displayLocations();
                updatePagination();
              };
              paginationButtons.appendChild(nextButton);
            }
          }

          // Change items per page
          function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('items-per-page').value) || 
                          parseInt(document.getElementById('items-per-page-select').value) || 25;
            currentPage = 1;
            displayLocations();
            updatePagination();
          }

          // Update showing range
          function updateShowingRange() {
            const startItem = ((currentPage - 1) * itemsPerPage) + 1;
            const endItem = Math.min(currentPage * itemsPerPage, filteredLocations.length);
            
            document.getElementById('showing-range').textContent = 
              filteredLocations.length > 0 ? `${startItem}-${endItem}` : '0-0';
            document.getElementById('total-items').textContent = filteredLocations.length;
          }

          // Toggle between active and deleted locations
          function toggleDeletedLocations() {
            showingDeleted = !showingDeleted;
            const btn = document.getElementById('deleted-toggle-btn');
            
            if (showingDeleted) {
              btn.innerHTML = '<i class="fas fa-eye mr-2"></i>ดูตำแหน่งปกติ';
              btn.className = btn.className.replace('btn-warning', 'btn-info');
            } else {
              btn.innerHTML = '<i class="fas fa-trash mr-2"></i>ดูตำแหน่งที่ลบ';
              btn.className = btn.className.replace('btn-info', 'btn-warning');
            }
            
            filterLocations();
            updateBulkActions();
          }

          // Location selection functions
          function toggleLocationSelection(locationId) {
            if (selectedLocations.has(locationId)) {
              selectedLocations.delete(locationId);
            } else {
              selectedLocations.add(locationId);
            }
            updateBulkActions();
          }

          function toggleSelectAllLocations() {
            const selectAll = document.getElementById('select-all-locations');
            const checkboxes = document.querySelectorAll('.location-checkbox');
            
            if (selectAll.checked) {
              checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                selectedLocations.add(checkbox.value);
              });
            } else {
              checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                selectedLocations.delete(checkbox.value);
              });
            }
            updateBulkActions();
          }

          function clearSelection() {
            selectedLocations.clear();
            document.querySelectorAll('.location-checkbox').forEach(checkbox => {
              checkbox.checked = false;
            });
            document.getElementById('select-all-locations').checked = false;
            updateBulkActions();
          }

          function updateBulkActions() {
            const bulkBar = document.getElementById('bulk-actions-bar');
            const selectedCount = document.getElementById('selected-count');
            const restoreBtn = document.getElementById('bulk-restore-btn');
            
            if (selectedLocations.size > 0) {
              bulkBar.style.display = 'block';
              selectedCount.textContent = `เลือก ${selectedLocations.size} รายการ`;
              restoreBtn.style.display = showingDeleted ? 'inline-block' : 'none';
            } else {
              bulkBar.style.display = 'none';
            }
          }

          // Modal functions
          function openAddLocationModal() {
            isEditMode = false;
            editingLocationId = null;
            document.getElementById('modal-title').textContent = 'เพิ่มตำแหน่งใหม่';
            document.getElementById('locationForm').reset();
            document.getElementById('location-id').disabled = false;
            document.getElementById('hidden-location-id').value = ''; // Clear hidden field
            document.getElementById('location-modal').style.display = 'flex';
          }

          function closeModal() {
            document.getElementById('location-modal').style.display = 'none';
            document.getElementById('locationForm').reset();
            isEditMode = false;
            editingLocationId = null;
          }

          // Edit location
          async function editLocation(locationId) {
            try {
              // Find location from existing data
              const location = [...allLocations, ...deletedLocations].find(l => l.location_id === locationId);
              
              if (location) {
                document.getElementById('location-id').value = location.location_id;
                document.getElementById('location-name').value = location.location_name;
                document.getElementById('location-latitude').value = location.latitude;
                document.getElementById('location-longitude').value = location.longtitude;
                document.getElementById('hidden-location-id').value = location.location_id; // Set hidden field
                
                isEditMode = true;
                editingLocationId = locationId;
                document.getElementById('modal-title').textContent = 'แก้ไขตำแหน่ง';
                document.getElementById('location-id').disabled = true;
                document.getElementById('location-modal').style.display = 'flex';
              } else {
                throw new Error('ไม่พบข้อมูลตำแหน่ง');
              }
            } catch (error) {
              console.error('Error loading location for edit:', error);
              showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
            }
          }

          // Save location (add or update)
          async function saveLocation(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const locationData = Object.fromEntries(formData.entries());
            
            // In add mode, get location_id from visible field
            if (!isEditMode) {
              locationData.location_id = document.getElementById('location-id').value;
            }
            
            const lat = parseFloat(locationData.latitude);
            const lng = parseFloat(locationData.longtitude);
            
            if (isNaN(lat) || isNaN(lng)) {
              showNotification('กรุณาระบุพิกัดที่ถูกต้อง', 'error');
              return;
            }
            
            if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
              showNotification('พิกัดไม่ถูกต้อง', 'error');
              return;
            }
            
            try {
              if (window.showGlobalLoading) window.showGlobalLoading(true);
              let response;
              
              if (isEditMode) {
                response = await LocationAPI.updateLocation(locationData);
              } else {
                response = await LocationAPI.addLocation(locationData);
              }
              
              if (response.success) {
                showNotification(response.message || 'บันทึกข้อมูลสำเร็จ', 'success');
                closeModal();
                await refreshLocationList();
              } else {
                showNotification(response.error || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
              }
            } catch (error) {
              console.error('Error saving location:', error);
              showNotification('เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
            } finally {
              if (window.showGlobalLoading) window.showGlobalLoading(false);
            }
          }

          // Delete location
          function deleteLocation(locationId, hardDelete = false) {
            const action = hardDelete ? 'ลบถาวร' : 'ลบ';
            const confirmMessage = hardDelete ? 
              'คุณต้องการลบตำแหน่งนี้ออกจากระบบถาวรใช่หรือไม่?' :
              'คุณต้องการลบตำแหน่งนี้ใช่หรือไม่?';
              
            showConfirmDialog(
              `${action}ตำแหน่ง`,
              confirmMessage,
              async () => {
                try {
                  if (window.showGlobalLoading) window.showGlobalLoading(true);
                  const response = await LocationAPI.deleteLocation(locationId, hardDelete);
                  
                  if (response.success) {
                    showNotification(response.message || `${action}ตำแหน่งสำเร็จ`, 'success');
                    await refreshLocationList();
                  } else {
                    showNotification(response.error || `เกิดข้อผิดพลาดในการ${action}ตำแหน่ง`, 'error');
                  }
                } catch (error) {
                  console.error(`Error ${action.toLowerCase()} location:`, error);
                  showNotification(`เกิดข้อผิดพลาดในการ${action}ตำแหน่ง`, 'error');
                } finally {
                  if (window.showGlobalLoading) window.showGlobalLoading(false);
                }
              },
              hardDelete ? 'danger' : 'warning'
            );
          }

          // Restore location
          async function restoreLocation(locationId) {
            showConfirmDialog(
              'กู้คืนตำแหน่ง',
              'คุณต้องการกู้คืนตำแหน่งนี้ใช่หรือไม่?',
              async () => {
                try {
                  if (window.showGlobalLoading) window.showGlobalLoading(true);
                  const response = await LocationAPI.restoreLocation(locationId);
                  
                  if (response.success) {
                    showNotification(response.message || 'กู้คืนตำแหน่งสำเร็จ', 'success');
                    await refreshLocationList();
                  } else {
                    showNotification(response.error || 'เกิดข้อผิดพลาดในการกู้คืนตำแหน่ง', 'error');
                  }
                } catch (error) {
                  console.error('Error restoring location:', error);
                  showNotification('เกิดข้อผิดพลาดในการกู้คืนตำแหน่ง', 'error');
                } finally {
                  if (window.showGlobalLoading) window.showGlobalLoading(false);
                }
              }
            );
          }

          // Bulk operations
          function bulkDeleteLocations() {
            if (selectedLocations.size === 0) return;
            
            const action = showingDeleted ? 'ลบถาวร' : 'ลบ';
            showConfirmDialog(
              `${action}ตำแหน่งที่เลือก`,
              `คุณต้องการ${action}ตำแหน่งที่เลือก ${selectedLocations.size} รายการใช่หรือไม่?`,
              async () => {
                try {
                  if (window.showGlobalLoading) window.showGlobalLoading(true);
                  const promises = Array.from(selectedLocations).map(locationId =>
                    LocationAPI.deleteLocation(locationId, showingDeleted)
                  );
                  await Promise.all(promises);
                  
                  showNotification(`${action}ตำแหน่งสำเร็จ`, 'success');
                  clearSelection();
                  await refreshLocationList();
                } catch (error) {
                  console.error(`Error bulk ${action.toLowerCase()} locations:`, error);
                  showNotification(`เกิดข้อผิดพลาดในการ${action}ตำแหน่ง`, 'error');
                } finally {
                  if (window.showGlobalLoading) window.showGlobalLoading(false);
                }
              },
              showingDeleted ? 'danger' : 'warning'
            );
          }

          function bulkRestoreLocations() {
            if (selectedLocations.size === 0) return;
            
            showConfirmDialog(
              'กู้คืนตำแหน่งที่เลือก',
              `คุณต้องการกู้คืนตำแหน่งที่เลือก ${selectedLocations.size} รายการใช่หรือไม่?`,
              async () => {
                try {
                  if (window.showGlobalLoading) window.showGlobalLoading(true);
                  const promises = Array.from(selectedLocations).map(locationId =>
                    LocationAPI.restoreLocation(locationId)
                  );
                  await Promise.all(promises);
                  
                  showNotification('กู้คืนตำแหน่งสำเร็จ', 'success');
                  clearSelection();
                  await refreshLocationList();
                } catch (error) {
                  console.error('Error bulk restore locations:', error);
                  showNotification('เกิดข้อผิดพลาดในการกู้คืนตำแหน่ง', 'error');
                } finally {
                  if (window.showGlobalLoading) window.showGlobalLoading(false);
                }
              }
            );
          }

          // Refresh location list
          async function refreshLocationList() {
            await Promise.all([
              loadLocations(),
              loadLocationStats()
            ]);
            filterLocations();
            clearSelection();
          }

          // Export location data
          function exportLocationData() {
            if (filteredLocations.length === 0) {
              showNotification('ไม่มีข้อมูลให้ส่งออก', 'warning');
              return;
            }
            
            const headers = ['รหัสตำแหน่ง', 'ชื่อตำแหน่ง', 'ละติจูด', 'ลองจิจูด', 'สถานะ'];
            const csvContent = [
              headers.join(','),
              ...filteredLocations.map(location => [
                location.location_id,
                `"${location.location_name}"`,
                location.latitude,
                location.longtitude,
                location.void == 1 ? 'ลบแล้ว' : 'ใช้งาน'
              ].join(','))
            ].join('\n');
            
            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `locations_${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            
            if (window.notify) window.notify('ส่งออกข้อมูลสำเร็จ', 'success');
          }

          // Get current location coordinates
          function getCurrentLocationCoordinates() {
            if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(
                function(position) {
                  document.getElementById('location-latitude').value = position.coords.latitude.toFixed(6);
                  document.getElementById('location-longitude').value = position.coords.longitude.toFixed(6);
                  if (window.notify) window.notify('ดึงตำแหน่งปัจจุบันสำเร็จ', 'success');
                },
                function(error) {
                  if (window.notify) window.notify('ไม่สามารถดึงตำแหน่งปัจจุบันได้', 'error');
                }
              );
            } else {
              if (window.notify) window.notify('เบราว์เซอร์ไม่รองรับการระบุตำแหน่ง', 'error');
            }
          }

          // Utility functions
          // Loading is handled globally in header.php via window.showGlobalLoading(show)

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