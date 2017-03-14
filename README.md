Geoname divisions
=========

Parser to get list of divisions and subdivisions with ISO-3166-2 codes in CSV format from http://www.geonames.org/

To execute run the command in shell: 

~~~
php parse.php
~~~

The results are saved in files:

~~~
result/divisions.csv
result/subdivisions.csv
~~~

The first column is country ISO-3166-1 code

Divisions and subdivisions marked as **"no longer exists:"** are skipped.  
Territories and other divisions and subdivisions without **Geoname ID (URL)** are skipped.

**Example of parsed urls:**

* http://www.geonames.org/FR/administrative-division-.html
* http://www.geonames.org/GB/administrative-division-.html
* http://www.geonames.org/US/administrative-division-.html


License
-------

[MIT](LICENSE)
