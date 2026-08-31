# Policy review before public launch

The pages are detailed drafts matched to the current V1 implementation. They are not a legal opinion or a guarantee of compliance in every jurisdiction. Do not remove the draft warning simply to make the site appear launch-ready.

The user has confirmed the legal operator as **WAMEER**, registered in **Ghana**, with postal address **PMB CT 3, Cantonments, Accra, Ghana**. The support and privacy contact is **support@grantnivo.com**. No telephone contact is published. WAMEER links to https://wameergroup.com/. These details remain configured in the application and environment examples. At the user’s request, the policy body displays only the support/privacy email, not the ownership statement, registration country or postal address. The existing WAMEER footer link is unchanged. Confirmation of operator details does not represent a legal review of the policies.

Confirm before launch:

- Ensure production uses the confirmed `LEGAL_OPERATOR`, `LEGAL_COUNTRY`, `LEGAL_ADDRESS`, and `PRIVACY_CONTACT_EMAIL` values; the email must be monitored for support and privacy requests.
- The jurisdictions and age requirements applicable to the business and intended users, lawful grounds for each purpose, and any registration or notification obligations. Obtain qualified review of the final Terms, Privacy notice and Cookie notice.
- Actual hosting, database, backups and email providers, processing countries, contracts and transfer safeguards. Replace draft qualifications with verified specifics where needed; audit external image/font requests and any new embeds.
- A documented retention/deletion schedule for waitlist records, consent evidence, suppression records, analytics, sessions, security logs, audit history and backups. There is currently no automatic analytics purge or self-service waitlist deletion route. Assign an owner to handle contact requests, unsubscribe/deletion and proportionate identity checks.
- Before sending waitlist emails, implement the required withdrawal/unsubscribe process, honor `unsubscribed_at`/suppression records, and verify provider behavior. Signup currently records interest; it does not send emails or subscribe people to unrelated marketing.
- Do not treat existing rows with null consent fields as having accepted these new policies. Check historical evidence before using those addresses for a new purpose.
- Production HTTPS, mail delivery, restricted staff access, database access, backups, incident procedures and secret management are operational requirements, not guarantees provided by policy text.

Set `LEGAL_POLICIES_REVIEWED=true` only after these details and the reviewed wording are ready. The current template has its draft banner commented out; this presentation choice does not change the review status. Refresh the Laravel config cache after environment changes.

## Acceptance evidence

The server records `consented_at`, `privacy_policy_version`, `terms_version`, and `consent_version` on a new signup. Versions come from `config/legal.php`, not client input. Duplicate attempts cannot change original details or consent. No consent IP/browser fingerprint is added.

Statement version **2026-08-31**, initially unchecked:

> I agree to the Terms & Waitlist Conditions and acknowledge the Privacy Policy. I request waitlist updates and early-access invitations by email. I can withdraw this request at any time.

Linked notices: Privacy version **2026-08-31** (`resources/views/pages/policies/privacy.blade.php`) and Terms version **2026-08-31** (`resources/views/pages/policies/terms.blade.php`). The initial drafts require final review; finalizing or materially changing wording must create a new version before collecting production acceptance.

Before changing any accepted wording or operator details, preserve a dated rendered copy of the notices, configuration values and statement in controlled records, and commit the corresponding source version. Update the relevant version keys and visible dates together. A version string without its historical text is insufficient evidence of what a person saw. Do not change earlier rows to the latest version. Seek a new choice where a new purpose requires one.

## Optional analytics

`grantnivo_analytics` records accepted/declined choices for up to one year. No choice means off. `/cookies` provides equally available enable/decline controls; no optional tracking is necessary to read that page or join the waitlist. DNT/GPC, staff and recognized bot exclusions still apply. Declining expires `grantnivo_visitor` and stops future events but does not erase historical analytics rows. Analytics totals now describe consenting traffic only.
