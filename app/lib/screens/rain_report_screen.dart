import 'package:flutter/material.dart';
import '../models/rain_report_response.dart';
import '../services/rain_report_service.dart';
import '../theme/app_theme.dart';

class RainReportScreen extends StatefulWidget {
  const RainReportScreen({super.key});

  @override
  State<RainReportScreen> createState() => _RainReportScreenState();
}

class _RainReportScreenState extends State<RainReportScreen> {
  RainReportResponse? _rainReport;
  List<RainStation> _filteredStations = [];
  bool _isLoading = true;
  String? _errorMessage;

  // Filter variables
  String? _selectedProvince;
  String? _selectedDistrict;
  String? _selectedSubdistrict;
  String? _selectedVillage;

  // Available filter options
  List<String> _provinces = [];
  List<String> _districts = [];
  List<String> _subdistricts = [];
  List<String> _villages = [];

  @override
  void initState() {
    super.initState();
    _loadRainReport();
  }

  Future<void> _loadRainReport() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final rainReport = await RainReportService.getRainReport();
      
      if (rainReport != null) {
        setState(() {
          _rainReport = rainReport;
          _filteredStations = rainReport.stations;
          _isLoading = false;
        });
        _updateFilterOptions();
      } else {
        setState(() {
          _errorMessage = 'ไม่สามารถโหลดข้อมูลได้';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'เกิดข้อผิดพลาด: $e';
        _isLoading = false;
      });
    }
  }

  void _updateFilterOptions() {
    if (_rainReport == null) return;

    // Get unique provinces
    _provinces = _rainReport!.stations
        .map((station) => station.province)
        .toSet()
        .toList()
      ..sort();

    // Reset other filters
    _districts.clear();
    _subdistricts.clear();
    _villages.clear();
    _selectedDistrict = null;
    _selectedSubdistrict = null;
    _selectedVillage = null;

    setState(() {});
  }

  void _updateDistrictOptions() {
    if (_selectedProvince == null) return;

    _districts = _rainReport!.stations
        .where((station) => station.province == _selectedProvince)
        .map((station) => station.district)
        .toSet()
        .toList()
      ..sort();

    // Reset other filters
    _subdistricts.clear();
    _villages.clear();
    _selectedDistrict = null;
    _selectedSubdistrict = null;
    _selectedVillage = null;

    setState(() {});
  }

  void _updateSubdistrictOptions() {
    if (_selectedProvince == null || _selectedDistrict == null) return;

    _subdistricts = _rainReport!.stations
        .where((station) =>
            station.province == _selectedProvince &&
            station.district == _selectedDistrict)
        .map((station) => station.subdistrict)
        .toSet()
        .toList()
      ..sort();

    // Reset other filters
    _villages.clear();
    _selectedSubdistrict = null;
    _selectedVillage = null;

    setState(() {});
  }

  void _updateVillageOptions() {
    if (_selectedProvince == null ||
        _selectedDistrict == null ||
        _selectedSubdistrict == null) return;

    _villages = _rainReport!.stations
        .where((station) =>
            station.province == _selectedProvince &&
            station.district == _selectedDistrict &&
            station.subdistrict == _selectedSubdistrict)
        .map((station) => station.village)
        .toSet()
        .toList()
      ..sort();

    _selectedVillage = null;
    setState(() {});
  }

  void _applyFilters() {
    if (_rainReport == null) return;

    List<RainStation> filtered = _rainReport!.stations;

    if (_selectedProvince != null) {
      filtered = filtered.where((station) => station.province == _selectedProvince).toList();
    }
    if (_selectedDistrict != null) {
      filtered = filtered.where((station) => station.district == _selectedDistrict).toList();
    }
    if (_selectedSubdistrict != null) {
      filtered = filtered.where((station) => station.subdistrict == _selectedSubdistrict).toList();
    }
    if (_selectedVillage != null) {
      filtered = filtered.where((station) => station.village == _selectedVillage).toList();
    }

    setState(() {
      _filteredStations = filtered;
    });
  }

  void _clearFilters() {
    setState(() {
      _selectedProvince = null;
      _selectedDistrict = null;
      _selectedSubdistrict = null;
      _selectedVillage = null;
      _filteredStations = _rainReport?.stations ?? [];
    });
    _updateFilterOptions();
  }

  @override
  Widget build(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('รายงานปริมาณน้ำฝน'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadRainReport,
            tooltip: 'รีเฟรชข้อมูล',
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: isDarkMode
                ? [AppTheme.darkBackground, AppTheme.darkSurface]
                : [AppTheme.brandPrimary.withOpacity(0.05), Colors.white],
          ),
        ),
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? _buildErrorWidget()
                : ListView(
                    padding: EdgeInsets.zero,
                    children: [
                      _buildHeaderSection(),
                      _buildFilterSection(),
                      _buildStationsList(),
                    ],
                  ),
      ),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 64,
            color: AppTheme.errorColor,
          ),
          const SizedBox(height: 16),
          Text(
            _errorMessage!,
            style: TextStyle(
              fontSize: 16,
              color: AppTheme.errorColor,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadRainReport,
            child: const Text('ลองใหม่'),
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderSection() {
    if (_rainReport == null) return const SizedBox.shrink();
    
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: isDarkMode 
                ? Colors.black.withOpacity(0.3)
                : Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: isDarkMode 
            ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
            : null,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.water_drop,
                color: AppTheme.brandPrimary,
                size: 24,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  _rainReport!.title,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.brandPrimary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            _rainReport!.dateText,
            style: TextStyle(
              fontSize: 14,
              color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.darkGrey,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(
                Icons.location_on,
                size: 16,
                color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.darkGrey,
              ),
              const SizedBox(width: 4),
              Text(
                'จำนวนสถานี: ${_rainReport!.count}',
                style: TextStyle(
                  fontSize: 14,
                  color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.darkGrey,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildFilterSection() {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: isDarkMode 
                ? Colors.black.withOpacity(0.3)
                : Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: isDarkMode 
            ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
            : null,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.filter_list,
                color: AppTheme.brandPrimary,
                size: 20,
              ),
              const SizedBox(width: 8),
              Text(
                'ตัวกรองข้อมูล',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.brandPrimary,
                ),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: _clearFilters,
                icon: const Icon(Icons.clear, size: 16),
                label: const Text('ล้างตัวกรอง'),
                style: TextButton.styleFrom(
                  foregroundColor: AppTheme.errorColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildFilterRow('จังหวัด', _provinces, _selectedProvince, (value) {
            setState(() {
              _selectedProvince = value;
            });
            _updateDistrictOptions();
            _applyFilters();
          }),
          if (_districts.isNotEmpty)
            _buildFilterRow('อำเภอ', _districts, _selectedDistrict, (value) {
              setState(() {
                _selectedDistrict = value;
              });
              _updateSubdistrictOptions();
              _applyFilters();
            }),
          if (_subdistricts.isNotEmpty)
            _buildFilterRow('ตำบล', _subdistricts, _selectedSubdistrict, (value) {
              setState(() {
                _selectedSubdistrict = value;
              });
              _updateVillageOptions();
              _applyFilters();
            }),
          if (_villages.isNotEmpty)
            _buildFilterRow('หมู่บ้าน', _villages, _selectedVillage, (value) {
              setState(() {
                _selectedVillage = value;
              });
              _applyFilters();
            }),
        ],
      ),
    );
  }

  Widget _buildFilterRow(String label, List<String> options, String? selectedValue, Function(String?) onChanged) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          SizedBox(
            width: 80,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.darkGrey,
              ),
            ),
          ),
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: isDarkMode ? AppTheme.darkCard : Colors.white,
                border: Border.all(
                  color: isDarkMode 
                      ? AppTheme.brandPrimary.withOpacity(0.5)
                      : AppTheme.brandPrimary.withOpacity(0.3)
                ),
                borderRadius: BorderRadius.circular(8),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: selectedValue,
                  hint: Text(
                    'เลือก$label',
                    style: TextStyle(
                      color: isDarkMode ? AppTheme.textLight.withOpacity(0.6) : AppTheme.darkGrey,
                    ),
                  ),
                  isExpanded: true,
                  dropdownColor: isDarkMode ? AppTheme.darkCard : Colors.white,
                  items: [
                    DropdownMenuItem<String>(
                      value: null,
                      child: Text(
                        'ทั้งหมด',
                        style: TextStyle(
                          color: isDarkMode ? AppTheme.textLight : AppTheme.darkGrey,
                        ),
                      ),
                    ),
                    ...options.map((option) => DropdownMenuItem<String>(
                      value: option,
                      child: Text(
                        option,
                        style: TextStyle(
                          color: isDarkMode ? AppTheme.textLight : AppTheme.darkGrey,
                        ),
                      ),
                    )),
                  ],
                  onChanged: onChanged,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStationsList() {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    if (_filteredStations.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.search_off,
              size: 64,
              color: isDarkMode 
                  ? AppTheme.textLight.withOpacity(0.3)
                  : AppTheme.darkGrey.withOpacity(0.5),
            ),
            const SizedBox(height: 16),
            Text(
              'ไม่พบข้อมูลที่ตรงกับตัวกรอง',
              style: TextStyle(
                fontSize: 16,
                color: isDarkMode 
                    ? AppTheme.textLight.withOpacity(0.6)
                    : AppTheme.darkGrey,
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      children: _filteredStations.map((station) => _buildStationCard(station)).toList(),
    );
  }

  Widget _buildStationCard(RainStation station) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: isDarkMode 
                ? Colors.black.withOpacity(0.3)
                : Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: isDarkMode 
            ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
            : null,
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header row
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.brandPrimary,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '#${station.order}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    station.id,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.brandPrimary,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            
            // Location info
            Row(
              children: [
                Icon(
                  Icons.location_on,
                  size: 16,
                  color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.darkGrey,
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    '${station.village}, ${station.subdistrict}, ${station.district}, ${station.province}',
                    style: TextStyle(
                      fontSize: 14,
                      color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.darkGrey,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            
            // Data grid
            Row(
              children: [
                Expanded(
                  child: _buildDataItem(
                    'ฝน 12 ชม.',
                    '${station.rain12h} mm',
                    Icons.water_drop,
                    AppTheme.brandPrimary,
                  ),
                ),
                Expanded(
                  child: _buildDataItem(
                    'ฝนรายวัน',
                    '${station.rain07h} mm',
                    Icons.wb_sunny,
                    AppTheme.warningColor,
                  ),
                ),
                Expanded(
                  child: _buildDataItem(
                    'อุณหภูมิ',
                    '${station.temp}°C',
                    Icons.thermostat,
                    AppTheme.errorColor,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            
            // Additional data
            Row(
              children: [
                Expanded(
                  child: _buildDataItem(
                    'ระดับน้ำ',
                    station.wl == 'N/A' ? 'ไม่มีข้อมูล' : '${station.wl} m',
                    Icons.waves,
                    AppTheme.secondaryGreen,
                  ),
                ),
                Expanded(
                  child: _buildDataItem(
                    'ความชื้นดิน',
                    station.soil == 'N/A' ? 'ไม่มีข้อมูล' : '${station.soil}%',
                    Icons.grass,
                    AppTheme.darkGreen,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDataItem(String label, String value, IconData icon, Color color) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: isDarkMode 
            ? color.withOpacity(0.15)
            : color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isDarkMode 
              ? color.withOpacity(0.5)
              : color.withOpacity(0.3)
        ),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              color: color,
              fontWeight: FontWeight.w500,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: TextStyle(
              fontSize: 12,
              color: color,
              fontWeight: FontWeight.w600,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
