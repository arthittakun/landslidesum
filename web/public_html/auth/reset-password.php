<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีเซ็ตรหัสผ่าน - ระบบเตือนภัยดินสไลด์</title>
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

        .token-info {
            background: linear-gradient(135deg, #E8F5E8, #F1F8E9);
            border: 1px solid #A5D6A7;
            color: #2E7D32;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 28px;
            text-align: center;
            border-left: 4px solid #66BB6A;
        }

        .token-info.expired {
            background: linear-gradient(135deg, #FFEBEE, #FFF3E0);
            border-color: #FFCDD2;
            color: #C62828;
            border-left-color: #F44336;
        }

        .countdown {
            font-weight: 600;
            font-size: 18px;
            color: #66BB6A;
        }

        .form-group {
            margin-bottom: 28px;
            position: relative;
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
            padding: 18px 55px 18px 20px;
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

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50px;
            background: none;
            border: none;
            color: #757575;
            cursor: pointer;
            font-size: 20px;
            padding: 8px;
            transition: color 0.3s;
            border-radius: 50%;
        }

        .password-toggle:hover {
            color: #66BB6A;
            background: rgba(102, 187, 106, 0.1);
        }

        .password-strength {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            display: none;
        }

        .strength-weak {
            background: linear-gradient(135deg, #FFEBEE, #FFF3E0);
            color: #C62828;
            border: 1px solid #FFCDD2;
        }

        .strength-medium {
            background: linear-gradient(135deg, #FFF8E1, #FFF3E0);
            color: #E65100;
            border: 1px solid #FFECB3;
        }

        .strength-strong {
            background: linear-gradient(135deg, #E8F5E8, #F1F8E9);
            color: #2E7D32;
            border: 1px solid #A5D6A7;
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

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 187, 106, 0.4);
        }

        .submit-btn:hover:not(:disabled)::before {
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

        .loading-state {
            text-align: center;
            padding: 40px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e1e5e9;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
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

            .loading-state p {
                font-size: 13px;
                line-height: 1.4;
                margin-top: 16px;
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

            .password-input-container {
                position: relative;
            }

            .toggle-password {
                width: 38px;
                height: 38px;
                font-size: 16px;
            }

            .password-strength-meter {
                margin-top: 8px;
            }

            .password-strength-text {
                font-size: 12px;
                margin-top: 4px;
                line-height: 1.3;
            }

            .password-requirements {
                margin-top: 12px;
            }

            .password-requirements h4 {
                font-size: 13px;
                margin-bottom: 8px;
            }

            .password-requirements ul {
                margin-left: 16px;
            }

            .password-requirements li {
                font-size: 12px;
                line-height: 1.4;
                margin-bottom: 4px;
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

            .success-state h3 {
                font-size: 18px;
                margin-bottom: 12px;
                line-height: 1.3;
            }

            .success-state p {
                font-size: 13px;
                line-height: 1.5;
                margin-bottom: 16px;
            }

            .error-state h3 {
                font-size: 18px;
                margin-bottom: 12px;
                line-height: 1.3;
            }

            .error-state p {
                font-size: 13px;
                line-height: 1.5;
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
            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="loading-spinner"></div>
                <p>กำลังตรวจสอบโทเค็นรีเซ็ต...</p>
            </div>

            <!-- Main Form -->
            <div id="mainForm" style="display: none;">
                <div class="form-title">
                    <h2>รีเซ็ตรหัสผ่าน</h2>
                    <p>กรอกรหัสผ่านใหม่ของคุณ</p>
                </div>

                <div id="tokenInfo" class="token-info">
                    <div>โทเค็นหมดอายุใน: <span class="countdown" id="countdown"></span></div>
                    <div style="font-size: 12px; margin-top: 5px;">
                        ลิงก์รีเซ็ตสำหรับ: <strong id="userEmail"></strong>
                    </div>
                </div>

                <div id="alert" class="alert"></div>

                <form id="resetPasswordForm">
                    <div class="form-group">
                        <label for="newPassword">รหัสผ่านใหม่</label>
                        <input type="password" id="newPassword" name="new_password" placeholder="กรอกรหัสผ่านใหม่" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">👁️</button>
                        <div id="passwordStrength" class="password-strength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">👁️</button>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="spinner" id="spinner"></span>
                        <span id="btnText">รีเซ็ตรหัสผ่าน</span>
                    </button>
                </form>
            </div>

            <!-- Error State -->
            <div id="errorState" style="display: none; text-align: center; padding: 20px;">
                <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                <h3 style="color: #C62828; margin-bottom: 10px;">ลิงก์ไม่ถูกต้องหรือหมดอายุ</h3>
                <p style="color: #757575; margin-bottom: 25px;">ลิงก์รีเซ็ตรหัสผ่านนี้ไม่ถูกต้องหรือหมดอายุแล้ว</p>
                <a href="/auth/forgot-password" style="color: #66BB6A; text-decoration: none; font-weight: 600;">ขอลิงก์รีเซ็ตใหม่</a>
            </div>

            <div class="back-link">
                <a href="/login">&larr; กลับสู่หน้าเข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

    <script>
        let countdownInterval;
        let tokenData;

        // Get token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const resetToken = urlParams.get('token');

        if (!resetToken) {
            showErrorState();
        } else {
            verifyToken(resetToken);
        }

        async function verifyToken(token) {
            try {
                const response = await fetch(`/api/auth/verify-reset-token?token=${encodeURIComponent(token)}`);
                const data = await response.json();

                if (data.success && data.valid) {
                    tokenData = data.data;
                    showMainForm();
                    startCountdown(data.data.time_left_seconds);
                    document.getElementById('userEmail').textContent = data.data.email;
                } else {
                    showErrorState();
                }
            } catch (error) {
                console.error('Token verification error:', error);
                showErrorState();
            }
        }

        function showMainForm() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('mainForm').style.display = 'block';
        }

        function showErrorState() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
        }

        function startCountdown(seconds) {
            const countdownElement = document.getElementById('countdown');
            const tokenInfoElement = document.getElementById('tokenInfo');
            
            function updateCountdown() {
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    tokenInfoElement.className = 'token-info expired';
                    countdownElement.textContent = 'หมดอายุแล้ว';
                    document.getElementById('submitBtn').disabled = true;
                    showAlert('ลิงก์รีเซ็ตนี้หมดอายุแล้ว กรุณาขอลิงก์ใหม่', 'error');
                    return;
                }

                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                countdownElement.textContent = `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
                seconds--;
            }

            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        function checkPasswordStrength(password) {
            const strengthElement = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthElement.style.display = 'none';
                return;
            }

            strengthElement.style.display = 'block';
            
            let score = 0;
            let feedback = [];

            // Length check
            if (password.length >= 8) score++;
            else feedback.push('อย่างน้อย 8 ตัวอักษร');

            // Character variety checks
            if (/[a-z]/.test(password)) score++;
            else feedback.push('ตัวอักษรพิมพ์เล็ก');

            if (/[A-Z]/.test(password)) score++;
            else feedback.push('ตัวอักษรพิมพ์ใหญ่');

            if (/[0-9]/.test(password)) score++;
            else feedback.push('ตัวเลข');

            if (/[^A-Za-z0-9]/.test(password)) score++;
            else feedback.push('อักขระพิเศษ');

            if (score <= 2) {
                strengthElement.className = 'password-strength strength-weak';
                strengthElement.textContent = `รหัสผ่านอ่อน ต้องการ: ${feedback.slice(0, 2).join(', ')}`;
            } else if (score <= 3) {
                strengthElement.className = 'password-strength strength-medium';
                strengthElement.textContent = 'รหัสผ่านความแข็งแกร่งปานกลาง';
            } else {
                strengthElement.className = 'password-strength strength-strong';
                strengthElement.textContent = 'รหัสผ่านแข็งแกร่ง ✓';
            }
        }

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert ${type}`;
            alert.style.display = 'block';
            
            if (type === 'success') {
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
            }
        }

        function hideAlert() {
            document.getElementById('alert').style.display = 'none';
        }

        function setLoading(loading) {
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btnText');
            
            if (loading) {
                submitBtn.disabled = true;
                spinner.style.display = 'inline-block';
                btnText.textContent = 'กำลังรีเซ็ต...';
            } else {
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'รีเซ็ตรหัสผ่าน';
            }
        }

        // Event Listeners
        document.getElementById('newPassword').addEventListener('input', (e) => {
            checkPasswordStrength(e.target.value);
            hideAlert();
        });

        document.getElementById('confirmPassword').addEventListener('input', (e) => {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = e.target.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                e.target.style.borderColor = '#F44336';
            } else {
                e.target.style.borderColor = '#A5D6A7';
            }
            hideAlert();
        });

        document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showAlert('รหัสผ่านไม่ตรงกัน', 'error');
                return;
            }

            if (newPassword.length < 8) {
                showAlert('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร', 'error');
                return;
            }

            setLoading(true);

            try {
                const response = await fetch('/api/auth/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token: resetToken,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    })
                });

                const data = await response.json();

                if (data.success) {
                    clearInterval(countdownInterval);
                    showAlert('รีเซ็ตรหัสผ่านสำเร็จ! กำลังเปลี่ยนเส้นทางไปหน้าเข้าสู่ระบบ...', 'success');
                } else {
                    showAlert(data.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตและลองใหม่อีกครั้ง', 'error');
            } finally {
                setLoading(false);
            }
        });
    </script>
</body>
</html>
