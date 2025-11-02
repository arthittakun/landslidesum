import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import '../theme/app_theme.dart';
import '../services/about_service.dart';
import '../models/about_response.dart';
import '../widgets/custom_bottom_navigation_bar.dart';
import '../controllers/navigation_controller.dart';

class AboutScreen extends StatefulWidget {
  const AboutScreen({super.key});

  @override
  State<AboutScreen> createState() => _AboutScreenState();
}

class _AboutScreenState extends State<AboutScreen> {
  AboutResponse? _aboutData;
  bool _isLoading = true;
  String? _errorMessage;
  int _currentIndex = 4; // Index ของ Settings tab

  @override
  void initState() {
    super.initState();
    _loadAboutInfo();
  }

  void _onTabTapped(int index) {
    setState(() {
      _currentIndex = index;
      NavigationController.setCurrentIndex(index);
    });
    
    // Navigate to the selected tab
    switch (index) {
      case 0:
        Navigator.of(context).pushReplacementNamed('/main');
        break;
      case 1:
        Navigator.of(context).pushReplacementNamed('/main');
        break;
      case 2:
        Navigator.of(context).pushReplacementNamed('/main');
        break;
      case 3:
        Navigator.of(context).pushReplacementNamed('/main');
        break;
      case 4:
        // Already on settings/about page
        break;
    }
  }

