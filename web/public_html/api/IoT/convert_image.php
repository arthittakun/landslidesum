<?php

/**
 * Image Processing Functions for IoT API
 * 
 * ฟังก์ชันสำหรับประมวลผลรูปภาพ base64
 * - แปลง base64 เป็นไฟล์ภาพ
 * - ย่อขนาดภาพ
 * - บันทึกในโฟลเดอร์ที่กำหนด
 */

class ImageConverter
{
    private $base_path;
    private $max_size;
    private $quality;

    public function __construct($base_path = null, $max_size = 800, $quality = 85)
    {
        $this->base_path = $base_path ?? __DIR__ . '/../../image/';
        $this->max_size = $max_size;
        $this->quality = $quality;
        
        // สร้างโฟลเดอร์ถ้าไม่มี
        if (!file_exists($this->base_path)) {
            mkdir($this->base_path, 0755, true);
        }
    }

    /**
     * แปลงภาพ base64 เป็นไฟล์และย่อขนาด
     * 
     * @param string $base64_image ข้อมูลภาพ base64
     * @param string $device_id รหัสอุปกรณ์
     * @param string $prefix คำนำหน้าชื่อไฟล์ (optional)
     * @return array ผลลัพธ์การประมวลผล
     */
    public function convertAndResize($base64_image, $device_id, $prefix = 'device')
    {
        try {
            if (empty($base64_image)) {
                return [
                    'success' => false,
                    'message' => 'No image data provided',
                    'filename' => null
                ];
            }

            // ลบ data URL prefix ออกถ้ามี
            $image_data = preg_replace('/^data:image\/[a-z]+;base64,/', '', $base64_image);
            $image_data = base64_decode($image_data);
            
            if ($image_data === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to decode base64 image data',
                    'filename' => null
                ];
            }

            // สร้างชื่อไฟล์ใหม่ (device + เวลา)
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "{$prefix}_{$device_id}_{$timestamp}.jpg";
            $file_path = $this->base_path . $filename;

            // สร้างภาพจาก string
            $source_image = imagecreatefromstring($image_data);
            if ($source_image === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to create image from data',
                    'filename' => null
                ];
            }

            // ได้ขนาดภาพต้นฉบับ
            $original_width = imagesx($source_image);
            $original_height = imagesy($source_image);

            // คำนวณขนาดใหม่
            $new_dimensions = $this->calculateNewDimensions($original_width, $original_height);

            // สร้างภาพใหม่ที่ย่อขนาดแล้ว
            $resized_image = imagecreatetruecolor($new_dimensions['width'], $new_dimensions['height']);
            
            // รักษาความโปร่งใสสำหรับ PNG
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);

            // ย่อขนาดภาพ
            imagecopyresampled(
                $resized_image, $source_image,
                0, 0, 0, 0,
                $new_dimensions['width'], $new_dimensions['height'],
                $original_width, $original_height
            );

            // บันทึกภาพ
            $save_result = imagejpeg($resized_image, $file_path, $this->quality);

            // ล้างหน่วยความจำ
            imagedestroy($source_image);
            imagedestroy($resized_image);

            if ($save_result) {
                $file_size = filesize($file_path);
                return [
                    'success' => true,
                    'message' => 'Image converted and saved successfully',
                    'filename' => $filename,
                    'file_path' => $file_path,
                    'relative_path' => '/image/' . $filename,
                    'original_size' => ['width' => $original_width, 'height' => $original_height],
                    'new_size' => $new_dimensions,
                    'file_size' => $file_size,
                    'quality' => $this->quality
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to save image file',
                    'filename' => null
                ];
            }

        } catch (Exception $e) {
            error_log("Image conversion error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Image processing error: ' . $e->getMessage(),
                'filename' => null
            ];
        }
    }

    /**
     * คำนวณขนาดใหม่ของภาพ
     * 
     * @param int $width ความกว้างต้นฉบับ
     * @param int $height ความสูงต้นฉบับ
     * @return array ขนาดใหม่
     */
    private function calculateNewDimensions($width, $height)
    {
        // ถ้าภาพเล็กกว่า max_size แล้ว ไม่ต้องย่อ
        if ($width <= $this->max_size && $height <= $this->max_size) {
            return ['width' => $width, 'height' => $height];
        }

        // คำนวณอัตราส่วน
        if ($width > $height) {
            $new_width = $this->max_size;
            $new_height = intval(($height * $this->max_size) / $width);
        } else {
            $new_height = $this->max_size;
            $new_width = intval(($width * $this->max_size) / $height);
        }

        return ['width' => $new_width, 'height' => $new_height];
    }

    /**
     * ลบไฟล์ภาพ
     * 
     * @param string $filename ชื่อไฟล์
     * @return bool ผลลัพธ์การลบ
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
     * 
     * @param string $filename ชื่อไฟล์
     * @return bool ผลลัพธ์การตรวจสอบ
     */
    public function imageExists($filename)
    {
        return file_exists($this->base_path . $filename);
    }

    /**
     * รับข้อมูลไฟล์ภาพ
     * 
     * @param string $filename ชื่อไฟล์
     * @return array|false ข้อมูลไฟล์
     */
    public function getImageInfo($filename)
    {
        $file_path = $this->base_path . $filename;
        if (!file_exists($file_path)) {
            return false;
        }

        $image_info = getimagesize($file_path);
        $file_size = filesize($file_path);

        return [
            'filename' => $filename,
            'width' => $image_info[0],
            'height' => $image_info[1],
            'mime_type' => $image_info['mime'],
            'file_size' => $file_size,
            'file_path' => $file_path,
            'relative_path' => '/image/' . $filename
        ];
    }
}

/**
 * Helper function สำหรับใช้งานแบบง่าย
 */
function convertBase64ToImage($base64_image, $device_id, $options = [])
{
    $base_path = $options['base_path'] ?? null;
    $max_size = $options['max_size'] ?? 800;
    $quality = $options['quality'] ?? 85;
    $prefix = $options['prefix'] ?? 'device';

    $converter = new ImageConverter($base_path, $max_size, $quality);
    return $converter->convertAndResize($base64_image, $device_id, $prefix);
}

?>
