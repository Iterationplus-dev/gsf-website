# Managing the website

A guide for the people who run the Global Support Foundation website day to day. No technical background assumed.

Sign in at **`/admin`** with the account you were given. If you do not have one, ask a super administrator to create it — please don't share logins, because the record of who changed what depends on each person having their own.

---

## The one rule that matters most

This website is shown to donors, development partners and government agencies. Everything on it should be true and checkable.

The system is built to help you with that:

- **Draft** content is invisible to the public. Nothing appears until someone publishes it.
- **Demo** records can *never* be published while the demo flag is set.
- An **impact figure won't publish without a source** recorded against it.
- **Media is unapproved by default** and won't display until someone approves it.

If you are not sure whether something is accurate, leave it as a draft and write what needs checking in the **Provenance** tab. A page that is missing is far less damaging than a page that is wrong.

---

## Finding your way around

The sidebar is grouped by what you are trying to do:

| Group | What is in it |
|---|---|
| **Content** | Everything published on the site, plus impact figures and media |
| **Giving** | Donations and donation campaigns |
| **Engagement** | Enquiries from the public, and newsletter subscribers |
| **Settings** | Website settings, redirects and user accounts |

The dashboard shows what needs attention: drafts with outstanding review notes, enquiries nobody has answered yet, and — if you have permission to see them — donation totals.

---

## Content

Everything published on the site lives under **Content → Contents**: pages, programs, projects, news articles, stories, awards, people, partners, publications and policies. They share one editor because they behave the same way.

Use the **Type** filter to narrow down to what you came for.

### Creating or editing

The editor has five tabs.

**Content** — the title, the web address (slug), a short summary, the main text, and the featured image.

- The **slug** is the web address. On a new item it fills itself in from the title. **Changing the slug of something already published breaks every existing link to it** — if you must, add a redirect (see below).
- The **summary** is what appears on cards, in search results, and as the page's description in Google. Write one or two real sentences.
- The **featured image** is chosen from media that has already been uploaded and approved.

**Project details** — appears only for projects. Location, dates, status, who benefited, objectives, activities, outcomes, SDGs, gallery and documents. Enter list fields one item per line.

**Publishing** — status, publication date, featured flag and ordering.

- **Draft** — invisible to the public.
- **Scheduled** — appears by itself when the publication date arrives. No one needs to be at a computer.
- **Published** — live now.

An item needs both a status of Published (or Scheduled) *and* a publication date to appear.

**SEO** — optional. Left empty, the title and summary are used, which is usually right.

**Provenance** — never published. Where the content came from, and what still needs checking. This is how the next person can tell verified fact from placeholder. Please use it.

### Publishing

If you are an **Editor**, you can write and edit but not publish — the status field is locked. Save your work as a draft and ask a content manager or administrator to review it.

If you are a **Content manager** or **Administrator**, you can publish.

### Demo content

Some records are marked **Demo**. These were created during development to show how the system works — the sample projects, for instance. They are not real GSF work.

They cannot be published while the flag is set. When you have a real project to record, either replace the demo record's content entirely and untick Demo, or delete it and start fresh.

---

## Media

**Content → Media.**

Upload the **highest-quality original you have**. Display sizes are generated automatically, so a large file is not a problem — and starting from a compressed copy means you can never get the quality back.

Every upload arrives **unapproved with no alt text**, and won't display anywhere until you fix both.

**Alt text** is what a blind visitor hears and what Google reads. Describe *what the image shows*:

- ✅ "Six women seated around a table during a co-operative governance training session in Port Harcourt"
- ❌ "Image", "photo1.jpg", "training"

Leave alt text empty only for purely decorative images.

**Before approving a photograph of identifiable people, make sure there is consent to publish it.** This matters most for photographs of children and of programme beneficiaries.

Use the **Missing alt text** filter to find images that need describing.

### Certificates and scans

Award certificates are delivered at the highest quality setting and open full-size when clicked, because the whole point is that the citation can be read. Upload the best scan you have.

---

## Impact figures

**Content → Impact metrics.** These are the counters on the homepage and impact page.

