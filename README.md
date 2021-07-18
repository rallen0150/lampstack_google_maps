# lampstack_google_maps
LAMP stack structured code base that uses Google Maps API and is user based of which saved places show up on the map.

A user can be created and then once they are logged in they will see a table of saved points of interests with a marker on the map showing the location. The logged in user is session based and a local LAMP server is required. You can update the connection in the _db/connection.php_ to whatever fits your local database credentials. 

- _A Google Maps API key is required that you will need to get. It is stored as an **environment variable**._
