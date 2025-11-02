import 'dart:convert';
import 'package:flutter/foundation.dart'; // สำหรับ debugPrint
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'secure_storage_service.dart';
import '../config/api_config.dart';

/// **Notification Service - บริการจัดการการแจ้งเตือน**
/// 
/// ใช้สำหรับ:
/// - ดึงข้อมูลการแจ้งเตือนจาก API
/// - จัดการ token authentication
/// - แปลงข้อมูลให้อยู่ในรูปแบบที่ใช้งานได้
/// - แคชข้อมูลการแจ้งเตือน
class NotificationService {
  static const String _cacheKey = 'cached_notifications';
  static const String _lastFetchKey = 'last_notification_fetch';
  static const String _persistentNotificationsKey = 'persistent_notifications'; // เก็บการแจ้งเตือนถาวร
  static const String _processedNotificationIdsKey = 'processed_notification_ids'; // เก็บ ID ที่ประมวลผลแล้ว
  static const String _readNotificationIdsKey = 'read_notification_ids'; // เก็บ ID ที่อ่านแล้ว
  static const Duration _cacheExpiry = Duration(minutes: 5); // แคชหมดอายุใน 5 นาที
  
  /// ดึงข้อมูลการแจ้งเตือนทั้งหมด (จากแคช, Persistent Storage หรือ API)
  static Future<List<Map<String, dynamic>>> fetchNotifications({bool forceRefresh = false}) async {
    try {
      List<Map<String, dynamic>> allNotifications = [];
      
      // 1. ดึงข้อมูลการแจ้งเตือนถาวรก่อน
      final persistentNotifications = await _getPersistentNotifications();
      allNotifications.addAll(persistentNotifications);
      
      // 2. ตรวจสอบแคชก่อน (ถ้าไม่ force refresh)
      if (!forceRefresh) {
        final cachedData = await _getCachedNotifications();
        if (cachedData.isNotEmpty) {
          // รวมกับข้อมูลถาวรและลบข้อมูลซ้ำ
          allNotifications = _mergeDuplicateNotifications(allNotifications, cachedData);
          return allNotifications;
        }
      }

      // 3. ดึงข้อมูลจาก API
      final apiNotifications = await _fetchFromAPI();
      
      // 4. กรองการแจ้งเตือนใหม่ที่ยังไม่เคยประมวลผล
      final newNotifications = await _filterNewNotifications(apiNotifications);
      
      // 5. เก็บการแจ้งเตือนใหม่ลง Persistent Storage
      if (newNotifications.isNotEmpty) {
        await _saveToPersistentStorage(newNotifications);
        allNotifications.addAll(newNotifications);
      }
      
      // 6. รวมข้อมูลและลบข้อมูลซ้ำ
      allNotifications = _mergeDuplicateNotifications(allNotifications, apiNotifications);
      
      // 7. บันทึกลงแคช
      await _cacheNotifications(allNotifications);
      
      return allNotifications;
    } catch (e) {
      // หาก API ล้มเหลว ให้ใช้ข้อมูลจาก Persistent Storage หรือแคช
      final persistentData = await _getPersistentNotifications();
      if (persistentData.isNotEmpty) {
        return persistentData;
      }
      
      final cachedData = await _getCachedNotifications();
      if (cachedData.isNotEmpty) {
        return cachedData;
      }
      
      throw Exception('Failed to fetch notifications: $e');
    }
  }

  /// ดึงข้อมูลจาก API
  static Future<List<Map<String, dynamic>>> _fetchFromAPI() async {
    final token = await SecureStorageService.getAccessToken();
    if (token == null) {
      throw Exception('No authentication token found');
    }

    final response = await http.get(
      Uri.parse(ApiConfig.notificationCheckEndpoint),
      headers: ApiConfig.getAuthHeaders(token),
    ).timeout(ApiConfig.timeoutDuration);

    if (response.statusCode == 200) {
      final Map<String, dynamic> jsonResponse = json.decode(response.body);
      
      if (jsonResponse['status'] == 'success' && jsonResponse['data'] != null) {
        final List<dynamic> notifications = jsonResponse['data'];
        
        // แปลงข้อมูลให้อยู่ในรูปแบบที่ใช้งานได้
        return notifications.map((notification) => _formatNotification(notification)).toList();
      } else {
        throw Exception('Invalid response format: ${jsonResponse['message'] ?? 'Unknown error'}');
      }
    } else {
      throw Exception('Failed to fetch notifications: ${response.statusCode}');
    }
  }

