import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:geolocator/geolocator.dart';
import 'package:flutter/material.dart';
import 'dart:math' as math;
import '../config/api_config.dart';

class WeatherService {
  static Future<WeatherData?> getDailyWeather(double lat, double lon) async {
    try {
      final url = '${ApiConfig.weatherDayEndpoint}?lat=$lat&lon=$lon';
      final response = await http.get(
        Uri.parse(url),
        headers: ApiConfig.defaultHeaders,
      ).timeout(ApiConfig.timeoutDuration);
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return WeatherData.fromJson(data);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  static Future<Position?> getCurrentLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.whileInUse ||
          permission == LocationPermission.always) {
        return await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high
        );
      }
      return null;
    } catch (e) {
      return null;
    }
  }
}

class WeatherData {
  final double latitude;
  final double longitude;
  final String timezone;
  final Daily daily;
  
  WeatherData({
    required this.latitude,
    required this.longitude,
    required this.timezone,
    required this.daily,
  });
  
  factory WeatherData.fromJson(Map<String, dynamic> json) {
    return WeatherData(
      latitude: json['latitude']?.toDouble() ?? 0.0,
      longitude: json['longitude']?.toDouble() ?? 0.0,
      timezone: json['timezone'] ?? '',
      daily: Daily.fromJson(json['daily'] ?? {}),
    );
  }
}

class Daily {
  final List<String> time;
  final List<double> temperatureMin;
  final List<double> temperatureMax;
  final List<double> precipitationSum;
  final List<double> uvIndexMax;
  final List<double> windspeedMax;
  final List<double> windgustsMax;
  final List<String> sunrise;
  final List<String> sunset;
  final List<int> weathercode;
  
  Daily({
    required this.time,
    required this.temperatureMin,
    required this.temperatureMax,
    required this.precipitationSum,
    required this.uvIndexMax,
    required this.windspeedMax,
    required this.windgustsMax,
    required this.sunrise,
    required this.sunset,
    required this.weathercode,
  });
  
  factory Daily.fromJson(Map<String, dynamic> json) {
    return Daily(
      time: List<String>.from(json['time'] ?? []),
      temperatureMin: List<double>.from((json['temperature_2m_min'] ?? []).map((x) => x.toDouble())),
      temperatureMax: List<double>.from((json['temperature_2m_max'] ?? []).map((x) => x.toDouble())),
      precipitationSum: List<double>.from((json['precipitation_sum'] ?? []).map((x) => x.toDouble())),
      uvIndexMax: List<double>.from((json['uv_index_max'] ?? []).map((x) => x.toDouble())),
      windspeedMax: List<double>.from((json['windspeed_10m_max'] ?? []).map((x) => x.toDouble())),
      windgustsMax: List<double>.from((json['windgusts_10m_max'] ?? []).map((x) => x.toDouble())),
      sunrise: List<String>.from(json['sunrise'] ?? []),
      sunset: List<String>.from(json['sunset'] ?? []),
      weathercode: List<int>.from(json['weathercode'] ?? []),
    );
  }
}

