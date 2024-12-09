# ITSM-NG Changelog

## ITSM-NG - 1.5.1

### Add

* New configuration options for OpenID Connect
* New username mapping for OpenID Connect 

### Fixes

* Fix encoded & in url
* Fix blank page due to an itemtype set to 0
* Fix passing null value as parameter for mb_strlen() and sscanf()

## ITSM-NG - 1.5.0

### Add

* Add phpCAS 1.5 compatibility

### Fixes

* Fix dark palette logo
* Fix font family if user doesn't use accessibility feature
* Fix PHP 8.2 warning
* Fix prevent xss on browse views (Parent fixing)
* Fix escape external links URLs (Parent fixing)
* Fix rights checks in export feature (Parent fixing)
* Fix sanitize help URL (Parent fixing)
* Fix ensure emails are always attached to current user (Parent fixing)
* Fix prevent SQL injection (Parent fixing)
* Fix prevent XSS through dashboard (Parent fixing)
* Fix prevent XSS on external links (Parent fixing)
* Fix prevent XSS on search button (Parent fixing)
* Fix remove RSS feeds autodiscovery (Parent fixing)

## ITSM-NG - 1.4.0

### Add

* Add Update file for 1.4.0
* Allow admin to change user accessibility shortcuts
* Add placeholder to specify scopes input format in openid conf

### Updates

* Update to 1.4.0
* Update chat integration
* Prevent XSS on formatted user link (fix from parent)
* Prevent XSS on generated links (Parent project CVE fixing)

### Fixes

* Fix SQL Injection from API (Parent project CVE fixing)
* Fix access to debug panel (Parent project CVE fixing)
* Fix input validation on email link (Parent project CVE fixing)
* Fix XSS on login page
* Fix update files for 1.1.0 and 1.2.0
* Fix pending status translation
* Fix accessibility shortcut modal
* Fix logo size
* Fix import of LDAP users
* Fix PHP8 compatibility issue

## ITSM-NG - 1.3.0

### Add

* Add Update file for 1.3.0
* Add chat notifications feature
* Add accessibility feature

### Updates

* Update to 1.3.0
* Improve OIDC connexion

### Fixes

* Remove tests dir in vendor for release (Parent project CVE fixing)
* Fix injection on SVG file upload (Parent project CVE fixing)
* Fix CSS XSS injection (Parent project CVE fixing)

## ITSM-NG - 1.2.0

### Add

* Add Update file for 1.2.0
* Add Special status page in dropdown/tools
* Add Special status page to add new status
* Add Status customization (Name, weight,color or if it's activate)
* Add Profile right to Special status (desactivate by default)
* Add Mapping openID connect page to get proply all informations on the user
* Add New command **php bin/console itsmng:oidc:update** to force evry user of OIDC to update their information

### Updates

* Update to 1.2.0
* Update the list of statuses with the new statuses created and sorted

### Fixes

* Fix translation don't apply on a button

## ITSM-NG - 1.1.0

### Add

* Add OpenID connect authentication
* Add OpenID connect cofiguration page
* Add Button on login page when OpenID connect is activate

## ITSM-NG - 1.0.1

### Add

* Add first update file for 1.0.1
* Add itsmng.png
* Add itsmn version
* Add itsm update function
* Add itsm update management in update console command
* Add header

### Updates

* Update setup title
* Update empty data
* Update translations
* Update console command name
* Update to 1.0.1

### Fixes

* Fix logo css on setup
* Fix undefined var

### Remove

* Remove telemetry
* Remove issue and pull request template
* Remove useless folder

## ITSM-NG 1.0.0

* Initial version