  /// แปลงข้อมูลการแจ้งเตือนให้อยู่ในรูปแบบที่ใช้งานได้
  static Map<String, dynamic> _formatNotification(Map<String, dynamic> rawNotification) {
    return {
      'id': rawNotification['notification_id'] ?? '',
      'device_id': rawNotification['device_id'] ?? '',
      'location_id': rawNotification['location_id'] ?? '',
      'type': _getNotificationType(rawNotification['type']),
      'title': _generateTitle(rawNotification),
      'message': rawNotification['text'] ?? 'ไม่มีข้อความ',
      'time': _formatDateTime(rawNotification['create_at']),
      'device_name': rawNotification['device_name'] ?? 'ไม่ระบุอุปกรณ์',
      'location_name': rawNotification['location_name'] ?? 'ไม่ระบุตำแหน่ง',
      'raw_type': rawNotification['type'],
      'create_at': rawNotification['create_at'],
      // ข้อมูลใหม่
      'latitude': rawNotification['latitude'],
      'longitude': rawNotification['longtitude'],
      'img_path': rawNotification['img_path'],
      'img_url': rawNotification['img_url'],
      'flood_level': rawNotification['floot'] ?? 0,
      'landslide_level': rawNotification['landslide'] ?? 0,
      'datekey': rawNotification['datekey'],
      'timekey': rawNotification['timekey'],
      'is_critical': rawNotification['is_critical'] ?? false,
      'critical_types': rawNotification['critical_type'] ?? [],
      // สร้าง URL รูปภาพเต็มจาก img_path
      'full_image_url': _buildFullImageUrl(rawNotification['img_path']),
    };
  }

  /// สร้าง URL รูปภาพเต็มจาก img_path
  static String? _buildFullImageUrl(String? imgPath) {
    if (imgPath == null || imgPath.isEmpty) return null;
    
    // หากเป็น URL เต็มแล้ว
    if (imgPath.startsWith('http://') || imgPath.startsWith('https://')) {
      return imgPath;
    }
    
    // หากเป็น path เฉพาะ ให้ต่อกับ base URL
    if (imgPath.startsWith('/')) {
      return '${ApiConfig.baseUrl}$imgPath';
    }
    
    // หากเป็น path ปกติ (ไม่มี / นำหน้า) ให้ต่อกับ base URL + /
    return '${ApiConfig.baseUrl}/$imgPath';
  }

  /// แปลงประเภทการแจ้งเตือนจากตัวเลขเป็นข้อความ
  static String _getNotificationType(dynamic type) {
    // แปลง type เป็น int อย่างปลอดภัย
    int? typeInt;
    if (type is int) {
      typeInt = type;
    } else if (type is String) {
      typeInt = int.tryParse(type);
    }
    
    switch (typeInt) {
      case 1:
        return 'info';      // ข้อมูลทั่วไป
      case 2:
        return 'warning';   // คำเตือน
      case 3:
        return 'critical';  // วิกฤต
      case 4:
        return 'emergency'; // ฉุกเฉิน
      default:
        return 'info';      // ค่าเริ่มต้น
    }
  }

  /// สร้างหัวข้อการแจ้งเตือนตามประเภท
  static String _generateTitle(Map<String, dynamic> notification) {
    // แปลง type เป็น int อย่างปลอดภัย
    int type = 1; // ค่าเริ่มต้น
    final rawType = notification['type'];
    if (rawType is int) {
      type = rawType;
    } else if (rawType is String) {
      type = int.tryParse(rawType) ?? 1;
    }
    
    final String locationName = notification['location_name'] ?? 'ตำแหน่งไม่ทราบ';
    
    // แปลง is_critical เป็น bool อย่างปลอดภัย
    bool isCritical = false;
    final rawIsCritical = notification['is_critical'];
    if (rawIsCritical is bool) {
      isCritical = rawIsCritical;
    } else if (rawIsCritical is int) {
      isCritical = rawIsCritical == 1;
    } else if (rawIsCritical is String) {
      isCritical = rawIsCritical == '1' || rawIsCritical.toLowerCase() == 'true';
    }
    
    // หากเป็น critical ให้แสดงสถานะพิเศษ
    if (isCritical) {
      final criticalTypes = notification['critical_type'];
      List<String> criticalTypesList = [];
      
      if (criticalTypes is List) {
        criticalTypesList = criticalTypes.map((e) => e.toString()).toList();
      }
      
      if (criticalTypesList.contains('flood') && criticalTypesList.contains('landslide')) {
        return 'เฝ้าระวังความเสี่ยงน้ำป่าไหลหลากและดินถล่ม - $locationName';
      } else if (criticalTypesList.contains('flood')) {
        return 'เฝ้าระวังความเสี่ยงน้ำป่าไหลหลาก - $locationName';
      } else if (criticalTypesList.contains('landslide')) {
        return 'เฝ้าระวังความเสี่ยงดินถล่ม - $locationName';
      }
    }
    
    switch (type) {
      case 1:
        return 'ข้อมูลจาก $locationName';
      case 2:
        return 'เฝ้าระวังความเสี่ยง - $locationName';
      case 3:
        return 'เฝ้าระวังความเสี่ยง! - $locationName';
      case 4:
        return 'เฝ้าระวังความเสี่ยง! - $locationName';
      default:
        return 'การแจ้งเตือน - $locationName';
    }
  }

