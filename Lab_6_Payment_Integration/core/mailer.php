<?php
// mailer.php — PHPMailer wrapper for order confirmation emails
// Requires: core/paystack_config.php already loaded (defines MAIL_* constants)
// PHPMailer source is at vendor/phpmailer/src/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

/**
 * Send an order confirmation email to the customer.
 *
 * @param array $order  Row from getOrderById()['order']
 * @param array $items  Rows from getOrderById()['items']
 * @param float $total  Grand total in GHS
 * @return bool True on success, false on failure
 */
function send_order_confirmation(array $order, array $items, float $total): bool
{
    // skip sending when mail is turned off
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // smtp
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        // from / to
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($order['customer_email'], $order['customer_name']);

        // message
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation — Invoice #' . (int)$order['invoice_no'];

        // Build a simple HTML table for the email body
        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr>'
                . '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;">'
                . htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8')
                . '</td>'
                . '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">'
                . (int)$item['qty']
                . '</td>'
                . '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:right;">'
                . 'GH₵ ' . number_format((float)$item['unit_price'], 2)
                . '</td>'
                . '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:right;">'
                . 'GH₵ ' . number_format((float)$item['line_total'], 2)
                . '</td>'
                . '</tr>';
        }

        $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Order Confirmation</title></head>
<body style="font-family:Segoe UI,Arial,sans-serif;background:#f4f6f8;margin:0;padding:0;">
<div style="max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
  <div style="background:#1e3a5f;padding:24px 32px;">
    <h1 style="margin:0;color:#fff;font-size:1.4rem;">Shoppn — Order Confirmed</h1>
  </div>
  <div style="padding:28px 32px;">
    <p style="color:#374151;">Hi <strong>' . htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') . '</strong>,</p>
    <p style="color:#374151;">Thank you for your order! Here is your receipt.</p>

    <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:0.9rem;">
      <tr style="background:#f8fafc;">
        <td style="padding:6px 10px;color:#6b7280;font-size:0.8rem;text-transform:uppercase;">Invoice #</td>
        <td style="padding:6px 10px;font-weight:600;">' . (int)$order['invoice_no'] . '</td>
        <td style="padding:6px 10px;color:#6b7280;font-size:0.8rem;text-transform:uppercase;">Date</td>
        <td style="padding:6px 10px;">' . htmlspecialchars(date('d M Y', strtotime($order['order_date'])), ENT_QUOTES, 'UTF-8') . '</td>
      </tr>
      <tr>
        <td style="padding:6px 10px;color:#6b7280;font-size:0.8rem;text-transform:uppercase;">Status</td>
        <td style="padding:6px 10px;font-weight:600;color:#15803d;">' . htmlspecialchars(strtoupper($order['order_status']), ENT_QUOTES, 'UTF-8') . '</td>
        <td style="padding:6px 10px;color:#6b7280;font-size:0.8rem;text-transform:uppercase;">Reference</td>
        <td style="padding:6px 10px;font-size:0.8rem;">' . htmlspecialchars($order['paystack_ref'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
      </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
      <thead>
        <tr style="background:#1e3a5f;color:#fff;">
          <th style="padding:10px;text-align:left;">Product</th>
          <th style="padding:10px;text-align:center;">Qty</th>
          <th style="padding:10px;text-align:right;">Unit Price</th>
          <th style="padding:10px;text-align:right;">Subtotal</th>
        </tr>
      </thead>
      <tbody>' . $rows . '</tbody>
      <tfoot>
        <tr style="background:#f8fafc;">
          <td colspan="3" style="padding:10px;text-align:right;font-weight:700;">Grand Total</td>
          <td style="padding:10px;text-align:right;font-weight:700;">GH₵ ' . number_format($total, 2) . '</td>
        </tr>
      </tfoot>
    </table>

    <p style="margin-top:24px;color:#4b5563;font-size:0.88rem;">
      If you have any questions, please reply to this email.
    </p>
    <p style="color:#4b5563;font-size:0.88rem;">— The Shoppn Team</p>
  </div>
</div>
</body>
</html>';

        // Plain-text fallback
        $plain = "Order Confirmation — Invoice #{$order['invoice_no']}\n\n";
        $plain .= "Hi {$order['customer_name']},\n\n";
        $plain .= "Thank you for your order!\n\n";
        foreach ($items as $item) {
            $plain .= "- {$item['product_title']} x{$item['qty']} @ GH₵" . number_format((float)$item['unit_price'], 2) . "\n";
        }
        $plain .= "\nTotal: GH₵" . number_format($total, 2) . "\n";
        $mail->AltBody = $plain;

        $mail->send();
        return true;

    } catch (PHPMailerException $e) {
        error_log('[mailer] PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
