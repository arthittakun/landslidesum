import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import 'secure_storage_service.dart';

class RadarFrame {
  final int time;
  final String path;

  RadarFrame({
    required this.time,
    required this.path,
  });

  factory RadarFrame.fromJson(Map<String, dynamic> json) {
    return RadarFrame(
      time: json['time'] as int,
      path: json['path'] as String,
    );
  }

  DateTime get dateTime => DateTime.fromMillisecondsSinceEpoch(time * 1000);
}

class RadarData {
  final String host;
  final List<RadarFrame> frames;

  RadarData({
    required this.host,
    required this.frames,
  });

  factory RadarData.fromJson(Map<String, dynamic> json) {
    return RadarData(
      host: json['host'] as String,
      frames: (json['frames'] as List)
          .map((frame) => RadarFrame.fromJson(frame))
          .toList(),
    );
  }
}

class RadarService {
  static Future<RadarData?> getRadarData() async {
    try {
      final token = await SecureStorageService.getAccessToken();
      if (token == null) {
        throw Exception('No authentication token found');
      }

      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/api/weather/rainviewer-radar'),
        headers: ApiConfig.getAuthHeaders(token),
      );

      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);
        return RadarData.fromJson(jsonData);
      } else {
        throw Exception('Failed to load radar data: ${response.statusCode}');
      }
    } catch (e) {
      return null;
    }
  }
}
