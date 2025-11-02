import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'dart:developer' as dev;
import '../services/device_by_location_service.dart';
import '../theme/app_theme.dart';

class DeviceDetailScreen extends StatefulWidget {
  final DeviceData device;
  final DevicesByLocationResult deviceResult;

  const DeviceDetailScreen({
    super.key,
    required this.device,
    required this.deviceResult,
  });

  @override
  State<DeviceDetailScreen> createState() => _DeviceDetailScreenState();
}

class _DeviceDetailScreenState extends State<DeviceDetailScreen> {
  @override
  void initState() {
    super.initState();
    dev.log('📱 [DeviceDetailScreen] เปิดรายละเอียดอุปกรณ์: ${widget.device.deviceId}', name: 'DeviceDetailScreen');
  }

  String _formatUnixTimestamp(int timestamp) {
    if (timestamp == 0) return 'ไม่ระบุ';
    try {
      final dateTime = DateTime.fromMillisecondsSinceEpoch(timestamp * 1000);
      return DateFormat('dd/MM/yyyy HH:mm:ss').format(dateTime);
    } catch (e) {
      return timestamp.toString();
    }
  }

  IconData _getDeviceIcon() {
    return Icons.device_hub; // อุปกรณ์ทั่วไป - ใช้ไอคอนเดียวกันหมด
  }

  Color _getDeviceColor() {
    return AppTheme.brandPrimary; // ใช้สีเดียวกันหมด
  }

  @override
  Widget build(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    final deviceColor = _getDeviceColor();

    return Scaffold(
      appBar: AppBar(
        title: Text('รายละเอียดอุปกรณ์'),
        backgroundColor: deviceColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: isDarkMode
                ? [
                    AppTheme.darkBackground,
                    AppTheme.darkSurface,
                  ]
                : [
                    AppTheme.brandPrimary.withOpacity(0.05),
                    Colors.white,
                  ],
          ),
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Device Header Card
              Card(
                color: isDarkMode ? AppTheme.darkCard : Colors.white,
                elevation: 4,
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      // Device Icon and Name
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: deviceColor,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Icon(
                              _getDeviceIcon(),
                              color: Colors.white,
                              size: 32,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.device.deviceName,
                                  style: TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: isDarkMode ? AppTheme.textLight : Colors.black,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'อุปกรณ์ตรวจวัด',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: deviceColor,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      // Status Badge
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                        decoration: BoxDecoration(
                          color: widget.device.voidStatus == 0 
                              ? AppTheme.brandPrimary.withOpacity(0.1)
                              : AppTheme.errorColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: widget.device.voidStatus == 0 
                                ? AppTheme.brandPrimary
                                : AppTheme.errorColor,
                            width: 1,
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              widget.device.voidStatus == 0 
                                  ? Icons.check_circle
                                  : Icons.error,
                              color: widget.device.voidStatus == 0 
                                  ? AppTheme.brandPrimary
                                  : AppTheme.errorColor,
                              size: 20,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              widget.device.voidStatus == 0 ? 'ใช้งานได้' : 'ปิดใช้งาน',
                              style: TextStyle(
                                fontSize: 16,
                                color: widget.device.voidStatus == 0 
                                    ? AppTheme.brandPrimary
                                    : AppTheme.errorColor,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              
              const SizedBox(height: 16),
              
              // Device Information
              Card(
                color: isDarkMode ? AppTheme.darkCard : Colors.white,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'ข้อมูลอุปกรณ์',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: isDarkMode ? AppTheme.textLight : Colors.black,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildInfoRow(
                        icon: Icons.tag,
                        label: 'รหัสอุปกรณ์',
                        value: widget.device.deviceId,
                        isDarkMode: isDarkMode,
                      ),
                      _buildInfoRow(
                        icon: Icons.camera_alt,
                        label: 'ฟีเจอร์ถ่ายภาพ',
                        value: widget.device.takePhoto == 1 ? 'เปิดใช้งาน' : 'ปิดใช้งาน',
                        isDarkMode: isDarkMode,
                        valueColor: widget.device.takePhoto == 1 ? AppTheme.brandPrimary : Colors.grey,
                      ),
                    ],
                  ),
                ),
              ),
              
              const SizedBox(height: 16),
              
              // Location Information
              Card(
                color: isDarkMode ? AppTheme.darkCard : Colors.white,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'ข้อมูลสถานที่',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: isDarkMode ? AppTheme.textLight : Colors.black,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildInfoRow(
                        icon: Icons.location_on,
                        label: 'ชื่อสถานที่',
                        value: widget.device.locationName,
                        isDarkMode: isDarkMode,
                      ),
                      _buildInfoRow(
                        icon: Icons.place,
                        label: 'รหัสสถานที่',
                        value: widget.device.locationId,
                        isDarkMode: isDarkMode,
                      ),
                      _buildInfoRow(
                        icon: Icons.my_location,
                        label: 'พิกัด',
                        value: '${widget.device.latitude}, ${widget.device.longitude}',
                        isDarkMode: isDarkMode,
                      ),
                    ],
                  ),
                ),
              ),
              
              const SizedBox(height: 16),
              
              // User Info (if available)
              if (widget.deviceResult.userInfo != null)
                Card(
                  color: isDarkMode ? AppTheme.darkCard : Colors.white,
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'ข้อมูลผู้ใช้',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: isDarkMode ? AppTheme.textLight : Colors.black,
                          ),
                        ),
                        const SizedBox(height: 16),
                        _buildInfoRow(
                          icon: Icons.person,
                          label: 'ผู้ใช้',
                          value: widget.deviceResult.userInfo!.username,
                          isDarkMode: isDarkMode,
                        ),
                        _buildInfoRow(
                          icon: Icons.email,
                          label: 'อีเมล',
                          value: widget.deviceResult.userInfo!.email,
                          isDarkMode: isDarkMode,
                        ),
                        _buildInfoRow(
                          icon: Icons.access_time,
                          label: 'เวลาเข้าสู่ระบบ',
                          value: _formatUnixTimestamp(widget.deviceResult.userInfo!.loginTime),
                          isDarkMode: isDarkMode,
                        ),
                      ],
                    ),
                  ),
                ),
              
              const SizedBox(height: 24),
              
              // Action Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.of(context).pop();
                  },
                  icon: const Icon(Icons.arrow_back),
                  label: const Text('กลับ'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: deviceColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
    required bool isDarkMode,
    Color? valueColor,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            size: 20,
            color: AppTheme.brandPrimary,
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 14,
                color: isDarkMode 
                    ? AppTheme.textLight.withOpacity(0.8) 
                    : Colors.black54,
              ),
            ),
          ),
          Expanded(
            flex: 3,
            child: Text(
              value,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: valueColor ?? (isDarkMode ? AppTheme.textLight : Colors.black),
              ),
              textAlign: TextAlign.end,
            ),
          ),
        ],
      ),
    );
  }
}
