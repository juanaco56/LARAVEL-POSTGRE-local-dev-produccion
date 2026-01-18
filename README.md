# DESPLEGAR NUESTRO PROYECTO LARAVEL EN VERCEL 🚀

Para esta práctica, es esencial que tengamos nuestro proyecto Laravel montado. En este caso, cogeremos el proyecto de nuestra rama main, de tenis, para desplegarlo en la plataforma Vercel. 

En esta rama hemos eliminado todo lo relacionado con Docker, ya que no es necesario que tengamos estos archivos para conseguir desplegar nuestra aplicación. Sin embargo, si la quisieramos desplegarlo en Render como la práctica anterior, si que serian necesario los archivos de Docker, ya que Render se basa en usa tus Dockerfiles para hacer correr la aplicación.

En este README.md, vamos a explicar paso por paso lo que hemos hecho para conseguir desplegar la aplicación, una pequeña bibliografía y un apartado de problemas que nos hemos ido encontrando durante el despliegue.

Sin más preámbulos, empezamos


### PASO 1. NUEVOS ARCHIVOS

Para empezar esta práctica, es necesario que, en nuestro proyecto Laravel, creemos unos nuevos archivos que nos servirán a la hora de desplegarlo en Vercel.

El primero de ellos seria una carpeta llamada: "api" en la raiz del pryecto, conteniendo este un documento .php que lo llamaremos index. Contendrá las siguientes lineas de código: 
```
<?php

require __DIR__ ."/../public/index.php";
```

Tras esto, creamos un archivo ".vercelignore" igual que hicimos la práctica anterior con ".dockerignore", el cuál contendrá lo que Vercel tiene que ignorar a la hora de desplegar el servicio. Este archivo contendrá la carpeta vendor.

```
/vendor
```

El siguiente archivo será la configuración de vercel en json (vercel.json). En el cual escribiremos:
```
{
    "version": 2,
      "framework": null,
    "functions": {
        "api/index.php": { "runtime": "vercel-php@0.7.1" }
    },
    "routes": [{
        "src": "/(.*)",
        "dest": "/api/index.php"
    }],
    "env": {
        "APP_ENV": "production",
        "APP_DEBUG": "true",
        "APP_URL":"laravel-postgre-local-dev-produccion-5uepojm4e.vercel.app",

        "APP_CONFIG_CACHE": "/tmp/config.php",
        "APP_EVENTS_CACHE": "/tmp/events.php",
        "APP_PACKAGES_CACHE": "/tmp/packages.php",
        "APP_ROUTES_CACHE": "/tmp/routes.php",
        "APP_SERVICES_CACHE": "/tmp/services.php",
        "VIEW_COMPILED_PATH": "/tmp",

        "CACHE_DRIVER": "array",
        "LOG_CHANNEL": "stderr",
        "SESSION_DRIVER": "cookie"
    }
}
```

En esta configuración, en APP_URL habría que poner la dirección donde se ha desplegado tu proyecto en Vercel. En los siguientes pasos iniciaremos sesión y sacaremos esa dirección fácilmente.


Por último, habrá que crear una carpeta vacía en la raíz del proyecto llamada "dist".

### PASO 2. CONEXIÓN CON VERCEL

 #### NOTA: Antes de nada, es necesario hacer un commit a la rama que tengamos el despliegue de Vercel ya que esta utilizará nuestro úlitmo commit para realizar el despliegue, y necesita su configuración, archivos y carpetas que hemos creado previamente

Para esta conexión a Vercel, es necesario que tengamos Node.js descargado en nuestro ordenador y escribir esto en terminal:
```
npm i -g vercel
```

Esto lo hacemos para instalar de forma global en nuestro sistema Vercel CLI, lo cuál nos permite usar los comando Vercel desde la terminal.

Una vez hecho, haremos:
```
vercel login
``` 
En nuestra terminal para hacer un login y crear un token que nos servirá para poder desplegar nuestra aplicación. Llegados a este punto, recomiendo iniciar sesión con GitHub, indicando el repositorio y la rama que utilizaremos para este despliegue. 

El siguiente paso sería irnos a la terminal otra vez y poner el comando:
```
vercel .
```
Para de esta forma, darle nuestro proyecto a Vercel, el cuál nos preguntará cosas sobre nuestro proyecto (nombre, linkearlo con un proyecto existente, en que directorio está localizado...). Cuando hayamos respondido a esas preguntas, se nos lanzará el proyecto, dándonos error en la carpeta "/dist". Eso lo solucionaremos en el siguiente apartado.


### PASO 3. CONFIGURACIONES DENTRO DE VERCEL

