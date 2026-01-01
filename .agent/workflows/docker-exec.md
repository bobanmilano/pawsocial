---
description: How to execute commands in this Docker environment
---

All commands should be executed via Docker Compose using the appropriate service.

// turbo-all
1. To run Symfony console commands:
`docker compose exec php bin/console [command]`

2. To run tests:
`docker compose exec php bin/phpunit`

3. To run composer:
`docker compose exec php composer [command]`

> [!NOTE]
> The service name is assumed to be `php` based on common Symfony Docker setups. Adjust if `compose.yaml` specifies otherwise.
