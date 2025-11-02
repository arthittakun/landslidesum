import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/// **Global Notification System - ระบบการแจ้งเตือนส่วนกลาง**
/// 
/// ใช้สำหรับ:
/// - แสดงการแจ้งเตือนแบบ Overlay บนหน้าจอ
/// - จัดการการแจ้งเตือนแบบ In-App
/// - รองรับหลายประเภทการแจ้งเตือน
class GlobalNotificationService {
  static OverlayEntry? _currentOverlay;
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  /// แสดงการแจ้งเตือนแบบ Floating
  static void showFloatingNotification({
    required String title,
    required String message,
    NotificationType type = NotificationType.info,
    Duration duration = const Duration(seconds: 4),
    VoidCallback? onTap,
  }) {
    final context = navigatorKey.currentContext;
    if (context == null) return;

    // ลบการแจ้งเตือนเก่าถ้ามี
    _currentOverlay?.remove();

    _currentOverlay = OverlayEntry(
      builder: (context) => _FloatingNotification(
        title: title,
        message: message,
        type: type,
        onTap: onTap,
        onDismiss: () {
          _currentOverlay?.remove();
          _currentOverlay = null;
        },
      ),
    );

    Overlay.of(context).insert(_currentOverlay!);

    // ลบการแจ้งเตือนอัตโนมัติ
    Future.delayed(duration, () {
      _currentOverlay?.remove();
      _currentOverlay = null;
    });
  }

  /// แสดงการแจ้งเตือนแบบ SnackBar
  static void showSnackBarNotification({
    required String message,
    NotificationType type = NotificationType.info,
    Duration duration = const Duration(seconds: 3),
    VoidCallback? action,
    String? actionLabel,
  }) {
    final context = navigatorKey.currentContext;
    if (context == null) return;

    Color backgroundColor;
    IconData icon;
    
    switch (type) {
      case NotificationType.success:
        backgroundColor = AppTheme.successColor;
        icon = Icons.check_circle;
        break;
      case NotificationType.warning:
        backgroundColor = AppTheme.warningColor;
        icon = Icons.warning;
        break;
      case NotificationType.error:
        backgroundColor = AppTheme.errorColor;
        icon = Icons.error;
        break;
      case NotificationType.info:
        backgroundColor = AppTheme.brandPrimary;
        icon = Icons.info;
        break;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(icon, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: backgroundColor,
        duration: duration,
        action: action != null && actionLabel != null
            ? SnackBarAction(
                label: actionLabel,
                textColor: Colors.white,
                onPressed: action,
              )
            : null,
      ),
    );
  }

  /// แสดงการแจ้งเตือนแบบ Dialog
  static void showDialogNotification({
    required String title,
    required String message,
    NotificationType type = NotificationType.info,
    String? actionText,
    VoidCallback? onAction,
  }) {
    final context = navigatorKey.currentContext;
    if (context == null) return;

    IconData icon;
    Color iconColor;
    
    switch (type) {
      case NotificationType.success:
        icon = Icons.check_circle;
        iconColor = AppTheme.successColor;
        break;
      case NotificationType.warning:
        icon = Icons.warning;
        iconColor = AppTheme.warningColor;
        break;
      case NotificationType.error:
        icon = Icons.error;
        iconColor = AppTheme.errorColor;
        break;
      case NotificationType.info:
        icon = Icons.info;
        iconColor = AppTheme.brandPrimary;
        break;
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        title: Row(
          children: [
            Icon(icon, color: iconColor),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.textLight 
                      : Colors.black,
                ),
              ),
            ),
          ],
        ),
        content: Text(
          message,
          style: TextStyle(
            color: Theme.of(context).brightness == Brightness.dark 
                ? AppTheme.textLight.withOpacity(0.8) 
                : Colors.black87,
          ),
        ),
        actions: [
          if (onAction != null && actionText != null)
            ElevatedButton(
              onPressed: () {
                Navigator.of(context).pop();
                onAction();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: iconColor,
                foregroundColor: Colors.white,
              ),
              child: Text(actionText),
            ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text(
              'ปิด',
              style: TextStyle(color: AppTheme.textSecondary),
            ),
          ),
        ],
      ),
    );
  }

  /// ลบการแจ้งเตือน Floating ปัจจุบัน
  static void dismissFloatingNotification() {
    _currentOverlay?.remove();
    _currentOverlay = null;
  }
}

/// ประเภทการแจ้งเตือน
enum NotificationType {
  info,
  success,
  warning,
  error,
}

/// Widget สำหรับการแจ้งเตือนแบบ Floating
class _FloatingNotification extends StatefulWidget {
  final String title;
  final String message;
  final NotificationType type;
  final VoidCallback? onTap;
  final VoidCallback onDismiss;

  const _FloatingNotification({
    required this.title,
    required this.message,
    required this.type,
    this.onTap,
    required this.onDismiss,
  });

  @override
  State<_FloatingNotification> createState() => _FloatingNotificationState();
}

class _FloatingNotificationState extends State<_FloatingNotification>
    with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, -1),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOut,
    ));

    _fadeAnimation = Tween<double>(
      begin: 0,
      end: 1,
    ).animate(_animationController);

    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  void _dismiss() {
    _animationController.reverse().then((_) {
      widget.onDismiss();
    });
  }

  @override
  Widget build(BuildContext context) {
    Color backgroundColor;
    Color borderColor;
    IconData icon;
    
    switch (widget.type) {
      case NotificationType.success:
        backgroundColor = AppTheme.successColor;
        borderColor = AppTheme.successColor;
        icon = Icons.check_circle;
        break;
      case NotificationType.warning:
        backgroundColor = AppTheme.warningColor;
        borderColor = AppTheme.warningColor;
        icon = Icons.warning;
        break;
      case NotificationType.error:
        backgroundColor = AppTheme.errorColor;
        borderColor = AppTheme.errorColor;
        icon = Icons.error;
        break;
      case NotificationType.info:
        backgroundColor = AppTheme.brandPrimary;
        borderColor = AppTheme.brandPrimary;
        icon = Icons.notifications;
        break;
    }

    return Positioned(
      top: MediaQuery.of(context).padding.top + 8,
      left: 16,
      right: 16,
      child: SlideTransition(
        position: _slideAnimation,
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: Material(
            elevation: 8,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              decoration: BoxDecoration(
                color: backgroundColor,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: borderColor, width: 1),
              ),
              child: InkWell(
                onTap: widget.onTap ?? _dismiss,
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Icon(
                        icon,
                        color: Colors.white,
                        size: 24,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              widget.title,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              widget.message,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 14,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: _dismiss,
                        icon: const Icon(
                          Icons.close,
                          color: Colors.white,
                          size: 20,
                        ),
                        constraints: const BoxConstraints(),
                        padding: EdgeInsets.zero,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
