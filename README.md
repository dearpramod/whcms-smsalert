# WHMCS AakashSMS Module

A WHMCS addon module for sending SMS notifications using AakashSMS gateway.

## Features

- Send SMS notifications for various WHMCS events
- OTP verification for customer phone numbers
- Admin notifications
- Customer notifications for:
  - New registrations
  - Invoice creation and payment
  - Service activation/suspension
  - Domain registration/renewal
  - Ticket updates
  - And more...

## Installation

1. Upload the module files to your WHMCS `/modules/addons/` directory
2. Go to WHMCS Admin → Setup → Addon Modules
3. Find "AakashSMS" and click "Activate"
4. Configure the module with your AakashSMS API credentials

## Configuration

1. Get your API token from AakashSMS dashboard
2. Go to WHMCS Admin → Addons → AakashSMS
3. Enter your API Token and Sender ID
4. Configure SMS templates as needed
5. Enable/disable specific notification types

## API Requirements

This module uses AakashSMS API v4. You need:
- Valid AakashSMS account
- API Token
- Approved Sender ID

## Supported Endpoints

- Send SMS: `POST /admin/public/sms/v4/send`
- Check Balance: `POST /admin/public/sms/v4/balance`
- Delivery Status: `POST /admin/public/sms/v4/status`

## Phone Number Format

The module automatically formats phone numbers for Nepal:
- Accepts: 9812345678, 97798123456789, +977-9812345678
- Converts to: 9812345678 (standard Nepal mobile format)

## Support

For support and documentation:
- Website: https://www.aakashsms.com
- API Documentation: https://bitbucket.org/aakashsms/api/src/v4/
- Email: support@aakashsms.com

## License

This module is licensed under GPL v3.