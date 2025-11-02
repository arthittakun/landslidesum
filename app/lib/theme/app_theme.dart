import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Enum สำหรับการเลือกโหมดธีม
enum AppThemeMode { light, dark, auto }

/// **AppTheme Class - ระบบจัดการสีและธีมของแอป**
/// 
/// ### การใช้งานสีแบบ Semantic:
/// - **brandPrimary**: ปุ่มหลัก, AppBar, ลิงก์สำคัญ
/// - **successColor**: สถานะสำเร็จ, ออนไลน์, การดำเนินการสำเร็จ
/// - **warningColor**: การเตือน, สถานะระวัง, แจ้งเตือน
/// - **errorColor**: ข้อผิดพลาด, สถานะออฟไลน์, อันตราย
/// - **infoColor**: ข้อมูลทั่วไป, การแจ้งเตือนปกติ
/// 
/// ### การใช้งานสีพื้นผิว:
/// - **backgroundLight**: พื้นหลังหน้าจอ, การไล่สี
/// - **surfaceLight**: พื้นผิวการ์ด, ป๊อปอัป
/// - **cardLight**: การ์ดเนื้อหา, กล่องข้อความ
/// 
/// ### การใช้งานสีข้อความ:
/// - **textPrimary**: หัวข้อ, ข้อความสำคัญ
/// - **textSecondary**: คำอธิบาย, ข้อความรอง
/// - **textTertiary**: วันที่, เวลา, ข้อมูลเสริม
/// 
/// ### ตัวอย่างการใช้งาน:
/// ```dart
/// // ใช้สีแบบ semantic
/// color: AppTheme.brandPrimary
/// color: AppTheme.successColor
/// 
/// // ใช้ method helper
/// color: AppTheme.getSemanticColor('warning')
/// backgroundColor: AppTheme.getCardBackgroundColor('success')
/// ```
class AppTheme {
  // === Core Brand Colors - ปรับเปลี่ยนได้ง่าย ===
  static const Color primaryColor = Color(0xFF64B5F6);       // สีหลักของแบรนด์ (ใช้เป็นฐาน)
  static const Color secondaryColor = Color(0xFF81C784);      // สีรองของแบรนด์ (สีเขียวอ่อน)
  static const Color accentColor = Color(0xFF4DB6AC);         // สีเน้น (สีเขียวฟ้า)
  
  // === UI Element Colors - ตามหน้าที่การใช้งาน ===
  static const Color brandPrimary = Color.fromARGB(255, 143, 200, 247);    // ปุ่มหลัก, AppBar, ลิงก์สำคัญ
  static const Color brandSecondary = secondaryColor;        // ปุ่มรอง, เมนูย่อย
  static const Color brandTertiary = Color(0xFF9C27B0);     // สีที่ 3, เมนูเพิ่มเติม
  static const Color brandAccent = accentColor;              // ไฮไลท์, การเน้น, แท็บที่เลือก
  
  // === Semantic Colors - ตามความหมาย ===
  static const Color successColor = Color(0xFF66BB6A);       // สำเร็จ, ออนไลน์, สถานะดี
  static const Color warningColor = Color(0xFFFFB74D);       // เตือน, ระวัง, แจ้งเตือน
  static const Color errorColor = Color(0xFFEF5350);         // ข้อผิดพลาด, อันตราย, ออฟไลน์
  static const Color infoColor = primaryColor;               // ข้อมูลทั่วไป, แจ้งเตือนปกติ
  
  // === Surface Colors - พื้นผิวและพื้นหลัง ===
  static const Color surfaceLight = Color(0xFFF1F8FF);       // พื้นผิวการ์ด, ป๊อปอัป
  static const Color backgroundLight = Color(0xFFE3F2FD);    // พื้นหลังหน้าจอ, การไล่สี
  static const Color cardLight = Colors.white;               // การ์ดเนื้อหา, กล่องข้อความ
  
  // === Text Colors - สีข้อความ ===
  static const Color textPrimary = Color(0xFF2D3748);        // ข้อความหลัก, หัวข้อ
  static const Color textSecondary = Color(0xFF4A5568);      // ข้อความรอง, คำอธิบาย
  static const Color textTertiary = Color(0xFF718096);       // ข้อความเบา, วันที่, เวลา
  static const Color textLight = Color(0xFFE2E8F0);          // ข้อความในโหมดมืด
  
