
API-Referenz für den Core User Service
======================================
 
## Messages ##

### User ###

Wird in der Regel nur bei einem HTTP-Statuscode 200 oder 201 zurückgegeben.

JSON-Format Beispiel:

    {
        "user\_id": 1234,
        "loginname": "bosupport",
        "name": "BerlinOnline Support",
        "comment": "Support-Account für BerlinOnline",
        "email": "support@berlin.de",
        "groups": \["group1", "group2"\]
        "locked": false
    }

Eigenschaften:

* *loginname*: Identifier des Users, dieser kann nicht geändert werden
* *name*: Klarname des Users, sollte in der Regel Vor- und Zuname enthalten
* *comment*: Kommentar für Administratoren, sollte nicht in einer Applikation angezeigt werden. Bei Bedarf kann hier automtisiert ein Hinweis stehen, warum der User z.B. gesperrt wurde.
* *email*: E-Mail des Nutzers
* *locked*: Zahl der Sekunden, die der Nutzer noch gelockt ist, "false" bedeutet nicht gelockt, "-1" bedeutet, der Nutzer ist ohne Zeitbeschränkung gelockt

### Status ###

**JSON-Format Beispiel:**

    {
        "ok": false,
        "code": "403",
        "message": "Invalid credentials for %s",
        "parameters": \["bosupport"\]
    }

**Eigenschaften:**

* *ok*: Bei "true" ist alles in Ordnung und die anderen Felder sind nicht ausgefüllt, bei "false" sollten die anderen Felder überprüft werden
* *code*: Entspricht dem HTTP-Status-Code
* *message*: Eine genauere Fehlermeldung, wird zum Zweck der Übersetzung mit Platzhalter "%s" für Strings ausgeliefert
* *parameters*: Parameter für die Platzhalter von message


## Methoden ##

### Nutzer abfragen ###

**Request:**

    GET /user/:user_id/

**Returns:** User

**Statuscodes:**

* *200*: Der Nutzer existiert
* *302*: Der Nutzer ist gesperrt, der Expires Header gibt die verbleibende Zeit an, der Location header führt nach /user/:user_id/lock/  (not implemented)
* *404*: Der angegebene Nutzer existiert nicht

### Nutzer Info ###

**Request:**

    GET /user/login/?auth=:auth

**Parameter:**

* *auth*: Loginname, User ID oder E-Mail-Adresse des Nutzers

**Returns:** User

**Statuscodes:**

* *200*: Der Nutzer existiert
* *404*: Der angegebene Nutzer existiert nicht

### Nutzer login (not implemented) ###

**Request:**

    POST /user/login/

**Parameter:**

* *auth*: Loginname, User ID oder E-Mail-Adresse des Nutzers
* *password*: Passwort des Nutzers

**Returns:** User

**Statuscodes:**

* *302*: Der Nutzer ist gesperrt, der Expires Header gibt die verbleibende Zeit an, der Location header führt nach /user/:user_id/lock/
* *200*: Der Nutzer existiert und Passwort ist korrekt
* *403*: Das Passwort des Users stimmt nicht mit dem übermitteltem überein
* *404*: Der angegebene Nutzer existiert nicht

### Nutzer ist gesperrt###

**Request:**

    GET /user/:user_id/lock/

**Returns:** User

**Statuscodes:**

* *200*: Der Nutzer ist gesperrt, der Expires Header gibt die verbleibende Zeit an
* *404*: Der angegebene Nutzer existiert nicht oder ist nicht gesperrt


### Nutzer wird für eine bestimmte Zeit in Sekunden gesperrt, bei -1 für immer (not implemented) ###

**Request:**

    POST /user/:user_id/lock/

**Parameter:**

* *time*: Sperrzeit in Sekunden, -1 für eine Sperre ohne Ende

**Returns:** User

**Statuscodes:**

* *303*: Der Nutzer ist gesperrt, der Expires Header gibt die verbleibende Zeit an, Redirect zu /user/:user_id/lock/
* *404*: Der angegebene Nutzer existiert nicht

### Nutzer registriert sich neu ###

**Request:**

    POST /user/create/

**Parameter:**

* *login*: Loginname
* *email*: E-Mail-Address
* *password*: Passwort des Nutzers

**Returns:** User

**Statuscodes:**

