// We're not using bing here. If using bing, please provide your own API key
Cesium.BingMapsApi.defaultKey = null;

function createViewer(target, scriptUrlRoot) {
    //Create the list of available providers we would like the user to select from.
    //This example uses 3, OpenStreetMap, The Black Marble, and a single, non-streaming world image.
    var imageryViewModels = [
        new Cesium.ProviderViewModel({
            name: "OpenStreetMap",
            tooltip: "OpenStreetMap",
            iconUrl: scriptUrlRoot + "/Cesium/Widgets/Images/ImageryProviders/openStreetMap.png",
            creationFunction: function () {
                return Cesium.createOpenStreetMapImageryProvider();
            }
        }),
        new Cesium.ProviderViewModel({
            name: "Stamen Toner",
            tooltip: "Stamen Toner",
            iconUrl: scriptUrlRoot + "/Cesium/Widgets/Images/ImageryProviders/stamenToner.png",
            creationFunction: function () {
                return Cesium.createOpenStreetMapImageryProvider({
                    url: "//stamen-tiles.a.ssl.fastly.net/toner/",
                    credit: "Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under CC BY SA"
                });
            }
        })
    ];

    var viewer = new Cesium.Viewer(target, { imageryProviderViewModels: imageryViewModels });
    return viewer;
}