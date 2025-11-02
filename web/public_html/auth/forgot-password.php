<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - ระบบเตือนภัยดินสไลด์</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #E8F5E8 0%, #F1F8E9 50%, #66BB6A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.6s ease-out;
            border: 1px solid #A5D6A7;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .header {
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        .header img {
            width: 70px;
            height: 70px;
            margin-bottom: 20px;
            background: white;
            border-radius: 50%;
            padding: 15px;
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .header p {
            opacity: 0.95;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .form-container {
            padding: 45px 35px;
            background: #FAFAFA;
        }

        .form-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .form-title h2 {
            color: #2E7D32;
            font-size: 26px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .form-title p {
            color: #757575;
            font-size: 16px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2E7D32;
            font-weight: 600;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid #A5D6A7;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            font-family: 'Sarabun', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #66BB6A;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 187, 106, 0.15);
            transform: translateY(-1px);
        }

        .form-group input::placeholder {
            color: #9E9E9E;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: white;
            border: none;
            padding: 18px 20px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-family: 'Sarabun', sans-serif;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 187, 106, 0.4);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .submit-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.success {
            background: linear-gradient(135deg, #E8F5E8, #F1F8E9);
            color: #2E7D32;
            border: 1px solid #A5D6A7;
        }

        .alert.error {
            background: linear-gradient(135deg, #FFEBEE, #FFF3E0);
            color: #C62828;
            border: 1px solid #FFCDD2;
        }

        .alert.warning {
            background: linear-gradient(135deg, #FFF8E1, #FFF3E0);
            color: #E65100;
            border: 1px solid #FFECB3;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #E0E0E0;
        }

        .back-link a {
            color: #66BB6A;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #4CAF50;
        }

        .rate-limit-info {
            background: linear-gradient(135deg, #E8F5E8, #F1F8E9);
            border: 1px solid #A5D6A7;
            color: #2E7D32;
            padding: 15px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 25px;
            border-left: 4px solid #66BB6A;
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
            }

            .container {
                margin: 5px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(139, 195, 74, 0.15);
            }

            .form-container {
                padding: 24px 16px;
            }

            .header {
                padding: 20px 16px;
            }

            .header h1 {
                font-size: 18px;
                font-weight: 600;
                line-height: 1.3;
                margin-bottom: 6px;
            }

            .header p {
                font-size: 12px;
                line-height: 1.4;
                margin-top: 6px;
            }

            .header img {
                width: 45px;
                height: 45px;
                margin-bottom: 12px;
            }

            .form-title h2 {
                font-size: 20px;
                font-weight: 600;
                margin-bottom: 8px;
                line-height: 1.3;
            }

            .form-title p {
                font-size: 13px;
                line-height: 1.5;
                margin-bottom: 16px;
            }

            .rate-limit-info {
                font-size: 12px;
                line-height: 1.4;
                padding: 12px 14px;
                margin-bottom: 20px;
                border-radius: 8px;
            }

            .form-group label {
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 6px;
            }

            .form-group input {
                font-size: 14px;
                padding: 12px 14px;
                border-radius: 8px;
                line-height: 1.4;
            }

            .submit-btn {
                font-size: 14px;
                font-weight: 600;
                padding: 14px 24px;
                margin-top: 20px;
                border-radius: 8px;
                letter-spacing: 0.3px;
            }

            .back-link {
                margin-top: 20px;
                text-align: center;
            }

            .back-link a {
                font-size: 13px;
                line-height: 1.4;
            }

            .alert {
                font-size: 13px;
                line-height: 1.4;
                padding: 12px 14px;
                border-radius: 8px;
                margin-bottom: 16px;
            }

            small {
                font-size: 11px !important;
                line-height: 1.3;
            }

            small a {
                font-size: 11px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTIwIDM4QzMwLjQ5MzQgMzggMzkgMjkuNDkzNCAzOSAyMUM5IDEwLjUwNjYgMzAuNDkzNCAyIDIwIDJDOS41MDY2IDIgMSAxMC41MDY2IDEgMjBDMSAyOS40OTM0IDkuNTA2NiAzOCAyMCAzOFoiIGZpbGw9IiM2NjdlZWEiLz4KPHBhdGggZD0iTTE2IDI2SDE4VjI4SDE2VjI2WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTIyIDI2SDI0VjI4SDIyVjI2WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTEyIDIyQzEyIDIwLjg5NTQgMTIuODk1NCAyMCAxNCAyMEgyNkMyNy4xMDQ2IDIwIDI4IDIwLjg5NTQgMjggMjJWMjRDMjggMjUuMTA0NiAyNy4xMDQ2IDI2IDI2IDI2SDE0QzEyLjg5NTQgMjYgMTIgMjUuMTA0NiAxMiAyNFYyMloiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0xNiAxNkMxNiAxNC4zNDMxIDE3LjM0MzEgMTMgMTkgMTNIMjFDMjIuNjU2OSAxMyAyNCAxNC4zNDMxIDI0IDE2VjE4SDE2VjE2WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+" alt="Landslide Alert Logo">
            <h1>ระบบเตือนภัยดินสไลด์</h1>
            <p>ระบบรักษาความปลอดภัย</p>
        </div>

        <div class="form-container">
            <div class="form-title">
                <h2>ลืมรหัสผ่าน?</h2>
                <p>กรอกที่อยู่อีเมลของคุณ และเราจะส่งลิงก์สำหรับรีเซ็ตรหัสผ่านให้คุณ</p>
            </div>

            <div class="rate-limit-info">
                <strong>หมายเหตุ:</strong> คุณสามารถขอรีเซ็ตรหัสผ่านได้สูงสุด 5 ครั้งใน 12 ชั่วโมง เพื่อความปลอดภัย
            </div>

            <div id="alert" class="alert"></div>

            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label for="email">ที่อยู่อีเมล</label>
                    <input type="email" id="email" name="email" placeholder="กรอกที่อยู่อีเมลของคุณ" required>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="spinner" id="spinner"></span>
                    <span id="btnText">ส่งลิงก์รีเซ็ต</span>
                </button>
            </form>

            <div class="back-link">
                <a href="/login">&larr; กลับสู่หน้าเข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const btnText = document.getElementById('btnText');
        const alert = document.getElementById('alert');

        function showAlert(message, type) {
            alert.textContent = message;
            alert.className = `alert ${type}`;
            alert.style.display = 'block';
            
            // Auto hide success messages
            if (type === 'success') {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            }
        }

        function hideAlert() {
            alert.style.display = 'none';
        }

        function setLoading(loading) {
            if (loading) {
                submitBtn.disabled = true;
                spinner.style.display = 'inline-block';
                btnText.textContent = 'กำลังส่ง...';
            } else {
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'ส่งลิงก์รีเซ็ต';
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                showAlert('กรุณากรอกที่อยู่อีเมลของคุณ', 'error');
                return;
            }

            setLoading(true);

            try {
                // Build API URL using current domain
                const apiUrl = window.location.origin + '/api/auth/forgot-password.php';
                
                console.log('API URL:', apiUrl);
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });
                if (!response.ok) {
                    console.error('HTTP error:', response.status, response);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Check if response has content
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    showAlert(`เซิร์ฟเวอร์เกิดข้อผิดพลาด: รูปแบบการตอบกลับไม่ถูกต้อง สถานะ: ${response.status}`, 'error');
                    return;
                }

                console.log('Parsed response:', data);
                
                if (data.success) {
                    showAlert('หากมีบัญชีผู้ใช้ที่มีอีเมลนี้อยู่จริง เราได้ส่งลิงก์รีเซ็ตรหัสผ่านแล้ว กรุณาตรวจสอบอีเมลของคุณ', 'success');
                    form.reset();
                } else {
                    // จัดการกับ error codes ต่างๆ
                    switch (data.error_code) {
                        case 'RATE_LIMIT_EXCEEDED':
                            showAlert(`คำขอรีเซ็ตมากเกินไป คุณสามารถลองได้อีก ${data.remaining_attempts || 0} ครั้ง กรุณาลองใหม่อีกครั้งหลังจาก ${data.reset_after || '12 ชั่วโมง'}`, 'warning');
                            break;
                        case 'VALIDATION_FAILED':
                            showAlert('ข้อมูลที่กรอกไม่ถูกต้อง กรุณาตรวจสอบอีเมลของคุณ', 'error');
                            break;
                        case 'DATABASE_CONNECTION_ERROR':
                            showAlert('ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้ กรุณาลองใหม่อีกครั้งภายหลัง', 'error');
                            break;
                        case 'EMAIL_SEND_FAILED':
                        case 'SMTP_CONNECTION_ERROR':
                        case 'SMTP_AUTHENTICATION_ERROR':
                            showAlert('ไม่สามารถส่งอีเมลได้ในขณะนี้ กรุณาลองใหม่อีกครั้งภายหลัง', 'error');
                            break;
                        case 'SERVICE_UNAVAILABLE':
                            showAlert('ระบบไม่พร้อมให้บริการชั่วคราว กรุณาลองใหม่อีกครั้งภายหลัง', 'error');
                            break;
                        default:
                            const errorMsg = data.message || data.error || 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
                            showAlert(`ข้อผิดพลาด: ${errorMsg}`, 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตและลองใหม่อีกครั้ง', 'error');
            } finally {
                setLoading(false);
            }
        });

        // Real-time email validation
        document.getElementById('email').addEventListener('input', (e) => {
            const email = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                e.target.style.borderColor = '#dc3545';
            } else {
                e.target.style.borderColor = '#e1e5e9';
            }
            
            hideAlert();
        });
    </script>
</body>
</html>