* *201*: Der Nutzer wurde angelegt
* *400*: Das Passwort, die E-Mail-Adresse oder der Loginname entspricht nicht den Anforderungen, der Nutzer wurde nicht angelegt
* *409*: Der Nutzer existiert bereits

### Nutzer wird gelöscht ###

**Request:**

    POST /user/:user_id/delete/

**Parameter:**

* *user_id*: User ID


**Returns:** Status

**Statuscodes:**

* *200*: Der Nutzer wurde gelöscht
* *404*: Der Nutzer existiert nicht

### Nutzer ändert Stammdaten ###

**Request:**

    POST /user/:user_id/update/

**Request-Body:** User (Gruppen werden ignoriert)

**Returns:** User

**Statuscodes:**

* *302*: Der Nutzer wurde geändert, Redirect zu /user/:user_id/ als GET-Request
* *400*: Invalide Angaben, Status im Body enthält nähere Angaben
* *404*: Der Nutzer existiert nicht


### Liste alle verfügbaren globalen Gruppen (listGroups) (not implemented) ###

**Request:**

    GET /group/

**Returns:**
    {
        "groups": \["group1", group2", "group3"\]
    }

**Statuscodes:**

* *200*: Immer erfolgreich


### Nutzer einer globalen Gruppe hinzufügen (not implemented) ###

Eine nicht existierende Gruppe wird automatisch erstellt

**Request:**

    POST /group/:groupname/add/:user_id/

**Returns:** User

**Statuscodes:**

* *200*: Die Gruppe beinhaltet den Nutzer bereits
* *201*: Der Gruppe wurde der Nutzer hinzugefügt
* *404*: Der Nutzer existiert nicht


### Gehört ein Nutzer zu einer Gruppe (not implemented) ###

**Request:**

    GET /group/:groupname/member/:user_id/

**Returns:** User

**Statuscodes:**

* *200*: Der Nutzer existiert in der Gruppe
* *404*: Der Nutzer oder die Gruppe existiert nicht

### Nutzer kriegt globale Rechte entzogen (not implemented) ###

Sind in der Gruppe keine Nutzer mehr vorhanden, wird diese entfernt

**Request:**

    POST /group/:groupname/remove/:user_id/

**Returns:** Status

**Statuscodes:**

* *200*: Aus der Gruppe wurde der Nutzer entfernt
* *404*: Der Nutzer oder die Gruppe existiert nicht


### Liste alle verfügbaren Applikationen (not implemented) ###

**Request:**

    GET /app/

**Returns:**
    {
        "applicationId": \["app1", app2", "app3"\]
    }

**Statuscodes:**

* *200*: Immer erfolgreich

### Liste alle Gruppen einer Applikation (not implemented) ###


**Request:**

    GET /app/:applicationId/group/

**Returns:**
    {
        "groups": \["group1", group2", "group3"\]
    }

**Statuscodes:**

* *200*: Erfolgreich
* *404*: Die ApplicationId existiert nicht

### Externe Applikation: Hat der Nutzer die richtigen Rechte? (not implemented) ###

**Request:**

    GET /app/:applicationId/group/:groupname/member/:user_id/

**Returns:** User

**Statuscodes:**

* *200*: Der Nutzer existiert in der Gruppe
* *404*: Der Nutzer oder die Gruppe oder die ApplicationId existiert nicht

### Externe Applikation: Erteile dem Nutzer Rechte für diese Applikation (not implemented) ###

Eine nicht existierende ApplicationId oder Gruppe wird automatisch erstellt

**Request:**

    POST /app/:applicationId/group/:groupname/add/:user_id/

**Returns:** User

**Statuscodes:**

* *200*: Die Gruppe beinhaltet den Nutzer bereits
* *201*: Der Gruppe wurde der Nutzer hinzugefügt
* *404*: Der Nutzer existiert nicht


### Externe Applikation: Entziehe dem Nutzer Rechte für diese Applikation (not implemented) ###


Sind in der Gruppe keine Nutzer mehr vorhanden, wird diese entfernt, gleiches gilt für ApplicationIds

**Request:**

    POST /app/:applicationId/group/:groupname/remove/:user_id/

**Returns:** Status

**Statuscodes:**

* *200*: Aus der Gruppe wurde der Nutzer entfernt
* *404*: Der Nutzer oder die Gruppe oder die ApplicationId existiert nicht




