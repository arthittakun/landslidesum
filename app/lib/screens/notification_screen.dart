import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../theme/app_theme.dart';
import '../services/notification_service.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});
  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  List<Map<String, dynamic>> _notifications = [];
  bool _isLoading = true;
  String? _errorMessage;
  int _unreadCount = 0; // เพิ่มตัวแปรสำหรับนับจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  /// โหลดข้อมูลการแจ้งเตือนจาก API
  Future<void> _loadNotifications() async {
    try {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      final notifications = await NotificationService.fetchNotifications();
      final unreadCount = await NotificationService.getUnreadNotificationCount();
      
      setState(() {
        _notifications = notifications;
        _unreadCount = unreadCount;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
  }

  /// รีเฟรชข้อมูล
  Future<void> _refreshNotifications() async {
    await _loadNotifications();
  }

  /// โหลดข้อมูลใหม่จาก API
  Future<void> _forceRefreshNotifications() async {
    try {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      final notifications = await NotificationService.fetchNotifications(forceRefresh: true);
      final unreadCount = await NotificationService.getUnreadNotificationCount();
      
      setState(() {
        _notifications = notifications;
        _unreadCount = unreadCount;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: FutureBuilder<int>(
          future: NotificationService.getUnreadNotificationCount(),
          builder: (context, snapshot) {
            final unreadCount = snapshot.data ?? _unreadCount;
            return Text(unreadCount > 0 ? 'แจ้งเตือน (${unreadCount})' : 'แจ้งเตือน');
          },
        ),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: AppTheme.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshNotifications,
            tooltip: 'รีเฟรชข้อมูล',
          ),
          IconButton(
            icon: const Icon(Icons.cloud_download),
            onPressed: _forceRefreshNotifications,
            tooltip: 'โหลดข้อมูลใหม่จาก API',
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: Theme.of(context).brightness == Brightness.dark
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
        child: RefreshIndicator(
          onRefresh: _refreshNotifications,
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Summary Card
                _buildSummaryCard(),
                const SizedBox(height: 16),
                
                // Action Buttons
                _buildActionButtons(),
                const SizedBox(height: 16),
                
                // Notifications List
                Text(
                  'การแจ้งเตือนล่าสุด',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight 
                        : Colors.black,
                  ),
                ),
                const SizedBox(height: 8),
                
                // Content
                _buildNotificationContent(),
              ],
            ),
          ),
        ),
      ),
    );
  }

  /// สร้าง Summary Card
  Widget _buildSummaryCard() {
    final hasImageCount = _notifications.where((n) => NotificationService.hasImage(n)).length;
    
    return Card(
      color: Theme.of(context).brightness == Brightness.dark 
          ? AppTheme.darkCardSoft 
          : AppTheme.lightOrange,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              children: [
                Icon(
                  _isLoading 
                      ? Icons.hourglass_empty 
                      : _errorMessage != null 
                          ? Icons.error 
                          : Icons.notifications_active,
                  color: _errorMessage != null ? AppTheme.errorColor : AppTheme.brandPrimary,
                  size: 32,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _isLoading 
                            ? 'กำลังโหลด...'
                            : _errorMessage != null 
                                ? 'เกิดข้อผิดพลาด'
                                : '${_notifications.length} การแจ้งเตือนทั้งหมด',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: Theme.of(context).brightness == Brightness.dark 
                              ? AppTheme.textLight 
                              : Colors.black,
                        ),
                      ),
                      FutureBuilder<int>(
                        future: NotificationService.getUnreadNotificationCount(),
                        builder: (context, snapshot) {
                          final unreadCount = snapshot.data ?? _unreadCount;
                          
                          return Text(
                            _isLoading 
                                ? 'กำลังดึงข้อมูลการแจ้งเตือน'
                                : _errorMessage != null 
                                    ? 'ไม่สามารถโหลดข้อมูลได้'
                                    : '${unreadCount} รายการที่ยังไม่ได้อ่าน',
                            style: TextStyle(
                              color: Theme.of(context).brightness == Brightness.dark 
                                  ? AppTheme.textLight.withOpacity(0.7) 
                                  : AppTheme.mediumGrey,
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  /// สร้างปุ่มดำเนินการ
  Widget _buildActionButtons() {
    return const SizedBox.shrink(); // ซ่อนปุ่มทั้งหมด
  }

  /// สร้างเนื้อหาการแจ้งเตือน
  Widget _buildNotificationContent() {
    if (_isLoading) {
      return const SizedBox(
        height: 200,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(AppTheme.brandPrimary),
              ),
              SizedBox(height: 16),
              Text(
                'กำลังโหลดการแจ้งเตือน...',
                style: TextStyle(
                  fontSize: 16,
                  color: AppTheme.textSecondary,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_errorMessage != null) {
      return SizedBox(
        height: 200,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.error_outline,
                size: 64,
                color: AppTheme.errorColor,
              ),
              const SizedBox(height: 16),
              Text(
                'เกิดข้อผิดพลาด',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.errorColor,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  color: AppTheme.textSecondary,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _refreshNotifications,
                icon: const Icon(Icons.refresh),
                label: const Text('ลองใหม่'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.brandPrimary,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_notifications.isEmpty) {
      return SizedBox(
        height: 200,
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.notifications_off,
                size: 64,
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight.withOpacity(0.5) 
                    : AppTheme.mediumGrey,
              ),
              const SizedBox(height: 16),
              Text(
                'ไม่มีการแจ้งเตือน',
                style: TextStyle(
                  fontSize: 18,
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight.withOpacity(0.7) 
                      : AppTheme.mediumGrey,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'ดึงลงเพื่อรีเฟรชข้อมูล',
                style: TextStyle(
                  fontSize: 14,
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight.withOpacity(0.5) 
                      : AppTheme.mediumGrey,
                ),
              ),
            ],
          ),
        ),
      );
    }

    // แสดงการแจ้งเตือนล่าสุดขึ้นบนสุด (เรียงตามเวลา)
    final sortedNotifications = List<Map<String, dynamic>>.from(_notifications);
    sortedNotifications.sort((a, b) {
      final timeA = a['time']?.toString() ?? '';
      final timeB = b['time']?.toString() ?? '';
      return timeB.compareTo(timeA); // เรียงจากใหม่ไปเก่า
    });

    return Column(
      children: sortedNotifications.map((notification) => 
        _buildNotificationCard(notification)
      ).toList(),
    );
  }

  /// สร้างการ์ดการแจ้งเตือน (แสดงเฉพาะ title)
  Widget _buildNotificationCard(Map<String, dynamic> notification) {
    final bool isCriticalNotification = NotificationService.isCriticalNotification(notification);
    final String notificationId = notification['id']?.toString() ?? '';

    return LayoutBuilder(
      builder: (context, constraints) {
        final isSmallScreen = constraints.maxWidth < 300;
        
        return GestureDetector(
          onTap: () {
            _showNotificationDetail(context, notification);
          },
          child: Card(
            margin: const EdgeInsets.only(bottom: 8),
            color: Theme.of(context).brightness == Brightness.dark 
                ? AppTheme.darkCardSoft 
                : null,
            child: Padding(
              padding: EdgeInsets.all(isSmallScreen ? 12.0 : 16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // หัวข้อ
                  Text(
                    notification['title'] ?? 'การแจ้งเตือน',
                    style: TextStyle(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.textLight 
                          : Colors.black,
                      fontWeight: FontWeight.w600,
                      fontSize: isSmallScreen ? 14 : 16,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  
                  const SizedBox(height: 8),
                  
                  // แสดงระดับความรุนแรง (ถ้ามี)
                  if (isCriticalNotification) ...[
                    _buildRiskLevels(notification),
                    const SizedBox(height: 8),
                  ],
                  
                  // เวลา
                  Text(
                    notification['time'] ?? '',
                    style: TextStyle(
                      fontSize: isSmallScreen ? 11 : 12,
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.textLight.withOpacity(0.6) 
                          : AppTheme.mediumGrey,
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  /// สร้างรูปภาพการแจ้งเตือน
  Widget _buildNotificationImage(Map<String, dynamic> notification) {
    final String? imgPath = notification['img_path'];
    if (imgPath == null || imgPath.isEmpty) return const SizedBox.shrink();

    // สร้าง URL เต็มจาก img_path
    final String fullImageUrl = _buildFullImageUrl(imgPath);

    return Container(
      width: double.infinity,
      height: 150, // ลดความสูงลงเพื่อรองรับหน้าจอเล็ก
      decoration: BoxDecoration(
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(12),
          topRight: Radius.circular(12),
        ),
      ),
      child: ClipRRect(
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(12),
          topRight: Radius.circular(12),
        ),
        child: CachedNetworkImage(
          imageUrl: fullImageUrl,
          fit: BoxFit.cover,
          placeholder: (context, url) => Container(
            color: Colors.grey[300],
            child: const Center(
              child: CircularProgressIndicator(),
            ),
          ),
          errorWidget: (context, url, error) => Container(
            color: Colors.grey[300],
            child: const Center(
              child: Icon(Icons.error, color: Colors.grey),
            ),
          ),
        ),
      ),
    );
  }

  /// สร้าง URL รูปภาพเต็มจาก img_path
  String _buildFullImageUrl(String imgPath) {
    // หากเป็น URL เต็มแล้ว
    if (imgPath.startsWith('http://') || imgPath.startsWith('https://')) {
      return imgPath;
    }
    
    // หากเป็น path เฉพาะ ให้ต่อกับ base URL
    if (imgPath.startsWith('/')) {
      return 'https://landslide-alerts.com$imgPath';
    }
    
    // หากเป็น path ปกติ (ไม่มี / นำหน้า) ให้ต่อกับ base URL + /
    return 'https://landslide-alerts.com/$imgPath';
  }

  /// สร้างระดับความเสี่ยง (ไม่มีคำว่า "ระดับ")
  Widget _buildRiskLevels(Map<String, dynamic> notification) {
    final int floodLevel = NotificationService.getFloodLevel(notification);
    final int landslideLevel = NotificationService.getLandslideLevel(notification);
    
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Wrap(
        spacing: 6,
        runSpacing: 4,
        children: [
          if (floodLevel > 0)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.blue.withOpacity(0.2),
                borderRadius: BorderRadius.circular(4),
                border: Border.all(color: Colors.blue.withOpacity(0.3)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.water_drop, size: 10, color: Colors.blue),
                  const SizedBox(width: 2),
                  Text(
                    'น้ำป่าไหลหลาก',
                    style: TextStyle(
                      fontSize: 9,
                      color: Colors.blue[700],
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
          if (landslideLevel > 0)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.orange.withOpacity(0.2),
                borderRadius: BorderRadius.circular(4),
                border: Border.all(color: Colors.orange.withOpacity(0.3)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.landscape, size: 10, color: Colors.orange),
                  const SizedBox(width: 2),
                  Text(
                    'ดินถล่ม',
                    style: TextStyle(
                      fontSize: 9,
                      color: Colors.orange[700],
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
  
  /// แสดงรายละเอียดการแจ้งเตือน
  void _showNotificationDetail(BuildContext context, Map<String, dynamic> notification) {
    final bool isCriticalNotification = NotificationService.isCriticalNotification(notification);
    final bool hasImage = NotificationService.hasImage(notification);
    final String notificationId = notification['id']?.toString() ?? '';

    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (BuildContext context) {
        return Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.all(24),
          child: Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.8, // จำกัดความสูงไว้ที่ 80% ของหน้าจอ
            ),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.darkCardSoft 
                  : Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 20,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Header
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppTheme.brandPrimary.withOpacity(0.1),
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(16),
                      topRight: Radius.circular(16),
                    ),
                    border: Border(
                      bottom: BorderSide(
                        color: AppTheme.brandPrimary.withOpacity(0.2),
                        width: 1,
                      ),
                    ),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          notification['title'] ?? 'การแจ้งเตือน',
                          style: TextStyle(
                            color: Theme.of(context).brightness == Brightness.dark 
                                ? AppTheme.textLight 
                                : Colors.black,
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
                
                // Scrollable Content
                Flexible(
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // รูปภาพ (ถ้ามี)
                        if (hasImage) _buildDetailImage(notification),
                        
                        // Content
                        Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Message
                              Text(
                                'ข้อความ',
                                style: TextStyle(
                                  fontWeight: FontWeight.w600,
                                  color: Theme.of(context).brightness == Brightness.dark 
                                      ? AppTheme.textLight 
                                      : Colors.black87,
                                  fontSize: 16,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                notification['message'] ?? 'ไม่มีข้อความ',
                                style: TextStyle(
                                  color: Theme.of(context).brightness == Brightness.dark 
                                      ? AppTheme.textLight.withOpacity(0.8) 
                                      : Colors.black54,
                                  fontSize: 15,
                                  height: 1.5,
                                ),
                              ),
                              
                              const SizedBox(height: 20),
                              
                              // Risk Levels (ถ้าเป็น critical)
                              if (isCriticalNotification) ...[
                                _buildDetailRiskLevels(notification),
                                const SizedBox(height: 20),
                              ],
                              
                              // Details
                              Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).brightness == Brightness.dark 
                                      ? AppTheme.darkCardSoft.withOpacity(0.5)
                                      : Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: Theme.of(context).brightness == Brightness.dark 
                                        ? AppTheme.darkCardSoft.withOpacity(0.3)
                                        : Colors.grey.shade200,
                                  ),
                                ),
                                child: Column(
                                  children: [
                                    // ตำแหน่ง
                                    Row(
                                      children: [
                                        Icon(
                                          Icons.location_on,
                                          color: AppTheme.brandPrimary,
                                          size: 20,
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'ตำแหน่ง',
                                                style: TextStyle(
                                                  fontWeight: FontWeight.w600,
                                                  color: Theme.of(context).brightness == Brightness.dark 
                                                      ? AppTheme.textLight 
                                                      : Colors.black87,
                                                  fontSize: 14,
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                notification['location_name'] ?? 'ไม่ระบุตำแหน่ง',
                                                style: TextStyle(
                                                  color: Theme.of(context).brightness == Brightness.dark 
                                                      ? AppTheme.textLight.withOpacity(0.7) 
                                                      : Colors.black54,
                                                  fontSize: 14,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    
                                    const SizedBox(height: 16),
                                    
                                    // เวลา
                                    Row(
                                      children: [
                                        Icon(
                                          Icons.access_time,
                                          color: AppTheme.brandPrimary,
                                          size: 20,
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'เวลา',
                                                style: TextStyle(
                                                  fontWeight: FontWeight.w600,
                                                  color: Theme.of(context).brightness == Brightness.dark 
                                                      ? AppTheme.textLight 
                                                      : Colors.black87,
                                                  fontSize: 14,
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                notification['time'] ?? '',
                                                style: TextStyle(
                                                  color: Theme.of(context).brightness == Brightness.dark 
                                                      ? AppTheme.textLight.withOpacity(0.7) 
                                                      : Colors.black54,
                                                  fontSize: 14,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    
                                    // อุปกรณ์ (ถ้ามี)
                                    if (notification['device_name'] != null) ...[
                                      const SizedBox(height: 16),
                                      Row(
                                        children: [
                                          Icon(
                                            Icons.device_hub,
                                            color: AppTheme.brandPrimary,
                                            size: 20,
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  'อุปกรณ์',
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.w600,
                                                    color: Theme.of(context).brightness == Brightness.dark 
                                                        ? AppTheme.textLight 
                                                        : Colors.black87,
                                                    fontSize: 14,
                                                  ),
                                                ),
                                                const SizedBox(height: 4),
                                                Text(
                                                  notification['device_name'],
                                                  style: TextStyle(
                                                    color: Theme.of(context).brightness == Brightness.dark 
                                                        ? AppTheme.textLight.withOpacity(0.7) 
                                                        : Colors.black54,
                                                    fontSize: 14,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                
                // Close Button - Fixed at bottom
                Container(
                  padding: const EdgeInsets.all(20),
                  child: SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.brandPrimary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
                      ),
                      child: const Text(
                        'ปิด',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 16,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  /// สร้างรูปภาพในรายละเอียด
  Widget _buildDetailImage(Map<String, dynamic> notification) {
    final String? imgPath = notification['img_path'];
    if (imgPath == null || imgPath.isEmpty) return const SizedBox.shrink();

    // สร้าง URL เต็มจาก img_path
    final String fullImageUrl = _buildFullImageUrl(imgPath);

    return Container(
      width: double.infinity,
      height: 180, // ลดความสูงลงจาก 250
      margin: const EdgeInsets.symmetric(horizontal: 20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: CachedNetworkImage(
          imageUrl: fullImageUrl,
          fit: BoxFit.cover,
          placeholder: (context, url) => Container(
            color: Colors.grey[300],
            child: const Center(
              child: CircularProgressIndicator(),
            ),
          ),
          errorWidget: (context, url, error) => Container(
            color: Colors.grey[300],
            child: const Center(
              child: Icon(Icons.error, color: Colors.grey),
            ),
          ),
        ),
      ),
    );
  }

  /// สร้างระดับความเสี่ยงในรายละเอียด (แสดงแค่การ์ดความเสี่ยง)
  Widget _buildDetailRiskLevels(Map<String, dynamic> notification) {
    final int floodLevel = NotificationService.getFloodLevel(notification);
    final int landslideLevel = NotificationService.getLandslideLevel(notification);
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'ความเสี่ยง',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            color: Theme.of(context).brightness == Brightness.dark 
                ? AppTheme.textLight 
                : Colors.black87,
            fontSize: 14,
          ),
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            if (floodLevel > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.blue.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.blue.withOpacity(0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.water_drop, color: Colors.blue, size: 16),
                    const SizedBox(width: 6),
                    Text(
                      'น้ำป่าไหลหลาก',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Colors.blue[700],
                      ),
                    ),
                  ],
                ),
              ),
            if (landslideLevel > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.orange.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.orange.withOpacity(0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.landscape, color: Colors.orange, size: 16),
                    const SizedBox(width: 6),
                    Text(
                      'ดินถล่ม',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Colors.orange[700],
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ],
    );
  }
}
