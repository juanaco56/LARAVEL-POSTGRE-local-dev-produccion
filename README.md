## LARAVEL DOCKERIZADO (LOCAL,DEV,DESARROLLO(RENDER)) 🚀

En este proyecto de tenis, hemos utilizado las siguientes tecnologías:

- PHP (Laravel) 

- POSTGRESQL (Base de datos online)

- DOCKER (Virtualizar el entorno de nuestro proyecto, tanto la base de datos para
el entorno local, como el laravel y la base de datos en dev) 

- RENDER (Para desplegar nuestro proyecto de forma online, creando una base de
datos en Render como un servicio PostgreSQL y un servicio web para la
API de Laravel)



# ¿Cómo funciona el proyecto y cada una de sus partes?

Empezamos por descargarnos el proyecto proporcionado por el profesor
desde su GitHub, el cual viene ya con el entorno local prácticamente
configurado. Yo personalmente no me lo descargué así y tuve que hacer yo
el \"docker-compose.local.yml\", y me dieron algunos problemas con las
migraciones y los seeders, pero pude solucionarlo de manera fácil. Es
necesario que hagamos el \"composer install\" es nuestra terminal desde
la raiz del proyecto, para gestionar e instalar las dependencias del
proyecto.

### ENTORNO LOCAL

Para el entorno local, necesitaremos crearnos un
\"docker-compose.local.yml\" en el cual haremos un contenedor con
nuestra base de datos PostgreSQL. Cabe recalcar que es muy importante
también añadir nuestro .env para las variables de entorno, como el host,
el puerto\... Así se vería nuestro .env:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1 
DB_PORT=5432 DB_DATABASE=laravel
DB_USERNAME=user DB_PASSWORD=1234
```

Para que funcione y arrancar el proyecto una vez tengamos esto, es
necesario arrancar nuestro contenedor de Docker de PostgreSQL,
mencionado anteriormente. Aquí su estructura:
```
services:
    db:
        image: postgres:16 
        container_name: postgres_example
        restart: always 
        environment: 
            POSTGRES_USER: user 
            POSTGRES_PASSWORD: 1234
            POSTGRES_DB: laravel 
        ports:
          - "5432:5432"
```

Cuando esté arrancado, podemos lanzar el comando \"php artisan serve\"
para que nos salga que el proyecto ha sido lanzado en por ejemplo
\"http://localhost:8000", o directamente meternos a Laravel Herd y
meternos en el link proporcionado por ellos, en mi caso :
\"http://players.test/players".

##### NOTA: yo uso el endpoint "players\", en render también hay que ponerlo para que funcione correctamente y nos lleve a nuestro proyecto.

### ENTORNO DEV:

Para que el entorno dev nos funcione, necesitaremos crear 3 archivos muy
importantes:

El primero de ellos es el Dockerfile, que también nos servirá para
desplegarlo en Render en el siguiente punto. En este Dockerfile,
cogeremos la imagen de PHP e instalaremos sus dependencias, junto a las
de node que son las que utilizamos. También daremos permisos para evitar
errores en el futuro, lo expondremos en el puerto 8000 y el comando más
importante: 
```
\"CMD sh -c \"sleep 10 && php artisan migrate:fresh \--seed
\--force && php artisan serve \--host=0.0.0.0 \--port=8000\"
```
el cual
hará que se ejecuten las migraciones y seeders automáticamente al
arrancar nuestro proyecto. Esta es su estructura:
```
# COGEMOS LA IMAGEN DE PHP
FROM php:8.2-fpm 