class WeatherHelper {
  static String getProvinceFromCoords(double lat, double lon) {
    final provinces = [
      {'name': 'กรุงเทพมหานคร', 'lat': 13.7278956, 'lon': 100.5241235},
      {'name': 'กระบี่', 'lat': 8.0862997, 'lon': 98.9062835},
      {'name': 'กาญจนบุรี', 'lat': 14.0227797, 'lon': 99.5328115},
      {'name': 'กาฬสินธุ์', 'lat': 16.4314078, 'lon': 103.5058755},
      {'name': 'กำแพงเพชร', 'lat': 16.4827798, 'lon': 99.5226618},
      {'name': 'ขอนแก่น', 'lat': 16.4419355, 'lon': 102.8359921},
      {'name': 'จันทบุรี', 'lat': 12.61134, 'lon': 102.1038546},
      {'name': 'ฉะเชิงเทรา', 'lat': 13.6904194, 'lon': 101.0779596},
      {'name': 'ชลบุรี', 'lat': 13.3611431, 'lon': 100.9846717},
      {'name': 'ชัยนาท', 'lat': 15.1851971, 'lon': 100.125125},
      {'name': 'ชัยภูมิ', 'lat': 15.8068173, 'lon': 102.0315027},
      {'name': 'ชุมพร', 'lat': 10.4930496, 'lon': 99.1800199},
      {'name': 'เชียงราย', 'lat': 19.9071656, 'lon': 99.830955},
      {'name': 'เชียงใหม่', 'lat': 18.7877477, 'lon': 98.9931311},
      {'name': 'ตรัง', 'lat': 7.5593851, 'lon': 99.6110065},
      {'name': 'ตราด', 'lat': 12.2427563, 'lon': 102.5174734},
      {'name': 'ตาก', 'lat': 16.8839901, 'lon': 99.1258498},
      {'name': 'นครนายก', 'lat': 14.2069466, 'lon': 101.2130511},
      {'name': 'นครปฐม', 'lat': 13.8199206, 'lon': 100.0621676},
      {'name': 'นครพนม', 'lat': 17.392039, 'lon': 104.7695508},
      {'name': 'นครราชสีมา', 'lat': 14.9798997, 'lon': 102.0977693},
      {'name': 'นครศรีธรรมราช', 'lat': 8.4303975, 'lon': 99.9631219},
      {'name': 'นครสวรรค์', 'lat': 15.6930072, 'lon': 100.1225595},
      {'name': 'นนทบุรี', 'lat': 13.8621125, 'lon': 100.5143528},
      {'name': 'นราธิวาส', 'lat': 6.4254607, 'lon': 101.8253143},
      {'name': 'น่าน', 'lat': 18.7756318, 'lon': 100.7730417},
      {'name': 'บุรีรัมย์', 'lat': 14.9930017, 'lon': 103.1029191},
      {'name': 'ปทุมธานี', 'lat': 14.0208391, 'lon': 100.5250276},
      {'name': 'ประจวบคีรีขันธ์', 'lat': 11.812367, 'lon': 99.7973271},
      {'name': 'ปราจีนบุรี', 'lat': 14.0509704, 'lon': 101.3727439},
      {'name': 'ปัตตานี', 'lat': 6.8694844, 'lon': 101.2504826},
      {'name': 'พระนครศรีอยุธยา', 'lat': 14.3532128, 'lon': 100.5689599},
      {'name': 'พะเยา', 'lat': 19.1664789, 'lon': 99.9019419},
      {'name': 'พังงา', 'lat': 8.4407456, 'lon': 98.5193032},
      {'name': 'พัทลุง', 'lat': 7.6166823, 'lon': 100.0740231},
      {'name': 'พิจิตร', 'lat': 16.4429516, 'lon': 100.3482329},
      {'name': 'พิษณุโลก', 'lat': 16.8298048, 'lon': 100.2614915},
      {'name': 'เพชรบุรี', 'lat': 13.1111601, 'lon': 99.9391307},
      {'name': 'เพชรบูรณ์', 'lat': 16.4189807, 'lon': 101.1550926},
      {'name': 'แพร่', 'lat': 18.1445774, 'lon': 100.1402831},
      {'name': 'ภูเก็ต', 'lat': 7.9810496, 'lon': 98.3638824},
      {'name': 'มหาสารคาม', 'lat': 16.1850896, 'lon': 103.3026461},
      {'name': 'มุกดาหาร', 'lat': 16.542443, 'lon': 104.7209151},
      {'name': 'แม่ฮ่องสอน', 'lat': 19.2990643, 'lon': 97.9656226},
      {'name': 'ยโสธร', 'lat': 15.792641, 'lon': 104.1452827},
      {'name': 'ยะลา', 'lat': 6.541147, 'lon': 101.2803947},
      {'name': 'ร้อยเอ็ด', 'lat': 16.0538196, 'lon': 103.6520036},
      {'name': 'ระนอง', 'lat': 9.9528702, 'lon': 98.6084641},
      {'name': 'ระยอง', 'lat': 12.6833115, 'lon': 101.2374295},
      {'name': 'ราชบุรี', 'lat': 13.5282893, 'lon': 99.8134211},
      {'name': 'ลพบุรี', 'lat': 14.7995081, 'lon': 100.6533706},
      {'name': 'ลำปาง', 'lat': 18.2888404, 'lon': 99.490874},
      {'name': 'ลำพูน', 'lat': 18.5744606, 'lon': 99.0087221},
      {'name': 'เลย', 'lat': 17.4860232, 'lon': 101.7223002},
      {'name': 'ศรีสะเกษ', 'lat': 15.1186009, 'lon': 104.3220095},
      {'name': 'สกลนคร', 'lat': 17.1545995, 'lon': 104.1348365},
      {'name': 'สงขลา', 'lat': 7.1756004, 'lon': 100.614347},
      {'name': 'สตูล', 'lat': 6.6238158, 'lon': 100.0673744},
      {'name': 'สมุทรปราการ', 'lat': 13.5990961, 'lon': 100.5998319},
      {'name': 'สมุทรสงคราม', 'lat': 13.4098217, 'lon': 100.0022645},
      {'name': 'สมุทรสาคร', 'lat': 13.5475216, 'lon': 100.2743956},
      {'name': 'สระแก้ว', 'lat': 13.824038, 'lon': 102.0645839},
      {'name': 'สระบุรี', 'lat': 14.5289154, 'lon': 100.9101421},
      {'name': 'สิงห์บุรี', 'lat': 14.8936253, 'lon': 100.3967314},
      {'name': 'สุโขทัย', 'lat': 17.0055573, 'lon': 99.8263712},
      {'name': 'สุพรรณบุรี', 'lat': 14.4744892, 'lon': 100.1177128},
      {'name': 'สุราษฎร์ธานี', 'lat': 9.1382389, 'lon': 99.3217483},
      {'name': 'สุรินทร์', 'lat': 14.882905, 'lon': 103.4937107},
      {'name': 'หนองคาย', 'lat': 17.8782803, 'lon': 102.7412638},
      {'name': 'หนองบัวลำภู', 'lat': 17.2218247, 'lon': 102.4260368},
      {'name': 'อ่างทอง', 'lat': 14.5896054, 'lon': 100.455052},
      {'name': 'อำนาจเจริญ', 'lat': 15.8656783, 'lon': 104.6257774},
      {'name': 'อุดรธานี', 'lat': 17.4138413, 'lon': 102.7872325},
      {'name': 'อุตรดิตถ์', 'lat': 17.6200886, 'lon': 100.0992942},
      {'name': 'อุทัยธานี', 'lat': 15.3835001, 'lon': 100.0245527},
      {'name': 'อุบลราชธานี', 'lat': 15.2286861, 'lon': 104.8564217},
      {'name': 'บึงกาฬ', 'lat': 18.3609104, 'lon': 103.6464463},
    ];

    Map<String, dynamic> closestProvince = provinces[0];
    double minDistance = _getDistance(lat, lon, closestProvince['lat'], closestProvince['lon']);

    for (var province in provinces) {
      final distance = _getDistance(lat, lon, (province['lat'] as num).toDouble(), (province['lon'] as num).toDouble());
      if (distance < minDistance) {
        minDistance = distance;
        closestProvince = province;
      }
    }

    return closestProvince['name'];
  }

