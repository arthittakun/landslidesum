import 'package:flutter/material.dart';
import '../services/device_environment_service.dart';
import '../theme/app_theme.dart';

class DeviceEnvironmentScreen extends StatefulWidget {
  const DeviceEnvironmentScreen({super.key});

  @override
  State<DeviceEnvironmentScreen> createState() => _DeviceEnvironmentScreenState();
}

class _DeviceEnvironmentScreenState extends State<DeviceEnvironmentScreen> {
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  List<DeviceEnvironmentData> _environmentData = [];
  List<DeviceEnvironmentData> _filteredData = [];
  PaginationInfo? _pagination;
  
  int _currentPage = 1;
  final int _pageSize = 10;
  bool _isLoadingMore = false;
  
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  bool _isSearchVisible = false;

  @override
  void initState() {
    super.initState();
    _loadEnvironmentData();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _filterData(String query) {
    setState(() {
      if (query.isEmpty) {
        _filteredData = List.from(_environmentData);
      } else {
        _filteredData = _environmentData.where((data) {
          return data.deviceId.toLowerCase().contains(query.toLowerCase());
        }).toList();
      }
    });
  }

  void _toggleSearch() {
    setState(() {
      _isSearchVisible = !_isSearchVisible;
      if (!_isSearchVisible) {
        _searchController.clear();
        _filteredData = List.from(_environmentData);
      }
    });
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      _loadMoreData();
    }
  }

