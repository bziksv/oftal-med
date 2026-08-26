# oftal-med.ru

Bitrix-магазин офтальмологического оборудования.

| | |
|---|---|
| GitHub | https://github.com/bziksv/oftal-med |
| Prod | https://oftal-med.ru (`45.90.35.63`) |
| Path | `/var/www/oftal-med.ru/data/www/oftal-med.ru` |
| Local | http://127.0.0.1:8101/ |

Полная документация: [docs/PROJECT.md](docs/PROJECT.md)

```bash
cp .local/db.env.example .local/db.env
./scripts/setup-local-db.sh --background
./scripts/start-dev.sh
./scripts/stop-dev.sh
```
