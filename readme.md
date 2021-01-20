# Composer Security Adviser

With this program, you can check to see if theres any security advisories for packages in your composer.json

## How it works.

This works by getting the require/require-dev lists from your composer.json, and check each package you have for all of it's security advisories via the Packagist API.

You can also search individual packages as well.

## Installation

You just need to install this globally using composer:

` composer global require elijahcruz/composer-security-adviser`

## Usage

This is a pretty simple program to use. If you are ever unsure of how to use it, just run `composer-security-adviser help` to view the help screen.

### run:check

Aliases: run, r

````
composer-security-adviser run:check
````

Options:

````
  -D, --dev                      If require-dev should be checked as well
  -G, --global                   If you wish to check your global composer requires.
  -f, --first                    If you want to check view only the latest advisory for each package.
````

### run:single

Aliases: rs, single

````
composer-security-advisor run:single laravel/framework
````

Options

````
  -f, --first           If you want to only get the latest advisory.
````

## Contributing

If you want to contribute to this, or have any issues, feel free to open an issue, or pr on GitHub.
