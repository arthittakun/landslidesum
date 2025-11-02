import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import 'notification_service.dart';
import 'local_notification_service.dart';

/// **App Settings Service - บริการจัดการการตั้งค่าแอป**
/// 
/// ใช้สำหรับ:
/// - จัดการการตั้งค่าระยะเวลาการดึงข้อมูล API
/// - จัดการการแจ้งเตือน
/// - บันทึกและโหลดการตั้งค่า
class AppSettingsService {
  static const String _keyApiRefreshInterval = 'api_refresh_interval';
  static const String _keyNotificationEnabled = 'notification_enabled';
  static const String _keyLastNotificationCheck = 'last_notification_check';
  
  // ค่าเริ่มต้นสำหรับการดึงข้อมูล API (นาที)
  static const int defaultRefreshInterval = 5;
  
  static Timer? _refreshTimer;
  static Timer? _notificationTimer;
  static Function()? _onDataRefreshCallback;
  static Function(String)? _onNotificationCallback;

  /// ตั้งค่าระยะเวลาการดึงข้อมูล API (หน่วย: นาที)
  static Future<void> setApiRefreshInterval(int minutes) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_keyApiRefreshInterval, minutes);
    // รีสตาร์ทระบบรวม
    _startUnifiedCheckSystem();
  }

  /// ดึงระยะเวลาการดึงข้อมูล API (หน่วย: นาที)
  static Future<int> getApiRefreshInterval() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_keyApiRefreshInterval) ?? defaultRefreshInterval;
  }

  /// เปิด/ปิดการแจ้งเตือน
  static Future<void> setNotificationEnabled(bool enabled) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyNotificationEnabled, enabled);
    
    // รีสตาร์ทระบบรวม
    _startUnifiedCheckSystem();
  }

  /// ตรวจสอบว่าเปิดการแจ้งเตือนหรือไม่
  static Future<bool> isNotificationEnabled() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_keyNotificationEnabled) ?? true; // เปิดตามค่าเริ่มต้น
  }

  /// เริ่มต้นระบบ (เรียกเมื่อแอปเริ่มต้น)
  static Future<void> initialize({
    Function()? onDataRefresh,
    Function(String)? onNotification,
  }) async {
    _onDataRefreshCallback = onDataRefresh;
    _onNotificationCallback = onNotification;
    
    await _startUnifiedCheckSystem();
  }

  /// หยุดการทำงานของระบบ (เรียกเมื่อแอปปิด)
  static void dispose() {
    _refreshTimer?.cancel();
    _notificationTimer?.cancel();
    _refreshTimer = null;
    _notificationTimer = null;
  }

  /// เริ่มระบบตรวจสอบรวม
  static Future<void> _startUnifiedCheckSystem() async {
    // หยุด timers เก่า
    dispose();
    
    final refreshInterval = await getApiRefreshInterval();
    final notificationEnabled = await isNotificationEnabled();
    
    // เริ่ม timer สำหรับดึงข้อมูล และ ตรวจสอบการแจ้งเตือน
    if (refreshInterval > 0) {
      _refreshTimer = Timer.periodic(
        Duration(minutes: refreshInterval),
        (timer) async {
          await _checkForNewNotifications();
        },
      );
    }
    
    // ตรวจสอบครั้งแรกทันที
    if (notificationEnabled) {
      _notificationTimer = Timer.periodic(
        Duration(minutes: refreshInterval),
        (timer) async {
          await _checkForNewNotifications();
        },
      );
    }
  }

  /// ตรวจสอบการแจ้งเตือนใหม่
  static Future<void> _checkForNewNotifications() async {
    try {
      // ดึงการแจ้งเตือนจาก API เท่านั้น (ไม่รวม cache/persistent)
      final apiNotifications = await NotificationService.fetchNotifications(forceRefresh: true);
      
      if (apiNotifications.isNotEmpty) {
        // กรองเฉพาะการแจ้งเตือนใหม่ที่ยังไม่เคยแจ้งเตือนทางมือถือ
        final prefs = await SharedPreferences.getInstance();
        final processedMobileIds = prefs.getStringList('mobile_processed_notification_ids') ?? [];
        
        final newNotifications = apiNotifications.where((notification) {
          final id = notification['id']?.toString() ?? '';
          return id.isNotEmpty && !processedMobileIds.contains(id);
        }).toList();
        
        // หากมีการแจ้งเตือนใหม่
        for (final notification in newNotifications) {
          final text = notification['message'] as String? ?? 'มีการแจ้งเตือนใหม่';
          final locationName = notification['location_name'] as String? ?? '';
          final isCritical = notification['is_critical'] as bool? ?? false;
          
          // แปลง type ให้รองรับทั้ง int และ string
          int typeInt;
          final typeValue = notification['raw_type'];
          if (typeValue is int) {
            typeInt = typeValue;
          } else if (typeValue is String) {
            typeInt = int.tryParse(typeValue) ?? 1;
          } else {
            typeInt = 1;
          }
          
          // แปลง type จาก int เป็น string และปรับตาม critical status
          String typeString;
          String title;
          
          // หากเป็น critical notification ให้แสดงเป็น emergency
          if (isCritical) {
            typeString = 'emergency';
            final criticalTypes = NotificationService.getCriticalTypes(notification);
            if (criticalTypes.contains('flood') && criticalTypes.contains('landslide')) {
              title = 'เฝ้าระวังความเสี่ยงน้ำป่าไหลหลากและดินถล่ม';
            } else if (criticalTypes.contains('flood')) {
              title = 'เฝ้าระวังความเสี่ยงน้ำป่าไหลหลาก';
            } else if (criticalTypes.contains('landslide')) {
              title = 'เฝ้าระวังความเสี่ยงดินถล่ม';
            } else {
              title = 'เฝ้าระวังความเสี่ยง!';
            }
          } else {
            switch (typeInt) {
              case 1:
                typeString = 'info';
                title = 'ข้อมูลการตรวจสอบ';
                break;
              case 2:
                typeString = 'warning';
                title = 'เฝ้าระวังความเสี่ยง';
                break;
              case 3:
                typeString = 'critical';
                title = 'เฝ้าระวังความเสี่ยง!';
                break;
              case 4:
                typeString = 'emergency';
                title = 'เฝ้าระวังความเสี่ยง!';
                break;
              default:
                typeString = 'info';
                title = 'ข้อมูลการตรวจสอบ';
                break;
            }
          }
          
          // ส่งการแจ้งเตือนบนมือถือ
          await _showMobileNotification(
            typeString,
            title,
            text,
            locationName.isNotEmpty ? locationName : null,
          );
          
          // เรียก callback (ถ้ามี)
          _onNotificationCallback?.call('$title: $text');
          
          // บันทึก ID ที่แจ้งเตือนแล้ว (สำหรับมือถือ)
          final notificationId = notification['id']?.toString();
          if (notificationId != null && notificationId.isNotEmpty) {
            processedMobileIds.add(notificationId);
          }
        }
        
        // บันทึก ID ที่ประมวลผลแล้ว
        if (processedMobileIds.isNotEmpty) {
          // จำกัดจำนวน ID ที่เก็บไว้ (เก็บแค่ 500 ID ล่าสุด)
          if (processedMobileIds.length > 500) {
            processedMobileIds.removeRange(0, processedMobileIds.length - 500);
          }
          await prefs.setStringList('mobile_processed_notification_ids', processedMobileIds);
        }
      }
    } catch (e) {
      // Error handling - silent fail
    }
  }

  /// แสดงการแจ้งเตือนบนมือถือตามประเภท
  static Future<void> _showMobileNotification(
    String type, 
    String title, 
    String message, 
    String? locationName
  ) async {
    switch (type) {
      case 'emergency':
        await LocalNotificationService.showEmergencyNotification(
          title: title,
          message: message,
          locationName: locationName,
        );
        break;
      case 'critical':
      case 'warning':
        await LocalNotificationService.showWarningNotification(
          title: title,
          message: message,
          locationName: locationName,
        );
        break;
      case 'info':
      default:
        await LocalNotificationService.showInfoNotification(
          title: title,
          message: message,
          locationName: locationName,
        );
        break;
    }
  }

  /// ดึงการตั้งค่าทั้งหมด
  static Future<Map<String, dynamic>> getAllSettings() async {
    return {
      'refreshInterval': await getApiRefreshInterval(),
      'notificationEnabled': await isNotificationEnabled(),
    };
  }

  /// รีเซ็ตการตั้งค่าเป็นค่าเริ่มต้น
  static Future<void> resetToDefaults() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyApiRefreshInterval);
    await prefs.remove(_keyNotificationEnabled);
    await prefs.remove(_keyLastNotificationCheck);
    
    // รีสตาร์ทระบบ
    dispose();
    await initialize(
      onDataRefresh: _onDataRefreshCallback,
      onNotification: _onNotificationCallback,
    );
  }

  /// รีเฟรชข้อมูลทันที
  static void refreshDataNow() {
    _onDataRefreshCallback?.call();
    _checkForNewNotifications();
  }

  /// ตรวจสอบการแจ้งเตือนทันที
  static Future<void> checkNotificationsNow() async {
    await _checkForNewNotifications();
  }
}
