import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../models/gallery_response.dart';

class GalleryDetailScreen extends StatelessWidget {
  final GalleryItem item;
  
  const GalleryDetailScreen({
    super.key,
    required this.item,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).brightness == Brightness.dark 
          ? AppTheme.darkBackground 
          : Colors.grey.shade50,
      appBar: AppBar(
        title: Text(
          item.deviceName.isNotEmpty ? item.deviceName : 'อุปกรณ์ ${item.deviceId}',
          style: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 16,
          ),
        ),
        backgroundColor: AppTheme.brandPrimary,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: Theme.of(context).brightness == Brightness.dark
                ? [
                    AppTheme.darkBackground,
                    AppTheme.darkSurface,
                  ]
                : [
                    AppTheme.brandPrimary.withOpacity(0.05),
                    Colors.white,
                  ],
          ),
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Image Section
              _buildImageSection(context),
              const SizedBox(height: 24),
              
              // Basic Info
              _buildBasicInfoSection(context),
              const SizedBox(height: 24),
              
              // Description
              _buildDescriptionSection(context),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildImageSection(BuildContext context) {
    return Container(
      height: 200,
      width: double.infinity,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Stack(
          children: [
            Image.network(
              item.fullImageUrl,
              width: double.infinity,
              height: double.infinity,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  color: AppTheme.brandPrimary.withOpacity(0.1),
                  child: Center(
                    child: Icon(
                      Icons.image_not_supported,
                      size: 50,
                      color: AppTheme.brandPrimary,
                    ),
                  ),
                );
              },
            ),
            // Time Badge
            Positioned(
              top: 12,
              left: 12,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.7),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.access_time,
                      size: 14,
                      color: Colors.white,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      '${item.datekey} ${item.timekey}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBasicInfoSection(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'ข้อมูลพื้นฐาน',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: AppTheme.brandPrimary,
            ),
          ),
          const SizedBox(height: 16),
          _buildInfoRow('อุปกรณ์', item.deviceName.isNotEmpty ? item.deviceName : item.deviceId, Icons.device_hub),
          _buildInfoRow('ซีเรียลนัมเบอร์', item.serialno.isNotEmpty ? item.serialno : item.deviceId, Icons.qr_code),
          _buildInfoRow('สถานที่', item.locationName.isNotEmpty ? item.locationName : 'ไม่ระบุ', Icons.location_on),
          if (item.latitude.isNotEmpty && item.longitude.isNotEmpty)
            _buildInfoRow('พิกัด', '${item.latitude}, ${item.longitude}', Icons.gps_fixed),
          _buildInfoRow('วันที่', item.datekey, Icons.calendar_today),
          _buildInfoRow('เวลา', item.timekey, Icons.access_time),
        ],
      ),
    );
  }

  Widget _buildDescriptionSection(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                item.hasAIError ? Icons.error : Icons.description,
                color: item.hasAIError ? Colors.red : AppTheme.brandPrimary,
                size: 20,
              ),
              const SizedBox(width: 8),
              Text(
                item.hasAIError ? 'ข้อผิดพลาด AI' : 'รายละเอียดเพิ่มเติม',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: item.hasAIError ? Colors.red : AppTheme.brandPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: item.hasAIError 
                  ? Colors.red.withOpacity(0.1)
                  : Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.darkCard.withOpacity(0.3)
                      : AppTheme.brandPrimary.withOpacity(0.05),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: item.hasAIError 
                    ? Colors.red.withOpacity(0.3)
                    : Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.darkCardSoft.withOpacity(0.3)
                        : Colors.grey.shade200,
              ),
            ),
            child: Text(
              item.textes,
              style: TextStyle(
                color: item.hasAIError
                    ? Colors.red.shade700
                    : Theme.of(context).brightness == Brightness.dark 
                        ? AppTheme.textLight.withOpacity(0.8) 
                        : AppTheme.darkGrey,
                fontSize: 14,
                height: 1.5,
                fontStyle: item.hasAIError ? FontStyle.normal : FontStyle.italic,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            size: 18,
            color: AppTheme.brandPrimary,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 13,
                    color: AppTheme.mediumGrey,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 14,
                    color: AppTheme.darkGrey,
                    fontWeight: FontWeight.w600,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
