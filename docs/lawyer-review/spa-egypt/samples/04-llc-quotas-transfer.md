# Sample 04 — Small LLC Quotas Transfer

**Deal:** A single quota-holder sells 100% of his 5,000-quota interest in a
family-owned LLC (capital EGP 500,000) to his cousin. EGP 800,000 cash, paid
in three installments. Intra-family + small-business case to stress-test the
template's flexibility downward.

**Key differences from Sample 01:**

### 1. Drastically reduced complexity

This is closer to **عقد تنازل عن حصص** (assignment of quotas) than a full
SPA. **Reviewer: is the SPA template overkill for small-business cases?
Should we have a separate "quotas-assignment" simplified template?**

The current draft uses the full SPA structure (10 clauses). For a deal this
small that's likely 6–7 pages too long.

### 2. Installment payment with no escrow

> "يُسدَّد الثمن البالغ 800,000 جنيه مصري على ثلاثة أقساط متساوية:
> القسط الأول 266,666 جنيه عند التوقيع.
> القسط الثاني 266,667 جنيه بعد 6 أشهر من التوقيع.
> القسط الثالث 266,667 جنيه بعد 12 شهراً من التوقيع.
> ولا تنتقل ملكية الحصص للمشتري إلا بعد سداد كامل الثمن، ويُسجَّل ذلك
> رسمياً في السجل التجاري عقب آخر دفعة."

**Reviewer:**
- Is conditional ownership transfer (only on full payment) the right
  mechanic? Or does Egyptian law require full transfer at signing with
  the unpaid balance as a debt?
- The "retention of ownership" clause — is it enforceable under
  Article 425 of the Civil Code (vente à crédit / sale with retention
  of title)?

### 3. No reps & warranties

For an intra-family deal, the current draft omits reps & warranties. **Is
this acceptable in Egyptian practice, or does the seller still owe statutory
warranties (against eviction Art. 429, against hidden defects Art. 442)
regardless of the contract being silent?**

### 4. Pre-emption — single quota-holder

Companies Law 159/1981 pre-emption rules **do not apply** when there are no
other quota-holders. The draft skips clause 4 (pre-emption notice).

**Reviewer:** confirm that for a sole-quota-holder transfer, no
pre-emption mechanics are required.

### 5. Simplified closing

No GAFI notification because LLC (not JSC). Only:
- Notarized quota-assignment deed
- Update to commercial register (extract reissued)
- Update to quota-holders register

> "يتم نقل الحصص بموجب عقد تنازل عن حصص موثَّق لدى الشهر العقاري بقلم
> توثيق ___________ ، ويُقدَّم العقد للسجل التجاري لاستخراج مستخرج
> رسمي محدَّث للشركة."

**Reviewer:** is this the right procedure for a 100% LLC transfer in
Cairo / Alexandria / Giza? Any regional differences?

### 6. Family relationship disclosure

The buyer is the seller's cousin. Some Egyptian commercial-registry
officials require disclosure of family relationships for tax-fraud
screening.

> "يقر الطرفان بأنهما تربطهما صلة قرابة من الدرجة الرابعة، وأن البيع تم
> بسعر السوق العادل وفقاً لتقدير محاسب قانوني مستقل."

**Reviewer:** is this disclosure customary or mandatory? What's the rule on
"sale at fair market value" for related-party transactions for tax
purposes?

---

## Citations specific to this draft

| Article | Used in clause |
|---|---|
| Civil Code 425 | Sale with retention of title (clause 2 — installment + ownership transfer) |
| Companies Law 159/1981 Art. 85 | LLC quota-holder rules |
| (Possibly) Civil Code 442 | Hidden-defect warranty (still applies even when contract is silent?) |
| Income Tax Law 91/2005 + Tax Treaty (none) | Related-party fair-value rule |

**Reviewer please confirm.**

---

## Reviewer flags expected

- **Should this case use a different (simpler) template?** Probably yes —
  rec is to add a separate `quotas-assignment-simple` template for deals
  < EGP 5M / sole-quota-holder / intra-family.
- Conditional ownership transfer on installment payments — Egyptian law
  position?
- Tax treatment of intra-family sales (sale-at-fair-value rules, gift-tax
  re-characterization risk)
- Notarization vs. private writing — which is required for LLC quota
  transfer in Egypt today?
