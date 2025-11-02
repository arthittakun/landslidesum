class RainReportResponse {
  final bool ok;
  final String source;
  final String title;
  final String dateText;
  final int count;
  final RainReportFilters filters;
  final List<RainStation> stations;

  RainReportResponse({
    required this.ok,
    required this.source,
    required this.title,
    required this.dateText,
    required this.count,
    required this.filters,
    required this.stations,
  });

  factory RainReportResponse.fromJson(Map<String, dynamic> json) {
    return RainReportResponse(
      ok: json['ok'] ?? false,
      source: json['source'] ?? '',
      title: json['title'] ?? '',
      dateText: json['date_text'] ?? '',
      count: json['count'] ?? 0,
      filters: RainReportFilters.fromJson(json['filters'] ?? {}),
      stations: (json['stations'] as List<dynamic>?)
              ?.map((station) => RainStation.fromJson(station))
              .toList() ??
          [],
    );
  }
}

class RainReportFilters {
  final String? province;
  final String? top;
  final String? min12h;
  final String? sort;
  final int? dmode;
  final String? ondate;

  RainReportFilters({
    this.province,
    this.top,
    this.min12h,
    this.sort,
    this.dmode,
    this.ondate,
  });

  factory RainReportFilters.fromJson(Map<String, dynamic> json) {
    return RainReportFilters(
      province: json['province'],
      top: json['top'],
      min12h: json['min12h'],
      sort: json['sort'],
      dmode: json['dmode'],
      ondate: json['ondate'],
    );
  }
}

class RainStation {
  final int order;
  final String id;
  final String village;
  final String subdistrict;
  final String district;
  final String province;
  final String rain12h;
  final String rain12hName;
  final String rain07h;
  final String rain07hName;
  final String temp;
  final String wl;
  final String soil;

  RainStation({
    required this.order,
    required this.id,
    required this.village,
    required this.subdistrict,
    required this.district,
    required this.province,
    required this.rain12h,
    required this.rain12hName,
    required this.rain07h,
    required this.rain07hName,
    required this.temp,
    required this.wl,
    required this.soil,
  });

  factory RainStation.fromJson(Map<String, dynamic> json) {
    return RainStation(
      order: json['order'] ?? 0,
      id: json['id'] ?? '',
      village: json['village'] ?? '',
      subdistrict: json['subdistrict'] ?? '',
      district: json['district'] ?? '',
      province: json['province'] ?? '',
      rain12h: json['rain12h'] ?? '0',
      rain12hName: json['rain12h_name'] ?? '',
      rain07h: json['rain07h'] ?? '0',
      rain07hName: json['rain07h_name'] ?? '',
      temp: json['temp'] ?? '0',
      wl: json['wl'] ?? 'N/A',
      soil: json['soil'] ?? 'N/A',
    );
  }

  double get rain12hValue => double.tryParse(rain12h) ?? 0.0;
  double get rain07hValue => double.tryParse(rain07h) ?? 0.0;
  double get tempValue => double.tryParse(temp) ?? 0.0;
  double get wlValue => wl == 'N/A' ? 0.0 : double.tryParse(wl) ?? 0.0;
  double get soilValue => soil == 'N/A' ? 0.0 : double.tryParse(soil) ?? 0.0;
}
