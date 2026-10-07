# PHASM theme changelog

Theme by IamPhasm. Every change bumps `Version` in `style.css` and `PHASM_VERSION` in `functions.php`.

## 1.5.0 — 2026-10-07
- Added: contact form. "Start a conversation" opens it in the middle of the page with the site blurred behind; Name, E-mail, Phone, Message (max 400 characters), Send, Reset and a close button. After sending it shows a thank-you message with the PHASM logo. Links to #contact-form open it from anywhere.
- Added: Site inbox in the admin, with Messages and Closed messages. Close a message with the Closed checkbox on the message, or Close/Reopen in the list. The menu shows how many messages are open.
- Added: Site inbox › Email settings: SMTP server, encryption, optional port, username and password (stored encrypted), sender, notification address and a test e-mail. The customer gets a dated copy of their message.
- Added: spam protection on the form (hidden honeypot field, security token, max 5 messages per 10 minutes per visitor).
- Changed: the divider line is now switched on or off per module in the Modules list.

## 1.4.0 — 2026-10-07
- Added: green divider line above every front page module (on/off in Customize › PHASM front page › Modules).
- Added: colour scheme per module: Light, Grey or Dark (inverted), picked next to each module in the Modules list.
- Changed: a module with nothing to show (no posts, no quotes) leaves no empty band.

## 1.3.1 — 2026-10-07
- Fixed: the About module no longer pulls in the homepage's page content (e.g. a "Sign up" block) by default. New checkbox in Customize › PHASM front page › About to show it.

## 1.3.0 — 2026-10-07
- Added: front page modules (What we do, About, Daily wisdom, Insights, Start a conversation). Turn each on or off and drag them into any order in Customize › PHASM front page › Modules.
- Added: Daily wisdom module. Add quotes with "+ Add quote" (quote and author); one quote is shown per day, in rotation.
- Changed: PHASM front page settings are now a panel with one section per module.

## 1.2.2 — 2026-10-07
- Added: "Show site title next to the logo" checkbox in Customize › Site Identity (off by default).

## 1.2.1 — 2026-10-07
- Added: "Back to top" link in the footer, with smooth scrolling (off when the visitor prefers reduced motion).

## 1.2.0 — 2026-10-07
- Added: auto-updates from GitHub releases (iamphasm/phasm-theme), with a "Check for updates now" link on the Themes screen.
- Added: GitHub Action that builds phasm.zip and publishes a release when a version tag is pushed.

## 1.1.0 — 2026-10-07
- Added: icon picker for the three front-page service boxes (Customize › PHASM front page), 25 built-in outline icons.
- Added: live preview of icon changes in the Customizer.
- Changed: author set to IamPhasm.

## 1.0.0 — 2026-10-07
- First release: front page, blog/archive, single post, page, search and 404 templates.
- Terminal palette (night #0B0F14, signal #00E08A, signal deep #007A4D) on the PHASM monochrome base.
- Customizer settings for hero, services, about, call to action, contact and footer.
