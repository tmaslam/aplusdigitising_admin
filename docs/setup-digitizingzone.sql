-- ============================================================================
-- Setup digitizingzone.com as a second site on the multi-tenant portal
-- ============================================================================
-- Run this in cPanel phpMyAdmin or Terminal:
--   mysql -u apluihej_aplusadmin -p'P@cistan1!@' apluihej_aplusadmin < setup-digitizingzone.sql
-- ============================================================================

-- 1. Insert the new site
--    Theme: Blue brand (#0066CC primary, #004499 dark)
--    Logo: /images/sites/digitizingzone/logo.png
--    Favicon: /images/sites/digitizingzone/favicon.png
INSERT INTO `sites` (
  `legacy_key`,
  `slug`,
  `name`,
  `brand_name`,
  `primary_domain`,
  `website_address`,
  `support_email`,
  `from_email`,
  `timezone`,
  `pricing_strategy`,
  `is_primary`,
  `is_active`,
  `settings_json`,
  `created_at`,
  `updated_at`
) VALUES (
  'digitizingzone',
  'digitizingzone',
  'Digitizing Zone',
  'Digitizing Zone',
  'digitizingzone.com',
  'https://digitizingzone.com',
  'support@digitizingzone.com',
  'support@digitizingzone.com',
  'UTC',
  'customer_rate',
  0,
  1,
  '{"logo_url":"/images/sites/digitizingzone/logo.png","favicon_url":"/images/sites/digitizingzone/favicon.png","primary_color":"#0066CC","accent_color":"#f4b43a","theme":{"primary_dark":"#004499"}}',
  NOW(),
  NOW()
);

-- 2. Map the domain to the new site
--    Get the auto-generated site_id first, then insert domain.
--    If you already know the site_id, replace @site_id below.
SET @site_id = LAST_INSERT_ID();

INSERT INTO `site_domains` (
  `site_id`,
  `host`,
  `is_primary`,
  `is_active`,
  `created_at`,
  `updated_at`
) VALUES (
  @site_id,
  'digitizingzone.com',
  1,
  1,
  NOW(),
  NOW()
);

-- Optional: also map www.digitizingzone.com
INSERT INTO `site_domains` (
  `site_id`,
  `host`,
  `is_primary`,
  `is_active`,
  `created_at`,
  `updated_at`
) VALUES (
  @site_id,
  'www.digitizingzone.com',
  0,
  1,
  NOW(),
  NOW()
);

-- ============================================================================
-- 3. Pricing profiles for digitizingzone.com
--    Adjust rates below to match Digitizing Zone's actual pricing.
-- ============================================================================

-- Embroidery Digitizing - Standard
INSERT INTO `site_pricing_profiles` (
  `site_id`, `profile_name`, `work_type`, `turnaround_code`,
  `pricing_mode`, `per_thousand_rate`, `minimum_charge`, `included_units`, `is_active`,
  `created_at`, `updated_at`
) VALUES (
  @site_id, 'Embroidery Standard', 'digitizing', 'standard',
  'customer_rate', 1.50, 15.00, 15000, 1,
  NOW(), NOW()
);

-- Embroidery Digitizing - Priority
INSERT INTO `site_pricing_profiles` (
  `site_id`, `profile_name`, `work_type`, `turnaround_code`,
  `pricing_mode`, `per_thousand_rate`, `minimum_charge`, `included_units`, `is_active`,
  `created_at`, `updated_at`
) VALUES (
  @site_id, 'Embroidery Priority', 'digitizing', 'priority',
  'customer_rate', 2.25, 20.00, 15000, 1,
  NOW(), NOW()
);

-- Embroidery Digitizing - Super Rush
INSERT INTO `site_pricing_profiles` (
  `site_id`, `profile_name`, `work_type`, `turnaround_code`,
  `pricing_mode`, `per_thousand_rate`, `minimum_charge`, `included_units`, `is_active`,
  `created_at`, `updated_at`
) VALUES (
  @site_id, 'Embroidery Super Rush', 'digitizing', 'superrush',
  'customer_rate', 3.00, 25.00, 15000, 1,
  NOW(), NOW()
);

-- Vector Art - Standard
INSERT INTO `site_pricing_profiles` (
  `site_id`, `profile_name`, `work_type`, `turnaround_code`,
  `pricing_mode`, `overage_rate`, `minimum_charge`, `included_units`, `is_active`,
  `created_at`, `updated_at`
) VALUES (
  @site_id, 'Vector Standard', 'vector', 'standard',
  'hourly', 15.00, 15.00, 1, 1,
  NOW(), NOW()
);

-- Vector Art - Priority
INSERT INTO `site_pricing_profiles` (
  `site_id`, `profile_name`, `work_type`, `turnaround_code`,
  `pricing_mode`, `overage_rate`, `minimum_charge`, `included_units`, `is_active`,
  `created_at`, `updated_at`
) VALUES (
  @site_id, 'Vector Priority', 'vector', 'priority',
  'hourly', 20.00, 20.00, 1, 1,
  NOW(), NOW()
);

-- Color Separation - Standard
INSERT INTO `site_pricing_profiles` (
  `site_id`, `profile_name`, `work_type`, `turnaround_code`,
  `pricing_mode`, `overage_rate`, `minimum_charge`, `included_units`, `is_active`,
  `created_at`, `updated_at`
) VALUES (
  @site_id, 'Color Separation Standard', 'color', 'standard',
  'hourly', 18.00, 18.00, 1, 1,
  NOW(), NOW()
);
