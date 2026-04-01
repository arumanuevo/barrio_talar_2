
import { consultando } from './funciones/alerta';
import * as data from './funciones/seteosGlobales.json';
import {formatDate2 } from './funciones/onReady';
var graficoConsumosXLotes = {    

    display: 
        function () { 
            let glbY = [];
            let glbX = [];
            function getConsumosXLote(){
                let dnsGeneral = data.default[0].dnsApi;
                let dnsApiAuth = data.default[0].dnsApiAuth;
                let endPoint = data.default[0].endPoints.getFacturasGraf; //la api necesita que se le envie el lote           
                let urlConsulta = dnsGeneral + dnsApiAuth + endPoint;   
                let token = data.default[0].access_token;  
                let lote = user.lote;  
                let seccion = user.seccion;
                  
                consultando(true);
               
                $.ajax({ 
                    jsonp: true,
                    jsonpCallback: 'getJson',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type' : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                       // 'Authorization' : token*/
                    },
                    data : {"seccion":seccion,"lote":lote},
                    url: urlConsulta,
                    crossDomain:true,
                    async: false,                        
                    success: function(data) {  
                       // console.log(data);
                      let zona = 'es';
                      let diaDesde;
                      let diaHasta;
                      consultando(false);
                      console.log(data);
                      data.forEach(function (element, i){
                       
                        glbY.push(element.sumario);
                       
                        diaDesde = formatDate2(element.fdesde, zona);
                        diaHasta = formatDate2(element.fhasta, zona);
                        glbX.push(diaDesde + ' » ' + diaHasta);
                      });
                    } //fin sucess
            
                  }); //fin ajax
            }//fin funcion getconsumosxlote

            $(function() {
                const ctx = document.getElementById('myChart');
                getConsumosXLote();
                
                const myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: glbX,
                        datasets: [{
                            label: 'Consumo del Periodo',
                            data: glbY,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                }); //fin del chart
            }); //fin funcion jquery

        }//fin funcion display
}//fin objeto
export default graficoConsumosXLotes;



