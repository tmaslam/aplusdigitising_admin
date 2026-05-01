-- Production / staging seed for system email templates
-- Safe to run multiple times. Inserts only missing site-specific templates for 1dollar.

SET @primary_site_id = (
  SELECT `id`
  FROM `sites`
  WHERE BINARY `legacy_key` = BINARY '1dollar'
  LIMIT 1
);

INSERT INTO `email_templates` (
  `site_id`,
  `template_name`,
  `subject`,
  `body`,
  `is_active`,
  `created_by`,
  `updated_by`,
  `created_at`,
  `updated_at`
)
SELECT
  @primary_site_id,
  seed.`template_name`,
  seed.`subject`,
  seed.`body`,
  1,
  'system',
  'system',
  NOW(),
  NOW()
FROM (
  SELECT
    'Customer Account Activation' AS `template_name`,
    'Activate your account - {{site_label}}' AS `subject`,
    '<p>Hello {{customer_name}},</p><p>Thank you for creating an account with {{site_label}}.</p><p>Please activate your account using the link below:</p><p><a href="{{activation_url}}">Activate Account</a></p><p>If the button does not open, use this link:</p><p>{{activation_url}}</p><p>If you need help, contact us at {{support_email}}.</p><p>Thank you,<br>{{site_label}}</p>' AS `body`
  UNION ALL
  SELECT
    'Customer Password Reset',
    'Reset your password - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received a request to reset your password for {{site_label}}.</p><p>You can set a new password using the link below:</p><p><a href="{{reset_url}}">Reset Password</a></p><p>This link expires on {{expires_at}}.</p><p>If you did not request this change, you can safely ignore this email.</p><p>If you need help, contact us at {{support_email}}.</p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Digitizing Order Confirmation',
    'Your digitizing order has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your digitizing order and it is now in our workflow.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{orders_url}}">View My Orders</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Vector Order Confirmation',
    'Your vector order has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your vector order and it is now in our workflow.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{orders_url}}">View My Orders</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Digitizing Quote Confirmation',
    'Your digitizing quote request has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your digitizing quote request and our team will review it shortly.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{quotes_url}}">View My Quotes</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Vector Quote Confirmation',
    'Your vector quote request has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your vector quote request and our team will review it shortly.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{quotes_url}}">View My Quotes</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Order Completed',
    'Your order with {{site_label}} has been completed',
    '<p>Hello {{customer_name}},</p><p>Your order with {{site_label}} has been completed.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p>Please review it in your account using the link below:</p><p><a href="{{review_url}}">Review Completed Order</a></p><p><strong>DISCLAIMER:</strong> Please conduct a test run and verify the sample against your design before proceeding with production. aplusdigitising.com is not responsible for any damage to materials incurred during use. Designs are provided for lawful use only. The recipient assumes all responsibility for ensuring reproduction rights and maintaining compliance with intellectual property laws.</p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Quote Completed',
    'Your quote from {{site_label}} is ready',
    '<p>Hello {{customer_name}},</p><p>Your quote from {{site_label}} is ready for review.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p>You can review it in your account using the link below:</p><p><a href="{{review_url}}">Review Quote</a></p><p><strong>PLEASE NOTE:</strong> This quotation is a preliminary estimate only. Final pricing may vary up to +/- 10% based on final design output. Should the cost exceed this range, we will notify you for approval prior to proceeding.</p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Quick Quote Completed',
    'Your quick quote from {{site_label}} is ready',
    '<p>Hello {{customer_name}},</p><p>Your quick quote from {{site_label}} is ready.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Amount:</strong> {{amount}}</p><p>You can review and complete payment using the link below:</p><p><a href="{{payment_url}}">Review Quick Quote</a></p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Quote Negotiation Response',
    'Your quote request has been reviewed - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>{{message}}</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Current Amount:</strong> {{amount}}</p><p>You can review the latest quote status here:</p><p><a href="{{review_url}}">Review Quote</a></p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
) AS seed
WHERE NOT EXISTS (
  SELECT 1
  FROM `email_templates` existing
  WHERE existing.`template_name` = seed.`template_name`
    AND (
      existing.`site_id` = @primary_site_id
      OR (@primary_site_id IS NULL AND existing.`site_id` IS NULL)
    )
);
