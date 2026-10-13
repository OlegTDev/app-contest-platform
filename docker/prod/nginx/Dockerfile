ARG VERSION_TAG=v1
ARG REGISTRY

FROM ${REGISTRY}/nginx:${VERSION_TAG}

COPY ./docker/prod/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /app

COPY ./public ./public
