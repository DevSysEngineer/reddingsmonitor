var map = null;
var center = null;
var gpsLocation = null;
var lastKnownGPSLocation = null;
var zoom = 5;
var stopRequest = false;
var isDragging = false;
var isPlacemarkDragging = false;
var Popup = null;
var darkMode = false;
var mapTypeId = 'roadmap';
var hasLocalStorage = (typeof(Storage) !== 'undefined');
var hasGPSLocation = (typeof(navigator.geolocation) !== 'undefined');
var refreshSeconds = %refreshSeconds%;
var lastKnownMinutesDiff = 0.0;
var lastKnownHash = '';
var validPayload = null;
var activeFollow = 'none';
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
var languages = [
    {
        language: 'nl',
        textObject: {
            myLocation: 'Mijn locatie',
            follow: 'Volg',
            darkMode: 'Donkere modus',
            lastUpdate: {
                normal: 'Laatst bijgewerkt',
                error: 'Herstart alsjeblieft de GPS server'
            }
        }
    }
];
var activeLanguage = languages[0];

function definePopupClass() {
    Popup = function(id, position, title, type = 'unknown', draggable = false, extraClassname = 'default') {
        // Set some values
        this.id = id;
        this.position = position;
        this.draggable = draggable;
        this.isDragging = false;
        this.origin = null;

        // Create content element
        var contentElement = document.createElement('div');
        contentElement.className = 'popup-bubble-content ' + extraClassname;

        // Create type icon
        var iconClassName = '';
        switch (type) {
            case 'car':
                iconClassName = 'fas fa-truck-monster';
                break;
            case 'radio_portable':
                iconClassName = 'fas fa-phone';
                break;
            case 'boat_rib':
                iconClassName = 'fas fa-ship';
                break;
             case 'water_scooter':
                iconClassName = 'fas fa-tachometer-alt';
                break;
        }

        // If icon class name is not empty; If not create icon element
        if (iconClassName != '') {
            iElement = document.createElement('i');
            iElement.className = iconClassName;
            contentElement.appendChild(iElement);
        }

        // Create title content
        var titleContent = document.createTextNode(title);
        contentElement.appendChild(titleContent);

        // Create pixel offset element
        var pixelOffset = document.createElement('div');
        pixelOffset.className = 'popup-bubble-anchor ' + extraClassname;
        pixelOffset.appendChild(contentElement);

        // Create anchor element
        this.anchor = document.createElement('div');
        this.anchor.id = id;
        this.anchor.className = 'popup-tip-anchor ' + extraClassname;
        this.anchor.appendChild(pixelOffset);
        this.anchor.style.position = 'absolute';
        this.anchor.draggable = draggable;

        // Stop events
        this.stopEventPropagation();
    };

    Popup.prototype = Object.create(google.maps.OverlayView.prototype);

    Popup.prototype.onAdd = function() {
        // Check if map still exists
        var map = this.getMap();
        if (typeof(map) === 'undefined' || map === null) {
            return null;
        }

        // Check if popup is draggable
        if (this.draggable) {
            // Set current object
            var oThis = this;
            var mapDiv = map.getDiv();

            // // Add dom listener for mouselease
            google.maps.event.addDomListener(mapDiv, 'mouseleave', function() {
                if (oThis.isDragging) {
                    google.maps.event.trigger(oThis.anchor, 'mouseup');
                }
            });

            // Add dom listener for mousedown
            google.maps.event.addDomListener(this.anchor, 'mousedown', function(e) {
                // Check if map still exists
                var map = oThis.getMap();
                if (typeof(map) === 'undefined' || map === null) {
                    return null;
                }

                // Set some settings
                oThis.origin = e;
                oThis.isDragging = false;

                // Set some style
                this.style.cursor = 'move';

                // Disable draggable event for map
                oThis.map.set('draggable', false);

                // Add dom listener for mousemove
                oThis.moveHandler = google.maps.event.addDomListener(mapDiv, 'mousemove', function(e) {
                    // Check if map still exists
                    var map = oThis.getMap();
                    if (typeof(map) === 'undefined' || map === null) {
                        return null;
                    }

                    // Set dragging state
                    oThis.isDragging = true;

                    // Get left and top information
                    var origin = oThis.origin;
                    var left = origin.clientX - e.clientX;
                    var top = origin.clientY - e.clientY;

                    // Get div position
                    var divPosition = oThis.getProjection().fromLatLngToDivPixel(oThis.position);

                    // Create new position
                    var latLng = oThis.getProjection().fromDivPixelToLatLng(
                        new google.maps.Point(divPosition.x - left, divPosition.y - top)
                    );

                    // Set some values
                    oThis.origin = e;
                    oThis.position = latLng;

                    // Draw marker
                    oThis.draw();
                });
            });

            google.maps.event.addDomListener(this.anchor, 'mouseup', function() {
                // Check if map still exists
                var map = oThis.getMap();
                if (typeof(map) === 'undefined' || map === null) {
                    return null;
                }

                // Set some style
                this.style.cursor = 'auto';

                // Enable draggable event for map
                map.set('draggable', true);

                // Remove move listener
                google.maps.event.removeListener(oThis.moveHandler);
            });
        }

        // Add anchor to map
        this.getPanes().floatPane.appendChild(this.anchor);
    };

    Popup.prototype.getId = function() {
        return this.id;
    };

    Popup.prototype.getPosition = function() {
        return this.position;
    };

    Popup.prototype.getAnchor = function() {
        return this.anchor;
    };

    Popup.prototype.clearEvents = function() {
        google.maps.event.clearInstanceListeners(this.anchor);
    }

    Popup.prototype.onRemove = function() {
        // Remove listeners
        this.clearEvents();

        // Remove element
        if (this.anchor.parentElement) {
            this.anchor.parentElement.removeChild(this.anchor);
        }
    };

    Popup.prototype.draw = function() {
        var divPosition = this.getProjection().fromLatLngToDivPixel(this.position);
        this.anchor.style.left = divPosition.x + 'px';
        this.anchor.style.top = divPosition.y + 'px';
    };

    Popup.prototype.stopEventPropagation = function() {
        // Set style
        var anchor = this.anchor;
        anchor.style.cursor = 'auto';

        // Set events
        var events = null;
        if (this.draggable) {
            events = ['click', 'dblclick', 'contextmenu', 'wheel', 'touchstart', 'pointerdown'];
        } else {
            events = ['click', 'dblclick', 'contextmenu', 'wheel', 'touchstart', 'pointerdown', 'mousedown', 'mouseup'];
        }

        // Stop events
        events.forEach(function(event) {
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
    if (map === null) {
        return;
    }

    // Remove placemakers
    removePlacemarkers().then(function() {
        // Set dark mode state
        darkMode = active;

        // Check if user has local storage and if possible store data
        if (hasLocalStorage) {
            if (darkMode) {
                localStorage.setItem('darkMode', 'true');
            } else {
                localStorage.setItem('darkMode', 'false');
            }
        }

        // Set options
        map.setOptions({styles: getMapStyles()});

        // Create placemarkers
        return rebuildPlacemarkers();
    });
}

function triggerFollowMode(index) {
    // Stop here if index is the same as current active follow
    if (activeFollow === index) {
        return;
    }

    // Set folloow
    activeFollow = index;

    // Check if user has local storage and if possible store data
    if (hasLocalStorage) {
        localStorage.setItem('activeFollow', activeFollow);
    }

    // Do nothing when acitive follow is none
    if (activeFollow === 'none') {
        return;
    }

    // Try to pan correct locaion
    for (var i = 0; i < placemarkObjects.length; i++) {
        var placemarkObject = placemarkObjects[i];
        if (placemarkObject.id === activeFollow) {
            // Remove place makers
            removePlacemarkers().then(function() {
                // Pan to
                var centerCoordinate = placemarkObject.centerCoordinate;
                map.panTo(new google.maps.LatLng(centerCoordinate.lat, centerCoordinate.lng));

                // Create placemarkers
                return rebuildPlacemarkers();
            });
            break;
        }
    }
}

function removePlacemarkers() {
    // Remove placemakers
    var promises = [];
    for (var i = 0; i < placemarkMapObjects.length; i++) {
        promises.push(new Promise(resolve => {
            placemarkMapObjects[i].setMap(null);
            resolve();
        }));
    }

    /// Clear data
    return Promise.all(promises).then((values) => {
        placemarkMapObjects = [];
    });
}

function rebuildPlacemarkers() {
    // Get promises
    var promises = [];
    for (var i = 0; i < placemarkObjects.length; i++) {
        var placemarkObject = placemarkObjects[i];
        promises.push(createPlacemarkerMarker(placemarkObject));
    }

    // Wait for all promises
    return Promise.all(promises).then((values) => {
        return true;
    });
}

function createOptionElement(index, placemarkObject) {
     // Create option element
    var optionElement = document.createElement('option');
    optionElement.value = placemarkObject.id;
    optionElement.text = placemarkObject.name;

    // Return element
    return optionElement;
}

function updateSidebarElement(index, placemarkObject, element) {
    var locations = element.getElementsByClassName('location');
    if (locations[0]) {
        var centerCoordinate = placemarkObject.centerCoordinate;
        locations[0].innerText = centerCoordinate.lat + ', ' + centerCoordinate.lng;
    }
}

function createSidebarElement(index, placemarkObject) {
    // Create title element
    var titleElement = document.createElement('div');
    titleElement.className = 'title';

    // Create title content
    var titleContent = document.createTextNode(placemarkObject.name);

    // Create type icon
    var iconClassName = '';
    switch (placemarkObject.type) {
        case 'car':
            iconClassName = 'fas fa-truck-monster';
            break;
        case 'radio_portable':
            iconClassName = 'fas fa-phone';
            break;
        case 'boat_rib':
            iconClassName = 'fas fa-ship';
            break;
        case 'water_scooter':
            iconClassName = 'fas fa-tachometer-alt';
            break;
    }

    // If icon class name is not empty; If not create icon element
    if (iconClassName != '') {
        iElement = document.createElement('i');
        iElement.className = iconClassName;
        titleElement.appendChild(iElement);
    }

    // Add text node
    titleElement.appendChild(titleContent);

    // Create location element
    var locationElement = document.createElement('div');
    locationElement.className = 'location';

    // Create location content
    var centerCoordinate = placemarkObject.centerCoordinate;
    var locationContent = document.createTextNode(centerCoordinate.lat + ', ' + centerCoordinate.lng);
    locationElement.appendChild(locationContent);

    // Create a element
    var aElement = document.createElement('a');
    aElement.className = 'placemark-' + index;

    // Add title element
    aElement.appendChild(titleElement);

    // Create type content
    var typeContent = null;
    switch (placemarkObject.type) {
        case 'car':
            typeContent = document.createTextNode('Auto');
            break;
        case 'radio_portable':
            typeContent = document.createTextNode('Portofoon');
            break;
        case 'boat_rib':
            typeContent = document.createTextNode('Rib boot');
            break;
        case 'water_scooter':
            typeContent = document.createTextNode('Waterscooter');
            break;
    }

    // Check if type content is not NULL
    if (typeContent != null) {
        // Create type element
        var typeElement = document.createElement('div');
        typeElement.className = 'type';
        typeElement.appendChild(typeContent);
        aElement.appendChild(typeElement);
    }

    // Add location element
    aElement.appendChild(locationElement);

    // Create onclick event
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
        removePlacemarkers().then(function() {
            // Pan to
            var centerCoordinate = placemarkObject.centerCoordinate;
            map.panTo(new google.maps.LatLng(centerCoordinate.lat, centerCoordinate.lng));

            // Create placemarkers
            return rebuildPlacemarkers();
        });
    };

    // Create li element
    var liElement = document.createElement('li');
    liElement.appendChild(aElement);

    // Return element
    return liElement;
}

function createPlacemarkerMarker(placemarkObject) {
    return new Promise(function (resolve, reject) {
        // Get center coordinate
        var centerCoordinate = placemarkObject.centerCoordinate;

        // Get draggable state
        var draggable = false;
        if (placemarkObject.id == 'gps') {
            draggable = true;
        }

        // Create marker
        var popup = new Popup(
            placemarkObject.id,
            new google.maps.LatLng(centerCoordinate.lat, centerCoordinate.lng),
            placemarkObject.name,
            placemarkObject.type,
            draggable,
            getMapClassName(),
        );

        // Set map
        popup.setMap(map);

        // Check if popup is draggable
        if (draggable) {
            // Add event listener for draggable place marker
            google.maps.event.addDomListener(popup.getAnchor(), 'mousedown', function(e) {
                isPlacemarkDragging = true;
            });

            // Add event listener for draggable place marker
            google.maps.event.addDomListener(popup.getAnchor(), 'mouseup', function(e) {
                // Search for placemark object
                var date = new Date();
                var coordinate = { lng: -1, lat: -1 };
                for (var i = 0; i < placemarkMapObjects.length; i++) {
                    if (placemarkMapObjects[i].getId() == this.id) {
                        var position = placemarkMapObjects[i].getPosition();
                        coordinate.lng = position.lng();
                        coordinate.lat = position.lat();
                        break;
                    }
                }

                // Set local storage data
                if (hasLocalStorage) {
                    localStorage.setItem(this.id + 'Lng', coordinate.lng);
                    localStorage.setItem(this.id + 'Lat', coordinate.lat);
                }

                // Check if id is GPS; If yes, update GPS location
                if (this.id == 'gps') {
                    gpsLocation = (coordinate.lng >= 0 && coordinate.lat >= 0) ? coordinate : null;
                }

                // Remove sidebar elements
                var listElement = document.getElementById('list');
                while (listElement.firstChild) {
                    listElement.removeChild(listElement.firstChild);
                }

                // Create sidebar elements
                for (var i = 0; i < placemarkObjects.length; i++) {
                    // Search for object with same id and update some values
                    if (placemarkObjects[i].id == this.id) {
                        placemarkObjects[i].updateTime = new Date().getTime();
                        placemarkObjects[i].centerCoordinate.lng = coordinate.lng;
                        placemarkObjects[i].centerCoordinate.lat = coordinate.lat;
                    }

                    // Create  li element
                    var liElement = createSidebarElement(i, placemarkObjects[i]);
                    listElement.appendChild(liElement);
                }

                // Dragging is stopped
                isPlacemarkDragging = false;
            });
        }

        // Add marker
        placemarkMapObjects.push(popup);
        resolve(popup);
    });
}

function updateLayout(selectElement, listElement, minutesDiff, smartUpdate) {
    return new Promise(function (resolve, reject) {
        /* Create some log */
        console.log('Update layout');

        // Move to center
        var foundFollow = false;
        if (activeFollow !== 'none') {
            for (var i = 0; i < placemarkObjects.length; i++) {
                var placemarkObject = placemarkObjects[i];
                if (placemarkObject.id === activeFollow) {
                    // We found the follow
                    foundFollow = true;

                    // Update maps
                    var centerCoordinate = placemarkObject.centerCoordinate;
                    map.panTo(new google.maps.LatLng(centerCoordinate.lat, centerCoordinate.lng));
                }
            }
        } else {
            foundFollow = true;
        }

        // Retrun result
        resolve(foundFollow);
    }).then(foundFollow => {
        // Create list
        var promises = [];

        // Add only options when smart update is false
        if (!smartUpdate) {
            promises.push(new Promise(resolve => {
                // Create option element
                var optionElement = document.createElement('option');
                optionElement.value = 'none';
                optionElement.text = '--';
                selectElement.appendChild(optionElement);

                // Add options based on placemarkObjects
                for (var i = 0; i < placemarkObjects.length; i++) {
                    // Add option element to list element
                    var placemarkObject = placemarkObjects[i];
                    var optionElement = createOptionElement(i, placemarkObject);
                    selectElement.appendChild(optionElement);

                    /// Select active follow
                    if (placemarkObject.id === activeFollow) {
                        selectElement.value = activeFollow;
                    }
                }
                resolve();
            }));
        }

        // When smart update is active, update only the elements
        if (smartUpdate) {
            promises.push(new Promise(resolve => {
                var childNodes = listElement.childNodes;
                for (var i = 0; i < childNodes.length; i++) {
                    var placemarkObject = placemarkObjects[i];
                    updateSidebarElement(i, placemarkObject, childNodes.length[i]); 
                }
                resolve();
            }));
        } else {
            promises.push(new Promise(resolve => {
                for (var i = 0; i < placemarkObjects.length; i++) {
                    var placemarkObject = placemarkObjects[i];
                    var liElement = createSidebarElement(i, placemarkObject);
                    listElement.appendChild(liElement);    
                }
                resolve();
            }));
        }

        // Add markers
        for (var i = 0; i < placemarkObjects.length; i++) {
            var placemarkObject = placemarkObjects[i];
            promises.push(createPlacemarkerMarker(placemarkObject));
        }

        // Update date
        promises.push(new Promise(resolve => {
            resolve(updateDate(minutesDiff));
        }));

        // Run all promise
        return Promise.all(promises).then(function() {
            // It cam be that active follow not exists anymore
            if (!foundFollow) {
                // Reset values
                selectElement.value = 'none';
                activeFollow = 'none';

                // Check if user has local storage and if possible store data
                if (hasLocalStorage) {
                    localStorage.setItem('activeFollow', 'none');
                }
            }

            // Success
            return true;
        });
    });
}

function updateDate(minutesDiff) {
    // Get last update element
    var lastUpdateElement = document.getElementById('lastupdate');

    // Check if minutes diff is to high
    if (minutesDiff < 10.0) {
        // Get current date
        var date = new Date();
        var n = date.toDateString();
        var time = date.toLocaleTimeString();

        // Set date information
        lastUpdateElement.innerHTML = activeLanguage.textObject.lastUpdate.normal + ': ' + n + ' ' + time;
        lastUpdateElement.className = '';
    } else {
        // Set some text
        lastUpdateElement.innerHTML = activeLanguage.textObject.lastUpdate.error;
        lastUpdateElement.className = 'error';
    }

    // Update last know minutes diff
    lastKnownMinutesDiff = minutesDiff;

    // Success
    return true;
}

function loadRemoteData() {
    stopRequest = true;
    return new Promise(function (resolve, reject) {
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
                        // Check if data is chnaged
                        var minutesDiff = parseFloat(jsonResponse.minutesDiff);
                        if (lastKnownMinutesDiff !== 0.0 && minutesDiff >= lastKnownMinutesDiff) {
                            reject(minutesDiff);
                        } else if (lastKnownHash === jsonResponse.md5) {
                            reject(minutesDiff);
                        } else {
                            // Update hash
                            lastKnownHash = jsonResponse.md5;
                            resolve([minutesDiff, jsonResponse.payload]);
                        }
                    } else {
                        reject(-1.0);
                    }
                } else {
                    reject(-1.0);
                }
            }
        };
        xhttp.send();
    }).then(httpValues => {
        return new Promise(function (parentResolve, partentReject) {
            // Check if gps location is null
            if (gpsLocation === null) {
                // Check if GPS location is enabled
                if (hasGPSLocation) {
                    return new Promise(function (resolve, reject) {
                        navigator.geolocation.getCurrentPosition(function (position) {
                            resolve(position);
                        }, function (err) {
                            reject(err);
                        });
                    }).then(position => {
                        console.log('DEBUG1');

                        /* Check if we already know the location */
                        if (lastKnownGPSLocation === null || (lastKnownGPSLocation !== null && 
                            (lastKnownGPSLocation.centerCoordinate.lng != position.coords.longitude || lastKnownGPSLocation.centerCoordinate.lat != position.coords.latitude))) {

                            // Create placemark
                            return [{
                                id: 'gps',
                                name: activeLanguage.textObject.myLocation,
                                description: '',
                                updateTime: new Date().getTime(),
                                centerCoordinate: {
                                    lng: position.coords.longitude,
                                    lat: position.coords.latitude,
                                    alt: 0
                                }
                            }, false];
                        } else {
                            return [lastKnownGPSLocation, true];
                        }
                    }).catch((err) => {
                        return [null, false];
                    });
                } else {
                    parentResolve([null, false]);
                }
            } else {
                // Create placemark
                parentResolve([{
                    id: 'gps',
                    name: activeLanguage.textObject.myLocation,
                    type: 'unknown',
                    description: '',
                    updateTime: new Date().getTime(),
                    centerCoordinate: {
                        lng: gpsLocation.lng,
                        lat: gpsLocation.lat,
                        alt: 0
                    }
                }, true]);
            }
        }).then(locationValues => {
            console.log('DEBUG');

            // Set values
            lastKnownGPSLocation = locationValues[0];
            var gpsPlacemarkObject = locationValues[0];
            var gpsSame = locationValues[1];

            // When we are using smart update
            var smartUpdate = (placemarkObjects.length > 0 && gpsSame);
            if (smartUpdate) {
                var newPlacemarkObjects = httpValues[1];
                for (var i = 1; i < placemarkObjects.length; i++) { // Skip GPS
                    if (!newPlacemarkObjects[i] || (newPlacemarkObjects[i] && newPlacemarkObjects[i].id !== placemarkObjects[i].id) ) {
                        smartUpdate = false;
                        break;
                    }
                }
            }

            // Create list
            var promises = []
            var selectElement = document.getElementById('select-follow-mode');
            var listElement = document.getElementById('list');

            // Do only when smart update is not active
            if (!smartUpdate) {
                // Remove old values
                promises.push(new Promise(resolve => {
                    while (selectElement.firstChild) {
                        selectElement.removeChild(selectElement.firstChild);
                    }
                    resolve();
                }));

                // Remove old values
                promises.push(new Promise(resolve => {
                    while (listElement.firstChild) {
                        listElement.removeChild(listElement.firstChild);
                    }
                    resolve();
                }));
            }

            // Clear data
            return Promise.all(promises).then(function() {
                return removePlacemarkers().then(function() {
                    // Set response payload
                    var minutesDiff = httpValues[0];
                    placemarkObjects = httpValues[1];

                    // Add GPS placemark when it's not null
                    if (gpsPlacemarkObject !== null) {
                        placemarkObjects.unshift(gpsPlacemarkObject);
                    }

                    // Update layout with GPS location
                    return updateLayout(selectElement, listElement, minutesDiff, smartUpdate);
                });
            }).catch(error => {
                console.log(error);
                return true;
            });
        }).catch(error => {
            console.log(error);
            return true;
        });
    }).catch(error => {
        if (error >= 0) {
            updateDate(error);
        } else {
            console.log(error);
        }
        return true;
    });
}

