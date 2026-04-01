import { consultando } from './funciones/alerta';
import * as data from './funciones/seteosGlobales.json';
import {formatDate2 } from './funciones/onReady';

import 'ol/ol.css';
import Map from 'ol/Map';
import View from 'ol/View.js';
import GeoJSON from 'ol/format/GeoJSON';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import TileLayer from 'ol/layer/Tile.js';
import OSM from 'ol/source/OSM.js';
import Feature from 'ol/Feature';
import Polygon from 'ol/geom/Polygon';
import { Style, Fill, Stroke } from 'ol/style';
import {Text} from 'ol/style';
var mapaGis = {    

    display: 
        function () { 

            let head = {
              
             // "Content-Type": "application/json",
              
            };
            let dnsGeneral = data.default[0].dnsApi;
            const dns = dnsGeneral + data.default[0].dnsMapaGis;
            const map = new Map({
              layers: [
                new TileLayer({
                  source: new OSM(),
                }),

              ],
              target: 'map',
              view: new View({
                center: [0, 0],
                zoom: 2,
              }),
            });
            $.ajax({
              jsonp: true,
              jsonpCallback: "getJson",
              type: "GET",
              headers: head,
              url: dns,
              crossDomain: true,
              async: true,
              success: function (data) {
                let minConsumo = Number.MAX_VALUE;
                let maxConsumo = Number.MIN_VALUE;
            
                const features = data.map((element) => {
                  const geometry = new Polygon(element.st_asgeojson.coordinates);
                  const feature = new Feature({
                    geometry: geometry,
                  });
            
                  // Actualiza los valores mínimo y máximo de consumo
                  const consumo = parseFloat(element.consumo);
                  if (consumo < minConsumo) {
                    minConsumo = consumo;
                  }
                  if (consumo > maxConsumo) {
                    maxConsumo = consumo;
                  }
            
                  // Calcula el color del relleno basado en el consumo
                  const fillColor = calculateFillColor(consumo, 0, 168);
            
                  // Establece el estilo con el color de relleno calculado
                  const customStyle = new Style({
                    text: new Text({
                      text: element.Lote.toString(),
                      fill: new Fill({
                        color: 'white',
                      }),
                      stroke: new Stroke({
                        color: 'black',
                        width: 2,
                      }),
                    }),
                    fill: new Fill({
                      color: fillColor, // Color del relleno basado en la escala
                    }),
                    stroke: new Stroke({
                      color: 'red',
                      width: 2,
                    }),
                  });
            
                  feature.setStyle(customStyle);
            
                  return feature;
                });
            
                const featureCollection = new VectorSource({
                  features: features,
                });
            
                const vectorLayer = new VectorLayer({
                  source: featureCollection,
                });
            
               // map.addLayer(vectorLayer);
            
                // Asegúrate de que el mapa se ajuste a las características
                map.getView().fit(featureCollection.getExtent());
              }
            });
            
            // Función para mapear un valor a un color en una escala degradada de azul a rojo
            function calculateFillColor(value, min, max) {
              const hue = (1 - (value - min) / (max - min)) * 0.7;
              const color = `hsl(${240 * hue}, 100%, 50%)`; // Escala de azul a rojo
              return color;
            }
            
       

        }//fin funcion display
}//fin objeto
export default mapaGis;