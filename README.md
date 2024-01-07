# ZKTeco Device Data Read

This project develop for syncing attendance data from ZKTeco devices to live through API.

## Description

First of all, this project does not connect directly to ZKTeco devices. Instead it connects to the access database which is connected with the ZKTeco device. The ZKTeco Attendance Management software is connected to device directly and pull data and store it to mdb file. We can get data by reading the mdb file.

There have two steps to get data to live.

First, get data from mdf file and store to local database. Read mdb file and get data which are new. I detect new data by `is_process=0` column which i manually added to mdb database file. After store data to local database i need to update mdb database and thats why i add another column to mdb database named `id` which is auto increment primary key.

Second, by `project/sync` url, data get from local database and make a post api call to live api. After getting success return update local database.

## Getting Started

### Dependencies

* You need to enable pdo_odbc extension in php.ini file
* You have to be installed Microsoft office 2010

### Installing

* Install ZKTeco Attendance Management system which provide with device.

### Executing program

* Import database which include in this project
* Open your ZKTeco Attendance Management software and change the database file to your project `ATT2000.mdb` file which is modified copy of zkteco application database.
* Configure `settings.ini` file and set `ATT2000.mdb` file path, local database information and your live API url.
* If everything goes well browse project and you will get data insert into your local database.
* If you want to sync data to live database, browse `project/slave` url and hope data will be inserted into your live database.

## Help

Any advise for common problems or issues please contact me.

## Authors

Shoriful Islam
Github: [@shorifulislam00](https://github.com/shorifulislam00/) 
Facebook: [@Shoriful Islam](https://www.facebook.com/shorifulislam433) 
Email: shorifulislam433@gmail.com