  Future<void> _loadAboutInfo() async {
    try {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      final aboutInfo = await AboutService.getAboutInfo();
      
      if (mounted) {
        setState(() {
          _aboutData = aboutInfo;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('เกี่ยวกับ'),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAboutInfo,
            tooltip: 'รีเฟรชข้อมูล',
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: isDarkMode
                ? [AppTheme.darkBackground, AppTheme.darkSurface]
                : [AppTheme.brandPrimary.withOpacity(0.05), Colors.white],
          ),
        ),
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? _buildErrorWidget()
                : _buildAboutContent(),
      ),
      bottomNavigationBar: CustomBottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _onTabTapped,
      ),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
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
            _errorMessage!,
            style: TextStyle(
              fontSize: 16,
              color: AppTheme.errorColor,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadAboutInfo,
            child: const Text('ลองใหม่'),
          ),
        ],
      ),
    );
  }

  Widget _buildAboutContent() {
    if (_aboutData?.data == null) {
      return const Center(
        child: Text('ไม่พบข้อมูลเกี่ยวกับ'),
      );
    }

    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    final aboutData = _aboutData!.data!;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: isDarkMode 
                      ? Colors.black.withOpacity(0.3)
                      : Colors.black.withOpacity(0.1),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
              border: isDarkMode 
                  ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
                  : null,
            ),
            child: Column(
              children: [
                Icon(
                  Icons.info_outline,
                  size: 64,
                  color: AppTheme.brandPrimary,
                ),
                const SizedBox(height: 16),
                Text(
                  'เกี่ยวกับเรา',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.brandPrimary,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Landslide Alerts',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: isDarkMode ? AppTheme.textLight : AppTheme.darkGrey,
                  ),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 20),
          
          // Sponsors Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: isDarkMode 
                      ? Colors.black.withOpacity(0.3)
                      : Colors.black.withOpacity(0.1),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
              border: isDarkMode 
                  ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
                  : null,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                                         Icon(
                       Icons.favorite,
                       color: AppTheme.brandPrimary,
                       size: 24,
                     ),
                    const SizedBox(width: 8),
                    Text(
                      'ผู้สนับสนุนการสร้างแอป',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.brandPrimary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                                 LayoutBuilder(
                   builder: (context, constraints) {
                     final screenWidth = constraints.maxWidth;
                     final isSmallScreen = screenWidth < 600;
                     final logoSize = isSmallScreen ? 100.0 : 120.0;
                     final spacing = isSmallScreen ? 20.0 : 24.0;
                    
                    return Column(
                      children: [
                        // แถวที่ 1: รูปสำคัญที่สุด + รูปที่ 2
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            // Logo 1 (รูปสำคัญที่สุด)
                            Container(
                              width: logoSize,
                              height: logoSize,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.1),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.asset(
                                  'image/537185280_2246831462448838_2306324316680305938_n.png',
                                  fit: BoxFit.contain,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      decoration: BoxDecoration(
                                        color: AppTheme.lightGrey,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(
                                        Icons.image_not_supported,
                                        color: AppTheme.mediumGrey,
                                        size: logoSize * 0.4,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                            // Logo 2
                            Container(
                              width: logoSize,
                              height: logoSize,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.1),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.asset(
                                  'image/969f841c-ae82-43dd-a25a-d86c501a5c47.png',
                                  fit: BoxFit.contain,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      decoration: BoxDecoration(
                                        color: AppTheme.lightGrey,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(
                                        Icons.image_not_supported,
                                        color: AppTheme.mediumGrey,
                                        size: logoSize * 0.4,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                          ],
                        ),
                        
                        SizedBox(height: spacing),
                        
                        // แถวที่ 2: รูปที่ 3 + รูปที่ 4
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            // Logo 3
                            Container(
                              width: logoSize,
                              height: logoSize,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.1),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.asset(
                                  'image/9537e178-2822-4285-b5ba-a5edff03a584.png',
                                  fit: BoxFit.contain,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      decoration: BoxDecoration(
                                        color: AppTheme.lightGrey,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(
                                        Icons.image_not_supported,
                                        color: AppTheme.mediumGrey,
                                        size: logoSize * 0.4,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                            // Logo 4
                            Container(
                              width: logoSize,
                              height: logoSize,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.1),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.asset(
                                  'image/fcd51af6-54b1-4f1f-9051-a788cc4620d1.png',
                                  fit: BoxFit.contain,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      decoration: BoxDecoration(
                                        color: AppTheme.lightGrey,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(
                                        Icons.image_not_supported,
                                        color: AppTheme.mediumGrey,
                                        size: logoSize * 0.4,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 20),
          
          // Content Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: isDarkMode ? AppTheme.darkCardSoft : Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: isDarkMode 
                      ? Colors.black.withOpacity(0.3)
                      : Colors.black.withOpacity(0.1),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
              border: isDarkMode 
                  ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
                  : null,
            ),
            child: Html(
              data: aboutData.policyText,
              style: {
                "body": Style(
                  fontSize: FontSize(16),
                  color: isDarkMode ? AppTheme.textLight : AppTheme.darkGrey,
                  lineHeight: LineHeight(1.6),
                ),
                "h2": Style(
                  fontSize: FontSize(20),
                  fontWeight: FontWeight.bold,
                  color: AppTheme.brandPrimary,
                  margin: Margins.only(bottom: 16),
                ),
                "h3": Style(
                  fontSize: FontSize(18),
                  fontWeight: FontWeight.w600,
                  color: AppTheme.brandSecondary,
                  margin: Margins.only(top: 20, bottom: 12),
                ),
                "p": Style(
                  margin: Margins.only(bottom: 12),
                ),
                "ul": Style(
                  margin: Margins.only(bottom: 16, left: 20),
                ),
                "li": Style(
                  margin: Margins.only(bottom: 8),
                ),
                "strong": Style(
                  fontWeight: FontWeight.w600,
                  color: isDarkMode ? AppTheme.textLight : AppTheme.darkGrey,
                ),
              },
            ),
          ),
          
          const SizedBox(height: 20),
          
          // Footer Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: isDarkMode ? AppTheme.darkCard : AppTheme.brandPrimary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isDarkMode 
                    ? AppTheme.brandPrimary.withOpacity(0.3)
                    : AppTheme.brandPrimary.withOpacity(0.3),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.security,
                      color: AppTheme.brandPrimary,
                      size: 24,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'ระบบจัดการและติดตามอุปกรณ์เตือนภัยดินถล่ม',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: isDarkMode ? AppTheme.textLight : AppTheme.brandPrimary,
                        ),
                      ),
                    ),
                  ],
                ),
                                 const SizedBox(height: 16),
              ],
            ),
          ),
        ],
      ),
    );
  }


}
