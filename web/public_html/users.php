<?php
include_once 'plugin/header.php';

// Role-based page guard: only admin (role=1) can access user management
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
  header('Location: dashbroad');
  exit;
}
?>
<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">จัดการผู้ใช้</h1>
    <p class="text-muted">เพิ่ม แก้ไข ลบ กู้คืน และดูสถิติผู้ใช้</p>
  </div>

  <div class="flex flex-wrap gap-3 mb-6">
    <button onclick="openAddUserModal()" class="btn btn-primary">
      <i class="fas fa-user-plus mr-2"></i>เพิ่มผู้ใช้ใหม่
    </button>
    <button onclick="refreshUserList()" class="btn btn-secondary">
      <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
    </button>
    <button onclick="toggleDeletedUsers()" class="btn btn-warning" id="deleted-toggle-btn">
      <i class="fas fa-trash mr-2"></i>ดูผู้ใช้ที่ลบ
    </button>

  </div>

  <div class="card rounded-lg p-5 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2">ค้นหา</label>
        <div class="relative">
          <input type="text" id="search-input" placeholder="ค้นหาผู้ใช้..."
                 class="w-full px-4 py-2 border rounded-lg pl-10" onkeyup="searchUsers()">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">สิทธิ์</label>
        <select id="role-filter" class="w-full px-4 py-2 border rounded-lg" onchange="filterUsers()">
          <option value="">ทั้งหมด</option>
          <option value="1">ผู้ดูแลระบบ</option>
          <option value="0">ผู้ใช้</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">สถานะ</label>
        <select id="status-filter" class="w-full px-4 py-2 border rounded-lg" onchange="filterUsers()">
          <option value="">ทุกสถานะ</option>
          <option value="active">ใช้งาน</option>
          <option value="deleted">ลบแล้ว</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">เรียงตาม</label>
        <select id="sort-order" class="w-full px-4 py-2 border rounded-lg" onchange="sortUsers()">
          <option value="id">รหัส</option>
          <option value="username">ชื่อผู้ใช้</option>
          <option value="email">อีเมล</option>
          <option value="role">สิทธิ์</option>
        </select>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card rounded-lg p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted">ผู้ใช้ทั้งหมด</p>
          <p id="total-users-stat" class="text-2xl font-bold">-</p>
        </div>
        <div class="icon-bg-info p-3 rounded-full">
          <i class="fas fa-users"></i>
        </div>
      </div>
    </div>
    <div class="card rounded-lg p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted">ใช้งานอยู่</p>
          <p id="active-users-stat" class="text-2xl font-bold">-</p>
        </div>
        <div class="icon-bg-success p-3 rounded-full">
          <i class="fas fa-user-check"></i>
        </div>
      </div>
    </div>
    <div class="card rounded-lg p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted">ถูกลบ</p>
          <p id="deleted-users-stat" class="text-2xl font-bold">-</p>
        </div>
        <div class="icon-bg-danger p-3 rounded-full">
          <i class="fas fa-user-times"></i>
        </div>
      </div>
    </div>
    <div class="card rounded-lg p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted">ผู้ดูแลระบบ</p>
          <p id="admin-users-stat" class="text-2xl font-bold">-</p>
        </div>
        <div class="icon-bg-warning p-3 rounded-full">
          <i class="fas fa-user-shield"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="card rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full table-theme table-zebra">
        <thead>
          <tr>
            <th class="px-6 py-3 text-left">รหัส</th>
            <th class="px-6 py-3 text-left">ชื่อผู้ใช้</th>
            <th class="px-6 py-3 text-left">อีเมล</th>
            <th class="px-6 py-3 text-left">สิทธิ์</th>
            <th class="px-6 py-3 text-left">สถานะ</th>
            <th class="px-6 py-3 text-right">การจัดการ</th>
          </tr>
        </thead>
        <tbody id="users-table"></tbody>
      </table>
    </div>
    <div id="no-data" class="p-6 text-center hidden" style="color: var(--text-muted);">
      <i class="fas fa-users text-4xl mb-3" style="color: var(--text-muted);"></i>
      <p class="text-lg font-medium mb-2">ไม่พบข้อมูลผู้ใช้</p>
      <p class="text-sm">ลองใช้คำค้นหาอื่นหรือเพิ่มผู้ใช้ใหม่</p>
    </div>
  </div>

  <!-- Modal -->
  <div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="rounded-lg w-full max-w-lg p-6" style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color);">
      <h3 id="modal-title" class="text-xl font-semibold mb-4" style="color: var(--text-primary);">เพิ่มผู้ใช้</h3>
      <form id="user-form" onsubmit="return submitUserForm(event)">
        <input type="hidden" id="user-id">
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">ชื่อผู้ใช้</label>
            <input id="username" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" required>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">อีเมล</label>
            <input id="email" type="email" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" required>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">สิทธิ์</label>
            <select id="role" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);">
              <option value="0">ผู้ใช้</option>
              <option value="1">ผู้ดูแลระบบ</option>
            </select>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">รหัสผ่าน</label>
            <input id="password" type="password" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" placeholder="ปล่อยว่างถ้าไม่เปลี่ยน">
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" class="btn" onclick="closeUserModal()">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
</main>
<script>
const API = {
  GET: '/api/user/get.php',
  INSERT: '/api/user/insert.php',
  UPDATE: '/api/user/update.php',
  DELETE: '/api/user/delete.php',
  RESTORE: '/api/user/restore.php',
  STATS: '/api/user/stats.php'
};

