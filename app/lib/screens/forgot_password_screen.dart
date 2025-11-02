import 'package:flutter/material.dart';
import '../services/forgot_password_service.dart';
import '../models/forgot_password_response.dart';
import '../theme/app_theme.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  void _handleForgotPassword() async {
    if (_formKey.currentState!.validate()) {
      setState(() {
        _isLoading = true;
      });

      try {
        final result = await ForgotPasswordService.requestPasswordReset(
          _emailController.text.trim(),
        );

        setState(() {
          _isLoading = false;
        });

        if (result.success) {
          _showSuccessDialog(result);
        } else {
          _showErrorDialog(result.message, result.errorCode);
        }
      } catch (e) {
        setState(() {
          _isLoading = false;
        });
        _showErrorDialog('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'UNKNOWN_ERROR');
      }
    }
  }

  void _showSuccessDialog(ForgotPasswordResponse result) {
    final data = result.data;
    final remainingAttempts = data?.remainingAttempts ?? 0;
    final expiresIn = data?.expiresIn ?? '20 นาที';
    
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Row(
            children: [
              Icon(
                Icons.check_circle,
                color: AppTheme.brandPrimary,
                size: 28,
              ),
              const SizedBox(width: 8),
              const Text('ส่งอีเมลสำเร็จ'),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(result.message),
              const SizedBox(height: 16),
              if (data != null) ...[
                Text('• อีเมล: ${data.email}'),
                Text('• ลิงก์มีอายุ: $expiresIn'),
                Text('• เหลือโอกาสขอรีเซ็ต: $remainingAttempts ครั้ง'),
              ],
              const SizedBox(height: 8),
              const Text(
                'กรุณาตรวจสอบอีเมลของคุณและคลิกลิงก์เพื่อรีเซ็ตรหัสผ่าน',
                style: TextStyle(fontWeight: FontWeight.w500),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(); // Close dialog
                Navigator.of(context).pop(); // Return to login
              },
              child: Text(
                'ตกลง',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  void _showErrorDialog(String message, String? errorCode) {
    String title = 'เกิดข้อผิดพลาด';
    IconData icon = Icons.error;
    Color iconColor = AppTheme.errorColor;
    
    // ปรับ title และ icon ตาม error code
    if (errorCode == 'USER_NOT_FOUND') {
      title = 'ไม่พบผู้ใช้';
      icon = Icons.person_off;
      iconColor = AppTheme.warningColor;
    } else if (errorCode == 'RATE_LIMIT_EXCEEDED') {
      title = 'ขอรหัสผ่านบ่อยเกินไป';
      icon = Icons.timer_off;
      iconColor = AppTheme.warningColor;
    }
    
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Row(
            children: [
              Icon(
                icon,
                color: iconColor,
                size: 28,
              ),
              const SizedBox(width: 8),
              Text(title),
            ],
          ),
          content: Text(message),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              child: Text(
                'ตกลง',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    
    return Scaffold(
      backgroundColor: isDarkMode ? AppTheme.darkBackground : AppTheme.backgroundLight,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                const SizedBox(height: 16),
                
                // Back Button และ Title
                Row(
                  children: [
                    GestureDetector(
                      onTap: () => Navigator.of(context).pop(),
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: isDarkMode 
                                  ? Colors.black.withOpacity(0.3)
                                  : Colors.black.withOpacity(0.05),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Icon(
                          Icons.arrow_back_ios,
                          color: AppTheme.brandPrimary,
                          size: 20,
                        ),
                      ),
                    ),
                    Expanded(
                      child: Center(
                        child: Text(
                          'ลืมรหัสผ่าน',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 44), // เพื่อให้ title อยู่กึ่งกลาง
                  ],
                ),
                const SizedBox(height: 32),
                
                // Icon
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
                    borderRadius: BorderRadius.circular(50),
                    boxShadow: [
                      BoxShadow(
                        color: isDarkMode 
                            ? Colors.black.withOpacity(0.3)
                            : Colors.black.withOpacity(0.1),
                        blurRadius: 15,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Icon(
                    Icons.lock_reset,
                    size: 50,
                    color: AppTheme.brandPrimary,
                  ),
                ),
                const SizedBox(height: 24),
                
                // Header Text
                Text(
                  'รีเซ็ตรหัสผ่าน',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'กรอกอีเมลของคุณเพื่อรับลิงก์รีเซ็ตรหัสผ่าน',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 16,
                    color: isDarkMode ? AppTheme.textLight.withOpacity(0.8) : AppTheme.mediumGrey,
                  ),
                ),
                const SizedBox(height: 32),

                // Email Field
                Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: isDarkMode 
                            ? Colors.black.withOpacity(0.3)
                            : Colors.black.withOpacity(0.05),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: 'อีเมล',
                      hintText: 'กรุณาใส่อีเมลของคุณ',
                      prefixIcon: Container(
                        margin: const EdgeInsets.all(12),
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.brandPrimary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(
                          Icons.email_outlined,
                          color: AppTheme.brandPrimary,
                          size: 20,
                        ),
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                      labelStyle: TextStyle(
                        color: isDarkMode ? AppTheme.textLight.withOpacity(0.7) : AppTheme.darkGrey,
                      ),
                      hintStyle: TextStyle(
                        color: isDarkMode ? AppTheme.textLight.withOpacity(0.5) : AppTheme.mediumGrey,
                      ),
                    ),
                    style: TextStyle(
                      color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'กรุณาใส่อีเมล';
                      }
                      if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
                        return 'รูปแบบอีเมลไม่ถูกต้อง';
                      }
                      return null;
                    },
                  ),
                ),
                const SizedBox(height: 32),

                // Information Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDarkMode 
                          ? AppTheme.brandPrimary.withOpacity(0.3)
                          : AppTheme.brandPrimary.withOpacity(0.2),
                      width: 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: isDarkMode 
                            ? Colors.black.withOpacity(0.3)
                            : Colors.black.withOpacity(0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppTheme.brandPrimary.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              Icons.info_outline,
                              color: AppTheme.brandPrimary,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              'ข้อมูลการรีเซ็ตรหัสผ่าน',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: isDarkMode ? AppTheme.textLight : AppTheme.black87,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        '• ลิงก์รีเซ็ตรหัสผ่านจะมีอายุ 20 นาที\n'
                        '• สามารถขอรีเซ็ตได้สูงสุด 5 ครั้งต่อ 12 ชั่วโมง\n'
                        '• หากไม่ได้รับอีเมล กรุณาตรวจสอบโฟลเดอร์ Spam',
                        style: TextStyle(
                          fontSize: 14,
                          color: isDarkMode ? AppTheme.textLight.withOpacity(0.7) : AppTheme.black54,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 32),

                // Send Email Button
                Container(
                  width: double.infinity,
                  height: 56,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    gradient: LinearGradient(
                      colors: [
                        AppTheme.brandPrimary,
                        AppTheme.brandPrimary.withOpacity(0.8),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppTheme.brandPrimary.withOpacity(0.3),
                        blurRadius: 15,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _handleForgotPassword,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.transparent,
                      foregroundColor: AppTheme.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 0,
                      shadowColor: Colors.transparent,
                    ),
                    child: _isLoading
                        ? const SizedBox(
                            width: 24,
                            height: 24,
                            child: CircularProgressIndicator(
                              color: AppTheme.white,
                              strokeWidth: 3,
                            ),
                          )
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.send_rounded,
                                size: 20,
                                color: AppTheme.white,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'ส่งลิงก์รีเซ็ตรหัสผ่าน',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
                const SizedBox(height: 24),

                // Back to Login Link
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: isDarkMode ? AppTheme.darkCardSoft : AppTheme.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: isDarkMode 
                            ? Colors.black.withOpacity(0.3)
                            : Colors.black.withOpacity(0.05),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.arrow_back,
                        color: isDarkMode ? AppTheme.textLight.withOpacity(0.7) : AppTheme.mediumGrey,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'จำรหัสผ่านได้แล้ว? ',
                        style: TextStyle(
                          color: isDarkMode ? AppTheme.textLight.withOpacity(0.7) : AppTheme.black54,
                          fontSize: 16,
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          Navigator.of(context).pop();
                        },
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 8),
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: Text(
                          'เข้าสู่ระบบ',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: AppTheme.brandPrimary,
                            fontSize: 16,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