# INSTALAMOS LAS DEPENDENCIAS
RUN apt-get update && apt-get install -y \ 
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev libpq-dev \ 
    nodejs npm \ 
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl gd intl \ 
    && apt-get clean && rm -rf /var/lib/apt/lists/* # 2. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# DIRECTORIO DE TRABAJO
WORKDIR /var/www 

# AQUÍ COPIAMOS ARCHIVOS
COPY . . 

# DEPENDENCIAS DE PHP Y NODE QUE ES LO QUE UTILIZAMOS
RUN composer install --optimize-autoloader --no-interaction
RUN npm install && npm run build 

# PERMISOS PARA EVITAR ERRORES FUTUROS CON EL TEMA PERMISOS
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# PUERTO
EXPOSE 8000 

# COMANDO FINAL ; Esperamos 10 segundos, migra con datos de prueba y arranca 
CMD sh -c "sleep 10 && php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=8000"
```
El siguiente archivo que necesitaremos crear será el
\"docker-compose.dev.yml\", en el cual crearemos dos contenedores; uno
para la base de datos PostgreSQL, y otro para la API. Esta sería su
estructura:
```
services:
  #CONTENEDOR BASE DE DATOS POSTGRES_DEV
  db-dev:
    image: postgres:16
    container_name: postgres_dev
    restart: always
    environment:
      POSTGRES_DB: laravel
      POSTGRES_USER: user
      POSTGRES_PASSWORD: 1234
    ports:
      - "5432:5432"

  # CONTENEDOR DE LA APP LARAVEL_DEV
  app-dev:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_app_dev
    depends_on:
      - db-dev
    ports:
      - "8080:8000"
    environment:
    #AQUI CAMBIAMOS HOST POR EL NOMBRE DEL CONTENEDOR DE LA BASE DE DATOS POSTGRES_DEV
      DB_CONNECTION: pgsql
      DB_HOST: db-dev 
      DB_PORT: 5432
      DB_DATABASE: laravel
      DB_USERNAME: user
      DB_PASSWORD: 1234
      APP_KEY: ${APP_KEY}
      APP_ENV: local
      APP_DEBUG: "true"
```
Y por último, el archivo que es necesario para que nos funcione:
.dockerignore. El archivo .dockerignore sirve para indicar qué archivos
y carpetas Docker debe ignorar al construir una imagen evitando que se
envíen al contexto de build lo que hace el proceso más rápido reduce el
tamaño de la imagen y mejora la seguridad ya que evita incluir cosas
como node_modules .git archivos temporales o variables de entorno
funciona de forma similar a .gitignore pero solo afecta a Docker y no a
Git y se usa para que cuando el Dockerfile hace COPY . solo se copien
los archivos realmente necesarios. Sin este archivo, mi proyecto no
funcionaba.

Para que funcione se arranca de la siguiente manera:

- Arrancamos ambos contenedores (DB Y API) y cuando esten arrancados, nos
meteremos en el puerto 8000 que es a donde apunta nuestra app. Y con
estos sencillos pasos, deveria funcionarnos sin problemas. 

##### NOTA: ES NECESARIO QUE CUANDO NOS ARRANQUE EL PUERTO 8000 Y NOS METAMOS EN ÉL,
NECESITAREMOS PONER EL ENDPOINT \"/players\", QUEDANDO DE ESTA FORMA:
\"http://localhost:8000/players".

### ENTORNO PRODUCCIÓN

Necesitaremos desplegar dos servicios: Base de datos PostgreSQL y uno de
WebService que es donde estará la API. Primero, desplegaremos la Base de
Datos. Cuando esté desplegada, necesitaremos arrancar nuestro Web
Service y configurar sus variables de entorno, las cuales van adjuntadas
en la carpeta imágenes con el nombre:
\"entornoDesarrollo_variablesEntornoWebService.png\".
* APP_KEY la podemos conseguir yéndonos a nuestro .env de nuestro proyecto y en la primera
linea nos saldrá. 
* DB_CONNECTION pondremos pgsql (PostgreSQL).
* DB_NAME la cogeremos del servicio de Render de nuestra base de datos. 
* DB_HOST lo cogeremos de nuestro servicio de Render de la base de datos, del External Link. 
* DB_PASSWORD también lo cogeremos de nuestro servicio en
render 
* DB_PORT es el 5432 ya que es PostgreSQL. 
* DB_USER nuestro user,
en la base de datos Render puse user y en este también.

Una vez esto claro, para que funcione necesitaremos coger nuestro
Dockerfile de nuestro proyecto ya subido en GitHub, y desplegarlo. No
debería darnos problemas a la hora de desplegarlo. Nuestro proyecto está
alojado en el link
:\"https://laravel-postgre-local-dev-produccion.onrender.com/players\"

##### NOTA: Es necesario poner el endpoint \"/players" para que nos funcione

Las capturas más relevantes están en la carpeta \"imágenes\", cada una
con un nombre identificado del entorno y lo que es.