  /// แปลงวันที่และเวลาให้อยู่ในรูปแบบที่อ่านง่าย
  static String _formatDateTime(String? dateTimeString) {
    if (dateTimeString == null || dateTimeString.isEmpty) {
      return 'ไม่ทราบเวลา';
    }
    
    try {
      final DateTime dateTime = DateTime.parse(dateTimeString);
      final DateTime now = DateTime.now();
      
      // แสดงเวลาตาม create_at ที่แท้จริง
      final months = [
        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
      ];
      
      // หากเป็นวันนี้ ให้แสดงเวลา
      if (dateTime.year == now.year && dateTime.month == now.month && dateTime.day == now.day) {
        return 'วันนี้ ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
      }
      
      // หากเป็นเมื่อวาน ให้แสดงเวลา
      final yesterday = now.subtract(const Duration(days: 1));
      if (dateTime.year == yesterday.year && dateTime.month == yesterday.month && dateTime.day == yesterday.day) {
        return 'เมื่อวาน ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
      }
      
      // หากเป็นวันอื่น ให้แสดงวันที่และเวลาเต็ม
      return '${dateTime.day} ${months[dateTime.month - 1]} ${dateTime.year + 543} ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
      
    } catch (e) {
      // หากไม่สามารถ parse ได้ ให้แสดงข้อมูลต้นฉบับ
      return dateTimeString;
    }
  }

  /// ดึงสีตามประเภทการแจ้งเตือน
  static String getNotificationColor(String type) {
    switch (type) {
      case 'info':
        return 'brandPrimary';
      case 'warning':
        return 'warningColor';
      case 'critical':
        return 'errorColor';
      case 'emergency':
        return 'errorColor';
      default:
        return 'brandPrimary';
    }
  }

  /// ดึงไอคอนตามประเภทการแจ้งเตือน
  static String getNotificationIcon(String type) {
    switch (type) {
      case 'info':
        return 'info';
      case 'warning':
        return 'warning';
      case 'critical':
        return 'error';
      case 'emergency':
        return 'emergency';
      default:
        return 'info';
    }
  }

  /// ตรวจสอบว่ามีการแจ้งเตือนใหม่หรือไม่
  static Future<int> getUnreadNotificationCount() async {
    try {
      final notifications = await fetchNotifications();
      final readIds = await _getReadNotificationIds();
      
      int unreadCount = 0;
      for (final notification in notifications) {
        final id = notification['id']?.toString() ?? '';
        if (id.isNotEmpty && !readIds.contains(id)) {
          unreadCount++;
        }
      }
      
      return unreadCount;
    } catch (e) {
      return 0;
    }
  }

  /// ตรวจสอบว่าการแจ้งเตือนถูกอ่านแล้วหรือไม่
  static Future<bool> isNotificationRead(String notificationId) async {
    try {
      final readIds = await _getReadNotificationIds();
      return readIds.contains(notificationId);
    } catch (e) {
      return false;
    }
  }

  /// ทำเครื่องหมายว่าการแจ้งเตือนถูกอ่านแล้ว
  static Future<void> markNotificationAsRead(String notificationId) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final readIds = prefs.getStringList(_readNotificationIdsKey) ?? [];
      
