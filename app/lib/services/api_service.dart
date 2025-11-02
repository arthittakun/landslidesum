import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/api_response.dart';
import '../config/api_config.dart';

class ApiService {
  // Login API
  static Future<ApiResponse<LoginData>> login({
    required String identifier,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.loginEndpoint),
        headers: ApiConfig.defaultHeaders,
        body: jsonEncode({
          'identifier': identifier,
          'password': password,
        }),
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final userData = data['data'];
        final loginData = LoginData(
          user: UserInfo(
            username: userData['user']['username'],
            email: userData['user']['email'],
          ),
          accessToken: userData['access_token'],
          refreshToken: userData['refresh_token'],
          expiresIn: userData['expires_in'],
        );

        return ApiResponse<LoginData>(
          success: true,
          message: data['message'] ?? 'เข้าสู่ระบบสำเร็จ',
          data: loginData,
          code: response.statusCode,
        );
      } else {
        return ApiResponse<LoginData>(
          success: false,
          message: data['message'] ?? 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
          code: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<LoginData>(
        success: false,
        message: 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ: ${e.toString()}',
        code: 500,
      );
    }
  }

  // Register API
  static Future<ApiResponse<RegisterData>> register({
    required String username,
    required String email,
    required String password,
    required String confirmPassword,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.registerEndpoint),
        headers: ApiConfig.defaultHeaders,
        body: jsonEncode({
          'username': username,
          'email': email,
          'password': password,
          'confirm_password': confirmPassword,
        }),
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 201 && data['success'] == true) {
        final userData = data['data'];
        final registerData = RegisterData(
          username: userData['username'],
          email: userData['email'],
        );

        return ApiResponse<RegisterData>(
          success: true,
          message: data['message'] ?? 'สมัครสมาชิกสำเร็จ',
          data: registerData,
          code: response.statusCode,
        );
      } else {
        return ApiResponse<RegisterData>(
          success: false,
          message: data['message'] ?? 'เกิดข้อผิดพลาดในการสมัครสมาชิก',
          code: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<RegisterData>(
        success: false,
        message: 'เกิดข้อผิดพลาดในการสมัครสมาชิก: ${e.toString()}',
        code: 500,
      );
    }
  }

  // Verify Token API
  static Future<ApiResponse<TokenVerifyData>> verifyToken(String token) async {
    try {
      final response = await http.get(
        Uri.parse(ApiConfig.verifyEndpoint),
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final userData = data['data'];
        final verifyData = TokenVerifyData(
          username: userData['username'] ?? '',
          email: userData['email'] ?? '',
          iat: userData['iat'] ?? 0,
          exp: userData['exp'] ?? 0,
          iss: userData['iss'] ?? '',
        );

        return ApiResponse<TokenVerifyData>(
          success: true,
          message: data['message'] ?? 'Token ถูกต้อง',
          data: verifyData,
          code: response.statusCode,
        );
      } else {
        return ApiResponse<TokenVerifyData>(
          success: false,
          message: data['message'] ?? 'Token ไม่ถูกต้องหรือหมดอายุ',
          code: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<TokenVerifyData>(
        success: false,
        message: 'เกิดข้อผิดพลาดในการตรวจสอบ Token: ${e.toString()}',
        code: 500,
      );
    }
  }

  // Refresh Token API
  static Future<ApiResponse<RefreshTokenData>> refreshToken(
    String refreshToken,
  ) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.refreshEndpoint),
        headers: ApiConfig.defaultHeaders,
        body: jsonEncode({
          'refresh_token': refreshToken,
        }),
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final tokenData = data['data'];
        final refreshData = RefreshTokenData(
          accessToken: tokenData['access_token'],
          expiresIn: tokenData['expires_in'],
        );

        return ApiResponse<RefreshTokenData>(
          success: true,
          message: data['message'] ?? 'รีเฟรช Token สำเร็จ',
          data: refreshData,
          code: response.statusCode,
        );
      } else {
        return ApiResponse<RefreshTokenData>(
          success: false,
          message: data['message'] ?? 'ไม่สามารถรีเฟรช Token ได้',
          code: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<RefreshTokenData>(
        success: false,
        message: 'เกิดข้อผิดพลาดในการรีเฟรช Token: ${e.toString()}',
        code: 500,
      );
    }
  }

  // Logout API
  static Future<ApiResponse<Map<String, dynamic>>> logout(String token) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.logoutEndpoint),
        headers: ApiConfig.getAuthHeaders(token),
      ).timeout(ApiConfig.timeoutDuration);

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return ApiResponse<Map<String, dynamic>>(
          success: true,
          message: data['message'] ?? 'ออกจากระบบสำเร็จ',
          data: data['data'] ?? {},
          code: response.statusCode,
        );
      } else {
        return ApiResponse<Map<String, dynamic>>(
          success: false,
          message: data['message'] ?? 'เกิดข้อผิดพลาดในการออกจากระบบ',
          code: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'เกิดข้อผิดพลาดในการออกจากระบบ: ${e.toString()}',
        code: 500,
      );
    }
  }
}
