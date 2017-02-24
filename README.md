#ISTSI

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

This project includes config files to create a dev environment with Nginx, PHP-FPM, MySQL, among others.

* Install [Docker] and [Docker Compose]
* Go to `docker/`, rename `.env.dist` to `.env` and fill all the options.

To start containers:
    ```sh
    $ ./deploy.sh up
    ```

To stop them:
    ```sh
    $ ./deploy.sh down
    ```

You may access the webpage at [istsi.dev](http://istsi.dev)

### License

See `License.md`

   [IST Summer Internships]: <https://istsi.org/>
   [Composer]: <https://getcomposer.org/download/>
   [Docker]: <https://docs.docker.com/engine/installation/>
   [Docker Compose]: <https://docs.docker.com/compose/install/>
   [Miguel de Moura]: <https://migueldemoura.com/>
