import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../theme/app_theme.dart';
import '../services/weather_service.dart';
import '../widgets/custom_bottom_navigation_bar.dart';
import '../controllers/navigation_controller.dart';
import 'home_screen.dart';
import 'menu_screen.dart';
import 'device_status_screen.dart';
import 'notification_screen.dart';
import 'settings_screen.dart';

class WeatherScreen extends StatefulWidget {
  const WeatherScreen({super.key});

  @override
  State<WeatherScreen> createState() => _WeatherScreenState();
}

class _WeatherScreenState extends State<WeatherScreen> {
  WeatherData? _weatherData;
  bool _isLoading = true;
  String? _errorMessage;
  String _locationText = 'กำลังตรวจสอบตำแหน่ง...';
  int _currentIndex = 1; // Default to Menu tab since we're in weather from menu

  @override
  void initState() {
    super.initState();
    _loadWeatherData();
  }

  Future<void> _loadWeatherData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      // ลองใช้ location ปัจจุบัน
      Position? position = await WeatherService.getCurrentLocation();
      double lat = 13.7563; // Default Bangkok
      double lon = 100.5018;
      
      if (position != null) {
        lat = position.latitude;
        lon = position.longitude;
        _locationText = WeatherHelper.getProvinceFromCoords(lat, lon);
      } else {
        _locationText = 'กรุงเทพมหานคร (ค่าเริ่มต้น)';
      }

      final data = await WeatherService.getDailyWeather(lat, lon);
      
