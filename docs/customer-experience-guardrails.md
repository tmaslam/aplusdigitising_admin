# Customer Experience Guardrails

These rules apply to every customer-facing phase-two migration task.

## Visual and UX direction

- Preserve the familiar website look and page structure.
- Allow only careful cleanup, spacing polish, and minor color refinement.
- Do not introduce labels, wording, or presentation that suggest generated code or a rebuilt experimental interface.
- Improve usability where it clearly helps customers complete work faster or with less confusion.
- Every customer page must work well on mobile, tablet, and desktop.

## Security

- Security is the highest priority for customer flows.
- Customer queries must always be site-scoped.
- Downloads, profile access, notifications, and password reset must never cross site boundaries.
- File access rules must preserve the current release gate behavior.
- Uploads must stay on the approved extension and size policy already used by the admin portal.

## Performance

- Customer routes should load only the data needed for the current screen.
- Avoid global dashboards that eager-load large unrelated collections.
- Heavy reporting and internal-only data must remain out of customer pages.
- File previews should use preview-safe formats and not eagerly load production files unless requested.

## Download gate parity

- Preserve the current legacy rules for `scanned` and `sewout` customer visibility.
- Preserve the current payment, balance, topup, single-order credit, and global credit checks.
- Preserve preview-safe access for preview file extensions even when full release is blocked.

## Payments

- Do not rely on browser redirect success alone to mark invoices paid.
- Payment confirmation must support server-side reconciliation from the provider response or callback.
- Marking billing rows paid and storing customer credit must be auditable and repeatable.

## Quote negotiation

- Customers should be able to reject a quote with a reason.
- Customers should be able to propose an acceptable price target.
- Admin should be able to accept the target, counter-offer, or keep the original amount.
- This must remain a controlled workflow, not an uncontrolled self-service price edit.