function initMap() {
    // Define Popup class
    definePopupClass();

    // Create some default values
    var mapLat = 0;
    var mapLng = 0;

    // Check if user has local storage
    if (hasLocalStorage) {
        // Check if active follow storage exists
        var activeFollowStorage = localStorage.getItem('activeFollow');
        if (activeFollowStorage !== null) {
            activeFollow = activeFollowStorage
        }

        // Check if dark mode storage exists
        var darkModeStorage = localStorage.getItem('darkMode');
        if (darkModeStorage !== null) {
            // Set dark mode
            darkMode = (darkModeStorage == 'true');

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

        // Get GPS location
        var gpsLatLocalStorage = localStorage.getItem('gpsLat');
        var gpsLngLocalStorage = localStorage.getItem('gpsLng');
        if (gpsLatLocalStorage !== null && gpsLngLocalStorage !== null) {
            if (gpsLatLocalStorage >= 0 && gpsLngLocalStorage >= 0) {
                gpsLocation = { lat: gpsLatLocalStorage, lng: gpsLngLocalStorage };
            }
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

        // Rebuild placemakers
        removePlacemarkers().then(function() {
            return rebuildPlacemarkers();
        });
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
        isDragging = true;
    });

    // Add listener for drag end
    google.maps.event.addListener(map, 'dragend', function () {
        isDragging = false;
    });

    // Update text label
    var textFollowModeElement = document.getElementById('text-follow-mode');
    textFollowModeElement.innerHTML = activeLanguage.textObject.follow;

    // Update text label
    var labelDarkModeElement = document.getElementById('label-dark-mode');
    labelDarkModeElement.innerHTML = activeLanguage.textObject.darkMode;

    // Load for first time remote data
    loadRemoteData().then(function() {
        stopRequest = false;
    }).catch((err) => {
        stopRequest = false;
        console.log(err);
    });

    // Create interval for retrieving new data
    setInterval(function() {
        if (!stopRequest && !isDragging && !isPlacemarkDragging) {
            loadRemoteData().then(function() {
                stopRequest = false;
            }).catch((err) => {
                stopRequest = false;
                console.log(err);
            });
        } else {
            console.log("Can't retrieve new data");
        }
    }, refreshSeconds);
}

// Set google event listener
google.maps.event.addDomListener(window, 'load', initMap);
