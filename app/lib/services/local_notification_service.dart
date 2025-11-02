import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:timezone/timezone.dart' as tz;
import 'package:timezone/data/latest_all.dart' as tz;

/// **Local Notification Service - บริการแจ้งเตือนบนมือถือ**
/// 
/// ใช้สำหรับ:
/// - แจ้งเตือนผ่านระบบมือถือ
/// - จัดการ permission การแจ้งเตือน
/// - รองรับ Android และ iOS
class LocalNotificationService {
  static final FlutterLocalNotificationsPlugin _flutterLocalNotificationsPlugin =
      FlutterLocalNotificationsPlugin();
  
  static bool _initialized = false;
  static Function(String?)? _onNotificationTap;

  /// เริ่มต้นระบบการแจ้งเตือน
  static Future<bool> initialize({Function(String?)? onNotificationTap}) async {
    if (_initialized) return true;

    try {
      // เริ่มต้น timezone
      tz.initializeTimeZones();
      
      _onNotificationTap = onNotificationTap;

      // กำหนดการตั้งค่าเริ่มต้นสำหรับ Android - ใช้ไอคอนระบบ
      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@android:drawable/ic_dialog_info');

      // กำหนดการตั้งค่าเริ่มต้นสำหรับ iOS
      const DarwinInitializationSettings initializationSettingsIOS =
          DarwinInitializationSettings(
        requestAlertPermission: true,
        requestBadgePermission: true,
        requestSoundPermission: true,
      );

      const InitializationSettings initializationSettings =
          InitializationSettings(
        android: initializationSettingsAndroid,
        iOS: initializationSettingsIOS,
      );

      final bool? initialized = await _flutterLocalNotificationsPlugin.initialize(
        initializationSettings,
        onDidReceiveNotificationResponse: _onNotificationResponse,
      );

      if (initialized == true) {
        _initialized = true;
        
        // ขอ permission สำหรับการแจ้งเตือน
        await _requestPermissions();
      }

      return initialized ?? false;
    } catch (e) {
      return false;
    }
  }

  /// จัดการเมื่อผู้ใช้กดการแจ้งเตือน
  static void _onNotificationResponse(NotificationResponse response) {
    _onNotificationTap?.call(response.payload);
  }

  /// ขอ permission สำหรับการแจ้งเตือน
  static Future<bool> _requestPermissions() async {
    try {
      if (Platform.isAndroid) {
        // สำหรับ Android 13+ ต้องขอ permission
        final status = await Permission.notification.request();
        return status.isGranted;
      } else if (Platform.isIOS) {
        // สำหรับ iOS ขอ permission ผ่าน plugin
        final bool? granted = await _flutterLocalNotificationsPlugin
            .resolvePlatformSpecificImplementation<
                IOSFlutterLocalNotificationsPlugin>()
            ?.requestPermissions(
              alert: true,
              badge: true,
              sound: true,
            );
        return granted ?? false;
      }
      return true;
    } catch (e) {
      return false;
    }
  }

  /// ตรวจสอบ permission การแจ้งเตือน
  static Future<bool> hasPermission() async {
    if (Platform.isAndroid) {
      return await Permission.notification.isGranted;
    } else if (Platform.isIOS) {
      final bool? granted = await _flutterLocalNotificationsPlugin
          .resolvePlatformSpecificImplementation<
              IOSFlutterLocalNotificationsPlugin>()
          ?.requestPermissions(
            alert: false,
            badge: false,
            sound: false,
          );
      return granted ?? false;
    }
    return true;
  }

  /// แสดงการแจ้งเตือนแบบทันที
  static Future<void> showInstantNotification({
    required String title,
    required String body,
    String? payload,
    Priority priority = Priority.defaultPriority,
    Importance importance = Importance.defaultImportance,
  }) async {
    if (!_initialized) {
      await initialize();
    }

    try {
      final androidPlatformChannelSpecifics = AndroidNotificationDetails(
        'landslide_instant',
        'การแจ้งเตือนทันที',
        channelDescription: 'การแจ้งเตือนที่แสดงทันที',
        importance: importance,
        priority: priority,
        icon: '@android:drawable/ic_dialog_info', // ใช้ไอคอนระบบ
        showWhen: true,
        enableVibration: true,
        playSound: true,
      );

      const iOSPlatformChannelSpecifics = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      final platformChannelSpecifics = NotificationDetails(
        android: androidPlatformChannelSpecifics,
        iOS: iOSPlatformChannelSpecifics,
      );

      await _flutterLocalNotificationsPlugin.show(
        DateTime.now().millisecondsSinceEpoch ~/ 1000,
        title,
        body,
        platformChannelSpecifics,
        payload: payload,
      );
    } catch (e) {
      // Error handling - แสดง log สำหรับ debug
      rethrow; // ส่งต่อ error เพื่อให้ caller จัดการได้
    }
  }

  /// แสดงการแจ้งเตือนสำหรับข้อมูลใหม่
  static Future<void> showDataUpdateNotification({
    required int count,
    String? locationName,
  }) async {
    final title = locationName != null
        ? 'ข้อมูลใหม่จาก $locationName'
        : 'มีข้อมูลใหม่';
    
    final body = count == 1
        ? 'มีการแจ้งเตือน 1 รายการใหม่'
        : 'มีการแจ้งเตือน $count รายการใหม่';

    await showInstantNotification(
      title: title,
      body: body,
      payload: 'data_update',
      priority: Priority.high,
      importance: Importance.high,
    );
  }

