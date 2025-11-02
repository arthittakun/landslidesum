import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/forgot_password_response.dart';

class ForgotPasswordService {
  static const String endpoint = ApiConfig.forgotPasswordEndpoint;

  static Future<ForgotPasswordResponse> requestPasswordReset(String email) async {
    try {
      // สร้าง FormData
      final request = http.MultipartRequest('POST', Uri.parse(endpoint));
      
      // เพิ่ม email field
      request.fields['email'] = email;
      
      // เพิ่ม headers
      request.headers['Accept'] = 'application/json';

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        try {
          final jsonData = json.decode(response.body);
          final forgotPasswordResponse = ForgotPasswordResponse.fromJson(jsonData);
          
          if (forgotPasswordResponse.success) {
            return forgotPasswordResponse;
          } else {
            return forgotPasswordResponse;
          }
        } catch (jsonError) {
          return ForgotPasswordResponse(
            success: false,
            message: 'เกิดข้อผิดพลาดในการประมวลผลข้อมูลจากเซิร์ฟเวอร์',
            code: 500,
          );
        }
      } else {
        // พยายาม parse error response
        try {
          final jsonData = json.decode(response.body);
          return ForgotPasswordResponse.fromJson(jsonData);
        } catch (e) {
          return ForgotPasswordResponse(
            success: false,
            message: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์ (Status: ${response.statusCode})',
            code: response.statusCode,
          );
        }
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
      
      return ForgotPasswordResponse(
        success: false,
        message: errorMessage,
        code: 500,
      );
    }
  }

  // Verify Reset Token
  static Future<VerifyTokenResult> verifyResetToken(String token) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.verifyResetTokenEndpoint}?token=$token'),
        headers: ApiConfig.defaultHeaders,
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return VerifyTokenResult(
          success: true,
          message: data['message'] ?? 'Token ถูกต้อง',
          email: data['data']?['email'],
          expiresAt: data['data']?['expires_at'],
          timeRemaining: data['data']?['time_remaining'],
        );
      } else {
        return VerifyTokenResult(
          success: false,
          message: data['message'] ?? 'ลิงก์ไม่ถูกต้องหรือหมดอายุแล้ว',
        );
      }
    } catch (e) {
      return VerifyTokenResult(
        success: false,
        message: 'เกิดข้อผิดพลาดในการตรวจสอบ Token: ${e.toString()}',
      );
    }
  }

  // Reset Password
  static Future<ResetPasswordResult> resetPassword({
    required String token,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.resetPasswordEndpoint),
        headers: ApiConfig.defaultHeaders,
        body: jsonEncode({
          'token': token,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return ResetPasswordResult(
          success: true,
          message: data['message'] ?? 'รหัสผ่านได้ถูกเปลี่ยนแปลงเรียบร้อยแล้ว',
        );
      } else {
        String message = data['message'] ?? 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน';
        
        // แปลข้อความ error เป็นภาษาไทย
        if (message.contains('Passwords do not match')) {
          message = 'รหัสผ่านไม่ตรงกัน';
        } else if (message.contains('at least 8 characters')) {
          message = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
        } else if (message.contains('Invalid or expired token')) {
          message = 'ลิงก์ไม่ถูกต้องหรือหมดอายุแล้ว';
        }
        
        return ResetPasswordResult(
          success: false,
          message: message,
        );
      }
    } catch (e) {
      return ResetPasswordResult(
        success: false,
        message: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ${e.toString()}',
      );
    }
  }
}

// Result Classes
class ForgotPasswordResult {
  final bool success;
  final String message;
  final String? email;
  final String? expiresIn;
  final int? remainingAttempts;
  final String? resetAfter;

  ForgotPasswordResult({
    required this.success,
    required this.message,
    this.email,
    this.expiresIn,
    this.remainingAttempts,
    this.resetAfter,
  });
}

class VerifyTokenResult {
  final bool success;
  final String message;
  final String? email;
  final String? expiresAt;
  final String? timeRemaining;

  VerifyTokenResult({
    required this.success,
    required this.message,
    this.email,
    this.expiresAt,
    this.timeRemaining,
  });
}

class ResetPasswordResult {
  final bool success;
  final String message;

  ResetPasswordResult({
    required this.success,
    required this.message,
  });
}
