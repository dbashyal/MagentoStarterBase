/**
 * Technooze_Stores extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Technooze
 * @package    Technooze_Stores
 * @copyright  Copyright (c) 2008 Technooze LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Technooze
 * @package    Technooze_Stores
 * @author     Technooze <info@technooze.com>
 */
function TechnoozeStores(config) {
    var map;
    var geocoder;

    function downloadUrl(url, callback) {
        var request = window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;

        request.onreadystatechange = function() {
            if (request.readyState == 4) {
                request.onreadystatechange = doNothing;
                callback(request.responseText, request.status);
            }
        };

        request.open('GET', url, true);
        request.send(null);
    }

    function searchLocationsNear(center, params) {
        var searchUrl = config.searchUrl+'?lat='+center.lat()+'&lng='+center.lng()+'&'+Object.toQueryString(params);
        downloadUrl(searchUrl, function(data) {
            // if it is set to redirect to first result then evaluate script returned
            if (config.redirect && data.match(/window\.top\.location\.href/g)) {
                eval(data);
            } else {
                // else update result container
                var sidebar = config.sidebarEl;
                sidebar.innerHTML = data;
            }

            var bounds = new google.maps.LatLngBounds();
        });
    }

    function escapeUserText(text) {
        if (text === undefined) {
            return null;
        }
        text = text.replace(/@/, "@@");
        text = text.replace(/\\/, "@\\");
        text = text.replace(/'/, "@'");
        text = text.replace(/\[/, "@[");
        text = text.replace(/\]/, "@]");
        return encodeURIComponent(text);
    }

    function doNothing() {}

    return {
        load: function () {
            //geocoder = new GClientGeocoder(); //google maps v2
            geocoder = new google.maps.Geocoder();
        },
        search: function(address, params) {
            geocoder.geocode({address: address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
                    searchLocationsNear(results[0].geometry.location, params);
                } else {
                    alert('The address is not valid: '+address);
				}
            });
        }
    };
}
