<?php
require_once __DIR__ . '/plugin/header_user.php';
require_once __DIR__ . '/../database/table_device.php';

$deviceTable = new Table_device();
$locations = $deviceTable->getDevicesGroupedByLocationDetailed();
?>
<style>
.map-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}
.map-title {
  text-align: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  color: #0ea5e9;
}
#device-map {
z-index: 1;
  width: 100%;
  height: 60vh;
  min-height: 320px;
  max-height: 70vh;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  border: 1px solid #e0e0e0;
}
.device-list {
  margin: 2rem auto 0 auto;
  max-width: 900px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 1.5rem;
}
.device-list h2 {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 1rem;
  text-align: center;
}
.device-list-table {
  width: 100%;
  border-collapse: collapse;
}
.device-list-table th, .device-list-table td {
  border: 1px solid #e0e0e0;
  padding: 10px;
  text-align: center;
}
.device-list-table th {
  background: #f8f9fa;
  font-weight: 600;
}
.device-list-table tbody tr:nth-child(even) {
  background: #f9f9f9;
}
</style>

<div class="map-container">
  <h1 class="map-title">🗺️ แผนที่แสดงจำนวนอุปกรณ์ที่ติดตั้งตามสถานที่ต่างๆ</h1>
  <div id="device-map"></div>
</div>

<div class="device-list">
  <h2>รายชื่อสถานที่และจำนวนอุปกรณ์</h2>
  <table class="device-list-table">
    <thead>
      <tr>
        <th>ชื่อสถานที่</th>
        <th>ละติจูด</th>
        <th>ลองจิจูด</th>
        <th>จำนวนอุปกรณ์</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($locations as $loc): ?>
        <tr>
          <td><?= htmlspecialchars($loc['location_name']) ?></td>
          <td><?= htmlspecialchars($loc['latitude']) ?></td>
          <td><?= htmlspecialchars($loc['longtitude']) ?></td>
          <td><?= intval($loc['total_devices']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Leaflet -->
<link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var map = L.map('device-map').setView([19.5, 100.5], 7);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  var locations = <?php echo json_encode($locations, JSON_UNESCAPED_UNICODE); ?>;

  locations.forEach(function(loc) {
    if (!loc.latitude || !loc.longtitude) return;
    var lat = parseFloat(loc.latitude);
    var lng = parseFloat(loc.longtitude);
    if (isNaN(lat) || isNaN(lng)) return;
    var marker = L.marker([lat, lng], { zIndexOffset: -1000 }).addTo(map); // zIndex ต่ำ
    var popup = `<strong>${loc.location_name}</strong><br>จำนวนอุปกรณ์: <b>${loc.total_devices}</b>`;
    marker.bindPopup(popup);
  });
});
</script>
<?php
require_once __DIR__ . '/plugin/footer_user.php';
?>
