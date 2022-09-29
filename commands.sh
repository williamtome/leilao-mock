# PHP
$ docker run -itv $(pwd):/app -w /app -p 8080:8080 php -S 0.0.0.0:8080 -t public

# Composer
$ docker run --rm -itv $(pwd):/app -w /app -u $(id -u):$(id -g) composer {comando}