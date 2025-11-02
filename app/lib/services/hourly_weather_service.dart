import 'dart:convert';
import 'dart:math' as math;
import 'package:http/http.dart' as http;
import 'package:geolocator/geolocator.dart';
import 'package:flutter/material.dart';
import '../config/api_config.dart';

// Data Models
class HourlyWeatherData {
  final double latitude;
  final double longitude;
  final String timezone;
  final CurrentWeather? currentWeather;
  final HourlyData? hourly;

  HourlyWeatherData({
    required this.latitude,
    required this.longitude,
    required this.timezone,
    this.currentWeather,
    this.hourly,
  });

  factory HourlyWeatherData.fromJson(Map<String, dynamic> json) {
    return HourlyWeatherData(
      latitude: json['latitude']?.toDouble() ?? 0.0,
      longitude: json['longitude']?.toDouble() ?? 0.0,
      timezone: json['timezone'] ?? '',
      currentWeather: json['current_weather'] != null 
          ? CurrentWeather.fromJson(json['current_weather'])
          : null,
      hourly: json['hourly'] != null 
          ? HourlyData.fromJson(json['hourly'])
          : null,
    );
  }
}

class CurrentWeather {
  final String time;
  final double temperature;
  final double windspeed;
  final double winddirection;
  final int isDay;
  final int weathercode;

  CurrentWeather({
    required this.time,
    required this.temperature,
    required this.windspeed,
    required this.winddirection,
    required this.isDay,
    required this.weathercode,
  });

  factory CurrentWeather.fromJson(Map<String, dynamic> json) {
    return CurrentWeather(
      time: json['time'] ?? '',
      temperature: json['temperature']?.toDouble() ?? 0.0,
      windspeed: json['windspeed']?.toDouble() ?? 0.0,
      winddirection: json['winddirection']?.toDouble() ?? 0.0,
      isDay: json['is_day'] ?? 0,
      weathercode: json['weathercode'] ?? 0,
    );
  }
}

class HourlyData {
  final List<String> time;
  final List<double> temperature2m;
  final List<double> precipitation;
  final List<double> windspeed10m;
  final List<int> weathercode;
  final List<double> pressureMsl;
  final List<int> cloudcover;

  HourlyData({
    required this.time,
    required this.temperature2m,
    required this.precipitation,
    required this.windspeed10m,
    required this.weathercode,
    required this.pressureMsl,
    required this.cloudcover,
  });

  factory HourlyData.fromJson(Map<String, dynamic> json) {
    return HourlyData(
      time: List<String>.from(json['time'] ?? []),
      temperature2m: List<double>.from((json['temperature_2m'] ?? []).map((x) => x?.toDouble() ?? 0.0)),
      precipitation: List<double>.from((json['precipitation'] ?? []).map((x) => x?.toDouble() ?? 0.0)),
      windspeed10m: List<double>.from((json['windspeed_10m'] ?? []).map((x) => x?.toDouble() ?? 0.0)),
      weathercode: List<int>.from(json['weathercode'] ?? []),
      pressureMsl: List<double>.from((json['pressure_msl'] ?? []).map((x) => x?.toDouble() ?? 0.0)),
      cloudcover: List<int>.from(json['cloudcover'] ?? []),
    );
  }
}

// Weather Info Model
class WeatherInfo {
  final String desc;
  final IconData iconData;
  final String className;

  WeatherInfo({
    required this.desc,
    required this.iconData,
    required this.className,
  });
}

