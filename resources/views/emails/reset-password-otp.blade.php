<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

        <h2 style="color: #333333; margin-top: 0;">Reset Your Password</h2>

        <p style="color: #555555; font-size: 16px; line-height: 1.5;">
            Hello <strong>{{ $userName }}</strong>,
        </p>

        <p style="color: #555555; font-size: 16px; line-height: 1.5;">
            We received a request to reset the password for your account. Use the code below to complete the process.
            This code expires in <strong>{{ $ttlMinutes }} minutes</strong>.
        </p>

        <div style="text-align: center; background-color: #f9f9f9; border-left: 4px solid #4F46E5; padding: 20px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0; color: #333;"><strong>Your Reset Code:</strong></p>
            <p style="margin: 0; color: #4F46E5; font-size: 32px; font-weight: bold; letter-spacing: 6px;">{{ $otp }}</p>
        </div>

        <p style="color: #555555; font-size: 16px; line-height: 1.5;">
            If you did not request a password reset, you can safely ignore this email — your account remains secure and no changes have been made.
        </p>

        <p style="color: #888888; font-size: 14px; margin-top: 30px; border-top: 1px solid #eeeeee; padding-top: 20px;">
            For your security, never share this code with anyone. Our team will never ask for it.<br>
            Best regards,<br>
            <strong>The {{ config('app.name', 'PecEduGlobal') }} Team</strong>
        </p>
    </div>
</body>
</html>
