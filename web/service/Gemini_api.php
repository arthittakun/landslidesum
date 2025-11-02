<?php
class Gemini_api {
    private $service_account_path;
    private $projectId = 'landslide-1750071587113';
    private $location = 'us-central1'; 
    private $modelId = 'gemini-2.0-flash-lite-001';

    public function __construct(){
        $this->service_account_path = __DIR__ . '/ai-wo-461706-3e9436a01a1f.json';
    }

    private function getGoogleAccessToken($creds) {
        try {
            // error_log("DEBUG: Generating Google Access Token...");
            
            $now = time();
            $header = ['typ' => 'JWT', 'alg' => 'RS256'];
            $claim = [
                'iss'   => $creds['client_email'],
                'aud'   => 'https://oauth2.googleapis.com/token',
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];
            
            // error_log("DEBUG: JWT claims prepared for: " . $creds['client_email']);
            
            $base64url = fn($data) => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
            $unsigned = $base64url(json_encode($header)) . '.' . $base64url(json_encode($claim));
            openssl_sign($unsigned, $signature, $creds['private_key'], 'sha256WithRSAEncryption')
                or exit("JWT sign error");
            $jwt = $unsigned . '.' . $base64url($signature);
            
            // error_log("DEBUG: JWT token generated successfully");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // error_log("DEBUG: Token request HTTP code: " . $http_code);
            
            if ($http_code !== 200) {
                error_log("ERROR: Token request failed. Response: " . $response);
            }
            
            $tokenData = json_decode($response, true);
            $access_token = $tokenData['access_token'] ?? null;
            
            // if ($access_token) {
            //     error_log("DEBUG: Access token obtained successfully");
            // } else {
            //     error_log("ERROR: Failed to get access token from response");
            // }
            
            if (!$access_token) {
                error_log("ERROR: Failed to get access token from response");
            }
            
            return $access_token;
        } catch (Exception $e) {
            error_log("ERROR: Exception in getGoogleAccessToken: " . $e->getMessage());
            return null;
        }
    }

