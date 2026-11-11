=== Allpay Payment Gateway ===
Contributors: Allpay
Tags: payment provider, Israel, платежная система, Израиль, Allpay
Requires PHP: 7.0
Requires at least: 4.7
Tested up to: 7.1.4
Stable tag: 1.3.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Accept Visa, Mastercard, Amex, Diners, Isracard, Apple Pay, Google Pay and Bit in WooCommerce. Payouts to an Israeli bank, installments, invoices.

== Description ==
The plugin allows you to accept online payments from all over the world (except for the Russian Federation) with funds credited to a bank account in Israel. Cards of major brands are supported: Visa, Mastercard, American Express, Diners, Discover, and JCB.

The plugin supports Apple Pay, Google Pay and Bit.

It also supports installments and digital invoices.

### Mobile App
The Allpay mobile application allows you to manage transactions.

### Digital Invoices
Automatic creation of digital invoices (kabala or hashbonit mas) through integration with a licensed service in Israel.

### Security
Payments are made via a secure channel, ensuring safety for the customer and transparency and legality for your business.

### Features
1. Low commission.
2. Support via email, Telegram, and WhatsApp.
3. Supported cards: Visa, Mastercard, American Express, Diners, Isracard, JCB.
4. Fast payment buttons: Apple Pay, Google Pay and Bit.
5. Payments from all countries (except the Russian Federation).
6. Installments (tashlumim).
7. Integration with digital receipts.
8. Mobile app for Android and iOS.
9. Automatic detection of payment page language. Supports 8 languages, including English, Hebrew, Russian, Arabic, and French.
10. Your logo on the payment page.
11. Currency display option: show prices in a customer-friendly currency while charging in ILS, USD, or EUR.

== Screenshots ==
1. Payment Page
2. Plugin Settings
3. Allpay Transactions Screen

== Installation ==
1. [Sign up](https://www.allpay.co.il/signup) on the Allpay website.
2. In your Allpay account, go to Settings > API Integrations and click the "Create Login/Key Pair" button.
3. Copy and paste the Login and Key into the settings of the Allpay plugin.
4. Done.

[Watch video instructions](https://www.allpay.co.il/integrations/wordpress#video)

== Frequently Asked Questions ==
= What are the onboarding requirements? =
Your business must be registered in Israel. For example, as an Osek Patur/Murshe (private entrepreneur) or Hevra Baam (LTD).

= How fast is the onboarding process? =
Same day.

= How often are funds credited to the bank account? =
On the 6th day of each month, with an option for weekly credits.

== Changelog ==
= 1.3.1 – 11 November 2026 =
* Security: Hardened the payment notification handler. Updating is strongly recommended.

= 1.3.0 – 9 November 2026 =
* New: Switched to Allpay API v15.
* New: Payment requests are sent as JSON.
* Fix: Webhook signature is now verified over all fields (signature v2), so orders are reliably marked as paid.
* Fix: Pre-authorized (held) payments are no longer marked as paid.

= 1.2.0 – 27 January 2026 =
* New: Payment currency selector added to plugin settings.
* New: Currency converter: show prices in a customer-friendly currency while charging in ILS, USD, or EUR.
* New: Tehudat Zehut (Israeli ID) field control
* New: Support of WP 6.9
* Fix: VAT calculation for delivery.
* Fix: Double stock deduction.
* Fix: Issue where orders were not marked as paid.

= 1.1.3 - 22 SEPTEMBER 2025 =
* New: VAT calculation support.
* Fix: Inclusion of taxes in payment.

= 1.1.2 - 31 AUGUST 2025 =
* Fix: Order status wasn’t always changing to paid.

= 1.1.1 - 20TH FEBRUARY 2025 =
* Fix: VAT calculation.

= 1.1.0 - 6TH NOVEMBER 2024 =
* New: Support for HPOS.
* New: Adapted to the new WooCommerce checkout page.
* Fix: SSL error.
* Improvement: The plugin now sends Allpay a full list of products so they can be included in the receipt.

= 1.0.4 - MONDAY, 25TH DECEMBER 2023 =
* New: Added payment page language support for ENG, HEB, and RUS.
* Fix: Payment error for logged-in users on some WP versions.
* Improvement: Aligned with the new Allpay payment page.

= 1.0.2 - THURSDAY, 10TH AUGUST 2023 =
* Improvement: Tested with WordPress v6.3.
