import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'dart:developer' as dev;
import '../services/location_stats_service.dart';
import '../services/device_by_location_service.dart';
import '../controllers/auth_controller.dart';
import '../theme/app_theme.dart';
import 'device_detail_screen.dart';

class MapScreen extends StatefulWidget {
  const MapScreen({super.key});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> {
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  List<LocationData> _locations = [];
  List<LocationData> _filteredLocations = [];
  
  // กำหนดจุดกึ่งกลางของแผนที่ (พื้นที่เชียงราย)
  static const LatLng _centerLocation = LatLng(20.31, 99.75);
  
  final MapController _mapController = MapController();
  final TextEditingController _searchController = TextEditingController();
  bool _isSearchVisible = false;

  @override
  void initState() {
    super.initState();
    _loadLocationData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _filterLocations(String query) {
    dev.log('🔍 [MapScreen] กำลังกรองสถานที่ด้วยคำค้นหา: "$query"', name: 'MapScreen');
    
    setState(() {
      if (query.isEmpty) {
        _filteredLocations = List.from(_locations);
        dev.log('🔍 [MapScreen] ล้างการค้นหา - แสดงทั้งหมด ${_filteredLocations.length} สถานที่', name: 'MapScreen');
      } else {
        _filteredLocations = _locations.where((location) {
          final nameMatch = location.locationName.toLowerCase().contains(query.toLowerCase());
          final idMatch = location.locationId.toLowerCase().contains(query.toLowerCase());
          final matched = nameMatch || idMatch;
          
          if (matched) {
            dev.log('🔍 [MapScreen] ✅ พบ: ${location.locationId} (${location.locationName}) - nameMatch=$nameMatch, idMatch=$idMatch', name: 'MapScreen');
          }
          
          return matched;
        }).toList();
        
        dev.log('🔍 [MapScreen] ผลการค้นหา: พบ ${_filteredLocations.length} จาก ${_locations.length} สถานที่', name: 'MapScreen');
        
        if (_filteredLocations.isEmpty) {
          dev.log('🔍 [MapScreen] ⚠️ ไม่พบสถานที่ที่ตรงกับการค้นหา "$query"', name: 'MapScreen');
        }
      }
    });
  }

  void _toggleSearch() {
    dev.log('🔍 [MapScreen] สลับการค้นหา: ${!_isSearchVisible ? "เปิด" : "ปิด"}', name: 'MapScreen');
    
    setState(() {
      _isSearchVisible = !_isSearchVisible;
      if (!_isSearchVisible) {
        dev.log('🔍 [MapScreen] ปิดการค้นหา - ล้างข้อความและรีเซ็ตรายการ', name: 'MapScreen');
        _searchController.clear();
        _filteredLocations = List.from(_locations);
      } else {
        dev.log('🔍 [MapScreen] เปิดการค้นหา - พร้อมรับข้อความค้นหา', name: 'MapScreen');
      }
    });
  }

  Future<void> _loadLocationData() async {
    dev.log('🗺️ [MapScreen] เริ่มโหลดข้อมูลสถานที่', name: 'MapScreen');
    
    // ตรวจสอบสถานะ auth ก่อน
    dev.log('🔑 [MapScreen] ตรวจสอบสถานะการ login: ${AuthController.isLoggedIn}', name: 'MapScreen');
    if (AuthController.currentUser != null) {
      dev.log('👤 [MapScreen] User ปัจจุบัน: ${AuthController.currentUser!.username}', name: 'MapScreen');
    }
    
    setState(() {
      _isLoading = true;
      _hasError = false;
    });

    try {
      final result = await LocationStatsService.getLocationStats();
      
      dev.log('🗺️ [MapScreen] ผลลัพธ์ API: success=${result.success}, message=${result.message}', name: 'MapScreen');
      dev.log('🗺️ [MapScreen] จำนวนสถานที่: ${result.locations.length}', name: 'MapScreen');

      if (mounted) {
        setState(() {
          _isLoading = false;
          if (result.success) {
            _locations = result.locations;
            _filteredLocations = List.from(_locations);
            _hasError = false;
            
            dev.log('✅ [MapScreen] โหลดสถานที่สำเร็จ: ${_locations.length} สถานที่', name: 'MapScreen');
            
            for (int i = 0; i < _locations.length; i++) {
              final location = _locations[i];
              dev.log('📍 [MapScreen] Location[$i]: ID=${location.locationId}, Name=${location.locationName}', name: 'MapScreen');
              dev.log('📍 [MapScreen] Location[$i]: Position=(${location.position.latitude}, ${location.position.longitude})', name: 'MapScreen');
              
              // ตรวจสอบ latitude/longitude ว่าอยู่ในช่วงที่ถูกต้องหรือไม่
              if (location.position.latitude < -90 || location.position.latitude > 90) {
                dev.log('⚠️ [MapScreen] Location[$i]: Invalid latitude: ${location.position.latitude}', name: 'MapScreen');
              }
              if (location.position.longitude < -180 || location.position.longitude > 180) {
                dev.log('⚠️ [MapScreen] Location[$i]: Invalid longitude: ${location.position.longitude}', name: 'MapScreen');
              }
              
              // ตรวจสอบว่าอยู่ในพื้นที่ประเทศไทยหรือไม่ (ประมาณ)
              if (location.position.latitude < 5.6 || location.position.latitude > 20.5 ||
                  location.position.longitude < 97.3 || location.position.longitude > 105.6) {
                dev.log('⚠️ [MapScreen] Location[$i]: Position seems outside Thailand boundaries', name: 'MapScreen');
              }
            }
            
            dev.log('🎯 [MapScreen] การสร้างหมุดแผนที่: จะสร้าง ${_filteredLocations.length} หมุด', name: 'MapScreen');
          } else {
            _hasError = true;
            _errorMessage = result.message;
            
            dev.log('❌ [MapScreen] โหลดสถานที่ไม่สำเร็จ: ${result.message}', name: 'MapScreen');
            
            // หากต้องเข้าสู่ระบบใหม่
            if (result.needsRelogin) {
              dev.log('🔑 [MapScreen] ต้องเข้าสู่ระบบใหม่', name: 'MapScreen');
              _showReloginDialog();
            }
          }
        });
      }
    } catch (e, stackTrace) {
      dev.log('💥 [MapScreen] Exception: ${e.toString()}', name: 'MapScreen');
      dev.log('📚 [MapScreen] Stack trace: $stackTrace', name: 'MapScreen');
      
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ${e.toString()}';
        });
      }
    }
  }

  void _showReloginDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          backgroundColor: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.darkCardSoft 
              : Colors.white,
          title: Row(
            children: [
              Icon(
                Icons.warning,
                color: AppTheme.warningColor,
                size: 28,
              ),
              const SizedBox(width: 8),
              Text(
                'ต้องเข้าสู่ระบบใหม่',
                style: TextStyle(
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight 
                      : Colors.black,
                ),
              ),
            ],
          ),
          content: Text(
            'เซสชันของคุณหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่',
            style: TextStyle(
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight.withOpacity(0.8) 
                  : Colors.black87,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () async {
                dev.log('🔑 [MapScreen] กำลัง logout และไปหน้า login', name: 'MapScreen');
                Navigator.of(context).pop();
                
                // ใช้ AuthController เพื่อ logout
                await AuthController.logout();
                
                // ไปหน้า login
                if (mounted) {
                  Navigator.of(context).pushReplacementNamed('/login');
                }
              },
              child: Text(
                'เข้าสู่ระบบ',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  void _showLocationDetails(LocationData location) {
    dev.log('🗺️ [MapScreen] คลิกหมุดสำหรับ location: ${location.locationId} (${location.locationName})', name: 'MapScreen');
    dev.log('🗺️ [MapScreen] Location position: (${location.position.latitude}, ${location.position.longitude})', name: 'MapScreen');
    dev.log('🗺️ [MapScreen] เปิด Modal Bottom Sheet สำหรับรายละเอียดอุปกรณ์', name: 'MapScreen');
    
    try {
      showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        backgroundColor: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        builder: (BuildContext context) {
          dev.log('🗺️ [MapScreen] Modal Bottom Sheet ถูกสร้างสำหรับ: ${location.locationId}', name: 'MapScreen');
          
          return DraggableScrollableSheet(
            initialChildSize: 0.6,
            minChildSize: 0.3,
            maxChildSize: 0.9,
            expand: false,
            builder: (context, scrollController) {
              return _LocationDevicesWidget(
                location: location,
                scrollController: scrollController,
                mapController: _mapController,
              );
            },
          );
        },
      ).then((_) {
        dev.log('🗺️ [MapScreen] Modal Bottom Sheet ปิดแล้วสำหรับ: ${location.locationId}', name: 'MapScreen');
      }).catchError((error) {
        dev.log('💥 [MapScreen] Error ในการแสดง Modal Bottom Sheet: $error', name: 'MapScreen');
      });
    } catch (e, stackTrace) {
      dev.log('💥 [MapScreen] Exception ในการเปิด Modal Bottom Sheet: $e', name: 'MapScreen');
      dev.log('📚 [MapScreen] Stack trace: $stackTrace', name: 'MapScreen');
      
      // แสดง error dialog หากมีปัญหา
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            backgroundColor: Theme.of(context).brightness == Brightness.dark 
                ? AppTheme.darkCardSoft 
                : Colors.white,
            title: Row(
              children: [
                Icon(Icons.error, color: AppTheme.errorColor),
                const SizedBox(width: 8),
                Text(
                  'เกิดข้อผิดพลาด',
                  style: TextStyle(
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight 
                        : Colors.black,
                  ),
                ),
              ],
            ),
            content: Text(
              'ไม่สามารถแสดงรายละเอียดสถานที่ได้: ${e.toString()}',
              style: TextStyle(
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight.withOpacity(0.8) 
                    : Colors.black87,
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(),
                child: Text(
                  'ตกลง',
                  style: TextStyle(color: AppTheme.brandPrimary),
                ),
              ),
            ],
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: _isSearchVisible 
          ? TextField(
              controller: _searchController,
              autofocus: true,
              style: const TextStyle(color: Colors.white),
              decoration: const InputDecoration(
                hintText: 'ค้นหาสถานที่...',
                hintStyle: TextStyle(color: Colors.white70),
                border: InputBorder.none,
              ),
              onChanged: _filterLocations,
            )
          : const Text('แผนที่สถานที่ติดตั้ง', style: TextStyle(fontSize: 18)),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(_isSearchVisible ? Icons.close : Icons.search),
            onPressed: _toggleSearch,
            tooltip: _isSearchVisible ? 'ปิดการค้นหา' : 'ค้นหา',
          ),
          if (!_isSearchVisible)
            IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: _loadLocationData,
              tooltip: 'รีเฟรชข้อมูล',
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
                  const SizedBox(height: 16),
                  Text(
                    'กำลังโหลดข้อมูลสถานที่...',
                    style: TextStyle(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.textLight 
                          : Colors.black,
                    ),
                  ),
                ],
              ),
            )
          : _hasError
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
                        _errorMessage,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: AppTheme.errorColor,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: _loadLocationData,
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
              : Stack(
                  children: [
                    FlutterMap(
                      mapController: _mapController,
                      options: MapOptions(
                        initialCenter: _centerLocation,
                        initialZoom: 10.0,
                        minZoom: 8.0,
                        maxZoom: 18.0,
                        interactionOptions: const InteractionOptions(
                          flags: InteractiveFlag.all,
                        ),
                      ),
                      children: [
                        TileLayer(
                          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                          userAgentPackageName: 'com.example.landslideapp',
                          maxZoom: 19,
                        ),
                        MarkerLayer(
                          markers: _filteredLocations.asMap().entries.map((entry) {
                            final index = entry.key;
                            final location = entry.value;
                            
                            dev.log('🎯 [MapScreen] สร้างหมุด[$index]: ${location.locationId} (${location.locationName})', name: 'MapScreen');
                            dev.log('🎯 [MapScreen] หมุด[$index]: Position=(${location.position.latitude}, ${location.position.longitude})', name: 'MapScreen');
                            
                            // สีสันสำหรับหมุด (หมุนวนตามดัชนี)
                            final colors = [
                              const Color(0xFF4CAF50), // เขียว
                              const Color(0xFF2196F3), // น้ำเงิน
                              const Color(0xFFFF9800), // ส้ม
                              const Color(0xFF9C27B0), // ม่วง
                              const Color(0xFFF44336), // แดง
                              const Color(0xFF00BCD4), // ฟ้า
                              const Color(0xFF795548), // น้ำตาล
                              const Color(0xFF607D8B), // เทา-น้ำเงิน
                            ];
                            final markerColor = colors[index % colors.length];
                            
                            dev.log('🎯 [MapScreen] หมุด[$index]: Color=${markerColor.toString()}', name: 'MapScreen');
                            
                            return Marker(
                              point: location.position,
                              width: 120,
                              height: 90, // เพิ่มความสูงเพื่อรองรับข้อความยาว
                              child: GestureDetector(
                                onTap: () {
                                  dev.log('👆 [MapScreen] คลิกหมุด[$index]: ${location.locationId} (${location.locationName})', name: 'MapScreen');
                                  _showLocationDetails(location);
                                },
                                child: Column(
                                  mainAxisSize: MainAxisSize.min, // ใช้พื้นที่ตามเนื้อหา
                                  children: [
                                    // ชื่อสถานที่
                                    Flexible( // ใช้ Flexible แทน Container เพื่อป้องกัน overflow
                                      child: Container(
                                        constraints: const BoxConstraints(maxWidth: 110),
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 4,
                                        ),
                                        decoration: BoxDecoration(
                                          color: markerColor,
                                          borderRadius: BorderRadius.circular(16),
                                          boxShadow: [
                                            BoxShadow(
                                              color: Colors.black.withOpacity(0.3),
                                              blurRadius: 6,
                                              offset: const Offset(0, 3),
                                            ),
                                          ],
                                        ),
                                        child: Text(
                                          location.locationName,
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                          ),
                                          textAlign: TextAlign.center,
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    // หมุดแผนที่
                                    Container(
                                      width: 40,
                                      height: 40,
                                      decoration: BoxDecoration(
                                        color: markerColor,
                                        shape: BoxShape.circle,
                                        border: Border.all(
                                          color: Colors.white,
                                          width: 3,
                                        ),
                                        boxShadow: [
                                          BoxShadow(
                                            color: Colors.black.withOpacity(0.3),
                                            blurRadius: 6,
                                            offset: const Offset(0, 3),
                                          ),
                                        ],
                                      ),
                                      child: const Icon(
                                        Icons.location_on,
                                        color: Colors.white,
                                        size: 24,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                    // Legend/Info Card
                    Positioned(
                      top: 16,
                      left: 16,
                      right: 16,
                      child: Card(
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.darkCardSoft.withOpacity(0.95)
                            : Colors.white.withOpacity(0.95),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Column(
                            children: [
                              Row(
                                children: [
                                  Icon(
                                    Icons.info_outline,
                                    color: AppTheme.brandPrimary,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      _isSearchVisible && _searchController.text.isNotEmpty
                                        ? 'พบ ${_filteredLocations.length} จาก ${_locations.length} สถานที่'
                                        : 'แสดง ${_filteredLocations.length} สถานที่ติดตั้ง',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Theme.of(context).brightness == Brightness.dark 
                                            ? AppTheme.textLight 
                                            : Colors.black,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              if (_isSearchVisible && _searchController.text.isNotEmpty && _filteredLocations.isEmpty)
                                Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: Text(
                                    'ไม่พบสถานที่ที่ตรงกับการค้นหา',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: AppTheme.errorColor,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                ),
                              const SizedBox(height: 4),
                              Text(
                                'แตะที่หมุดเพื่อดูรายละเอียดอุปกรณ์',
                                style: TextStyle(
                                  fontSize: 10,
                                  color: Theme.of(context).brightness == Brightness.dark 
                                      ? AppTheme.textLight.withOpacity(0.7) 
                                      : Colors.grey,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    // Search Results List
                    if (_isSearchVisible && _searchController.text.isNotEmpty && _filteredLocations.isNotEmpty)
                      Positioned(
                        top: 90,
                        left: 16,
                        right: 16,
                        child: Container(
                          constraints: const BoxConstraints(maxHeight: 200),
                          child: Card(
                            color: Theme.of(context).brightness == Brightness.dark 
                                ? AppTheme.darkCardSoft.withOpacity(0.95)
                                : Colors.white.withOpacity(0.95),
                            child: ListView.separated(
                              shrinkWrap: true,
                              itemCount: _filteredLocations.length,
                              separatorBuilder: (context, index) => const Divider(height: 1),
                              itemBuilder: (context, index) {
                                final location = _filteredLocations[index];
                                final colors = [
                                  const Color(0xFF4CAF50), // เขียว
                                  const Color(0xFF2196F3), // น้ำเงิน
                                  const Color(0xFFFF9800), // ส้ม
                                  const Color(0xFF9C27B0), // ม่วง
                                  const Color(0xFFF44336), // แดง
                                  const Color(0xFF00BCD4), // ฟ้า
                                  const Color(0xFF795548), // น้ำตาล
                                  const Color(0xFF607D8B), // เทา-น้ำเงิน
                                ];
                                final markerColor = colors[_locations.indexOf(location) % colors.length];
                                
                                return ListTile(
                                  dense: true,
                                  leading: Container(
                                    width: 24,
                                    height: 24,
                                    decoration: BoxDecoration(
                                      color: markerColor,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.location_on,
                                      color: Colors.white,
                                      size: 16,
                                    ),
                                  ),
                                  title: Text(
                                    location.locationName,
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: Theme.of(context).brightness == Brightness.dark 
                                          ? AppTheme.textLight 
                                          : Colors.black,
                                    ),
                                  ),
                                  subtitle: Text(
                                    'รหัส: ${location.locationId}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Theme.of(context).brightness == Brightness.dark 
                                          ? AppTheme.textLight.withOpacity(0.8) 
                                          : Colors.black54,
                                    ),
                                  ),
                                  trailing: Icon(
                                    Icons.zoom_in_map, 
                                    size: 20,
                                    color: Theme.of(context).brightness == Brightness.dark 
                                        ? AppTheme.textLight.withOpacity(0.7) 
                                        : Colors.black54,
                                  ),
                                  onTap: () {
                                    dev.log('📋 [MapScreen] คลิกรายการค้นหา: ${location.locationId} (${location.locationName})', name: 'MapScreen');
                                    dev.log('📋 [MapScreen] ย้ายแผนที่ไป: (${location.position.latitude}, ${location.position.longitude}) ซูม 15.0', name: 'MapScreen');
                                    
                                    _mapController.move(location.position, 15.0);
                                    setState(() {
                                      _isSearchVisible = false;
                                      _searchController.clear();
                                      _filteredLocations = List.from(_locations);
                                    });
                                    
                                    dev.log('📋 [MapScreen] ปิดการค้นหาและรีเซ็ตรายการเรียบร้อย', name: 'MapScreen');
                                  },
                                );
                              },
                            ),
                          ),
                        ),
                      ),
                    // Zoom Controls
                    Positioned(
                      bottom: 16,
                      right: 16,
                      child: Column(
                        children: [
                          FloatingActionButton.small(
                            heroTag: "zoom_in",
                            onPressed: () {
                              final currentZoom = _mapController.camera.zoom;
                              final newZoom = currentZoom + 1;
                              final center = _mapController.camera.center;
                              
                              dev.log('🔍 [MapScreen] Zoom In: $currentZoom → $newZoom, Center: (${center.latitude}, ${center.longitude})', name: 'MapScreen');
                              
                              _mapController.move(center, newZoom);
                            },
                            backgroundColor: AppTheme.brandPrimary,
                            foregroundColor: Colors.white,
                            child: const Icon(Icons.add),
                          ),
                          const SizedBox(height: 8),
                          FloatingActionButton.small(
                            heroTag: "zoom_out",
                            onPressed: () {
                              final currentZoom = _mapController.camera.zoom;
                              final newZoom = currentZoom - 1;
                              final center = _mapController.camera.center;
                              
                              dev.log('🔍 [MapScreen] Zoom Out: $currentZoom → $newZoom, Center: (${center.latitude}, ${center.longitude})', name: 'MapScreen');
                              
                              _mapController.move(center, newZoom);
                            },
                            backgroundColor: AppTheme.brandPrimary,
                            foregroundColor: Colors.white,
                            child: const Icon(Icons.remove),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
      ),
    );
  }
}

class _LocationDevicesWidget extends StatefulWidget {
  final LocationData location;
  final ScrollController scrollController;
  final MapController mapController;

  const _LocationDevicesWidget({
    required this.location,
    required this.scrollController,
    required this.mapController,
  });

  @override
  State<_LocationDevicesWidget> createState() => _LocationDevicesWidgetState();
}

class _LocationDevicesWidgetState extends State<_LocationDevicesWidget> {
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  DevicesByLocationResult? _devicesResult;

  @override
  void initState() {
    super.initState();
    dev.log('🏗️ [LocationDevicesWidget] เริ่มต้น Widget สำหรับ location: ${widget.location.locationId}', name: 'LocationDevicesWidget');
    dev.log('🏗️ [LocationDevicesWidget] Location details: ${widget.location.locationName} at (${widget.location.position.latitude}, ${widget.location.position.longitude})', name: 'LocationDevicesWidget');
    _loadDevices();
  }

  Future<void> _loadDevices() async {
    dev.log('🔄 [MapScreen] เริ่มโหลดข้อมูลอุปกรณ์สำหรับ location: ${widget.location.locationId}', name: 'MapScreen');
    
    setState(() {
      _isLoading = true;
      _hasError = false;
    });

    try {
      final result = await DeviceByLocationService.getDevicesByLocation(widget.location.locationId);
      
      dev.log('📊 [MapScreen] ผลลัพธ์: success=${result.success}, message=${result.message}, needsRelogin=${result.needsRelogin}', name: 'MapScreen');
      
      if (result.success) {
        dev.log('✅ [MapScreen] โหลดอุปกรณ์สำเร็จ: ${result.devices.length} อุปกรณ์', name: 'MapScreen');
        for (var device in result.devices) {
          dev.log('📱 [MapScreen] Device: ${device.deviceId} - ${device.deviceName} (status: ${device.voidStatus})', name: 'MapScreen');
        }
      } else {
        dev.log('❌ [MapScreen] โหลดอุปกรณ์ไม่สำเร็จ: ${result.message}', name: 'MapScreen');
        
        // แสดง error ที่เฉพาะเจาะจงมากขึ้น
        if (result.message.contains('JSON') || result.message.contains('FormatException')) {
          dev.log('🔍 [MapScreen] เป็น JSON parsing error - อาจเป็นปัญหาจาก server response', name: 'MapScreen');
        }
      }
      
      if (mounted) {
        setState(() {
          _isLoading = false;
          _devicesResult = result;
          if (!result.success) {
            _hasError = true;
            
            // ปรับปรุง error message ให้เป็นมิตรกับผู้ใช้มากขึ้น
            if (result.message.contains('JSON') || result.message.contains('FormatException')) {
              _errorMessage = 'เซิร์ฟเวอร์ส่งข้อมูลที่ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
            } else if (result.message.contains('schema')) {
              _errorMessage = 'เกิดปัญหาในฐานข้อมูล กรุณาติดต่อผู้ดูแลระบบ';
            } else {
              _errorMessage = result.message;
            }
            
            if (result.needsRelogin) {
              dev.log('🔑 [MapScreen] ต้องเข้าสู่ระบบใหม่', name: 'MapScreen');
              _showReloginDialog();
            }
          }
        });
      }
    } catch (e, stackTrace) {
      dev.log('💥 [MapScreen] Exception เมื่อโหลดอุปกรณ์: $e', name: 'MapScreen');
      dev.log('📚 [MapScreen] Stack trace: $stackTrace', name: 'MapScreen');
      
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          
          // ปรับปรุง error message สำหรับ exception
          if (e.toString().contains('FormatException')) {
            _errorMessage = 'เซิร์ฟเวอร์ส่งข้อมูลที่ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
          } else if (e.toString().contains('TimeoutException')) {
            _errorMessage = 'การเชื่อมต่อใช้เวลานานเกินไป กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต';
          } else {
            _errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูลอุปกรณ์: ${e.toString()}';
          }
        });
      }
    }
  }

  void _showReloginDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          backgroundColor: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.darkCardSoft 
              : Colors.white,
          title: Row(
            children: [
              Icon(
                Icons.warning,
                color: AppTheme.warningColor,
                size: 28,
              ),
              const SizedBox(width: 8),
              Text(
                'ต้องเข้าสู่ระบบใหม่',
                style: TextStyle(
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight 
                      : Colors.black,
                ),
              ),
            ],
          ),
          content: Text(
            'เซสชันของคุณหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่',
            style: TextStyle(
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight.withOpacity(0.8) 
                  : Colors.black87,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () async {
                dev.log('🔑 [MapScreen] Device widget - กำลัง logout และไปหน้า login', name: 'MapScreen');
                Navigator.of(context).pop(); // ปิด dialog
                Navigator.of(context).pop(); // ปิด modal bottom sheet
                
                // ใช้ AuthController เพื่อ logout
                await AuthController.logout();
                
                // ไปหน้า login
                if (mounted) {
                  Navigator.of(context).pushReplacementNamed('/login');
                }
              },
              child: Text(
                'เข้าสู่ระบบ',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  // Helper method เพื่อแปลง voidStatus เป็น int
  int _getVoidStatus(dynamic value) {
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  // Helper method เพื่อแปลง takePhoto เป็น int
  int _getTakePhoto(dynamic value) {
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  Widget _buildDeviceCard(DeviceData device) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: isDarkMode ? AppTheme.darkCard : AppTheme.backgroundLight,
      child: InkWell(
        onTap: () {
          dev.log('📱 [MapScreen] คลิกอุปกรณ์: ${device.deviceId} (${device.deviceName})', name: 'MapScreen');
          
          // เปิด Device Detail Screen
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) => DeviceDetailScreen(
                device: device,
                deviceResult: _devicesResult!,
              ),
            ),
          );
        },
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppTheme.brandPrimary, // ใช้สีเดียวกันหมด
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      _getDeviceIcon(device.deviceId),
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          device.deviceName,
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: isDarkMode ? AppTheme.textLight : Colors.black,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'รหัส: ${device.deviceId}',
                          style: TextStyle(
                            fontSize: 14,
                            color: isDarkMode 
                                ? AppTheme.textLight.withOpacity(0.8) 
                                : Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: _getVoidStatus(device.voidStatus) == 0 
                              ? AppTheme.brandPrimary.withOpacity(0.2)
                              : AppTheme.errorColor.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          _getVoidStatus(device.voidStatus) == 0 ? 'ใช้งาน' : 'ปิดใช้งาน',
                          style: TextStyle(
                            fontSize: 12,
                            color: _getVoidStatus(device.voidStatus) == 0 
                                ? AppTheme.brandPrimary
                                : AppTheme.errorColor,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 16,
                        color: isDarkMode 
                            ? AppTheme.textLight.withOpacity(0.5) 
                            : Colors.black26,
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  if (_getTakePhoto(device.takePhoto) == 1)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppTheme.brandPrimary.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.camera_alt,
                            size: 12,
                            color: AppTheme.brandPrimary,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'ฟีเจอร์ถ่ายภาพ',
                            style: TextStyle(
                              fontSize: 10,
                              color: AppTheme.brandPrimary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _getDeviceIcon(String deviceId) {
    return Icons.device_hub; // ใช้ไอคอนเดียวกันหมดสำหรับทุกอุปกรณ์
  }

  @override
  Widget build(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Container(
      decoration: BoxDecoration(
        color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Column(
        children: [
          // Drag Handle
          Container(
            margin: const EdgeInsets.only(top: 8),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey.withOpacity(0.3),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          // Header
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.location_on,
                      color: AppTheme.brandPrimary,
                      size: 28,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        widget.location.locationName,
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: isDarkMode ? AppTheme.textLight : Colors.black,
                        ),
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.of(context).pop(),
                      icon: Icon(
                        Icons.close,
                        color: isDarkMode ? AppTheme.textLight : Colors.black54,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Icon(
                      Icons.tag,
                      color: AppTheme.brandPrimary,
                      size: 16,
                    ),
                    const SizedBox(width: 8),
                    Flexible(
                      child: Text(
                        'รหัส: ${widget.location.locationId}',
                        style: TextStyle(
                          fontSize: 14,
                          color: isDarkMode 
                              ? AppTheme.textLight.withOpacity(0.8) 
                              : Colors.black54,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Icon(
                      Icons.my_location,
                      color: AppTheme.brandPrimary,
                      size: 16,
                    ),
                    const SizedBox(width: 8),
                    Flexible(
                      child: Text(
                        '${widget.location.position.latitude.toStringAsFixed(4)}, ${widget.location.position.longitude.toStringAsFixed(4)}',
                        style: TextStyle(
                          fontSize: 12,
                          color: isDarkMode 
                              ? AppTheme.textLight.withOpacity(0.8) 
                              : Colors.black54,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          // Content
          Expanded(
            child: _isLoading
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        CircularProgressIndicator(
                          color: AppTheme.brandPrimary,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'กำลังโหลดข้อมูลอุปกรณ์...',
                          style: TextStyle(
                            color: isDarkMode ? AppTheme.textLight : Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  )
                : _hasError
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.error_outline,
                              size: 48,
                              color: AppTheme.errorColor,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              _errorMessage,
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: AppTheme.errorColor,
                                fontSize: 16,
                              ),
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: _loadDevices,
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
                    : SingleChildScrollView(
                        controller: widget.scrollController,
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Statistics
                            if (_devicesResult != null)
                              Card(
                                color: AppTheme.brandPrimary.withOpacity(0.1),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Row(
                                    children: [
                                      Flexible(
                                        child: Column(
                                          children: [
                                            Text(
                                              '${_devicesResult!.totalDevices}',
                                              style: TextStyle(
                                                fontSize: 24,
                                                fontWeight: FontWeight.bold,
                                                color: AppTheme.brandPrimary,
                                              ),
                                            ),
                                            Text(
                                              'อุปกรณ์ทั้งหมด',
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: isDarkMode 
                                                    ? AppTheme.textLight.withOpacity(0.8) 
                                                    : Colors.black54,
                                              ),
                                              textAlign: TextAlign.center,
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        width: 1,
                                        height: 40,
                                        color: AppTheme.brandPrimary.withOpacity(0.3),
                                      ),
                                      Flexible(
                                        child: Column(
                                          children: [
                                            Text(
                                              '${_devicesResult!.activeDevices}',
                                              style: TextStyle(
                                                fontSize: 24,
                                                fontWeight: FontWeight.bold,
                                                color: AppTheme.brandPrimary,
                                              ),
                                            ),
                                            Text(
                                              'ใช้งานได้',
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: isDarkMode 
                                                    ? AppTheme.textLight.withOpacity(0.8) 
                                                    : Colors.black54,
                                              ),
                                              textAlign: TextAlign.center,
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            const SizedBox(height: 16),
                            // Devices List
                            if (_devicesResult != null && _devicesResult!.devices.isNotEmpty) ...[
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'รายการอุปกรณ์',
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: isDarkMode ? AppTheme.textLight : Colors.black,
                                    ),
                                  ),
                                  Text(
                                    'แตะเพื่อดูรายละเอียด',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: AppTheme.brandPrimary,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              ..._devicesResult!.devices.map((device) => _buildDeviceCard(device)),
                            ] else ...[
                              Center(
                                child: Column(
                                  children: [
                                    Icon(
                                      Icons.device_unknown,
                                      size: 48,
                                      color: isDarkMode 
                                          ? AppTheme.textLight.withOpacity(0.5) 
                                          : Colors.black26,
                                    ),
                                    const SizedBox(height: 16),
                                    Text(
                                      'ไม่พบอุปกรณ์ในสถานที่นี้',
                                      style: TextStyle(
                                        color: isDarkMode 
                                            ? AppTheme.textLight.withOpacity(0.8) 
                                            : Colors.black54,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                            const SizedBox(height: 16),
                            // Action Button
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: () {
                                  Navigator.of(context).pop();
                                  widget.mapController.move(widget.location.position, 15.0);
                                },
                                icon: const Icon(Icons.center_focus_strong),
                                label: const Text('ย้ายแผนที่ไปที่นี่'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.brandPrimary,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 16),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
