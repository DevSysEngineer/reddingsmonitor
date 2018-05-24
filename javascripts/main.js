var map = null;
var center = null
var zoom = 5;
var stop = false;
var kmlLayer = null;
var metadataChanged = null;
var hasLocalStorage = (typeof(Storage) !== 'undefined');
var refreshSeconds = %refreshSeconds%;
var placemarkObjects = [];
var darkModeStyles = [
    {elementType: 'geometry', stylers: [{color: '#242f3e'}]},
    {elementType: 'labels.text.stroke', stylers: [{color: '#242f3e'}]},
    {elementType: 'labels.text.fill', stylers: [{color: '#746855'}]},
    {
        featureType: 'administrative.locality',
        elementType: 'labels.text.fill',
        stylers: [{color: '#d59563'}]
    },
    {
        featureType: 'poi',
        elementType: 'labels.text.fill',
        stylers: [{color: '#d59563'}]
    },
    {
        featureType: 'poi.park',
        elementType: 'geometry',
        stylers: [{color: '#263c3f'}]
    },
    {
        featureType: 'poi.park',
        elementType: 'labels.text.fill',
        stylers: [{color: '#6b9a76'}]
    },
    {
        featureType: 'road',
        elementType: 'geometry',
        stylers: [{color: '#38414e'}]
    },
    {
        featureType: 'road',
        elementType: 'geometry.stroke',
        stylers: [{color: '#212a37'}]
    },
    {
        featureType: 'road',
        elementType: 'labels.text.fill',
        stylers: [{color: '#9ca5b3'}]
    },
    {
        featureType: 'road.highway',
        elementType: 'geometry',
        stylers: [{color: '#746855'}]
    },
    {
        featureType: 'road.highway',
        elementType: 'geometry.stroke',
        stylers: [{color: '#1f2835'}]
    },
    {
        featureType: 'road.highway',
        elementType: 'labels.text.fill',
        stylers: [{color: '#f3d19c'}]
    },
    {
        featureType: 'transit',
        elementType: 'geometry',
        stylers: [{color: '#2f3948'}]
    },
    {
        featureType: 'transit.station',
        elementType: 'labels.text.fill',
        stylers: [{color: '#d59563'}]
    },
    {
        featureType: 'water',
        elementType: 'geometry',
        stylers: [{color: '#17263c'}]
    },
    {
        featureType: 'water',
        elementType: 'labels.text.fill',
        stylers: [{color: '#515c6d'}]
    },
    {
        featureType: 'water',
        elementType: 'labels.text.stroke',
        stylers: [{color: '#17263c'}]
    }
];

function triggerDarkMode(active) {
    /* Map is not init */
    if (map === null) {
        return;
    }

    /* Set style */
    if (active) {
        map.setOptions({styles: darkModeStyles});
    } else {
        map.setOptions({styles: []});
    }
}

function createSidebarElement(index, placemarkObject) {
    // Create title element
    var titleElement = document.createElement('div');
    titleElement.className = 'title';

    // Create title content
    var titleContent = document.createTextNode(placemarkObject.name);
    titleElement.appendChild(titleContent);

    // Create description element
    var descriptionElement = document.createElement('div');
    descriptionElement.className = 'description';

    // Create description content
    var descriptionContent = document.createTextNode(placemarkObject.description);
    descriptionElement.appendChild(descriptionContent);

    // Create a element
    var aElement = document.createElement('a');
    aElement.className = 'placemark-' + index;
    aElement.appendChild(titleElement);
    aElement.appendChild(descriptionElement);
    aElement.onclick = function() {
        console.log(this.className);
    };

    // Create li element
    var liElement = document.createElement('li');
    liElement.appendChild(aElement);

    // Return element
    return liElement;
}

