$(document).ready(function () {
    $.get(url+"users", function(data) {
        let html = "<option value=''>Selecciona un usuario</option>";
        for (var i = 0; i < data.length; i++) {
            html += "<option value="+data[i].usu_id+">"+data[i].usu_nombres+"</option>";
        }
        $("#user").empty().html(html);
    },"json");

    $.get(url+"provinces&deparment=22", function(data) {
        let html = "<option value=''>Selecciona una provincia</option>";
        for (var i = 0; i < data.length; i++) {
            html += "<option value="+data[i].pro_id+">"+data[i].pro_nombre+"</option>";
        }
        $("#province").empty().html(html);
    },"json");

    list(); 
});

function list(){
    if($("#type").val() == 1){
        $.get(url+"agricultural_census&user="+$("#user").val()+"&state="+$("#state").val()+"&province="+$("#province").val()+"&district="+$("#district").val()+"&from="+$("#from").val()+"&to="+$("#to").val(), function(data) {
            $("#total").text(data.length); maps(data);
        },"json");
    }else{
        $.get(url+"crop_monitoring&user="+$("#user").val()+"&state="+$("#state").val()+"&province="+$("#province").val()+"&district="+$("#district").val()+"&from="+$("#from").val()+"&to="+$("#to").val(), function(data) {
            $("#total").text(data.length); maps(data);
        },"json");
    }
}

function provinces(){
    if($("#province").val() != ""){
        $.get(url+"districts&province="+$("#province").val(), function(data) {
            let html = "<option value=''>Selecciona un distrito</option>";
            for (var i = 0; i < data.length; i++) {
                html += "<option value="+data[i].dis_id+">"+data[i].dis_nombre+"</option>";
            }
            $("#district").empty().html(html);
        },"json");
    }
    list();
}

function dates(){
    list();
}

function maps(points = []){
    let layer = "https://portal.regionsanmartin.gob.pe/server/rest/services/DRASAM/%C3%81mbito_Ma%C3%ADz/MapServer/4";
    if($("#zoom").val() == 8){
        layer = "https://portal.regionsanmartin.gob.pe/server/rest/services/DRASAM/proyecto_visor/MapServer/3";
    }
    if($("#zoom").val() == 11){
        layer = "https://portal.regionsanmartin.gob.pe/server/rest/services/DRASAM/proyecto_visor/MapServer/4";
    }
    require([
        "esri/config", 
        "esri/Map", 
        "esri/views/MapView", 
        "esri/layers/FeatureLayer",

        "esri/Graphic",
        "esri/layers/GraphicsLayer",
        "esri/widgets/Home"
    ], function(esriConfig, Map, MapView, FeatureLayer, Graphic, GraphicsLayer, Home) {
        esriConfig.apiKey = "AAPKa8da52544c14476c8d3ff27ed1827498UsCounCL4Xx4R6nBo67xs17w3qd3_vZO";

        const map = new Map({
            basemap: "satellite"
        });

        const view = new MapView({
            map: map,
            center: [-76.92070074385694, -6.36497151510392],
            zoom: $("#zoom").val(),
            container: "viewDiv"
        });

        const trailheadsLayer = new FeatureLayer({
            //url: "https://portal.regionsanmartin.gob.pe/server/rest/services/DRASAM/%C3%81mbito_Ma%C3%ADz/MapServer/5"
            //url: "https://portal.regionsanmartin.gob.pe/server/rest/services/DRASAM/proyecto_visor/MapServer/3"
            url: layer
            //url: "http://geoportal.cofopri.gob.pe/cofopri/rest/services/Tematicos/LIMITES_NACIONALES_IGN/MapServer/0",
            //definitionExpression: "IDDPTO = '22'",
            /* title: "Provincia",
            popupTemplate: {
                title: "Provincia {PROVINCIA}",
                content:[{
                    type: "fields",
                    fieldInfos:[
                        {fieldName: "IDDPTO", label: "Ubigeo departamental"},
                        {fieldName: "PROVINCIA", label: "Provincia"}
                    ]
                }]
            } */
        });
        map.add(trailheadsLayer);
        
        let graphicsLayer = new GraphicsLayer();
        map.add(graphicsLayer);

        for (var i = 0; i < points.length; i++) {
            let color = [13, 110, 253];
            if(parseInt(points[i].estado) == 0){
                color = [220, 53, 69];
            }else if(parseInt(points[i].estado) == 1){
                color = [255, 193, 7];
            }
            
            let point = {
                type: "point",
                longitude: points[i].longitud,
                latitude: points[i].latitud
            };
            let simpleMarkerSymbol = {
                type: "simple-marker",
                color: color,
                outline: {
                    color: [255, 255, 255], 
                    width: 1
                }
            };
            let pointGraphic = new Graphic({
                geometry: point,
                symbol: simpleMarkerSymbol,
                popupTemplate: {
                    title: "Monitoreo de cultivo",
                    content:[
                        {
                            type: "fields",
                            fieldInfos:[
                                {fieldName: "name", label: "Nombres"},
                                {fieldName: "date", label: "Creado"},
                                {fieldName: "syncronize", label: "Sincronizado"},
                                {fieldName: "user", label: "Técnico"},
                                {fieldName: "province", label: "Provincia"},
                                {fieldName: "district", label: "Distrito"},
                                {fieldName: "location", label: "Localidad"},
                                {fieldName: "natural", label: "Natural de"},
                                {fieldName: "point", label: "Lat / Lng"}
                            ]
                        },
                        {
                            type: "media",
                            mediaInfos: [{
                                title: "<b>Foto</b>",
                                type: "image",
                                value: {
                                    sourceURL: "https://oat.pe/agroat/api/images/{photo}"
                                }
                            }]
                        }
                    ]
                }
            });
            pointGraphic.attributes = {
                "name": points[i].nombres,
                "date": points[i].fecha,
                "syncronize": points[i].sincronizado,
                "province": points[i].provincia,
                "district": points[i].distrito,
                "location": points[i].localidad+" / "+points[i].sector,
                "natural": points[i].natural,
                "point": points[i].latitud+" / "+points[i].longitud,
                "photo": points[i].foto,
                "user": points[i].tecnico
            };
            graphicsLayer.add(pointGraphic);
        }

        var btnHome = new Home({
            view: view
        });
        view.ui.add(btnHome, "top-left");
    });
}