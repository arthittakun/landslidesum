import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';
import 'radar_screen.dart';
import 'weather_screen.dart';
import 'hourly_weather_screen.dart';
import 'rain_report_screen.dart';

class MenuScreen extends StatelessWidget {
  const MenuScreen({super.key});



  @override
  Widget build(BuildContext context) {
    return Scaffold(
       appBar: AppBar(
        title: const Text('เมนูหลัก'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Colors.white,
       
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
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20.0, 20.0, 20.0, 20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Menu Title
                Text(
                  'เมนูระบบ',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.brandPrimary,
                    letterSpacing: 0.3,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'เลือกฟีเจอร์ที่ต้องการใช้งาน',
                  style: TextStyle(
                    fontSize: 14,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight 
                        : AppTheme.darkGrey,
                    height: 1.4,
                    fontWeight: FontWeight.w400,
                  ),
                ),
                const SizedBox(height: 32),
                
                // Menu Cards Section
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.only(bottom: 16.0),
                    child: _buildResponsiveGrid(context),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildResponsiveGrid(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    
    if (width > 800) {
      // Desktop: 2x2 grid
      return GridView.count(
        crossAxisCount: 2,
        crossAxisSpacing: 20,
        mainAxisSpacing: 20,
        childAspectRatio: 2.2,
        padding: const EdgeInsets.symmetric(vertical: 8.0),
        children: _buildMenuCards(context),
      );
    } else if (width > 600) {
      // Tablet: 2x2 grid with different ratio
      return GridView.count(
        crossAxisCount: 2,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        childAspectRatio: 1.8,
        padding: const EdgeInsets.symmetric(vertical: 8.0),
        children: _buildMenuCards(context),
      );
    } else {
      // Mobile: Single column
      return ListView.separated(
        padding: const EdgeInsets.symmetric(vertical: 8.0),
        itemCount: 4,
        separatorBuilder: (context, index) => const SizedBox(height: 16),
        itemBuilder: (context, index) => _buildMenuCards(context)[index],
      );
    }
  }

  List<Widget> _buildMenuCards(BuildContext context) {
    return [
      _buildMenuCard(
        context,
        icon: Icons.radar_rounded,
        title: 'เรดาร์ฝน',
        subtitle: 'ติดตามสภาพอากาศแบบเรียลไทม์',
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.brandPrimary,
            AppTheme.darkGreen,
          ],
        ),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const RadarScreen()),
          );
        },
      ),
      _buildMenuCard(
        context,
        icon: Icons.wb_sunny_rounded,
        title: 'พยากรณ์รายวัน',
        subtitle: 'พยากรณ์อากาศ 7 วันข้างหน้า',
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.warningColor,
            AppTheme.warningColor.withOpacity(0.8),
          ],
        ),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const WeatherScreen()),
          );
        },
      ),
      _buildMenuCard(
        context,
        icon: Icons.access_time_rounded,
        title: 'พยากรณ์รายชั่วโมง',
        subtitle: 'พยากรณ์อากาศ 24 ชั่วโมงข้างหน้า',
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.secondaryGreen,
            AppTheme.brandPrimary,
          ],
        ),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const HourlyWeatherScreen()),
          );
        },
      ),
      // _buildMenuCard(
      //   context,
      //   icon: Icons.water_drop_rounded,
      //   title: 'รายงานปริมาณน้ำฝน',
      //   subtitle: 'ติดตามปริมาณน้ำฝนทั่วไป',
      //   gradient: LinearGradient(
      //     begin: Alignment.topLeft,
      //     end: Alignment.bottomRight,
      //     colors: [
      //       AppTheme.brandSecondary,
      //       AppTheme.brandSecondary.withOpacity(0.8),
      //     ],
      //   ),
      //   onTap: () {
      //     Navigator.push(
      //       context,
      //       MaterialPageRoute(builder: (context) => const RainReportScreen()),
      //     );
      //   },
      // ),
      _buildMenuCard(
        context,
        icon: Icons.language_rounded,
        title: 'รายงานปริมาณน้ำฝนผ่านเว็บ',
        subtitle: 'เปิดดูข้อมูลผ่านเว็บไซต์',
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.brandTertiary,
            AppTheme.brandTertiary.withOpacity(0.8),
          ],
        ),
        onTap: () {
          _launchWebUrl(context, 'https://landslide-alerts.com/rain_day');
        },
      ),
    ];
  }

  Widget _buildMenuCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required LinearGradient gradient,
    required VoidCallback onTap,
  }) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          gradient: isDarkMode 
              ? LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    AppTheme.darkCardSoft,
                    AppTheme.darkCard,
                  ],
                )
              : gradient,
          borderRadius: BorderRadius.circular(24),
          boxShadow: [
            BoxShadow(
              color: isDarkMode 
                  ? Colors.black.withOpacity(0.3)
                  : gradient.colors.first.withOpacity(0.3),
              blurRadius: isDarkMode ? 12 : 20,
              offset: Offset(0, isDarkMode ? 6 : 10),
              spreadRadius: 0,
            ),
          ],
          border: isDarkMode 
              ? Border.all(
                  color: AppTheme.brandPrimary.withOpacity(0.3),
                  width: 1,
                )
              : null,
        ),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: Row(
            children: [
              // Icon Section
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDarkMode 
                      ? AppTheme.brandPrimary.withOpacity(0.15)
                      : Colors.white.withOpacity(0.25),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: isDarkMode 
                        ? AppTheme.brandPrimary.withOpacity(0.3)
                        : Colors.white.withOpacity(0.3),
                    width: 1,
                  ),
                ),
                child: Icon(
                  icon,
                  color: isDarkMode ? AppTheme.brandPrimary : Colors.white,
                  size: 28,
                ),
              ),
              
              const SizedBox(width: 18),
              
              // Text Section
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        color: isDarkMode ? AppTheme.textLight : Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: isDarkMode 
                            ? AppTheme.textLight.withOpacity(0.8)
                            : Colors.white.withOpacity(0.9),
                        fontSize: 13,
                        height: 1.3,
                        fontWeight: FontWeight.w400,
                      ),
                    ),
                  ],
                ),
              ),
              
              // Arrow Icon
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: isDarkMode 
                      ? AppTheme.brandPrimary.withOpacity(0.2)
                      : Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  Icons.arrow_forward_ios_rounded,
                  color: isDarkMode ? AppTheme.brandPrimary : Colors.white,
                  size: 16,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _launchWebUrl(BuildContext context, String url) async {
    try {
      final Uri uri = Uri.parse(url);
      
      // ลองเปิดแบบ externalApplication ก่อน
      bool launched = await launchUrl(
        uri, 
        mode: LaunchMode.externalApplication,
      );
      
      // ถ้าไม่ได้ ลองแบบ platformDefault
      if (!launched) {
        launched = await launchUrl(
          uri, 
          mode: LaunchMode.platformDefault,
        );
      }
      
      // ถ้ายังไม่ได้ ลองแบบ inAppWebView
      if (!launched) {
        launched = await launchUrl(
          uri,
          mode: LaunchMode.inAppWebView,
        );
      }
      
      // ถ้ายังไม่ได้ แสดง error
      if (!launched) {
        _showErrorDialog(
          context, 
          'ไม่สามารถเปิดลิงก์ได้', 
          'ไม่พบแอปพลิเคชันที่สามารถเปิดลิงก์นี้ได้\n\nลิงก์: $url'
        );
      }
    } catch (e) {
      _showErrorDialog(
        context, 
        'เกิดข้อผิดพลาด', 
        'ไม่สามารถเปิดลิงก์ได้\n\nข้อผิดพลาด: $e\nลิงก์: $url'
      );
    }
  }

  void _showErrorDialog(BuildContext context, String title, String message) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Row(
            children: [
              Icon(
                Icons.error_outline,
                color: AppTheme.errorColor,
                size: 24,
              ),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
          content: Text(
            message,
            style: TextStyle(
              fontSize: 14,
              height: 1.4,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: Text(
                'ตกลง',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  void _showComingSoon(BuildContext context, String feature) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Row(
            children: [
              Icon(
                Icons.info_outline,
                color: AppTheme.brandPrimary,
                size: 24,
              ),
              const SizedBox(width: 8),
              const Text(
                'Coming Soon',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
          content: Text(
            'ฟีเจอร์ "$feature" กำลังพัฒนา\nจะเปิดให้ใช้งานเร็วๆ นี้',
            style: TextStyle(
              fontSize: 14,
              height: 1.4,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: Text(
                'ตกลง',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}
