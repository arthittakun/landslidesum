import '../models/api_response.dart';
import '../services/api_service.dart';
import '../services/secure_storage_service.dart';

class AuthController {
  static bool _isLoggedIn = false;
  static UserInfo? _currentUser;

  // ตรวจสอบสถานะการ Login
  static bool get isLoggedIn => _isLoggedIn;
  static UserInfo? get currentUser => _currentUser;

  // เริ่มต้น - ตรวจสอบ Token ที่เก็บไว้
  static Future<void> initialize() async {
    try {
      // ตรวจสอบว่ามี access token หรือไม่
      final accessToken = await SecureStorageService.getAccessToken();
      if (accessToken == null) {
        _isLoggedIn = false;
        _currentUser = null;
        return;
      }

      // ตรวจสอบว่า token หมดอายุหรือยัง
      final isExpired = await SecureStorageService.isTokenExpired();
      if (!isExpired) {
        // Token ยังใช้งานได้ - โหลดข้อมูล user
        final userInfo = await SecureStorageService.getUserInfo();
        if (userInfo != null) {
          _isLoggedIn = true;
          _currentUser = UserInfo(
            username: userInfo['username']!,
            email: userInfo['email']!,
          );
          return;
        }
      }

      // Token หมดอายุ - ลอง refresh
      final refreshSuccess = await refreshToken();
      if (refreshSuccess) {
        // Refresh สำเร็จ - โหลดข้อมูล user
        final userInfo = await SecureStorageService.getUserInfo();
        if (userInfo != null) {
          _isLoggedIn = true;
          _currentUser = UserInfo(
            username: userInfo['username']!,
            email: userInfo['email']!,
          );
          return;
        }
      }

      // หากทุกอย่างล้มเหลว - logout
      _isLoggedIn = false;
      _currentUser = null;
      await SecureStorageService.clearAll();
    } catch (e) {
      // หากเกิดข้อผิดพลาด - logout
      _isLoggedIn = false;
      _currentUser = null;
      await SecureStorageService.clearAll();
    }
  }

  // Login
  static Future<LoginResult> login(String identifier, String password) async {
    try {
      final response = await ApiService.login(
        identifier: identifier,
        password: password,
      );

      if (response.success && response.data != null) {
        final loginData = response.data!;
        
        // เก็บ Token ใน Secure Storage
        await SecureStorageService.saveAccessToken(loginData.accessToken);
        await SecureStorageService.saveRefreshToken(loginData.refreshToken);
        
        // คำนวณเวลาหมดอายุ (ลบ 5 นาทีเพื่อความปลอดภัย)
        final expiry = DateTime.now().add(Duration(seconds: loginData.expiresIn - 300));
        await SecureStorageService.saveTokenExpiry(expiry);
        
        // เก็บข้อมูล User
        await SecureStorageService.saveUserInfo(
          loginData.user.username,
          loginData.user.email,
        );

        // อัพเดทสถานะ
        _isLoggedIn = true;
        _currentUser = loginData.user;

        return LoginResult(success: true, message: response.message);
      } else {
        return LoginResult(success: false, message: response.message);
      }
    } catch (e) {
      return LoginResult(
        success: false,
        message: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
      );
    }
  }

  // Register
  static Future<RegisterResult> register({
    required String username,
    required String email,
    required String password,
    required String confirmPassword,
  }) async {
    try {
      final response = await ApiService.register(
        username: username,
        email: email,
        password: password,
        confirmPassword: confirmPassword,
      );

      if (response.success) {
        return RegisterResult(success: true, message: response.message);
      } else {
        return RegisterResult(success: false, message: response.message);
      }
    } catch (e) {
      return RegisterResult(
        success: false,
        message: 'เกิดข้อผิดพลาด: ${e.toString()}',
      );
    }
  }

  // Refresh Token
  static Future<bool> refreshToken() async {
    try {
      final refreshTokenValue = await SecureStorageService.getRefreshToken();
      if (refreshTokenValue == null || refreshTokenValue.isEmpty) {
        return false;
      }

      final response = await ApiService.refreshToken(refreshTokenValue);
      
      if (response.success && response.data != null) {
        final refreshData = response.data!;
        
        // อัพเดท Access Token
        await SecureStorageService.saveAccessToken(refreshData.accessToken);
        
        // คำนวณเวลาหมดอายุใหม่ (ลบ 5 นาทีเพื่อความปลอดภัย)
        final expiry = DateTime.now().add(Duration(seconds: refreshData.expiresIn - 300));
        await SecureStorageService.saveTokenExpiry(expiry);
        
        return true;
      }
      
      return false;
    } catch (e) {
      return false;
    }
  }

  // Verify Token
  static Future<bool> verifyToken() async {
    try {
      final accessToken = await SecureStorageService.getAccessToken();
      if (accessToken == null) return false;

      final response = await ApiService.verifyToken(accessToken);
      return response.success;
    } catch (e) {
      return false;
    }
  }

  // Logout
  static Future<void> logout() async {
    try {
      final accessToken = await SecureStorageService.getAccessToken();
      if (accessToken != null) {
        await ApiService.logout(accessToken);
      }
    } catch (e) {
      // ถึงแม้ API จะ error ก็ยัง logout ได้
    } finally {
      // ลบข้อมูลทั้งหมดจาก Storage
      await SecureStorageService.clearAll();
      
      // รีเซ็ตสถานะ
      _isLoggedIn = false;
      _currentUser = null;
    }
  }

  // ตรวจสอบและรีเฟรช Token อัตโนมัติ
  static Future<bool> ensureTokenValid() async {
    final isExpired = await SecureStorageService.isTokenExpired();
    if (isExpired) {
      return await refreshToken();
    }
    return true;
  }

  // ดึง Access Token สำหรับเรียก API อื่นๆ
  static Future<String?> getAccessToken() async {
    await ensureTokenValid();
    return await SecureStorageService.getAccessToken();
  }
}

// Classes สำหรับ Result
class LoginResult {
  final bool success;
  final String message;

  LoginResult({required this.success, required this.message});
}

class RegisterResult {
  final bool success;
  final String message;

  RegisterResult({required this.success, required this.message});
}
