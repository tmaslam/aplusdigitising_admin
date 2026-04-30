-- Phase 3 Fixes — A Plus Digitizing Platform
-- Applied: 2026-04-10
-- Covers all database changes made as part of the Phase 3 testing report fixes.
-- Run this script once on any environment that was set up from the Phase 2 baseline.

-- ─────────────────────────────────────────────────────────────────────────────
-- FIX #12 — Site Pricing: missing vector priority and super rush profiles
--
-- Only digitizing profiles existed. Vector priority and super rush were absent,
-- so those turnaround codes fell back to hardcoded values and could not be
-- managed through the admin Site Pricing UI.
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `site_pricing_profiles`
    (`site_id`, `profile_name`, `work_type`, `turnaround_code`, `pricing_mode`,
     `fixed_price`, `per_thousand_rate`, `minimum_charge`, `included_units`,
     `overage_rate`, `package_name`, `config_json`, `is_active`, `created_at`, `updated_at`)
SELECT
    1, 'Vector Priority', 'vector', 'priority', 'fixed_price',
    '9.00', NULL, NULL, NULL,
    NULL, NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `site_pricing_profiles`
    WHERE `site_id` = 1 AND `work_type` = 'vector' AND `turnaround_code` = 'priority'
);

INSERT INTO `site_pricing_profiles`
    (`site_id`, `profile_name`, `work_type`, `turnaround_code`, `pricing_mode`,
     `fixed_price`, `per_thousand_rate`, `minimum_charge`, `included_units`,
     `overage_rate`, `package_name`, `config_json`, `is_active`, `created_at`, `updated_at`)
SELECT
    1, 'Vector Super Rush', 'vector', 'superrush', 'fixed_price',
    '12.00', NULL, NULL, NULL,
    NULL, NULL, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `site_pricing_profiles`
    WHERE `site_id` = 1 AND `work_type` = 'vector' AND `turnaround_code` = 'superrush'
);

-- ─────────────────────────────────────────────────────────────────────────────
-- FIX #13 — Admin Password Recovery
--
-- Adds the token table that backs the admin forgot/reset password flow.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `admin_password_reset_tokens` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `admin_user_id`  INT NOT NULL,
    `selector`       VARCHAR(16)  NOT NULL UNIQUE,
    `token_hash`     VARCHAR(64)  NOT NULL,
    `token_type`     VARCHAR(20)  NOT NULL DEFAULT 'password_reset',
    `attempts`       TINYINT      NOT NULL DEFAULT 0,
    `expires_at`     DATETIME     NOT NULL,
    `created_at`     DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2026-04-11 — Two-Factor Authentication
--
-- Admin 2FA is mandatory; customer 2FA is optional (toggled from My Profile).
-- 2FA codes reuse the existing password-reset token tables — no new table needed.
-- token_type distinguishes 'password_reset' rows from '2fa_code' rows.
-- attempts tracks wrong-code submissions for lockout after 5 failures.
--
-- Environments where admin_password_reset_tokens already exists (without the
-- new columns) need only the ALTER TABLE below — the CREATE TABLE above already
-- includes the full definition for fresh installs.
-- ─────────────────────────────────────────────────────────────────────────────

-- Upgrade existing admin token table (safe no-op if columns already present).
ALTER TABLE `admin_password_reset_tokens`
    ADD COLUMN IF NOT EXISTS `token_type` VARCHAR(20) NOT NULL DEFAULT 'password_reset' AFTER `token_hash`,
    ADD COLUMN IF NOT EXISTS `attempts`   TINYINT     NOT NULL DEFAULT 0                AFTER `token_type`;

-- 1. Optional 2FA flag for customer accounts.
SET SESSION sql_mode = '';
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0
    AFTER `password_migrated_at`;

-- Extend customer token table.
ALTER TABLE `customer_password_reset_tokens`
    ADD COLUMN IF NOT EXISTS `token_type` VARCHAR(20) NOT NULL DEFAULT 'password_reset' AFTER `token_hash`,
    ADD COLUMN IF NOT EXISTS `attempts`   TINYINT     NOT NULL DEFAULT 0                AFTER `token_type`;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2026-04-11 — Email Template Bodies
--
-- Replaces plain <p>-only bodies with properly structured, email-client-safe
-- HTML: full <!DOCTYPE html>/<head>/<body>, table-based layout and CTA buttons,
-- px units only, no rem/em, no border-radius on non-table elements.
-- Safe to re-run — only updates rows by primary key.
-- ─────────────────────────────────────────────────────────────────────────────

UPDATE `email_templates` SET `subject` = 'Activate your account — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Activate Your Account</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Thank you for creating an account with {{site_label}}. Please activate your account by clicking the button below.</p>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{activation_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">Activate Account</a></td></tr></table>
<p>If the button does not work, copy and paste this link into your browser:</p>
<p><a href="{{activation_url}}" style="color:#0d6ea3;">{{activation_url}}</a></p>
<p>This link will expire at {{expires_at}}.</p>
<p style="margin-bottom:0;">If you did not create this account, you can safely ignore this email.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 2;

