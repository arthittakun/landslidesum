class AboutResponse {
  final bool success;
  final String message;
  final AboutData? data;

  AboutResponse({
    required this.success,
    required this.message,
    this.data,
  });

  factory AboutResponse.fromJson(Map<String, dynamic> json) {
    return AboutResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? AboutData.fromJson(json['data']) : null,
    );
  }
}

class AboutData {
  final int policyId;
  final String policyType;
  final String policyText;

  AboutData({
    required this.policyId,
    required this.policyType,
    required this.policyText,
  });

  factory AboutData.fromJson(Map<String, dynamic> json) {
    return AboutData(
      policyId: json['policy_id'] ?? 0,
      policyType: json['policy_type'] ?? '',
      policyText: json['policy_text'] ?? '',
    );
  }
}
