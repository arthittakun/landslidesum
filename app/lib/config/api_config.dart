class ApiConfig {
  // Base URL สำหรับ API
  static const String baseUrl = 'https://landslide-alerts.com';
  // static const String baseUrl = 'https://trash.docdag.com';
  
  // API Endpoints
  static const String apiVersion = '/api';
  static const String applicationPath = '$apiVersion/application';
  static const String authPath = '$apiVersion/auth';
  
  // Complete URLs
  static const String applicationBaseUrl = '$baseUrl$applicationPath';
  static const String authBaseUrl = '$baseUrl$authPath';
  
  // Timeout Duration
  static const Duration timeoutDuration = Duration(seconds: 30);
  
  // Headers
  static const Map<String, String> defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
  
  // Helper method to get headers with authorization
  static Map<String, String> getAuthHeaders(String token) {
    return {
      ...defaultHeaders,
      'Authorization': 'Bearer $token',
    };
  }
  
  // Specific endpoints
  static const String loginEndpoint = '$applicationBaseUrl/login';
  static const String registerEndpoint = '$applicationBaseUrl/register';
  static const String verifyEndpoint = '$applicationBaseUrl/verify';
  static const String refreshEndpoint = '$applicationBaseUrl/refresh';
  static const String logoutEndpoint = '$applicationBaseUrl/logout';
  static const String deviceStatusEndpoint = '$applicationBaseUrl/status_device';
  static const String environmentStateEndpoint = '$applicationBaseUrl/environment_state';
  static const String locationStatsEndpoint = '$applicationBaseUrl/location_stats';
  static const String deviceByLocationEndpoint = '$applicationBaseUrl/deviceBylocation';
  static const String galleryEndpoint = '$applicationBaseUrl/getgallery';
  static const String rainReportEndpoint = '$baseUrl/api/rain/day';
  static const String notificationCheckEndpoint = '$baseUrl/api/notification/notifyforapp';
  static const String weatherDayEndpoint = '$baseUrl/api/weather/weather-day';
  static const String weatherHourEndpoint = '$baseUrl/api/weather/weather-hour';
  static const String aboutEndpoint = '$baseUrl/api/about';
  static const String forgotPasswordEndpoint = '$baseUrl/api/forgot/forgot-password';
  static const String termsEndpoint = '$baseUrl/api/policy/term';
  static const String verifyResetTokenEndpoint = '$authBaseUrl/verify-reset-token';
  static const String resetPasswordEndpoint = '$authBaseUrl/reset-password';
}
