import 'dart:convert';
import 'package:http/http.dart' as http;
import 'secure_storage_service.dart';
import '../config/api_config.dart';

class DeviceStatusService {
  // Get Device Status
  static Future<DeviceStatusResult> getDeviceStatus() async {
    try {
      // ดึง access token จาก secure storage
      final token = await SecureStorageService.getAccessToken();
      if (token == null) {
        return DeviceStatusResult(
          success: false,
          message: 'กรุณาเข้าสู่ระบบใหม่',
        );
      }

      final response = await http.get(
        Uri.parse(ApiConfig.deviceStatusEndpoint),
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);

      // ตรวจสอบว่า response body เป็น JSON หรือไม่
      dynamic data;
      try {
        data = jsonDecode(response.body);
      } catch (jsonError) {
        return DeviceStatusResult(
          success: false,
          message: 'เซิร์ฟเวอร์ตอบกลับในรูปแบบที่ไม่ถูกต้อง (Status: ${response.statusCode})',
        );
      }

      if (response.statusCode == 200) {
        // ตรวจสอบรูปแบบ response
        if (data is Map<String, dynamic>) {
          // ถ้า response มี structure ใหม่ตาม API doc
          if (data.containsKey('status') && data['status'] == 'success') {
            return DeviceStatusResult(
              success: true,
              message: 'ดึงข้อมูลสถานะอุปกรณ์สำเร็จ',
              countOnline: data['count_online'] ?? 0,
              countOffline: data['count_offline'] ?? 0,
              countLocation: data['count_location'] ?? 0,
            );
          }
          // ถ้า response มี structure เก่า (success, data)
          else if (data.containsKey('success') && data['success'] == true) {
            final deviceData = data['data'] ?? {};
            return DeviceStatusResult(
              success: true,
              message: data['message'] ?? 'ดึงข้อมูลสถานะอุปกรณ์สำเร็จ',
              countOnline: deviceData['count_online'] ?? 0,
              countOffline: deviceData['count_offline'] ?? 0,
              countLocation: deviceData['count_location'] ?? 0,
            );
          }
        }

        // ถ้า response ไม่ตรงกับรูปแบบที่คาดหวัง
        return DeviceStatusResult(
          success: false,
          message: 'รูปแบบข้อมูลจากเซิร์ฟเวอร์ไม่ถูกต้อง',
        );
      } else if (response.statusCode == 401) {
        // Token หมดอายุ
        return DeviceStatusResult(
          success: false,
          message: 'กรุณาเข้าสู่ระบบใหม่',
          needsRelogin: true,
        );
      } else {
        String message = 'เกิดข้อผิดพลาดในการดึงข้อมูล';
        
        if (data is Map<String, dynamic> && data.containsKey('message')) {
          message = data['message'];
        }
        
        return DeviceStatusResult(
          success: false,
          message: message,
        );
      }
    } catch (e) {
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
      
      return DeviceStatusResult(
        success: false,
        message: errorMessage,
      );
    }
  }
}

// Result Class
class DeviceStatusResult {
  final bool success;
  final String message;
  final int countOnline;
  final int countOffline;
  final int countLocation;
  final bool needsRelogin;

  DeviceStatusResult({
    required this.success,
    required this.message,
    this.countOnline = 0,
    this.countOffline = 0,
    this.countLocation = 0,
    this.needsRelogin = false,
  });

  @override
  String toString() {
    return 'DeviceStatusResult(success: $success, message: $message, online: $countOnline, offline: $countOffline, locations: $countLocation)';
  }
}
