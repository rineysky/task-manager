## Task Manager - test application to manage user tasks.

## Dev environment installation

#### Linux requirements

* **git** (source code version control system): [installation guide](https://git-scm.com/download/linux)
* **docker** (to facilitate setup and configuring of dependant services): [installation guide](https://docs.docker.com/install/linux/docker-ce/ubuntu/)

### Project setup

#### Dependant services

Checkout project's source code and change current directory to it.

To build all the required services you need to execute:
```bash
docker-compose build
```
To start them:
```bash
docker-compose up -d
```

To stop them:
```bash
docker-compose stop
```

To be inside the php container you need to execute:
```bash
docker exec -it task_manager-php bash
```

To exit from the php container you need to enter:
```bash
Ctrl + P + Q
```

To generate JWT keys please take a look at [jwt docs](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md)

#### Install project's dependencies (inside the php container)

You need to repeat this operations every time when `composer.lock` file gets updates.  
But, you can safely execute these commands multiple times. So, you can execute this commands every time when you pull from the repository.

Install dependencies:
```bash
composer install
```

#### Usage (inside the php container)

Execute a migration to the latest available version:
```bash
bin/console doctrine:migrations:migrate
```

Create test data:
```bash
bin/console doctrine:fixtures:load
```

Clear doctrine cache:
```bash
bin/console doctrine:cache:clear-metadata
bin/console doctrine:cache:clear-query
bin/console doctrine:cache:clear-result
```

Clear symfony cache:
```bash
bin/console ca:cl
```
