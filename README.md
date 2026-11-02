# LunaDrive

Self-hosted storage gateway that joins multiple Google Drive accounts into one virtual drive.

Stack: **Laravel + Vue 3 + Inertia + Tailwind CSS 4 + PostgreSQL + pnpm**.

## Features (MVP)

- Email/password auth and Continue with Google
- Connect multiple Google Drive accounts (OAuth tokens encrypted at rest)
- Combined Quota Tracker
- Upload routing: most available, round robin, or priority
- Virtual folders (DB-only) with list/grid file manager
- Upload / preview / download / rename / soft-delete
- Settings UI for Google OAuth credentials

## Requirements

- Docker + Docker Compose
- Node.js 20+ and pnpm
- PHP 8.4+ with Composer (optional if you use Docker for the app)

## Quick start (Docker)

```bash
cp .env.example .env
# set APP_KEY (see below), TOKEN_ENCRYPTION_KEY, and DB password if desired

docker compose up -d --build postgres
# wait for healthy, then:

docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --force

pnpm install
pnpm run build

docker compose up -d
```

Open **http://localhost:8080**.

### Local Vite during development

```bash
pnpm run dev
```

Keep `APP_URL` pointing at the nginx container (`http://localhost:8080`) and let Vite HMR serve assets.

## First minutes

1. Register an account (or Continue with Google after OAuth is configured).
2. Open **Settings** and paste Google Cloud OAuth Client ID / Secret.
3. Authorized redirect URI must be:
   `http://localhost:8080/connected-accounts/google/callback`
4. Click **Connect Google Drive** for each account you want in the pool.
5. Upload from **All Files** — LunaDrive routes to an account with enough free space.

## Google Cloud setup

1. Create a project and enable **Google Drive API**.
2. Configure OAuth consent screen (External / Testing) with scopes:
   - `https://www.googleapis.com/auth/drive`
   - `https://www.googleapis.com/auth/userinfo.email`
   - `https://www.googleapis.com/auth/userinfo.profile`
3. Create **OAuth client ID → Web application**.
4. Add JS origin `http://localhost:8080` and the redirect URI above.

## Project layout

```txt
app/Services/Google/     Drive client, quota sync, uploads
app/Services/Storage/    UploadRouter
app/Http/Controllers/Drive/
resources/js/Pages/Drive/
docker-compose.yml
```

## Notes

- Uploads stream through the app to Google Drive; keep nginx/`client_max_body_size` and PHP upload limits high (configured in `docker/`).
- Virtual folders never become real Drive folders; files land in a root Drive folder named `lunadrive`.
- Inspired by [9Drive](https://github.com/zenhosta/9drive); LunaDrive is an independent Laravel/Vue implementation.