let allUsers = [];
let filteredUsers = [];
let showDeleted = false;

async function fetchUsers() {
  try {
    const url = `${API.GET}?include_deleted=${showDeleted}`;
    const res = await fetch(url);
    const json = await res.json();
    return json.data || [];
  } catch (e) {
    console.error(e);
    return [];
  }
}

async function fetchStats() {
  try {
    const res = await fetch(API.STATS);
    const json = await res.json();
    return json.data || {};
  } catch (e) { return {}; }
}

function renderStats(stats) {
  document.getElementById('total-users-stat').textContent = stats.total_users ?? '-';
  document.getElementById('active-users-stat').textContent = stats.active_users ?? '-';
  document.getElementById('deleted-users-stat').textContent = stats.deleted_users ?? '-';
  document.getElementById('admin-users-stat').textContent = stats.roles ? (stats.roles.admin ?? '-') : '-';
}

function filterUsers() {
  const role = document.getElementById('role-filter').value;
  const status = document.getElementById('status-filter').value;
  const search = document.getElementById('search-input').value.toLowerCase();

  filteredUsers = allUsers.filter(u => {
    const matchesRole = role === '' || String(u.role) === role;
    const matchesStatus = status === '' || (status === 'active' ? u.void == 0 : u.void == 1);
    const s = `${u.id} ${u.username ?? ''} ${u.email ?? ''}`.toLowerCase();
    const matchesSearch = s.includes(search);
    return matchesRole && matchesStatus && matchesSearch;
  });

  renderTable();
}

