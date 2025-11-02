import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../services/permission_service.dart';
import '../services/local_notification_service.dart';
import '../controllers/auth_controller.dart';

class PermissionScreen extends StatefulWidget {
  const PermissionScreen({super.key});

  @override
  State<PermissionScreen> createState() => _PermissionScreenState();
}

class _PermissionScreenState extends State<PermissionScreen> {
  bool _isRequesting = false;
  Map<String, bool> _permissionStatus = {};

  @override
  void initState() {
    super.initState();
    _checkPermissions();
  }

  Future<void> _checkPermissions() async {
    final status = await PermissionService.checkAllPermissions();
    setState(() {
      _permissionStatus = status;
    });
  }

  Future<void> _requestPermissions() async {
    setState(() {
      _isRequesting = true;
    });

    try {
      // ขออนุญาตทั้งหมด
      final results = await PermissionService.requestAllPermissions(context);
      
      // เริ่มต้น notification service อีกครั้งหลังจากได้รับอนุญาต
      if (results['notifications'] == true) {
        await LocalNotificationService.initialize(
          onNotificationTap: (payload) {
            // Handle notification tap
          },
        );
      }

      setState(() {
        _permissionStatus = results;
        _isRequesting = false;
      });

      // ถ้าได้รับอนุญาตแล้ว ไปหน้าที่เหมาะสม
      final allGranted = results.values.every((granted) => granted);
      if (allGranted && mounted) {
        // ตรวจสอบสถานะ login เพื่อ navigate ไปหน้าที่เหมาะสม
        if (AuthController.isLoggedIn) {
          Navigator.of(context).pushReplacementNamed('/main');
        } else {
          Navigator.of(context).pushReplacementNamed('/login');
        }
      }
    } catch (e) {
      setState(() {
        _isRequesting = false;
      });
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('เกิดข้อผิดพลาด: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final allGranted = _permissionStatus.isNotEmpty && 
                      _permissionStatus.values.every((granted) => granted);

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              AppTheme.brandPrimary,
              AppTheme.brandPrimary.withOpacity(0.8),
            ],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24.0, 24.0, 24.0, 32.0),
            child: Column(
              children: [
                const SizedBox(height: 40),
                
                // Logo และหัวข้อ
                Container(
                  width: 120,
                  height: 120,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(60),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.2),
                        blurRadius: 20,
                        offset: const Offset(0, 10),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(60),
                    child: Image.asset(
                      'image/logo.png',
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
                
                const SizedBox(height: 32),
                
                const Text(
                  'ตั้งค่าสิทธิ์การใช้งาน',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                  textAlign: TextAlign.center,
                ),
                
                const SizedBox(height: 16),
                
                const Text(
                  'เพื่อให้แอปทำงานได้อย่างเต็มประสิทธิภาพ\nกรุณาอนุญาตสิทธิ์ต่อไปนี้',
                  style: TextStyle(
                    fontSize: 16,
                    color: Colors.white,
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),
                
                const SizedBox(height: 48),
                
                // รายการสิทธิ์
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Column(
                      children: [
                        _buildPermissionItem(
                          icon: Icons.notifications_active,
                          title: 'การแจ้งเตือน',
                          description: 'เพื่อแจ้งเตือนข้อมูลสำคัญและเหตุการณ์ฉุกเฉิน',
                          isGranted: _permissionStatus['notifications'] ?? false,
                        ),
                        
                        const SizedBox(height: 24),
                        
                        _buildPermissionItem(
                          icon: Icons.location_on,
                          title: 'การเข้าถึงตำแหน่ง',
                          description: 'เพื่อแสดงข้อมูลสภาพอากาศและแผนที่ในพื้นที่ของคุณ',
                          isGranted: _permissionStatus['location'] ?? false,
                        ),
                        
                        const Spacer(),
                        
                        // ปุ่มดำเนินการ
                        if (allGranted) ...[
                          Container(
                            width: double.infinity,
                            height: 56,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(16),
                              gradient: LinearGradient(
                                colors: [
                                  AppTheme.successColor,
                                  AppTheme.successColor.withOpacity(0.8),
                                ],
                              ),
                            ),
                            child: ElevatedButton(
                              onPressed: () {
                                Navigator.of(context).pushReplacementNamed('/main');
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ),
                              child: const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.check_circle, color: Colors.white),
                                  SizedBox(width: 8),
                                  Text(
                                    'เริ่มใช้งาน',
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ] else ...[
                          Container(
                            width: double.infinity,
                            height: 56,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(16),
                              gradient: LinearGradient(
                                colors: [
                                  AppTheme.brandPrimary,
                                  AppTheme.brandPrimary.withOpacity(0.8),
                                ],
                              ),
                            ),
                            child: ElevatedButton(
                              onPressed: _isRequesting ? null : _requestPermissions,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ),
                              child: _isRequesting
                                  ? const SizedBox(
                                      width: 24,
                                      height: 24,
                                      child: CircularProgressIndicator(
                                        color: Colors.white,
                                        strokeWidth: 2,
                                      ),
                                    )
                                  : const Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.security, color: Colors.white),
                                        SizedBox(width: 8),
                                        Text(
                                          'อนุญาตสิทธิ์',
                                          style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                            color: Colors.white,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                          
                          const SizedBox(height: 16),
                          
                          TextButton(
                            onPressed: () {
                              // ตรวจสอบสถานะ login เพื่อ navigate ไปหน้าที่เหมาะสม
                              if (AuthController.isLoggedIn) {
                                Navigator.of(context).pushReplacementNamed('/main');
                              } else {
                                Navigator.of(context).pushReplacementNamed('/login');
                              }
                            },
                            child: const Text(
                              'ข้ามไปก่อน',
                              style: TextStyle(
                                color: AppTheme.textSecondary,
                                fontSize: 16,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPermissionItem({
    required IconData icon,
    required String title,
    required String description,
    required bool isGranted,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isGranted ? AppTheme.successColor.withOpacity(0.1) : AppTheme.lightGrey,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isGranted ? AppTheme.successColor : AppTheme.mediumGrey,
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: isGranted ? AppTheme.successColor : AppTheme.brandPrimary,
              borderRadius: BorderRadius.circular(24),
            ),
            child: Icon(
              isGranted ? Icons.check : icon,
              color: Colors.white,
              size: 24,
            ),
          ),
          
          const SizedBox(width: 16),
          
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: isGranted ? AppTheme.successColor : AppTheme.black87,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppTheme.textSecondary,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          
          if (isGranted)
            const Icon(
              Icons.check_circle,
              color: AppTheme.successColor,
              size: 24,
            ),
        ],
      ),
    );
  }
}
