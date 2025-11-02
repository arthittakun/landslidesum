import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/about_response.dart';
import 'secure_storage_service.dart';

class AboutService {
  static const String endpoint = ApiConfig.aboutEndpoint;

  static Future<AboutResponse?> getAboutInfo() async {
    try {
      final accessToken = await SecureStorageService.getAccessToken();

      if (accessToken == null) {
        return null;
      }

      final response = await http.get(
        Uri.parse(endpoint),
        headers: {
          'Authorization': 'Bearer $accessToken',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);
        return AboutResponse.fromJson(jsonData);
      } else if (response.statusCode == 401) {
        // Token expired, try to refresh
        final newToken = await _refreshToken();
        if (newToken != null) {
          // Retry with new token
          return await getAboutInfo();
        }
      }

      return null;
    } catch (e) {
      return null;
    }
  }

  static Future<String?> _refreshToken() async {
    try {
      final refreshToken = await SecureStorageService.getRefreshToken();
      if (refreshToken == null) return null;

      final response = await http.post(
        Uri.parse(ApiConfig.refreshEndpoint),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'refresh_token': refreshToken}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final newAccessToken = data['access_token'];
        final newRefreshToken = data['refresh_token'];

        if (newAccessToken != null) {
          await SecureStorageService.saveAccessToken(newAccessToken);
          if (newRefreshToken != null) {
            await SecureStorageService.saveRefreshToken(newRefreshToken);
          }
          return newAccessToken;
        }
      }
      return null;
    } catch (e) {
      return null;
    }
  }
}
