<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Updates all email_templates rows with properly structured, email-client-safe HTML.
 *
 * Every body uses:
 *  - Full <!DOCTYPE html> / <head> / <body> structure
 *  - Table-based layout (no CSS grid/flex)
 *  - Table-based CTA buttons (no display:inline-block on <span>)
 *  - px units only (no rem/em)
 *  - No border-radius on non-table/img elements
 *  - Inline styles throughout
 *
 * Safe to run multiple times — uses UPDATE WHERE id = N.
 */
class EmailTemplateBodySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $id => $data) {
            DB::table('email_templates')
                ->where('id', $id)
                ->update([
                    'subject' => $data['subject'],
                    'body'    => $data['body'],
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
        }
    }

    // -------------------------------------------------------------------------
    // Template definitions
    // -------------------------------------------------------------------------

    private function templates(): array
    {
        return [
            2 => [
                'subject' => 'Activate your account — {{site_label}}',
                'body'    => $this->wrap('Activate Your Account', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Thank you for creating an account with {{site_label}}. Please activate your account by clicking the button below.</p>
'.$this->button('{{activation_url}}', 'Activate Account').'
<p>If the button does not work, copy and paste this link into your browser:</p>
<p><a href="{{activation_url}}" style="color:#0d6ea3;">{{activation_url}}</a></p>
<p>This link will expire at {{expires_at}}.</p>
<p style="margin-bottom:0;">If you did not create this account, you can safely ignore this email.</p>
'),
            ],

            3 => [
                'subject' => 'Reset your password — {{site_label}}',
                'body'    => $this->wrap('Password Reset', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We received a request to reset the password for your account on {{site_label}}.</p>
'.$this->button('{{reset_url}}', 'Reset Password').'
<p>This link will expire at {{expires_at}}.</p>
<p>If you did not request this change, you can safely ignore this email — your password will remain unchanged.</p>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            4 => [
                'subject' => 'Your digitizing order has been received — {{site_label}}',
                'body'    => $this->wrap('Order Received', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your digitizing order and it is now in our production workflow.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
    'Format'        => '{{format}}',
    'Turnaround'    => '{{turnaround}}',
]).'
'.$this->button('{{orders_url}}', 'View My Orders').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            5 => [
                'subject' => 'Your vector order has been received — {{site_label}}',
                'body'    => $this->wrap('Order Received', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your vector order and it is now in our production workflow.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
    'Format'        => '{{format}}',
    'Turnaround'    => '{{turnaround}}',
]).'
'.$this->button('{{orders_url}}', 'View My Orders').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            6 => [
                'subject' => 'Your digitizing quote request has been received — {{site_label}}',
                'body'    => $this->wrap('Quote Request Received', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your digitizing quote request and our team will review it shortly.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
    'Format'        => '{{format}}',
    'Turnaround'    => '{{turnaround}}',
]).'
'.$this->button('{{quotes_url}}', 'View My Quotes').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            7 => [
                'subject' => 'Your vector quote request has been received — {{site_label}}',
                'body'    => $this->wrap('Quote Request Received', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your vector quote request and our team will review it shortly.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
    'Format'        => '{{format}}',
    'Turnaround'    => '{{turnaround}}',
]).'
'.$this->button('{{quotes_url}}', 'View My Quotes').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            8 => [
                'subject' => 'Your order with {{site_label}} has been completed',
                'body'    => $this->wrap('Order Completed', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Great news — your order with {{site_label}} has been completed and is ready for your review.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
]).'
'.$this->button('{{review_url}}', 'Review Completed Order').'
'.$this->noteBox('<strong>Important:</strong> Please conduct a test run and verify the sample against your design before proceeding with production. {{site_label}} is not responsible for any damage to materials incurred during use. Designs are provided for lawful use only. The recipient assumes all responsibility for ensuring reproduction rights and maintaining compliance with intellectual property laws.').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            9 => [
                'subject' => 'Your quote from {{site_label}} is ready',
                'body'    => $this->wrap('Quote Ready', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Your quote from {{site_label}} is ready for your review.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
]).'
'.$this->button('{{review_url}}', 'Review Quote').'
'.$this->noteBox('<strong>Please note:</strong> This quotation is a preliminary estimate only. Final pricing may vary up to +/&#8209;10% based on the final design output. Should the cost exceed this range, we will notify you for approval before proceeding.').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            10 => [
                'subject' => 'Your quick quote from {{site_label}} is ready',
                'body'    => $this->wrap('Quick Quote Ready', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Your quick quote from {{site_label}} is ready. You can review it and complete payment using the link below.</p>
'.$this->detailTable([
    'Reference ID'  => '{{order_id}}',
    'Design Name'   => '{{design_name}}',
    'Amount'        => '{{amount}}',
]).'
'.$this->button('{{payment_url}}', 'Review &amp; Pay').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],

            11 => [
                'subject' => 'Your quote request has been reviewed — {{site_label}}',
                'body'    => $this->wrap('Quote Update', '
<p style="margin-top:0;">Hello {{customer_name}},</p>
<p>{{message}}</p>
'.$this->detailTable([
    'Reference ID'    => '{{order_id}}',
    'Design Name'     => '{{design_name}}',
    'Current Amount'  => '{{amount}}',
]).'
'.$this->button('{{review_url}}', 'Review Quote').'
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p>
'),
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // HTML builder helpers
    // -------------------------------------------------------------------------

    private function wrap(string $title, string $content): string
    {
        return '<!DOCTYPE html>'
            .'<html lang="en" xmlns="http://www.w3.org/1999/xhtml">'
            .'<head>'
            .'<meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<meta http-equiv="X-UA-Compatible" content="IE=edge">'
            .'<title>'.htmlspecialchars($title).'</title>'
            .'</head>'
            .'<body>'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;">'
            .'<tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;">'
            .'<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">'
            // Header
            .'<tr><td style="background:#17212a;padding:22px 28px;">'
            .'<span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span>'
            .'</td></tr>'
            // Body
            .'<tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;">'
            .trim($content)
            .'</td></tr>'
            // Footer
            .'<tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">'
            .'{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>'
            .'</td></tr>'
            .'</table>'
            .'</td></tr>'
            .'</table>'
            .'</body>'
            .'</html>';
    }

    private function button(string $url, string $label): string
    {
        return '<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;">'
            .'<tr><td style="background:#17212a;padding:13px 24px;">'
            .'<a href="'.$url.'" style="color:#ffffff;font-size:14px;font-weight:700;">'.$label.'</a>'
            .'</td></tr>'
            .'</table>';
    }

    private function detailTable(array $rows): string
    {
        $html = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;">';

        $keys = array_keys($rows);
        foreach ($keys as $i => $label) {
            $isLast   = $i === count($keys) - 1;
            $border   = $isLast ? '' : 'border-bottom:1px solid #d9dee5;';
            $html .= '<tr>'
                .'<td style="padding:9px 14px;font-size:13px;background:#f9f9f9;'.$border.'"><strong>'.htmlspecialchars($label).'</strong></td>'
                .'<td style="padding:9px 14px;font-size:13px;'.$border.'">'.$rows[$label].'</td>'
                .'</tr>';
        }

        return $html.'</table>';
    }

    private function noteBox(string $content): string
    {
        return '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:20px 0;">'
            .'<tr><td style="padding:13px 16px;background:#f9f9f9;border-left:3px solid #0f5f66;font-size:13px;line-height:20px;color:#17212a;">'
            .$content
            .'</td></tr>'
            .'</table>';
    }
}
