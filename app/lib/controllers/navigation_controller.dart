class NavigationController {
  static int currentIndex = 0;
  
  static void setCurrentIndex(int index) {
    currentIndex = index;
  }
  
  static int getCurrentIndex() {
    return currentIndex;
  }
  
  static const List<String> tabNames = [
    'หน้าแรก',
    'รายงาน', 
    'สถานะอุปกรณ์',
    'แจ้งเตือน',
    'การตั้งค่า',
  ];
  
  static String getCurrentTabName() {
    return tabNames[currentIndex];
  }
}
