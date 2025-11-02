package com.landslide_alerts

import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.Context
import android.os.Build
import android.os.Bundle
import io.flutter.embedding.android.FlutterActivity

class MainActivity : FlutterActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Create notification channels for Android 8.0+
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            createNotificationChannels()
        }
    }
    
    private fun createNotificationChannels() {
        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        
        // Instant notification channel
        val instantChannel = NotificationChannel(
            "landslide_instant",
            "การแจ้งเตือนทันที",
            NotificationManager.IMPORTANCE_HIGH
        ).apply {
            description = "การแจ้งเตือนที่แสดงทันที"
            enableVibration(true)
            setShowBadge(true)
        }
        
        // Scheduled notification channel
        val scheduledChannel = NotificationChannel(
            "landslide_scheduled",
            "การแจ้งเตือนตามเวลา",
            NotificationManager.IMPORTANCE_HIGH
        ).apply {
            description = "การแจ้งเตือนที่จัดเวลาไว้"
            enableVibration(true)
            setShowBadge(true)
        }
        
        notificationManager.createNotificationChannel(instantChannel)
        notificationManager.createNotificationChannel(scheduledChannel)
    }
}
