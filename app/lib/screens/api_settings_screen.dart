import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../theme/app_theme.dart';
import '../services/app_settings_service.dart';
import '../services/local_notification_service.dart';

class ApiSettingsScreen extends StatefulWidget {
  const ApiSettingsScreen({super.key});

  @override
  State<ApiSettingsScreen> createState() => _ApiSettingsScreenState();
}

class _ApiSettingsScreenState extends State<ApiSettingsScreen> {
  int _refreshInterval = 5;
  bool _notificationEnabled = true;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
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
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('เกิดข้อผิดพลาดในการโหลดการตั้งค่า: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }
  }

  Future<void> _saveRefreshInterval() async {
    try {
      await AppSettingsService.setApiRefreshInterval(_refreshInterval);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('บันทึกการตั้งค่าเรียบร้อย (ทุก $_refreshInterval นาที)'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('เกิดข้อผิดพลาดในการบันทึก: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }
  }

  Future<void> _saveNotificationSetting() async {
    try {
      await AppSettingsService.setNotificationEnabled(_notificationEnabled);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(_notificationEnabled 
                ? 'เปิดการแจ้งเตือนแล้ว' 
                : 'ปิดการแจ้งเตือนแล้ว'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('เกิดข้อผิดพลาดในการบันทึก: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }
  }

  void _showCustomIntervalDialog() {
    final TextEditingController controller = TextEditingController(
      text: _refreshInterval.toString(),
    );

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        title: Row(
          children: [
            Icon(
              Icons.timer,
              color: AppTheme.brandPrimary,
            ),
            const SizedBox(width: 8),
            Text(
              'ตั้งค่าเวลา',
              style: TextStyle(
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight 
                    : Colors.black,
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'ระบุจำนวนนาทีที่ต้องการให้แอปดึงข้อมูลจาก API',
              style: TextStyle(
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight.withOpacity(0.8) 
                    : Colors.black87,
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: controller,
              keyboardType: TextInputType.number,
              inputFormatters: [
                FilteringTextInputFormatter.digitsOnly,
              ],
              decoration: InputDecoration(
                labelText: 'จำนวนนาที',
                hintText: 'เช่น 5, 10, 15',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                prefixIcon: Icon(
                  Icons.access_time,
                  color: AppTheme.brandPrimary,
                ),
              ),
              style: TextStyle(
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight 
                    : Colors.black,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'แนะนำ: 1-60 นาที',
              style: TextStyle(
                fontSize: 12,
                color: AppTheme.textSecondary,
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text(
              'ยกเลิก',
              style: TextStyle(color: AppTheme.textSecondary),
            ),
          ),
          ElevatedButton(
            onPressed: () {
              final value = int.tryParse(controller.text);
              if (value != null && value > 0 && value <= 60) {
                setState(() {
                  _refreshInterval = value;
                });
                Navigator.of(context).pop();
                _saveRefreshInterval();
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('กรุณาระบุตัวเลขระหว่าง 1-60 นาที'),
                    backgroundColor: AppTheme.errorColor,
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.brandPrimary,
              foregroundColor: Colors.white,
            ),
            child: const Text('บันทึก'),
          ),
        ],
      ),
    );
  }

  void _testNotification() {
    // ทดสอบการแจ้งเตือนใน App
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              Icons.notifications_active,
              color: Colors.white,
            ),
            const SizedBox(width: 8),
            const Text('🔔 ทดสอบการแจ้งเตือนใน App'),
          ],
        ),
        backgroundColor: AppTheme.brandPrimary,
        duration: const Duration(seconds: 3),
        action: SnackBarAction(
          label: 'ปิด',
          textColor: Colors.white,
          onPressed: () {},
        ),
      ),
    );
    
    // ทดสอบการแจ้งเตือนบนมือถือ
    LocalNotificationService.showTestNotification();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('การตั้งค่า API และการแจ้งเตือน'),
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
        child: _isLoading
            ? const Center(
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(AppTheme.brandPrimary),
                ),
              )
            : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header
                    Card(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : AppTheme.lightOrange,
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Row(
                          children: [
                            Icon(
                              Icons.settings_applications,
                              color: AppTheme.brandPrimary,
                              size: 32,
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'การตั้งค่าระบบ',
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: Theme.of(context).brightness == Brightness.dark 
                                          ? AppTheme.textLight 
                                          : Colors.black,
                                    ),
                                  ),
                                  Text(
                                    'จัดการการดึงข้อมูลและการแจ้งเตือน',
                                    style: TextStyle(
                                      color: Theme.of(context).brightness == Brightness.dark 
                                          ? AppTheme.textLight.withOpacity(0.7) 
                                          : AppTheme.mediumGrey,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // API Refresh Settings
                    Text(
                      '📡 การดึงข้อมูล API',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight 
                            : Colors.black,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Card(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : Colors.white,
                      child: Column(
                        children: [
                          ListTile(
                            leading: CircleAvatar(
                              backgroundColor: AppTheme.brandPrimary,
                              child: const Icon(
                                Icons.schedule,
                                color: Colors.white,
                              ),
                            ),
                            title: const Text('ระยะเวลาการดึงข้อมูล'),
                            subtitle: Text('ทุก $_refreshInterval นาที'),
                            trailing: Icon(
                              Icons.edit,
                              color: AppTheme.brandPrimary,
                            ),
                            onTap: _showCustomIntervalDialog,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Notification Settings
                    Text(
                      '🔔 การแจ้งเตือน',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight 
                            : Colors.black,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Card(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : Colors.white,
                      child: Column(
                        children: [
                          SwitchListTile(
                            value: _notificationEnabled,
                            onChanged: (value) {
                              setState(() {
                                _notificationEnabled = value;
                              });
                              _saveNotificationSetting();
                            },
                            title: const Text('เปิดใช้การแจ้งเตือน'),
                            subtitle: Text(
                              _notificationEnabled 
                                  ? 'จะแจ้งเตือนเมื่อมีข้อมูลใหม่' 
                                  : 'ปิดการแจ้งเตือน',
                            ),
                            secondary: CircleAvatar(
                              backgroundColor: _notificationEnabled 
                                  ? AppTheme.successColor 
                                  : AppTheme.textSecondary,
                              child: Icon(
                                _notificationEnabled 
                                    ? Icons.notifications_active 
                                    : Icons.notifications_off,
                                color: Colors.white,
                              ),
                            ),
                            activeColor: AppTheme.brandPrimary,
                          ),
                          if (_notificationEnabled) ...[
                            const Divider(height: 1),
                            ListTile(
                              leading: CircleAvatar(
                                backgroundColor: AppTheme.warningColor,
                                child: const Icon(
                                  Icons.notifications_active,
                                  color: Colors.white,
                                ),
                              ),
                              title: const Text('ทดสอบการแจ้งเตือน'),
                              subtitle: const Text('ตรวจสอบว่าระบบทำงานปกติ'),
                              trailing: Icon(
                                Icons.play_arrow,
                                color: AppTheme.warningColor,
                              ),
                              onTap: _testNotification,
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Quick Actions
                    Text(
                      '⚡ การดำเนินการด่วน',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight 
                            : Colors.black,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Card(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : Colors.white,
                      child: Column(
                        children: [
                          ListTile(
                            leading: CircleAvatar(
                              backgroundColor: AppTheme.errorColor,
                              child: const Icon(
                                Icons.restore,
                                color: Colors.white,
                              ),
                            ),
                            title: const Text('รีเซ็ตการตั้งค่า'),
                            subtitle: const Text('กลับไปเป็นค่าเริ่มต้น'),
                            trailing: Icon(
                              Icons.warning,
                              color: AppTheme.errorColor,
                            ),
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => AlertDialog(
                                  title: const Text('รีเซ็ตการตั้งค่า'),
                                  content: const Text(
                                    'คุณต้องการรีเซ็ตการตั้งค่าทั้งหมดกลับเป็นค่าเริ่มต้นหรือไม่?'
                                  ),
                                  actions: [
                                    TextButton(
                                      onPressed: () => Navigator.of(context).pop(),
                                      child: const Text('ยกเลิก'),
                                    ),
                                    ElevatedButton(
                                      onPressed: () async {
                                        Navigator.of(context).pop();
                                        await AppSettingsService.resetToDefaults();
                                        await _loadSettings();
                                        if (mounted) {
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            const SnackBar(
                                              content: Text('รีเซ็ตการตั้งค่าเรียบร้อย'),
                                              backgroundColor: AppTheme.successColor,
                                            ),
                                          );
                                        }
                                      },
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: AppTheme.errorColor,
                                        foregroundColor: Colors.white,
                                      ),
                                      child: const Text('รีเซ็ต'),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Info Card
                    Card(
                      color: AppTheme.brandPrimary.withOpacity(0.1),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(
                                  Icons.info_outline,
                                  color: AppTheme.brandPrimary,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'ข้อมูลการตั้งค่า',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.brandPrimary,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              '• การดึงข้อมูลทำงานในพื้นหลังตามเวลาที่กำหนด\n'
                              '• การแจ้งเตือนจะตรวจสอบข้อมูลใหม่ทุก 30 วินาที\n'
                              '• ระยะเวลาที่แนะนำ: 1-10 นาทีสำหรับข้อมูลแบบเรียลไทม์',
                              style: TextStyle(
                                fontSize: 13,
                                color: Theme.of(context).brightness == Brightness.dark 
                                    ? AppTheme.textLight.withOpacity(0.8) 
                                    : Colors.black87,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