  // === Neutral Colors - สีเทาและพื้นฐาน ===
  static const Color white = Colors.white;                   // พื้นหลังการ์ด, ปุ่ม
  static const Color lightGrey = Color(0xFFF8F9FA);         // พื้นหลังหน้าจอ, แบ่งส่วน
  static const Color mediumGrey = Color(0xFFB0BEC5);        // เส้นขอบ, ไอคอนไม่ใช้งาน
  static const Color darkGrey = Color(0xFF78909C);          // ข้อความรอง, ไอคอน
  static const Color black87 = Color(0xDE000000);           // ข้อความหลักในโหมดสว่าง
  static const Color black54 = Color(0x8A000000);           // ข้อความรองในโหมดสว่าง

  // === Dark Theme Colors - โหมดมืด ===
  static const Color darkBackground = Color(0xFF0F1419);    // พื้นหลังหลักโหมดมืด
  static const Color darkSurface = Color(0xFF1A202C);       // พื้นผิวโหมดมืด, AppBar
  static const Color darkCard = Color(0xFF2D3748);          // การ์ดโหมดมืด
  static const Color darkCardSoft = Color(0xFF1E2A3A);      // การ์ดอ่อนโหมดมืด, ฟอร์ม
  static const Color darkGrey2 = Color(0xFF4A5568);         // เส้นขอบโหมดมืด

  // === Backward Compatibility - รองรับโค้ดเก่า ===
  // เก็บชื่อเก่าไว้เพื่อไม่ให้โค้ดเก่า error
  static Color get primaryGreen => brandPrimary;     // เปลี่ยนเป็น brandPrimary
  static Color get primaryBlue => brandPrimary;      // เปลี่ยนเป็น brandPrimary
  static Color get secondaryGreen => brandSecondary; // เปลี่ยนเป็น brandSecondary
  static Color get secondaryBlue => brandSecondary;  // เปลี่ยนเป็น brandSecondary
  static Color get darkGreen => brandPrimary;        // เปลี่ยนเป็น brandPrimary
  static Color get lightGreen => successColor;       // เปลี่ยนเป็น successColor
  static Color get backgroundGreen => backgroundLight; // เปลี่ยนเป็น backgroundLight
  static Color get surfaceGreen => surfaceLight;     // เปลี่ยนเป็น surfaceLight
  static Color get warningOrange => warningColor;    // เปลี่ยนเป็น warningColor
  static Color get lightOrange => Color(0xFFFFF8E1); // พื้นหลังเตือน
  static Color get errorRed => errorColor;           // เปลี่ยนเป็น errorColor
  static Color get lightRed => Color(0xFFFFEBEE);    // พื้นหลังข้อผิดพลาด
  static Color get successGreen => successColor;     // เปลี่ยนเป็น successColor
  static Color get onlineGreen => successColor;      // สถานะออนไลน์
  static Color get onlineBlue => successColor;       // สถานะออนไลน์
  static Color get offlineRed => errorColor;         // สถานะออฟไลน์
  static Color get warningAmber => warningColor;     // แจ้งเตือน

  // === Flexible Color System - สำหรับการกำหนดสีแบบยืดหยุ่น ===
  static const Map<String, Color> colorPalette = {
    // Primary Brand Colors
    'primary': brandPrimary,
    'primaryDark': primaryColor,
    'primaryLight': Color(0xFFBBDEFB),
    'secondary': brandSecondary,
    'accent': brandAccent,
    
    // Semantic Colors
    'success': successColor,
    'warning': warningColor,
    'error': errorColor,
    'info': infoColor,
    
    // Surface Colors
    'surface': surfaceLight,
    'background': backgroundLight,
    'card': cardLight,
    
    // Text Colors
    'textPrimary': textPrimary,
    'textSecondary': textSecondary,
    'textTertiary': textTertiary,
    'textLight': textLight,
    
    // Dark Theme Colors
    'darkBackground': darkBackground,
    'darkSurface': darkSurface,
    'darkCard': darkCard,
  };

  // === Helper Methods - วิธีการใช้งานสีแบบยืดหยุ่น ===
  /// ดึงสีจาก colorPalette พร้อมรองรับโหมดมืด
  /// ใช้สำหรับ: การกำหนดสีแบบ dynamic ตาม key
  static Color getColor(String colorKey, {bool isDark = false}) {
    if (isDark && colorKey.startsWith('dark')) {
      return colorPalette[colorKey] ?? darkSurface;
    }
    
    // Auto dark variants - แปลงสีอัตโนมัติสำหรับโหมดมืด
    if (isDark) {
      switch (colorKey) {
        case 'background':
          return darkBackground;
        case 'surface':
          return darkSurface;
        case 'card':
          return darkCard;
        case 'textPrimary':
          return textLight;
        case 'textSecondary':
          return textLight.withOpacity(0.8);
        case 'textTertiary':
          return textLight.withOpacity(0.6);
        default:
          return colorPalette[colorKey] ?? brandPrimary;
      }
    }
    
    return colorPalette[colorKey] ?? brandPrimary;
  }

