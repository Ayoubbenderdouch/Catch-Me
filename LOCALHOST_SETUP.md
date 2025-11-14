# 🚀 Schnellstart für Localhost

## Voraussetzungen

- PHP 8.3 oder höher
- Composer
- MySQL 8.0
- Node.js & NPM

## Setup-Schritte

### 1. Composer-Abhängigkeiten installieren

```bash
composer install
```

### 2. NPM-Abhängigkeiten installieren

```bash
npm install
```

### 3. Umgebungsdatei erstellen

```bash
cp .env.example .env
```

### 4. Application Key generieren

```bash
php artisan key:generate
```

### 5. Datenbank konfigurieren

Öffnen Sie `.env` und passen Sie die Datenbank-Einstellungen an:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=catchme_app
DB_USERNAME=root
DB_PASSWORD=ihr_passwort
```

### 6. Datenbank erstellen

Erstellen Sie eine neue Datenbank in MySQL:

```sql
CREATE DATABASE catchme_app;
```

### 7. Migrations und Seeders ausführen

```bash
php artisan migrate --seed
```

Dies erstellt alle Tabellen und fügt Testdaten ein.

### 8. Frontend-Assets kompilieren

```bash
npm run build
```

Für Entwicklung (mit Hot Reload):

```bash
npm run dev
```

### 9. Storage Link erstellen

```bash
php artisan storage:link
```

### 10. Server starten

```bash
php artisan serve
```

Die Anwendung läuft jetzt auf: **http://localhost:8000**

## 🎯 Admin Dashboard Zugriff

1. Öffnen Sie: **http://localhost:8000/admin/login**

2. Login-Daten:
   - **Email**: admin@catchme.app
   - **Passwort**: password

## 📱 Test-Benutzer

Nach dem Seeding sind folgende Test-Benutzer verfügbar:

| Name | Email | Phone | Passwort |
|------|-------|-------|----------|
| Marie Dupont | marie@example.com | +33612345678 | password |
| Ahmed Hassan | ahmed@example.com | +33612345679 | password |
| Sophie Martin | sophie@example.com | +33612345680 | password |

## 🔧 Troubleshooting

### Fehler: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Fehler: "SQLSTATE[HY000] [1045] Access denied"
Überprüfen Sie Ihre Datenbank-Zugangsdaten in `.env`

### Fehler: "Class 'Storage' not found"
```bash
composer dump-autoload
```

### CSS/JS wird nicht geladen
```bash
npm run build
php artisan config:clear
```

### "Target class [Controller] does not exist"
```bash
composer dump-autoload
php artisan clear-compiled
```

## 📚 Nächste Schritte

### API-Dokumentation generieren (optional)

```bash
php artisan l5-swagger:generate
```

Dann öffnen Sie: **http://localhost:8000/api/documentation**

### Weitere Konfiguration

Für Produktion benötigen Sie:

1. **Firebase Cloud Messaging**:
   - Fügen Sie `FCM_SERVER_KEY` in `.env` hinzu
   - Platzieren Sie `firebase-credentials.json` in `storage/`

2. **Google Maps API**:
   - Fügen Sie `GOOGLE_MAPS_API_KEY` in `.env` hinzu

3. **AWS S3** (für Datei-Uploads):
   - Konfigurieren Sie AWS-Zugangsdaten in `.env`

## 🎨 Dashboard-Funktionen

Nach dem Login können Sie auf folgende Funktionen zugreifen:

- **Dashboard**: Statistiken und Diagramme
- **Users**: Benutzerverwaltung
- **Likes & Matches**: Like-Anfragen überwachen
- **Chats**: Nachrichten moderieren
- **Reports**: Meldungen bearbeiten
- **Security**: Gesperrte Benutzer und Aktivitätsprotokolle
- **Live Map**: Karte mit Online-Benutzern (Google Maps API erforderlich)
- **Push Notifications**: FCM-Benachrichtigungen senden
- **Settings**: App-Einstellungen
- **Admin Users**: Admin-Benutzer verwalten

## 🔥 Schnell-Setup (Ein Befehl)

Alternativ können Sie das Setup-Skript verwenden:

```bash
chmod +x SETUP.sh
./SETUP.sh
```

Dieses Skript führt alle Schritte automatisch aus!

## ⚠️ Wichtige Hinweise

- Die App ist für **Entwicklung** konfiguriert
- Standard-Admin-Passwort in Produktion ändern!
- Firebase und Google Maps sind optional für lokale Tests
- Für File-Uploads wird S3 benötigt (oder ändern Sie zu `local` in `config/filesystems.php`)

## 📞 Support

Bei Problemen:
- Überprüfen Sie die Logs: `storage/logs/laravel.log`
- Führen Sie aus: `php artisan config:clear && php artisan cache:clear`
- Konsultieren Sie `README.md` für detaillierte Informationen

---

**Viel Erfolg! 🎉**