UPDATE `email_templates` SET `subject` = 'Reset your password — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Password Reset</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We received a request to reset the password for your account on {{site_label}}.</p>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{reset_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">Reset Password</a></td></tr></table>
<p>This link will expire at {{expires_at}}.</p>
<p>If you did not request this change, you can safely ignore this email — your password will remain unchanged.</p>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 3;

UPDATE `email_templates` SET `subject` = 'Your digitizing order has been received — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Order Received</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your digitizing order and it is now in our production workflow.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{design_name}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Format</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{format}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Turnaround</strong></td><td style="padding:9px 14px;font-size:13px;">{{turnaround}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{orders_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">View My Orders</a></td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 4;

UPDATE `email_templates` SET `subject` = 'Your vector order has been received — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Order Received</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your vector order and it is now in our production workflow.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{design_name}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Format</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{format}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Turnaround</strong></td><td style="padding:9px 14px;font-size:13px;">{{turnaround}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{orders_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">View My Orders</a></td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 5;

UPDATE `email_templates` SET `subject` = 'Your digitizing quote request has been received — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Quote Request Received</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your digitizing quote request and our team will review it shortly.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{design_name}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Format</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{format}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Turnaround</strong></td><td style="padding:9px 14px;font-size:13px;">{{turnaround}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{quotes_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">View My Quotes</a></td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 6;

UPDATE `email_templates` SET `subject` = 'Your vector quote request has been received — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Quote Request Received</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>We have received your vector quote request and our team will review it shortly.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{design_name}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Format</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{format}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Turnaround</strong></td><td style="padding:9px 14px;font-size:13px;">{{turnaround}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{quotes_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">View My Quotes</a></td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 7;

UPDATE `email_templates` SET `subject` = 'Your order with {{site_label}} has been completed', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Order Completed</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Great news — your order with {{site_label}} has been completed and is ready for your review.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;">{{design_name}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{review_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">Review Completed Order</a></td></tr></table>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:20px 0;"><tr><td style="padding:13px 16px;background:#f9f9f9;border-left:3px solid #0f5f66;font-size:13px;line-height:20px;color:#17212a;"><strong>Important:</strong> Please conduct a test run and verify the sample against your design before proceeding with production. {{site_label}} is not responsible for any damage to materials incurred during use. Designs are provided for lawful use only. The recipient assumes all responsibility for ensuring reproduction rights and maintaining compliance with intellectual property laws.</td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 8;

UPDATE `email_templates` SET `subject` = 'Your quote from {{site_label}} is ready', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Quote Ready</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Your quote from {{site_label}} is ready for your review.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;">{{design_name}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{review_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">Review Quote</a></td></tr></table>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:20px 0;"><tr><td style="padding:13px 16px;background:#f9f9f9;border-left:3px solid #0f5f66;font-size:13px;line-height:20px;color:#17212a;"><strong>Please note:</strong> This quotation is a preliminary estimate only. Final pricing may vary up to +/&#8209;10% based on the final design output. Should the cost exceed this range, we will notify you for approval before proceeding.</td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 9;

UPDATE `email_templates` SET `subject` = 'Your quick quote from {{site_label}} is ready', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Quick Quote Ready</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>Your quick quote from {{site_label}} is ready. You can review it and complete payment using the link below.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{design_name}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Amount</strong></td><td style="padding:9px 14px;font-size:13px;">{{amount}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{payment_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">Review &amp; Pay</a></td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 10;

UPDATE `email_templates` SET `subject` = 'Your quote request has been reviewed — {{site_label}}', `body` = '<!DOCTYPE html><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title>Quote Update</title></head><body><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f4f6f8;"><tr><td align="center" style="padding:24px 16px;font-family:Arial,Helvetica,sans-serif;color:#17212a;"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;"><tr><td style="background:#17212a;padding:22px 28px;"><span style="font-size:20px;font-weight:700;color:#ffffff;">{{site_label}}</span></td></tr><tr><td style="background:#ffffff;padding:28px;font-size:14px;line-height:22px;color:#17212a;border:1px solid #d9dee5;border-top:0;"><p style="margin-top:0;">Hello {{customer_name}},</p>
<p>{{message}}</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:16px 0;border:1px solid #d9dee5;"><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Reference ID</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{order_id}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;border-bottom:1px solid #d9dee5;"><strong>Design Name</strong></td><td style="padding:9px 14px;font-size:13px;border-bottom:1px solid #d9dee5;">{{design_name}}</td></tr><tr><td style="padding:9px 14px;font-size:13px;background:#f9f9f9;"><strong>Current Amount</strong></td><td style="padding:9px 14px;font-size:13px;">{{amount}}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:24px 0;"><tr><td style="background:#17212a;padding:13px 24px;"><a href="{{review_url}}" style="color:#ffffff;font-size:14px;font-weight:700;">Review Quote</a></td></tr></table>
<p style="margin-bottom:0;">Need help? Contact <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a>.</p></td></tr><tr><td style="padding:14px 28px;font-size:12px;color:#888888;background:#f9f9f9;border:1px solid #d9dee5;border-top:0;">{{site_label}} &bull; Questions? <a href="mailto:{{support_email}}" style="color:#0d6ea3;">{{support_email}}</a></td></tr></table></td></tr></table></body></html>', `updated_at` = NOW() WHERE `id` = 11;

