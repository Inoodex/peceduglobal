<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to PecEduGlobal</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-w-600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        
        <h2 style="color: #333333; margin-top: 0;">Welcome to PecEduGlobal!</h2>
        
        <p style="color: #555555; font-size: 16px; line-height: 1.5;">
            Hello <strong>{{ $studentName }}</strong>,
        </p>

        <p style="color: #555555; font-size: 16px; line-height: 1.5;">
            Your student account has been successfully created.
            @if($consultantName)
                Your account was set up by your consultant, <strong>{{ $consultantName }}</strong>.
            @endif
        </p>

        <div style="background-color: #f9f9f9; border-left: 4px solid #4F46E5; padding: 15px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0; color: #333;"><strong>Your Login Credentials:</strong></p>
            <p style="margin: 0; color: #555;">Email: <strong>{{ $studentEmail }}</strong></p>
            <p style="margin: 5px 0 0 0; color: #555;">Password: <strong>{{ $password }}</strong></p>
        </div>

        <p style="color: #555555; font-size: 16px; line-height: 1.5;">
            Please log in to complete your profile and start your journey with us. We recommend changing your password after your first login for security purposes.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $loginUrl }}" style="background-color: #4F46E5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Log In to Dashboard</a>
        </div>

        <p style="color: #888888; font-size: 14px; margin-top: 30px; border-top: 1px solid #eeeeee; padding-top: 20px;">
            If you have any questions, please contact your consultant or our support team.<br>
            Best regards,<br>
            <strong>The PecEduGlobal Team</strong>
        </p>
    </div>
</body>
</html>
