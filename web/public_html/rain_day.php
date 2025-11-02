<?php
require_once __DIR__ . '/plugin/header_user.php';

$url = "https://ews.dwr.go.th/website/webservice/rain_daily.php?uid=arthittakun123&upass=Arthit0987944735&dmode=1&dtype=2";
$context = stream_context_create([
    "http" => ["method" => "GET", "timeout" => 30]
]);
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);
?>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
/* Simple table styling */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
   
    color: black;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    text-align: center;
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.page-header h1 i {
    font-size: 32px;
    color: #000000ff;
}

.info-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.info-card h3 {
    margin: 0 0 8px 0;
    color: #1976d2;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card p {
    margin: 0;
    font-size: 15px;
    font-weight: bold;
    color: #333;
}

.filter-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    border: 1px solid #e0e0e0;
}

.filter-section h3 {
    margin: 0 0 20px 0;
    color: #1976d2;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.filter-item {
    display: flex;
    flex-direction: column;
}

.filter-item label {
    font-weight: 500;
    margin-bottom: 5px;
    color: #555;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-item select {
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: border-color 0.2s ease;
}

.filter-item select:focus {
    outline: none;
    border-color: #1976d2;
}

.table-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.table-header {
    background: linear-gradient(135deg, #1976d2, #42a5f5);
    color: white;
    padding: 20px 25px;
}

.table-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-container {
    padding: 0;
    overflow-x: auto;
    scrollbar-width: none; /* Firefox */
}

.table-container::-webkit-scrollbar {
    display: none; /* Chrome, Safari */
}

.simple-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.simple-table th {
    background: #f8f9fa;
    padding: 15px 10px;
    text-align: center;
    border: 1px solid #e0e0e0;
    font-weight: 600;
    color: #555;
    font-size: 14px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.simple-table td {
    padding: 12px 10px;
    text-align: center;
    border: 1px solid #e0e0e0;
    font-size: 14px;
}

.simple-table tbody tr:nth-child(even) {
    background: #f9f9f9;
}

.simple-table tbody tr:hover {
    background: #e3f2fd;
    transition: background-color 0.2s ease;
}

/* DataTables styling */
.dataTables_wrapper {
    padding: 25px;
}

.dataTables_filter {
    margin-bottom: 15px;
}

.dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    justify-content: flex-end;
}

.dataTables_filter input {
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.dataTables_filter input:focus {
    outline: none;
    border-color: #1976d2;
}

.dataTables_length select {
    padding: 8px 10px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    margin: 0 5px;
}

.dataTables_length {
    float: left !important;
    margin-top: 10px !important;
}


.dataTables_paginate .paginate_button {
    padding: 8px 12px !important;
    margin: 0 2px !important;
    border: 2px solid #e0e0e0 !important;
    background: white !important;
    color: #555 !important;
    text-decoration: none !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background: #f5f5f5 !important;
    border-color: #1976d2 !important;
    color: #1976d2 !important;
}

.dataTables_paginate .paginate_button.current {
    background: #1976d2 !important;
    color: white !important;
    border-color: #1976d2 !important;
}

.dataTables_paginate .paginate_button.disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}

/* Pagination alignment */
.dataTables_paginate {
    text-align: right !important;
    float: right !important;
}

.dataTables_info {
    float: left !important;
}

.dataTables_wrapper::after {
    content: "";
    display: table;
    clear: both;
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }
    
    .page-header h1 {
        font-size: 22px;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .info-section {
        grid-template-columns: 1fr;
    }
    
    .simple-table {
        font-size: 12px;
    }
    
    .simple-table th,
    .simple-table td {
        padding: 8px 6px;
    }
}
</style>

<div class="container">
    <div class="page-header">
        <h1>
            <i class="fas fa-cloud-rain"></i>
            ข้อมูลปริมาณน้ำฝน 12 ชั่วโมง
        </h1>
    </div>
    
    <div class="info-section">
        <?php if (isset($data['date'])): ?>
            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> วันที่</h3>
                <p><?= $data['date'] ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($data['numstation'])): ?>
            <div class="info-card">
                <h3><i class="fas fa-broadcast-tower"></i> จำนวนสถานี</h3>
                <p><?= number_format($data['numstation']) ?> สถานี</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($data['department'])): ?>
            <div class="info-card">
                <h3><i class="fas fa-building"></i> หน่วยงาน</h3>
                <p><?= $data['department'] ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="filter-section">
        <h3><i class="fas fa-filter"></i> ตัวกรองข้อมูล</h3>
        <div class="filter-grid">
            <div class="filter-item">
                <label><i class="fas fa-map-marked-alt"></i> จังหวัด:</label>
                <select id="province-filter">
                    <option value="">ทั้งหมด</option>
                </select>
            </div>
            
            <div class="filter-item">
                <label><i class="fas fa-city"></i> อำเภอ:</label>
                <select id="district-filter">
                    <option value="">ทั้งหมด</option>
                </select>
            </div>
            
            <div class="filter-item">
                <label><i class="fas fa-map-pin"></i> ตำบล:</label>
                <select id="subdistrict-filter">
                    <option value="">ทั้งหมด</option>
                </select>
            </div>
            
            <div class="filter-item">
                <label><i class="fas fa-home"></i> หมู่บ้าน:</label>
                <select id="village-filter">
                    <option value="">ทั้งหมด</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="table-section">
        <div class="table-header">
            <h3><i class="fas fa-table"></i> ตารางข้อมูลปริมาณน้ำฝน</h3>
        </div>
        <div class="table-container">
            <table id="rain-table" class="simple-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-list-ol"></i> ลำดับ</th>
                        <th><i class="fas fa-map-marked-alt"></i> จังหวัด</th>
                        <th><i class="fas fa-city"></i> อำเภอ</th>
                        <th><i class="fas fa-map-pin"></i> ตำบล</th>
                        <th><i class="fas fa-home"></i> หมู่บ้าน</th>
                        <th><i class="fas fa-cloud-rain"></i> ฝน 12 ชม. (mm)</th>
                        <th><i class="fas fa-cloud-showers-heavy"></i> ฝนรายวัน (07:00) (mm)</th>
                        <th><i class="fas fa-thermometer-half"></i> อุณหภูมิ (°C)</th>
                        <th><i class="fas fa-water"></i> ระดับน้ำ (m)</th>
                        <th><i class="fas fa-seedling"></i> ความชื้นดิน</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($data && isset($data['station'])) {
                    // แสดงเชียงรายก่อน
                    foreach ($data['station'] as $station) {
                        if ($station['province'] === 'เชียงราย') {
                            echo "<tr>";
                            echo "<td>{$station['order']}</td>";
                            echo "<td>{$station['province']}</td>";
                            echo "<td>{$station['district']}</td>";
                            echo "<td>{$station['subdistrict']}</td>";
                            echo "<td>{$station['village']}</td>";
                            echo "<td>{$station['rain12h']}</td>";
                            echo "<td>{$station['rain07h']}</td>";
                            echo "<td>{$station['temp']}</td>";
                            echo "<td>{$station['wl']}</td>";
                            echo "<td>{$station['soil']}</td>";
                            echo "</tr>";
                        }
                    }
                    // แสดงจังหวัดอื่น ๆ
                    foreach ($data['station'] as $station) {
                        if ($station['province'] !== 'เชียงราย') {
                            echo "<tr>";
                            echo "<td>{$station['order']}</td>";
                            echo "<td>{$station['province']}</td>";
                            echo "<td>{$station['district']}</td>";
                            echo "<td>{$station['subdistrict']}</td>";
                            echo "<td>{$station['village']}</td>";
                            echo "<td>{$station['rain12h']}</td>";
                            echo "<td>{$station['rain07h']}</td>";
                            echo "<td>{$station['temp']}</td>";
                            echo "<td>{$station['wl']}</td>";
                            echo "<td>{$station['soil']}</td>";
                            echo "</tr>";
                        }
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#rain-table').DataTable({
        pageLength: 25,
        order: [[0, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        }
    });
    
    // Build filter options
    var provinces = new Set();
    var districts = new Set();
    var subdistricts = new Set();
    var villages = new Set();
    
    var provinceDistrictMap = {};
    var districtSubdistrictMap = {};
    var subdistrictVillageMap = {};
    
    table.rows().every(function() {
        var data = this.data();
        var province = data[1];
        var district = data[2];
        var subdistrict = data[3];
        var village = data[4];
        
        provinces.add(province);
        districts.add(district);
        subdistricts.add(subdistrict);
        villages.add(village);
        
        // Build mapping
        if (!provinceDistrictMap[province]) provinceDistrictMap[province] = new Set();
        provinceDistrictMap[province].add(district);
        
        if (!districtSubdistrictMap[district]) districtSubdistrictMap[district] = new Set();
        districtSubdistrictMap[district].add(subdistrict);
        
        if (!subdistrictVillageMap[subdistrict]) subdistrictVillageMap[subdistrict] = new Set();
        subdistrictVillageMap[subdistrict].add(village);
    });
    
    // Populate province filter
    provinces.forEach(function(province) {
        $('#province-filter').append('<option value="' + province + '">' + province + '</option>');
    });
    
    // Set default to เชียงราย
    $('#province-filter').val('เชียงราย');
    table.columns(1).search('เชียงราย').draw();
    updateFilters();
    
    // Update filter functions
    function updateFilters() {
        updateDistricts();
        updateSubdistricts();
        updateVillages();
    }
    
    function updateDistricts() {
        var selectedProvince = $('#province-filter').val();
        var availableDistricts = selectedProvince ? provinceDistrictMap[selectedProvince] || new Set() : districts;
        
        $('#district-filter').html('<option value="">ทั้งหมด</option>');
        availableDistricts.forEach(function(district) {
            $('#district-filter').append('<option value="' + district + '">' + district + '</option>');
        });
    }
    
    function updateSubdistricts() {
        var selectedDistrict = $('#district-filter').val();
        var availableSubdistricts = selectedDistrict ? districtSubdistrictMap[selectedDistrict] || new Set() : subdistricts;
        
        $('#subdistrict-filter').html('<option value="">ทั้งหมด</option>');
        availableSubdistricts.forEach(function(subdistrict) {
            $('#subdistrict-filter').append('<option value="' + subdistrict + '">' + subdistrict + '</option>');
        });
    }
    
    function updateVillages() {
        var selectedSubdistrict = $('#subdistrict-filter').val();
        var availableVillages = selectedSubdistrict ? subdistrictVillageMap[selectedSubdistrict] || new Set() : villages;
        
        $('#village-filter').html('<option value="">ทั้งหมด</option>');
        availableVillages.forEach(function(village) {
            $('#village-filter').append('<option value="' + village + '">' + village + '</option>');
        });
    }
    
    function applyFilters() {
        var province = $('#province-filter').val();
        var district = $('#district-filter').val();
        var subdistrict = $('#subdistrict-filter').val();
        var village = $('#village-filter').val();
        
        table.columns(1).search(province);
        table.columns(2).search(district);
        table.columns(3).search(subdistrict);
        table.columns(4).search(village);
        table.draw();
    }
    
    // Filter event handlers
    $('#province-filter').on('change', function() {
        applyFilters();
        updateFilters();
    });
    
    $('#district-filter').on('change', function() {
        applyFilters();
        updateSubdistricts();
        updateVillages();
    });
    
    $('#subdistrict-filter').on('change', function() {
        applyFilters();
        updateVillages();
    });
    
    $('#village-filter').on('change', applyFilters);
});
</script>

<?php
require_once __DIR__ . '/plugin/footer_user.php';
?>
