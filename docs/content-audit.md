# Content and asset audit

Audit date: 9 September 2026. Primary website: https://globalsupportfoundation.org/. The web reader could not retrieve the site; the direct HTTP probe also failed. Local backup is the available evidence, not independent verification of current operations.

## Source register

| Source | Findings | Migration decision |
| --- | --- | --- |
| `backup/application/views/pages/view_about.php` | Founded August 2015; mission, vision, cooperative formation, business advice, training, consultancy, advocacy | Rewrite conservatively with source provenance |
| `view_team.php` | Golden Chudi Anikwe, Executive Director; founder biography; management profiles | Use Executive Director as evidenced title; current staff appointments require confirmation |
| `view_contacts.php` | No 1 Ohiamini Street, Rumoula Road, Port Harcourt, Rivers State, Nigeria; info@globalsupportfoundation.org; +2348126138041, +2347033351303, +353857864995 | Preserve address spelling; map by address, never guessed coordinates; confirm currency before launch |
| `view_testimonies.php` | Ireland cooperative accounts from 2008–2010; CSS history | Historical context, not GSF beneficiary stories or results |
| Right column of `view_testimonies.php` | `2008.jpg`, `2007.jpg` certificate scans | Preserve originals; draft award records until recipients and award names are transcribed and approved |
| `view_home.php` | Target groups, mission, videos, dated advocacy material | Preserve source; omit unsourced employment statistics and template countdowns |
| `assets/images/logo.jpg` | Circular dark green/charcoal botanical mark with red wording | Forest green foundation, red accent, warm neutral surfaces |
| `assets/images/t1.jpg` | Founder portrait explicitly linked in team page | Founder asset |
| `assets/images/g*.jpg`, `gl*.jpg` | Legacy photo gallery | Preserve; captions, subjects, consent and project associations need review |
| Template navigation/footer | Personal Facebook link; other social icons without URLs | Do not invent organizational accounts; founder LinkedIn supplied by user |
| Config routes | about-us, support-us, gallery, testimonies, form, contact-us, our-team, partnership | Preserve or redirect to matching new pages |

## Information missing or requiring administrative review

No database dump, annual report, audited financial statement, approved policy, registration certificate, current trustee list, verified donor list or downloadable publication was found in the initial extension inventory. No reliable GSF impact totals or sufficiently documented completed projects have been established. Agriculture, technology and community project examples must stay demo drafts until approved. Do not attribute staff achievements from prior employers to GSF.

Founder biography distinguishes study toward qualifications from completion. Do not turn his research interest into an awarded doctorate or fabricate a founder quotation. The user supplied a CEO label, but the available GSF record says Executive Director; title reconciliation is a launch review item.

Legacy configuration includes embedded integration configuration. Do not migrate credentials, execute legacy PHP, or expose the backup through the production document root. Deploy only the new application's public directory.

**Credential exposure — action required.** `backup/application/config/database.php` contains the previous hosting account's database username and password in plaintext. The `backup/` directory is therefore excluded from version control (`.gitignore`) and must never be placed inside the document root. Because these credentials existed in a web-accessible source tree on the old host, they should be treated as compromised: GSF should rotate the database password on the legacy hosting account and confirm whether the old site or its database is still reachable.

## Publication rules

All seeded demo projects, new articles, unknown awards, policy placeholders and unverified metrics are draft. Public queries must enforce publication status and publication date. Retain provenance and review notes in the CMS. Original photos and scans remain available; optimized variants must not replace original certificate files.
