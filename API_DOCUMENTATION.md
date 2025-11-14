# 📚 Catch Me - Complete API Documentation

**Base URL:** `http://localhost:8080/api`  
**Swagger UI:** `http://localhost:8080/api/documentation`

---

## 🔐 Authentication

All protected endpoints require Bearer Token in header:
```
Authorization: Bearer {your_token_here}
```

---

## 📋 COMPLETE API ENDPOINTS LIST

### **1. AUTHENTICATION** 🔑

#### Register
```http
POST /api/auth/register
```
**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+33612345678",
  "password": "password123",
  "password_confirmation": "password123",
  "date_of_birth": "1995-05-15",
  "gender": "male"
}
```
**Response:** User + Token

---

#### Login
```http
POST /api/auth/login
```
**Request:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```
**Response:** Token

---

#### Logout
```http
POST /api/auth/logout
Headers: Authorization: Bearer {token}
```

---

#### Get Current User
```http
GET /api/auth/user
Headers: Authorization: Bearer {token}
```

---

#### Social Login (Google/Apple)
```http
POST /api/auth/social
```
**Request:**
```json
{
  "provider": "google",
  "provider_id": "google_user_id",
  "name": "John Doe",
  "email": "john@gmail.com"
}
```

---

### **2. USER PROFILE** 👤

#### Update Profile
```http
PUT /api/user/profile
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "name": "John Updated",
  "bio": "Hello world",
  "gender": "male",
  "language": "fr",
  "is_visible": true
}
```

---

#### Upload Profile Image
```http
POST /api/user/profile-image
Headers: Authorization: Bearer {token}
Content-Type: multipart/form-data
```
**Request:**
```
image: [file]
```

---

#### Update Location 📍
```http
POST /api/user/location
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "latitude": 48.8566,
  "longitude": 2.3522
}
```
**Note:** Max 6 requests/minute (Rate Limited)

---

#### Get Nearby Users 🔍
```http
GET /api/user/nearby?radius=50
Headers: Authorization: Bearer {token}
```
**Response:**
```json
{
  "users": [
    {
      "id": 2,
      "name": "Jane",
      "gender": "female",
      "profile_image": "https://...",
      "bio": "Hello!",
      "distance": "Nearby"
    }
  ]
}
```

---

#### Toggle Ghost Mode 👻
```http
POST /api/user/ghost-mode
Headers: Authorization: Bearer {token}
```
**Request (Optional):**
```json
{
  "is_visible": false
}
```
**Response:**
```json
{
  "message": "You are now hidden",
  "is_visible": false,
  "status": "Ghost Mode ON"
}
```
**Note:**
- If no body is sent, it will toggle current state
- `is_visible: false` = Ghost Mode (hidden from nearby users)
- `is_visible: true` = Visible Mode (shown to nearby users)

---

#### Delete Account ⚠️
```http
DELETE /api/user/account
Headers: Authorization: Bearer {token}
```

---

### **3. LIKES & MATCHES** ❤️

#### Send Like
```http
POST /api/likes
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "to_user_id": 2
}
```
**Response:**
```json
{
  "message": "Like sent",
  "is_match": false,
  "like": {...}
}
```
**Note:** Max 30 likes/minute

---

#### Accept Like
```http
PUT /api/likes/{id}/accept
Headers: Authorization: Bearer {token}
```

---

#### Reject Like
```http
PUT /api/likes/{id}/reject
Headers: Authorization: Bearer {token}
```

---

#### Get Received Likes
```http
GET /api/likes/received
Headers: Authorization: Bearer {token}
```

---

#### Get Matches
```http
GET /api/matches
Headers: Authorization: Bearer {token}
```
**Response:**
```json
{
  "matches": [
    {
      "like_id": 5,
      "matched_at": "2025-10-31T...",
      "user": {
        "id": 2,
        "name": "Jane",
        "profile_image": "...",
        "gender": "female"
      }
    }
  ]
}
```

---

### **4. MESSAGES** 💬

#### Send Message
```http
POST /api/messages
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "receiver_id": 2,
  "message": "Hello!"
}
```
**Response:**
```json
{
  "message": "Message sent",
  "data": {
    "id": 123,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "Hello!",
    "status": "sent",
    "created_at": "2025-11-01T12:00:00.000000Z"
  }
}
```
**Note:**
- Max 60 messages/minute
- Bad words filtered automatically
- Contact info blocked
- Messages start with `status: "sent"` (one checkmark ✓)

---

#### Get Conversation
```http
GET /api/messages/{userId}?page=1
Headers: Authorization: Bearer {token}
```
**Response:** Paginated messages (50 per page)

---

#### Get All Conversations
```http
GET /api/conversations
Headers: Authorization: Bearer {token}
```

---

#### Delete Message
```http
DELETE /api/messages/{id}
Headers: Authorization: Bearer {token}
```

---

#### Mark Message as Delivered ✓✓ (NEW!)
```http
POST /api/messages/{id}/delivered
Headers: Authorization: Bearer {token}
```
**Description:** Mark a message as delivered (two gray checkmarks - WhatsApp style)
**Note:** Only receiver can mark as delivered

---

#### Mark Message as Read ✓✓ (NEW!)
```http
POST /api/messages/{id}/read
Headers: Authorization: Bearer {token}
```
**Description:** Mark a message as read (two green checkmarks - WhatsApp style)
**Note:** Only receiver can mark as read

**Response:**
```json
{
  "message": "Message marked as read",
  "status": "read"
}
```

---

