import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import '../theme/app_theme.dart';
import '../services/radar_service.dart';
import '../widgets/custom_bottom_navigation_bar.dart';
import '../controllers/navigation_controller.dart';
import 'home_screen.dart';
import 'menu_screen.dart';
import 'device_status_screen.dart';
import 'notification_screen.dart';
import 'settings_screen.dart';

class RadarScreen extends StatefulWidget {
  const RadarScreen({super.key});

  @override
  State<RadarScreen> createState() => _RadarScreenState();
}

class _RadarScreenState extends State<RadarScreen> {
  final MapController _mapController = MapController();
  RadarData? _radarData;
  bool _isLoading = true;
  String? _errorMessage;
  LatLng _currentLocation = const LatLng(13.7563, 100.5018); // Default to Bangkok
  int _currentIndex = 1; // Default to Menu tab since we're in radar from menu

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
    _loadRadarData();
  }

  Future<void> _getCurrentLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.whileInUse ||
          permission == LocationPermission.always) {
        Position position = await Geolocator.getCurrentPosition(
            desiredAccuracy: LocationAccuracy.high);
        setState(() {
          _currentLocation = LatLng(position.latitude, position.longitude);
        });
        _mapController.move(_currentLocation, 8.0);
      }
    } catch (e) {
      // Error handling
    }
  }

  Future<void> _loadRadarData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final data = await RadarService.getRadarData();
    
    setState(() {
      if (data != null) {
        _radarData = data;
        _errorMessage = null;
      } else {
        _errorMessage = 'ไม่สามารถโหลดข้อมูลเรดาร์ได้';
      }
      _isLoading = false;
    });
  }

  // คำนวณ scale ตาม zoom (เหมือน HTML)
  int _getTileScale() {
    try {
      final zoom = _mapController.camera.zoom;
      if (zoom < 7) return 2;
      else if (zoom < 10) return 1;
      else return 0;
    } catch (e) {
      return 1; // default scale
    }
  }

  // อัปเดต layers เมื่อ zoom เปลี่ยน - ใช้เฟรมล่าสุดเสมอ
  String? _getCurrentRadarUrl() {
    if (_radarData == null || _radarData!.frames.isEmpty) return null;
    
    // ใช้เฟรมล่าสุดเสมอ
    final frame = _radarData!.frames.last;
    final timestamp = frame.dateTime.millisecondsSinceEpoch ~/ 1000;
    // สร้าง URL สำหรับภาพนิ่ง
    return '${_radarData!.host}${frame.path}/256/{z}/{x}/{y}/${_getTileScale()}/1_0.png?t=$timestamp';
  }

  // สำหรับแสดงภาพเรดาร์แบบคงที่ ไม่ refresh เมื่อซูม
  Widget _buildRadarTileLayer() {
    final url = _getCurrentRadarUrl();
    if (url == null) return const SizedBox.shrink();

    return TileLayer(
      urlTemplate: url,
      key: ValueKey('radar_static_${_radarData?.frames.last.path}'), // ใช้ path แทน dateTime
      backgroundColor: Colors.transparent,
      tileSize: 256,
      maxZoom: 16,
      minZoom: 3,
      errorTileCallback: (tile, error, stackTrace) {
        // Error handling
      },
    );
  }

  void _onTabTapped(int index) {
    setState(() {
      _currentIndex = index;
      NavigationController.setCurrentIndex(index);
    });

    // Navigate to the appropriate screen
    switch (index) {
      case 0:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const HomeScreen()),
        );
        break;
      case 1:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const MenuScreen()),
        );
        break;
      case 2:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const DeviceStatusScreen()),
        );
        break;
      case 3:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const NotificationScreen()),
        );
        break;
      case 4:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const SettingsScreen()),
        );
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'เรดาร์ฝน',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 20,
          ),
        ),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 8),
            child: IconButton(
              icon: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.refresh_rounded,
                  size: 20,
                ),
              ),
              onPressed: _loadRadarData,
              tooltip: 'รีเฟรชข้อมูล',
            ),
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: Theme.of(context).brightness == Brightness.dark
                ? [
                    AppTheme.darkBackground,
                    AppTheme.darkSurface,
                  ]
                : [
                    AppTheme.brandPrimary.withOpacity(0.05),
                    Colors.white,
                  ],
          ),
        ),
        child: _isLoading
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: AppTheme.brandPrimary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: CircularProgressIndicator(
                      strokeWidth: 3,
                      valueColor: AlwaysStoppedAnimation<Color>(AppTheme.brandPrimary),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'กำลังโหลดข้อมูลเรดาร์...',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.textLight
                          : AppTheme.darkGrey,
                    ),
                  ),
                ],
              ),
            )
          : _errorMessage != null
              ? Center(
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
                      const SizedBox(height: 24),
                      ElevatedButton.icon(
                        onPressed: _loadRadarData,
                        icon: const Icon(Icons.refresh),
                        label: const Text('ลองใหม่'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.brandPrimary,
                          foregroundColor: Colors.white,
                        ),
                      ),
                    ],
                  ),
                )
              : Padding(
              padding: const EdgeInsets.fromLTRB(0, 20, 0, 20),
              child: Column(
                children: [
                  // Map - ย้ายมาไว้บนสุด
                  Expanded(
                    flex: 3,
                    child: Container(
                      margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.08),
                            blurRadius: 12,
                            offset: const Offset(0, 4),
                            spreadRadius: 0,
                          ),
                        ],
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: FlutterMap(
                        mapController: _mapController,
                        options: MapOptions(
                          initialCenter: _currentLocation,
                          initialZoom: 8.0,
                          minZoom: 3.0,
                          maxZoom: 16.0,
                          // ลบการ refresh อัตโนมัติเพื่อให้ซูมได้อย่างเสถียร
                        ),
                        children: [
                          // Base Map Layer
                          TileLayer(
                            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                            userAgentPackageName: 'com.example.landslideapp',
                          ),
                          
                          // Radar Layer - ใช้ function ที่มี error handling
                          _buildRadarTileLayer(),
                          
                          // Current Location Marker
                          MarkerLayer(
                            markers: [
                              Marker(
                                point: _currentLocation,
                                width: 40,
                                height: 40,
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: AppTheme.brandPrimary,
                                    shape: BoxShape.circle,
                                    border: Border.all(
                                      color: Colors.white,
                                      width: 3,
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: AppTheme.brandPrimary.withOpacity(0.3),
                                        blurRadius: 6,
                                        offset: const Offset(0, 2),
                                      ),
                                    ],
                                  ),
                                  child: const Icon(
                                    Icons.person_pin_circle,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                  // Control Panel
                  Container(
                    margin: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                          spreadRadius: 0,
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        // Time Display
                        if (_radarData?.frames.isNotEmpty == true)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 10,
                            ),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  AppTheme.brandPrimary.withOpacity(0.1),
                                  AppTheme.secondaryGreen.withOpacity(0.05),
                                ],
                                begin: Alignment.centerLeft,
                                end: Alignment.centerRight,
                              ),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: AppTheme.brandPrimary.withOpacity(0.2),
                                width: 1,
                              ),
                            ),
                            child: Column(
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(
                                      Icons.access_time_rounded,
                                      size: 16,
                                      color: AppTheme.brandPrimary,
                                    ),
                                    const SizedBox(width: 6),
                                    Flexible(
                                      child: Text(
                                        'เวลา: ${_formatDateTime(_radarData!.frames.last.dateTime)}',
                                        style: TextStyle(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 14,
                                          color: AppTheme.brandPrimary,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        
                        const SizedBox(height: 16),
                        
                        // Modern Controls - แก้ไข overflow ด้วย Tooltip
                        Row(
                          children: [
                            Expanded(
                              child: Tooltip(
                                message: 'รีเฟรชข้อมูล',
                                child: Container(
                                  height: 45,
                                  child: ElevatedButton(
                                    onPressed: _loadRadarData,
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppTheme.brandPrimary,
                                      foregroundColor: Colors.white,
                                      elevation: 0,
                                      shadowColor: Colors.transparent,
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(16),
                                      ),
                                    ),
                                    child: Icon(
                                      Icons.refresh_rounded,
                                      size: 22,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Tooltip(
                                message: 'ตำแหน่งปัจจุบัน',
                                child: Container(
                                  height: 45,
                                  child: ElevatedButton(
                                    onPressed: () => _getCurrentLocation(),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Theme.of(context).brightness == Brightness.dark 
                                          ? AppTheme.darkCard 
                                          : Colors.white,
                                      foregroundColor: AppTheme.brandPrimary,
                                      elevation: 0,
                                      shadowColor: Colors.transparent,
                                      side: BorderSide(
                                        color: AppTheme.brandPrimary.withOpacity(0.3),
                                        width: 1.5,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(16),
                                      ),
                                    ),
                                    child: Icon(
                                      Icons.my_location_rounded,
                                      size: 22,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                
                // Radar Legend
                Container(
                  padding: const EdgeInsets.all(16),
                  margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  decoration: BoxDecoration(
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.darkCardSoft 
                        : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.08),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                        spreadRadius: 0,
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.palette_rounded,
                            size: 18,
                            color: AppTheme.brandPrimary,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'คำอธิบายสีเรดาร์ฝน',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                              color: Theme.of(context).brightness == Brightness.dark 
                                  ? AppTheme.textLight
                                  : AppTheme.brandPrimary,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _buildLegendItem('ฝนเบามาก', const Color(0xFF00fa00)),
                          _buildLegendItem('ฝนเบา', const Color(0xFF00c3ff)),
                          _buildLegendItem('ฝนปานกลาง', const Color(0xFF006aff)),
                          _buildLegendItem('ฝนค่อนข้างหนัก', const Color(0xFF001fff)),
                          _buildLegendItem('ฝนหนัก', const Color(0xFFfffa00)),
                          _buildLegendItem('ฝนหนักมาก', const Color(0xFFff9600)),
                          _buildLegendItem('ฝนรุนแรง', const Color(0xFFff0000)),
                          _buildLegendItem('ฝนรุนแรงมาก', const Color(0xFFd600ff)),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      bottomNavigationBar: CustomBottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _onTabTapped,
      ),
    );
  }

  String _formatDateTime(DateTime dateTime) {
    // ลบเวลา 30 นาที
    final adjustedTime = dateTime.subtract(const Duration(minutes: 30));
    
    // รายชื่อเดือนภาษาไทย
    const thaiMonths = [
      'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
      'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    
    final thaiMonth = thaiMonths[adjustedTime.month - 1];
    final buddhistYear = adjustedTime.year + 543;
    
    return '${adjustedTime.day} $thaiMonth ${buddhistYear} ${adjustedTime.hour.toString().padLeft(2, '0')}:${adjustedTime.minute.toString().padLeft(2, '0')} น.';
  }

  Widget _buildLegendItem(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCard 
            : Colors.grey.shade50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: AppTheme.brandPrimary.withOpacity(0.2),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 14,
            height: 12,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(3),
              border: Border.all(
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.darkGrey2 
                    : Colors.grey.shade400,
                width: 0.5,
              ),
            ),
          ),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w500,
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight
                  : AppTheme.darkGrey,
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    super.dispose();
  }
}
