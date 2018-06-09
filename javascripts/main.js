var map = null;
var center = null
var zoom = 5;
var stop = false;
var Popup = null;
var darkMode = false;
var mapTypeId = 'roadmap';
var hasLocalStorage = (typeof(Storage) !== 'undefined');
var hasGPSLocation = (typeof(navigator.geolocation) !== 'undefined');
var refreshSeconds = %refreshSeconds%;
var placemarkObjects = [];
var placemarkMapObjects = [];
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

function definePopupClass() {
    Popup = function(position, title, extraClassname = 'default') {
        // Set position
        this.position = position;

        // Create content element
        var contentElement = document.createElement('div');
        contentElement.className = 'popup-bubble-content ' + extraClassname;

        // Create title content
        var titleContent = document.createTextNode(title);
        contentElement.appendChild(titleContent);

        // Create pixel offset element
        var pixelOffset = document.createElement('div');
        pixelOffset.className = 'popup-bubble-anchor ' + extraClassname;
        pixelOffset.appendChild(contentElement);

        // Create anchor element
        this.anchor = document.createElement('div');
        this.anchor.className = 'popup-tip-anchor ' + extraClassname;
        this.anchor.appendChild(pixelOffset);

        // Stop events
        this.stopEventPropagation();
    };

    Popup.prototype = Object.create(google.maps.OverlayView.prototype);

    Popup.prototype.onAdd = function() {
        this.getPanes().floatPane.appendChild(this.anchor);
    };

    Popup.prototype.onRemove = function() {
        if (this.anchor.parentElement) {
            this.anchor.parentElement.removeChild(this.anchor);
        }
    };

    Popup.prototype.draw = function() {
        var divPosition = this.getProjection().fromLatLngToDivPixel(this.position);
        var display = Math.abs(divPosition.x) < 4000 && Math.abs(divPosition.y) < 4000 ? 'block' : 'none';

        if (display === 'block') {
            this.anchor.style.left = divPosition.x + 'px';
            this.anchor.style.top = divPosition.y + 'px';
        }

        if (this.anchor.style.display !== display) {
            this.anchor.style.display = display;
        }
    };

    Popup.prototype.stopEventPropagation = function() {
        var anchor = this.anchor;
        anchor.style.cursor = 'auto';

        ['click', 'dblclick', 'contextmenu', 'wheel', 'mousedown', 'touchstart',
        'pointerdown'].forEach(function(event) {
            anchor.addEventListener(event, function(e) {
                e.stopPropagation();
            });
        });
    };
}

function getMapStyles() {
    if (darkMode) {
        return darkModeStyles;
    } else {
        return [];
    }
}

function getMapClassName() {
    if (!darkMode) {
        if (mapTypeId === 'roadmap') {
            return 'default';
        } else {
            return 'dark-mode';
        }
    } else {
        return 'dark-mode';
    }
}

function triggerDarkMode(active) {
    if (map !== null) {
        // Remove placemakers
        removePlacemarkers();

        // Set dark mode state
        darkMode = active;

        // Check if user has local storage and if possible store data
        if (hasLocalStorage) {
            localStorage.setItem('darkMode', darkMode);
        }

        // Set options
        map.setOptions({styles: getMapStyles()});

        // Create placemarkers
        rebuildPlacemarkers();
    }
}

function removePlacemarkers() {
    // Remove placemakers
    for (var i = 0; i < placemarkMapObjects.length; i++) {
        placemarkMapObjects[i].setMap(null);
    }

    // Clear data
    placemarkMapObjects = [];
}

function rebuildPlacemarkers() {
    for (var i = 0; i < placemarkObjects.length; i++) {
        var placemarkObject = placemarkObjects[i];
        createPlacemarkerMarker(placemarkObject);
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
        // Get index
        var className = this.className;
        var res = className.split('-');
        var placemarkObject = placemarkObjects[res[1]];

        // Check if map or placemark object is not valid
        if (map == null || (typeof(placemarkObject) === 'undefined')) {
            return;
        }

        // Remove place makers
        removePlacemarkers();

        // Pan to
        var centerCoordinate = placemarkObject.centerCoordinate;
        map.panTo(new google.maps.LatLng(centerCoordinate.lat, centerCoordinate.lng));

        // Create placemarkers
        rebuildPlacemarkers();
    };

    // Create li element
    var liElement = document.createElement('li');
    liElement.appendChild(aElement);

    // Return element
    return liElement;
}

function createPlacemarkerMarker(placemarkObject) {
    // Get center coordinate
    var centerCoordinate = placemarkObject.centerCoordinate;

    // Create marker
    var popup = new Popup(
        new google.maps.LatLng(centerCoordinate.lat, centerCoordinate.lng),
        placemarkObject.name,
        getMapClassName()
    );

    // Add to map
    popup.setMap(map);

    // Add marker
    placemarkMapObjects.push(popup);
}

