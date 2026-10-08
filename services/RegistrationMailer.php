<?php

require_once BASE_PATH . '/services/OutreachMailer.php';
require_once BASE_PATH . '/models/RegistrationEmailLog.php';

/**
 * Email notifications for the existing community-registration workflow
 * (controllers/CommunityController::submitApplication,
 * controllers/AdminCommunityController::approve/reject). This is purely
 * an additional notification channel alongside the existing flash
 * messages and status fields — it never creates or changes application
 * state, and a delivery failure here must never surface as an error to
 * the registrant or admin performing the action. Every attempt is
 * logged to registration_emails, whose unique (application_id,
 * event_type) key keeps sends idempotent across refreshes/re-submits.
 */
class RegistrationMailer
{
    public static function newRegistration(array $app): void
    {
        self::deliver($app, 'registration_received_admin', SITE_EMAIL, SITE_NAME, fn(array $app) => [
            'subject' => 'New Tiv Heritage Archive Registration — ' . $app['full_name'],
            'body'    => self::wrap(self::adminNewRegistrationHtml($app)),
        ]);

        self::deliver($app, 'registration_received_user', (string) $app['email'], $app['full_name'], fn(array $app) => [
            'subject' => 'Your Tiv Heritage Archive Registration Has Been Received',
            'body'    => self::wrap(self::userReceivedHtml($app)),
        ]);
    }

    public static function approved(array $app): void
    {
        self::deliver($app, 'registration_approved', (string) $app['email'], $app['full_name'], fn(array $app) => [
            'subject' => 'Your Tiv Heritage Archive Registration Has Been Approved',
            'body'    => self::wrap(self::approvedHtml($app)),
        ]);
    }

    public static function declined(array $app): void
    {
        self::deliver($app, 'registration_declined', (string) $app['email'], $app['full_name'], fn(array $app) => [
            'subject' => 'Update on Your Tiv Heritage Archive Registration',
            'body'    => self::wrap(self::declinedHtml($app)),
        ]);
    }

