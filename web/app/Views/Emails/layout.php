<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
    <title><?= esc($title ?? 'Mpesa Analyzer') ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6; 
            color: #1a1a2e; 
            background-color: #f3f4f6; 
            -webkit-text-size-adjust: 100%; 
            -ms-text-size-adjust: 100%; 
        }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a { color: #4f46e5; text-decoration: none; }
        .email-wrapper { width: 100%; padding: 40px 20px; background-color: #f3f4f6; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); 
            padding: 32px 40px; 
            text-align: center; 
        }
        .logo { 
            display: inline-block; 
            font-size: 24px; 
            font-weight: 700; 
            color: #ffffff; 
            text-decoration: none; 
            letter-spacing: -0.5px;
        }
        .logo-icon { 
            display: inline-block; 
            width: 40px; 
            height: 40px; 
            background: rgba(255,255,255,0.2); 
            border-radius: 10px; 
            margin-right: 12px; 
            vertical-align: middle; 
            line-height: 40px; 
            font-size: 20px;
        }
        .content { padding: 40px; }
        .content h1 { 
            font-size: 24px; 
            font-weight: 700; 
            color: #ffffff; 
            margin: 0 0 16px; 
            line-height: 1.3; 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); 
            padding: 20px 24px; 
            border-radius: 8px; 
            text-align: center;
        }
        .content p { 
            font-size: 16px; 
            color: #4b5563; 
            margin: 0 0 16px; 
            line-height: 1.6; 
        }
        .content .lead { 
            font-size: 17px; 
            color: #374151; 
            font-weight: 500; 
            margin-bottom: 24px; 
        }
        .info-box { 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            padding: 0; 
            margin: 24px 0; 
            overflow: hidden;
        }
        .info-box h3 { 
            font-size: 14px; 
            font-weight: 600; 
            color: #ffffff; 
            margin: 0 0 16px 0; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); 
            padding: 16px 20px; 
            border-radius: 8px 8px 0 0; 
        }
        .button { 
            display: inline-block; 
            padding: 14px 28px; 
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); 
            color: #ffffff !important; 
            font-size: 15px; 
            font-weight: 600; 
            border-radius: 8px; 
            text-decoration: none; 
            margin: 8px 0 24px; 
            border: none;
        }
        .button:hover { opacity: 0.95; }
        .info-row { 
            display: table; 
            width: 100%; 
            margin-bottom: 8px; 
            padding: 0 20px 20px;
        }
        .info-row:first-child { padding-top: 16px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { 
            display: table-cell; 
            width: 140px; 
            font-size: 13px; 
            color: #6b7280; 
            font-weight: 500; 
        }
        .info-value { 
            display: table-cell; 
            font-size: 13px; 
            color: #1f2937; 
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Mono', monospace; 
        }
        .divider { 
            height: 1px; 
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent); 
            margin: 24px 0; 
        }
        .footer { 
            background: #f9fafb; 
            border-top: 1px solid #e5e7eb; 
            padding: 24px 40px; 
            text-align: center; 
        }
        .footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 4px 0;
        }
        .timestamp {
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .footer a {
            color: #6b7280;
            font-size: 12px;
            text-decoration: underline;
        }
        .badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 11px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .timestamp { 
            font-size: 12px; 
            color: #9ca3af; 
            text-align: center; 
            margin-top: 16px; 
            padding-top: 16px; 
            border-top: 1px solid #e5e7eb; 
        }
        @media only screen and (max-width: 600px) {
            .email-container { border-radius: 0; }
            .header, .content, .footer { padding-left: 24px; padding-right: 24px; }
            .info-label { width: 100px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" class="email-container" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td class="header" style="background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 32px 40px; text-align: center;">
                    <a href="<?= esc(base_url()) ?>" class="logo" style="display: inline-block; font-size: 24px; font-weight: 700; color: #ffffff; text-decoration: none; letter-spacing: -0.5px;">
                        <span class="logo-icon" style="display: inline-block; width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; margin-right: 12px; vertical-align: middle; line-height: 40px; font-size: 20px; color: #ffffff;"><i class="fa-solid fa-chart-line"></i></span>
                        Mpesa Analyzer
                    </a>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <?= $content ?>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <div class="timestamp">
                        <strong>Tracking ID:</strong> <code><?= esc($trackingNumber ?? 'N/A') ?></code>
                        &middot;
                        <strong>Email ID:</strong> <code><?= esc($emailId ?? 'unknown') ?></code>
                        &middot;
                        <strong>As at:</strong> <?= esc($sentAt ?? date('Y-m-d H:i:s T')) ?>
                    </div>
                    <p>&copy; <?= date('Y') ?> Mpesa Analyzer. All rights reserved.</p>
                    <p>
                        <a href="<?= esc(base_url('settings/notifications')) ?>">Email Preferences</a> &middot;
                        <a href="<?= esc(base_url('settings/security')) ?>">Security Settings</a> &middot;
                        <a href="<?= esc(base_url()) ?>">Visit App</a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>