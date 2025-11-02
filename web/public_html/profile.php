<?php
require_once __DIR__ . '/plugin/header.php';
require_once __DIR__ . '/../database/table_user.php';

// Fetch current user data (by username/email from session)
$currentUser = [
  'id' => null,
  'username' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
  'email' => isset($_SESSION['email']) ? $_SESSION['email'] : ''
];

// Best effort: try to get full user row by username
try {
  $userTable = new Table_user();
  if (!empty($currentUser['username'])) {
    $row = $userTable->getUserByUsername($currentUser['username']);
    if ($row) { $currentUser = $row; }
  }
} catch (Throwable $e) {}
?>
<div class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <div class="max-w-3xl mx-auto">
    <div class="mb-4">
      <h1 class="text-2xl font-semibold" style="color: var(--text-primary);">โปรไฟล์ของฉัน</h1>
      <p class="text-sm" style="color: var(--text-secondary);">อัปเดตข้อมูลโปรไฟล์และรหัสผ่านของคุณ</p>
    </div>

    <!-- Profile card -->
    <div class="rounded-lg p-6" style="background-color: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 10px 25px var(--shadow-color);">
      <div class="flex items-center gap-4">
        <img class="h-16 w-16 rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['username'] ?: 'User'); ?>&background=10b981&color=fff" alt="Avatar">
        <div>
          <div class="text-lg font-medium" style="color: var(--text-primary);"><?php echo htmlspecialchars($currentUser['username'] ?: 'ไม่ทราบชื่อ'); ?></div>
          <div class="text-sm" style="color: var(--text-secondary);"><?php echo htmlspecialchars($currentUser['email'] ?: ''); ?></div>
        </div>
      </div>

      <hr class="my-6" style="border-color: var(--border-color);">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic info form -->
        <form id="profile-form" onsubmit="return saveProfile(event)" class="space-y-4">
          <h2 class="text-lg font-semibold" style="color: var(--text-primary);">ข้อมูลพื้นฐาน</h2>
          <input type="hidden" id="user-id" value="<?php echo htmlspecialchars($currentUser['id'] ?? ''); ?>">
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">ชื่อผู้ใช้</label>
            <input id="pf-username" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" value="<?php echo htmlspecialchars($currentUser['username'] ?: ''); ?>" required>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">อีเมล</label>
            <input id="pf-email" type="email" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" value="<?php echo htmlspecialchars($currentUser['email'] ?: ''); ?>" required>
          </div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary flex-1"><i class="fas fa-save mr-2"></i>บันทึก</button>
            <button type="button" class="btn flex-1" onclick="resetProfile()">ยกเลิก</button>
          </div>
        </form>

        <!-- Change password form -->
        <form id="password-form" onsubmit="return changePassword(event)" class="space-y-4">
          <h2 class="text-lg font-semibold" style="color: var(--text-primary);">เปลี่ยนรหัสผ่าน</h2>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">รหัสผ่านเดิม</label>
            <input id="old-password" type="password" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" required>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">รหัสผ่านใหม่</label>
            <input id="new-password" type="password" minlength="8" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" required>
          </div>
          <div>
            <label class="block text-sm mb-1" style="color: var(--text-secondary);">ยืนยันรหัสผ่านใหม่</label>
            <input id="confirm-password" type="password" minlength="8" class="w-full px-3 py-2 border rounded" style="background-color: var(--bg-input); border-color: var(--border-color); color: var(--text-primary);" required>
          </div>
          <button type="submit" class="btn btn-warning w-full"><i class="fas fa-key mr-2"></i>เปลี่ยนรหัสผ่าน</button>
        </form>
      </div>
    </div>
  </div>
</div>



<script>
// API endpoints - using existing user APIs with slight extensions
const API_PROFILE = {
  GET: '/api/profile/get.php',
  UPDATE: '/api/profile/update.php',
  CHANGE_PASSWORD: '/api/profile/change-password.php'
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  // Theme observer init if available
  if (typeof window.themeObserver === 'function') window.themeObserver();
});