    /**
     * Checks the delivery log for an existing attempt, sends via the
     * existing OutreachMailer transport, and records the outcome.
     * Swallows every failure mode (bad recipient, mail() failure, a
     * throwable from the mailer itself) so the caller's registration/
     * approval/decline action always completes regardless of email
     * delivery.
     */
    private static function deliver(array $app, string $eventType, string $toEmail, string $toName, callable $build): void
    {
        $log = new RegistrationEmailLog();

        try {
            if ($log->alreadySent((int) $app['id'], $eventType)) {
                return;
            }
        } catch (\Throwable $e) {
            // Can't confirm send history — fail closed rather than risk a duplicate.
            error_log('[RegistrationMailer] idempotency check failed for application ' . $app['id'] . '/' . $eventType . ': ' . $e->getMessage());
            return;
        }

        $built   = $build($app);
        $subject = $built['subject'];
        $body    = $built['body'];

        if ($toEmail === '' || !Security::validateEmail($toEmail)) {
            $log->record([
                'application_id'  => (int) $app['id'],
                'user_id'         => $app['user_id'] ?? null,
                'event_type'      => $eventType,
                'recipient_email' => $toEmail ?: null,
                'subject'         => $subject,
                'status'          => 'failed',
                'error_message'   => 'No valid recipient email address on file',
                'sent_at'         => null,
            ]);
            return;
        }

        $sent  = false;
        $error = null;
        try {
            $sent = OutreachMailer::send($toEmail, $toName, $subject, $body);
            if (!$sent) {
                $error = 'mail() returned false';
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $log->record([
            'application_id'  => (int) $app['id'],
            'user_id'         => $app['user_id'] ?? null,
            'event_type'      => $eventType,
            'recipient_email' => $toEmail,
            'subject'         => $subject,
            'status'          => $sent ? 'sent' : 'failed',
            'error_message'   => $sent ? null : substr((string) $error, 0, 500),
            'sent_at'         => $sent ? date('Y-m-d H:i:s') : null,
        ]);

        if (!$sent) {
            error_log('[RegistrationMailer] failed to send ' . $eventType . ' for application ' . $app['id'] . ': ' . $error);
        }
    }

    private static function adminNewRegistrationHtml(array $app): string
    {
        $reviewUrl = url('admin/community/applications/' . $app['id']);
        $roleLabel = ucfirst($app['member_type']);

        return '
            <h2 style="margin:0 0 16px;color:#5C3A21;font-size:20px;">New Community Registration</h2>
            <p>A new application to join the Tiv Heritage Archive community has been submitted.</p>
            <table cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0;font-size:14px;">
                <tr><td style="padding:6px 0;color:#8a7a6a;width:140px;">Name</td><td style="padding:6px 0;">' . e($app['full_name']) . '</td></tr>
                <tr><td style="padding:6px 0;color:#8a7a6a;">Email</td><td style="padding:6px 0;">' . e($app['email']) . '</td></tr>
                <tr><td style="padding:6px 0;color:#8a7a6a;">Requested role</td><td style="padding:6px 0;">' . e($roleLabel) . '</td></tr>
                <tr><td style="padding:6px 0;color:#8a7a6a;">Area / expertise</td><td style="padding:6px 0;">' . e($app['area_of_interest'] ?: '—') . '</td></tr>
                <tr><td style="padding:6px 0;color:#8a7a6a;">Registered</td><td style="padding:6px 0;">' . e(date('F j, Y', strtotime($app['created_at'] ?? 'now'))) . '</td></tr>
                <tr><td style="padding:6px 0;color:#8a7a6a;">Status</td><td style="padding:6px 0;">Pending</td></tr>
            </table>
            <p style="text-align:center;margin:28px 0;">
                <a href="' . e($reviewUrl) . '" style="background:#5C3A21;color:#C8A951;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:bold;display:inline-block;">Review Application</a>
            </p>';
    }

    private static function userReceivedHtml(array $app): string
    {
        return '
            <h2 style="margin:0 0 16px;color:#5C3A21;font-size:20px;">Registration Received</h2>
            <p>Dear ' . e($app['full_name']) . ',</p>
            <p>Thank you for applying to join the Tiv Heritage Archive community. Your registration has been successfully received and is currently <strong>pending review</strong>.</p>
            <p>You will receive another email as soon as your registration has been approved or declined.</p>
            <p>Thank you for helping preserve Tiv language, history, and identity.</p>';
    }

    private static function approvedHtml(array $app): string
    {
        $loginUrl  = url('login');
        $roleLabel = ucfirst($app['member_type']);
        $access    = $app['member_type'] === 'researcher'
            ? 'a public profile in the Community directory, and access to submit and manage research contributions to the archive'
            : 'a public profile in the Community directory, and access to submit contributions to the archive';

        return '
            <h2 style="margin:0 0 16px;color:#5C3A21;font-size:20px;">Registration Approved</h2>
            <p>Dear ' . e($app['full_name']) . ',</p>
            <p>Congratulations — your Tiv Heritage Archive registration has been <strong>approved</strong> as a <strong>' . e($roleLabel) . '</strong>.</p>
            <p>You now have ' . $access . '. Log in to your account to get started.</p>
            <p style="text-align:center;margin:28px 0;">
                <a href="' . e($loginUrl) . '" style="background:#5C3A21;color:#C8A951;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:bold;display:inline-block;">Log In</a>
            </p>';
    }

    private static function declinedHtml(array $app): string
    {
        $roleLabel = ucfirst($app['member_type']);
        $notes     = trim((string) ($app['admin_notes'] ?? ''));

        $notesHtml = $notes !== ''
            ? '<p><strong>Notes from our review team:</strong><br>' . nl2br(e($notes)) . '</p>'
            : '';

        return '
            <h2 style="margin:0 0 16px;color:#5C3A21;font-size:20px;">Registration Update</h2>
            <p>Dear ' . e($app['full_name']) . ',</p>
            <p>Thank you for your interest in joining the Tiv Heritage Archive community as a <strong>' . e($roleLabel) . '</strong>.</p>
            <p>After review, we are unable to approve your registration at this time.</p>
            ' . $notesHtml . '
            <p>If you have questions, you\'re welcome to reach us at <a href="mailto:' . e(SITE_EMAIL) . '">' . e(SITE_EMAIL) . '</a>.</p>';
    }

    private static function wrap(string $content): string
    {
        $siteName = SITE_NAME;
        $siteUrl  = SITE_URL;

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
}
