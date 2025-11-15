# Catch Me - Setup Guide für API-Integrationen

Diese Anleitung hilft Ihnen, alle notwendigen API-Keys und Services korrekt zu konfigurieren.

## 📋 Übersicht

Die App benötigt folgende externe Services:
1. ✅ **Firebase Cloud Messaging** - Push-Benachrichtigungen
2. ✅ **AWS S3** - Bild-Speicherung (Profil-Fotos)
3. ✅ **Google Maps API** - Live-Karte im Admin-Dashboard
4. ✅ **Laravel Sanctum** - API-Authentifizierung (bereits konfiguriert)

---

## 🔥 1. Firebase Cloud Messaging (FCM) Setup

### Was wird Firebase verwendet?
- Push-Benachrichtigungen für Likes, Matches und Nachrichten
- iOS und Android App-Benachrichtigungen

### Setup-Schritte:

#### Schritt 1: Firebase Projekt erstellen
1. Gehen Sie zu: https://console.firebase.google.com/
2. Klicken Sie auf "Projekt hinzufügen"
3. Geben Sie den Projektnamen ein: "Catch Me"
4. Folgen Sie den Setup-Schritten

#### Schritt 2: Server Key holen
1. Öffnen Sie Ihr Firebase Projekt
2. Gehen Sie zu: **Projekteinstellungen** (Zahnrad-Symbol oben links)
3. Wählen Sie den Tab: **Cloud Messaging**
4. Kopieren Sie den **Server-Schlüssel** (Server Key)
5. Kopieren Sie die **Sender-ID**

#### Schritt 3: Service Account Credentials
1. In Projekteinstellungen > **Service Accounts**
2. Klicken Sie auf "Neuen privaten Schlüssel generieren"
3. Speichern Sie die JSON-Datei als: `firebase-credentials.json`
4. Verschieben Sie die Datei nach: `/storage/firebase-credentials.json`

#### Schritt 4: .env konfigurieren
```bash
FCM_SERVER_KEY=AAAA...Ihr_Server_Key_hier
FCM_SENDER_ID=123456789012
FIREBASE_CREDENTIALS=storage/firebase-credentials.json
```

#### iOS App einrichten:
1. In Firebase Console: "App hinzufügen" > iOS
2. Bundle ID eingeben: `com.catchme.app`
3. `GoogleService-Info.plist` herunterladen
4. In Xcode Projekt hinzufügen

#### Android App einrichten:
1. In Firebase Console: "App hinzufügen" > Android
2. Package Name eingeben: `com.catchme.app`
3. `google-services.json` herunterladen
4. In Flutter Projekt unter `android/app/` speichern

---

## ☁️ 2. AWS S3 Setup (Bild-Speicherung)

### Wofür wird S3 verwendet?
- Speicherung von Profilbildern
- Upload von Benutzer-Fotos
- Skalierbare Cloud-Speicherung

### Setup-Schritte:

#### Schritt 1: AWS Account erstellen
1. Gehen Sie zu: https://aws.amazon.com/
2. Erstellen Sie einen Account (oder loggen Sie sich ein)

#### Schritt 2: S3 Bucket erstellen
1. Öffnen Sie die **S3 Console**: https://console.aws.amazon.com/s3/
2. Klicken Sie auf **"Bucket erstellen"**
3. Bucket-Name: `catchme-uploads` (muss global eindeutig sein)
4. Region: `us-east-1` (oder Ihre bevorzugte Region)
5. **Block all public access**: **DEAKTIVIEREN** (für öffentliche Bild-URLs)
6. Bucket erstellen

