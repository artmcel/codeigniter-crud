=Comandos de Migración=

Migrar:
docker-compose run --rm migration migrate

Restaurar desde 0:
docker-compose run --rm migration migrate --fresh

Sembrar:
docker-compose run --rm migration migrate --seed

Migrar hasta cierta etapa:
docker-compose run --rm migration migrate --to=NOMBRE_STAGE

Downgrade de la base 
docker-compose run --rm migration migrate --down