  /// ดึงสี semantic ตามประเภท
  /// ใช้สำหรับ: การแสดงสถานะ, การแจ้งเตือน
  static Color getSemanticColor(String type) {
    switch (type) {
      case 'success':
        return successColor;    // สีเขียว - ใช้กับสำเร็จ, ออนไลน์
      case 'warning':
        return warningColor;    // สีส้ม - ใช้กับเตือน, ระวัง
      case 'error':
        return errorColor;      // สีแดง - ใช้กับข้อผิดพลาด, อันตราย
      case 'info':
        return infoColor;       // สีฟ้า - ใช้กับข้อมูลทั่วไป
      default:
        return brandPrimary;    // สีหลักแบรนด์
    }
  }

  // Theme Management
  static const String _themeKey = 'app_theme_mode';
  
  static Future<AppThemeMode> getThemeMode() async {
    final prefs = await SharedPreferences.getInstance();
    final themeIndex = prefs.getInt(_themeKey) ?? 2; // default to auto
    return AppThemeMode.values[themeIndex];
  }
  
  static Future<void> setThemeMode(AppThemeMode mode) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_themeKey, mode.index);
  }
  
  static bool isDarkTime() {
    final hour = DateTime.now().hour;
    return hour < 6 || hour >= 18; // 6 PM to 6 AM
  }

  static ThemeData get lightTheme {
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(
        seedColor: primaryColor,
        brightness: Brightness.light,
      ).copyWith(
        primary: brandPrimary,
        secondary: secondaryBlue,
        surface: surfaceLight,
        onPrimary: white,
        onSecondary: white,
        error: errorColor,
      ),
      useMaterial3: true,
      
      // AppBar Theme
      appBarTheme: const AppBarTheme(
        backgroundColor: brandPrimary,
        foregroundColor: white,
        elevation: 2,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: white,
        ),
      ),

      // Card Theme
      cardTheme: const CardThemeData(
        color: white,
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(12)),
        ),
      ),

      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: brandPrimary,
          foregroundColor: white,
          elevation: 2,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      // Text Button Theme
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: brandPrimary,
          textStyle: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: mediumGrey),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: mediumGrey),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: brandPrimary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: errorColor, width: 2),
        ),
        prefixIconColor: brandPrimary,
        suffixIconColor: mediumGrey,
      ),

      // Bottom Navigation Bar Theme
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: white,
        selectedItemColor: brandPrimary,
        unselectedItemColor: mediumGrey,
        elevation: 8,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: TextStyle(
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
        unselectedLabelStyle: TextStyle(
          fontSize: 10,
        ),
      ),

      // Checkbox Theme
      checkboxTheme: CheckboxThemeData(
        fillColor: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return brandPrimary;
          }
          return null;
        }),
      ),

      // Dialog Theme
      dialogTheme: const DialogThemeData(
        backgroundColor: white,
        elevation: 8,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(16)),
        ),
        titleTextStyle: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.bold,
          color: black87,
        ),
        contentTextStyle: TextStyle(
          fontSize: 16,
          color: darkGrey,
        ),
      ),

      // List Tile Theme
      listTileTheme: const ListTileThemeData(
        contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        iconColor: brandPrimary,
      ),

      // Icon Theme
      iconTheme: const IconThemeData(
        color: brandPrimary,
        size: 24,
      ),

      // Progress Indicator Theme
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: brandPrimary,
      ),

      // Scaffold Background
      scaffoldBackgroundColor: lightGrey,
    );
  }

  static ThemeData get darkTheme {
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(
        seedColor: brandPrimary,
        brightness: Brightness.dark,
      ).copyWith(
        primary: brandPrimary,
        secondary: secondaryBlue,
        surface: darkSurface,
        onPrimary: white,
        onSecondary: white,
        error: errorColor,
        background: darkBackground,
      ),
      useMaterial3: true,
      
      // AppBar Theme
      appBarTheme: const AppBarTheme(
        backgroundColor: darkSurface,
        foregroundColor: textLight,
        elevation: 2,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: textLight,
        ),
      ),

      // Card Theme
      cardTheme: const CardThemeData(
        color: darkCardSoft,
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(12)),
        ),
      ),

      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: brandPrimary,
          foregroundColor: white,
          elevation: 2,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      // Text Button Theme
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: brandPrimary,
          textStyle: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: darkCardSoft,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: darkGrey2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: darkGrey2),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: brandPrimary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: errorColor, width: 2),
        ),
        prefixIconColor: brandPrimary,
        suffixIconColor: textLight,
      ),

      // Bottom Navigation Bar Theme
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: darkSurface,
        selectedItemColor: brandPrimary,
        unselectedItemColor: textLight,
        elevation: 8,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: TextStyle(
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
        unselectedLabelStyle: TextStyle(
          fontSize: 10,
        ),
      ),

      // Checkbox Theme
      checkboxTheme: CheckboxThemeData(
        fillColor: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return brandPrimary;
          }
          return null;
        }),
      ),

      // Dialog Theme
      dialogTheme: const DialogThemeData(
        backgroundColor: darkCardSoft,
        elevation: 8,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(16)),
        ),
        titleTextStyle: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.bold,
          color: textLight,
        ),
        contentTextStyle: TextStyle(
          fontSize: 16,
          color: textLight,
        ),
      ),

      // List Tile Theme
      listTileTheme: const ListTileThemeData(
        contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        iconColor: brandPrimary,
        textColor: textLight,
      ),

      // Icon Theme
      iconTheme: const IconThemeData(
        color: textLight,
        size: 24,
      ),

      // Progress Indicator Theme
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: brandPrimary,
      ),

      // Scaffold Background
      scaffoldBackgroundColor: darkBackground,
      
      // Text Theme
      textTheme: const TextTheme(
        bodyLarge: TextStyle(color: textLight),
        bodyMedium: TextStyle(color: textLight),
        bodySmall: TextStyle(color: textLight),
        headlineLarge: TextStyle(color: textLight),
        headlineMedium: TextStyle(color: textLight),
        headlineSmall: TextStyle(color: textLight),
        titleLarge: TextStyle(color: textLight),
        titleMedium: TextStyle(color: textLight),
        titleSmall: TextStyle(color: textLight),
        labelLarge: TextStyle(color: textLight),
        labelMedium: TextStyle(color: textLight),
        labelSmall: TextStyle(color: textLight),
      ),
    );
  }

  // === Extension Methods - เมธอดเสริมสำหรับสีเฉพาะ ===
  
  /// ดึงสีตามสถานะอุปกรณ์
  /// ใช้สำหรับ: ไอคอนสถานะ, ข้อความสถานะ, สัญญาณ
  static Color getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'online':
        return onlineBlue;      // สีเขียว - อุปกรณ์ออนไลน์
      case 'offline':
        return offlineRed;      // สีแดง - อุปกรณ์ออฟไลน์
      case 'warning':
        return warningOrange;   // สีส้ม - สถานะเตือน
      default:
        return mediumGrey;      // สีเทา - สถานะไม่ทราบ
    }
  }

  /// ดึงสีตามประเภทการแจ้งเตือน
  /// ใช้สำหรับ: การ์ดแจ้งเตือน, ป๊อปอัป, สัญญาณเตือน
  static Color getNotificationColor(String type) {
    switch (type.toLowerCase()) {
      case 'warning':
        return warningOrange;   // สีส้ม - การเตือน
      case 'error':
        return errorColor;      // สีแดง - ข้อผิดพลาด
      case 'success':
        return successGreen;    // สีเขียว - สำเร็จ
      default:
        return brandPrimary;    // สีฟ้า - ข้อมูลทั่วไป
    }
  }

  /// ดึงสีพื้นหลังการ์ดตามประเภท
  /// ใช้สำหรับ: พื้นหลังการ์ด, คอนเทนเนอร์, บล็อกเนื้อหา
  static Color getCardBackgroundColor(String type, {bool isDark = false}) {
    if (isDark) {
      // สีสำหรับโหมดมืด
      switch (type.toLowerCase()) {
        case 'success':
          return darkCardSoft.withOpacity(0.8);  // เขียวอ่อนมืด
        case 'warning':
          return darkCardSoft.withOpacity(0.9);  // ส้มอ่อนมืด
        case 'error':
          return darkCardSoft.withOpacity(0.8);  // แดงอ่อนมืด
        case 'info':
          return darkCardSoft;                   // ฟ้าอ่อนมืด
        default:
          return darkCardSoft;                   // การ์ดมาตรฐานมืด
      }
    } else {
      // สีสำหรับโหมดสว่าง
      switch (type.toLowerCase()) {
        case 'success':
          return backgroundLight;                // เขียวอ่อนสว่าง
        case 'warning':
          return lightOrange;                    // ส้มอ่อนสว่าง
        case 'error':
          return lightRed;                       // แดงอ่อนสว่าง
        case 'info':
          return white;                          // ขาวสำหรับข้อมูล
        default:
          return white;                          // การ์ดมาตรฐานสว่าง
      }
    }
  }
}
