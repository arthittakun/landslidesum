class GalleryResponse {
  final String status;
  final List<GalleryItem> data;
  final Pagination pagination;
  final String message;

  GalleryResponse({
    required this.status,
    required this.data,
    required this.pagination,
    required this.message,
  });

  factory GalleryResponse.fromJson(Map<String, dynamic> json) {
    return GalleryResponse(
      status: json['status'],
      data: (json['data'] as List).map((item) => GalleryItem.fromJson(item)).toList(),
      pagination: Pagination.fromJson(json['pagination']),
      message: json['message'],
    );
  }
}

class GalleryItem {
  final String deviceId;
  final String timekey;
  final String datekey;
  final String landslide;
  final String floot;
  final String imgPath;
  final String textes;
  final String locationName;
  final String latitude;
  final String longitude;
  final String deviceName;
  final String serialno;

  GalleryItem({
    required this.deviceId,
    required this.timekey,
    required this.datekey,
    required this.landslide,
    required this.floot,
    required this.imgPath,
    required this.textes,
    required this.locationName,
    required this.latitude,
    required this.longitude,
    required this.deviceName,
    required this.serialno,
  });

  factory GalleryItem.fromJson(Map<String, dynamic> json) {
    return GalleryItem(
      deviceId: json['device_id'] ?? '',
      timekey: json['timekey'] ?? '',
      datekey: json['datekey'] ?? '',
      landslide: json['landslide'] ?? "0",
      floot: json['floot'] ?? "0",
      imgPath: json['img_path'] ?? '',
      textes: json['textes'] ?? '',
      locationName: json['location_name'] ?? '',
      latitude: json['latitude'] ?? '',
      longitude: json['longtitude'] ?? '', // Note: API has typo "longtitude"
      deviceName: json['device_name'] ?? '',
      serialno: json['serialno'] ?? '',
    );
  }

  String get fullImageUrl => '${imgPath.startsWith('/') ? 'http://landslide-alerts.com$imgPath' : imgPath}';
  
  String get formattedDateTime => '$datekey $timekey';
  
  String get riskLevel {
    switch (landslide) {
      case "1":
        return 'ระดับต่ำ';
      case "2":
        return 'ระดับปานกลาง';
      case "3":
        return 'ระดับสูง';
      default:
        return 'ระดับต่ำ'; // Default to level 1 if invalid
    }
  }

  String get categoryTag {
    switch (landslide) {
      case "1":
        return 'เสี่ยงต่ำ';
      case "2":
        return 'เสี่ยงปานกลาง';
      case "3":
        return 'เสี่ยงสูง';
      default:
        return 'เสี่ยงต่ำ'; // Default to level 1 if invalid
    }
  }

  /// ตรวจสอบว่าการแจ้งเตือนนี้มีข้อผิดพลาดจาก AI หรือไม่
  bool get hasAIError => textes.contains('AI analysis failed');

  /// ตรวจสอบว่าการแจ้งเตือนนี้มีความเสี่ยงหรือไม่
  bool get hasRisk {
    final int? landslideValue = int.tryParse(landslide);
    final int? flootValue = int.tryParse(floot);
    return (landslideValue != null && landslideValue > 0) ||
           (flootValue != null && flootValue > 0);
  }
}

class Pagination {
  final int currentPage;
  final int pageSize;
  final int totalRecords;
  final int totalPages;
  final bool hasNext;
  final bool hasPrevious;

  Pagination({
    required this.currentPage,
    required this.pageSize,
    required this.totalRecords,
    required this.totalPages,
    required this.hasNext,
    required this.hasPrevious,
  });

  factory Pagination.fromJson(Map<String, dynamic> json) {
    return Pagination(
      currentPage: json['current_page'],
      pageSize: json['page_size'],
      totalRecords: json['total_records'],
      totalPages: json['total_pages'],
      hasNext: json['has_next'],
      hasPrevious: json['has_previous'],
    );
  }
}