function updateLayout(listElement) {
    // Create sidebar elements
    for (var i = 0; i < placemarkObjects.length; i++) {
        // Get placemarkobject
        var placemarkObject = placemarkObjects[i];

        // Add li element to list element
        var liElement = createSidebarElement(i, placemarkObject);
        listElement.appendChild(liElement);

        // Create placemark
        createPlacemarkerMarker(placemarkObject);
    }

    // Get current date
    var date = new Date();
    var n = date.toDateString();
    var time = date.toLocaleTimeString();

    // Set date information
    document.getElementById('lastupdate').innerHTML = 'Laatst bijgewekt: ' + n + ' ' + time;

    // Ready
    stop = false;
}

function loadRemoteData() {
    var xhttp = new XMLHttpRequest();
    xhttp.overrideMimeType('application/json');
    xhttp.open('GET', '%scriptMain%', true);
    xhttp.onreadystatechange = function() {
        // Check if request is done
        if (this.readyState == 4) {
            // Check if the response status is good
            if (this.status == 200 && this.responseText !== null) {
                // Loop object
                var jsonResponse = JSON.parse(this.responseText);
                if (jsonResponse !== null) {
                    // Get element by id and remove old childs
                    var listElement = document.getElementById('list');
                    while (listElement.firstChild) {
                        listElement.removeChild(listElement.firstChild);
                    }

                    // Remove place makers
                    removePlacemarkers();

                    // Set response payload
                    placemarkObjects = jsonResponse.payload;

                    // Check if GPS location is enabled
                    if (hasGPSLocation) {
                        // Try to get GPS location of current device
                        navigator.geolocation.getCurrentPosition(function(position) {
                            // Create placemark
                            var gpsPlacemarkObject = {
                                name: 'Mijn locatie',
                                description: '',
                                centerCoordinate: {
                                    lng: position.coords.longitude,
                                    lat: position.coords.latitude,
                                    alt: 0
                                }
                            };

                            // Add GPS placemark
                            placemarkObjects.unshift(gpsPlacemarkObject);

                            // Update layout with GPS location
                            updateLayout(listElement);
                        }, function() {
                            // Error by retrieving GPS location
                            updateLayout(listElement);
                        });
                    } else {
                        // No GPS location
                        updateLayout(listElement);
                    }
                }
            } else {
                stop = false;
            }
        }
    };
    xhttp.send();
}

function initMap() {
    // Define Popup class
    definePopupClass();

    // Create some default values
    var mapLat = 0;
    var mapLng = 0;

    // Check if user has local storage
    if (hasLocalStorage) {
        // Check if dark mode storage exists
        var darkModeStorage = localStorage.getItem('darkMode');
        if (darkModeStorage !== null) {
            // Set dark mode
            darkMode = darkModeStorage;

            // Update checkbox
            document.getElementById('checkbox-dark-mode').checked = darkMode;
        }

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

        // Check if map type id storage exists
        var mapTypeIdLocalStorage = localStorage.getItem('mapTypeId');
        if (mapTypeIdLocalStorage !== null) {
            mapTypeId = mapTypeIdLocalStorage;
        }
    }

    // Set some values
    center = new google.maps.LatLng(mapLat, mapLng);

    // Setup map
    map = new google.maps.Map(document.getElementById('map'), {
        styles: getMapStyles(),
        center: center,
        zoom: zoom,
        mapTypeId: mapTypeId
    });

    // Add listener for map type id changed
    google.maps.event.addListener(map, 'maptypeid_changed', function() {
        // Set map type id
        mapTypeId = map.getMapTypeId();

        // Check if user has local storage and if possible store data
        if (hasLocalStorage) {
            localStorage.setItem('mapTypeId', mapTypeId);
        }

        // Create placemarkers
        rebuildPlacemarkers();
    });

    // Add listener for center changed
    google.maps.event.addListener(map, 'center_changed', function() {
        // Set map center
        center = map.getCenter();

        // Check if user has local storage and if possible store data
        if (hasLocalStorage) {
            localStorage.setItem('mapLat', center.lat());
            localStorage.setItem('mapLng', center.lng());
        }
    });

    // Add listener for zoom changed
    google.maps.event.addListener(map, 'zoom_changed', function() {
        /* Set map zoom */
        zoom = map.getZoom();

        // Check if user has local storage and if possible store data
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

    // Load for first time remote data
    loadRemoteData();

    // Create interval for retrieving new data
    setInterval(function() {
        if (!stop) {
            stop = true;
            loadRemoteData();
        }
    }, refreshSeconds);
}

// Set google event listener
google.maps.event.addDomListener(window, 'load', initMap);
