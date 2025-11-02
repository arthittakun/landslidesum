class ForgotPasswordResponse {
  final bool success;
  final String message;
  final ForgotPasswordData? data;
  final String? errorCode;
  final int code;

  ForgotPasswordResponse({
    required this.success,
    required this.message,
    this.data,
    this.errorCode,
    required this.code,
  });

  factory ForgotPasswordResponse.fromJson(Map<String, dynamic> json) {
    return ForgotPasswordResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? ForgotPasswordData.fromJson(json['data']) : null,
      errorCode: json['error_code'],
      code: json['code'] ?? 0,
    );
  }
}

class ForgotPasswordData {
  final String email;
  final String expiresIn;
  final int remainingAttempts;

  ForgotPasswordData({
    required this.email,
    required this.expiresIn,
    required this.remainingAttempts,
  });

  factory ForgotPasswordData.fromJson(Map<String, dynamic> json) {
    return ForgotPasswordData(
      email: json['email'] ?? '',
      expiresIn: json['expires_in'] ?? '',
      remainingAttempts: json['remaining_attempts'] ?? 0,
    );
  }
}