En este punto, daremos las configuraciones que tenemos que realizar dentro de Vercel para el correcto funcionamiento de nuestro proyecto Laravel. 

#### El primer error

El primer error que nos encontramos es en la carpeta "/dist", que está vacía, pero es muy fácil solucionarlo. Nos iremos a la página web de Vercel, a nuestro proyecto más concretamente, y nos iremos al apartado de opciones o Settings. Cuando estemos dentro, bajaremos hasta Build & Develpment Settings. Nos iremos al apartado Output Directory, lo sobre escribimos y ponemos "public" para que corra la carpeta "/public" que tiene contenido en vez de "/dist". Una vez hecho, no deberiamos tener problema en que se nos despliegue.


#### El segundo error

La versión de PHP y de Node son muy antiguas. Este error es el error que más tiempo me ha llevado solucionar ya que no encontraba nada de información acerca de este posible error. La solución a este error está en nuestro "vercel.json", más concretamente en esta línea: 
```
"functions": {
        "api/index.php": { "runtime": "vercel-php@0.7.1" }
```

En el código que he proporcionado yo ya está aplicada la correción, pero igualmente prefiero comentarlo por si da el caso de dar error en la versión. Cambié la versión de Node interna de la propia aplicación a "Node 20.x" para ver si ese era el problema, pero el problema estaba en "vercel-php@0.7.1". La guía que estaba siguiendo para hacer este ejercicio usaba la versión 0.6.0, la cuál es incompatible con Laravel. Probé también una versión más nueva como la 0.8.0, pero también me salía un nuevo error con Node. La versión que me ha funcionado correctamente es la 0.7.1, que es la que he puesto en el código anterior. Con esa configuración, se debería desplegar, aunque aun nos fataría una última cosa

### CONFIGURACIÓN DE LA BASE DE DATOS

Necesitamos configurar las variables de entorno de nuestro proyecto en Vercel para que se conecte a la Base de Datos y poder terminar de desplegar la aplicación correctamente.

Vamos a utilizar nuestra base de datos en Render creada en nuestra práctica anterior, ya que tenemos desglosadas y bien hechas nuestras variables de entorno para conectarnos a la API en Render. Para añadir las variables de entorno en Vercel nos iremos, en su página web, a nuestro proyecto. Una vez ahí, Settings -> Environments, y en Production nos dejará añadir las variables de entorno, las cuales son las siguientes:
```
  APP_KEY (disponible en nuestro .env del proyecto Laravel)
  DB_CONNECTION (en este caso, pgsql)
  DB_DATABASE: (nombre de la DB)
  DB_HOST: (nuestro host de Render conseguido con el External Link)
  DB_PASSWORD: (nuestra contraseña también sacada con el External Link)
  DB_PORT:(al ser pgsql, por defecto es el 5432)
  DB_USERNAME: (nombre de usuario de nuestra DB)
```

Una vez configuradas las variables de entorno, es el momento de "Redeployar" nuestro proyecto.


### POSIBLES ERRORES 

Es posible que nos encontremos con errores, como los mencionados previamente como la carpeta "/dist" o el de la versión de php. En este apartado, contemplamos otro posible error como los permisos de lectura. Si nos apareciera este error (que es bastante posible), lo que debemos hacer es irnos a nuestro proyecto, carpeta "/bootstrap/app.php", y forzar a Laravel a usar la carpeta "/tmp" para vistas compiladas, caché y logs. El código que habría que añadir sería este (al final del mismo):
```
  if (env('APP_ENV') === 'production') {
      $app->useStoragePath('/tmp');
  }

  return $app;
```
De esta forma, conseguimos que Laravel tenga los permisos y no haya problemas.


### CONCLUSIÓN

Esta es la forma en la que yo he conseguido desplegar mi proyecto Laravel de tenis en Vercel. Muchos problemas durante el camino, el peor con diferencia el de la versión mencionada previamente. En la carpeta imagenes habrá capturas de pantalla con nombres identificados para ver un poco más gráficamente las cosas explicadas y la aplicación desplegada.


### BIBLIOGRAFIA

Para este proyecto, me he basado en la guía proporcionada por el profesor: https://rezamandala.medium.com/how-to-deploy-laravel-project-to-vercel-7b3c2800e974

Y un video: https://www.youtube.com/watch?v=ONTDijxuTHc&t=1s , el cuál explica y enseña cosas clave sobre como desplegar un servicio Laravel en Vercel


### LINK DE LA APLICACIÓN

Este es el link de mi aplicación que me ha dado Vercel: https://laravel-postgre-local-dev-produccion-21huggfuj.vercel.app/players

###### IMPORTANTE: Poner el Endpoint para visualizar el contenido, ya que sino nos aparecerá la cabecera de Laravel