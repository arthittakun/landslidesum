import 'package:flutter/material.dart';
import '../controllers/auth_controller.dart';
import '../controllers/theme_controller.dart';
import '../theme/app_theme.dart';
import '../services/app_settings_service.dart';
import '../services/local_notification_service.dart';
import 'about_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final ThemeController _themeController = ThemeController();
  int _refreshInterval = 5;
  bool _notificationEnabled = true;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _themeController.addListener(_onThemeChanged);
    _loadNotificationSettings();
  }

  @override
  void dispose() {
    _themeController.removeListener(_onThemeChanged);
    super.dispose();
  }

  Future<void> _loadNotificationSettings() async {
    try {
      final settings = await AppSettingsService.getAllSettings();
      setState(() {
        _refreshInterval = settings['refreshInterval'] ?? 5;
        _notificationEnabled = settings['notificationEnabled'] ?? true;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _onThemeChanged() {
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('การตั้งค่า'),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: AppTheme.white,
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
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
            const Icon(
              Icons.settings,
              size: 80,
              color: AppTheme.brandPrimary,
            ),
            const SizedBox(height: 16),
            const Text(
              'การตั้งค่า',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'จัดการการตั้งค่าแอปพลิเคชัน',
              style: TextStyle(
                fontSize: 16,
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight 
                    : AppTheme.mediumGrey,
              ),
            ),
            const SizedBox(height: 24),
            Card(
              child: Column(
                children: [
                  ListTile(
                    leading: Icon(_themeController.themeModeIcon),
                    title: const Text('ธีมแอป'),
                    subtitle: Text(_themeController.themeModeDisplayName),
                    trailing: const Icon(Icons.arrow_forward_ios),
                    onTap: () {
                      _showThemeDialog(context);
                    },
                  ),
                  const Divider(height: 1),
                  
                  // Notification Settings Section
                  if (!_isLoading) ...[
                    SwitchListTile(
                      value: _notificationEnabled,
                      onChanged: (value) async {
                        setState(() {
                          _notificationEnabled = value;
                        });
                        await AppSettingsService.setNotificationEnabled(value);
                      },
                      title: const Text('การแจ้งเตือน'),
                      subtitle: Text(
                        _notificationEnabled 
                            ? 'ตรวจสอบความเสี่ยงทุก $_refreshInterval นาที' 
                            : 'ปิดการแจ้งเตือน',
                      ),
                      secondary: CircleAvatar(
                        backgroundColor: _notificationEnabled 
                            ? AppTheme.brandPrimary 
                            : AppTheme.textSecondary,
                        child: Icon(
                          _notificationEnabled 
                              ? Icons.notifications_active 
                              : Icons.notifications_off,
                          color: Colors.white,
                          size: 20,
                        ),
                      ),
                      activeColor: AppTheme.brandPrimary,
                    ),
                    
                    if (_notificationEnabled) ...[
                      const Divider(height: 1),
                      ListTile(
                        leading: CircleAvatar(
                          backgroundColor: AppTheme.brandPrimary,
                          child: const Icon(
                            Icons.schedule,
                            color: Colors.white,
                            size: 20,
                          ),
                        ),
                        title: const Text('ช่วงเวลาการแจ้งเตือน'),
                        subtitle: Text('ตรวจสอบทุก $_refreshInterval นาที'),
                        trailing: const Icon(Icons.edit),
                        onTap: _showIntervalOptions,
                      ),
                      const Divider(height: 1),
                      ListTile(
                        leading: CircleAvatar(
                          backgroundColor: AppTheme.warningColor,
                          child: const Icon(
                            Icons.science,
                            color: Colors.white,
                            size: 20,
                          ),
                        ),
                        title: const Text('ทดสอบการแจ้งเตือน'),
                        subtitle: const Text('ตรวจสอบระบบการแจ้งเตือน'),
                        trailing: const Icon(Icons.play_arrow),
                        onTap: _testNotification,
                      ),
                    ],
                    const Divider(height: 1),
                  ],
                  
                  ListTile(
                    leading: CircleAvatar(
                      backgroundColor: AppTheme.brandPrimary,
                      child: const Icon(
                        Icons.info,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                    title: const Text('เกี่ยวกับ'),
                    trailing: const Icon(Icons.arrow_forward_ios),
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (context) => const AboutScreen(),
                        ),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.logout, color: AppTheme.errorColor),
                    title: const Text(
                      'ออกจากระบบ',
                      style: TextStyle(color: AppTheme.errorColor),
                    ),
                    onTap: () {
                      _showLogoutDialog(context);
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

  void _showLogoutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('ออกจากระบบ'),
          content: const Text('คุณต้องการออกจากระบบหรือไม่?'),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              child: const Text('ยกเลิก'),
            ),
            TextButton(
              onPressed: () async {
                // เก็บ context ไว้ก่อนใช้ใน async
                final navigator = Navigator.of(context);
                navigator.pop(); // ปิด dialog ก่อน
                
                // แสดง loading
                showDialog(
                  context: context,
                  barrierDismissible: false,
                  builder: (context) => const Center(
                    child: CircularProgressIndicator(),
                  ),
                );
                
                try {
                  // ทำการ logout
                  await AuthController.logout();
                  
                  // ปิด loading และไปหน้า login
                  if (mounted) {
                    navigator.pop(); // ปิด loading
                    navigator.pushNamedAndRemoveUntil(
                      '/login',
                      (route) => false,
                    );
                  }
                } catch (e) {
                  // หาก logout ล้มเหลว
                  if (mounted) {
                    navigator.pop(); // ปิด loading
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('เกิดข้อผิดพลาดในการออกจากระบบ'),
                        backgroundColor: Colors.red,
                      ),
                    );
                  }
                }
              },
              child: const Text(
                'ออกจากระบบ',
                style: TextStyle(color: AppTheme.errorColor),
              ),
            ),
          ],
        );
      },
    );
  }

  void _showThemeDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Row(
            children: [
              Icon(Icons.palette, color: AppTheme.brandPrimary),
              SizedBox(width: 12),
              Text('เลือกธีมแอป'),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildThemeOption(
                context,
                AppThemeMode.light,
                'สว่าง',
                'ธีมสีสว่างสำหรับการใช้งานในเวลากลางวัน',
                Icons.light_mode,
              ),
              const Divider(),
              _buildThemeOption(
                context,
                AppThemeMode.dark,
                'มืด',
                'ธีมสีมืดสำหรับการใช้งานในเวลากลางคืน',
                Icons.dark_mode,
              ),
              const Divider(),
              _buildThemeOption(
                context,
                AppThemeMode.auto,
                'อัตโนมัติ',
                'ปรับธีมตามการตั้งค่าของระบบ',
                Icons.brightness_auto,
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('ปิด'),
            ),
          ],
        );
      },
    );
  }

  Widget _buildThemeOption(
    BuildContext context,
    AppThemeMode mode,
    String title,
    String subtitle,
    IconData icon,
  ) {
    final isSelected = _themeController.themeMode == mode;
    
    return ListTile(
      leading: Icon(
        icon,
        color: isSelected ? AppTheme.brandPrimary : null,
      ),
      title: Text(
        title,
        style: TextStyle(
          fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          color: isSelected ? AppTheme.brandPrimary : null,
        ),
      ),
      subtitle: Text(subtitle),
      trailing: isSelected
          ? const Icon(
              Icons.check_circle,
              color: AppTheme.brandPrimary,
            )
          : null,
      onTap: () async {
        await _themeController.setThemeMode(mode);
        if (context.mounted) {
          Navigator.of(context).pop();
        }
      },
    );
  }

  // Notification Settings Methods
  void _showIntervalOptions() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                Icon(Icons.schedule, color: AppTheme.brandPrimary),
                const SizedBox(width: 12),
                const Text(
                  'ตั้งค่าการแจ้งเตือนทุก',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 20),
            
            // Quick options
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: [
                _buildIntervalChip(15),
                _buildIntervalChip(20),
                _buildIntervalChip(25),
                _buildIntervalChip(30),
                _buildCustomIntervalChip(),
              ],
            ),
            
            const SizedBox(height: 20),
            Text(
              'ปัจจุบัน: ตรวจสอบทุก $_refreshInterval นาที',
              style: TextStyle(
                color: AppTheme.brandPrimary,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildIntervalChip(int minutes) {
    final isSelected = _refreshInterval == minutes;
    return GestureDetector(
      onTap: () async {
        setState(() {
          _refreshInterval = minutes;
        });
        await AppSettingsService.setApiRefreshInterval(minutes);
        if (mounted) {
          Navigator.pop(context);
        }
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.brandPrimary : Colors.grey[200],
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppTheme.brandPrimary : Colors.grey[400]!,
          ),
        ),
        child: Text(
          '$minutes นาที',
          style: TextStyle(
            color: isSelected ? Colors.white : Colors.black,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ),
    );
  }

  Widget _buildCustomIntervalChip() {
    final isCustom = ![15, 20, 25, 30].contains(_refreshInterval);
    return GestureDetector(
      onTap: () {
        Navigator.pop(context);
        _showCustomIntervalDialog();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isCustom ? AppTheme.warningColor : Colors.grey[200],
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isCustom ? AppTheme.warningColor : Colors.grey[400]!,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.edit,
              size: 16,
              color: isCustom ? Colors.white : Colors.black,
            ),
            const SizedBox(width: 4),
            Text(
              isCustom ? '$_refreshInterval นาที' : 'กำหนดเอง',
              style: TextStyle(
                color: isCustom ? Colors.white : Colors.black,
                fontWeight: isCustom ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showCustomIntervalDialog() {
    final controller = TextEditingController(text: _refreshInterval.toString());
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('กำหนดเวลาเอง'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: controller,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'จำนวนนาที',
                hintText: 'ระบุจำนวน 1-60 นาที',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'แนะนำ: 15-30 นาที',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('ยกเลิก'),
          ),
          ElevatedButton(
            onPressed: () async {
              final value = int.tryParse(controller.text);
              if (value != null && value > 0 && value <= 60) {
                setState(() {
                  _refreshInterval = value;
                });
                await AppSettingsService.setApiRefreshInterval(value);
                if (mounted) {
                  Navigator.pop(context);
                }
              }
            },
            child: const Text('บันทึก'),
          ),
        ],
      ),
    );
  }

  Future<void> _testNotification() async {
    try {
      // ตรวจสอบสถานะระบบการแจ้งเตือนโดยละเอียด
      final status = await LocalNotificationService.getNotificationStatus();
      
      if (status['status'] != 'ready') {
        // แสดง dialog สำหรับสถานะที่ไม่พร้อม
        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: const Row(
                children: [
                  Icon(Icons.warning, color: AppTheme.warningColor),
                  SizedBox(width: 8),
                  Text('ระบบการแจ้งเตือนไม่พร้อม'),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('สถานะ: ${status['status']}'),
                  Text('เริ่มต้นแล้ว: ${status['initialized']}'),
                  Text('มีสิทธิ์: ${status['hasPermission']}'),
                  Text('แพลตฟอร์ม: ${status['platform']}'),
                  if (status['error'] != null) ...[
                    const SizedBox(height: 8),
                    Text('ข้อผิดพลาด: ${status['error']}', 
                         style: const TextStyle(color: AppTheme.errorColor)),
                  ],
                  const SizedBox(height: 16),
                  const Text('กรุณาตรวจสอบการตั้งค่าการแจ้งเตือนในเครื่อง'),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('ปิด'),
                ),
              ],
            ),
          );
        }
        return;
      }

      // ทดสอบการแจ้งเตือน
      await LocalNotificationService.showTestNotification();

      // แสดงผลสำเร็จ
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.white),
                SizedBox(width: 8),
                Text('✅ ทดสอบการแจ้งเตือนสำเร็จ!'),
              ],
            ),
            backgroundColor: AppTheme.successColor,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      // แสดงข้อผิดพลาด
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error, color: Colors.white),
                const SizedBox(width: 8),
                Expanded(child: Text('❌ เกิดข้อผิดพลาด: $e')),
              ],
            ),
            backgroundColor: AppTheme.errorColor,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    }
  }
}