Every figure needs a **source** — where it comes from: attendance registers, training reports, registration records, a named project report. **The system will refuse to publish a figure without one.** That is deliberate. A number on a donor-facing website is a claim, and you may be asked to back it up.

Record the **year** it covers and the **geography** it applies to. "1,200 people trained" means something quite different with and without "in Rivers State, in 2025".

If you don't have a defensible figure, don't publish one. The impact page reads perfectly well without it.

---

## Donations

**Giving → Donations.** Visible only to donation managers, administrators and super administrators.

You can search, filter by status, campaign and date, and export a selection to CSV.

**You cannot edit or delete a donation.** This is intentional: these records are financial evidence and must keep matching Paystack's own records.

**Statuses:**

- **Pending** — checkout started, payment not yet confirmed. Normal for a few minutes; investigate anything still pending after an hour.
- **Successful** — confirmed by Paystack on our server. Money arrived.
- **Failed** — the payment did not go through.

**Resending a receipt:** use the action on the row. Available for successful donations only.

### Campaigns

**Giving → Campaigns.** These are what a donor can choose to support on the Donate page.

Only **General Donation** is active out of the box. The others are set up but switched off, because a campaign is a promise about what the money does. Activate one when GSF has agreed what it funds and what will be reported back.

---

## Enquiries

**Engagement → Enquiries.** Contact, partnership and volunteer submissions.

What someone wrote is read-only — it is a record of what they said. The only thing you change is the **status**: New → In progress → Closed.

Enquirers get an automatic acknowledgement. That is not an answer, so please work through the New ones.

---

## Newsletter subscribers

**Engagement → Subscribers.**

- **Pending** — subscribed but hasn't confirmed the email yet.
- **Subscribed** — confirmed, receiving email.
- **Unsubscribed** — opted out.

**Never export this list to a third party, and never email people who are not Subscribed.** Consent timestamps are recorded against each subscriber for exactly this reason.

---

## Website settings

**Settings → Website settings.** The office address, phone numbers, email, social profiles and registration details.

- The **contact address here is what places the map** on the contact page. Correct it here, not anywhere else.
- **Social profiles left empty are simply omitted** from the site. Don't enter a personal profile as an organisational account — the previous website did, and it read as unprofessional.
- **Registration number and authority** appear in the footer and transparency section once filled in. Enter these only from the actual registration certificate.

---

## Redirects

**Settings → Redirects.**

When you change the slug of something already published, add a redirect from the old address to the new one. Otherwise every existing link — in emails, in donor documents, in Google — breaks.

Old addresses from the previous website are already redirected.

---

## User accounts

**Settings → Users.** Super administrators only.

| Role | Can do |
|---|---|
| Super administrator | Everything, including managing accounts |
| Administrator | All content, media, operations and settings |
| Content manager | Write, edit **and publish** content; manage media |
| Editor | Write and edit drafts; manage media. Cannot publish |
| Donation manager | View, export and resend receipts for donations. No content access |

**When someone leaves, deactivate their account rather than deleting it.** Deactivation blocks access immediately, and keeps the record of what they changed attributable.

Turning on **two-factor authentication** under your profile is strongly recommended for anyone who can publish or see donations.

---

## Things worth knowing

**Changes are recorded.** Who changed what, and when. Field names only — never the content itself.

**Deleted content can be restored.** Use the Trashed filter. Permanent deletion is separate and restricted.

**The site search only finds published content.** If you can't find something on the public site that you know exists, it is almost certainly still a draft.

**Scheduled items publish themselves.** No one needs to be online.

---

## If something looks wrong

1. **Something published that shouldn't be** — set it back to Draft. It disappears immediately.
2. **Wrong figure on the homepage** — Content → Impact metrics, untick Published.
3. **An image shouldn't be public** — Content → Media, untick Approved.
4. **A donation looks wrong** — do not attempt to edit it. Note the reference and raise it with whoever administers the site; the payment record and Paystack's own record have to stay in step.

---

## Still outstanding

`docs/content-audit.md` lists what the previous website could not evidence and what GSF still needs to supply — award details, the board list, registration information, approved policies and verified impact figures.

Please keep it up to date as items are resolved.
