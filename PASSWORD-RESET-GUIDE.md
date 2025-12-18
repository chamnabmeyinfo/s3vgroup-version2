# Password Reset Feature Guide

## ✅ Setup Complete!

The password reset via email feature has been successfully implemented for all admin users, including Super Administrators.

## 🚀 Quick Setup

### 1. Create Database Table

Run the setup script to create the required database table:

```bash
php setup-password-reset.php
```

Or manually import the SQL file:

```bash
mysql -u your_user -p your_database < database/password-reset.sql
```

### 2. Verify Setup

After running the setup, you should see:
- ✅ Password reset feature setup completed successfully!

## 📋 Features

### For Users (Self-Service)

1. **Forgot Password Link**
   - Available on the login page (`admin/login.php`)
   - Click "Forgot Password?" link
   - Enter your email address
   - Receive password reset link via email
   - Link expires in 1 hour

2. **Reset Password Page**
   - Access via email link: `admin/reset-password.php?token=...`
   - Enter new password (minimum 6 characters)
   - Automatically redirects to login after success

### For Administrators

1. **Send Password Reset from Users Page**
   - Go to `admin/users.php`
   - Click the key icon (🔑) next to any user
   - Confirm to send password reset email
   - Works for all users including Super Admin

2. **Super Admin Password Reset**
   - Super Administrators can now reset their password via email
   - No restrictions - works the same as other users
   - Can be reset by another admin or self-service

## 🔧 How It Works

1. **Request Reset:**
   - User enters email on forgot password page
   - System generates secure token (32 bytes, hex encoded)
   - Token stored in `password_reset_tokens` table
   - Email sent with reset link

2. **Reset Password:**
   - User clicks link in email
   - System validates token (checks expiry, usage status)
   - User enters new password
   - Password updated in database
   - Token marked as used

3. **Security Features:**
   - Tokens expire after 1 hour
   - Tokens can only be used once
   - Old tokens are invalidated when new one is created
   - Secure random token generation
   - Password hashing with bcrypt

## 📧 Email Configuration

The system uses the existing email queue system:

1. **Email Queue (Preferred):**
   - Emails are queued in `email_queue` table
   - Processed by email cron job
   - More reliable for production

2. **Direct Mail (Fallback):**
   - If queue fails, uses PHP `mail()` function
   - Requires server mail configuration
   - Works immediately but less reliable

### Configure Email Settings

Make sure your email settings are configured in:
- `config/app.php` - Site name and URL
- Email queue processing (if using queue)
- Server mail configuration (if using direct mail)

## 🎯 Usage Examples

### User Self-Service Reset

1. Go to: `http://localhost:8080/admin/login.php`
2. Click: "Forgot Password?"
3. Enter: Your email address
4. Check: Your email inbox
5. Click: Reset link in email
6. Enter: New password
7. Login: With new password

### Admin Sends Reset for User

1. Go to: `admin/users.php`
2. Find: User in the list
3. Click: Key icon (🔑) in Actions column
4. Confirm: Send reset email
5. User receives: Email with reset link

### Super Admin Reset

Super Administrators can use either method:
- Self-service via "Forgot Password" link
- Another admin can send reset email
- No special restrictions

## 🔒 Security Notes

- **Token Expiry:** 1 hour (configurable in code)
- **One-Time Use:** Tokens are marked as used after password reset
- **Secure Generation:** Uses `random_bytes()` for cryptographically secure tokens
- **Password Hashing:** Uses PHP `password_hash()` with bcrypt
- **Email Privacy:** Doesn't reveal if email exists (security best practice)

## 🐛 Troubleshooting

### "Password reset feature is not set up"
- **Solution:** Run `php setup-password-reset.php` to create the table

### "Failed to send email"
- **Check:** Email queue table exists
- **Check:** Server mail configuration
- **Check:** Email address is valid

### "Invalid or expired reset token"
- **Cause:** Token expired (1 hour) or already used
- **Solution:** Request a new reset link

### Email not received
- **Check:** Spam/junk folder
- **Check:** Email address is correct
- **Check:** Server mail configuration
- **Check:** Email queue processing (if using queue)

## 📝 Files Created/Modified

### New Files:
- `admin/forgot-password.php` - Request password reset
- `admin/reset-password.php` - Reset password with token
- `database/password-reset.sql` - Database table schema
- `setup-password-reset.php` - Setup script

### Modified Files:
- `admin/login.php` - Added "Forgot Password?" link
- `admin/users.php` - Added "Send Reset" button
- `app/Helpers/EmailHelper.php` - Added `sendPasswordReset()` method

## ✅ Testing Checklist

- [ ] Database table created successfully
- [ ] "Forgot Password" link appears on login page
- [ ] Can request password reset with email
- [ ] Email received with reset link
- [ ] Reset link works and validates token
- [ ] Can set new password via reset link
- [ ] Can login with new password
- [ ] "Send Reset" button works in users page
- [ ] Super Admin can reset password
- [ ] Tokens expire after 1 hour
- [ ] Used tokens cannot be reused

## 🎉 Success!

The password reset feature is now fully functional for all users, including Super Administrators!

---

**Need Help?** Check the troubleshooting section or review the code comments in the new files.

