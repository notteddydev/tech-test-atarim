# Atarim Tech Test

To set the project up:

Environment:
* php version 8.4.1
* redis-server version 6.0.16

```sh
git clone git@github.com:notteddydev/tech-test-atarim.git /path/to/project
cd /path/to/project
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

---

To use this service, please register as a user to obtain your bearer token, and then use that bearer token to authorize your requests so that you can shorten URLs. Information on the endpoints is below.

---

### Shorten a URL

* `POST` - `/api/encode`

**Params:**
```json
{
    "original_url": "https://www.thisisalongdomain.com/with/some/parameters?and=here_too"
}
```

**Headers:**
```json
{
    "Authorization": "Bearer {bearer_token}",
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Response:**
```json
{
    "original_url": "https://www.thisisalongdomain.com/with/some/parameters?and=here_too",
    "shortened_url": "https://short.est/845176"
}
```

---

### Get the target / original URL for a shortened URL

* `GET` - `/api/decode`

**Params:**
```json
{
    "shortened_url": "https://short.est/845176"
}
```

**Headers:**
```json
{
    "Authorization": "Bearer {bearer_token}",
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Response:**
```json
{
    "original_url": "https://www.thisisalongdomain.com/with/some/parameters?and=here_too",
    "shortened_url": "https://short.est/845176"
}
```

---

### Register to obtain a bearer token

* `POST` - `/api/register`

**Params:**
```json
{
    "name": "Tess Ting",
    "email": "tess@ting.com",
    "password": "super_difficult_to_guess",
    "password_confirmation": "super_difficult_to_guess"
}
```

**Headers:**
```json
{
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Response:**
```json
{
    "bearer_token": "{bearer_token}",
    "user": {
        "name": "Tess Ting",
        "email": "tess@ting.com",
        "updated_at": "2025-04-10T02:04:22.000000Z",
        "created_at": "2025-04-10T02:04:22.000000Z",
        "id": 1
    }
}
```

---

### Login to retrieve a bearer token

* `POST` - `/api/login`

**Params:**
```json
{
    "email": "tess@ting.com",
    "password": "super_difficult_to_guess"
}
```

**Headers:**
```json
{
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "Tess Ting",
        "email": "tess@ting.com",
        "email_verified_at": null,
        "created_at": "2025-04-10T02:04:22.000000Z",
        "updated_at": "2025-04-10T02:04:22.000000Z"
    },
    "bearer_token": "{bearer_token}"
}
```