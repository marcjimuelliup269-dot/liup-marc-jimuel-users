# Stockroom

React client for the LavaLust product API.

## Local development

1. From the LavaLust project root, generate the API signing keys with `php lava jwt:generate` and configure the Aiven MySQL values in `.env`.
2. Run the database migrations with `php lava migration run`.
3. Start the API with `php lava serve`.
4. Copy `.env.example` to `.env`, then run `npm install` and `npm run dev` from `frontend/`.
5. Use the React client's **Create an account** form, then sign in without leaving the client.

The client stores short-lived access and refresh tokens in `sessionStorage`; it never connects to MySQL directly. Set `VITE_API_BASE_URL` to the LavaLust API origin when deploying the frontend. On the API, set `API_ALLOW_ORIGIN` to the exact frontend origin and provide `JWT_SECRET`, `REFRESH_TOKEN_KEY`, and the Aiven `DB_*` values as Render environment variables. Do not add `.env` files or database credentials to either repository.

For a static Render deployment, use `npm install` as the build command and `npm run build` as the publish output `dist`. Configure the frontend environment variable before building.