  Future<void> _loadEnvironmentData({bool isRefresh = false}) async {
    if (isRefresh) {
      _currentPage = 1;
    }
    
    setState(() {
      if (isRefresh) {
        _environmentData.clear();
      }
      _isLoading = true;
      _hasError = false;
    });

    try {
      final result = await DeviceEnvironmentService.getEnvironmentState(
        page: _currentPage,
        pageSize: _pageSize,
      );

      if (mounted) {
        setState(() {
          _isLoading = false;
          if (result.success) {
            if (isRefresh) {
              _environmentData = result.data;
            } else {
              _environmentData.addAll(result.data);
            }
            _filteredData = List.from(_environmentData);
            _pagination = result.pagination;
            _hasError = false;
          } else {
            _hasError = true;
            _errorMessage = result.message;
            
            // หากต้องเข้าสู่ระบบใหม่
            if (result.needsRelogin) {
              _showReloginDialog();
            }
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
        });
      }
    }
  }

  Future<void> _loadMoreData() async {
    if (_pagination != null && _pagination!.hasNext && !_isLoading && !_isLoadingMore) {
      setState(() {
        _isLoadingMore = true;
      });
      
      _currentPage++;
      await _loadEnvironmentData();
      
      setState(() {
        _isLoadingMore = false;
      });
    }
  }

  void _showReloginDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Row(
            children: [
              Icon(
                Icons.warning,
                color: AppTheme.warningColor,
                size: 28,
              ),
              const SizedBox(width: 8),
              const Text('ต้องเข้าสู่ระบบใหม่'),
            ],
          ),
          content: const Text('เซสชันของคุณหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่'),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                Navigator.of(context).pushReplacementNamed('/login');
              },
              child: Text(
                'เข้าสู่ระบบ',
                style: TextStyle(
                  color: AppTheme.brandPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Color _getValueColor(String value) {
    final numericValue = double.tryParse(value.replaceAll('%', '')) ?? 0;
    
    if (numericValue >= 80) {
      return AppTheme.errorRed; // สีแดงสำหรับค่าสูง
    } else if (numericValue >= 60) {
      return AppTheme.warningColor; // สีส้มสำหรับค่าปานกลาง
    } else if (numericValue >= 40) {
      return const Color(0xFFFFC107); // สีเหลืองสำหรับค่าปานกลาง
    } else {
      return AppTheme.brandPrimary; // สีเขียวสำหรับค่าปกติ
    }
  }

  String _formatTime(String createAt) {
    try {
      final DateTime dateTime = DateTime.parse(createAt.replaceAll(' ', 'T'));
      final now = DateTime.now();
      final difference = now.difference(dateTime);
      
      if (difference.inMinutes < 60) {
        return '${difference.inMinutes} นาทีที่แล้ว';
      } else if (difference.inHours < 24) {
        return '${difference.inHours} ชั่วโมงที่แล้ว';
      } else {
        return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour.toString().padLeft(2, '0')}:${dateTime.minute.toString().padLeft(2, '0')}';
      }
    } catch (e) {
      return createAt;
    }
  }

  Widget _buildEnvironmentRow(DeviceEnvironmentData data) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      decoration: BoxDecoration(
        color: Theme.of(context).brightness == Brightness.dark 
            ? AppTheme.darkCardSoft 
            : Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: Theme.of(context).brightness == Brightness.dark 
              ? AppTheme.brandPrimary.withOpacity(0.2) 
              : Colors.grey.shade200,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            blurRadius: 2,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            // Header Row
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.brandPrimary,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    (data.location_name?.isNotEmpty == true) ? data.location_name! : data.deviceId,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    _formatTime(data.createAt),
                    style: TextStyle(
                      fontSize: 13,
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.textLight.withOpacity(0.7) 
                          : Colors.grey[600],
                    ),
                  ),
                ),
                Icon(
                  Icons.sensors,
                  size: 16,
                  color: AppTheme.brandPrimary,
                ),
              ],
            ),
            const SizedBox(height: 8),
            
            // Data Row
            Row(
              children: [
                Expanded(child: _buildCompactTile('🌧️', 'ฝน', data.rain)),
                const SizedBox(width: 8),
                Expanded(child: _buildCompactTile('🌡️', 'อุณหภูมิ', data.temp)),
                const SizedBox(width: 8),
                Expanded(child: _buildCompactTile('💧', 'ความชื้น', data.humid)),
                const SizedBox(width: 8),
                Expanded(child: _buildCompactTile('🌱', 'ดิน', data.soil)),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCompactTile(String emoji, String label, String value) {
    final color = _getValueColor(value);
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(
          color: color.withOpacity(0.3),
          width: 0.5,
        ),
      ),
      child: Column(
        children: [
          Text(
            emoji,
            style: const TextStyle(fontSize: 16),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              color: Colors.grey[700],
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 1),
          Text(
            value,
            style: TextStyle(
              fontSize: 12,
              color: color,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: _isSearchVisible 
          ? TextField(
              controller: _searchController,
              autofocus: true,
              style: const TextStyle(color: Colors.white),
              decoration: const InputDecoration(
                hintText: 'ค้นหาอุปกรณ์...',
                hintStyle: TextStyle(color: Colors.white70),
                border: InputBorder.none,
              ),
              onChanged: _filterData,
            )
          : const Text('การทำงานอุปกรณ์'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(_isSearchVisible ? Icons.close : Icons.search),
            onPressed: _toggleSearch,
            tooltip: _isSearchVisible ? 'ปิดการค้นหา' : 'ค้นหา',
          ),
          if (!_isSearchVisible)
            IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: () => _loadEnvironmentData(isRefresh: true),
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
        child: _isLoading && _environmentData.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: AppTheme.brandPrimary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: CircularProgressIndicator(
                      strokeWidth: 3,
                      valueColor: AlwaysStoppedAnimation<Color>(AppTheme.brandPrimary),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'กำลังโหลดข้อมูลการทำงานอุปกรณ์...',
                    style: TextStyle(
                      color: Theme.of(context).brightness == Brightness.dark 
                          ? AppTheme.textLight 
                          : Colors.black,
                    ),
                  ),
                ],
              ),
            )
          : _hasError && _environmentData.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.wifi_off,
                        size: 64,
                        color: AppTheme.errorRed,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        _errorMessage,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: AppTheme.errorRed,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () => _loadEnvironmentData(isRefresh: true),
                        icon: const Icon(Icons.refresh),
                        label: const Text('ลองใหม่'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.brandPrimary,
                          foregroundColor: Colors.white,
                        ),
                      ),
                    ],
                  ),
                )                              : _filteredData.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.search_off,
                            size: 64,
                            color: Theme.of(context).brightness == Brightness.dark 
                                ? AppTheme.textLight.withOpacity(0.5) 
                                : Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            _isSearchVisible && _searchController.text.isNotEmpty
                                ? 'ไม่พบอุปกรณ์ที่ค้นหา'
                                : 'ยังไม่มีข้อมูลการทำงานอุปกรณ์',
                            style: TextStyle(
                              fontSize: 18,
                              color: Theme.of(context).brightness == Brightness.dark 
                                  ? AppTheme.textLight.withOpacity(0.7) 
                                  : Colors.grey[600],
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _isSearchVisible && _searchController.text.isNotEmpty
                                ? 'ลองค้นหาด้วยรหัสอุปกรณ์อื่น'
                                : 'ข้อมูลจะปรากฏเมื่อมีการส่งจากอุปกรณ์',
                            style: TextStyle(
                              fontSize: 14,
                              color: Theme.of(context).brightness == Brightness.dark 
                                  ? AppTheme.textLight.withOpacity(0.6) 
                                  : Colors.grey[500],
                            ),
                          ),
                          if (!_isSearchVisible)
                            const SizedBox(height: 16),
                          if (!_isSearchVisible)
                            TextButton.icon(
                              onPressed: () => _loadEnvironmentData(isRefresh: true),
                              icon: const Icon(Icons.refresh),
                              label: const Text('รีเฟรช'),
                            ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () => _loadEnvironmentData(isRefresh: true),
                      child: CustomScrollView(
                        controller: _scrollController,
                        slivers: [
                          // Summary Header
                          if (_pagination != null && !_isSearchVisible)
                            SliverToBoxAdapter(
                              child: Container(
                                margin: const EdgeInsets.all(16),
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).brightness == Brightness.dark 
                                      ? AppTheme.darkCardSoft 
                                      : AppTheme.backgroundLight,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: AppTheme.brandPrimary.withOpacity(0.3),
                                    width: 1,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Icon(
                                      Icons.info_outline,
                                      color: AppTheme.brandPrimary,
                                      size: 18,
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        'แสดง ${_environmentData.length} จาก ${_pagination!.totalCount} รายการ',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: Theme.of(context).brightness == Brightness.dark 
                                              ? AppTheme.textLight.withOpacity(0.7) 
                                              : Colors.grey,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          
                          // Search Summary
                          if (_isSearchVisible && _searchController.text.isNotEmpty)
                            SliverToBoxAdapter(
                              child: Container(
                                margin: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.blue.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: Colors.blue.shade200,
                                    width: 1,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Icon(
                                      Icons.search,
                                      color: Colors.blue.shade600,
                                      size: 18,
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        'พบ ${_filteredData.length} รายการจากการค้นหา "${_searchController.text}"',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: Colors.blue.shade700,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          
                          // Environment Data List
                          SliverList(
                            delegate: SliverChildBuilderDelegate(
                              (context, index) {
                                final displayData = _isSearchVisible && _searchController.text.isNotEmpty 
                                    ? _filteredData 
                                    : _environmentData;
                                
                                if (index == displayData.length) {
                                  // Loading indicator สำหรับข้อมูลเพิ่มเติม
                                  if (_isLoadingMore) {
                                    return const Padding(
                                      padding: EdgeInsets.all(20),
                                      child: Center(
                                        child: Column(
                                          children: [
                                            SizedBox(
                                              width: 20,
                                              height: 20,
                                              child: CircularProgressIndicator(strokeWidth: 2),
                                            ),
                                            SizedBox(height: 8),
                                            Text(
                                              'กำลังโหลดข้อมูลเพิ่มเติม...',
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: Colors.grey,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    );
                                  } else if (_pagination?.hasNext != true) {
                                    return const Padding(
                                      padding: EdgeInsets.all(20),
                                      child: Center(
                                        child: Text(
                                          'แสดงข้อมูลครบทั้งหมดแล้ว',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Colors.grey,
                                          ),
                                        ),
                                      ),
                                    );
                                  }
                                  return const SizedBox.shrink();
                                }
                                
                                return _buildEnvironmentRow(displayData[index]);
                              },
                              childCount: (_isSearchVisible && _searchController.text.isNotEmpty 
                                             ? _filteredData.length 
                                             : _environmentData.length) + 
                                         (_pagination?.hasNext == true || _isLoadingMore ? 1 : 1),
                            ),
                          ),
                        ],
                      ),
                    ),
        ),
    );
  }
}