#### Schritt 3: Bucket Policy konfigurieren
1. Öffnen Sie den erstellten Bucket
2. Gehen Sie zu **Berechtigungen** > **Bucket-Richtlinie**
3. Fügen Sie folgende Policy ein (ersetzen Sie `catchme-uploads`):

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::catchme-uploads/*"
    }
  ]
}
```

#### Schritt 4: IAM User erstellen (für API-Zugriff)
1. Gehen Sie zu: https://console.aws.amazon.com/iam/
2. Klicken Sie auf **Benutzer** > **Benutzer hinzufügen**
3. Benutzername: `catchme-s3-user`
4. **Zugriffstyp**: Programmatischer Zugriff
5. **Berechtigungen**: `AmazonS3FullAccess` (oder erstellen Sie eine Custom Policy)

#### Schritt 5: Access Keys kopieren
Nach dem Erstellen werden Ihnen angezeigt:
- **Access Key ID** (z.B. `AKIA...`)
- **Secret Access Key** (z.B. `wJalrXUtn...`)

⚠️ **WICHTIG**: Speichern Sie diese Keys sicher! Der Secret Key wird nur einmal angezeigt.

#### Schritt 6: .env konfigurieren
```bash
AWS_ACCESS_KEY_ID=AKIA...Ihre_Access_Key_ID
AWS_SECRET_ACCESS_KEY=wJalr...Ihr_Secret_Access_Key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=catchme-uploads
FILESYSTEM_DISK=s3
```

#### CORS-Konfiguration (optional für Browser-Uploads):
1. Im S3 Bucket > **Berechtigungen** > **CORS**
2. Fügen Sie hinzu:

```json
[
  {
    "AllowedHeaders": ["*"],
    "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
    "AllowedOrigins": ["*"],
    "ExposeHeaders": []
  }
]
```

---

## 🗺️ 3. Google Maps API Setup

### Wofür wird Google Maps verwendet?
- Admin-Dashboard: Live-Karte mit online Nutzern
- Android App: Karte mit Nutzern in der Nähe
- *(iOS verwendet MapKit - kein API-Key nötig)*

### Setup-Schritte:

#### Schritt 1: Google Cloud Projekt erstellen
1. Gehen Sie zu: https://console.cloud.google.com/
2. Erstellen Sie ein neues Projekt: "Catch Me"

#### Schritt 2: APIs aktivieren
1. Gehen Sie zu: **APIs & Services** > **Bibliothek**
2. Aktivieren Sie:
   - ✅ **Maps JavaScript API** (für Admin-Dashboard)
   - ✅ **Geocoding API** (für Adress-Suche)
   - ✅ **Maps SDK for Android** (für Android App)

#### Schritt 3: API-Key erstellen
1. Gehen Sie zu: **APIs & Services** > **Anmeldedaten**
2. Klicken Sie auf **+ Anmeldedaten erstellen** > **API-Schlüssel**
3. Kopieren Sie den generierten Key

#### Schritt 4: API-Key einschränken (Sicherheit)
1. Klicken Sie auf den erstellten Key
2. **Anwendungseinschränkungen**:
   - Für Web (Dashboard): **HTTP-Referrer**
     - Fügen Sie hinzu: `https://catch-me-main-x7a5pm.laravel.cloud/*`
   - Für Android: **Android-Apps**
     - Package Name: `com.catchme.app`
     - SHA-1-Fingerprint (von Android Studio holen)

3. **API-Einschränkungen**:
   - Wählen Sie: "Schlüssel einschränken"
   - Aktivieren Sie nur: Maps JavaScript API, Geocoding API

#### Schritt 5: .env konfigurieren
```bash
GOOGLE_MAPS_API_KEY=AIzaSy...Ihr_API_Key_hier
```

---

## ✅ 4. Konfiguration testen

### Firebase testen:
```bash
php artisan tinker
>>> $firebase = app(\App\Services\FirebaseService::class);
>>> $firebase->sendToUser(1, 'Test', 'Test Nachricht');
```

### S3 testen:
```bash
php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'Hello S3!');
>>> Storage::disk('s3')->exists('test.txt');
```

### Google Maps testen:
1. Öffnen Sie: `https://your-domain.com/admin/map`
2. Die Karte sollte geladen werden
3. Online-Nutzer sollten als Marker angezeigt werden

---

## 🔒 Sicherheits-Checkliste

- [ ] Alle API-Keys sind in `.env` (NICHT in Git committen!)
- [ ] `.env` ist in `.gitignore` eingetragen
- [ ] Firebase Server Key ist korrekt
- [ ] AWS S3 Bucket-Policy ist konfiguriert
- [ ] Google Maps API ist auf Ihre Domain eingeschränkt
- [ ] Production `.env` hat `APP_DEBUG=false`
- [ ] SSL-Zertifikat ist installiert (HTTPS)

---

## 📱 Mobile App Konfiguration

### iOS App (Info.plist):
```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>Wir brauchen Ihren Standort, um Personen in Ihrer Nähe zu finden</string>
<key>NSLocationAlwaysUsageDescription</key>
<string>Für die beste Erfahrung benötigen wir Zugriff auf Ihren Standort</string>
```

### Android App (AndroidManifest.xml):
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />

<meta-data
    android:name="com.google.android.geo.API_KEY"
    android:value="YOUR_GOOGLE_MAPS_API_KEY" />
```

---

## 🆘 Troubleshooting

### Firebase-Benachrichtigungen funktionieren nicht:
- Prüfen Sie, ob FCM_SERVER_KEY korrekt ist
- Stellen Sie sicher, dass `firebase-credentials.json` existiert
- Loggen Sie Firebase-Fehler: `tail -f storage/logs/laravel.log`

### S3-Upload schlägt fehl:
- Prüfen Sie AWS Credentials
- Stellen Sie sicher, dass Bucket-Policy korrekt ist
- Prüfen Sie IAM-Benutzer-Berechtigungen

### Google Maps lädt nicht:
- Prüfen Sie Browser-Console auf Fehler
- Stellen Sie sicher, dass Maps JavaScript API aktiviert ist
- Prüfen Sie API-Key-Einschränkungen

### "Rate Limit Exceeded" Fehler:
- Die App hat Rate-Limiting implementiert
- Warten Sie 1 Minute und versuchen Sie es erneut
- Prüfen Sie `routes/api.php` für Limits

---

## 📞 Support

Bei Problemen:
1. Prüfen Sie die Logs: `storage/logs/laravel.log`
2. Testen Sie mit `php artisan tinker`
3. Aktivieren Sie Debug-Modus: `APP_DEBUG=true` (nur lokal!)

**Dokumentation:**
- Firebase: https://firebase.google.com/docs
- AWS S3: https://docs.aws.amazon.com/s3/
- Google Maps: https://developers.google.com/maps/documentation

---

**Version**: 1.0
**Zuletzt aktualisiert**: 2025-11-15
