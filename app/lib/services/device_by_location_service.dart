import 'dart:convert';
import 'dart:developer' as dev;
import 'package:http/http.dart' as http;
import 'secure_storage_service.dart';
import '../config/api_config.dart';

class DeviceData {
  final String deviceId;
  final String deviceName;
  final String locationId;
  final String serialNo;
  final int voidStatus;
  final int takePhoto;
  final String locationName;
  final String latitude;
  final String longitude;

  DeviceData({
    required this.deviceId,
    required this.deviceName,
    required this.locationId,
    required this.serialNo,
    required this.voidStatus,
    required this.takePhoto,
    required this.locationName,
    required this.latitude,
    required this.longitude,
  });

  factory DeviceData.fromJson(Map<String, dynamic> json) {
    return DeviceData(
      deviceId: json['device_id'] ?? '',
      deviceName: json['device_name'] ?? '',
      locationId: json['location_id'] ?? '',
      serialNo: json['serialno'] ?? '',  // เปลี่ยนจาก serial_no เป็น serialno
      voidStatus: _parseToInt(json['void']),  // แปลงเป็น int อย่างปลอดภัย
      takePhoto: _parseToInt(json['take_photo']),  // แปลงเป็น int อย่างปลอดภัย
      locationName: json['location_name'] ?? '',
      latitude: json['latitude'] ?? '',
      longitude: json['longtitude'] ?? '',
    );
  }
  
  // Helper method เพื่อแปลงค่าเป็น int อย่างปลอดภัย
  static int _parseToInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    if (value is double) return value.toInt();
    return 0;
  }
}

class LocationInfo {
  final String locationId;
  final String locationName;
  final String latitude;
  final String longitude;
  final double lat;
  final double lng;

  LocationInfo({
    required this.locationId,
    required this.locationName,
    required this.latitude,
    required this.longitude,
    required this.lat,
    required this.lng,
  });

  factory LocationInfo.fromJson(Map<String, dynamic> json) {
    final coordinates = json['coordinates'] ?? {};
    return LocationInfo(
      locationId: json['location_id'] ?? '',
      locationName: json['location_name'] ?? '',
      latitude: json['latitude'] ?? '',
      longitude: json['longtitude'] ?? '',
      lat: (coordinates['lat'] ?? 0.0).toDouble(),
      lng: (coordinates['lng'] ?? 0.0).toDouble(),
    );
  }
}

class DeviceStatistics {
  final List<String> deviceIds;
  final List<String> deviceNames;
  final List<String> serialNumbers;

  DeviceStatistics({
    required this.deviceIds,
    required this.deviceNames,
    required this.serialNumbers,
  });

  factory DeviceStatistics.fromJson(Map<String, dynamic> json) {
    return DeviceStatistics(
      deviceIds: List<String>.from(json['device_ids'] ?? []),
      deviceNames: List<String>.from(json['device_names'] ?? []),
      serialNumbers: List<String>.from(json['serial_numbers'] ?? []),
    );
  }
}

class UserInfo {
  final String username;
  final String email;
  final int loginTime;
  final int iat;
  final int exp;
  final String iss;

  UserInfo({
    required this.username,
    required this.email,
    required this.loginTime,
    required this.iat,
    required this.exp,
    required this.iss,
  });

  factory UserInfo.fromJson(Map<String, dynamic> json) {
    return UserInfo(
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      loginTime: json['login_time'] ?? 0,
      iat: json['iat'] ?? 0,
      exp: json['exp'] ?? 0,
      iss: json['iss'] ?? '',
    );
  }
}

class DevicesByLocationResult {
  final bool success;
  final String message;
  final LocationInfo? locationInfo;
  final int totalDevices;
  final int activeDevices;
  final List<DeviceData> devices;
  final DeviceStatistics? deviceStatistics;
  final UserInfo? userInfo;
  final String? timestamp;
  final int? code;
  final bool needsRelogin;

  DevicesByLocationResult({
    required this.success,
    required this.message,
    this.locationInfo,
    this.totalDevices = 0,
    this.activeDevices = 0,
    this.devices = const [],
    this.deviceStatistics,
    this.userInfo,
    this.timestamp,
    this.code,
    this.needsRelogin = false,
  });
}