      if (!readIds.contains(notificationId)) {
        readIds.add(notificationId);
        
        // จำกัดจำนวน ID ที่เก็บไว้ (เก็บแค่ 1000 ID ล่าสุด)
        if (readIds.length > 1000) {
          readIds.removeRange(0, readIds.length - 1000);
        }
        
        await prefs.setStringList(_readNotificationIdsKey, readIds);
        debugPrint('Marked notification $notificationId as read');
      }
    } catch (e) {
      debugPrint('Error marking notification as read: $e');
    }
  }

  /// ทำเครื่องหมายว่าการแจ้งเตือนหลายรายการถูกอ่านแล้ว
  static Future<void> markMultipleNotificationsAsRead(List<String> notificationIds) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final readIds = prefs.getStringList(_readNotificationIdsKey) ?? [];
      
      for (final id in notificationIds) {
        if (!readIds.contains(id)) {
          readIds.add(id);
        }
      }
      
      // จำกัดจำนวน ID ที่เก็บไว้ (เก็บแค่ 1000 ID ล่าสุด)
      if (readIds.length > 1000) {
        readIds.removeRange(0, readIds.length - 1000);
      }
      
      await prefs.setStringList(_readNotificationIdsKey, readIds);
      debugPrint('Marked ${notificationIds.length} notifications as read');
    } catch (e) {
      debugPrint('Error marking multiple notifications as read: $e');
    }
  }

  /// ล้างประวัติการอ่านการแจ้งเตือนทั้งหมด
  static Future<void> clearReadHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_readNotificationIdsKey);
      debugPrint('Cleared read notification history');
    } catch (e) {
      debugPrint('Error clearing read history: $e');
    }
  }

  /// ดึงรายการ ID ของการแจ้งเตือนที่อ่านแล้ว
  static Future<List<String>> _getReadNotificationIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getStringList(_readNotificationIdsKey) ?? [];
    } catch (e) {
      debugPrint('Error getting read notification IDs: $e');
      return [];
    }
  }

  /// ดึงการแจ้งเตือนที่ยังไม่ได้อ่าน
  static Future<List<Map<String, dynamic>>> getUnreadNotifications() async {
    try {
      final notifications = await fetchNotifications();
      final readIds = await _getReadNotificationIds();
      
      return notifications.where((notification) {
        final id = notification['id']?.toString() ?? '';
        return id.isNotEmpty && !readIds.contains(id);
      }).toList();
    } catch (e) {
      return [];
    }
  }

  /// ดึงการแจ้งเตือนที่อ่านแล้ว
  static Future<List<Map<String, dynamic>>> getReadNotifications() async {
    try {
      final notifications = await fetchNotifications();
      final readIds = await _getReadNotificationIds();
      
      return notifications.where((notification) {
        final id = notification['id']?.toString() ?? '';
        return id.isNotEmpty && readIds.contains(id);
      }).toList();
    } catch (e) {
      return [];
    }
  }

  /// ดึงรายการ ID ของการแจ้งเตือนที่อ่านแล้ว (public method)
  static Future<List<String>> getReadNotificationIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getStringList(_readNotificationIdsKey) ?? [];
    } catch (e) {
      debugPrint('Error getting read notification IDs: $e');
      return [];
    }
  }

  /// ดึงการแจ้งเตือนที่สำคัญ (critical) เท่านั้น
  static Future<List<Map<String, dynamic>>> getCriticalNotifications() async {
    try {
      final notifications = await fetchNotifications();
      return notifications.where((n) => isCriticalNotification(n)).toList();
    } catch (e) {
      return [];
    }
  }

  /// ดึงการแจ้งเตือนที่มีรูปภาพ
  static Future<List<Map<String, dynamic>>> getNotificationsWithImages() async {
    try {
      final notifications = await fetchNotifications();
      return notifications.where((n) => hasImage(n)).toList();
    } catch (e) {
      return [];
    }
  }

  /// ดึงการแจ้งเตือนตามประเภท
  static Future<List<Map<String, dynamic>>> getNotificationsByType(String type) async {
    try {
      final notifications = await fetchNotifications();
      return notifications.where((n) => n['type'] == type).toList();
    } catch (e) {
      return [];
    }
  }

  /// ดึงการแจ้งเตือนตามตำแหน่ง
  static Future<List<Map<String, dynamic>>> getNotificationsByLocation(String locationId) async {
    try {
      final notifications = await fetchNotifications();
      return notifications.where((n) => n['location_id'] == locationId).toList();
    } catch (e) {
      return [];
    }
  }

  /// ดึงการแจ้งเตือนตามอุปกรณ์
  static Future<List<Map<String, dynamic>>> getNotificationsByDevice(String deviceId) async {
    try {
      final notifications = await fetchNotifications();
      return notifications.where((n) => n['device_id'] == deviceId).toList();
    } catch (e) {
      return [];
    }
  }

  /// ตรวจสอบสถานะการแคช
  static Future<Map<String, dynamic>> getCacheStatus() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedData = prefs.getString(_cacheKey);
      final lastFetch = prefs.getString(_lastFetchKey);
      
      if (cachedData == null || lastFetch == null) {
        return {
          'hasCache': false,
          'cacheAge': null,
          'cacheSize': 0,
        };
      }
      
      final lastFetchTime = DateTime.parse(lastFetch);
      final now = DateTime.now();
      final cacheAge = now.difference(lastFetchTime);
      
      return {
        'hasCache': true,
        'cacheAge': cacheAge.inMinutes,
        'cacheSize': cachedData.length,
        'isExpired': cacheAge > _cacheExpiry,
      };
    } catch (e) {
      return {
        'hasCache': false,
        'cacheAge': null,
        'cacheSize': 0,
        'error': e.toString(),
      };
    }
  }

  /// บันทึกข้อมูลการแจ้งเตือนลงแคช
  static Future<void> _cacheNotifications(List<Map<String, dynamic>> notifications) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final now = DateTime.now().toIso8601String();
      
      await prefs.setString(_cacheKey, json.encode(notifications));
      await prefs.setString(_lastFetchKey, now);
    } catch (e) {
      // Error handling
    }
  }

  /// ดึงข้อมูลการแจ้งเตือนจากแคช
  static Future<List<Map<String, dynamic>>> _getCachedNotifications() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedData = prefs.getString(_cacheKey);
      final lastFetch = prefs.getString(_lastFetchKey);
      
      if (cachedData == null || lastFetch == null) {
        return [];
      }
      
      // ตรวจสอบว่าแคชหมดอายุหรือไม่
      final lastFetchTime = DateTime.parse(lastFetch);
      final now = DateTime.now();
      if (now.difference(lastFetchTime) > _cacheExpiry) {
        return [];
      }
      
      final List<dynamic> decodedData = json.decode(cachedData);
      return decodedData.cast<Map<String, dynamic>>();
    } catch (e) {
      return [];
    }
  }

  /// ล้างแคชการแจ้งเตือน
  static Future<void> clearCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_cacheKey);
      await prefs.remove(_lastFetchKey);
    } catch (e) {
      // Error handling
    }
  }

  /// ตรวจสอบว่ามีรูปภาพหรือไม่
  static bool hasImage(Map<String, dynamic> notification) {
    final imgPath = notification['img_path'];
    return imgPath != null && imgPath.isNotEmpty;
  }

  /// ดึงระดับความรุนแรงของน้ำป่า
  static int getFloodLevel(Map<String, dynamic> notification) {
    final rawLevel = notification['flood_level'];
    if (rawLevel is int) return rawLevel;
    if (rawLevel is String) return int.tryParse(rawLevel) ?? 0;
    return 0;
  }

  /// ดึงระดับความรุนแรงของดินถล่ม
  static int getLandslideLevel(Map<String, dynamic> notification) {
    final rawLevel = notification['landslide_level'];
    if (rawLevel is int) return rawLevel;
    if (rawLevel is String) return int.tryParse(rawLevel) ?? 0;
    return 0;
  }

  /// ตรวจสอบว่าเป็น critical notification หรือไม่
  static bool isCriticalNotification(Map<String, dynamic> notification) {
    final rawIsCritical = notification['is_critical'];
    if (rawIsCritical is bool) return rawIsCritical;
    if (rawIsCritical is int) return rawIsCritical == 1;
    if (rawIsCritical is String) return rawIsCritical == '1' || rawIsCritical.toLowerCase() == 'true';
    return false;
  }

  /// ดึงประเภท critical
  static List<String> getCriticalTypes(Map<String, dynamic> notification) {
    final types = notification['critical_types'];
    if (types is List) {
      return types.cast<String>();
    }
    return [];
  }

  // ===== PERSISTENT STORAGE METHODS =====

  /// ดึงข้อมูลการแจ้งเตือนที่เก็บถาวร
  static Future<List<Map<String, dynamic>>> _getPersistentNotifications() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final persistentDataJson = prefs.getStringList(_persistentNotificationsKey) ?? [];
      
      return persistentDataJson.map((jsonStr) {
        return Map<String, dynamic>.from(jsonDecode(jsonStr));
      }).toList();
    } catch (e) {
      debugPrint('Error getting persistent notifications: $e');
      return [];
    }
  }

  /// เก็บการแจ้งเตือนลง Persistent Storage
  static Future<void> _saveToPersistentStorage(List<Map<String, dynamic>> notifications) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      // ดึงข้อมูลเก่า
      final existingNotifications = await _getPersistentNotifications();
      
      // รวมข้อมูลและลบซ้ำ
      final allNotifications = _mergeDuplicateNotifications(existingNotifications, notifications);
      
      // แปลงเป็น JSON strings
      final jsonStrings = allNotifications.map((notification) {
        return jsonEncode(notification);
      }).toList();
      
      // บันทึกลง SharedPreferences
      await prefs.setStringList(_persistentNotificationsKey, jsonStrings);
      
      // บันทึก ID ที่ประมวลผลแล้ว
      await _markNotificationsAsProcessed(notifications);
      
      debugPrint('Saved ${notifications.length} new notifications to persistent storage');
    } catch (e) {
      debugPrint('Error saving to persistent storage: $e');
    }
  }

  /// กรองการแจ้งเตือนใหม่ที่ยังไม่เคยประมวลผล
  static Future<List<Map<String, dynamic>>> _filterNewNotifications(List<Map<String, dynamic>> notifications) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final processedIds = prefs.getStringList(_processedNotificationIdsKey) ?? [];
      
      return notifications.where((notification) {
        final id = notification['id']?.toString() ?? '';
        return id.isNotEmpty && !processedIds.contains(id);
      }).toList();
    } catch (e) {
      debugPrint('Error filtering new notifications: $e');
      return notifications; // ถ้าเกิดข้อผิดพลาด ให้ส่งข้อมูลทั้งหมด
    }
  }

  /// บันทึก ID ของการแจ้งเตือนที่ประมวลผลแล้ว
  static Future<void> _markNotificationsAsProcessed(List<Map<String, dynamic>> notifications) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final existingIds = prefs.getStringList(_processedNotificationIdsKey) ?? [];
      
      final newIds = notifications
          .map((notification) => notification['id']?.toString() ?? '')
          .where((id) => id.isNotEmpty && !existingIds.contains(id))
          .toList();
      
      if (newIds.isNotEmpty) {
        existingIds.addAll(newIds);
        
        // จำกัดจำนวน ID ที่เก็บไว้ (เก็บแค่ 1000 ID ล่าสุด)
        if (existingIds.length > 1000) {
          existingIds.removeRange(0, existingIds.length - 1000);
        }
        
        await prefs.setStringList(_processedNotificationIdsKey, existingIds);
        debugPrint('Marked ${newIds.length} notifications as processed');
      }
    } catch (e) {
      debugPrint('Error marking notifications as processed: $e');
    }
  }

  /// รวมการแจ้งเตือนและลบข้อมูลซ้ำตาม ID
  static List<Map<String, dynamic>> _mergeDuplicateNotifications(
    List<Map<String, dynamic>> existing, 
    List<Map<String, dynamic>> newData
  ) {
    final Map<String, Map<String, dynamic>> mergedMap = {};
    
    // เพิ่มข้อมูลเก่า
    for (final notification in existing) {
      final id = notification['id']?.toString() ?? '';
      if (id.isNotEmpty) {
        mergedMap[id] = notification;
      }
    }
    
    // เพิ่มข้อมูลใหม่ (จะ overwrite ข้อมูลเก่าถ้า ID ซ้ำ)
    for (final notification in newData) {
      final id = notification['id']?.toString() ?? '';
      if (id.isNotEmpty) {
        mergedMap[id] = notification;
      }
    }
    
    // แปลงกลับเป็น List และเรียงตาม timestamp
    final result = mergedMap.values.toList();
    result.sort((a, b) {
      final timestampA = a['created_at']?.toString() ?? '';
      final timestampB = b['created_at']?.toString() ?? '';
      return timestampB.compareTo(timestampA); // เรียงจากใหม่ไปเก่า
    });
    
    return result;
  }

  /// ล้างข้อมูล Persistent Storage (สำหรับ debug หรือ maintenance)
  static Future<void> clearPersistentStorage() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_persistentNotificationsKey);
      await prefs.remove(_processedNotificationIdsKey);
      await prefs.remove(_readNotificationIdsKey);
      debugPrint('Cleared persistent notification storage');
    } catch (e) {
      debugPrint('Error clearing persistent storage: $e');
    }
  }
}
