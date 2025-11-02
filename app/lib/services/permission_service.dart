import 'dart:io';
import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:geolocator/geolocator.dart';
import '../theme/app_theme.dart';

/// **Permission Service - บริการจัดการสิทธิ์การใช้งาน**
/// 
/// ใช้สำหรับ:
/// - ขออนุญาตการแจ้งเตือน
/// - ขออนุญาตการเข้าถึงตำแหน่ง
/// - แสดง dialog คำอธิบายเหตุผล
class PermissionService {
  
  /// ขออนุญาตทั้งหมดที่จำเป็น (ขอพร้อมกัน)
  static Future<Map<String, bool>> requestAllPermissions(BuildContext context) async {
    // ขออนุญาตทั้งสองอย่างพร้อมกัน
    final results = await Future.wait([
      _requestNotificationPermission(context),
      _requestLocationPermission(context),
    ]);
    
    return {
      'notifications': results[0],
      'location': results[1],
    };
  }
  
  /// ขออนุญาตการแจ้งเตือน
  static Future<bool> _requestNotificationPermission(BuildContext context) async {
    if (Platform.isAndroid) {
      // ขออนุญาตโดยตรงจากระบบ
      final result = await Permission.notification.request();
      return result.isGranted;
    }
    return true; // iOS จะขออนุญาตผ่าน notification plugin
  }
  
  /// ขออนุญาตการเข้าถึงตำแหน่ง
  static Future<bool> _requestLocationPermission(BuildContext context) async {
    try {
      // ตรวจสอบว่าบริการ location เปิดอยู่หรือไม่
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        final shouldEnable = await _showLocationServiceDialog(context);
        if (!shouldEnable) return false;
        
        // ลองตรวจสอบอีกครั้งหลังจากแสดง dialog
        serviceEnabled = await Geolocator.isLocationServiceEnabled();
        if (!serviceEnabled) return false;
      }
      
      // ตรวจสอบสิทธิ์และขออนุญาตโดยตรงจากระบบ
      LocationPermission permission = await Geolocator.checkPermission();
      
      if (permission == LocationPermission.denied) {
        // ขออนุญาตโดยตรงจากระบบ ไม่แสดง dialog ของแอพ
        permission = await Geolocator.requestPermission();
      }
      
      if (permission == LocationPermission.deniedForever) {
        await _showPermissionDeniedDialog(context);
        return false;
      }
      
      return permission == LocationPermission.whileInUse ||
             permission == LocationPermission.always;
             
    } catch (e) {
      return false;
    }
  }
  
  /// แสดง dialog สำหรับขออนุญาต
  static Future<bool> _showPermissionDialog(
    BuildContext context, {
    required String title,
    required String message,
    required IconData icon,
    required Color iconColor,
  }) async {
    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(icon, color: iconColor, size: 28),
            const SizedBox(width: 12),
            Expanded(child: Text(title)),
          ],
        ),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text(
              'ไม่อนุญาต',
              style: TextStyle(color: AppTheme.textSecondary),
            ),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: iconColor,
              foregroundColor: Colors.white,
            ),
            child: const Text('อนุญาต'),
          ),
        ],
      ),
    );
    
    return result ?? false;
  }
  
  /// แสดง dialog เมื่อบริการ location ปิดอยู่
  static Future<bool> _showLocationServiceDialog(BuildContext context) async {
    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.location_off, color: AppTheme.warningColor, size: 28),
            SizedBox(width: 12),
            Text('เปิดบริการตำแหน่ง'),
          ],
        ),
        content: const Text(
          'กรุณาเปิดบริการตำแหน่ง (GPS) ในการตั้งค่าเครื่องของคุณ\n\n'
          'แอปจะใช้ตำแหน่งเพื่อแสดงข้อมูลสภาพอากาศและแผนที่ในพื้นที่ของคุณ'
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text(
              'ข้าม',
              style: TextStyle(color: AppTheme.textSecondary),
            ),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.warningColor,
              foregroundColor: Colors.white,
            ),
            child: const Text('เข้าใจแล้ว'),
          ),
        ],
      ),
    );
    
    return result ?? false;
  }
  
  /// แสดง dialog เมื่อถูกปฏิเสธอย่างถาวร
  static Future<void> _showPermissionDeniedDialog(BuildContext context) async {
    await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.block, color: AppTheme.errorColor, size: 28),
            SizedBox(width: 12),
            Text('ไม่ได้รับอนุญาต'),
          ],
        ),
        content: const Text(
          'การเข้าถึงตำแหน่งถูกปฏิเสธอย่างถาวร\n\n'
          'หากต้องการใช้ฟีเจอร์นี้ กรุณาเปิดอนุญาตในการตั้งค่าแอป'
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('ปิด'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              openAppSettings();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.brandPrimary,
              foregroundColor: Colors.white,
            ),
            child: const Text('เปิดการตั้งค่า'),
          ),
        ],
      ),
    );
  }
  
  /// ตรวจสอบสถานะสิทธิ์ทั้งหมด
  static Future<Map<String, bool>> checkAllPermissions() async {
    final results = <String, bool>{};
    
    // ตรวจสอบการแจ้งเตือน
    if (Platform.isAndroid) {
      results['notifications'] = await Permission.notification.isGranted;
    } else {
      results['notifications'] = true; // iOS จะตรวจสอบผ่าน notification plugin
    }
    
    // ตรวจสอบตำแหน่ง
    try {
      final permission = await Geolocator.checkPermission();
      results['location'] = permission == LocationPermission.whileInUse ||
                           permission == LocationPermission.always;
    } catch (e) {
      results['location'] = false;
    }
    
    return results;
  }
}
