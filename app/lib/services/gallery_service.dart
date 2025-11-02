import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/gallery_response.dart';
import 'secure_storage_service.dart';

class GalleryService {
  static const String refreshEndpoint = ApiConfig.refreshEndpoint;

  /// รีเฟรชโทเค็น
  static Future<String?> refreshToken() async {
    try {
      final refreshToken = await SecureStorageService.getRefreshToken();
      if (refreshToken == null || refreshToken.isEmpty) {
        return null;
      }
      
      final response = await http.post(
        Uri.parse(refreshEndpoint),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $refreshToken',
        },
        body: jsonEncode({
          'refresh_token': refreshToken,
        }),
      ).timeout(ApiConfig.timeoutDuration);
     
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['access_token'] != null) {
          final newAccessToken = data['access_token'];
          
          // บันทึกโทเค็นใหม่
          await SecureStorageService.saveAccessToken(newAccessToken);
          
          // บันทึก refresh token ใหม่ (ถ้ามี)
          if (data['refresh_token'] != null) {
            await SecureStorageService.saveRefreshToken(data['refresh_token']);
          }
          
          return newAccessToken;
        } else {
          return null;
        }
      } else {
        return null;
      }
    } catch (e) {
      return null;
    }
  }

  /// ดึงข้อมูลแกลเลอรี่พร้อมการจัดการโทเค็นหมดอายุ
  static Future<GalleryResponse?> getGallery({
    int page = 1,
    int pageSize = 20,
  }) async {
    try {
      final token = await SecureStorageService.getAccessToken();
      if (token == null || token.isEmpty) {
        return null;
      }

      final url = '${ApiConfig.applicationBaseUrl}/getgallery?page=$page&page_size=$pageSize';
      
      final response = await http.get(
        Uri.parse(url),
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);
     
      if (response.statusCode == 200) {
        final  data = jsonDecode(response.body);
        print("200");
        if (data['status'] == 'success') {
          print(data);
          return  GalleryResponse.fromJson(data);;
        } else {
          return null;
        }
      } else if (isTokenExpired(response.statusCode)) {
        print("401");
        // ลองรีเฟรชโทเค็น
        final newToken = await refreshToken();
        if (newToken != null) {
          // ลองใหม่ด้วยโทเค็นใหม่
          final retryResponse = await http.get(
            Uri.parse(url),
            headers: ApiConfig.getAuthHeaders(newToken),
          ).timeout(ApiConfig.timeoutDuration);

          if (retryResponse.statusCode == 200) {
            final retryData = jsonDecode(retryResponse.body);
            if (retryData['status'] == 'success') {
              return GalleryResponse.fromJson(retryData);
            }
          }
        }
        
        return null;
      } else {
        return null;
      } 
      
    } catch (e) {
      print("Error: $e");
      return null;
    }
   
  }
  
  /// ตรวจสอบว่าโทเค็นหมดอายุหรือไม่
  static bool isTokenExpired(int statusCode) {
    return statusCode == 401 || statusCode == 403;
  }
}