function sortUsers() {
  const key = document.getElementById('sort-order').value;
  filteredUsers.sort((a,b) => {
    const va = a[key];
    const vb = b[key];
    if (va == null && vb == null) return 0;
    if (va == null) return 1;
    if (vb == null) return -1;
    if (typeof va === 'number' && typeof vb === 'number') return va - vb;
    return String(va).localeCompare(String(vb), 'th');
  });
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('users-table');
  tbody.innerHTML = '';

  if (!filteredUsers.length) {
    document.getElementById('no-data').classList.remove('hidden');
    return;
  }
  document.getElementById('no-data').classList.add('hidden');

  for (const u of filteredUsers) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-6 py-3 whitespace-nowrap">${String(u.id).padStart(10,'0')}</td>
      <td class="px-6 py-3 whitespace-nowrap">${escapeHtml(u.username)}</td>
      <td class="px-6 py-3 whitespace-nowrap">${escapeHtml(u.email)}</td>
      <td class="px-6 py-3 whitespace-nowrap">${u.role == 1 ? '<span class="px-2 py-1 rounded text-xs status-warning">แอดมิน</span>' : '<span class="px-2 py-1 rounded text-xs status-info">ผู้ใช้</span>'}</td>
      <td class="px-6 py-3 whitespace-nowrap">${u.void == 1 ? '<span class="px-2 py-1 rounded text-xs status-danger">ลบแล้ว</span>' : '<span class="px-2 py-1 rounded text-xs status-success">ใช้งาน</span>'}</td>
      <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
        ${u.void == 0 ? `
          <button class="btn btn-sm" onclick="openEditUserModal(${u.id})"><i class='fas fa-edit'></i><span class='hidden sm:inline'>แก้ไข</span></button>
          <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})"><i class='fas fa-trash'></i><span class='hidden sm:inline'>ลบ</span></button>
        ` : `
          <button class="btn btn-sm btn-success" onclick="restoreUser(${u.id})"><i class='fas fa-undo'></i><span class='hidden sm:inline'>กู้คืน</span></button>
          <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id}, true)"><i class='fas fa-trash-alt'></i><span class='hidden sm:inline'>ลบถาวร</span></button>
        `}
      </td>`;
    tbody.appendChild(tr);
  }
}

function openAddUserModal() {
  document.getElementById('modal-title').textContent = 'เพิ่มผู้ใช้ใหม่';
  document.getElementById('user-id').value = '';
  document.getElementById('username').value = '';
  document.getElementById('email').value = '';
  document.getElementById('role').value = '0';
  document.getElementById('password').value = '';
  document.getElementById('password').placeholder = 'กรุณากรอกรหัสผ่าน';
  document.getElementById('password').required = true;
  document.getElementById('user-modal').classList.remove('hidden');
  document.getElementById('user-modal').classList.add('flex');
  // Focus on first input
  setTimeout(() => document.getElementById('username').focus(), 100);
}

function openEditUserModal(id) {
  const u = allUsers.find(x => x.id == id);
  if (!u) return;
  document.getElementById('modal-title').textContent = 'แก้ไขผู้ใช้';
  document.getElementById('user-id').value = u.id;
  document.getElementById('username').value = u.username || '';
  document.getElementById('email').value = u.email || '';
  document.getElementById('role').value = String(u.role ?? 0);
  document.getElementById('password').value = '';
  document.getElementById('password').placeholder = 'ปล่อยว่างถ้าไม่เปลี่ยนรหัสผ่าน';
  document.getElementById('password').required = false;
  document.getElementById('user-modal').classList.remove('hidden');
  document.getElementById('user-modal').classList.add('flex');
  // Focus on first input
  setTimeout(() => document.getElementById('username').focus(), 100);
}

function closeUserModal() {
  document.getElementById('user-modal').classList.add('hidden');
  document.getElementById('user-modal').classList.remove('flex');
}

async function submitUserForm(e) {
  e.preventDefault();
  const id = document.getElementById('user-id').value;
  const username = document.getElementById('username').value.trim();
  const email = document.getElementById('email').value.trim();
  const role = parseInt(document.getElementById('role').value, 10);
  const password = document.getElementById('password').value;

  // Validation (SweetAlert2)
  if (!username) { await Swal.fire({ icon: 'error', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกชื่อผู้ใช้' }); return; }
  if (!email) { await Swal.fire({ icon: 'error', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกอีเมล' }); return; }
  if (!id && !password) { await Swal.fire({ icon: 'error', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกรหัสผ่านสำหรับผู้ใช้ใหม่' }); return; }

  const payload = { username, email, role };
  if (password) payload.password = password;

  try {
    window.showGlobalLoading && window.showGlobalLoading(true);
    const res = await fetch(id ? API.UPDATE : API.INSERT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(id ? { id: parseInt(id,10), ...payload } : payload)
    });
    const json = await res.json();
    if (!res.ok || json.error) throw new Error(json.error || 'Request failed');
    closeUserModal();
    await refreshUserList();
    notify(id ? 'อัปเดตผู้ใช้สำเร็จ' : 'เพิ่มผู้ใช้สำเร็จ', 'success');
  } catch (err) {
    await Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: err.message });
  } finally {
    window.showGlobalLoading && window.showGlobalLoading(false);
  }
}

async function deleteUser(id, hard=false) {
  const result = await confirmAction(
    hard ? 'ลบผู้ใช้ถาวร' : 'ลบผู้ใช้',
    hard ? 'คุณต้องการลบผู้ใช้นี้ถาวรหรือไม่? การกระทำนี้ไม่สามารถยกเลิกได้' : 'คุณต้องการลบผู้ใช้นี้หรือไม่?',
    { icon: hard ? 'error' : 'warning', confirmText: hard ? 'ลบถาวร' : 'ลบ' }
  );
  if (!result.isConfirmed) return;

  try {
    window.showGlobalLoading && window.showGlobalLoading(true);
    const res = await fetch(API.DELETE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, hard_delete: !!hard })
    });
    const json = await res.json();
    if (!res.ok || json.error) throw new Error(json.error || 'Delete failed');
    await refreshUserList();
    notify(hard ? 'ลบผู้ใช้ถาวรสำเร็จ' : 'ลบผู้ใช้สำเร็จ', 'success');
  } catch (e) { 
    await Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: e.message });
  } finally { 
    window.showGlobalLoading && window.showGlobalLoading(false); 
  }
}

async function restoreUser(id) {
  const resConfirm = await confirmAction('กู้คืนผู้ใช้', 'คุณต้องการกู้คืนผู้ใช้นี้หรือไม่?', { icon: 'question', confirmText: 'กู้คืน' });
  if (!resConfirm.isConfirmed) return;

  try {
    window.showGlobalLoading && window.showGlobalLoading(true);
    const res = await fetch(API.RESTORE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!res.ok || json.error) throw new Error(json.error || 'Restore failed');
    await refreshUserList();
    notify('กู้คืนผู้ใช้สำเร็จ', 'success');
  } catch (e) { 
    await Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: e.message });
  } finally { 
    window.showGlobalLoading && window.showGlobalLoading(false); 
  }
}

// Add notification function
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  };
  const colors = {
    success: 'bg-green-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-black',
    info: 'bg-blue-500 text-white'
  };
  
  notification.className = `fixed top-4 right-4 z-[60] p-4 rounded-lg shadow-lg flex items-center gap-3 min-w-72 ${colors[type] || colors.info}`;
  notification.innerHTML = `
    <i class="${icons[type] || icons.info}"></i>
    <span>${message}</span>
    <button onclick="this.parentElement.remove()" class="ml-auto hover:bg-black hover:bg-opacity-20 rounded p-1">
      <i class="fas fa-times"></i>
    </button>
  `;
  
  document.body.appendChild(notification);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove();
    }
  }, 5000);
}

async function refreshUserList() {
  try {
    window.showGlobalLoading && window.showGlobalLoading(true);
    const [users, stats] = await Promise.all([fetchUsers(), fetchStats()]);
    allUsers = users;
    filteredUsers = [...allUsers];
    filterUsers();
    renderStats(stats);
  } catch (error) {
  await Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถโหลดข้อมูลได้' });
  } finally {
    window.showGlobalLoading && window.showGlobalLoading(false);
  }
}
function toggleDeletedUsers() {
  showDeleted = !showDeleted;
  const btn = document.getElementById('deleted-toggle-btn');
  if (showDeleted) {
    btn.innerHTML = '<i class="fas fa-eye mr-2"></i>ดูผู้ใช้ที่ใช้งาน';
    btn.className = 'btn btn-info';
  } else {
    btn.innerHTML = '<i class="fas fa-trash mr-2"></i>ดูผู้ใช้ที่ลบ';
    btn.className = 'btn btn-warning';
  }
  refreshUserList();
}

function searchUsers() { filterUsers(); }

function escapeHtml(s) {
  if (s == null) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\"/g, '&quot;')
      .replace(/'/g, '&#39;');
}

function validateField(e) {
  const field = e.target;
  const value = field.value.trim();
  
  // Clear previous error styling
  field.classList.remove('border-red-500', 'focus:border-red-500');
  
  if (field.hasAttribute('required') && !value) {
    field.classList.add('border-red-500', 'focus:border-red-500');
    return false;
  }
  
  // Email validation
  if (field.type === 'email' && value && !isValidEmail(value)) {
    field.classList.add('border-red-500', 'focus:border-red-500');
    return false;
  }
  
  return true;
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

(async function init(){
  // Initialize form validation
  const form = document.getElementById('userForm');
  if (form) {
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
      input.addEventListener('input', validateField);
      input.addEventListener('blur', validateField);
    });
  }

  // Initialize theme observer
  if (typeof window.themeObserver === 'function') {
    window.themeObserver();
  }

  // Load initial data
  await refreshUserList();
})();
</script>
<?php include_once 'plugin/footer.php'; ?>
