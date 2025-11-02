class TermsResponse {
  final bool success;
  final String message;
  final String updatedAt;
  final TermsData data;

  TermsResponse({
    required this.success,
    required this.message,
    required this.updatedAt,
    required this.data,
  });

  factory TermsResponse.fromJson(Map<String, dynamic> json) {
    // ตรวจสอบและแปลงค่า updated_at
    String updatedAtValue = '';
    final rawUpdatedAt = json['updated_at'];
    if (rawUpdatedAt != null) {
      if (rawUpdatedAt is String) {
        updatedAtValue = rawUpdatedAt;
      } else if (rawUpdatedAt is int) {
        // หากเป็น timestamp ให้แปลงเป็น DateTime แล้วเป็น String
        final dateTime = DateTime.fromMillisecondsSinceEpoch(rawUpdatedAt * 1000);
        updatedAtValue = dateTime.toIso8601String();
      } else {
        updatedAtValue = rawUpdatedAt.toString();
      }
    }
    
    final response = TermsResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      updatedAt: updatedAtValue,
      data: TermsData.fromJson(json['data'] ?? {}),
    );
    
    return response;
  }

  @override
  String toString() {
    return 'TermsResponse(success: $success, updatedAt: "$updatedAt", data: $data)';
  }
}

class TermsData {
  final PolicyItem policy;
  final PolicyItem term;

  TermsData({
    required this.policy,
    required this.term,
  });

  factory TermsData.fromJson(Map<String, dynamic> json) {
    final data = TermsData(
      policy: PolicyItem.fromJson(json['policy'] ?? {}),
      term: PolicyItem.fromJson(json['term'] ?? {}),
    );
    
    return data;
  }

  @override
  String toString() {
    return 'TermsData(policy: $policy, term: $term)';
  }
}

class PolicyItem {
  final int policyId;
  final String policyType;
  final String policyText;
  final String updatedAt;

  PolicyItem({
    required this.policyId,
    required this.policyType,
    required this.policyText,
    required this.updatedAt,
  });

  factory PolicyItem.fromJson(Map<String, dynamic> json) {
    final item = PolicyItem(
      policyId: json['policy_id'] ?? 0,
      policyType: json['policy_type'] ?? '',
      policyText: json['policy_text'] ?? '',
      updatedAt: json['updated_at'] ?? '',
    );
    
    return item;
  }

  @override
  String toString() {
    return 'PolicyItem(policyId: $policyId, policyType: "$policyType", updatedAt: "$updatedAt")';
  }
}
