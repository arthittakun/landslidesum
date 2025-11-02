import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/terms_response.dart';

class TermsService {
  static const String endpoint = ApiConfig.termsEndpoint;

  static Future<TermsResponse?> getTerms() async {
    try {
      final response = await http.get(
        Uri.parse(endpoint),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        try {
          final jsonData = json.decode(response.body);
          
          final termsResponse = TermsResponse.fromJson(jsonData);
          return termsResponse;
        } catch (jsonError) {
          return null;
        }
      }

      return null;
    } catch (e) {
      return null;
    }
  }
}
