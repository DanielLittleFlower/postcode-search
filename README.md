# Postcode Search API
This is a Symfony-based web API that allows searching for postcodes based on different criteria.

### Installation
To install the application, you need to have PHP and Composer installed on your system.

### Clone the repository:
`git clone https://github.com/DanielLittleFlower/postcode-search.git`

### Install dependencies:
```
cd postcode-search
composer install
```

## Set up the database:

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

---

# ImportPostcodesCommand

This is a PHP script that defines a Symfony console command, named ImportPostcodesCommand, that downloads and imports UK postcodes into a database. The purpose of this script is to automate the process of downloading a ZIP archive containing a CSV file of UK postcodes, extracting the CSV file, and then inserting the postcodes into a database table.

The ImportPostcodesCommand class extends the Command class from the Symfony Console component and overrides the `configure()` and `execute()` methods.

The `configure()` method sets the description of the command, which is displayed when the user runs `php bin/console list`.

The `execute()` method is called when the user runs the command. The method downloads the ZIP archive containing the CSV file of postcodes from the URL specified by _POSTCODES_URL_, extracts the CSV file from the archive, and inserts the postcodes into the database.

The script uses the `GuzzleHttp\Client` class to download the ZIP archive, and the `ZipArchive` class to extract the CSV file from the archive. The CSV file is then parsed using `fgetcsv()`, and each row is inserted into the database using a prepared statement.

The database connection is passed to the ImportPostcodesCommand class constructor, and stored in the _$connection_ instance variable, which is used in the `execute()` method to execute the SQL statement.

### Documentation:

To use this script, you will need to have PHP installed on your machine, as well as the Symfony Console and Doctrine DBAL components.

To run the script, navigate to the root directory of your Symfony project in a terminal window and enter the following command:

`php bin/console ImportPostcodesCommand`

This will download the ZIP archive containing the CSV file of UK postcodes, extract the CSV file, and insert the postcodes into the database.

The script defines the following command-line options:

_--help_: Displays the help message for the command.

The script assumes that the following database table exists:

```
CREATE TABLE postcodes (
  postcode VARCHAR(10) PRIMARY KEY,
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL
);
```

This table should have three columns: postcode, latitude, and longitude. The postcode column should be a VARCHAR(10) primary key, and the latitude and longitude columns should be DECIMAL(10, 8) and DECIMAL(11, 8), respectively.

The script assumes that the following constants are defined:

POSTCODES_URL: The URL of the ZIP archive containing the CSV file of UK postcodes.
If these assumptions do not hold true for your application, you will need to modify the script accordingly.
---

# Postcode Controller
This is a Symfony controller that provides several endpoints for searching for postcodes in a database. The controller is located in the App\Controller namespace and is named PostcodeController.

### Dependencies
This controller relies on the following dependencies:

1. `Doctrine\DBAL\Connection`: A database connection object.
2. `Symfony\Bundle\FrameworkBundle\Controller\AbstractController`: An abstract controller class that provides useful helper methods.

### Usage
This controller provides two endpoints for searching for postcodes:

1. `/postcode/search/{term}`: Searches for postcodes that match a given term.
2. `/postcode/nearby/{latitude}/{longitude}`: Searches for postcodes within a certain distance from a given location.

### Searching for postcodes

__Endpoint__: `/postcodes/search/{query}`

This endpoint searches for postcodes that start with a given partial code. The partial code is provided as a query parameter named partial. The response is a JSON object containing an array of matching postcodes. 

The endpoint uses the following HTTP method: `GET`: Retrieves postcodes that start with the given partial code.

_Optional parameters_:
`limit`: limits the number of results returned (default 10)

_Example_
To search for postcodes that start with the code "AB1", send a GET request to:
`/postcodes/search/AB1?limit=20`.

_Response_
```
{
  "data": [
    {
      "type": "postcode",
      "id": "AB1 0AA",
      "attributes": {
        "latitude": 57.101474,
        "longitude": -2.242851
      }
    },
    {
      "type": "postcode",
      "id": "AB1 0AB",
      "attributes": {
        "latitude": 57.102554,
        "longitude": -2.246308
      }
    },
    ...
  ]
}
```

__Endpoint__: `/postcode/nearby/{latitude}/{longitude}?distance={distance}&limit={limit}`

This endpoint searches for postcodes within a certain distance from a given location. The location is specified by latitude and longitude, which are provided as path parameters. The response is a JSON object containing an array of nearby postcodes. The endpoint supports the following query parameters:

1. `distance`: the maximum distance (in miles) from the given location to search for postcodes. Defaults to 1.0 mile.

2. `limit`: the maximum number of postcodes to return in the response. Defaults to 10.

The endpoint uses the following HTTP method: `GET`: Retrieves postcodes that are within the specified distance from the given location.

_Example_
To search for postcodes that are within 10 miles of latitude 51.507435 and longitude -0.108918, send a GET request to `/postcode/nearby/51.507435/-0.108918?distance=1.5&limit=5`.

_Response_
```
{
   "data":[
      {
         "type":"postcode",
         "id":"EC4Y0HJ",
         "attributes":{
            "latitude":51.510714,
            "longitude":-0.108508
         }
      },
      {
         "type":"postcode",
         "id":"EC4Y0BD",
         "attributes":{
            "latitude":51.51088,
            "longitude":-0.109337
         }
      },
      {
         "type":"postcode",
         "id":"EC4Y0LB",
         "attributes":{
            "latitude":51.51088,
            "longitude":-0.109337
         }
      },
      {
         "type":"postcode",
         "id":"EC4Y0NL",
         "attributes":{
            "latitude":51.51088,
            "longitude":-0.109337
         }
      },
      {
         "type":"postcode",
         "id":"EC4Y0LD",
         "attributes":{
            "latitude":51.51088,
            "longitude":-0.109337
         }
      }
   ]
}
```

### Review Criteria
This project will be reviewed based on the following criteria:

1. _Simplicity_: The code should be simple and easy to understand.
2. _Maintainability_: The code should be well-organized and easy to maintain.
3. _Correctness_: The code should be correct and free from errors.
4. _Understanding and use of suitable technology_: The technology used should be appropriate for the task at hand.
5. _Performance_: The code should be efficient and perform well.
6. _Documentation_: The code should be well-documented.
7. _Security_: The code should be secure and free from vulnerabilities.

