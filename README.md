# Amusement Terminal

Eight play-money cabinets in Laravel, laid out like a floor terminal: Double Chance, Grand Chance, Triple Chance, Triple Fun, Spin 2 Win, Fun Target, Lucky Horse Race, and Jackpot ABC.

Points have no cash value. There is no deposit, withdrawal, or payment integration. A free refill adds points when the balance is under the cap.

Draws are shared and timed: 45 seconds of betting, then an 8 second spin. The server settles the ticket. Claim or print it from the cabinet.

Players register themselves. An admin account is created only when `ADMIN_PASSWORD` is set in the environment before `php artisan db:seed`. That value stays out of the repository. The admin desk can credit play points. It cannot cash them out.

PHP 8.3+ is required, with `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, and `bcmath`.

## MySQL

Create a database named `amusement_terminal`, then import [`amusement_games.sql`](amusement_games.sql). Point the document root at `public/` and set `.env`:

```env
APP_NAME="Amusement Terminal"
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amusement_terminal
DB_USERNAME=root
DB_PASSWORD=
```

Generate a key if `.env` does not already have one:

```bash
php artisan key:generate
```

The SQL file has the tables and no players. For a developer install from source instead:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Optional scheduler, once a minute:

```bash
php artisan schedule:work
```

Web requests also advance draws, so cron is not required.

## Payouts

| Bet | Pays |
| --- | --- |
| Andar, Bahar, single digit, spot | 9× |
| Jodi or double | 90× |
| Family (92 also covers 29, 99, 22) | 22×, or 90× for a pair such as 11 |
| Triple | 900× |
| Horse | Odds printed on the card |
| Red or Black | 1.8× |
| Jackpot spot | 9× |
| Straight | 800× |
| Box | 130×, 250× for a pair, 800× for three of a kind |
| AB / AC / BC pair | 80× |
