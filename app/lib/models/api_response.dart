class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final int code;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    required this.code,
  });

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Map<String, dynamic> json)? fromJsonT,
  ) {
    return ApiResponse<T>(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null && fromJsonT != null
          ? fromJsonT(json['data'])
          : null,
      code: json['code'] ?? 0,
    );
  }
}

class LoginData {
  final UserInfo user;
  final String accessToken;
  final String refreshToken;
  final int expiresIn;

  LoginData({
    required this.user,
    required this.accessToken,
    required this.refreshToken,
    required this.expiresIn,
  });

  factory LoginData.fromJson(Map<String, dynamic> json) {
    return LoginData(
      user: UserInfo.fromJson(json['user']),
      accessToken: json['access_token'] ?? '',
      refreshToken: json['refresh_token'] ?? '',
      expiresIn: json['expires_in'] ?? 0,
    );
  }
}

class UserInfo {
  final String username;
  final String email;

  UserInfo({
    required this.username,
    required this.email,
  });

  factory UserInfo.fromJson(Map<String, dynamic> json) {
    return UserInfo(
      username: json['username'] ?? '',
      email: json['email'] ?? '',
    );
  }
}

class RegisterData {
  final String username;
  final String email;

  RegisterData({
    required this.username,
    required this.email,
  });

  factory RegisterData.fromJson(Map<String, dynamic> json) {
    return RegisterData(
      username: json['username'] ?? '',
      email: json['email'] ?? '',
    );
  }
}

class TokenVerifyData {
  final String username;
  final String email;
  final int iat;
  final int exp;
  final String iss;

  TokenVerifyData({
    required this.username,
    required this.email,
    required this.iat,
    required this.exp,
    required this.iss,
  });

  factory TokenVerifyData.fromJson(Map<String, dynamic> json) {
    return TokenVerifyData(
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      iat: json['iat'] ?? 0,
      exp: json['exp'] ?? 0,
      iss: json['iss'] ?? '',
    );
  }
}

class RefreshTokenData {
  final String accessToken;
  final int expiresIn;

  RefreshTokenData({
    required this.accessToken,
    required this.expiresIn,
  });

  factory RefreshTokenData.fromJson(Map<String, dynamic> json) {
    return RefreshTokenData(
      accessToken: json['access_token'] ?? '',
      expiresIn: json['expires_in'] ?? 0,
    );
  }
}
