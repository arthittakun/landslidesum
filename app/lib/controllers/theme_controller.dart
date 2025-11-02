import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class ThemeController extends ChangeNotifier {
  static final ThemeController _instance = ThemeController._internal();
  factory ThemeController() => _instance;
  ThemeController._internal();

  AppThemeMode _themeMode = AppThemeMode.auto;
  AppThemeMode get themeMode => _themeMode;

  // Initialize theme from saved preferences
  Future<void> initialize() async {
    _themeMode = await AppTheme.getThemeMode();
    notifyListeners();
  }

  // Change theme mode
  Future<void> setThemeMode(AppThemeMode mode) async {
    _themeMode = mode;
    await AppTheme.setThemeMode(mode);
    notifyListeners();
  }

  // Get current ThemeMode for MaterialApp
  ThemeMode get currentThemeMode {
    switch (_themeMode) {
      case AppThemeMode.light:
        return ThemeMode.light;
      case AppThemeMode.dark:
        return ThemeMode.dark;
      case AppThemeMode.auto:
        return ThemeMode.system;
    }
  }

  // Get theme mode display name
  String get themeModeDisplayName {
    switch (_themeMode) {
      case AppThemeMode.light:
        return 'สว่าง';
      case AppThemeMode.dark:
        return 'มืด';
      case AppThemeMode.auto:
        return 'อัตโนมัติ';
    }
  }

  // Get theme mode icon
  IconData get themeModeIcon {
    switch (_themeMode) {
      case AppThemeMode.light:
        return Icons.light_mode;
      case AppThemeMode.dark:
        return Icons.dark_mode;
      case AppThemeMode.auto:
        return Icons.brightness_auto;
    }
  }
}
