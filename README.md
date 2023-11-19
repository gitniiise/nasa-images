--- [Deutsche Version unten](#nasa-api-bild-downloader) ---


# NASA API Image Downloader

This Symfony Console task tool allows you to download images from the NASA API based on the EPIC service (Earth Polychromatic Imaging Camera).

## Table of Contents

- [Description](#description)
- [Requirements](#requirements)
- [Setup](#setup)
- [Usage](#usage)
- [Example](#example)
- [Commands](#commands)
- [Testing](#testing)
- [Documentation](#documentation)
- [Contact](#contact)

## Description

The tool accesses the NASA API to download images from the EPIC service for a specific day and save them in a specified folder. It also offers the possibility to pass the date to get images from a specific day. If no date is specified, the images of the last available day will be downloaded.

## Requirements:
To use this Symfony Console Task for downloading images from the NASA API, the following requirements are necessary:
- PHP version: 8.1 or higher
- Symfony: 6.3.*
- API key for NASA: You need an API key from the NASA API to be able to access the images. You can obtain the API key from the official [NASA API website](https://api.nasa.gov/index.html#browseAPI).

Make sure these requirements are met before running the app:download-images command to download and save images from the NASA API.

## Setup
1. clone the repository:

   ```shell
   git clone git@github.com:gitniiise/nasa-images.git
   ```
2. install the dependencies:

   ```shell
   composer install
   ```
3. manually create an `.env` file in the root directory of the project:
   ```shell
   cp .env.example .env
   ```
5. replace the NASA API key in the `.env` file: `NASA_API_KEY=YOUR_API_KEY`.
   ```shell
   nano .env
   ```

## Usage

 ```shell
 php bin/console app:download-images target-folder [date]
 ```
- target-folder: The target folder where the downloaded images should be saved.
- [date] (optional): The date for which the images are to be downloaded. In the format YYYY-MM-DD. If no date is specified, the images are downloaded for the last available day.

## Example

Info: By default, after a command, if the specified folder already exists, the user is expected to confirm whether the folder should be overwritten or not. The entry is made via the console by entering "yes" or "no".

- Downloading images to an existing folder:

   ```shell
   php bin/console app:download-images public/images
   ```

  This will download the images for the last available day and save them in the public/images folder.

- Downloading images for a specific day to an existing folder:

   ```shell
   php bin/console app:download-images public/images 2023-11-15
   ```
  
  This command downloads the images for November 15, 2023 and saves them in the public/images folder.


## Commands

- app:download-images: Downloads images from the NASA EPIC-API and saves them in a folder.

## Testing
To run the PHPUnit tests:

```shell
php bin/phpunit
```

## Documentation

- NASA APIs: Information about the available APIs can be found at [NASA-APIs](https://api.nasa.gov/index.html#browseAPI).
- Symfony Documentation: To learn more about Symfony and how to use the console, check out the official Symfony documentation: [Symfony Setup](https://symfony.com/doc/current/setup.html#creating-symfony-applications), [Symfony Console](https://symfony.com/doc/current/console.html).


## Author
Denise Bebenroth

## Contact
If you have any questions or concerns, you can reach me at denise.bebenroth@web.de.


***

--- [English version above](#nasa-api-image-downloader) ---


## NASA API Bild-Downloader

Dieses Symfony Console Task-Tool ermöglicht das Herunterladen von Bildern von der NASA-API basierend auf dem EPIC-Dienst (Earth Polychromatic Imaging Camera).

## Inhaltsverzeichnis

- [Beschreibung](#beschreibung)
- [Anforderungen](#anforderungen)
- [Installation](#installation)
- [Verwendung](#verwendung)
- [Beispielanwendungen](#beispielanwendungen)
- [Befehle](#befehle)
- [Tests](#tests)
- [Dokumentation](#dokumentation)
- [Kontakt](#kontakt)

## Beschreibung

Die Anwendung greift auf die NASA-API zu, um Bilder vom EPIC-Dienst für einen Tag herunterzuladen und in einem angegebenen Ordner zu speichern. Es bietet auch die Möglichkeit, das Datum zu übergeben, um Bilder von einem bestimmten Tag zu erhalten. Falls kein Datum angegeben wird, werden die Bilder des letzten verfügbaren Tages heruntergeladen.

## Anforderungen:
Um dieses Symfony Console Task für den Download von Bildern aus der NASA-API nutzen zu können, sind folgende Voraussetzungen erforderlich:
- PHP-Version: 8.1 oder höher
- Symfony: 6.3.*
- API Key für NASA: Du benötigst einen API-Schlüssel von der NASA-API, um auf die Bilder zugreifen zu können. Den API-Schlüssel kannst du auf der offiziellen [NASA-API-Website](https://api.nasa.gov/index.html#browseAPI) erhalten.

Stelle sicher, dass diese Anforderungen erfüllt sind, bevor du den Befehl app:download-images ausführst, um Bilder von der NASA-API herunterzuladen und zu speichern.

## Installation
1. Klone das Repository:

   ```shell
   git clone git@github.com:gitniiise/nasa-images.git
   ```
2. Installiere die Abhängigkeiten:

   ```shell
   composer install
   ```
3. Erstelle manuell eine `.env`-Datei im Stammverzeichnis des Projekts:
   ```shell
   cp .env.example .env
   ```
5. Ersetze den NASA API-Schlüssel in der `.env`-Datei: `NASA_API_KEY=YOUR_API_KEY`.
   ```shell
   nano .env
   ```

## Verwendung

 ```shell
 php bin/console app:download-images target-folder [date]
 ```
- target-folder: Der Zielordner, in dem die heruntergeladenen Bilder gespeichert werden sollen.
- [date] (optional): Das Datum, für das die Bilder heruntergeladen werden sollen. Im Format YYYY-MM-DD. Falls kein Datum angegeben wird, werden die Bilder für den letzten verfügbaren Tag heruntergeladen.

## Beispielanwendungen

Info: Standardmäßig wird nach einem Befehl, falls der angegebene Ordner bereits existiert, eine Bestätigung vom Benutzer erwartet, ob der Ordner überschrieben werden soll oder nicht. Die Eingabe erfolgt über die Konsole durch die Eingabe von "yes" oder "no".

- Herunterladen von Bildern in einen vorhandenen Ordner:

   ```shell
   php bin/console app:download-images public/images
   ```

  Dadurch werden die Bilder des letzten verfügbaren Tages heruntergeladen und im Ordner public/images gespeichert.

- Herunterladen von Bildern für einen bestimmten Tag in einen existierenden Ordner:

   ```shell
   php bin/console app:download-images public/images 2023-11-15
   ```
  
  Dieser Befehl lädt die Bilder für den 15. November 2023 herunter und speichert sie im Ordner public/images.


## Befehle

- app:download-images: Lädt Bilder von der NASA EPIC-API herunter und speichert sie in einem Ordner.

## Tests
Um die PHPUnit-Tests auszuführen:

```shell
php bin/phpunit
```

## Dokumentationen

- NASA-APIs: Informationen zu den verfügbaren APIs findest du unter [NASA-APIs](https://api.nasa.gov/index.html#browseAPI).
- Symfony Dokumentation: Um mehr über Symfony und die Verwendung der Konsole zu erfahren, sieh dir die offizielle Symfony-Dokumentation an: [Symfony Setup](https://symfony.com/doc/current/setup.html#creating-symfony-applications), [Symfony Console](https://symfony.com/doc/current/console.html).  


## Autorin
Denise Bebenroth

## Kontakt
Bei Fragen oder Anliegen kannst du mir unter denise.bebenroth@web.de erreichen.
