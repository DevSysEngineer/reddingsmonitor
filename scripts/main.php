<?php

namespace Reddingsmonitor\Scripts;

try {
    /* Require load */
    require_once 'load.php';

    /* Set header */
    header('Content-Type: text/javascript');
} catch (\Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
?>
var map = null;
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

function initMap() {
    // Setup map
    map = new google.maps.Map(document.getElementById('map'), {
        styles: []
    });

    // Setup layer
    var kmlLayer = new google.maps.KmlLayer({
        url: '<?= $config->createNetLinkURL(); ?>',
        map: map
    });

    // Add event listener for layer
    google.maps.event.addListener(kmlLayer, 'metadata_changed', function () {
        // Get current date
        var date = new Date();
        var n = date.toDateString();
        var time = date.toLocaleTimeString();

        // Set date information
        document.getElementById('lastupdate').innerHTML = 'Laatst bijgewekt: ' + n + ' ' + time;
    });
}

function initData() {
    // Set interval for retrieving data from server
    setInterval(function() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.log('data');
            }
        };
        xhttp.open('GET', '<?= $config->createScriptURL($token, "list"); ?>', true);
        xhttp.send();
    }, 10000);
}

// Set function for onload
window.onload = function() {
    initData();
};

// Set google event listener
google.maps.event.addDomListener(window, 'load', initMap);
