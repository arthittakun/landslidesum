<?php
include_once 'plugin/header.php';
require_once __DIR__ . '/../database/connect.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize = isset($_GET['page_size']) ? max(1, min(60, (int)$_GET['page_size'])) : 24;

// Fetch data directly from database
$images = [];
$total = 0;
try {
  $db = new database();
  $pdo = $db->getConnection();
  
  // Get total count
  $countSql = "SELECT COUNT(*) as total 
               FROM lnd_environment e
               LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
               LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
               WHERE e.img_path IS NOT NULL AND e.img_path != ''
               ORDER BY e.datekey DESC, e.timekey DESC";
  
  $countStmt = $pdo->query($countSql);
  $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
  
  // Calculate pagination
  $totalPages = max(1, (int)ceil($total / $pageSize));
  $page = min($page, $totalPages);
  $offset = ($page - 1) * $pageSize;
  
  // Get paginated data
  $sql = "SELECT e.device_id, e.datekey, e.timekey, e.img_path,
             COALESCE(d.device_name, e.device_id) as device_name, 
             COALESCE(l.location_name, 'ไม่ระบุตำแหน่ง') as location_name
      FROM lnd_environment e
      LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
      LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
      WHERE e.img_path IS NOT NULL AND e.img_path != ''
      ORDER BY e.datekey DESC, e.timekey DESC
      LIMIT $pageSize OFFSET $offset";
  
  $stmt = $pdo->query($sql);
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
} catch (Throwable $e) {
  // Silent error handling for production
  error_log("Gallery database error: " . $e->getMessage());
  $totalPages = 1;
}
?>

<style>
.gallery-card { 
  position: relative; 
  overflow: hidden; 
  border: 1px solid var(--border-color); 
  background: var(--bg-card); 
  border-radius: 10px;
  transition: all 0.2s ease;
}
.gallery-card:hover { 
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.gallery-card:hover img { transform: scale(1.05); }
.gallery-card img { 
  transition: transform 0.25s ease; 
  width: 100%;
  height: 160px;
  object-fit: cover;
}
.modal-backdrop { 
  position: fixed; 
  inset: 0; 
  background: rgba(0,0,0,.8); 
  display: none; 
  align-items: center; 
  justify-content: center; 
  z-index: 1000;
  backdrop-filter: blur(4px);
}
.modal-backdrop.show { display: flex; }
.modal { 
  background: var(--bg-card); 
  color: var(--text-primary); 
  max-width: 90vw; 
  max-height: 90vh; 
  border-radius: 12px; 
  overflow: hidden; 
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
  border: 1px solid var(--border-color);
}
.modal img { 
  max-width: 90vw; 
  max-height: 70vh; 
  object-fit: contain;
  border-radius: 8px;
}
.modal-caption { 
  padding: 1rem 1.25rem; 
  font-size: 0.95rem; 
  background: var(--bg-secondary); 
  border-top: 1px solid var(--border-color);
}
.modal-caption:first-of-type {
  border-top: none;
}
</style>

<main class="content-area flex-1 overflow-y-auto p-4 sm:p-6">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">แกลเลอรี่รูปภาพ</h1>
    <p class="text-gray-600 dark:text-gray-400">รูปภาพล่าสุดจากอุปกรณ์ในระบบ</p>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
    <?php if (empty($images)): ?>
      <div class="col-span-full text-center py-12">
        <div class="text-gray-500 dark:text-gray-400">
          <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-lg font-medium">ไม่พบรูปภาพ</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($images as $row): 
        // Use image path from database, fallback to default path if needed
        if (!empty($row['img_path'])) {
          $src = $row['img_path'];
          // If path doesn't start with http or /, prepend /image/
          if (!preg_match('/^(https?:\/\/|\/)/i', $src)) {
            $src = '/image/' . $src;
          }
        } else {
          // Fallback: construct filename from database data
          $filename = "device_{$row['device_id']}_{$row['datekey']}_" . str_replace(':', '-', $row['timekey']) . ".jpg";
          $src = '/image/' . $filename;
        }
        
        // Create caption with device name, location, and datetime
        $deviceName = !empty($row['device_name']) ? $row['device_name'] : $row['device_id'];
        $locationName = !empty($row['location_name']) ? $row['location_name'] : 'ไม่ระบุตำแหน่ง';
        $datetime = $row['datekey'] . ' ' . $row['timekey'];
        
        $caption = "{$deviceName} • {$locationName} • {$datetime}";
      ?>
        <div class="gallery-card">
          <button class="w-full text-left" onclick="openLightbox('<?= htmlspecialchars($src) ?>','<?= htmlspecialchars($caption) ?>')">
            <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($caption) ?>" class="w-full h-32 object-cover"/>
          </button>
          <div class="p-3">
            <div class="text-sm font-medium text-gray-900 dark:text-white truncate" title="<?= htmlspecialchars($caption) ?>">
              <?= htmlspecialchars($caption) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="mt-8 flex items-center justify-between">
      <div class="text-sm text-gray-600 dark:text-gray-400">
        แสดง <?= ($offset + 1) ?> - <?= min($offset + $pageSize, $total) ?> จากทั้งหมด <?= $total ?> รูป
      </div>
      <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page - 1 ?>&page_size=<?= $pageSize ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            ← ก่อนหน้า
          </a>
        <?php endif; ?>
        <span class="text-sm text-gray-600 dark:text-gray-400">หน้า <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?= $page + 1 ?>&page_size=<?= $pageSize ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
            ถัดไป →
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</main>

<div id="lightbox" class="modal-backdrop" onclick="closeLightbox(event)">
  <div class="modal" onclick="event.stopPropagation()">
    <img id="lightbox-img" src="" alt="" />
    <div class="modal-caption" id="lightbox-caption"></div>
  </div>
</div>

<script>
function openLightbox(src, caption){
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox-caption').textContent = caption || '';
  document.getElementById('lightbox').classList.add('show');
}

function closeLightbox(ev){ 
  document.getElementById('lightbox').classList.remove('show'); 
}

// Close lightbox with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeLightbox();
  }
});
</script>

<?php include_once 'plugin/footer.php'; ?>