    public function analysis(String $image) {
        try {
            // error_log("=== DEBUG: Starting Gemini AI analysis ===");
            
            $creds = json_decode(file_get_contents($this->service_account_path), true);
            if (!$creds) {
                error_log("ERROR: Service account file not found or invalid");
                throw new Exception('ไม่พบไฟล์ Service Account');
            }
            // error_log("DEBUG: Service account loaded successfully");

            $accessToken = $this->getGoogleAccessToken($creds);
            if (!$accessToken) {
                error_log("ERROR: Failed to get access token");
                throw new Exception('ไม่สามารถขอ Access Token ได้');
            }
            // error_log("DEBUG: Access token obtained successfully");

            // Vertex AI Endpoint
            $endpoint = sprintf(
                'https://%s-aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:generateContent',
                $this->location,
                $this->projectId,
                $this->location,
                $this->modelId
            );
            // error_log("DEBUG: Endpoint URL: " . $endpoint);

            // สร้าง prompt สำหรับวิเคราะห์รูปภาพ
            $prompt = "ตรวจสอบรูปที่ฉันส่งให้ต่อไปนี้ว่า มีความเสี่ยง ต่อดินสไลด์ หรือ น้ำท่วมไหม ให้ประเมินระดับความเสี่ยงและส่งค่ากลับมาเป็น json เช่น 

ถ้ามีความเสี่ยง:
{
\"status\" : \"True\",
\"landslide\" : \"2\",
\"flood\" : \"1\",
\"text\" : \"ระดับน้ำขึ้นสูง เเละดินถล่มค่อนข้างรุ่นเเรงโปรดระวังดินถล่มเเละน้ำป่าไหลหลาก ขอให้เตรียมตัวอพยพ หรือเตรียมตัวรับมือกับวิกฤต\"
}

หรือ ถ้าไม่มีความเสี่ยงเลย:
{
\"status\" : \"False\",
\"landslide\" : \"0\",
\"flood\" : \"0\",
\"text\" : \"\"
}

ระดับความเสี่ยง:
- 0 = ไม่มีความเสี่ยง/ปกติ
- 1 = ความเสี่ยงระดับเบา/ควรเฝ้าระวัง  
- 2 = ความเสี่ยงระดับปานกลาง/ควรระวัง
- 3 = ความเสี่ยงระดับสูง/อันตราย

และไม่ต้องอธิบายอะไรเพิ่มเติมนอกจากส่ง Json มาให้  (คำอธิบาย text ไม่เกิน 250 ตัวอักษร)

และนี่คือรูปที่ฉันจะให้คุณวิเคราะห์";

            $requestBody = [
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $image
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            // error_log("DEBUG: Image data length: " . strlen($image) . " characters");
            // error_log("DEBUG: Request body prepared, sending to Gemini API...");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            // error_log("DEBUG: HTTP response code: " . $http_code);
            // if ($curl_error) {
            //     error_log("ERROR: cURL error: " . $curl_error);
            // }

            if ($curl_error) {
                error_log("ERROR: cURL error: " . $curl_error);
                // error_log("ERROR: Returning cURL error response");
                return ['response' => '', 'http_code' => 0, 'error' => $curl_error, 'json' => null];
            }

            if ($http_code !== 200) {
                error_log("ERROR: HTTP error " . $http_code . ". Response: " . substr($response, 0, 500));
                return ['response' => $response, 'http_code' => $http_code, 'error' => 'HTTP Error ' . $http_code, 'json' => null];
            }

            // error_log("DEBUG: Gemini API response received successfully");
            
            // แปลง response เป็น JSON
            $data = json_decode($response, true);
            if (!$data) {
                error_log("ERROR: Failed to decode JSON response");
                // error_log("Raw response: " . substr($response, 0, 1000));
                return ['response' => $response, 'http_code' => $http_code, 'error' => 'Invalid JSON response', 'json' => null];
            }

            // error_log("DEBUG: JSON response decoded successfully");
            
            // ดึงข้อความจาก AI response
            $text = '';
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $data['candidates'][0]['content']['parts'][0]['text'];
                // error_log("DEBUG: AI response text: " . $text);
                
                // ลบ markdown code blocks
                if (strpos($text, '```json') !== false) {
                    // error_log("DEBUG: Removing markdown code blocks");
                    $text = preg_replace('/```json\s*/', '', $text);
                    $text = preg_replace('/\s*```.*/', '', $text);
                }
                
                // error_log("DEBUG: Cleaned text: " . trim($text));
                
                // แปลง JSON string เป็น array
                $json_result = json_decode(trim($text), true);
                
                if ($json_result && is_array($json_result)) {
                    // error_log("DEBUG: AI analysis parsed successfully");
                    // error_log("DEBUG: Landslide level: " . ($json_result['landslide'] ?? 'null'));
                    // error_log("DEBUG: Flood level: " . ($json_result['flood'] ?? 'null'));
                    // error_log("DEBUG: Text: " . ($json_result['text'] ?? 'null'));
                    // error_log("=== DEBUG: Gemini AI analysis completed successfully ===");
                    return ['response' => $response, 'http_code' => $http_code, 'error' => '', 'json' => $json_result];
                } else {
                    error_log("ERROR: Failed to parse AI response JSON");
                    // error_log("JSON decode error: " . json_last_error_msg());
                }
            } else {
                error_log("ERROR: No text found in AI response");
                // error_log("Response structure: " . json_encode($data, JSON_PRETTY_PRINT));
            }

            error_log("ERROR: Unable to parse AI response");
            return ['response' => $response, 'http_code' => $http_code, 'error' => 'Unable to parse AI response', 'json' => null];
            
        } catch (Exception $e) {
            error_log("ERROR: Exception in Gemini AI analysis: " . $e->getMessage());
            // error_log("ERROR: Stack trace: " . $e->getTraceAsString());
            // error_log("=== DEBUG: Gemini AI analysis failed ===");
            return ['response' => '', 'http_code' => 0, 'error' => $e->getMessage(), 'json' => null];
        }
    }
}
?>