      setState(() {
        if (data != null) {
          _weatherData = data;
          _errorMessage = null;
        } else {
          _errorMessage = 'ไม่สามารถโหลดข้อมูลพยากรณ์อากาศได้';
        }
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'เกิดข้อผิดพลาด: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  void _onTabTapped(int index) {
    setState(() {
      _currentIndex = index;
      NavigationController.setCurrentIndex(index);
    });

    // Navigate to the appropriate screen
    switch (index) {
      case 0:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const HomeScreen()),
        );
        break;
      case 1:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const MenuScreen()),
        );
        break;
      case 2:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const DeviceStatusScreen()),
        );
        break;
      case 3:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const NotificationScreen()),
        );
        break;
      case 4:
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const SettingsScreen()),
        );
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('🌤️'),
            const SizedBox(width: 8),
            const Text(
              'พยากรณ์อากาศ 7 วัน',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 18,
              ),
            ),
          ],
        ),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 8),
            child: IconButton(
              icon: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.refresh_rounded,
                  size: 20,
                ),
              ),
              onPressed: _loadWeatherData,
              tooltip: 'รีเฟรชข้อมูล',
            ),
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
        child: _isLoading
            ? _buildLoadingState()
            : _errorMessage != null
                ? _buildErrorState()
                : _buildWeatherContent(screenWidth),
      ),
      bottomNavigationBar: CustomBottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _onTabTapped,
      ),
    );
  }

  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppTheme.brandPrimary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: CircularProgressIndicator(
              strokeWidth: 3,
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.brandPrimary),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'กำลังโหลดข้อมูลพยากรณ์อากาศ...',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w500,
              color: AppTheme.darkGrey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.cloud_off_rounded,
              size: 64,
              color: AppTheme.errorColor,
            ),
            const SizedBox(height: 16),
            Text(
              _errorMessage!,
              style: TextStyle(
                fontSize: 16,
                color: AppTheme.errorColor,
                fontWeight: FontWeight.w500,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadWeatherData,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('ลองใหม่'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.brandPrimary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildWeatherContent(double screenWidth) {
    if (_weatherData == null) return const SizedBox.shrink();

    int crossAxisCount;
    double childAspectRatio;
    double cardPadding;

    // Responsive design
    if (screenWidth > 1200) {
      // Desktop large
      crossAxisCount = 3;
      childAspectRatio = 1.0; // เพิ่มความสูง
      cardPadding = 24;
    } else if (screenWidth > 800) {
      // Desktop/Tablet
      crossAxisCount = 2;
      childAspectRatio = 0.95; // เพิ่มความสูง
      cardPadding = 20;
    } else if (screenWidth > 600) {
      // Small tablet
      crossAxisCount = 2;
      childAspectRatio = 0.85; // เพิ่มความสูง
      cardPadding = 16;
    } else {
      // Mobile
      crossAxisCount = 1;
      childAspectRatio = 1.1; // เพิ่มความสูงมากขึ้นสำหรับมือถือ
      cardPadding = 16;
    }

    return SingleChildScrollView(
      padding: EdgeInsets.all(cardPadding),
      child: Column(
        children: [
          // Location Info Card
          Container(
            width: double.infinity,
            margin: const EdgeInsets.only(bottom: 20),
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.08),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
              border: Border.all(
                color: AppTheme.brandPrimary.withOpacity(0.2),
                width: 1,
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.brandPrimary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    Icons.location_on_rounded,
                    color: AppTheme.brandPrimary,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'พยากรณ์สำหรับพื้นที่',
                        style: TextStyle(
                          color: AppTheme.darkGrey,
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _locationText,
                        style: TextStyle(
                          color: AppTheme.brandPrimary,
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Weather Cards Grid
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: crossAxisCount,
              childAspectRatio: childAspectRatio,
              mainAxisSpacing: cardPadding,
              crossAxisSpacing: cardPadding,
            ),
            itemCount: _weatherData!.daily.time.length,
            itemBuilder: (context, index) {
              return _buildWeatherCard(index, screenWidth);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildWeatherCard(int index, double screenWidth) {
    final daily = _weatherData!.daily;
    final weatherInfo = WeatherHelper.getWeatherInfo(daily.weathercode[index]);
    
    // Responsive font sizes - ปรับให้เล็กลง
    final titleFontSize = screenWidth > 600 ? 16.0 : 14.0;
    final labelFontSize = screenWidth > 600 ? 12.0 : 10.0;
    final valueFontSize = screenWidth > 600 ? 12.0 : 11.0;
    final iconSize = screenWidth > 600 ? 18.0 : 16.0;
    
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Theme.of(context).brightness == Brightness.dark 
                ? Colors.black.withOpacity(0.3)
                : Colors.black.withOpacity(0.08),
            blurRadius: 12,
            offset: const Offset(0, 4),
            spreadRadius: 0,
          ),
        ],
        border: Theme.of(context).brightness == Brightness.dark 
            ? Border.all(
                color: AppTheme.brandPrimary.withOpacity(0.2),
                width: 1,
              )
            : null,
      ),
      child: Column(
        children: [
          // Header with weather type indicator
          Container(
            width: double.infinity,
            padding: EdgeInsets.all(screenWidth > 600 ? 20 : 16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: weatherInfo['gradient'] ?? [
                  AppTheme.brandPrimary.withOpacity(0.1),
                  AppTheme.secondaryGreen.withOpacity(0.05),
                ],
                begin: Alignment.centerLeft,
                end: Alignment.centerRight,
              ),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(20),
                topRight: Radius.circular(20),
              ),
            ),
            child: Column(
              children: [
                // Date
                Text(
                  WeatherHelper.formatDate(daily.time[index]),
                  style: TextStyle(
                    fontSize: titleFontSize,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.brandPrimary,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                
                // Weather condition badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.darkCard.withOpacity(0.9)
                        : Colors.white.withOpacity(0.9),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: AppTheme.brandPrimary.withOpacity(0.2),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        weatherInfo['icon'] ?? '☁️',
                        style: TextStyle(fontSize: iconSize + 4),
                      ),
                      const SizedBox(width: 8),
                      Flexible(
                        child: Text(
                          weatherInfo['desc'] ?? 'ไม่ทราบ',
                          style: TextStyle(
                            fontSize: labelFontSize,
                            fontWeight: FontWeight.w500,
                            color: AppTheme.brandPrimary,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Weather Details
          Flexible(
            child: Padding(
              padding: EdgeInsets.all(screenWidth > 600 ? 16 : 12), // ลดพื้นที่ padding
              child: Column(
                mainAxisSize: MainAxisSize.min, // ให้ Column ใช้พื้นที่เท่าที่จำเป็น
                children: [
                  // Temperature Row
                  Flexible(
                    child: Row(
                      children: [
                        Expanded(
                          child: _buildWeatherItem(
                            '🌡️',
                            'อุณหภูมิสูงสุด',
                            '${daily.temperatureMax[index].toStringAsFixed(1)}°C',
                            Colors.orange,
                            iconSize,
                            labelFontSize,
                            valueFontSize,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildWeatherItem(
                            '❄️',
                            'อุณหภูมิต่ำสุด',
                            '${daily.temperatureMin[index].toStringAsFixed(1)}°C',
                            Colors.blue,
                            iconSize,
                            labelFontSize,
                            valueFontSize,
                          ),
                        ),
                      ],
                    ),
                  ),
                  
                  const SizedBox(height: 6), // ลดระยะห่าง
                  
                  // Rain & UV Row
                  Flexible(
                    child: Row(
                      children: [
                        Expanded(
                          child: _buildWeatherItem(
                            '🌧️',
                            'ปริมาณฝน',
                            '${daily.precipitationSum[index].toStringAsFixed(1)} มม.',
                            Colors.blue.shade600,
                            iconSize,
                            labelFontSize,
                            valueFontSize,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildWeatherItem(
                            '☀️',
                            'ดัชนี UV',
                            daily.uvIndexMax[index].toStringAsFixed(1),
                            Colors.yellow.shade700,
                            iconSize,
                            labelFontSize,
                            valueFontSize,
                          ),
                        ),
                      ],
                    ),
                  ),
                  
                  const SizedBox(height: 6), // ลดระยะห่าง
                  
                  // Wind & Sunrise Row
                  Flexible(
                    child: Row(
                      children: [
                        Expanded(
                          child: _buildWeatherItem(
                            '💨',
                            'ความเร็วลม',
                            '${daily.windspeedMax[index].toStringAsFixed(1)} กม./ชม.',
                            Colors.cyan,
                            iconSize,
                            labelFontSize,
                            valueFontSize,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildWeatherItem(
                            '🌅',
                            'พระอาทิตย์ขึ้น',
                            WeatherHelper.formatTime(daily.sunrise[index]),
                            Colors.orange.shade600,
                            iconSize,
                            labelFontSize,
                            valueFontSize,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWeatherItem(String icon, String label, String value, Color color, 
      double iconSize, double labelFontSize, double valueFontSize) {
    return Container(
      padding: EdgeInsets.all(iconSize > 18 ? 8 : 6), // ลด padding
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: color.withOpacity(0.2),
          width: 1,
        ),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            icon,
            style: TextStyle(fontSize: iconSize - 2), // ลดขนาด icon เล็กน้อย
          ),
          SizedBox(height: iconSize > 18 ? 4 : 2), // ลดระยะห่าง
          Flexible(
            child: Text(
              label,
              style: TextStyle(
                fontSize: labelFontSize - 1, // ลดขนาดฟอนต์
                color: color,
                fontWeight: FontWeight.w500,
              ),
              textAlign: TextAlign.center,
              maxLines: 1, // จำกัดเป็น 1 บรรทัด
              overflow: TextOverflow.ellipsis,
            ),
          ),
          SizedBox(height: iconSize > 18 ? 2 : 1), // ลดระยะห่าง
          Flexible(
            child: Text(
              value,
              style: TextStyle(
                fontSize: valueFontSize - 1, // ลดขนาดฟอนต์
                fontWeight: FontWeight.w600,
                color: color,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}
