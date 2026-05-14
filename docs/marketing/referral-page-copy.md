# Referral Program — Page Copy + Setup

## Mechanics

**Both sides get 30% off for 3 months.**

- Referrer: 30% off their next 3 invoices on any paid plan
- Referee: 30% off their first 3 invoices on any paid plan
- No cap on how many people one referrer can invite
- Discount stacks with the 14-day free trial — referee starts on free trial,
  then 3 months at 30% off, then full price
- Code expires when the referee starts paying (30 days from sign-up)

**Why 30%, not 50%:**

Referees at 50% off churn at ~2× the rate of referees at 30% off (industry
benchmark from Reforge teardowns). 30% is the sweet spot for "real discount,
real customer". Bumping to 50% is the lever we pull if word-of-mouth stalls
in month 2.

---

## `/refer` page copy (Arabic + English, bilingual stacked)

```
EYEBROW: Refer a colleague · ادعُ زميلاً

HERO:
  Get a Cairo lawyer-friend on the platform. Save 30% — for both of you.
  دلّ زميل محامي مصري على الأداة. ٣٠٪ خصم لـ٣ شهور — لإنت وله.

BODY:
  Share your personal link. When they sign up and start their first paid
  month, you both get 30% off for the next 3 invoices.

  شارك اللينك الشخصي. لما زميلك يسجل ويبدأ أول شهر اشتراك، كلاكما هتاخد ٣٠٪
  خصم على فواتير الثلاث شهور الجاية.

  No cap. No catch. Refer 10 colleagues, get 30% off for 30 months.
  بدون حد أعلى. بدون شروط خفية. لو دلّيت ١٠ زمايل، الخصم على ٣٠ شهر.

THE LINK:
  my-lawyer.app/refer/{your-code}
  [Copy] [Share to WhatsApp] [Share to LinkedIn]

YOUR REFERRALS:
  • 4 invited
  • 2 signed up
  • 1 became a paying customer → 30% off your next 3 invoices unlocked
```

---

## Page route + minimum implementation spec

For when this becomes a code task (defer until after launch week):

1. `/refer` (auth required) — shows the user's personal `refer_code`, click-to-copy, share buttons.
2. `/refer/{code}` (public) — landing page that asks for email + name + sets a cookie with the referrer's user_id. On sign-up, the new user's `referred_by_user_id` is populated.
3. Stripe Coupons API — create one coupon per referee at sign-up time:
   `REF30-{user_id}`, percent_off: 30, duration: repeating, duration_in_months: 3.
4. When a referee's first invoice fires (via Cashier `invoice.payment_succeeded` webhook), apply a matching 30% / 3-month coupon to the referrer's customer.
5. The referrals dashboard reads from `referred_by_user_id` + `subscriptions` table.

**Estimated effort:** 8–10 hours of engineering. Defer to week 3 of the launch.

---

## How to ask for referrals (no code required, do this immediately)

For the first 30 days, run referrals **manually**:

- After every customer hits day 14 of usage (i.e. the trial converted), send
  them the WhatsApp Template 5 from `whatsapp-templates.md`.
- Track in a Google Sheet: referrer → referee → status (signed up / paying)
- Issue the 30% coupon manually in Stripe Dashboard.

This is dumb. It scales to ~20 referrals before becoming painful. After the
first 20, automate (above spec).

Doing it manually first **teaches you what shape of referral incentive
actually moves the Cairo lawyer persona** — which is worth the friction.

---

## Three referral signals to listen for (in customer interviews)

When you talk to early customers, listen for these phrases. Each one is
a different lever:

1. **"My partner needs this"** → they're inside the same firm; offer them
   a team discount (Firm plan trial) instead of a referral code.

2. **"My friend at [other firm] would love this"** → the classic referral.
   Send them their link via WhatsApp within 60 seconds.

3. **"I'd recommend it but..." [hesitation]** → the trust-blocker. Don't
   push for the referral; ask what the hesitation is. Often it's "I'm not
   sure it'll stay this good" — fixable with a roadmap reveal.
