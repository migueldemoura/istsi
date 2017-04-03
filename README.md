# ISTSI

ISTSI is the website and submission platform for the [IST Summer Internships].

It was built to ease the work of those behind the program and to allow the candidates to have a simple, consistent and comprehensive experience.

### Installation

* Install [Composer]
* Install dependencies:

    ```sh
    $ composer install
    ```
* Go to `app/src/`, rename `settings.php.dist` to `settings.php` and fill all the options.

### Docker

This project includes config files to create an environment with Nginx, PHP-FPM, MySQL, among others.

* Install [Docker] and [Docker Compose]
* Go to `docker/`, rename `.env.dist` to `.env` and fill all the options.
The available options for the ENV variable are `dev`, `staging` and `prod`.
* Go to `docker/nginx/shared/tls`, replace all files with your own and strip the `.dist` from the filenames.

* Start environment:

    ```sh
    $ ./deploy.sh up
    ```

You may need to also execute the following to fix some permission issues:

```sh
$ ./deploy.sh exec
container# cd /var/www && chown -R www-data:www-data cache data logs
container# exit
```

To access the dev webpage go to [istsi.localhost](http://istsi.localhost).

* Stop environment:

    ```sh
    $ ./deploy.sh down
    ```

### License

See `License.md`

   [IST Summer Internships]: <https://istsi.org/>
   [Composer]: <https://getcomposer.org/download/>
   [Docker]: <https://docs.docker.com/engine/installation/>
   [Docker Compose]: <https://docs.docker.com/compose/install/>
   [Miguel de Moura]: <https://migueldemoura.com/>
