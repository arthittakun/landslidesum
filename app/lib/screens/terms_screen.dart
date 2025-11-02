import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import '../theme/app_theme.dart';
import '../services/terms_service.dart';
import '../models/terms_response.dart';

class TermsScreen extends StatefulWidget {
  const TermsScreen({super.key});

  @override
  State<TermsScreen> createState() => _TermsScreenState();
}

class _TermsScreenState extends State<TermsScreen> {
  TermsResponse? _termsData;
  bool _isLoading = true;
  String? _errorMessage;
  int _selectedTabIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadTerms();
  }

  Future<void> _loadTerms() async {
    try {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      final termsInfo = await TermsService.getTerms();
      
      if (mounted) {
        setState(() {
          _termsData = termsInfo;
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
      backgroundColor: isDarkMode ? AppTheme.darkBackground : AppTheme.backgroundLight,
      appBar: AppBar(
        title: const Text('เงื่อนไขและข้อตกลง'),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTerms,
            tooltip: 'รีเฟรชข้อมูล',
          ),
        ],
      ),
      body: SafeArea(
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? _buildErrorWidget()
                : _buildTermsContent(isDarkMode),
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
            onPressed: _loadTerms,
            child: const Text('ลองใหม่'),
          ),
        ],
      ),
    );
  }

  Widget _buildTermsContent(bool isDarkMode) {
    if (_termsData == null) {
      return const Center(
        child: Text('ไม่พบข้อมูลเงื่อนไขและข้อตกลง'),
      );
    }

    // เอาเวลาจาก policy และ term มาเทียบกันว่าอันไหนใหม่กว่า
    final policyUpdatedAt = _termsData!.data.policy.updatedAt;
    final termUpdatedAt = _termsData!.data.term.updatedAt;
    final latestUpdatedAt = _getLatestDateTime(policyUpdatedAt, termUpdatedAt);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Header Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
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
                  Icons.gavel,
                  size: 48,
                  color: AppTheme.brandPrimary,
                ),
                const SizedBox(height: 16),
                Text(
                  'เงื่อนไขการใช้งานและนโยบายความเป็นส่วนตัว',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                Text(
                  'อัปเดตล่าสุด: ${_formatDateTime(latestUpdatedAt)}',
                  style: TextStyle(
                    fontSize: 14,
                    color: isDarkMode 
                        ? AppTheme.textLight.withOpacity(0.7) 
                        : AppTheme.mediumGrey,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Tab Bar
          Container(
            decoration: BoxDecoration(
              color: isDarkMode ? AppTheme.darkCard : AppTheme.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: isDarkMode 
                      ? Colors.black.withOpacity(0.2)
                      : Colors.black.withOpacity(0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Expanded(
                  child: _buildTabButton(
                    'นโยบายความเป็นส่วนตัว',
                    0,
                    isDarkMode,
                  ),
                ),
                Expanded(
                  child: _buildTabButton(
                    'เงื่อนไขการใช้งาน',
                    1,
                    isDarkMode,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Content
          _buildTabContent(isDarkMode),

          const SizedBox(height: 16),

          // Accept Button - แบบเรียบง่าย
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.brandPrimary,
                foregroundColor: AppTheme.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(vertical: 16),
                elevation: 2,
              ),
              child: const Text(
                'ยอมรับเงื่อนไข',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ),

          const SizedBox(height: 16),
        ],
      ),
    );
  }

  Widget _buildTabButton(String title, int index, bool isDarkMode) {
    final isSelected = _selectedTabIndex == index;
    
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedTabIndex = index;
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16),
        decoration: BoxDecoration(
          color: isSelected 
              ? AppTheme.brandPrimary 
              : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          title,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: isSelected 
                ? AppTheme.white 
                : isDarkMode 
                    ? AppTheme.textLight.withOpacity(0.7)
                    : AppTheme.mediumGrey,
          ),
          textAlign: TextAlign.center,
        ),
      ),
    );
  }

  Widget _buildTabContent(bool isDarkMode) {
    if (_termsData == null) return const SizedBox.shrink();

    final content = _selectedTabIndex == 0 
        ? _termsData!.data.policy.policyText
        : _termsData!.data.term.policyText;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: isDarkMode 
                ? Colors.black.withOpacity(0.2)
                : Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: isDarkMode 
            ? Border.all(color: AppTheme.brandPrimary.withOpacity(0.3))
            : null,
      ),
      child: Html(
        data: content,
        style: {
          "body": Style(
            fontSize: FontSize(14),
            color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
            lineHeight: LineHeight(1.6),
          ),
          "h1": Style(
            fontSize: FontSize(20),
            fontWeight: FontWeight.bold,
            color: AppTheme.brandPrimary,
            margin: Margins.only(bottom: 16),
          ),
          "h2": Style(
            fontSize: FontSize(18),
            fontWeight: FontWeight.bold,
            color: AppTheme.brandPrimary,
            margin: Margins.only(bottom: 16),
          ),
          "h3": Style(
            fontSize: FontSize(16),
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
          "ol": Style(
            margin: Margins.only(bottom: 16, left: 20),
          ),
          "li": Style(
            margin: Margins.only(bottom: 8),
          ),
          "strong": Style(
            fontWeight: FontWeight.w600,
            color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
          ),
          "a": Style(
            color: AppTheme.brandSecondary,
            textDecoration: TextDecoration.underline,
          ),
        },
      ),
    );
  }

  String _formatDateTime(String dateTimeString) {
    try {
      final DateTime dateTime = DateTime.parse(dateTimeString);
      final months = [
        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
      ];
      
      return '${dateTime.day} ${months[dateTime.month - 1]} ${dateTime.year} ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateTimeString;
    }
  }

  // ฟังก์ชันหาวันเวลาที่ใหม่ที่สุด
  String _getLatestDateTime(String dateTime1, String dateTime2) {
    try {
      // ตรวจสอบว่าข้อมูลไม่ว่าง
      if (dateTime1.isEmpty && dateTime2.isEmpty) {
        return DateTime.now().toIso8601String();
      }
      
      if (dateTime1.isEmpty) {
        return dateTime2;
      }
      
      if (dateTime2.isEmpty) {
        return dateTime1;
      }
      
      final DateTime dt1 = DateTime.parse(dateTime1);
      final DateTime dt2 = DateTime.parse(dateTime2);
      
      // เปรียบเทียบและคืนค่าวันเวลาที่ใหม่ที่สุด
      final result = dt1.isAfter(dt2) ? dateTime1 : dateTime2;
      return result;
      
    } catch (e) {
      // หากไม่สามารถ parse ได้ ให้คืนค่าที่ไม่ว่าง
      if (dateTime1.isNotEmpty) {
        return dateTime1;
      }
      if (dateTime2.isNotEmpty) {
        return dateTime2;
      }
      
      return DateTime.now().toIso8601String();
    }
  }
}
