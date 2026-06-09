(function(){

    //create map in leaflet and tie it to the div called 'theMap'
    var map = L.map('theMap').setView([44.650627, -63.597140], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

    // L.marker([44.650690, -63.596537]).addTo(map)
    //     .bindPopup('This is a sample popup. You can put any html structure in this including extra bus data. You can also swap this icon out for a custom icon. A png file has been provided for you to use if you wish.')
    //     .openPopup();
    
        
        function createCustomIcon (feature, latlng) {
            console.log(feature)
            // let newBusIcon = L.icon({
            //     iconUrl: 'bus2.png',
            //     iconSize:     [100, 102], // size of the icon
            //     iconAnchor:   [16, 37], // point of the icon which will correspond to marker's location
            //     popupAnchor:  [30, -40] // point from which the popup should open relative to the iconAnchor
            // });
            return L.marker(latlng, {
                icon: newBusIcon,
                // rotationAngle: 45,
                rotationOrigin: 'center center',
                rotationAngle: feature.properties.direction 
                // rotationAngle: feature.properties.direction 
            })
            .bindPopup('\rRoute #: ' + feature.properties.routeID
                                     + "<br>Vehicle #: " + feature.properties.vehicle_num 
                                     + "<br>Velocity: " 
                                     + (feature.properties.speed_Kph).toFixed(1) + " Km/h")                    
            .openPopup();
        }
        let myLayerOptions = {
            pointToLayer: createCustomIcon,
        }
 
 
            let newBusIcon = L.icon({
                // iconUrl: 'bus2.png',
                iconUrl: 'busNouveau.svg',   // Created a SVG of the bus to avoid scaling issues with map zoom, appears a tad slower at times but it's not a vector!!! 
                // iconSize:     [50, 51], // size of the icon
                iconSize:     [50, 51], // size of the icon
                iconAnchor:   [35, 25], // point of the icon which will correspond to marker's location
                popupAnchor:  [00, -30] // point from which the popup should open relative to the iconAnchor
            });



        // L.marker([44.638810, -63.594977], {
        //     icon:newBusIcon,
        //     rotationAngle: 70
        // }).addTo(map).bindPopup('Bringo!');

        

        // .bindPopup('This is a sample popup. You can put any html structure in this including extra bus data. You can also swap this icon out for a custom icon. A png file has been provided for you to use if you wish.')
        // .openPopup();

        // const busData = "https://hrmbusapi.herokuapp.com/";
        // routes1toTen = 

        // Plot Markers on the map using the converted GeoJSON data
        // Once you have your newly transformed data in GeoJSON format. Apply this data to the provided map using the programming API for GeoJSON in Leaflet.
        //==================================================================================================
      
        // const layer = L.geoJSON   
        // myLayer = L.geoJSON().addTo(map); //empty geoJSON layer

        //==================================================================================================
        // Need to get the proper icon rotation going here!!!
        //==================================================================================================
        // let myLayer = L.geoJSON(null, myLayerOptions).addTo(map); //empty geoJSON layer
        

        let myLayer = L.geoJSON(null, myLayerOptions, {
            // rotationAngle: 45,
            // rotationAngle: 0,
            rotationOrigin: 0
        }).addTo(map); //empty geoJSON layer



        function dejaWho() {

            fetch('https://hrmbusapi.herokuapp.com/')
            .then(function(response){
                return response.json();
            })
            .then(function(json){
                // console.log(`HRM Busses: ${getBusses(json)}`);
                myLayer.clearLayers();

                getBusses(json);
                // console.log("This should be the Bus json")
                // console.log(json);  // This drills into the JSON object ==> target the bus routes from #1 to #10 !!!
                setTimeout(dejaWho, 7000);
            })       
          
        }
        
        dejaWho()
           


        function getBusses(json) { // <- you may or may not need to define parameters for your function
            const getRoutes = json.entity.filter(function(busIn){
                return busIn.vehicle.trip.routeId == "1" || busIn.vehicle.trip.routeId == "2" || busIn.vehicle.trip.routeId == "3" || busIn.vehicle.trip.routeId == "4" || busIn.vehicle.trip.routeId == "5" || busIn.vehicle.trip.routeId == "6" || busIn.vehicle.trip.routeId == "7" || busIn.vehicle.trip.routeId == "8" || busIn.vehicle.trip.routeId == "9" || busIn.vehicle.trip.routeId == "10";
            })


                


            
            const data = getRoutes.map(function(busIn){
                // return {"RouteId":busIn.vehicle.trip.routeId,
                return {
                            "type": "Feature",
                            "geometry": {
                                "type": "Point",
                                "coordinates": [busIn.vehicle.position.longitude, busIn.vehicle.position.latitude]
                            },
                            "properties": {
                                "routeID": busIn.vehicle.trip.routeId,
                                "vehicle_num": busIn.vehicle.vehicle.label,
                                "speed_Kph": (busIn.vehicle.position.speed === undefined) ? 0 : (busIn.vehicle.position.speed * 1.6),
                                "direction": busIn.vehicle.position.bearing
                            }
                        }  
                                         
            })
            console.log("This should be the Bus bearing in degrees: ")
            // console.log(busDirection);

            const featureCollection = {
                type: "featureCollection",
                features: data
            }

            myLayer.addData(featureCollection)
            // console.log("This is the valid geoJson object: ")
            // console.log('--------------------------------');
            // console.log(data);
            // console.log('--------------------------------');   
        }
        
})()