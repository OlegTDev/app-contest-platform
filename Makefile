include .env

docker-up:
	docker compose up -d

docker-up-build:
	docker compose --env-file .env up -d --build

docker-down:
	docker compose --env-file .env down --remove-orphans

docker-clear:
	docker compose --env-file .env down down -v --remove-orphans

docker-prod-build:
	docker compose -f docker-compose.prod.yml --env-file .env build

docker-prod-up:
	docker compose -f docker-compose.prod.yml --env-file .env up -d

docker-prod-up-build:
	make images-build
	docker compose -f docker-compose.prod.yml --env-file .env up -d --build

docker-prod-down:
	docker compose -f docker-compose.prod.yml --env-file .env down -v --remove-orphans

docker-exec:
	docker compose --env-file .env exec php-fpm sh

docker-run-cli:
	docker compose --env-file .env run --rm php-cli sh


#################### IMAGES BEGIN ######################

images-create: php-fpm-create node-create php-cli-create nginx-create postgres-create
images-build: php-fpm-build node-build php-cli-build nginx-build postgres-build

# PHP-FPM
php-fpm-create: php-fpm-build php-fpm-save-image-to-file php-fpm-remove-images

php-fpm-build:
	docker build \
		--build-arg PHP_FPM_VERSION=${PHP_FPM_VERSION} \
		--target prod \
		-t php-pgsql-ldap-fpm:${PHP_FPM_TAG} \
		-f ./docker/common/php-fpm/Dockerfile .

php-fpm-save-image-to-file:
	docker save -o ./docker/var/php-pgsql-ldap-fpm_${PHP_FPM_TAG}.tar php-pgsql-ldap-fpm:${PHP_FPM_TAG}

php-fpm-remove-images:
	docker rmi -f php-pgsql-ldap-fpm:${PHP_FPM_TAG}

# PHP-CLI
php-cli-create: php-cli-build php-cli-save-image-to-file php-cli-remove-images

php-cli-build:
	docker build \
		--build-arg PHP_CLI_VERSION=${PHP_CLI_VERSION} \
		--target prod \
		-t php-pgsql-ldap-cli:${PHP_CLI_TAG} \
		-f ./docker/common/php-cli/Dockerfile .

php-cli-save-image-to-file:
	docker save -o ./docker/var/php-pgsql-ldap-cli_${PHP_CLI_TAG}.tar php-pgsql-ldap-cli:${PHP_CLI_TAG}

php-cli-remove-images:
	docker rmi -f php-pgsql-ldap-cli:${PHP_CLI_TAG}


# NODE
node-create: node-build node-save-image-to-file node-remove-images

node-build:
	docker build \
		--build-arg NODE_VERSION=${NODE_VERSION} \
		-t node:${NODE_TAG} \
		-f ./docker/common/node/Dockerfile .

node-save-image-to-file:
	docker save -o ./docker/var/node_${NODE_TAG}.tar node:${NODE_TAG}

node-remove-images:
	docker rmi -f node:${NODE_TAG}

# NGINX
nginx-create: nginx-build nginx-save-image-to-file nginx-remove-images

nginx-build:
	docker build \
		--build-arg NGINX_VERSION=${NGINX_VERSION} \
		-t nginx:${NGINX_TAG} \
		-f ./docker/common/nginx/Dockerfile .

nginx-save-image-to-file:
	docker save -o ./docker/var/nginx_${NGINX_TAG}.tar nginx:${NGINX_TAG}

nginx-remove-images:
	docker rmi -f nginx:${NGINX_TAG}


# POSTGRES
postgres-create: postgres-build postgres-save-image-to-file postgres-remove-images

postgres-build:
	docker build \
		--build-arg POSTGRES_VERSION=${POSTGRES_VERSION} \
		-t postgres:${POSTGRES_TAG} \
		-f ./docker/common/postgres/Dockerfile .

postgres-save-image-to-file:
	docker save -o ./docker/var/postgres_${POSTGRES_TAG}.tar postgres:${POSTGRES_TAG}

postgres-remove-images:
	docker rmi -f postgres:${POSTGRES_TAG}

#################### IMAGES END ######################


composer-install-dev:
	docker run --rm -v ./:/app -w /app php-pgsql-ldap-cli:${PHP_CLI_TAG} composer install --no-scripts

npm-install-dev:
	docker run --rm -v ./:/app -w /app node:${NODE_TAG} npm install


composer-install-prod:
	docker run --rm -v ./:/app -w /app php-pgsql-ldap-cli:${PHP_CLI_TAG} composer install --no-dev --optimize-autoloader --no-scripts

wayfinder-generate:
	docker run --rm -v ./:/app -w /app php-pgsql-ldap-cli:${PHP_CLI_TAG} php artisan wayfinder:generate --with-form

npm-install-prod:
	docker run --rm -v ./:/app -w /app node:${NODE_TAG} npm ci

npm-build:
	docker run --rm -v ./:/app -w /app node:${NODE_TAG} npm run build
