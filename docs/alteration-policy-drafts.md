# Alteration policy wording — drafts for approval

Task list items 1–3. These are drafts for Ese to approve. Nothing here is live yet. Once approved, paste each block into the page in **Admin → CMS → Pages**.

The system already enforces these rules. If you change a rule here, tell the developer so the code matches.

- The 7-day window counts from the delivery date DHL records.
- One ASO-covered alteration per order.
- At least 3 photos plus a measurement comparison.
- Only vetted partners are shown to customers.

---

## 1. Refund & Return Policy (`/page/refund-policy`)

### Replace the "Alteration Support" section with:

> **Alteration Support**
>
> **If we made a production error (Aso Signature fault).** If your garment was not made to the measurements you confirmed at checkout, including the material grade or quality you chose (grades are confirmed before production), we will arrange an alteration with one of our vetted alteration partners at our cost. Aso Signature covers one alteration per order due to a production error. Any further issues on that order are not covered under this provision.
>
> Before we confirm a claim as our error, we need photos of the garment and a measurement comparison. Lay the garment flat, measure it, and enter those measurements next to the ones you confirmed at checkout. You can do this from **My Account → Orders → Report a fit issue**.
>
> **If the garment matches your confirmed measurements (customer fault).** If your garment was made exactly to the measurements you submitted, but those measurements were wrong, the alteration is not covered by Aso Signature. We will give you a list of vetted alteration partners near you. You arrange and pay for the work directly with the partner, and the partner, not Aso Signature, is responsible for the outcome of that work once you engage them.

### In "What We Accept for Refund or Return", change the eligibility line to:

> To be eligible, including for an alteration claim, you must notify us within 7 days of receiving your item.

### In "How to Request a Refund or Return", change the first step to:

> For fit issues, use **Report a fit issue** on your order in My Account within 7 days of delivery. For anything else, email us at hello@asosignature.com within 7 days of delivery.

---

## 2. Terms & Conditions (`/page/terms-conditions`)

### Section 8 — Returns and Refunds: add after "Requests must be submitted within 7 days of receipt, with clear photo evidence.":

> Alteration claims follow the same 7-day window, counted from the date of delivery, and require photo evidence and a measurement comparison. See our Refund Policy and Fit Assurance Policy for full terms.

### Section 11 — Limitation of Liability: add as a new bullet:

> Alterations beyond the one alteration per order that Aso Signature covers for its own production error, including repeat issues on the same order, and any work carried out by an alteration partner after we have referred you to them. Alteration partners are independent businesses and are responsible for their own work.

---

## 3. Aso Fit Assurance Policy (new page, suggested URL `fit-assurance`)

Create a new CMS page holding the full process, and link to it from the Refund Policy and the footer.

> **Aso Fit Assurance**
>
> Every Aso Signature garment is made to the measurements you confirm at checkout. If something doesn't fit, here is how we help.
>
> **1. Report within 7 days.** Go to My Account → Orders → *Report a fit issue* within 7 days of delivery.
>
> **2. Show us the fit.** Add at least 3 clear photos: the full garment front and back, and the problem area with a tape measure visible. Then measure the garment where it doesn't fit. You'll see the measurements you confirmed at checkout alongside.
>
> **3. We review within 48 hours.** We compare the garment against your confirmed measurements and the material grade you chose.
>
> - **Our production error:** we arrange one alteration with a vetted partner near you, at our cost. This covers one alteration per order. Further issues on the same order are not covered under this provision.
> - **Made to your confirmed measurements:** we send you a list of vetted alteration partners near you. You arrange and pay for the work directly, and the partner is responsible for the outcome of their work.
>
> **Find a partner any time:** [Find an Alteration Partner](/alteration-partners)

---

## Internal fault-classification guide (staff only)

This text is already shown on every claim page in the admin (**Alterations → Claims → View**):

- **ASO / designer fault:** the garment was not produced to the spec the customer confirmed at checkout, including material grade or quality.
- **Customer fault:** the garment matches the confirmed spec, but the measurements the customer submitted were wrong.
- **One-off limit:** one ASO-covered alteration per order. The system blocks a second one.
- **Tolerance:** rows that differ by more than 2 cm / 0.75 in are highlighted as a guide only. Garments include ease, so check the fit preference before deciding.
