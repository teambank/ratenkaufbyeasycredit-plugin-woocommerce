Changelog
=========

1.6.12
------

- ratenkauf by easyCredit kann nun auch mit der Kundenbezahlungsseite verwendet werden (bei Erstellung der Bestellung durch den Händler)
- Kompatibilität bis Wordpress v5.6, wooCommerce v4.9.1

1.6.11
-------

- Anpassung zur Kompatibilität mit PHP 7.4
- Erweiterung der REST API Routes um permission_callback
- Verbesserung der Kompatibilität mit Elementor
- Kompatibilität bis Wordpress v5.5.3, wooCommerce v4.7.1

1.6.10
------

- der Administrator kann nun auf die Transaktions-API zugreifen und Transaktionen bearbeiten
- wenn die Review Seite nicht vorhanden ist, wird ein Hinweis angezeigt, wie diese wiederhergestellt werden kann
- Übersetzungen in "Deutsch" sind nun in Du-Form formuliert, Deutsch (Sie) weiterhin in Sie-Form
- Kompatibilität bis Wordpress v5.5.1, wooCommerce v4.5.1

1.6.9
------

- der Link zu „Was ist ratenkauf by easyCredit“ wurde aktualisiert

1.6.8
------

- behebt einen NOTICE-Fehler, der auftrat, wenn Multi-Site nicht verwendet wird

1.6.7
------

- wooCommerce wird als Abhängigkeit im Multi-Site Betrieb nun auch erkannt, wenn es netzwerkweit aktiviert ist

1.6.6
------

- behebt einen Fehler bei der Anzeige des Transaktionsmanagers im Backend

1.6.5
------

- Kompatibilität bis Wordpress v5.4.1, wooCommerce v4.2.0
- "Zugangsdaten prüfen" funktioniert nun auch in Umgebungen mit abweichender Admin-URL (wp_localize_script)
- die Transaktionsmanagement Box wird nur noch in Bestellungen mit Zahlungsart easyCredit angezeigt
- das Plugin verhindert das Entfernen von Bestellpositionen (Konflikt mit "Bestellung abgebrochen"-Seite von PayPal Plus Plugin)

1.6.4
------

- Anpassung an neuen Ratenrechner: die Desktop-Version der Modellrechnung wird nun angezeigt
- Kompatibilität bis Wordpress v5.4.1, wooCommerce v4.1.0
- die Übersetzungen in der Einstellung "Deutsch (Sie)" werden nun korrekt angezeigt
- die Bestellbearbeitung ist nun übersetzt

1.6.3
------

- die Order-Management Box wird nur noch in der Detailansicht von bestehenden Bestellungen angezeigt (führte zu einem Fehler bei Erstellung von Bestellungen über das Backend)

1.6.2
------

- Verwendung des Table Prefix bei Datenbank-Abfrage

1.6.1
------

- Produkte ohne Preis werden nicht mehr an die API übertragen (z.B. Gratiszugaben), siehe #3729
- die Merchant-Interface Integration enthält einige Änderungen (Schriftart, Fehlerbehebungen, kleineres Refactoring)
- Kompatibilität mit wooCommerce <4.0, Wordpress <5.4

1.6.0
------

- Integration Händler-Interface

1.5.0
------

- Kompatibilität mit wooCommerce < v3.9.2
- bei Unerreichbarkeit der API wird der Aufruf im Backend ignoriert, der Fehler wird geloggt
- der Aufruf zum automatischen Verifizieren der Zugangsdaten im Backend wird nur noch einmal täglich aufgerufen
- das Plugin wird nur noch eingebunden, wenn WooCommerce ebenfalls vorhanden ist (verhindert Fehler bei vorherigem Deaktivieren von WooCommerce)

1.4.9
------

- Kompatibilität mit wooCommerce <v3.9.1
- Kompatibiität mit Wordpress <5.3
- Ratenkauf wird nun auch ohne den update_checkout Ajax-Aufruf entsprechend der Adresse angezeigt

1.4.8
------

- Kompatibilität mit wooCommerce <v3.9.0
- Kompatibiität mit Wordpress <5.3
- kleinere Fehlerbehebungen (Notice-Fehler)
- Sprachdatei für de_DE_formal hinzugefügt

1.4.7
------

- Kompatibilität mit wooCommerce <v3.8.1
- Kompatibiität mit Wordpress <5.3
- Entfernt Tilungsplan & vorvertragliche Informationen
- Umstellung auf Ratenkauf API v2
- bei Bestätigung der Bestellung wird die Bestellnummer übergeben

1.4.6
------

- Kompatibilität mit Wordpress Multisite
- Kompatibilität mit wooCommerce <v3.6.5
- behebt einen Deprecated-Fehler von Zend_Http_Client unter PHP > 5.6
- behebt einen Notice-Fehler im Backend (prevent_shipping_address_change)

1.4.5
------

- Erhöhung der Kompatibilität mit WooCommerce Themes (zuverlässiger Umbruch/Float auf Review-Seite)
- Kompatibilität mit wooCommerce v3.5.5
- Autoload lädt keine nicht existenten Klassen mehr (behebt Konflikte mit Plugins, die ebenfalls Zend-Autoloader enthalten)

1.4.4
------

- der Zahlartentitel wird nun korrekt im Backend und Bestellung angezeigt
- Kompatibilität erhöht auf Wordpress 5.1 / wooCommerce v3.5.4
- kleinere textuelle Anpassungen

1.4.3
------

- Verbesserung der Übersetzung von Hinweistexten
- Aktualisierung des Checkouts bei Änderung des Firmennamens
- Kompatibilität erhöht auf Wordpress 5.0 / wooCommerce v3.5.1

1.4.2
------

- Entfernung von Bootstrap aus easycredit Widget (Reduzierung von Abhängigkeiten / Konfliktpotential)
- Anpassungen für Wordpress Plugin-Verzeichnis
- Einbindung des Widgets in Warenkorb & Einstellungsoption
- CSS-Selektor für Widget in Warenkorb & Produkt-Detailseite kann bestimmt werden
- kleinere Anpassungen in Texten & Übersetzungen

1.4.1
------

- behebt kleinere Fehler im Checkout, die bei wenigen Kunden aufgetreten sind
- das Plugin erstellt nun ein eigenes Log-File
- Anpassung des Links auf die Kundenseite von ratenkauf by easyCredit

1.4
------

- abfangen von Notice-Fehler & Undefined-Property Fehler bei aktiviertem E_NOTICE Error Reporting

1.3
------

- in wenigen Fällen war der Checkout Button nicht klickbar unter Firefox & Edge durch einen Bug z.B. in Firefox (https://bugzilla.mozilla.org/show_bug.cgi?id=630495)

1.2
------

- Verbesserung der Kompatibilität mit Drittanbieter Plugins (Payment Gateway wurde doppelt geladen durch WPML Plugin)

1.1
------

- die Transaktions-ID wird nun im Backend angezeigt
- die Zinsen werden nun im Backend angezeigt
- die Versandadresse kann nachträglich nicht mehr verändert werden
- ratenkauf by easyCredit ist nur für Deutschland wählbar
- das Release ist getestet mit allen PHP-Versionen von 5.4 - 7.1, sowie mit wooCommerce 3.0.