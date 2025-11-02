import 'dart:convert';
import 'package:http/http.dart' as http;
import 'secure_storage_service.dart';
import '../config/api_config.dart';

class DeviceEnvironmentData {
  final String? location_name;
  final String deviceId;
  final String rain;
  final String temp;
  final String humid;
  final String soil;
  final String createAt;

  DeviceEnvironmentData({
    this.location_name,
    required this.deviceId,
    required this.rain,
    required this.temp,
    required this.humid,
    required this.soil,
    required this.createAt,
  });

  factory DeviceEnvironmentData.fromJson(Map<String, dynamic> json) {
    return DeviceEnvironmentData(
      deviceId: json['device_id'] ?? '',
      location_name: json['location'], // อนุญาตให้เป็น null
      rain: json['rain'] ?? '0.00%',
      temp: json['temp'] ?? '0.00%',
      humid: json['humid'] ?? '0.00%',
      soil: json['soil'] ?? '0.00%',
      createAt: json['create_at'] ?? '',
    );
  }
}

class PaginationInfo {
  final int page;
  final int pageSize;
  final int totalCount;
  final int totalPages;
  final bool hasNext;
  final bool hasPrevious;

  PaginationInfo({
    required this.page,
    required this.pageSize,
    required this.totalCount,
    required this.totalPages,
    required this.hasNext,
    required this.hasPrevious,
  });

  factory PaginationInfo.fromJson(Map<String, dynamic> json) {
    return PaginationInfo(
      page: json['page'] ?? 1,
      pageSize: json['page_size'] ?? 5,
      totalCount: json['total_count'] ?? 0,
      totalPages: json['total_pages'] ?? 0,
      hasNext: json['has_next'] ?? false,
      hasPrevious: json['has_previous'] ?? false,
    );
  }
}

class DeviceEnvironmentResult {
  final bool success;
  final String message;
  final List<DeviceEnvironmentData> data;
  final PaginationInfo? pagination;
  final bool needsRelogin;

  DeviceEnvironmentResult({
    required this.success,
    required this.message,
    this.data = const [],
    this.pagination,
    this.needsRelogin = false,
  });

  @override
  String toString() {
    return 'DeviceEnvironmentResult(success: $success, message: $message, dataCount: ${data.length})';
  }
}

class DeviceEnvironmentService {
  // Get Device Environment State
  static Future<DeviceEnvironmentResult> getEnvironmentState({
    int page = 1,
    int pageSize = 10,
  }) async {
    try {
      // ดึง access token จาก secure storage
      final token = await SecureStorageService.getAccessToken();
      if (token == null) {
        return DeviceEnvironmentResult(
          success: false,
          message: 'ไม่พบข้อมูลการเข้าสู่ระบบ กรุณาเข้าสู่ระบบใหม่',
          needsRelogin: true,
        );
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.environmentStateEndpoint}?page=$page&page_size=$pageSize'),
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);

      if (response.statusCode == 200) {
        // ตรวจสอบว่า response body เป็น JSON หรือไม่
        dynamic responseData;
        try {
          responseData = jsonDecode(response.body);
        } catch (jsonError) {
          return DeviceEnvironmentResult(
            success: false,
            message: 'เซิร์ฟเวอร์ตอบกลับในรูปแบบที่ไม่ถูกต้อง (Status: ${response.statusCode})',
          );
        }

        // ตรวจสอบ status
        if (responseData['status'] == 'success') {
          final List<dynamic> dataList = responseData['data'] ?? [];
          
          final List<DeviceEnvironmentData> environmentData = dataList
              .map((item) => DeviceEnvironmentData.fromJson(item))
              .toList();

          PaginationInfo? paginationInfo;
          if (responseData['pagination'] != null) {
            paginationInfo = PaginationInfo.fromJson(responseData['pagination']);
          } else {
            // สร้าง pagination info จำลองถ้าไม่มี
            paginationInfo = PaginationInfo(
              page: page,
              pageSize: pageSize,
              totalCount: dataList.length,
              totalPages: 1,
              hasNext: false,
              hasPrevious: false,
            );
          }
          
          return DeviceEnvironmentResult(
            success: true,
            message: 'ดึงข้อมูลสภาพแวดล้อมอุปกรณ์สำเร็จ',
            data: environmentData,
            pagination: paginationInfo,
          );
        } else {
          String errorMessage = responseData['message'] ?? 'เกิดข้อผิดพลาดในการดึงข้อมูล';
          
          return DeviceEnvironmentResult(
            success: false,
            message: errorMessage,
          );
        }
      } else if (response.statusCode == 401) {
        // ลบ token และต้องเข้าสู่ระบบใหม่
        await SecureStorageService.clearAll();
        return DeviceEnvironmentResult(
          success: false,
          message: 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่',
          needsRelogin: true,
        );
      } else {
        String message = 'เซิร์ฟเวอร์มีปัญหา (รหัสข้อผิดพลาด: ${response.statusCode})';
        
        return DeviceEnvironmentResult(
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
      
      return DeviceEnvironmentResult(
        success: false,
        message: errorMessage,
      );
    }
  }
}
