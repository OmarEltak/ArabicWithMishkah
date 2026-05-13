# Transactional Email Setup

> What this covers: outbound mail for password resets, email verification,
> and (later) usage digests. Inbound mail (support inbox, reply parsing)
> is out of scope.

The app ships with `MAIL_MAILER=log` so local dev doesn't need credentials.
**Production must change this** — otherwise password-reset and email-verify
links go to `storage/logs/laravel.log` and the user never receives them.

## Pick a provider

| Provider | Pricing | Why | Set-up time |
|---|---|---|---|
| **Postmark** | Pay-as-you-go, $1.25/1k after first 100/mo | Best deliverability for transactional. Fast support. Sender verified by domain. | 30 min |
| **Resend** | First 3k/mo free, then $20/50k | Cleanest API, good DX, growing. | 20 min |
| **AWS SES** | $0.10/1k | Cheapest by far. More moving parts (sandbox → production review, SNS for bounces). | 1–2 hr |
| **SMTP (any)** | varies | Last resort. Use only if your host requires it. | depends |

For a 0–10k MAU legal-AI app, Postmark or Resend is the right call. SES becomes
attractive past ~100k mail/mo.

## Postmark (recommended)

1. Register at https://postmarkapp.com.
2. Create a **Server** (call it `my-lawyer-prod`). Servers are isolated
   sender pools — keep prod separate from staging.
3. Verify your sender domain: Postmark gives you DKIM + Return-Path DNS
   records to add at your DNS provider. Without DKIM, Gmail/Outlook will
   spam-bin you on day one.
4. Copy the Server Token (NOT the Account Token).
5. Set in production env:
   ```env
   MAIL_MAILER=postmark
   POSTMARK_API_KEY=<server-token>
   MAIL_FROM_ADDRESS=no-reply@your-domain.com
   MAIL_FROM_NAME="My-lawyer"
   ```
6. Test:
   ```bash
   php artisan tinker --execute='Mail::raw("test", fn($m) => $m->to("you@example.com")->subject("ping"));'
   ```
   You should see the mail land within seconds and Postmark dashboard
   should show the delivery.

## Resend

1. Register at https://resend.com.
2. Add and verify your sender domain (same DKIM/SPF dance).
3. Create an API key. Copy the `re_xxx` value.
4. Set in production env:
   ```env
   MAIL_MAILER=resend
   RESEND_API_KEY=re_xxx
   MAIL_FROM_ADDRESS=no-reply@your-domain.com
   MAIL_FROM_NAME="My-lawyer"
   ```

## AWS SES

1. Verify your sender domain in the SES console.
2. **Request production access** — SES starts every account in a sandbox
   that only sends to verified recipients. The form takes ~24 hours to
   approve.
3. Create an IAM user with `AmazonSESFullAccess` (or a tighter custom
   policy). Generate access keys.
4. Set in production env:
   ```env
   MAIL_MAILER=ses
   AWS_ACCESS_KEY_ID=AKIA...
   AWS_SECRET_ACCESS_KEY=...
   AWS_DEFAULT_REGION=us-east-1
   MAIL_FROM_ADDRESS=no-reply@your-domain.com
   ```

## DNS records you'll need

Whichever provider you pick, you'll add 2–4 DNS records:

| Record | Why |
|---|---|
| **DKIM** (CNAME or TXT) | Cryptographic signature on every outbound mail. Mandatory for deliverability. |
| **SPF** (TXT) | Tells receivers your provider is authorized to send for your domain. |
| **DMARC** (TXT) | Policy stating what to do with mail that fails SPF/DKIM. Start with `p=none` for monitoring, ratchet to `p=quarantine` once stable. |
| **Return-Path / MX** (Postmark/Resend) | Tracks bounces. Optional but recommended. |

If you skip these, expect 70%+ of your mail to land in spam on day one.

## Verifying it works

After setting env + DNS:

```bash
# From the production server
php artisan secrets:check
# Expect MAIL_MAILER + MAIL_FROM_ADDRESS green

# Send a real test
php artisan tinker --execute='Auth::loginUsingId(1); Mail::raw("smoke", fn($m) => $m->to(auth()->user()->email)->subject("smoke"));'
```

Then trigger a password reset from `/forgot-password` against a real
inbox. It should arrive in under 30 seconds.

## What breaks if you skip this

- `/register` users never see the verification email → can't pass the
  `verified` middleware → can't access `/dashboard`.
- `/forgot-password` silently writes the reset link to the log file →
  account recovery impossible for anyone but devs.
- Future digest emails (P2 #21) silently never fire.

The `secrets:check` command catches all of these as required.