  static double _getDistance(double lat1, double lon1, double lat2, double lon2) {
    const R = 6371;
    final dLat = (lat2 - lat1) * math.pi / 180;
    final dLon = (lon2 - lon1) * math.pi / 180;
    final a = math.sin(dLat / 2) * math.sin(dLat / 2) +
        math.cos(lat1 * math.pi / 180) * math.cos(lat2 * math.pi / 180) *
        math.sin(dLon / 2) * math.sin(dLon / 2);
    final c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a));
    return R * c;
  }

  static String formatDate(String dateStr) {
    final date = DateTime.parse(dateStr);
    final thaiMonths = [
      'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
      'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    
    final thaiDays = [
      'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'
    ];
    
    final today = DateTime.now();
    final tomorrow = today.add(const Duration(days: 1));
    
    if (date.year == today.year && date.month == today.month && date.day == today.day) {
      return 'วันนี้';
    } else if (date.year == tomorrow.year && date.month == tomorrow.month && date.day == tomorrow.day) {
      return 'พรุ่งนี้';
    } else {
      final dayName = thaiDays[date.weekday - 1];
      return '$dayName ${date.day} ${thaiMonths[date.month - 1]}';
    }
  }
  
  static String formatTime(String timeStr) {
    final time = DateTime.parse(timeStr);
    return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
  }
  
  static String getWeatherIcon(int weatherCode) {
    switch (weatherCode) {
      case 0: return '☀️'; // Clear sky
      case 1: case 2: case 3: return '⛅'; // Mainly clear, partly cloudy, overcast
      case 45: case 48: return '🌫️'; // Fog
      case 51: case 53: case 55: return '🌦️'; // Drizzle
      case 61: case 63: case 65: return '🌧️'; // Rain
      case 71: case 73: case 75: return '🌨️'; // Snow
      case 80: case 81: case 82: return '🌦️'; // Rain showers
      case 95: case 96: case 99: return '⛈️'; // Thunderstorm
      default: return '☁️'; // Default cloudy
    }
  }
  
  static String getWeatherDescription(int weatherCode) {
    switch (weatherCode) {
      case 0: return 'ท้องฟ้าแจ่มใส';
      case 1: return 'ท้องฟ้าเเจ่มใสเป็นส่วนใหญ่';
      case 2: return 'มีเมฆบางส่วน';
      case 3: return 'มีเมฆมาก';
      case 45: return 'มีหมอก';
      case 48: return 'หมอกจัด';
      case 51: return 'ฝนปรอยเบา';
      case 53: return 'ฝนปรอยปานกลาง';
      case 55: return 'ฝนปรอยหนัก';
      case 61: return 'ฝนเบา';
      case 63: return 'ฝนปานกลาง';
      case 65: return 'ฝนหนัก';
      case 71: return 'หิมะเบา';
      case 73: return 'หิมะปานกลาง';
      case 75: return 'หิมะหนัก';
      case 80: return 'ฝนฟ้าคะนองเบา';
      case 81: return 'ฝนฟ้าคะนองปานกลาง';
      case 82: return 'ฝนฟ้าคะนองหนัก';
      case 95: return 'พายุฟ้าคะนอง';
      case 96: return 'พายุฟ้าคะนองมีลูกเห็บเบา';
      case 99: return 'พายุฟ้าคะนองมีลูกเห็บหนัก';
      default: return 'สภาพอากาศไม่แน่นอน';
    }
  }

  static Map<String, dynamic> getWeatherInfo(int weatherCode) {
    switch (weatherCode) {
      case 0:
        return {
          'desc': 'ท้องฟ้าแจ่มใส',
          'icon': '☀️',
          'gradient': [Colors.orange.withOpacity(0.1), Colors.yellow.withOpacity(0.05)]
        };
      case 1:
      case 2:
      case 3:
        return {
          'desc': 'มีเมฆบางส่วน',
          'icon': '⛅',
          'gradient': [Colors.blue.withOpacity(0.1), Colors.grey.withOpacity(0.05)]
        };
      case 45:
      case 48:
        return {
          'desc': 'มีหมอก',
          'icon': '🌫️',
          'gradient': [Colors.grey.withOpacity(0.1), Colors.blueGrey.withOpacity(0.05)]
        };
      case 51:
      case 53:
      case 55:
      case 61:
      case 63:
      case 65:
        return {
          'desc': 'ฝนตก',
          'icon': '🌧️',
          'gradient': [Colors.blue.withOpacity(0.1), Colors.indigo.withOpacity(0.05)]
        };
      case 71:
      case 73:
      case 75:
        return {
          'desc': 'หิมะตก',
          'icon': '🌨️',
          'gradient': [Colors.lightBlue.withOpacity(0.1), Colors.cyan.withOpacity(0.05)]
        };
      case 80:
      case 81:
      case 82:
        return {
          'desc': 'ฝนฟ้าคะนอง',
          'icon': '🌦️',
          'gradient': [Colors.purple.withOpacity(0.1), Colors.blue.withOpacity(0.05)]
        };
      case 95:
      case 96:
      case 99:
        return {
          'desc': 'พายุฟ้าคะนอง',
          'icon': '⛈️',
          'gradient': [Colors.deepPurple.withOpacity(0.1), Colors.purple.withOpacity(0.05)]
        };
      default:
        return {
          'desc': 'ไม่ทราบสภาพอากาศ',
          'icon': '☁️',
          'gradient': [Colors.grey.withOpacity(0.1), Colors.blueGrey.withOpacity(0.05)]
        };
    }
  }
}
