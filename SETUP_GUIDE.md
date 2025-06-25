# Email Verification Setup Guide

## Overview
This guide will help you set up email verification for your registration system. The system now includes:
- Email verification with secure tokens
- Database structure for verification
- Proper error handling and logging
- User-friendly verification process

## Step 1: Database Setup

1. **Run the database setup script:**
   - Open your browser and go to: `http://localhost/itpclogin/setup_database.php`
   - This will create/update the users table with required columns
   - Delete `setup_database.php` after running it

## Step 2: Email Configuration

### For Gmail Users:

1. **Enable 2-Factor Authentication:**
   - Go to your Google Account settings
   - Navigate to Security
   - Enable 2-Step Verification

2. **Generate an App Password:**
   - Go to Google Account settings → Security
   - Under "2-Step Verification", click "App passwords"
   - Select "Mail" and "Other (Custom name)"
   - Enter a name like "ITPC Login System"
   - Copy the generated 16-character password

3. **Update config.php:**
   ```php
   $email_config = [
       'smtp_host' => 'smtp.gmail.com',
       'smtp_port' => 587,
       'smtp_username' => 'your-actual-gmail@gmail.com', // Your Gmail address
       'smtp_password' => 'your-16-char-app-password', // The app password you generated
       'from_email' => 'your-actual-gmail@gmail.com', // Your Gmail address
       'from_name' => 'ITPC Login System' // Your website name
   ];
   ```

### For Outlook/Hotmail Users:

1. **Update config.php:**
   ```php
   $email_config = [
       'smtp_host' => 'smtp-mail.outlook.com',
       'smtp_port' => 587,
       'smtp_username' => 'your-email@outlook.com',
       'smtp_password' => 'your-password',
       'from_email' => 'your-email@outlook.com',
       'from_name' => 'ITPC Login System'
   ];
   ```

## Step 3: Test Email Configuration

1. **Test your email setup:**
   - Go to: `http://localhost/itpclogin/test_email.php`
   - Enter your email address
   - Click "Send Test Email"
   - Check your inbox (and spam folder)

2. **If the test fails:**
   - Check your email configuration in `config.php`
   - Verify your Gmail app password is correct
   - Make sure 2-Factor Authentication is enabled
   - Check error logs in your server's error log

## Step 4: Update Site Configuration

Update the site configuration in `config.php`:
```php
$site_config = [
    'base_url' => 'http://localhost/itpclogin', // Change to your actual domain
    'site_name' => 'ITPC Login System'
];
```

## Step 5: Test Registration

1. **Register a new user:**
   - Go to your registration page
   - Fill in the form with a real email address
   - Submit the registration

2. **Check for verification email:**
   - Look in your email inbox
   - Check spam folder if not found
   - Click the verification link

3. **Verify the process:**
   - You should see a success message
   - Try logging in with the new account

## Troubleshooting

### Common Issues:

1. **"Email could not be sent" error:**
   - Check your email configuration in `config.php`
   - Verify your Gmail app password
   - Make sure 2-Factor Authentication is enabled

2. **"Invalid verification link" error:**
   - Check that `base_url` in `config.php` matches your actual domain
   - Verify the verification token is being generated correctly

3. **Database errors:**
   - Run `setup_database.php` to ensure proper table structure
   - Check your database connection settings

4. **PHPMailer errors:**
   - Verify PHPMailer files are in the `PHPMailer/` directory
   - Check PHP error logs for detailed error messages

### Debug Mode:

To enable debug mode for email testing, change this line in `register.php`:
```php
$mail->SMTPDebug = 2; // Change from 0 to 2 for debugging
```

## Security Notes:

1. **Delete setup files after use:**
   - Delete `setup_database.php` after running it
   - Delete `test_email.php` after testing
   - Delete `email_setup.php` after configuration

2. **Protect your credentials:**
   - Never commit `config.php` with real credentials to version control
   - Use environment variables in production

3. **Token security:**
   - Verification tokens expire after 24 hours
   - Tokens are cleared after successful verification

## Files Overview:

- `config.php` - Email and database configuration
- `register.php` - Registration with email verification
- `verify.php` - Email verification page
- `login.php` - Login with verification check
- `setup_database.php` - Database setup (delete after use)
- `test_email.php` - Email testing (delete after use)

## Production Deployment:

1. **Update domain settings:**
   - Change `base_url` to your actual domain
   - Update `site_name` to your website name

2. **Security measures:**
   - Use HTTPS in production
   - Set up proper error logging
   - Configure email rate limiting

3. **Email provider:**
   - Consider using a dedicated email service (SendGrid, Mailgun, etc.)
   - Set up proper SPF/DKIM records

## Support:

If you encounter issues:
1. Check the error logs
2. Verify your email configuration
3. Test with the email testing tool
4. Ensure all required files are present 