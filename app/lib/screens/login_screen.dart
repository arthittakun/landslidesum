import 'package:flutter/material.dart';
import '../controllers/auth_controller.dart';
import '../theme/app_theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isPasswordVisible = false;
  bool _isLoading = false;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _handleLogin() async {
    if (_formKey.currentState!.validate()) {
      setState(() {
        _isLoading = true;
      });

      try {
        // ใช้ AuthController สำหรับ Login
        final result = await AuthController.login(
          _usernameController.text,
          _passwordController.text,
        );

        setState(() {
          _isLoading = false;
        });

        if (result.success) {
          // Login สำเร็จ - ไปหน้าหลัก
          if (mounted) {
            Navigator.of(context).pushReplacementNamed('/main');
          }
        } else {
          // Login ไม่สำเร็จ
          _showErrorDialog(result.message);
        }
      } catch (e) {
        setState(() {
          _isLoading = false;
        });
        _showErrorDialog('เกิดข้อผิดพลาดในการเข้าสู่ระบบ');
      }
    }
  }

  void _showErrorDialog(String message) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          backgroundColor: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.darkCardSoft 
              : Colors.white,
          title: Text(
            'เข้าสู่ระบบไม่สำเร็จ',
            style: TextStyle(
              color: Theme.of(context).brightness == Brightness.dark 
                  ? AppTheme.textLight 
                  : Colors.black,
            ),
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
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              child: Text(
                'ตกลง',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
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
                
                // Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'เข้าสู่ระบบ',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight 
                            : AppTheme.black87,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 32),
                
                // Logo
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: AppTheme.white,
                    borderRadius: BorderRadius.circular(50),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 15,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(50),
                    child: Image.asset(
                      'image/remove_logo.png',
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                
                // Header Text
                Text(
                  'ยินดีต้อนรับกลับมา',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight 
                        : AppTheme.black87,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'กรุณาเข้าสู่ระบบเพื่อใช้งาน',
                  style: TextStyle(
                    fontSize: 16,
                    color: Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight.withOpacity(0.7) 
                        : AppTheme.mediumGrey,
                  ),
                ),
                const SizedBox(height: 32),

                // Username Field
                Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: TextFormField(
                    controller: _usernameController,
                    decoration: InputDecoration(
                      labelText: 'ชื่อผู้ใช้หรืออีเมล',
                      hintText: 'กรอกชื่อผู้ใช้หรืออีเมล',
                      prefixIcon: Container(
                        margin: const EdgeInsets.all(12),
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.brandPrimary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(
                          Icons.person,
                          color: AppTheme.brandPrimary,
                          size: 20,
                        ),
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : AppTheme.white,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'กรุณากรอกชื่อผู้ใช้หรืออีเมล';
                      }
                      return null;
                    },
                  ),
                ),
                const SizedBox(height: 20),

                // Password Field
                Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: TextFormField(
                    controller: _passwordController,
                    obscureText: !_isPasswordVisible,
                    decoration: InputDecoration(
                      labelText: 'รหัสผ่าน',
                      hintText: 'กรุณาใส่รหัสผ่าน',
                      prefixIcon: Container(
                        margin: const EdgeInsets.all(12),
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.brandPrimary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(
                          Icons.lock,
                          color: AppTheme.brandPrimary,
                          size: 20,
                        ),
                      ),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _isPasswordVisible ? Icons.visibility : Icons.visibility_off,
                          color: AppTheme.mediumGrey,
                        ),
                        onPressed: () {
                          setState(() {
                            _isPasswordVisible = !_isPasswordVisible;
                          });
                        },
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.darkCardSoft 
                          : AppTheme.white,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'กรุณาใส่รหัสผ่าน';
                      }
                      if (value.length < 6) {
                        return 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
                      }
                      return null;
                    },
                  ),
                ),
                const SizedBox(height: 16),

                // Forgot Password
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).pushNamed('/forgot-password');
                      },
                      child: Text(
                        'ลืมรหัสผ่าน?',
                        style: TextStyle(color: AppTheme.brandPrimary),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),

                // Login Button
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
                        blurRadius: 12,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _handleLogin,
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
                                Icons.login,
                                size: 20,
                                color: AppTheme.white,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'เข้าสู่ระบบ',
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

                                 // Register Link
                 Row(
                   mainAxisAlignment: MainAxisAlignment.center,
                   children: [
                     Text(
                       'ยังไม่มีบัญชี? ',
                       style: TextStyle(
                         color: Theme.of(context).brightness == Brightness.dark 
                             ? AppTheme.textLight.withOpacity(0.7) 
                             : AppTheme.black54,
                       ),
                     ),
                     TextButton(
                       onPressed: () {
                         Navigator.of(context).pushNamed('/register');
                       },
                       child: Text(
                         'สมัครสมาชิก',
                         style: TextStyle(
                           fontWeight: FontWeight.bold,
                           color: AppTheme.brandPrimary,
                         ),
                       ),
                     ),
                   ],
                 ),
                 
                 const SizedBox(height: 32),
                 
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
                     children: [
                       Text(
                         'สนับสนุนโดย',
                         style: TextStyle(
                           fontSize: 16,
                           fontWeight: FontWeight.w600,
                           color: isDarkMode 
                               ? AppTheme.textLight.withOpacity(0.8)
                               : AppTheme.mediumGrey,
                         ),
                       ),
                       const SizedBox(height: 16),
                                               Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            // Logo 1 (รูปสำคัญที่สุด)
                            Container(
                              width: 60,
                              height: 60,
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
                                        size: 24,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                            // Logo 2
                            Container(
                              width: 60,
                              height: 60,
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
                                        size: 24,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                            // Logo 3
                            Container(
                              width: 60,
                              height: 60,
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
                                        size: 24,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                            // Logo 4
                            Container(
                              width: 60,
                              height: 60,
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
                                        size: 24,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                          ],
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