  /// แสดงการแจ้งเตือนสำหรับเหตุการณ์ฉุกเฉิน
  static Future<void> showEmergencyNotification({
    required String title,
    required String message,
    String? locationName,
  }) async {
    final fullTitle = locationName != null
        ? '🚨 ฉุกเฉิน: $locationName'
        : '🚨 ฉุกเฉิน';

    await showInstantNotification(
      title: fullTitle,
      body: message,
      payload: 'emergency',
      priority: Priority.max,
      importance: Importance.max,
    );

    // สำหรับกรณีฉุกเฉิน แสดงการแจ้งเตือนซ้ำหลังจาก 5 นาที
    await _scheduleFollowUpNotification(
      title: 'ติดตาม: $fullTitle',
      body: 'ตรวจสอบสถานการณ์อีกครั้ง',
      delayMinutes: 5,
    );
  }

  /// แสดงการแจ้งเตือนคำเตือน
  static Future<void> showWarningNotification({
    required String title,
    required String message,
    String? locationName,
  }) async {
    final fullTitle = locationName != null
        ? '⚠️ คำเตือน: $locationName'
        : '⚠️ คำเตือน';

    await showInstantNotification(
      title: fullTitle,
      body: message,
      payload: 'warning',
      priority: Priority.high,
      importance: Importance.high,
    );
  }

  /// แสดงการแจ้งเตือนข้อมูลทั่วไป
  static Future<void> showInfoNotification({
    required String title,
    required String message,
    String? locationName,
  }) async {
    final fullTitle = locationName != null
        ? '📊 ข้อมูล: $locationName'
        : '📊 ข้อมูล';

    await showInstantNotification(
      title: fullTitle,
      body: message,
      payload: 'info',
      priority: Priority.defaultPriority,
      importance: Importance.defaultImportance,
    );
  }

  /// กำหนดการแจ้งเตือนตามเวลา
  static Future<void> _scheduleFollowUpNotification({
    required String title,
    required String body,
    required int delayMinutes,
  }) async {
    try {
      final scheduledDate = DateTime.now().add(Duration(minutes: delayMinutes));

      final androidPlatformChannelSpecifics = AndroidNotificationDetails(
        'landslide_scheduled',
        'การแจ้งเตือนตามเวลา',
        channelDescription: 'การแจ้งเตือนที่จัดเวลาไว้',
        importance: Importance.high,
        priority: Priority.high,
        icon: '@android:drawable/ic_dialog_info', // ใช้ไอคอนระบบ
        color: const Color(0xFFFF6F00), // สีส้มสำหรับการติดตาม
        showWhen: true,
        enableVibration: true,
        playSound: true,
      );

      const iOSPlatformChannelSpecifics = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      final platformChannelSpecifics = NotificationDetails(
        android: androidPlatformChannelSpecifics,
        iOS: iOSPlatformChannelSpecifics,
      );

      await _flutterLocalNotificationsPlugin.zonedSchedule(
        DateTime.now().millisecondsSinceEpoch ~/ 1000 + delayMinutes,
        title,
        body,
        tz.TZDateTime.from(scheduledDate, tz.local),
        platformChannelSpecifics,
        payload: 'follow_up',
        uiLocalNotificationDateInterpretation:
            UILocalNotificationDateInterpretation.absoluteTime,
      );
    } catch (e) {
      // Error handling
    }
  }

  /// ลบการแจ้งเตือนที่รอการแสดง
  static Future<void> cancelAllNotifications() async {
    try {
      await _flutterLocalNotificationsPlugin.cancelAll();
    } catch (e) {
      // Error handling
    }
  }

  /// ลบการแจ้งเตือนตาม ID
  static Future<void> cancelNotification(int id) async {
    try {
      await _flutterLocalNotificationsPlugin.cancel(id);
    } catch (e) {
      // Error handling
    }
  }

  /// ดึงการแจ้งเตือนที่รอการแสดง
  static Future<List<PendingNotificationRequest>> getPendingNotifications() async {
    try {
      return await _flutterLocalNotificationsPlugin.pendingNotificationRequests();
    } catch (e) {
      return [];
    }
  }

  /// ตรวจสอบว่าระบบการแจ้งเตือนพร้อมใช้งานหรือไม่
  static bool get isInitialized => _initialized;

  /// ตรวจสอบสถานะการแจ้งเตือนโดยละเอียด
  static Future<Map<String, dynamic>> getNotificationStatus() async {
    try {
      final hasPermission = await LocalNotificationService.hasPermission();
      final pendingNotifications = await getPendingNotifications();
      
      return {
        'initialized': _initialized,
        'hasPermission': hasPermission,
        'pendingCount': pendingNotifications.length,
        'platform': Platform.operatingSystem,
        'status': _initialized && hasPermission ? 'ready' : 'not_ready'
      };
    } catch (e) {
      return {
        'initialized': _initialized,
        'hasPermission': false,
        'pendingCount': 0,
        'platform': Platform.operatingSystem,
        'status': 'error',
        'error': e.toString()
      };
    }
  }

  /// แสดงการแจ้งเตือนทดสอบ
  static Future<void> showTestNotification() async {
    try {
      final status = await getNotificationStatus();
      
      if (status['status'] != 'ready') {
        throw Exception('Notification system not ready: ${status['status']}');
      }
      
      await showInstantNotification(
        title: '🧪 ทดสอบการแจ้งเตือน',
        body: 'ระบบการแจ้งเตือนทำงานปกติ - ${DateTime.now().toString()}',
        payload: 'test',
        priority: Priority.high,
        importance: Importance.high,
      );
    } catch (e) {
      rethrow;
    }
  }
}
