# ISTSI

> Since I've left the ISTSI team, and am no longer maintaining this repository, all infrastruture has been moved to the hands of the faculty. A *postmortem* and general overview of the program is available in [my blog](https://migueldemoura.com/posts/ist-summer-internships-istsi/).
---

ISTSI was the website and submission platform for the [IST Summer Internships].

It was built to ease the work of those behind the program and to allow the candidates to have a simple, consistent and comprehensive experience.

### Installation and Deployment

This project includes config files to create an environment with Nginx, PHP-FPM, [Composer], PostgreSQL/MySQL and automatic backups.

* Install [Docker] and [Docker Compose]
* Go to `app/src/`, rename `settings.php.dist` to `settings.php` and fill all options.
* Go to `deployment/`, rename `.env.dist` to `.env` and fill all fields.
The available settings for the ENV variable are `dev`, `staging` and `prod`.
* If the above ENV variable isn't `dev` go to `deployment/nginx/tls`, replace all files with your own and strip the `.dist` from the filenames.
* Run `$ bin/console setup`.

* Start environment:

    ```sh
    $ bin/console deploy up -d
    ```

When first starting the environment you will need to run `$ bin/console migrate` to update the database schema.
This process isn't done automatically since renamed columns are deleted upon migration (potential loss of data).

To access the dev webpage go to [istsi.localhost](https://istsi.localhost).

* Stop environment:

    ```sh
    $ bin/console deploy down
    ```

### License

See `License.md`

   [IST Summer Internships]: <https://istsi.org/>
   [Composer]: <https://getcomposer.org/download/>
   [Docker]: <https://docs.docker.com/engine/installation/>
   [Docker Compose]: <https://docs.docker.com/compose/install/>
   [Miguel de Moura]: <https://migueldemoura.com/>
