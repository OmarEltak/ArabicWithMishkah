---
title: Bilingual export (.pdf and .docx)
section: Drafting
order: 25
---

Every contract drafted in Arabic can be exported in three forms:

- **Arabic .pdf / .docx** — the legally binding source.
- **Bilingual .pdf / .docx** — Arabic + English side-by-side. The Arabic column is the binding one; the English column carries a "WORKING TRANSLATION — NOT LEGALLY BINDING" stamp.

## Generate the English translation

From any contract page, click **Translate to EN**. The first time takes ~10 seconds; subsequent re-exports of the same body are instant (we cache by content hash).

The translation is glossary-locked — civil-law terms of art (e.g. *الفسخ* → *rescission*, not *termination*) are pinned to the Cairo/MENA legal-translation glossary, not the LLM's default vocabulary.

## Why we don't sign the English

Civil-law contracts under Egyptian, Saudi, Emirati, etc. law are binding in their Arabic form. An English version is a working aid — useful for a counterparty who can't read Arabic, never the legal contract. The bilingual export makes this explicit with the stamp + a "language-prevailing" clause in the footer.

## Re-translating

Edit the Arabic body, save, then click the refresh icon next to the translation language. The new translation lands in ~10s. The old translation is overwritten — the contract version history tracks the Arabic source, not its translations.
