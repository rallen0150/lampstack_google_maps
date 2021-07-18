<?php
session_start();
include_once("db/connection.php");

Class User {
    public $user_id;
    public $firstname;
    public $lastname;

    function __construct() {
        global $con;
        // Find the user based on the user_id
        $findUser = "SELECT firstname, lastname FROM users WHERE user_id = '".$_SESSION['user_id']."' ";
        if(!$result = $con->query($findUser)){
            die('There was an error running the query 1 [' . $con->error . ']');
        } else {
            while($row = $result->fetch_assoc()) {
                $this->user_id = $_SESSION['user_id'];
                $this->firstname = $row['firstname'];
                $this->lastname = $row['lastname'];
            }
        }  
    }

    // Get the user's fullname into one variable
    function getFullname() {
        return $this->firstname." ".$this->lastname;
    }
}

Class POI {
    public $name = [];
    public $lat = [];
    public $long = [];
    public $address = [];
    public $data = [];

    function __construct($user_id) {
        global $con;
        // grab all saved points of interests based on the logged in user id
        $findPOI = "SELECT point_text, point_lat, point_long, address FROM point_of_interests WHERE user_id = '$user_id' ";
        if(!$result = $con->query($findPOI)){
            die('There was an error running the query 2 [' . $con->error . ']');
        } else {
            while($row = $result->fetch_assoc()) {
                $this->name[] = $row['point_text'];
                $this->lat[] = $row['point_lat'];
                $this->long[] = $row['point_long'];
                $this->address[] = $row['address'];
            }
        }
    }

    // Merge all the data into one array
    function mergeData() {
        for ($x = 0; $x < sizeof($this->name); $x++) {
            array_push($this->data, array(
                "name" => $this->name[$x],
                "lat" => $this->lat[$x],
                "long" => $this->long[$x],
                "address"  => $this->address[$x],
            ));
        }
    }
}
$user = new User;
$fullname = $user->getFullname();
$poi = new POI($user->user_id);
$poi->mergeData();

// I have this in an environment variable just when it gets moved to git I won't have an API key present for anyone to just grab and use
$google_maps_key = getenv("GOOGLEMAPS_API_KEY");

// Need this in right now to get the Map to show up for some reason
echo "<h2 style='text-align: center;'>{$fullname}'s Points of Interests</h2>";
?>
<!DOCTYPE html>
<html>    
    <head>    
        <title>Map View</title>    
        <link rel="stylesheet" type="text/css" href="css/style.css">    
    </head>
    <body> 
        <a id='logout_btn' href="index.php?logout=1">Logout</a>
        <table id="poi_table">
            <thead>
                <tr>
                    <td>Point of Interest</td>
                    <td>Address</td>
                </tr>
            </thead>
            <?php
                foreach ($poi->data as $info) {
                    echo "
                        <tr>
                            <td class='name_cell'>".$info['name']."</td>
                            <td>".$info['address']."</td>
                        </tr>
                    ";
                }
            ?>
        </table>
        <div id="map_div">
            <input type="text" id="address_lookup" placeholder="Search Address" autocomplete="on" style="width: 55%;">
            <input type="button" value="Search on Map" onclick="searchMap();">
            <div id="map-canvas"></div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_key; ?>&libraries=places"></script>
        <script type="text/javascript">
            function initialize() {
                // Default center position
                var mapOptions = {
                    center: {lat: 34.8574, lng: -82.390189},
                    zoom: 9
                };

                // Try HTML5 geolocation. This is to ask for permission on your current location to center the map if the user allows
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        map.setCenter(pos);
                    }, function() {
                        handleLocationError(true, infoWindow, map.getCenter());
                    });
                } else {
                    // Browser doesn't support Geolocation
                    handleLocationError(false, infoWindow, map.getCenter());
                }

                var input = document.getElementById('address_lookup');
                var autocomplete = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();
                    document.getElementById('address_lookup').value = place.formatted_address.replace(/'/g, "");
                    searchMap();
                });

                // Generate the maps in the map-canvas div with all the configurations needed with the map
                var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
                
                // Starting the infowindow popup
                var infowindow = new google.maps.InfoWindow();
                <?php
                    // Show all saved markers in database
                    $index = 0; // Index of the number of saved points of interests
                    foreach ($poi->data as $row) {
                        // For each marker, onclick show the correct content
                        echo 'var myLatlng1 = new google.maps.LatLng('.
                            json_encode($row['lat']).', '.json_encode($row['long']).'); 
                            var marker'.$index.' = new google.maps.Marker({ 
                                position: myLatlng1, 
                                map: map, 
                                title:'.json_encode($row['name']).',
                                content:'.json_encode($row['name']).',
                            });

                            google.maps.event.addListener(marker'.$index.', "click", (function(marker'.$index.', infowindow) {
                                return function() {
                                    infowindow.setContent(this.content);
                                    infowindow.open(map, this);
                                };
                            })(marker'.$index.', infowindow));
                        ';
                        $index++;
                    }
                ?>
            }

            // Once the user clicks the save location, a quick ajax call to save to the database and we append the row to the table
            function addLocation(latitude, longitude, address, text) {
                $.ajax({
                    url: "db/save_location.php",
                    type: "GET",
                    data: {
                        lat: latitude,
                        long: longitude,
                        address: address,
                        name: text
                    },
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(result) {
                        var row = `
                            <tr>
                                <td class='name_cell'>${text}</td>
                                <td>${address}</td>
                            </tr>`;
                        $("#poi_table tbody").append(row)
                        initialize(); // reset the map to keep the markers
                    }
                });
            }

            function searchMap() {
                var geocoder = new google.maps.Geocoder();
                var address = document.getElementById('address_lookup').value;

                geocoder.geocode({'address': address}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var map = new google.maps.Map(document.getElementById('map-canvas'), {
                            zoom: 15,
                            center: results[0].geometry.location,
                        });

                        // Starting the infowindow popup
                        var infowindow = new google.maps.InfoWindow();
                        // Create the marker after searching the item
                        var marker = new google.maps.Marker({
                            map: map,
                            position: results[0].geometry.location,
                            content: results[0].formatted_address+`<br><a href="javascript:void(0)" onclick="addLocation(\'${results[0].geometry.viewport.lc.g}\', \'${results[0].geometry.viewport.Eb.g}\', \'${results[0].formatted_address.replace(/'/g, "")}\', \'${document.getElementById('address_lookup').value.replace(/'/g, "")}\')">Save Location</a>`
                        });
                        // Add the onclick event for the markers
                        google.maps.event.addListener(marker, 'click', (function(marker, infowindow) {
                            return function() {
                                infowindow.setContent(this.content);
                                infowindow.open(map, this);
                            };
                        })(marker, infowindow));
                    } else {
                        alert('Geocode was not successful for the following reason: ' + status);
                    }
                });
            }
            google.maps.event.addDomListener(window, 'load', initialize);
        </script>
    </body>
</html>