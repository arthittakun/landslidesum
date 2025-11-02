import 'package:flutter/material.dart';
import '../services/device_status_service.dart';
import '../theme/app_theme.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  
  // ข้อมูลสถานะอุปกรณ์
  int _countOnline = 0;
  int _countOffline = 0;
  int _countLocation = 0;
  
  DateTime _lastUpdate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _loadDeviceStatus();
  }

  Future<void> _loadDeviceStatus() async {
    setState(() {
      _isLoading = true;
      _hasError = false;
    });

    try {
      final result = await DeviceStatusService.getDeviceStatus();

      if (mounted) {
        setState(() {
          _isLoading = false;
          if (result.success) {
            _countOnline = result.countOnline;
            _countOffline = result.countOffline;
            _countLocation = result.countLocation;
            _lastUpdate = DateTime.now();
            _hasError = false;
          } else {
            _hasError = true;
            _errorMessage = result.message;
            
            // หากต้องเข้าสู่ระบบใหม่
            if (result.needsRelogin) {
              _showReloginDialog();
            }
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
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
          title: Row(
            children: [
              Icon(
                Icons.warning,
                color: AppTheme.warningColor,
                size: 28,
              ),
              const SizedBox(width: 8),
              const Text('ต้องเข้าสู่ระบบใหม่'),
            ],
          ),
          content: const Text('เซสชันของคุณหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่'),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                Navigator.of(context).pushReplacementNamed('/login');
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('หน้าแรก'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDeviceStatus,
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
        child: RefreshIndicator(
        onRefresh: _loadDeviceStatus,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome Section
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      Icon(
                        Icons.home,
                        size: 40,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'ยินดีต้อนรับ',
                              style: TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            if (_isLoading)
                              Row(
                                children: [
                                  SizedBox(
                                    width: 16,
                                    height: 16,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: AppTheme.brandPrimary,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  const Text(
                                    'กำลังโหลดข้อมูล...',
                                    style: TextStyle(color: AppTheme.mediumGrey),
                                  ),
                                ],
                              )
                            else if (_hasError)
                              Row(
                                children: [
                                  Icon(
                                    Icons.error_outline,
                                    size: 16,
                                    color: AppTheme.errorColor,
                                  ),
                                  const SizedBox(width: 4),
                                  Expanded(
                                    child:                                      Text(
                                        _errorMessage,
                                        style: TextStyle(
                                          color: AppTheme.errorColor,
                                          fontSize: 12,
                                        ),
                                      ),
                                  ),
                                ],
                              )
                            else
                              Text(
                                'อัปเดตล่าสุด: ${_lastUpdate.hour.toString().padLeft(2, '0')}:${_lastUpdate.minute.toString().padLeft(2, '0')} น.',
                                style: const TextStyle(
                                  color: AppTheme.mediumGrey,
                                ),
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              
              // Status Cards
              Text(
                'สถานะระบบ',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight 
                      : AppTheme.darkGrey,
                ),
              ),
              const SizedBox(height: 8),
              
              // Online/Offline Status Row
              Row(
                children: [
                  Expanded(
                    child: Card(
                      color: AppTheme.getCardBackgroundColor('success', 
                          isDark: Theme.of(context).brightness == Brightness.dark),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          children: [
                            Icon(
                              Icons.wifi,
                              color: AppTheme.successColor,
                              size: 32,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _isLoading ? '...' : '$_countOnline',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.successColor,
                              ),
                            ),
                            Text(
                              'อุปกรณ์ออนไลน์',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 12,
                                color: AppTheme.successColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Card(
                      color: AppTheme.getCardBackgroundColor('error', 
                          isDark: Theme.of(context).brightness == Brightness.dark),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          children: [
                            Icon(
                              Icons.wifi_off,
                              color: AppTheme.errorColor,
                              size: 32,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _isLoading ? '...' : '$_countOffline',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.errorColor,
                              ),
                            ),
                            Text(
                              'อุปกรณ์ออฟไลน์',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 12,
                                color: AppTheme.errorColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              
              // Location Status Card
              Card(
                color: AppTheme.getCardBackgroundColor('info', 
                    isDark: Theme.of(context).brightness == Brightness.dark),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      Icon(
                        Icons.location_on,
                        color: AppTheme.brandPrimary,
                        size: 32,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              _isLoading ? '...' : '$_countLocation',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.brandPrimary,
                              ),
                            ),
                            Text(
                              'พื้นที่ติดตั้ง/Locations',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                                color: Theme.of(context).brightness == Brightness.dark 
                                    ? AppTheme.textLight 
                                    : AppTheme.darkGrey,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Icon(
                        Icons.map_outlined,
                        color: AppTheme.brandPrimary.withOpacity(0.5),
                        size: 28,
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              
              // Quick Actions
              Text(
                'เมนูด่วน',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight 
                      : AppTheme.darkGrey,
                ),
              ),
              const SizedBox(height: 8),
              Card(
                child: Column(
                  children: [
                    ListTile(
                      leading: Icon(Icons.refresh, color: Theme.of(context).colorScheme.primary),
                      title: const Text('รีเฟรชข้อมูล'),
                      trailing: const Icon(Icons.arrow_forward_ios),
                      onTap: _loadDeviceStatus,
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: Icon(Icons.map, color: AppTheme.brandSecondary),
                      title: const Text('ดูแผนที่'),
                      trailing: const Icon(Icons.arrow_forward_ios),
                      onTap: () {
                        Navigator.of(context).pushNamed('/map');
                      },
                    ),
                    const Divider(height: 1),
                    ListTile(
                      leading: Icon(Icons.sensors, color: AppTheme.successColor),
                      title: const Text('การทำงานอุปกรณ์'),
                      trailing: const Icon(Icons.arrow_forward_ios),
                      onTap: () {
                        Navigator.of(context).pushNamed('/device-environment');
                      },
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        ),
      ),
    );
  }
}
