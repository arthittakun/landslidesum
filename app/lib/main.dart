import 'package:flutter/material.dart';
import 'theme/app_theme.dart';
import 'controllers/theme_controller.dart';
import 'screens/splash_screen.dart';
import 'screens/login_screen.dart';
import 'screens/register_screen.dart';
import 'screens/forgot_password_screen.dart';
import 'screens/map_screen.dart';
import 'screens/device_environment_screen.dart';
import 'screens/home_screen.dart';
import 'screens/menu_screen.dart';
import 'screens/device_status_screen.dart';
import 'screens/notification_screen.dart';
import 'screens/settings_screen.dart';
import 'screens/about_screen.dart';
import 'widgets/custom_bottom_navigation_bar.dart';
import 'controllers/navigation_controller.dart';
import 'controllers/auth_controller.dart';
import 'services/app_settings_service.dart';
import 'services/global_notification_service.dart';
import 'services/local_notification_service.dart';
import 'screens/permission_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // เริ่มต้น Controllers
  await AuthController.initialize();
  await ThemeController().initialize();
  
  // เริ่มต้นระบบการแจ้งเตือนบนมือถือ (แก้ไขและเพิ่ม error handling)
  try {
    final bool notificationInitialized = await LocalNotificationService.initialize(
      onNotificationTap: (payload) {
        // ที่นี่สามารถนำทางไปยังหน้าที่เกี่ยวข้องได้
      },
    );
    
    if (notificationInitialized) {
      // Notification service initialized successfully
    } else {
      // Failed to initialize notification service
    }
  } catch (e) {
    // Error initializing notification service
  }
 
  try {
    await AppSettingsService.initialize(
      onDataRefresh: () {
        // ที่นี่สามารถเรียกฟังก์ชันการดึงข้อมูลจากหน้าต่างๆ ได้
      },
      onNotification: (message) {
        // แสดงการแจ้งเตือนบนมือถือเมื่อได้รับข้อมูลใหม่จาก API
        LocalNotificationService.showInstantNotification(
          title: 'การแจ้งเตือนใหม่',
          body: message,
          payload: 'api_notification',
        );
      },
    );
  } catch (e) {
    // Error initializing app settings service
  }
  
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  
  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: ThemeController(),
      builder: (context, child) {
        return MaterialApp(
          title: 'Landslide App',
          theme: AppTheme.lightTheme,
          darkTheme: AppTheme.darkTheme,
          themeMode: ThemeController().currentThemeMode,
          debugShowCheckedModeBanner: false,
          navigatorKey: GlobalNotificationService.navigatorKey,
          initialRoute: '/',
          routes: {
            '/': (context) => const SplashScreen(),
            '/login': (context) => const LoginScreen(),
            '/register': (context) => const RegisterScreen(),
            '/forgot-password': (context) => const ForgotPasswordScreen(),
            '/permission': (context) => const PermissionScreen(),
            '/map': (context) => const MapScreen(),
            '/device-environment': (context) => const DeviceEnvironmentScreen(),
            '/main': (context) => const MainScreen(),
            '/about': (context) => const AboutScreen(),
          },
        );
      },
    );
  }
}

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = [
    const HomeScreen(),
    const MenuScreen(),
    const DeviceStatusScreen(),
    const NotificationScreen(),
    const SettingsScreen(),
  ];

  void _onTabTapped(int index) {
    setState(() {
      _currentIndex = index;
      NavigationController.setCurrentIndex(index);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_currentIndex],
      bottomNavigationBar: CustomBottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _onTabTapped,
      ),
    );
  }
}