#### Bulk Mark as Delivered (NEW!)
```http
POST /api/messages/bulk-delivered
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "message_ids": [1, 2, 3, 4, 5]
}
```
**Description:** Mark multiple messages as delivered efficiently
**Response:**
```json
{
  "message": "Messages marked as delivered",
  "updated_count": 5
}
```

---

#### Bulk Mark as Read (NEW!)
```http
POST /api/messages/bulk-read
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "message_ids": [1, 2, 3, 4, 5]
}
```
**Description:** Mark multiple messages as read efficiently
**Response:**
```json
{
  "message": "Messages marked as read",
  "updated_count": 5
}
```

---

### **5. REPORTS** 🚨

#### Report User
```http
POST /api/reports
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "reported_user_id": 2,
  "reason": "spam",
  "description": "Sending inappropriate messages"
}
```
**Reasons:** spam, harassment, inappropriate_content, fake_profile, other  
**Note:** Max 10 reports/hour

---

#### Get My Reports
```http
GET /api/reports
Headers: Authorization: Bearer {token}
```

---

### **6. BLOCK USERS** 🛡️ (NEW!)

#### Block User
```http
POST /api/block
Headers: Authorization: Bearer {token}
```
**Request:**
```json
{
  "user_id": 2,
  "reason": "Unwanted contact"
}
```

---

#### Unblock User
```http
DELETE /api/block/{userId}
Headers: Authorization: Bearer {token}
```

---

#### Get Blocked Users
```http
GET /api/block/blocked-users
Headers: Authorization: Bearer {token}
```
**Response:**
```json
{
  "blocked_users": [
    {
      "id": 1,
      "blocker_id": 1,
      "blocked_id": 2,
      "reason": "Unwanted contact",
      "created_at": "2025-10-31T..."
    }
  ]
}
```

---

## 🔒 RATE LIMITS

| Endpoint | Limit | Window |
|----------|-------|--------|
| **Auth (Login/Register)** | 10 requests | 1 minute |
| **Location Update** | 6 requests | 1 minute |
| **Send Like** | 30 requests | 1 minute |
| **Send Message** | 60 requests | 1 minute |
| **Report User** | 10 requests | 1 hour |
| **All Other Endpoints** | 60 requests | 1 minute |

---

## 🔐 SECURITY FEATURES

### Content Moderation:
✅ **Bad Words Filter** - Auto-blocks inappropriate language  
✅ **Contact Info Detection** - Blocks phone/email/social media sharing  
✅ **Spam Protection** - Rate limiting prevents abuse  

### Privacy:
✅ **Location Fuzzing** - Your exact location never shown (±20-50m)  
✅ **Ghost Mode** - Hide from nearby searches  
✅ **Block Feature** - Block unwanted users  

### Safety:
✅ **Report System** - Report inappropriate behavior  
✅ **Age Verification** - 18+ only  
✅ **Ban System** - Admins can ban violators  

---

## 📊 RESPONSE FORMATS

### Success Response:
```json
{
  "message": "Success message",
  "data": {...}
}
```

### Error Response:
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

### HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests (Rate Limited)
- `500` - Server Error

---

## 🧪 TESTING WITH POSTMAN

### 1. Register & Get Token
```bash
POST http://localhost:8080/api/auth/register
Body: {JSON with user info}
```
Copy the `token` from response

### 2. Add Token to Headers
```
Authorization: Bearer {your_token}
```

### 3. Test Endpoints
All protected endpoints need this token!

---

## 📝 EXAMPLE WORKFLOW

```
1. Register → Get Token
2. Login → Get Token  
3. Update Profile → Add bio, photos
4. Update Location → Enable location tracking
5. Get Nearby Users → See who's nearby
6. Send Like → Like someone
7. Match! → Both liked each other
8. Send Message → Start chatting
9. Block User → If needed
10. Report User → If inappropriate
```

---

## 🌍 SUPPORTED LANGUAGES

- **French (fr)** - Default
- **Arabic (ar)** - Fallback
- Set via `Accept-Language` header or user profile

---

## 🔗 USEFUL LINKS

- **Swagger UI:** http://localhost:8080/api/documentation
- **Privacy Policy:** https://catchme.app/privacy
- **Terms of Service:** https://catchme.app/terms
- **Support:** support@catchme.app

---

## 📦 TOTAL ENDPOINTS

```
✅ Authentication: 4 endpoints
✅ User Profile: 6 endpoints (Ghost Mode Toggle!)
✅ Likes & Matches: 5 endpoints
✅ Messages: 8 endpoints (NEW: WhatsApp-style Status Checkmarks ✓✓)
✅ Reports: 2 endpoints
✅ Block Users: 3 endpoints

TOTAL: 28 API Endpoints
```

---

## 💬 MESSAGE STATUS SYSTEM (WhatsApp-Style)

All messages have a status field that shows delivery state:

| Status | Checkmarks | Description |
|--------|-----------|-------------|
| **sent** | ✓ (one gray) | Message sent to server |
| **delivered** | ✓✓ (two gray) | Message delivered to recipient's device |
| **read** | ✓✓ (two green) | Message opened and read by recipient |

**How it works:**
1. User sends message → `status: "sent"` (one checkmark)
2. Receiver's device receives push → Call `/messages/{id}/delivered` → `status: "delivered"` (two gray checkmarks)
3. Receiver opens chat → Auto calls `/messages/{id}/read` → `status: "read"` (two green checkmarks)

**Bulk Updates for Performance:**
- Use `/messages/bulk-delivered` when receiving multiple messages
- Use `/messages/bulk-read` when opening a conversation

---

**Last Updated:** November 1, 2025
**API Version:** 1.1 (WhatsApp-Style Message Status Added)
**Status:** Production Ready ✅

---

© 2025 Catch Me. All Rights Reserved.