// Service Class
class HourlyWeatherService {
  static Future<Position?> getCurrentLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          return null;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        return null;
      }

      return await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );
    } catch (e) {
      return null;
    }
  }

  static Future<HourlyWeatherData?> getHourlyWeather(double lat, double lon) async {
    try {
      final url = '${ApiConfig.weatherHourEndpoint}?lat=$lat&lon=$lon';
      final response = await http.get(
        Uri.parse(url),
        headers: ApiConfig.defaultHeaders,
      ).timeout(ApiConfig.timeoutDuration);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return HourlyWeatherData.fromJson(data);
      } else {
        throw Exception('Failed to load hourly weather data: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}

// Helper Class
class HourlyWeatherHelper {
  static WeatherInfo getWeatherInfo(int code) {
    final weatherCodes = {
      0: WeatherInfo(desc: "ท้องฟ้าแจ่มใส", iconData: Icons.wb_sunny, className: "sunny"),
      1: WeatherInfo(desc: "เมฆบางเบา", iconData: Icons.wb_cloudy, className: "partly-cloudy"),
      2: WeatherInfo(desc: "เมฆเป็นส่วนมาก", iconData: Icons.cloud, className: "cloudy"),
      3: WeatherInfo(desc: "เมฆปิดท้องฟ้า", iconData: Icons.cloud, className: "overcast"),
      45: WeatherInfo(desc: "หมอกลง", iconData: Icons.foggy, className: "foggy"),
      48: WeatherInfo(desc: "หมอกน้ำแข็งเกิดขึ้น", iconData: Icons.ac_unit, className: "frost"),
      51: WeatherInfo(desc: "ฝนฟอยเบา", iconData: Icons.grain, className: "drizzle"),
      53: WeatherInfo(desc: "ฝนฟอยปานกลาง", iconData: Icons.grain, className: "light-rain"),
      55: WeatherInfo(desc: "ฝนฟอยหนัก", iconData: Icons.grain, className: "heavy-drizzle"),
      61: WeatherInfo(desc: "ฝนเบา", iconData: Icons.water_drop, className: "light-rain"),
      63: WeatherInfo(desc: "ฝนปานกลาง", iconData: Icons.umbrella, className: "moderate-rain"),
      65: WeatherInfo(desc: "ฝนหนัก", iconData: Icons.thunderstorm, className: "heavy-rain"),
      80: WeatherInfo(desc: "ฝนฟ้าแลบเบา", iconData: Icons.cloud_queue, className: "shower-light"),
      81: WeatherInfo(desc: "ฝนฟ้าแลบปานกลาง", iconData: Icons.cloud_queue, className: "shower-moderate"),
      82: WeatherInfo(desc: "ฝนฟ้าแลบรุนแรง", iconData: Icons.thunderstorm, className: "shower-heavy"),
      95: WeatherInfo(desc: "พายุฝนฟ้าคะนอง", iconData: Icons.flash_on, className: "thunderstorm"),
      96: WeatherInfo(desc: "ฟ้าร้องอาจจะลูกเห็บเบา", iconData: Icons.flash_on, className: "thunder-hail"),
      99: WeatherInfo(desc: "ฟ้าร้องอาจจะลูกเห็บรุนแรง", iconData: Icons.flash_on, className: "severe-storm"),
    };
    
    return weatherCodes[code] ?? WeatherInfo(
      desc: "รหัส $code", 
      iconData: Icons.help_outline, 
      className: "unknown"
    );
  }

  static String getWindDirection(double degrees) {
    if (degrees < 0) return '-';
    
    const directions = [
      'เหนือ', 'เหนือ-ตะวันออก', 'ตะวันออก', 'ตะวันออก-ใต้', 
      'ใต้', 'ใต้-ตะวันตก', 'ตะวันตก', 'ตะวันตก-เหนือ'
    ];
    
    return directions[(degrees / 45).round() % 8];
  }

  static String getProvinceFromCoords(double lat, double lon) {
    // Simplified province detection - same as weather service
    final provinces = [
      {'name': 'กรุงเทพมหานคร', 'lat': 13.7278956, 'lon': 100.5241235},
      {'name': 'เชียงใหม่', 'lat': 18.7877477, 'lon': 98.9931311},
      {'name': 'เชียงราย', 'lat': 19.9071656, 'lon': 99.830955},
      {'name': 'ขอนแก่น', 'lat': 16.4419355, 'lon': 102.8359921},
      {'name': 'นครราชสีมา', 'lat': 14.9798997, 'lon': 102.0977693},
      {'name': 'สงขลา', 'lat': 7.1756004, 'lon': 100.614347},
      {'name': 'ภูเก็ต', 'lat': 7.9810496, 'lon': 98.3638824},
      {'name': 'ชลบุรี', 'lat': 13.3611431, 'lon': 100.9846717},
      // Add more provinces as needed
    ];

    double minDistance = double.infinity;
    String closestProvince = 'กรุงเทพมหานคร';

    for (var province in provinces) {
      double distance = _calculateDistance(
        lat, lon, 
        province['lat'] as double, 
        province['lon'] as double
      );
      if (distance < minDistance) {
        minDistance = distance;
        closestProvince = province['name'] as String;
      }
    }

    // If distance is more than 40km, show "ใกล้จังหวัด..."
    if (minDistance > 40) {
      return 'ใกล้$closestProvince';
    }
    
    return closestProvince;
  }

  static double _calculateDistance(double lat1, double lon1, double lat2, double lon2) {
    const double R = 6371; // Earth's radius in kilometers
    double dLat = _toRadians(lat2 - lat1);
    double dLon = _toRadians(lon2 - lon1);
    double a = (dLat / 2).sin() * (dLat / 2).sin() +
        lat1.cos() * lat2.cos() * (dLon / 2).sin() * (dLon / 2).sin();
    double c = 2 * (a.sqrt().atan2((1 - a).sqrt()));
    return R * c;
  }

  static double _toRadians(double degrees) {
    return degrees * (3.14159265359 / 180);
  }
}

// Extensions for math functions
extension on double {
  double sin() => math.sin(this);
  double cos() => math.cos(this);
  double sqrt() => math.sqrt(this);
  double atan2(double other) => math.atan2(this, other);
}
