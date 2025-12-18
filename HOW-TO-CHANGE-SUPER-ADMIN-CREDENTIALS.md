# How to Change Super Admin Username and Password

There are **multiple ways** to change your Super Administrator username and password. Choose the method that works best for you:

## 🔐 Method 1: Edit User Page (Recommended)

**Best for:** Changing both username and password at once, or updating other user details.

### Steps:

1. **Login to Admin Panel**
   - Go to: `https://s3vtgroup.com.kh/admin/login.php`
   - Login with your current credentials

2. **Navigate to Users Page**
   - Click on **"Users"** in the sidebar menu
   - Or go directly to: `https://s3vtgroup.com.kh/admin/users.php`

3. **Edit Your Account**
   - Find your user account in the list (marked with "(You)")
   - Click the **Edit icon** (pencil) next to your username

4. **Change Username and/or Password**
   - **Username:** Change the username in the "Username" field
   - **Password:** Enter new password in the "Password" field (leave blank to keep current)
   - **Note:** Password must be at least 6 characters

5. **Save Changes**
   - Click **"Save User"** button
   - If you changed your password, you'll see: "Password changed successfully. You may need to log in again."

6. **Re-login (if password changed)**
   - Logout and login again with your new credentials

---

## 🔑 Method 2: Change Password Page

**Best for:** Only changing password (not username) when logged in.

### Steps:

1. **Login to Admin Panel**
   - Login with your current credentials

2. **Go to Change Password**
   - Navigate to: `https://s3vtgroup.com.kh/admin/change-password.php`
   - Or look for "Change Password" in the user menu

3. **Enter Current and New Password**
   - **Current Password:** Enter your current password
   - **New Password:** Enter your new password (minimum 6 characters)
   - **Confirm Password:** Re-enter your new password

4. **Save**
   - Click **"Change Password"** button
   - You'll see: "Password changed successfully."

5. **Re-login**
   - Logout and login again with your new password

---

## 📧 Method 3: Password Reset via Email

**Best for:** When you've forgotten your password or want to reset it via email.

### Steps:

1. **Go to Forgot Password Page**
   - Visit: `https://s3vtgroup.com.kh/admin/forgot-password.php`
   - Or click **"Forgot Password?"** link on the login page

2. **Enter Your Email**
   - Enter the email address associated with your Super Admin account
   - Click **"Send Reset Link"**

3. **Check Your Email**
   - Look for an email with subject: "Password Reset Request - Admin Panel"
   - Click the reset link in the email
   - **Note:** Link expires in 1 hour

4. **Reset Your Password**
   - Enter your new password (minimum 6 characters)
   - Confirm your new password
   - Click **"Reset Password"**

5. **Login**
   - You'll be redirected to the login page
   - Login with your username and new password

---

## 👥 Method 4: Admin Sends Reset Email (For Another Admin)

**Best for:** When another admin needs to send you a password reset link.

### Steps:

1. **Another Admin Logs In**
   - Admin with "edit_users" permission logs in

2. **Go to Users Page**
   - Navigate to: `https://s3vtgroup.com.kh/admin/users.php`

3. **Send Reset Email**
   - Find your account in the list
   - Click the **Key icon** (🔑) in the Actions column
   - Confirm to send password reset email

4. **You Receive Email**
   - Check your email for the reset link
   - Follow the reset process (same as Method 3, steps 4-5)

---

## ⚠️ Important Notes

### Username Changes:
- ✅ **Allowed:** You can change your username at any time
- ✅ **No Restrictions:** Super Admin can change their own username
- ⚠️ **Remember:** After changing username, you must use the new username to login

### Password Changes:
- ✅ **Minimum Length:** Password must be at least 6 characters
- ✅ **Security:** Passwords are hashed with bcrypt
- ⚠️ **Re-login Required:** After changing password, you may need to logout and login again

### Super Admin Account:
- ✅ **No Restrictions:** Super Admin can change their own username and password
- ✅ **Cannot Deactivate:** You cannot deactivate your own account (security feature)
- ✅ **Always Active:** Your account will always remain active

---

## 🎯 Quick Reference

| Method | Change Username? | Change Password? | Requires Login? |
|--------|-----------------|------------------|----------------|
| Edit User Page | ✅ Yes | ✅ Yes | ✅ Yes |
| Change Password Page | ❌ No | ✅ Yes | ✅ Yes |
| Email Reset | ❌ No | ✅ Yes | ❌ No |
| Admin Sends Reset | ❌ No | ✅ Yes | ❌ No (for you) |

---

## 🔒 Security Best Practices

1. **Use Strong Passwords:**
   - Minimum 8+ characters (system requires 6, but use more)
   - Mix of uppercase, lowercase, numbers, and symbols
   - Don't use common words or personal information

2. **Change Default Credentials:**
   - Change from default `admin` / `admin` immediately
   - Use a unique username (not "admin")

3. **Regular Updates:**
   - Change password periodically (every 90 days recommended)
   - Don't reuse old passwords

4. **Keep Email Secure:**
   - Ensure your email account is secure
   - Password reset links are sent to your email

---

## 🆘 Troubleshooting

### "Username or email already exists"
- **Cause:** Another user already has that username/email
- **Solution:** Choose a different username/email

### "Password must be at least 6 characters long"
- **Cause:** Password is too short
- **Solution:** Use a password with at least 6 characters

### "Invalid or expired reset token"
- **Cause:** Reset link expired (1 hour) or already used
- **Solution:** Request a new password reset link

### "You cannot deactivate your own account"
- **Cause:** Trying to deactivate your own account
- **Solution:** This is a security feature - your account must remain active

### Email Not Received
- **Check:** Spam/junk folder
- **Check:** Email address is correct in your account
- **Check:** Email server configuration
- **Solution:** Use Method 1 or 2 instead (requires login)

---

## ✅ Success Checklist

After changing credentials:
- [ ] Username changed (if applicable)
- [ ] Password changed
- [ ] Can login with new credentials
- [ ] Old credentials no longer work
- [ ] Email address is correct (for future resets)

---

**Need Help?** If you're locked out and can't access any of these methods, contact your system administrator or check the database directly.

