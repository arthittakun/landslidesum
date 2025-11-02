# Flutter Local Notifications
-keep class com.dexterous.** { *; }
-keep class io.flutter.plugins.** { *; }
-keep class io.flutter.plugin.** { *; }

# Permission Handler
-keep class com.baseflow.permissionhandler.** { *; }

# Keep notification classes
-keep class * extends android.app.NotificationManager { *; }
-keep class * extends android.app.Notification { *; }
-keep class android.app.NotificationChannel { *; }
-keep class androidx.core.app.NotificationCompat** { *; }

# Keep timezone data
-keep class org.threeten.bp.** { *; }

# Keep all native methods
-keepclasseswithmembernames class * {
    native <methods>;
}
