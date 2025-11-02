import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../services/gallery_service.dart';
import '../models/gallery_response.dart';
import 'gallery_detail_screen.dart';

class DeviceStatusScreen extends StatefulWidget {
  const DeviceStatusScreen({super.key});

  @override
  State<DeviceStatusScreen> createState() => _DeviceStatusScreenState();
}

class _DeviceStatusScreenState extends State<DeviceStatusScreen> {
  List<GalleryItem> galleryItems = [];
  bool isLoading = true;
  bool isLoadingMore = false;
  int currentPage = 1;
  bool hasNextPage = true;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadGalleryData();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      if (!isLoadingMore && hasNextPage) {
        _loadMoreData();
      }
    }
  }

  Future<void> _loadGalleryData() async {
    setState(() {
      isLoading = true;
    });

    try {
      final response = await GalleryService.getGallery(page: 1, pageSize: 20);
      print(  'Gallery Response: $response');
      if (response != null && mounted) {
        setState(() {
          galleryItems = response.data;
          currentPage = response.pagination.currentPage;
          hasNextPage = response.pagination.hasNext;
          isLoading = false;
        });
      } else {
        if (mounted) {
          setState(() {
            isLoading = false;
          });
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง'),
              backgroundColor: Colors.orange,
            ),
          );
        }
      }

    } catch (e) {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('เกิดข้อผิดพลาดในการโหลดข้อมูล: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _loadMoreData() async {
    setState(() {
      isLoadingMore = true;
    });

    try {
      final response = await GalleryService.getGallery(page: currentPage + 1, pageSize: 20);
      if (response != null && mounted) {
        setState(() {
          galleryItems.addAll(response.data);
          currentPage = response.pagination.currentPage;
          hasNextPage = response.pagination.hasNext;
          isLoadingMore = false;
        });
      } else {
        if (mounted) {
          setState(() {
            isLoadingMore = false;
          });
          // ไม่แสดง SnackBar สำหรับการโหลดเพิ่มเติม เพื่อไม่ให้รบกวนผู้ใช้
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          isLoadingMore = false;
        });
        // ไม่แสดง SnackBar สำหรับการโหลดเพิ่มเติม เพื่อไม่ให้รบกวนผู้ใช้
      }
    }
  }

  Future<void> _refreshData() async {
    setState(() {
      currentPage = 1;
      hasNextPage = true;
    });
    await _loadGalleryData();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).brightness == Brightness.dark 
          ? AppTheme.darkBackground 
          : Colors.grey.shade50,
       appBar: AppBar(
        title: const Text('ภาพจากอุปกรณ์'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshData,
            tooltip: 'รีเฟรชข้อมูล',
          ),
        ],
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
        child: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              double horizontalPadding;
              
              // Responsive padding based on screen width
              if (constraints.maxWidth > 900) {
                horizontalPadding = 32;
              } else if (constraints.maxWidth > 600) {
                horizontalPadding = 24;
              } else {
                horizontalPadding = 16;
              }
              
              return Padding(
                padding: EdgeInsets.symmetric(
                  horizontal: horizontalPadding,
                  vertical: 16,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Section
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      child: LayoutBuilder(
                        builder: (context, constraints) {
                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'รูปภาพจากอุปกรณ์ระบบตรวจสอบดินถล่มและน้ำท่วม',
                                style: TextStyle(
                                  fontSize: constraints.maxWidth > 600 ? 16 : 14,
                                  color: Theme.of(context).brightness == Brightness.dark 
                                      ? AppTheme.textLight.withOpacity(0.7) 
                                      : AppTheme.darkGrey,
                                  height: 1.4,
                                  fontWeight: FontWeight.w400,
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ),
                    
                    // Gallery Grid
                    Expanded(
                      child: isLoading
                        ? const Center(child: CircularProgressIndicator())
                        : galleryItems.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.photo_library_outlined,
                                    size: 64,
                                    color: AppTheme.mediumGrey,
                                  ),
                                  const SizedBox(height: 16),
                                  Text(
                                    'ไม่มีภาพในแกลเลอรี่',
                                    style: TextStyle(
                                      fontSize: 16,
                                      color: AppTheme.mediumGrey,
                                    ),
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _refreshData,
                              child: LayoutBuilder(
                                builder: (context, constraints) {
                                  int crossAxisCount;
                                  double childAspectRatio;
                                  
                                  // Responsive grid based on screen width
                                  if (constraints.maxWidth > 900) {
                                    // Large tablets/desktop
                                    crossAxisCount = 4;
                                    childAspectRatio = 0.85;
                                  } else if (constraints.maxWidth > 600) {
                                    // Small tablets
                                    crossAxisCount = 3;
                                    childAspectRatio = 0.8;
                                  } else if (constraints.maxWidth > 400) {
                                    // Large phones
                                    crossAxisCount = 2;
                                    childAspectRatio = 0.75;
                                  } else {
                                    // Small phones
                                    crossAxisCount = 1;
                                    childAspectRatio = 1.2;
                                  }
                                  
                                  return GridView.builder(
                                    controller: _scrollController,
                                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                      crossAxisCount: crossAxisCount,
                                      mainAxisSpacing: 12,
                                      crossAxisSpacing: 12,
                                      childAspectRatio: childAspectRatio,
                                    ),
                                    itemCount: galleryItems.length + (isLoadingMore ? crossAxisCount : 0),
                                    itemBuilder: (context, index) {
                                      if (index >= galleryItems.length) {
                                        return const Center(child: CircularProgressIndicator());
                                      }
                                      return _buildGalleryCard(context, galleryItems[index]);
                                    },
                                  );
                                },
                              ),
                            ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }
  
  Widget _buildGalleryCard(BuildContext context, GalleryItem item) {
    return GestureDetector(
      onTap: () {
        _showImageDetails(context, item);
      },
      child: Container(
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
              spreadRadius: 0,
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image Section
            Expanded(
              flex: 3,
              child: Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(16),
                    topRight: Radius.circular(16),
                  ),
                  color: Theme.of(context).brightness == Brightness.dark 
                      ? AppTheme.darkCard 
                      : Colors.grey.shade200,
                ),
                child: ClipRRect(
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(16),
                    topRight: Radius.circular(16),
                  ),
                  child: Stack(
                    children: [
                      // Real Image from API
                      Container(
                        width: double.infinity,
                        height: double.infinity,
                        child: Image.network(
                          item.fullImageUrl,
                          fit: BoxFit.cover,
                          loadingBuilder: (context, child, loadingProgress) {
                            if (loadingProgress == null) return child;
                            return Container(
                              color: AppTheme.brandPrimary.withOpacity(0.1),
                              child: Center(
                                child: CircularProgressIndicator(
                                  valueColor: AlwaysStoppedAnimation<Color>(AppTheme.brandPrimary),
                                ),
                              ),
                            );
                          },
                          errorBuilder: (context, error, stackTrace) {
                            return Container(
                              color: AppTheme.brandPrimary.withOpacity(0.1),
                              child: Icon(
                                Icons.broken_image,
                                size: 40,
                                color: AppTheme.brandPrimary.withOpacity(0.7),
                              ),
                            );
                          },
                        ),
                      ),
                       // Time Badge (Top Left)
                       Positioned(
                         top: 8,
                         left: 8,
                         child: Container(
                           padding: const EdgeInsets.symmetric(
                             horizontal: 8,
                             vertical: 4,
                           ),
                           decoration: BoxDecoration(
                             color: Colors.black.withOpacity(0.7),
                             borderRadius: BorderRadius.circular(12),
                           ),
                           child: Row(
                             mainAxisSize: MainAxisSize.min,
                             children: [
                               const Icon(
                                 Icons.access_time,
                                 size: 12,
                                 color: Colors.white,
                               ),
                               const SizedBox(width: 4),
                               Text(
                                 '${item.datekey} ${item.timekey}',
                                 style: const TextStyle(
                                   color: Colors.white,
                                   fontSize: 10,
                                   fontWeight: FontWeight.w600,
                                 ),
                               ),
                             ],
                           ),
                         ),
                       ),
                       // คลิกเพื่อดู (Top Right)
                       Positioned(
                         top: 8,
                         right: 8,
                         child: Container(
                           padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                           decoration: BoxDecoration(
                             color: AppTheme.brandPrimary.withOpacity(0.9),
                             borderRadius: BorderRadius.circular(8),
                             border: Border.all(
                               color: AppTheme.brandPrimary,
                               width: 1,
                             ),
                           ),
                           child: Row(
                             mainAxisSize: MainAxisSize.min,
                             children: [
                               Icon(
                                 Icons.touch_app,
                                 size: 12,
                                 color: Colors.white,
                               ),
                               const SizedBox(width: 4),
                               Text(
                                 'คลิกเพื่อดู',
                                 style: const TextStyle(
                                   fontSize: 10,
                                   color: Colors.white,
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
              ),
            ),
            
            // Content Section
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.locationName.isNotEmpty ? item.locationName : 'อุปกรณ์ ${item.deviceId}',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight 
                            : AppTheme.darkGrey,
                        height: 1.2,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      item.deviceName.isNotEmpty ? item.deviceName : item.deviceId,
                      style: TextStyle(
                        fontSize: 11,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight.withOpacity(0.6) 
                            : AppTheme.mediumGrey,
                        fontWeight: FontWeight.w500,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      item.datekey,
                      style: TextStyle(
                        fontSize: 12,
                        color: Theme.of(context).brightness == Brightness.dark 
                            ? AppTheme.textLight.withOpacity(0.7) 
                            : AppTheme.mediumGrey,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Expanded(
                      child: Text(
                        item.textes,
                        style: TextStyle(
                          fontSize: 12,
                          color: Theme.of(context).brightness == Brightness.dark 
                              ? AppTheme.textLight.withOpacity(0.7) 
                              : AppTheme.mediumGrey,
                          height: 1.3,
                          fontWeight: FontWeight.w400,
                        ),
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis,
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

  void _showImageDetails(BuildContext context, GalleryItem item) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => GalleryDetailScreen(item: item),
      ),
    );
  }
}
