Backend Project (Laravel Sail)
This project is built with Laravel and managed via Laravel Sail (Docker).

🚀 Quick Start
1. Install Dependencies

If you don't have Composer installed locally, run this command to install dependencies using a temporary Docker container:

```

docker run --rm \

    -u "$(id -u):$(id -g)" \

    -v "$(pwd):/var/www/html" \

    -w /var/www/html \

    laravelsail/php83-composer:latest \

    composer install --ignore-platform-reqs

```

2. Environment Setup

```

cp .env.example .env

# Update your .env file with necessary credentials

./vendor/bin/sail artisan key:generate

```

3. Start the Project

```

./vendor/bin/sail up -d

```

Tip: Consider adding an alias: `alias sail="./vendor/bin/sail"`.

4. Database Migrations & Seeding

```

./vendor/bin/sail artisan migrate --seed

```

---

🛠 Features & Tools
📝 API Documentation

To generate or update the API documentation:

```

./vendor/bin/sail artisan scribe:generate

```

Access it at: `http://localhost/docs`

🛰 Laravel Telescope

For debugging requests, queries, and logs: Access it at: `http://localhost/telescope`

🔊 Laravel Reverb (WebSockets)

To start the WebSocket server:

```

./vendor/bin/sail artisan reverb:start

```

Note: Make sure your queue worker is also running for broadcasting:

```

./vendor/bin/sail artisan queue:work

```

---

🏗 Maintenance Commands
• Stop Project: `sail down`

• Shell Access: `sail shell`

• Running Tests: `sail artisan test`

• Fresh Database: `sail artisan migrate:fresh --seed`
