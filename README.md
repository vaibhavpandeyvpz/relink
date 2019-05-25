<h1 align="center">Relink</h1>

<p align="center">
    Open-source, feature packed URL shortener (like <a href="https://bit.ly/">bit.ly</a>) for business use. Made with <a href="https://symfony.com/">Symfony</a> mixed with love.
</p>

[![Screenshot](https://raw.githubusercontent.com/vaibhavpandeyvpz/relink/master/screenshot.png)](https://raw.githubusercontent.com/vaibhavpandeyvpz/relink/master/screenshot.png)

### Requirements
- [PHP](https://php.net/) 7.1 or later
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) w/ [npm](https://www.npmjs.com/)

### Installation
The installation is very simple and quick. Just open a **Terminal** or **Command Prompt** window and type below commands:

```bash
mkdir project-folder
cd project-folder

git clone https://github.com/vaibhavpandeyvpz/relink .

echo APP_ENV=dev > .env.local # or APP_ENV=prod
composer install # append --no-dev if using APP_ENV=prod
npm install
npm run build

nano .env.local # put appropriate DATABASE_URL and MAILER_URL
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load # to add administrative user, works only if APP_ENV=dev

php bin/console server:run
```

### Usage
Once installed, you can login to relink using `admin@relink.app`/`88888888` email and password combination.

### License
See [LICENSE](LICENSE) file.
