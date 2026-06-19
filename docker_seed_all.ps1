Write-Host "Resetting and seeding user-service..."
docker compose exec -T user-service php artisan migrate:fresh --seed --force

Write-Host "Resetting and seeding food-service..."
docker compose exec -T food-service php artisan migrate:fresh --seed --force

Write-Host "Resetting order-service..."
docker compose exec -T order-service php artisan migrate:fresh --force

Write-Host "Re-applying Hasura metadata..."
docker compose up -d --force-recreate hasura-init
docker wait iae-hasura-init | Out-Null
docker compose logs --tail=40 hasura-init

Write-Host "Database seed complete."
