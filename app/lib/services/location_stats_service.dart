import 'dart:convert';
import 'dart:developer' as dev;
import 'package:http/http.dart' as http;
import 'package:latlong2/latlong.dart';
import 'secure_storage_service.dart';
import '../config/api_config.dart';

class LocationStatsService {
  // Get Location Stats
  static Future<LocationStatsResult> getLocationStats() async {
    dev.log('🗺️ [LocationStats] เริ่มดึงข้อมูลสถานที่', name: 'LocationStatsService');
    
    try {
      // ดึง access token จาก secure storage
      final token = await SecureStorageService.getAccessToken();
      if (token == null) {
        dev.log('❌ [LocationStats] ไม่มี access token', name: 'LocationStatsService');
        return LocationStatsResult(
          success: false,
          message: 'กรุณาเข้าสู่ระบบใหม่',
        );
      }

      dev.log('🌐 [LocationStats] URL: ${ApiConfig.locationStatsEndpoint}', name: 'LocationStatsService');
      dev.log('🔑 [LocationStats] ใช้ token: ${token.substring(0, 20)}...', name: 'LocationStatsService');

      final response = await http.get(
        Uri.parse(ApiConfig.locationStatsEndpoint),
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);

      dev.log('📨 [LocationStats] Response Status: ${response.statusCode}', name: 'LocationStatsService');
      dev.log('📨 [LocationStats] Response Body: ${response.body}', name: 'LocationStatsService');
      
      dev.log('📨 [LocationStats] Response Status: ${response.statusCode}', name: 'LocationStatsService');
      dev.log('📨 [LocationStats] Response Body: ${response.body}', name: 'LocationStatsService');

      if (response.statusCode == 200) {
        // ตรวจสอบว่า response body เป็น JSON หรือไม่
        dynamic data;
        try {
          data = jsonDecode(response.body);
          dev.log('✅ [LocationStats] JSON ถูกต้อง', name: 'LocationStatsService');
        } catch (jsonError) {
          dev.log('❌ [LocationStats] JSON Error: ${jsonError.toString()}', name: 'LocationStatsService');
          return LocationStatsResult(
            success: false,
            message: 'เซิร์ฟเวอร์ตอบกลับในรูปแบบที่ไม่ถูกต้อง (Status: ${response.statusCode})',
          );
        }

        // ตรวจสอบว่า data เป็น List หรือไม่
        if (data is List) {
          List<LocationData> locations = [];
          
          for (var item in data) {
            if (item is Map<String, dynamic>) {
              try {
                final location = LocationData(
                  locationId: item['location_id']?.toString() ?? '',
                  locationName: item['location_name']?.toString() ?? 'ไม่ระบุชื่อ',
                  position: LatLng(
                    (item['lat'] as num?)?.toDouble() ?? 0.0,
                    (item['lon'] as num?)?.toDouble() ?? 0.0,
                  ),
                );
                locations.add(location);
              } catch (e) {
                dev.log('⚠️ [LocationStats] ข้อผิดพลาดในการแปลงข้อมูล location: $e', name: 'LocationStatsService');
              }
            }
          }

          dev.log('✅ [LocationStats] สำเร็จ: ดึงข้อมูล ${locations.length} สถานที่', name: 'LocationStatsService');
          return LocationStatsResult(
            success: true,
            message: 'ดึงข้อมูลสถานที่สำเร็จ',
            locations: locations,
          );
        }
        // ถ้า response มี structure แบบ object wrapper
        else if (data is Map<String, dynamic>) {
          if (data.containsKey('success') && data['success'] == true && data.containsKey('data')) {
            final locationList = data['data'];
            if (locationList is List) {
              List<LocationData> locations = [];
              
              for (var item in locationList) {
                if (item is Map<String, dynamic>) {
                  try {
                    final location = LocationData(
                      locationId: item['location_id']?.toString() ?? '',
                      locationName: item['location_name']?.toString() ?? 'ไม่ระบุชื่อ',
                      position: LatLng(
                        (item['lat'] as num?)?.toDouble() ?? 0.0,
                        (item['lon'] as num?)?.toDouble() ?? 0.0,
                      ),
                    );
                    locations.add(location);
                  } catch (e) {
                    dev.log('⚠️ [LocationStats] ข้อผิดพลาดในการแปลงข้อมูล location: $e', name: 'LocationStatsService');
                  }
                }
              }

              return LocationStatsResult(
                success: true,
                message: data['message'] ?? 'ดึงข้อมูลสถานที่สำเร็จ',
                locations: locations,
              );
            }
          }
        }

        // ถ้า response ไม่ตรงกับรูปแบบที่คาดหวัง
        dev.log('⚠️ [LocationStats] รูปแบบ response ไม่ตรงกับที่คาดหวัง', name: 'LocationStatsService');
        return LocationStatsResult(
          success: false,
          message: 'รูปแบบข้อมูลจากเซิร์ฟเวอร์ไม่ถูกต้อง',
        );
      } else if (response.statusCode == 401) {
        // Token หมดอายุ
        dev.log('❌ [LocationStats] Token หมดอายุ', name: 'LocationStatsService');
        return LocationStatsResult(
          success: false,
          message: 'กรุณาเข้าสู่ระบบใหม่',
          needsRelogin: true,
        );
      } else {
        dev.log('❌ [LocationStats] API Error: Status ${response.statusCode}', name: 'LocationStatsService');
        String message = 'เกิดข้อผิดพลาดในการดึงข้อมูล';
        
        try {
          final data = jsonDecode(response.body);
          if (data is Map<String, dynamic> && data.containsKey('message')) {
            message = data['message'];
          }
        } catch (e) {
          // ไม่สามารถแปลง JSON ได้
        }
        
        return LocationStatsResult(
          success: false,
          message: message,
        );
      }
    } catch (e) {
      dev.log('💥 [LocationStats] Exception: ${e.toString()}', name: 'LocationStatsService');
      
      String errorMessage;
      if (e.toString().contains('SocketException')) {
        errorMessage = 'ไม่สามารถเชื่อมต่ออินเทอร์เน็ตได้ กรุณาตรวจสอบการเชื่อมต่อ';
      } else if (e.toString().contains('TimeoutException')) {
        errorMessage = 'การเชื่อมต่อใช้เวลานานเกินไป กรุณาลองใหม่';
      } else if (e.toString().contains('HandshakeException')) {
        errorMessage = 'เกิดข้อผิดพลาดในการเชื่อมต่อ SSL/TLS';
      } else {
        errorMessage = 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ${e.toString()}';
      }
      
      return LocationStatsResult(
        success: false,
        message: errorMessage,
      );
    }
  }
}

// Data Classes
class LocationData {
  final String locationId;
  final String locationName;
  final LatLng position;

  LocationData({
    required this.locationId,
    required this.locationName,
    required this.position,
  });

  @override
  String toString() {
    return 'LocationData(id: $locationId, name: $locationName, lat: ${position.latitude}, lng: ${position.longitude})';
  }
}

// Result Class
class LocationStatsResult {
  final bool success;
  final String message;
  final List<LocationData> locations;
  final bool needsRelogin;

  LocationStatsResult({
    required this.success,
    required this.message,
    this.locations = const [],
    this.needsRelogin = false,
  });

  @override
  String toString() {
    return 'LocationStatsResult(success: $success, message: $message, locations: ${locations.length})';
  }
}
