Mailjet Iframe
==============

Introduction
------------

Embeds the Mailjet newsletter app (campaigns, contacts, statistics) as an
iframe into the Drupal admin UI, so editors manage newsletters without a
separate Mailjet login.

The Mailjet UI is shown in the editor's admin language if Mailjet supports it
(English, German, French, Spanish, Italian), otherwise in English.

Installation
------------

* Install the module. The "editor" role gets the permission "Use the Mailjet
  newsletter app", if the role exists.
* Visit *Configuration › Web services › Mailjet Iframe*
  (`/admin/config/services/mailjet`) and enter the API key and secret key of
  the Mailjet account, see [app.mailjet.com](https://app.mailjet.com/).
* On save, the module reuses or creates an iframe API token at Mailjet.
* Editors open the app via the "Newsletter" link in the editor toolbar
  (`/admin/mailjet`).

Permissions
-----------

* "Use the Mailjet newsletter app": open the iframe at `/admin/mailjet`.
* "Administer mailjet_iframe configuration": change the Mailjet credentials.

Requirements
------------

* The Gin admin theme with Gin Toolbar (as in webtourismus/drupal-starterkit).
* PHP with the curl extension.
