<?php

class OutreachMailer {

    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
        $fromEmail = defined('SITE_EMAIL') ? SITE_EMAIL : 'noreply@tivheritage.com';
        $fromName  = defined('SITE_NAME')  ? SITE_NAME  : 'Tiv Heritage Archive';

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::formatAddress($fromName, $fromEmail),
            'Reply-To: ' . $fromEmail,
            'Bcc: ' . $fromEmail,
            'X-Mailer: TivArchive-Outreach/1.0',
        ]);

        $toHeader = self::formatAddress($toName, $toEmail);

        return @mail($toHeader, self::encodeHeader($subject), $htmlBody, $headers);
    }

    /**
     * Format a "Display Name <email>" header value, RFC 5322-quoted when the
     * display name contains characters (comma, parens, etc.) that would
     * otherwise be parsed as address-list syntax by mail transfer agents.
     */
    private static function formatAddress(string $name, string $email): string {
        $encoded = self::encodeHeader($name);

        if (preg_match('/[,()<>";:\\\\]/', $encoded)) {
            $encoded = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $encoded) . '"';
        }

        return $encoded . ' <' . $email . '>';
    }

    public static function buildBody(string $templateHtml, array $vars): string {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'Tiv Heritage Archive';
        $siteUrl  = defined('SITE_URL')  ? SITE_URL  : '';

        $map = [
            '{{name}}'      => htmlspecialchars($vars['name']  ?? '', ENT_QUOTES, 'UTF-8'),
            '{{title}}'     => htmlspecialchars($vars['title'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{{site_name}}' => $siteName,
            '{{site_url}}'  => $siteUrl,
        ];

        $body = str_replace(array_keys($map), array_values($map), $templateHtml);

        $unsubLink = !empty($vars['unsubscribe_url'])
            ? '<p style="font-size:11px;color:#999;margin-top:24px;">
                 If you no longer wish to receive these emails, you may
                 <a href="' . htmlspecialchars($vars['unsubscribe_url'], ENT_QUOTES, 'UTF-8') . '">unsubscribe here</a>.
               </p>'
            : '';

        return self::wrapHtml($body . $unsubLink, $siteName, $siteUrl);
    }

    private static function wrapHtml(string $content, string $siteName, string $siteUrl): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$siteName}</title>
</head>
<body style="margin:0;padding:0;background:#f4f0e8;font-family:Georgia,serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f0e8;padding:30px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);">
          <tr>
            <td style="background:#5C3A21;padding:20px 32px;">
              <a href="{$siteUrl}" style="color:#C8A951;font-size:20px;font-weight:bold;text-decoration:none;">{$siteName}</a>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;color:#2d1b0e;font-size:15px;line-height:1.7;">
              {$content}
            </td>
          </tr>
          <tr>
            <td style="background:#f7f4ee;padding:16px 32px;font-size:12px;color:#8a7a6a;text-align:center;">
              &copy; {$siteName} &middot; <a href="{$siteUrl}" style="color:#5C3A21;">{$siteUrl}</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    private static function encodeHeader(string $value): string {
        if (mb_detect_encoding($value, 'ASCII', true)) {
            return $value;
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
