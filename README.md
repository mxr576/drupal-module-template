# Pronovix Drupal module template

## Start development of a new module

### Building a new module with Composer

```sh
$ composer create-project -s dev pronovix/drupal-module-template path/to/my_module
```
or

```sh
$ docker run --rm -it -v $PWD:/app --user $(id -u):$(id -g) composer create-project -s dev pronovix/drupal-module-template my_module
```

See the [composer documentation](https://getcomposer.org/doc/03-cli.md#create-project) for details.

## Contributing to this template

* Spin up the containers inside the `.dev` folder with `docker-compose up -d`.
* Open an interactive shell with `docker-compose exec php bash` and run any command that you need.

#### Test module creation from this template:

Run this inside the `php` container:

```sh
$ COMPOSER_MIRROR_PATH_REPOS=1 composer create-project --repository='{"type": "path", "url": "/mnt/files/drupal_module"}' -s dev pronovix/drupal-module-template /path/in/container/my_module
```

Alternatively, you can use the official Composer image for the same, just run this command in the root of the template:

```sh
$  docker run --rm -it -v $PWD:/app -v /tmp:/tmp --user $(id -u):$(id -g) -e COMPOSER_MIRROR_PATH_REPOS=1 composer:1.7 create-project --repository='{"type": "path", "url": "/app"}' -s dev pronovix/drupal-module-template /tmp/my_module
```

(This will create a module from the template in the host system's `/tmp/my_module` folder.)

## Applying/updating template files on an existing module

1. Follow the steps from "Building a new module with Composer". If your module's machine name is `foobar` use that as a parameter, like `composer create-project -s dev pronovix/drupal-module-template foobar`.
2. Move the following folders and files from the created folder to your module's root:
  * `.dev`
  * `docs`
3. Move or merge the content of any remaining files and folders.
  * **Pay more attention when you are merging the `composer.json` files, especially to the `require-dev` section which defines the correct versions of the testing related dependencies.**
  * If you do not want to run tests on Travis CI then files that contains `travis` can be ignored.

## Credits

Inspired by the [OpenEuropa Drupal module template](https://github.com/openeuropa/drupal-module-template).