class DeviceByLocationService {
  static Future<DevicesByLocationResult> getDevicesByLocation(String locationId) async {
    dev.log('🔍 [DeviceByLocationService] กำลังดึงข้อมูลอุปกรณ์สำหรับสถานที่: $locationId', name: 'DeviceByLocationService');
    
    try {
      final token = await SecureStorageService.getAccessToken();
      
      dev.log('🔑 [DeviceByLocationService] ตรวจสอบ token: ${token?.isNotEmpty == true ? "มี token (${token!.length} chars)" : "ไม่มี token"}', name: 'DeviceByLocationService');
      
      if (token == null || token.isEmpty) {
        dev.log('❌ [DeviceByLocationService] ไม่พบ token', name: 'DeviceByLocationService');
        return DevicesByLocationResult(
          success: false,
          message: 'ไม่พบข้อมูลการเข้าสู่ระบบ',
          needsRelogin: true,
        );
      }

      // ตรวจสอบว่า token หมดอายุหรือไม่
      final isExpired = await SecureStorageService.isTokenExpired();
      if (isExpired) {
        dev.log('⏰ [DeviceByLocationService] Token หมดอายุแล้ว', name: 'DeviceByLocationService');
        return DevicesByLocationResult(
          success: false,
          message: 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
          needsRelogin: true,
        );
      }

      final url = Uri.parse('${ApiConfig.deviceByLocationEndpoint}?location=$locationId');
      dev.log('🌐 [DeviceByLocationService] URL: $url', name: 'DeviceByLocationService');
      dev.log('🌐 [DeviceByLocationService] Headers: Authorization: Bearer ${token.substring(0, 20)}...', name: 'DeviceByLocationService');

      final response = await http.get(
        url,
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);

      dev.log('📡 [DeviceByLocationService] Response status: ${response.statusCode}', name: 'DeviceByLocationService');
      dev.log('📡 [DeviceByLocationService] Response headers: ${response.headers}', name: 'DeviceByLocationService');
      dev.log('📡 [DeviceByLocationService] Response content-type: ${response.headers['content-type']}', name: 'DeviceByLocationService');
      
      // ตรวจสอบ response body ว่าเป็น JSON หรือไม่
      dev.log('📡 [DeviceByLocationService] Raw response body length: ${response.body.length}', name: 'DeviceByLocationService');
      
      if (response.body.isEmpty) {
        dev.log('❌ [DeviceByLocationService] Response body is empty', name: 'DeviceByLocationService');
        return DevicesByLocationResult(
          success: false,
          message: 'ไม่ได้รับข้อมูลจากเซิร์ฟเวอร์',
        );
      }
      
      // แสดง response body แบบ raw
      if (response.body.length > 1000) {
        dev.log('📡 [DeviceByLocationService] Response body (first 500 chars): ${response.body.substring(0, 500)}', name: 'DeviceByLocationService');
        dev.log('📡 [DeviceByLocationService] Response body (last 500 chars): ${response.body.substring(response.body.length - 500)}', name: 'DeviceByLocationService');
      } else {
        dev.log('📡 [DeviceByLocationService] Full response body: ${response.body}', name: 'DeviceByLocationService');
      }
      
      // ตรวจสอบว่า response เริ่มต้นด้วย { หรือไม่
      final trimmedBody = response.body.trim();
      if (!trimmedBody.startsWith('{') && !trimmedBody.startsWith('[')) {
        dev.log('❌ [DeviceByLocationService] Response is not JSON format. First character: "${trimmedBody.isNotEmpty ? trimmedBody[0] : "empty"}"', name: 'DeviceByLocationService');
        return DevicesByLocationResult(
          success: false,
          message: 'ข้อมูลที่ได้รับจากเซิร์ฟเวอร์ไม่ถูกต้อง (ไม่ใช่ JSON)',
        );
      }

      if (response.statusCode == 200) {
        Map<String, dynamic> jsonData;
        
        try {
          dev.log('🔄 [DeviceByLocationService] พยายาม parse JSON...', name: 'DeviceByLocationService');
          jsonData = json.decode(response.body);
          dev.log('✅ [DeviceByLocationService] Parse JSON สำเร็จ', name: 'DeviceByLocationService');
          dev.log('📊 [DeviceByLocationService] JSON keys: ${jsonData.keys.toList()}', name: 'DeviceByLocationService');
        } catch (e) {
          dev.log('💥 [DeviceByLocationService] JSON Parse Error: $e', name: 'DeviceByLocationService');
          dev.log('📄 [DeviceByLocationService] Problematic response: ${response.body}', name: 'DeviceByLocationService');
          
          return DevicesByLocationResult(
            success: false,
            message: 'ไม่สามารถแปลงข้อมูลจากเซิร์ฟเวอร์ได้: $e',
          );
        }
        
        if (jsonData['success'] == true) {
          dev.log('✅ [DeviceByLocationService] API response success = true', name: 'DeviceByLocationService');
          
          try {
            final data = jsonData['data'];
            if (data == null) {
              dev.log('❌ [DeviceByLocationService] data field is null', name: 'DeviceByLocationService');
              return DevicesByLocationResult(
                success: false,
                message: 'ไม่พบข้อมูลในการตอบกลับจากเซิร์ฟเวอร์',
              );
            }
            
            dev.log('📊 [DeviceByLocationService] Data keys: ${data.keys.toList()}', name: 'DeviceByLocationService');
            
            final locationInfo = LocationInfo.fromJson(data['location_info']);
            dev.log('📍 [DeviceByLocationService] Location info parsed: ${locationInfo.locationName}', name: 'DeviceByLocationService');
            
            final devicesJson = data['devices'] as List;
            dev.log('📱 [DeviceByLocationService] Devices array length: ${devicesJson.length}', name: 'DeviceByLocationService');
            
            final devices = devicesJson.map((device) {
              try {
                return DeviceData.fromJson(device);
              } catch (e) {
                dev.log('❌ [DeviceByLocationService] Error parsing device: $e', name: 'DeviceByLocationService');
                dev.log('📄 [DeviceByLocationService] Problematic device data: $device', name: 'DeviceByLocationService');
                rethrow;
              }
            }).toList();
            
            final devicesCount = data['devices_count'];
            final activeDevices = devicesCount['active'] ?? 0;
            final totalDevices = data['total_devices'] ?? 0;
            
            // Parse device statistics
            DeviceStatistics? deviceStats;
            if (data['device_statistics'] != null) {
              deviceStats = DeviceStatistics.fromJson(data['device_statistics']);
              dev.log('📊 [DeviceByLocationService] Device statistics parsed', name: 'DeviceByLocationService');
            }
            
            // Parse user info
            UserInfo? userInfo;
            if (jsonData['user_info'] != null) {
              userInfo = UserInfo.fromJson(jsonData['user_info']);
              dev.log('👤 [DeviceByLocationService] User info parsed: ${userInfo.username}', name: 'DeviceByLocationService');
            }

            dev.log('✅ [DeviceByLocationService] ดึงข้อมูลสำเร็จ: ${devices.length} อุปกรณ์ (active: $activeDevices, total: $totalDevices)', name: 'DeviceByLocationService');
            
            return DevicesByLocationResult(
              success: true,
              message: jsonData['message'] ?? 'ดึงข้อมูลอุปกรณ์สำเร็จ',
              locationInfo: locationInfo,
              totalDevices: totalDevices,
              activeDevices: activeDevices,
              devices: devices,
              deviceStatistics: deviceStats,
              userInfo: userInfo,
              timestamp: jsonData['timestamp'],
              code: jsonData['code'],
            );
          } catch (e, stackTrace) {
            dev.log('💥 [DeviceByLocationService] Error processing success response: $e', name: 'DeviceByLocationService');
            dev.log('📚 [DeviceByLocationService] Stack trace: $stackTrace', name: 'DeviceByLocationService');
            
            return DevicesByLocationResult(
              success: false,
              message: 'เกิดข้อผิดพลาดในการประมวลผลข้อมูล: $e',
            );
          }
        } else {
          dev.log('❌ [DeviceByLocationService] API response success = false', name: 'DeviceByLocationService');
          final message = jsonData['message'] ?? 'เกิดข้อผิดพลาดในการดึงข้อมูล';
          dev.log('❌ [DeviceByLocationService] Error message: $message', name: 'DeviceByLocationService');
          
          return DevicesByLocationResult(
            success: false,
            message: message,
          );
        }
      } else if (response.statusCode == 401) {
        dev.log('🔒 [DeviceByLocationService] Status 401 - Unauthorized', name: 'DeviceByLocationService');
        
        // พยายาม parse response body เพื่อหา error message
        try {
          final errorData = json.decode(response.body);
          final errorMessage = errorData['message'] ?? 'Token หมดอายุ';
          dev.log('🔒 [DeviceByLocationService] 401 Error message: $errorMessage', name: 'DeviceByLocationService');
        } catch (e) {
          dev.log('🔒 [DeviceByLocationService] Cannot parse 401 response body: ${response.body}', name: 'DeviceByLocationService');
        }
        
        return DevicesByLocationResult(
          success: false,
          message: 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
          needsRelogin: true,
        );
      } else {
        dev.log('❌ [DeviceByLocationService] HTTP Error: ${response.statusCode} ${response.reasonPhrase}', name: 'DeviceByLocationService');
        dev.log('❌ [DeviceByLocationService] Error response body: ${response.body}', name: 'DeviceByLocationService');
        
        // พยายาม parse error message จาก response
        String errorMessage = 'HTTP Error ${response.statusCode}: ${response.reasonPhrase ?? "Unknown error"}';
        try {
          final errorData = json.decode(response.body);
          errorMessage = errorData['message'] ?? errorMessage;
        } catch (e) {
          dev.log('❌ [DeviceByLocationService] Cannot parse error response body', name: 'DeviceByLocationService');
        }
        
        return DevicesByLocationResult(
          success: false,
          message: errorMessage,
        );
      }
    } catch (e, stackTrace) {
      dev.log('💥 [DeviceByLocationService] Exception: $e', name: 'DeviceByLocationService');
      dev.log('📚 [DeviceByLocationService] Stack trace: $stackTrace', name: 'DeviceByLocationService');
      
      return DevicesByLocationResult(
        success: false,
        message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ: $e',
      );
    }
  }
}
