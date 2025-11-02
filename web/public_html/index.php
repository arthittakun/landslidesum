
<?php
require_once __DIR__ . '/plugin/header_user.php';
?>
<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
  /* Modal Styles */
  .modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
  }

  .modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 800px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
  }

  .modal-header {
    padding: 15px 20px;
    background-color: #3498db;
    color: white;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
  }

  .modal-body {
    padding: 20px;
  }

  .close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
  }

  .close:hover,
  .close:focus {
    color: #f1f1f1;
  }

  #map {
    width: 100%;
    height: 450px;
    border-radius: 8px;
  }

  .coord-link {
    color: #3498db;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.3s;
  }

  .coord-link:hover {
    color: #2980b9;
  }

  .map-layer-control {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    background: white;
    padding: 8px 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex;
    gap: 8px;
  }

  .map-layer-btn {
    padding: 6px 12px;
    border: 2px solid #3498db;
    background: white;
    color: #3498db;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s;
  }

  .map-layer-btn:hover {
    background: #3498db;
    color: white;
  }

  .map-layer-btn.active {
    background: #3498db;
    color: white;
  }
</style>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
  <!-- หมู่บ้านที่มีความเสี่ยง -->
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-center mb-6">
      <i class="fas fa-exclamation-triangle" style="margin-right: 10px; color: #e74c3c;"></i>
      หมู่บ้านที่มีความเสี่ยง
    </h1>
    
    <!-- Loading Spinner for Risk Table -->
    <div id="loadingSpinnerRisk" style="display: none; text-align: center; padding: 2rem;">
      <p>กำลังโหลดข้อมูล...</p>
    </div>
    
    <!-- Risk Location Table -->
    <div style="overflow-x: auto;">
      <table id="riskTable" class="display" style="width: 100%;">
        <thead>
          <tr>
            <th><i class="fas fa-map-marker-alt"></i> สถานที่</th>
            <th><i class="fas fa-globe"></i> พิกัด (Lat, Long)</th>
            <th><i class="fas fa-shield-alt"></i> ระดับความเสี่ยง</th>
            <th><i class="fas fa-info-circle"></i> คำอธิบาย</th>
            <th><i class="fas fa-microchip"></i> อุปกรณ์</th>
            <th><i class="fas fa-calendar-alt"></i> วันที่ล่าสุด</th>
            <th><i class="fas fa-clock"></i> เวลาล่าสุด</th>
          </tr>
        </thead>
        <tbody>
          <!-- Data will be inserted here by JS -->
        </tbody>
      </table>
    </div>
  </div>

  <hr class="my-8 border-gray-300">

  <!-- ข้อมูลสภาพแวดล้อม -->
  <div>
    <h1 class="text-2xl font-bold text-center mb-6">
      <i class="fas fa-seedling" style="margin-right: 10px; color: #16a085;"></i>
      ข้อมูลสภาพแวดล้อม
    </h1>
    
    <!-- Loading Spinner -->
    <div id="loadingSpinner" style="display: none; text-align: center; padding: 2rem;">
      <p>กำลังโหลดข้อมูล...</p>
    </div>
    
    <!-- Data Table -->
    <div style="overflow-x: auto;">
      <table id="envTable" class="display" style="width: 100%;">
        <thead>
          <tr>
            <th><i class="fas fa-map-marker-alt"></i> สถานที่</th>
            <th><i class="fas fa-thermometer-half"></i> อุณหภูมิ (°C)</th>
            <th><i class="fas fa-tint"></i> ความชื้น (%)</th>
            <th><i class="fas fa-cloud-rain"></i> ปริมาณฝน (mm)</th>
            <th><i class="fas fa-wave-square"></i> แรงสั่นสะเทือน</th>
            <th><i class="fas fa-ruler"></i> ระยะห่าง (cm)</th>
            <th><i class="fas fa-seedling"></i> ค่าความชื้นดิน</th>
            <th><i class="fas fa-clock"></i> วันเวลา</th>
          </tr>
        </thead>
        <tbody>
          <!-- Data will be inserted here by JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal สำหรับแสดงแผนที่ -->
<div id="mapModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2><i class="fas fa-map-marked-alt"></i> <span id="modalLocationName">แผนที่</span></h2>
      <span class="close">&times;</span>
    </div>
    <div class="modal-body">
      <div style="position: relative;">
        <div id="map"></div>
        <div class="map-layer-control">
          <button class="map-layer-btn active" id="mapBtnStreet">
            <i class="fas fa-map"></i> แผนที่
          </button>
          <button class="map-layer-btn" id="mapBtnSatellite">
            <i class="fas fa-satellite"></i> ดาวเทียม
          </button>
        </div>
      </div>
      <p style="margin-top: 15px; text-align: center; color: #7f8c8d;">
        <i class="fas fa-map-pin"></i> 
        <strong>พิกัด:</strong> <span id="modalCoords"></span>
      </p>
    </div>
  </div>
