import 'package:flutter/material.dart';
import '../controllers/auth_controller.dart';
import '../services/permission_service.dart';
import '../theme/app_theme.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    
    // ตั้งค่า Animation
    _animationController = AnimationController(
      duration: const Duration(seconds: 2),
      vsync: this,
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeIn),
    );

    _scaleAnimation = Tween<double>(begin: 0.5, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.elasticOut),
    );

    // เริ่ม Animation และตรวจสอบ Auth
    _initializeApp();
  }

  Future<void> _initializeApp() async {
    // เริ่ม Animation
    _animationController.forward();
    
    // รอให้ Animation ทำงาน
    await Future.delayed(const Duration(milliseconds: 500));
    
    // ตรวจสอบสถานะการ Login (ข้อมูลได้ถูกโหลดแล้วใน main.dart)
    // แต่เราจะตรวจสอบอีกครั้งเพื่อความแน่ใจ
    await AuthController.initialize();
    
    // ขออนุญาตทั้งหมดโดยอัตโนมัติแบบเงียบๆ (ไม่แสดงหน้า permission screen)
    if (mounted) {
      await PermissionService.requestAllPermissions(context);
    }
    
    // รอให้ Animation เสร็จ
    await Future.delayed(const Duration(seconds: 2));
    
    // ไปหน้าที่เหมาะสมตามสถานะ login
    if (mounted) {
      if (AuthController.isLoggedIn) {
        Navigator.of(context).pushReplacementNamed('/main');
      } else {
        Navigator.of(context).pushReplacementNamed('/login');
      }
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.brandPrimary,
      body: Center(
        child: AnimatedBuilder(
          animation: _animationController,
          builder: (context, child) {
            return FadeTransition(
              opacity: _fadeAnimation,
              child: ScaleTransition(
                scale: _scaleAnimation,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Logo
                    Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        color: AppTheme.white,
                        borderRadius: BorderRadius.circular(60),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.2),
                            blurRadius: 20,
                            offset: const Offset(0, 10),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(60),
                        child: Image.asset(
                          'image/remove_logo.png',
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    
                    // App Name
                    const Text(
                      'Landslide App',
                      style: TextStyle(
                        fontSize: 32,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.white,
                      ),
                    ),
                    const SizedBox(height: 8),
                    
                    // Subtitle
                    Text(
                      'ระบบตรวจสอบสถานะอุปกรณ์',
                      style: TextStyle(
                        fontSize: 16,
                        color: AppTheme.white.withOpacity(0.7),
                      ),
                    ),
                    const SizedBox(height: 48),
                    
                    // Loading Indicator
                    const SizedBox(
                      width: 40,
                      height: 40,
                      child: CircularProgressIndicator(
                        color: AppTheme.white,
                        strokeWidth: 3,
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