async function resetProfile() {
  try {
    const res = await fetch(API_PROFILE.GET);
    const json = await res.json();
    if (json && json.success && json.data) {
      document.getElementById('user-id').value = json.data.id || '';
      document.getElementById('pf-username').value = json.data.username || '';
      document.getElementById('pf-email').value = json.data.email || '';
    } else {
      document.getElementById('pf-username').value = '<?php echo addslashes(htmlspecialchars($currentUser['username'] ?: '')); ?>';
      document.getElementById('pf-email').value = '<?php echo addslashes(htmlspecialchars($currentUser['email'] ?: '')); ?>';
    }
  } catch (_) {
    document.getElementById('pf-username').value = '<?php echo addslashes(htmlspecialchars($currentUser['username'] ?: '')); ?>';
    document.getElementById('pf-email').value = '<?php echo addslashes(htmlspecialchars($currentUser['email'] ?: '')); ?>';
  }
}

async function saveProfile(e) {
  e.preventDefault();
  const id = document.getElementById('user-id').value;
  const username = document.getElementById('pf-username').value.trim();
  const email = document.getElementById('pf-email').value.trim();
  if (!username || !email) {
    if (window.Swal) {
      return Swal.fire({ icon: 'error', title: 'กรอกข้อมูลไม่ครบ' });
    } else {
      return alert('กรอกข้อมูลไม่ครบ');
    }
  }
  try {
    const form = new FormData();
    form.append('id', id);
    form.append('username', username);
    form.append('email', email);
    const res = await fetch(API_PROFILE.UPDATE, { method: 'POST', body: form });
    const json = await res.json();
    if (json.success) {
      if (window.notify) {
        notify('อัปเดตโปรไฟล์สำเร็จ', 'success');
      } else if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'อัปเดตโปรไฟล์สำเร็จ' });
      } else {
        alert('อัปเดตโปรไฟล์สำเร็จ');
      }
    } else {
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: json.error || 'อัปเดตโปรไฟล์ไม่สำเร็จ' });
      } else {
        alert(json.error || 'อัปเดตโปรไฟล์ไม่สำเร็จ');
      }
    }
  } catch (err) {
    if (window.Swal) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
    } else {
      alert('เกิดข้อผิดพลาด');
    }
  }
}

async function changePassword(e) {
  e.preventDefault();
  const oldPwd = document.getElementById('old-password').value;
  const newPwd = document.getElementById('new-password').value;
  const confPwd = document.getElementById('confirm-password').value;
  if (newPwd.length < 8) {
    if (window.Swal) {
      return Swal.fire({ icon: 'error', title: 'รหัสผ่านใหม่อย่างน้อย 8 ตัวอักษร' });
    } else {
      return alert('รหัสผ่านใหม่อย่างน้อย 8 ตัวอักษร');
    }
  }
  if (newPwd !== confPwd) {
    if (window.Swal) {
      return Swal.fire({ icon: 'error', title: 'รหัสผ่านใหม่ไม่ตรงกัน' });
    } else {
      return alert('รหัสผ่านใหม่ไม่ตรงกัน');
    }
  }
  try {
    const form = new FormData();
    form.append('old_password', oldPwd);
    form.append('new_password', newPwd);
    const res = await fetch(API_PROFILE.CHANGE_PASSWORD, { method: 'POST', body: form });
    const json = await res.json();
    if (json.success) {
      if (window.notify) {
        notify('เปลี่ยนรหัสผ่านสำเร็จ', 'success');
      } else if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'เปลี่ยนรหัสผ่านสำเร็จ' });
      } else {
        alert('เปลี่ยนรหัสผ่านสำเร็จ');
      }
      document.getElementById('old-password').value = '';
      document.getElementById('new-password').value = '';
      document.getElementById('confirm-password').value = '';
    } else {
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: json.error || 'เปลี่ยนรหัสผ่านไม่สำเร็จ' });
      } else {
        alert(json.error || 'เปลี่ยนรหัสผ่านไม่สำเร็จ');
      }
    }
  } catch (err) {
    if (window.Swal) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
    } else {
      alert('เกิดข้อผิดพลาด');
    }
  }
}
</script>


<?php require_once __DIR__ . '/plugin/footer.php'; ?>