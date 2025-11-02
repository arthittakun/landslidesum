import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorageService {
  static const FlutterSecureStorage _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
    ),
    iOptions: IOSOptions(
      accessibility: KeychainAccessibility.first_unlock_this_device,
    ),
  );

  // Keys สำหรับเก็บข้อมูล
  static const String _accessTokenKey = 'access_token';
  static const String _refreshTokenKey = 'refresh_token';
  static const String _userInfoKey = 'user_info';
  static const String _tokenExpiryKey = 'token_expiry';

  // เก็บ Access Token
  static Future<void> saveAccessToken(String token) async {
    await _storage.write(key: _accessTokenKey, value: token);
  }

  // ดึง Access Token
  static Future<String?> getAccessToken() async {
    return await _storage.read(key: _accessTokenKey);
  }

  // เก็บ Refresh Token
  static Future<void> saveRefreshToken(String token) async {
    await _storage.write(key: _refreshTokenKey, value: token);
  }

  // ดึง Refresh Token
  static Future<String?> getRefreshToken() async {
    return await _storage.read(key: _refreshTokenKey);
  }

  // เก็บข้อมูล User
  static Future<void> saveUserInfo(String username, String email) async {
    await _storage.write(key: _userInfoKey, value: '$username|$email');
  }

  // ดึงข้อมูล User
  static Future<Map<String, String>?> getUserInfo() async {
    final userInfo = await _storage.read(key: _userInfoKey);
    if (userInfo != null) {
      final parts = userInfo.split('|');
      if (parts.length == 2) {
        return {
          'username': parts[0],
          'email': parts[1],
        };
      }
    }
    return null;
  }

  // เก็บเวลาหมดอายุ Token
  static Future<void> saveTokenExpiry(DateTime expiry) async {
    await _storage.write(
      key: _tokenExpiryKey,
      value: expiry.millisecondsSinceEpoch.toString(),
    );
  }

  // ดึงเวลาหมดอายุ Token
  static Future<DateTime?> getTokenExpiry() async {
    final expiryString = await _storage.read(key: _tokenExpiryKey);
    if (expiryString != null) {
      final timestamp = int.tryParse(expiryString);
      if (timestamp != null) {
        return DateTime.fromMillisecondsSinceEpoch(timestamp);
      }
    }
    return null;
  }

  // ตรวจสอบว่า Token หมดอายุหรือยัง
  static Future<bool> isTokenExpired() async {
    final expiry = await getTokenExpiry();
    if (expiry == null) return true;
    return DateTime.now().isAfter(expiry);
  }

  // ลบข้อมูลทั้งหมด (สำหรับ Logout)
  static Future<void> clearAll() async {
    await _storage.delete(key: _accessTokenKey);
    await _storage.delete(key: _refreshTokenKey);
    await _storage.delete(key: _userInfoKey);
    await _storage.delete(key: _tokenExpiryKey);
  }

  // ตรวจสอบว่ามี Token ที่ใช้งานได้หรือไม่
  static Future<bool> hasValidToken() async {
    try {
      final accessToken = await getAccessToken();
      if (accessToken == null || accessToken.isEmpty) {
        return false;
      }
      
      final isExpired = await isTokenExpired();
      return !isExpired;
    } catch (e) {
      return false;
    }
  }

  // ตรวจสอบข้อมูลทั้งหมดที่เก็บไว้ (สำหรับ debug)
  static Future<Map<String, dynamic>> getAllStoredData() async {
    return {
      'access_token': await getAccessToken(),
      'refresh_token': await getRefreshToken(),
      'user_info': await getUserInfo(),
      'token_expiry': await getTokenExpiry(),
      'is_expired': await isTokenExpired(),
      'has_valid_token': await hasValidToken(),
    };
  }
}
