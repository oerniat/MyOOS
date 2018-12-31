<?php
/* ----------------------------------------------------------------------
   $Id: login.php,v 1.3 2007/06/13 16:15:14 r23 Exp $

   MyOOS [Shopsystem]
   https://www.oos-shop.de
   
   
   Copyright (c) 2003 - 2018 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: login.php,v 1.11 2002/06/03 13:19:42 hpdl 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */


define('HEADING_TITLE', 'Anmeldebereich');

define('HEADING_RETURNING_ADMIN', 'Anmeldebereich:');

define('TEXT_RETURNING_ADMIN', 'Nur für Mitarbeiter!');
define('ENTRY_EMAIL_ADDRESS', 'E-Mail-Adresse:');
define('ENTRY_PASSWORD', 'Passwort:');
define('ENTRY_FIRSTNAME', 'Vorname:');
define('IMAGE_BUTTON_LOGIN', 'Übertragen');

define('SECURITYCODE', 'Sicherheitscode:');
define('TEXT_PASSWORD_FORGOTTEN', 'Passwort vergessen?');

define('TEXT_WELCOME', ' Willkommen bei OOS <br />MyOOS [Shopsystem]!</p><p>Use a valid eMail and password to gain access to the administration console.');

define('TEXT_LOGIN_ERROR', '<font color="#ff0000"><b>ERROR:</b></font> Falscher Benutzername oder Passwort!');
define('TEXT_FORGOTTEN_ERROR', '<font color="#ff0000"><b>FEHLER:</b></font> Vorname und Passwort sind nicht hinterlegt!');
define('TEXT_FORGOTTEN_FAIL', 'Sie haben es mehr als 3x versucht. Aus Sicherheitsgrnden kontaktieren Sie bitte Ihren Administrator um ein neues Passwort zu erhalten.');
define('TEXT_FORGOTTEN_SUCCESS', 'Das neue Passwort wurde an Ihre E-Mail-Adresse gesendet. Überprüfen Sie Ihren E-Mail-Eingang und klicken Sie zurück um sich anzumelden.');

define('ADMIN_EMAIL_SUBJECT', 'Neues Passwort'); 
define('ADMIN_EMAIL_TEXT', 'Hallo %s,' . "\n\n" . 'Sie können den redaktionellen Bereich mit folgenden Passwort betreten. Nach erfolgtem Login, ändern Sie bitte aus Sicherheitsgrnden Ihr Passwort!' . "\n\n" . 'Website : %s' . "\n" . 'Benutzername: %s' . "\n" . 'Passwort: %s' . "\n\n" . 'Danke!' . "\n" . '%s' . "\n\n" . 'Dies ist eine automatisierte Antwortmail. Bitte beantworten Sie diese nicht!'); 

