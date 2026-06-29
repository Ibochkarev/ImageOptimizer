ImageOptimizer 1.0.0-beta1 — WebP/AVIF и responsive srcset для MODX 3.

Требуется: MODX 3, PHP 8.2+, pdoTools, VueTools 1.1.2+.
Опционально: MiniShop3 (инъекция picture в витрину магазина).

Сборка админки (из корня репозитория):
  npm install && npm run build

Функции: конвертация при загрузке, responsive srcset (480/768/1024/1440/1920),
ретро-bulk через CLI/cron, авто-<picture> на витрине, Vue 3 админка.

Документация: docs/