</div>

<!-- jQuery & DataTables JS CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<!-- Leaflet (OpenStreetMap) -->
<link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
  let map;
  let marker;
  let streetLayer;
  let satelliteLayer;
  let currentLayer = 'street';

  // ฟังก์ชันแปลง docno YYMMDDHHMM → DD/MM/YY HH:MM
  function formatDocno(docno) {
    if (!docno || docno.length !== 10) return docno ?? '-';
    const yy = docno.substring(0,2);
    const mm = docno.substring(2,4);
    const dd = docno.substring(4,6);
    const hh = docno.substring(6,8);
    const min = docno.substring(8,10);
    return `${dd}/${mm}/${yy} ${hh}:${min}`;
  }

  // ฟังก์ชันแปลงวันที่ YYYY-MM-DD → DD/MM/YYYY
  function formatDate(date) {
    if (!date) return '-';
    const parts = date.split('-');
    if (parts.length !== 3) return date;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }

  // ฟังก์ชันกำหนดสีตามระดับความเสี่ยง
  function getRiskBadge(riskText) {
    if (riskText === 'ความเสี่ยงสูง') {
      return `<span style="background-color: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
        <i class="fas fa-exclamation-circle"></i> ${riskText}
      </span>`;
    } else if (riskText === 'เข้าใกล้อันตราย') {
      return `<span style="background-color: #f39c12; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
        <i class="fas fa-exclamation-triangle"></i> ${riskText}
      </span>`;
    } else {
      return `<span style="background-color: #3498db; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
        <i class="fas fa-info-circle"></i> ${riskText}
      </span>`;
    }
  }

  // ฟังก์ชันสลับ Layer แผนที่
  function switchMapLayer(layerType) {
    if (!map) return;
    
    if (layerType === 'satellite') {
      if (streetLayer) map.removeLayer(streetLayer);
      if (!satelliteLayer) {
        satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
          attribution: 'Tiles &copy; Esri'
        });
      }
      satelliteLayer.addTo(map);
      currentLayer = 'satellite';
      $('#mapBtnSatellite').addClass('active');
      $('#mapBtnStreet').removeClass('active');
    } else {
      if (satelliteLayer) map.removeLayer(satelliteLayer);
      if (!streetLayer) {
        streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
      }
      streetLayer.addTo(map);
      currentLayer = 'street';
      $('#mapBtnStreet').addClass('active');
      $('#mapBtnSatellite').removeClass('active');
    }
  }

  // ฟังก์ชันเปิด Modal แสดงแผนที่ (Leaflet)
  function openMapModal(lat, lng, locationName) {
    const latitude = parseFloat(lat);
    const longitude = parseFloat(lng);
    
    if (isNaN(latitude) || isNaN(longitude)) {
      alert('พิกัดไม่ถูกต้อง');
      return;
    }

    // แสดง Modal
    $('#mapModal').fadeIn();
    $('#modalLocationName').text(locationName || 'แผนที่');
    $('#modalCoords').text(`${lat}, ${lng}`);

    // สร้างแผนที่ด้วย Leaflet
    setTimeout(function() {
      if (!map) {
        map = L.map('map', { zoomControl: true }).setView([latitude, longitude], 13);
        
        // Street Map Layer (OpenStreetMap) - Default
        streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Set up layer control buttons
        $('#mapBtnStreet').on('click', function() {
          switchMapLayer('street');
        });
        
        $('#mapBtnSatellite').on('click', function() {
          switchMapLayer('satellite');
        });
        
      } else {
        map.setView([latitude, longitude], 13);
      }

      // ลบ marker เก่า
      if (marker) {
        map.removeLayer(marker);
      }

      // สร้าง marker ใหม่
      marker = L.circleMarker([latitude, longitude], {
        radius: 10,
        color: '#e74c3c',
        fillColor: '#ff6b6b',
        fillOpacity: 0.8,
        weight: 3
      }).addTo(map);

      // Popup
      const popupContent = `
        <div style="padding: 8px; min-width: 200px;">
          <h3 style="margin: 0 0 8px 0; color: #3498db; font-size: 1.1rem;">
            <i class="fas fa-map-marker-alt"></i> ${locationName || 'สถานที่'}
          </h3>
          <p style="margin: 4px 0; font-size: 0.9rem;">
            <strong>พิกัด:</strong> ${lat}, ${lng}
          </p>
        </div>
      `;
      
      marker.bindPopup(popupContent).openPopup();

      // Refresh map size
      setTimeout(function() {
        map.invalidateSize();
      }, 200);
    }, 100);
  }

  // ปิด Modal
  $('.close, .modal').on('click', function(e) {
    if (e.target === this) {
      $('#mapModal').fadeOut();
    }
  });

  // ทำให้ฟังก์ชัน openMapModal เป็น global
  window.openMapModal = openMapModal;

  // ฟังก์ชันโหลดข้อมูลหมู่บ้านที่มีความเสี่ยง
  function loadRiskData() {
    $('#loadingSpinnerRisk').show();
    
    $.getJSON('/api/environment/critical-bylocation.php', function(res) {
      if (res.success && Array.isArray(res.data)) {
        let rows = '';
        res.data.forEach(function(item) {
          const lat = item.latitude ?? '';
          const lng = item.longtitude ?? '';
          const locationName = item.location_name ?? '-';
          
          // สร้างลิงก์คลิกได้สำหรับพิกัด
          const coordLink = (lat && lng) 
            ? `<a class="coord-link" onclick="openMapModal('${lat}', '${lng}', '${locationName}')">
                <i class="fas fa-map-marker-alt"></i> ${lat}, ${lng}
               </a>`
            : '-';
          
          rows += `<tr>
            <td><strong>${locationName}</strong></td>
            <td>${coordLink}</td>
            <td>${getRiskBadge(item.risk_level_text ?? '-')}</td>
            <td style="max-width: 300px;">${item.risk_description ?? '-'}</td>
            <td>${item.device_list ?? '-'}</td>
            <td>${formatDate(item.latest_alert_date)}</td>
            <td>${item.latest_alert_time ?? '-'}</td>
          </tr>`;
        });
        $('#riskTable tbody').html(rows);
        
        // Initialize DataTable
        if ($.fn.DataTable.isDataTable('#riskTable')) {
          $('#riskTable').DataTable().destroy();
        }
        
        $('#riskTable').DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
          },
          order: [[5, 'desc']], // เรียงตามวันที่ล่าสุด
          pageLength: 10,
          scrollX: true
        });
        
      } else {
        $('#riskTable tbody').html(`
          <tr>
            <td colspan="7" style="text-align: center; padding: 2rem;">ไม่พบข้อมูล</td>
          </tr>
        `);
      }
    }).fail(function() {
      $('#riskTable tbody').html(`
        <tr>
          <td colspan="7" style="text-align: center; padding: 2rem; color: red;">เกิดข้อผิดพลาดในการโหลดข้อมูล</td>
        </tr>
      `);
    }).always(function() {
      $('#loadingSpinnerRisk').hide();
    });
  }

  // ฟังก์ชันโหลดข้อมูลสภาพแวดล้อม
  function loadData() {
    $('#loadingSpinner').show();
    
    $.getJSON('/api/environment/getenvironmentAll', function(res) {
      if (res.status === 'success' && Array.isArray(res.data)) {
        let rows = '';
        res.data.forEach(function(item) {
          rows += `<tr>
            <td>${item.location_name ?? '-'}</td>
            <td>${item.temp ?? '-'}</td>
            <td>${item.humid ?? '-'}</td>
            <td>${item.rain ?? '-'}</td>
            <td>${item.vibration ?? '-'}</td>
            <td>${item.distance ?? '-'}</td>
            <td>${item.soil ?? '-'}</td>
            <td>${formatDocno(item.docno)}</td>
          </tr>`;
        });
        $('#envTable tbody').html(rows);
        
        // Initialize or destroy and reinitialize DataTable
        if ($.fn.DataTable.isDataTable('#envTable')) {
          $('#envTable').DataTable().destroy();
        }
        
        $('#envTable').DataTable({
          language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
          },
          order: [[7, 'desc']], // เรียงตามวันเวลาล่าสุด
          pageLength: 10,
          scrollX: true
        });
        
      } else {
        $('#envTable tbody').html(`
          <tr>
            <td colspan="8" style="text-align: center; padding: 2rem;">ไม่พบข้อมูล</td>
          </tr>
        `);
      }
    }).fail(function() {
      $('#envTable tbody').html(`
        <tr>
          <td colspan="8" style="text-align: center; padding: 2rem; color: red;">เกิดข้อผิดพลาดในการโหลดข้อมูล</td>
        </tr>
      `);
    }).always(function() {
      $('#loadingSpinner').hide();
    });
  }

  // โหลดข้อมูลทั้งสองตารางครั้งแรก
  loadRiskData();
  loadData();
});
</script>

<?php
require_once __DIR__ . '/plugin/footer_user.php';
?>