function createKMLLayer() {
    // Create new layer
    var time = (new Date()).getTime();
    kmlLayer = new google.maps.KmlLayer('%kmlMain%&time=' + time, {
        preserveViewport: true,
        map: map
    });

    // Force to set center
    map.setCenter(center);
    map.setZoom(zoom);

    // Add listener for layer
    metadataChanged = google.maps.event.addListener(kmlLayer, 'metadata_changed', function () {
        var xhttp = new XMLHttpRequest();
        xhttp.overrideMimeType('application/json');
        xhttp.open('GET', '%scriptMain%', true);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200 && this.responseText !== null) {
                // Get element by id and remove old childs
                var listElement = document.getElementById('list');
                while (listElement.firstChild) {
                    listElement.removeChild(listElement.firstChild);
                }

                // Loop object
                var jsonResponse = JSON.parse(this.responseText);
                if (jsonResponse !== null) {
                    // Set placemark objects
                    placemarkObjects = jsonResponse.payload;

                    // Create sidebar elements
                    var length = jsonResponse.payload.length;
                    for (var i = 0; i < length; i++) {
                        // Get placemarkobject
                        var placemarkObject = jsonResponse.payload[i];

                        // Add li element to list element
                        var liElement = createSidebarElement(i, placemarkObject);
                        listElement.appendChild(liElement);
                    }

                    // Get current date
                    var date = new Date();
                    var n = date.toDateString();
                    var time = date.toLocaleTimeString();

                    // Set date information
                    document.getElementById('lastupdate').innerHTML = 'Laatst bijgewekt: ' + n + ' ' + time;
                }
            }
        };
        xhttp.send();
    });
}

function initMap() {
    // Create some default values
    var mapLat = 0;
    var mapLng = 0;

    /* Check if user has local storage */
    if (hasLocalStorage) {
        // Check if map lat storage exists
        var latLocalStorage = localStorage.getItem('mapLat');
        if (latLocalStorage !== null) {
            mapLat = parseFloat(latLocalStorage);
        }

        // Check if map lng storage exists
        var lngLocalStorage = localStorage.getItem('mapLng');
        if (lngLocalStorage !== null) {
            mapLng = parseFloat(lngLocalStorage);
        }

        // Check if map zoom storage exists
        var zoomLocalStorage = localStorage.getItem('mapZoom');
        if (zoomLocalStorage !== null) {
            zoom = parseInt(zoomLocalStorage);
        }
    }

    // Create center object
    center = new google.maps.LatLng(mapLat, mapLng);

    // Setup map
    map = new google.maps.Map(document.getElementById('map'), {
        styles: [],
        center: center,
        zoom: zoom
    });

    // Add listener for center changed
    google.maps.event.addListener(map, 'center_changed', function() {
        /* Set map center */
        center = map.getCenter();

        /* Check if user has local storage and if possible store data */
        if (hasLocalStorage) {
            localStorage.setItem('mapLat', center.lat());
            localStorage.setItem('mapLng', center.lng());
        }
    });

    // Add listener for zoom changed
    google.maps.event.addListener(map, 'zoom_changed', function() {
        /* Set map zoom */
        zoom = map.getZoom();

        /* Check if user has local storage and if possible store data */
        if (hasLocalStorage) {
            localStorage.setItem('mapZoom', zoom);
        }
    });

    // Add listener for drag start
    google.maps.event.addListener(map, 'dragstart', function () {
        stop = true;
    });

    // Add listener for drag end
    google.maps.event.addListener(map, 'dragend', function () {
        stop = false;
    });

    // Create layer
    createKMLLayer();

    // Create interval for creating new layers
    setInterval(function() {
        // Stop if user is dragging
        if (stop) {
            return;
        }

        // Remove old layer
        if (kmlLayer) {
            kmlLayer.setMap(null);
        }

        // Remove old listener
        if (metadataChanged) {
            google.maps.event.removeListener(metadataChanged);
        }

        // Create new layer
        createKMLLayer();
    }, refreshSeconds);
}

// Set google event listener
google.maps.event.addDomListener(window, 'load', initMap);
