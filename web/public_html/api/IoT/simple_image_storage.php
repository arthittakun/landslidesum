<?php

/**
 * Image Storage Functions (Enhanced Version with Compression)
 * 
 * ฟังก์ชันสำหรับเก็บรูปภาพ base64 พร้อมบีบอัดและแปลงเป็น WebP
 * - แปลงเป็น WebP format (ขนาดเล็กกว่า)
 * - ย่อขนาดรูปภาพ (ถ้ามี GD extension)
 * - บีบอัดลดขนาดไฟล์
 */

class SimpleImageStorage
{
    private $base_path;

    public function __construct($base_path = null)
    {
        $this->base_path = $base_path ?? __DIR__ . '/../../image/';
        
        // สร้างโฟลเดอร์ถ้าไม่มี
        if (!file_exists($this->base_path)) {
            mkdir($this->base_path, 0755, true);
        }
    }

    /**
     * บันทึกรูปภาพ base64 เป็นไฟล์ พร้อมบีบอัดและแปลงเป็น WebP
     * 
     * @param string $base64_image ข้อมูลภาพ base64
     * @param string $device_id รหัสอุปกรณ์
     * @param string $prefix คำนำหน้าชื่อไฟล์
     * @param array $options ตัวเลือกการบีบอัด
     * @return array ผลลัพธ์การบันทึก
     */
    public function saveImage($base64_image, $device_id, $prefix = 'device', $options = [])
    {
        try {
            if (empty($base64_image)) {
                return [
                    'success' => false,
                    'message' => 'No image data provided',
                    'filename' => null
                ];
            }

            // ตัวเลือกเริ่มต้น
            $max_width = $options['max_width'] ?? 800;
            $max_height = $options['max_height'] ?? 600;
            $quality = $options['quality'] ?? 80; // WebP quality (0-100)
            $format = $options['format'] ?? 'webp'; // webp หรือ jpg

            // ลบ data URL prefix ออกถ้ามี
            $clean_data = preg_replace('/^data:image\/[a-z]+;base64,/', '', $base64_image);
            $image_data = base64_decode($clean_data);
            
            if ($image_data === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to decode base64 image data',
                    'filename' => null
                ];
            }

            // ตรวจสอบว่ามี GD extension หรือไม่
            if (!extension_loaded('gd')) {
                // ถ้าไม่มี GD ให้บันทึกแบบเดิม
                return $this->saveImageWithoutGD($image_data, $device_id, $prefix);
            }

            // สร้างรูปภาพจาก binary data
            $source_image = imagecreatefromstring($image_data);
            if ($source_image === false) {
                // ถ้าไม่สามารถสร้างรูปภาพได้ ให้บันทึกแบบเดิม
                return $this->saveImageWithoutGD($image_data, $device_id, $prefix);
            }

            // ขนาดรูปภาพต้นฉบับ
            $original_width = imagesx($source_image);
            $original_height = imagesy($source_image);

            // คำนวณขนาดใหม่ (รักษา aspect ratio)
            $ratio = min($max_width / $original_width, $max_height / $original_height);
            $new_width = intval($original_width * $ratio);
            $new_height = intval($original_height * $ratio);

            // สร้างรูปภาพใหม่
            $new_image = imagecreatetruecolor($new_width, $new_height);
            
            // รักษาความโปร่งใส (สำหรับ PNG)
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            
            // ย่อขนาดรูปภาพ
            imagecopyresampled(
                $new_image, $source_image,
                0, 0, 0, 0,
                $new_width, $new_height,
                $original_width, $original_height
            );

            // สร้างชื่อไฟล์ใหม่
            $timestamp = date('Y-m-d_H-i-s');
            $extension = ($format === 'webp') ? 'webp' : 'jpg';
            $filename = "{$prefix}_{$device_id}_{$timestamp}.{$extension}";
            $file_path = $this->base_path . $filename;

            // บันทึกไฟล์ตาม format ที่เลือก
            $save_success = false;
            if ($format === 'webp' && function_exists('imagewebp')) {
                $save_success = imagewebp($new_image, $file_path, $quality);
            } else {
                // fallback เป็น JPEG
                $save_success = imagejpeg($new_image, $file_path, $quality);
                $extension = 'jpg';
                $filename = "{$prefix}_{$device_id}_{$timestamp}.jpg";
                $file_path = $this->base_path . $filename;
            }

            // ทำความสะอาด memory
            imagedestroy($source_image);
            imagedestroy($new_image);

            if ($save_success) {
                $file_size = filesize($file_path);
                $original_size = strlen($image_data);
                $compression_ratio = round((1 - ($file_size / $original_size)) * 100, 1);
                
                return [
                    'success' => true,
                    'message' => 'Image saved and compressed successfully',
                    'filename' => $filename,
                    'file_path' => $file_path,
                    'relative_path' => '/image/' . $filename,
                    'file_size' => $file_size,
                    'original_size' => [
                        'width' => $original_width,
                        'height' => $original_height,
                        'bytes' => $original_size
                    ],
                    'new_size' => [
                        'width' => $new_width,
                        'height' => $new_height,
                        'bytes' => $file_size
                    ],
                    'compression_ratio' => $compression_ratio . '%',
                    'format' => $extension
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to save compressed image',
                    'filename' => null
                ];
            }

        } catch (Exception $e) {
            error_log("Image storage error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Image storage error: ' . $e->getMessage(),
                'filename' => null
            ];
        }
    }

    /**
     * บันทึกรูปภาพแบบเดิม (ไม่มี GD library)
     */
    private function saveImageWithoutGD($image_data, $device_id, $prefix)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "{$prefix}_{$device_id}_{$timestamp}.jpg";
        $file_path = $this->base_path . $filename;

        $save_result = file_put_contents($file_path, $image_data);

        if ($save_result !== false) {
            $file_size = filesize($file_path);
            return [
                'success' => true,
                'message' => 'Image saved successfully (no compression - GD not available)',
                'filename' => $filename,
                'file_path' => $file_path,
                'relative_path' => '/image/' . $filename,
                'file_size' => $file_size,
                'original_size' => ['width' => 'unknown', 'height' => 'unknown'],
                'new_size' => ['width' => 'unknown', 'height' => 'unknown'],
                'format' => 'jpg'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save image file',
                'filename' => null
            ];
        }
    }

    /**
     * ลบไฟล์ภาพ
     */
    public function deleteImage($filename)
    {
        $file_path = $this->base_path . $filename;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }

    /**
     * ตรวจสอบว่าไฟล์ภาพมีอยู่หรือไม่
     */
    public function imageExists($filename)
    {
        return file_exists($this->base_path . $filename);
    }
}

/**
 * Helper function สำหรับใช้งานแบบง่าย (แทน convertBase64ToImage)
 */
function convertBase64ToImage($base64_image, $device_id, $options = [])
{
    $base_path = $options['base_path'] ?? null;
    $prefix = $options['prefix'] ?? 'device';
    $max_width = $options['max_width'] ?? 800;
    $max_height = $options['max_height'] ?? 600;
    $quality = $options['quality'] ?? 80;
    $format = $options['format'] ?? 'webp'; // เปลี่ยนเป็น webp เป็นค่าเริ่มต้น

    $storage = new SimpleImageStorage($base_path);
    return $storage->saveImage($base64_image, $device_id, $prefix, [
        'max_width' => $max_width,
        'max_height' => $max_height,
        'quality' => $quality,
        'format' => $format
    ]);
}

?>
