<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal Access</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);">
        <h2 style="margin: 0 0 12px; color: #0f172a;">Welcome, {{ $user->name }}</h2>
        <p style="margin: 0 0 16px; color: #475569;">Your employee account has been created. Use the credentials below to access the portal.</p>
        <div style="background: #f1f5f9; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
            <p style="margin: 0 0 8px; color: #334155; font-weight: 600;">Login Details</p>
            <p style="margin: 0; color: #475569;">Email: {{ $user->email }}</p>
            <p style="margin: 0; color: #475569;">Password: {{ $password }}</p>
        </div>
        <a href="{{ route('login') }}" style="display: inline-block; background: #16a34a; color: #ffffff; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 600;">Login to Portal</a>
        <p style="margin: 16px 0 0; color: #94a3b8; font-size: 12px;">For security, please change your password after logging in.</p>
    </div>
</body>
</html>
