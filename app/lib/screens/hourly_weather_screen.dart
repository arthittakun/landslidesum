import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../theme/app_theme.dart';
import '../services/hourly_weather_service.dart';
import '../widgets/custom_bottom_navigation_bar.dart';
import '../controllers/navigation_controller.dart';
import 'home_screen.dart';
import 'menu_screen.dart';
import 'device_status_screen.dart';
import 'notification_screen.dart';
import 'settings_screen.dart';

class HourlyWeatherScreen extends StatefulWidget {
  const HourlyWeatherScreen({super.key});

  @override
  State<HourlyWeatherScreen> createState() => _HourlyWeatherScreenState();
}

class _HourlyWeatherScreenState extends State<HourlyWeatherScreen> {
  HourlyWeatherData? _weatherData;
  bool _isLoading = true;
  String? _errorMessage;
  String _locationText = 'กำลังตรวจสอบตำแหน่ง...';
  int _currentIndex = 1; // Default to Menu tab since we're in hourly weather from menu

  @override
  void initState() {
    super.initState();
    _loadHourlyWeatherData();
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

  Future<void> _loadHourlyWeatherData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      // ลองใช้ location ปัจจุบัน
      Position? position = await HourlyWeatherService.getCurrentLocation();
      double lat = 13.7563; // Default Bangkok
      double lon = 100.5018;
      
      if (position != null) {
        lat = position.latitude;
        lon = position.longitude;
        _locationText = HourlyWeatherHelper.getProvinceFromCoords(lat, lon);
      } else {
        _locationText = 'กรุงเทพมหานคร (ค่าเริ่มต้น)';
      }

      final data = await HourlyWeatherService.getHourlyWeather(lat, lon);
      
      setState(() {
        if (data != null) {
          _weatherData = data;
          _errorMessage = null;
        } else {
          _errorMessage = 'ไม่สามารถโหลดข้อมูลได้';
        }
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'เกิดข้อผิดพลาด: $e';
        _isLoading = false;
      });
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
            const Text('🌦️'),
            const SizedBox(width: 8),
            const Text(
              'พยากรณ์อากาศรายชั่วโมง',
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
              onPressed: _loadHourlyWeatherData,
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
        child: SafeArea(
        child: _isLoading
            ? _buildLoadingState()
            : _errorMessage != null
                ? _buildErrorState()
                : _buildHourlyWeatherContent(screenWidth),
        ),
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
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight
                  : AppTheme.darkGrey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 64,
            color: AppTheme.errorRed,
          ),
          const SizedBox(height: 16),
          Text(
            _errorMessage!,
            style: TextStyle(
              fontSize: 16,
              color: AppTheme.errorRed,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadHourlyWeatherData,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.brandPrimary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('ลองใหม่'),
          ),
        ],
      ),
    );
  }

  Widget _buildHourlyWeatherContent(double screenWidth) {
    if (_weatherData == null) return Container();

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Status Section
          _buildStatusSection(),
          const SizedBox(height: 16),
          
          // Current Weather Section
          _buildCurrentWeatherSection(),
          const SizedBox(height: 24),
          
          // Section Title
          _buildSectionTitle(),
          const SizedBox(height: 12),
          
          // Hourly Cards
          _buildHourlyCards(),
          const SizedBox(height: 16),
          
          // Scroll Indicator
          _buildScrollIndicator(),
        ],
      ),
    );
  }

  Widget _buildStatusSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
      decoration: BoxDecoration(
        color: AppTheme.brandPrimary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: AppTheme.brandPrimary.withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Icon(
            Icons.location_on,
            color: AppTheme.brandPrimary,
            size: 20,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'พยากรณ์สำหรับพื้นที่ $_locationText',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Theme.of(context).brightness == Brightness.dark 
                    ? AppTheme.textLight
                    : AppTheme.darkGrey,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCurrentWeatherSection() {
    final currentWeather = _weatherData!.currentWeather;
    if (currentWeather == null) return Container();

    final weather = HourlyWeatherHelper.getWeatherInfo(currentWeather.weathercode);
    final windDirection = HourlyWeatherHelper.getWindDirection(currentWeather.winddirection);

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: Theme.of(context).brightness == Brightness.dark
              ? [
                  AppTheme.darkCardSoft.withOpacity(0.8),
                  AppTheme.darkCardSoft.withOpacity(0.6),
                ]
              : [
                  AppTheme.brandPrimary.withOpacity(0.1),
                  AppTheme.brandPrimary.withOpacity(0.05),
                ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.brandPrimary.withOpacity(0.3)
              : AppTheme.brandPrimary.withOpacity(0.2),
          width: 1,
        ),
      ),
      child: Stack(
        children: [
          // Gradient top border
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: Container(
              height: 3,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [AppTheme.brandPrimary, Colors.blue.shade400],
                ),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(16),
                  topRight: Radius.circular(16),
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                // Main weather info
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _locationText,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.darkGrey,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            '${currentWeather.temperature.toStringAsFixed(1)}°',
                            style: TextStyle(
                              fontSize: 40,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.brandPrimary,
                              height: 1,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            weather.desc,
                            style: TextStyle(
                              fontSize: 14,
                              color: AppTheme.mediumGrey,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Column(
                      children: [
                        Icon(
                          weather.iconData,
                          size: 50,
                          color: AppTheme.brandPrimary,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _formatTime(currentWeather.time),
                          style: TextStyle(
                            fontSize: 12,
                            color: AppTheme.mediumGrey,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                
                const SizedBox(height: 20),
                
                // Weather details grid
                Container(
                  padding: const EdgeInsets.only(top: 16),
                  decoration: BoxDecoration(
                    border: Border(
                      top: BorderSide(
                        color: AppTheme.lightGrey,
                        width: 1,
                      ),
                    ),
                  ),
                  child: GridView.count(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisCount: 2,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: 2.5,
                    children: [
                      _buildCurrentWeatherDetailItem(
                        icon: Icons.air,
                        label: 'ลม',
                        value: '${currentWeather.windspeed.toStringAsFixed(1)} km/h',
                      ),
                      _buildCurrentWeatherDetailItem(
                        icon: Icons.explore,
                        label: 'ทิศทาง',
                        value: windDirection,
                      ),
                      _buildCurrentWeatherDetailItem(
                        icon: currentWeather.isDay == 1 ? Icons.wb_sunny : Icons.nightlight_round,
                        label: 'เวลา',
                        value: currentWeather.isDay == 1 ? 'วัน' : 'คืน',
                      ),
                      _buildCurrentWeatherDetailItem(
                        icon: Icons.thermostat,
                        label: 'รู้สึก',
                        value: '${currentWeather.temperature.toStringAsFixed(1)}°C',
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCurrentWeatherDetailItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft.withOpacity(0.5)
            : Colors.white.withOpacity(0.7),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.brandPrimary.withOpacity(0.3)
              : AppTheme.lightGrey,
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            size: 18,
            color: AppTheme.brandPrimary,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 10,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight.withOpacity(0.7)
                        : AppTheme.mediumGrey,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight
                        : AppTheme.darkGrey,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
      decoration: BoxDecoration(
        color: AppTheme.brandPrimary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border(
          left: BorderSide(
            color: AppTheme.brandPrimary,
            width: 3,
          ),
        ),
      ),
      child: Row(
        children: [
          Icon(
            Icons.access_time,
            color: AppTheme.brandPrimary,
            size: 20,
          ),
          const SizedBox(width: 8),
          Text(
            '24 ชั่วโมงข้างหน้า',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight
                  : AppTheme.darkGrey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHourlyCards() {
    final hourlyData = _weatherData!.hourly;
    if (hourlyData == null || hourlyData.time.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(32),
        child: Center(
          child: Text(
            'ไม่พบข้อมูลพยากรณ์อากาศ',
            style: TextStyle(
              fontSize: 16,
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight
                  : AppTheme.mediumGrey,
            ),
          ),
        ),
      );
    }

    return SizedBox(
      height: 200,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: 24 > hourlyData.time.length ? hourlyData.time.length : 24,
        itemBuilder: (context, index) {
          return _buildHourlyCard(index, hourlyData);
        },
      ),
    );
  }

  Widget _buildHourlyCard(int index, HourlyData hourlyData) {
    final time = DateTime.parse(hourlyData.time[index]);
    final weather = HourlyWeatherHelper.getWeatherInfo(hourlyData.weathercode[index]);
    
    return Container(
      width: 110,
      margin: const EdgeInsets.only(right: 12),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Theme.of(context).brightness == Brightness.dark 
                ? Colors.black.withOpacity(0.3)
                : Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: Border.all(
          color: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.brandPrimary.withOpacity(0.2)
              : AppTheme.lightGrey,
          width: 1,
        ),
      ),
      child: Stack(
        children: [
          // Top gradient border
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: Container(
              height: 3,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: _getWeatherGradient(hourlyData.weathercode[index]),
                ),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(12),
                  topRight: Radius.circular(12),
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                // Time
                Text(
                  _formatTime(time.toIso8601String()),
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight
                        : AppTheme.mediumGrey,
                  ),
                ),
                
                const SizedBox(height: 12),
                
                // Weather icon
                Icon(
                  weather.iconData,
                  size: 32,
                  color: AppTheme.brandPrimary,
                ),
                
                const SizedBox(height: 12),
                
                // Temperature
                Text(
                  '${hourlyData.temperature2m[index].toStringAsFixed(1)}°',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.brandPrimary,
                  ),
                ),
                
                const SizedBox(height: 8),
                
                // Details
                Expanded(
                  child: Column(
                    children: [
                      _buildHourlyDetailMini(Icons.water_drop, '${hourlyData.precipitation[index].toStringAsFixed(1)}mm'),
                      const SizedBox(height: 4),
                      _buildHourlyDetailMini(Icons.air, '${hourlyData.windspeed10m[index].toStringAsFixed(1)}'),
                      const SizedBox(height: 4),
                      _buildHourlyDetailMini(Icons.cloud, '${hourlyData.cloudcover[index]}%'),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHourlyDetailMini(IconData icon, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(
          icon,
          size: 10,
          color: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.textLight.withOpacity(0.7)
              : AppTheme.mediumGrey,
        ),
        const SizedBox(width: 4),
        Flexible(
          child: Text(
            value,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight
                  : AppTheme.darkGrey,
            ),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _buildScrollIndicator() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(
          Icons.swipe_left,
          color: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.textLight.withOpacity(0.7)
              : AppTheme.mediumGrey,
          size: 16,
        ),
        const SizedBox(width: 8),
        Text(
          'เลื่อนเพื่อดูเพิ่มเติม',
          style: TextStyle(
            fontSize: 12,
            color: Theme.of(context).brightness == Brightness.dark 
                ? AppTheme.textLight.withOpacity(0.7)
                : AppTheme.mediumGrey,
          ),
        ),
      ],
    );
  }

  List<Color> _getWeatherGradient(int weatherCode) {
    switch (weatherCode) {
      case 0:
      case 1:
        return [Colors.orange.shade400, Colors.yellow.shade400];
      case 2:
      case 3:
        return [Colors.blueGrey.shade500, Colors.blueGrey.shade400];
      case 45:
      case 48:
        return [Colors.blueGrey.shade400, Colors.blueGrey.shade300];
      case 51:
      case 53:
      case 55:
      case 61:
      case 63:
      case 65:
      case 80:
      case 81:
      case 82:
        return [Colors.blue.shade500, Colors.blue.shade300];
      case 95:
      case 96:
      case 99:
        return [Colors.purple.shade500, Colors.purple.shade300];
      default:
        return [AppTheme.brandPrimary, Colors.blue.shade400];
    }
  }

  String _formatTime(String isoString) {
    final time = DateTime.parse(isoString);
    return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
  }
}
