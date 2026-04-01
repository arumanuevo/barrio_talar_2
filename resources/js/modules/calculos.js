import * as data from '../funciones/seteosGlobales.json';
import {alertaVerde, consultando, mostrarToast, cartelAlerta } from '../funciones/alerta';
import formCalcDesdeHasta from '../funciones/formCalcDesdeHasta.js';
import moment from 'moment';
import {setCantidadLotes, formatDate } from '../funciones/onReady';
import {exportCSVFile} from '../funciones/exportarCSVFile';
import {bootboxF, bootboxConfirm } from '../funciones/bootboxF';

import $ from 'jquery';


const objListaCalculada = {    

    display: 
        function () { 
            console.log('modulo');
            //var tabla = $('#tblMedicionesDesdeHasta').DataTable();
            setCantidadLotes(); 
           
            let fijoxLote = 0;
            var table;
            $(document).on('click', '#confirmButton', function(event) {
                event.preventDefault();
                console.log('confirm');
                let dnsGeneral = data.default[0].dnsApi
                let dnsApiAuth = data.default[0].dnsApiAuth;
                let endPoint = data.default[0].endPoints.calcularDesdeHasta;
                //let urlConsulta = dnsGeneral + dnsApiAuth + endPoint;
                let urlConsulta = dnsApiAuth + endPoint;
                let token = data.default[0].access_token;
                let resultado = formCalcDesdeHasta();
                var tabla;
                var i = 0;
                let difDias;
                consultando(true);
             
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type' : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                       // 'Authorization' : token
                    },
                    type: "GET", 
                    url: urlConsulta,
                    data: resultado, 
                    async: true, 
                    success:function(datos){ 
                        consultando(true);
                        console.log('datos: ',datos);
                        if(datos.listaConsumos == null){
                            $("#btnExcel").attr("disabled", true);
                            $("#btnFacturacion").attr("disabled", true);
                            cartelAlerta('ERROR!', 'No se encontraron registros dentro de las fechas establecidas.');
                            consultando(false);
                            return;
                        }
                        $("#btnExcel").attr("disabled", false);
                        $("#btnFacturacion").attr("disabled", false);
                        //let tabla = $('#tblMedicionesDesdeHasta').DataTable();
                        var isDataTable = $.fn.DataTable.isDataTable('#tblMedicionesDesdeHasta');
                        
                        if (isDataTable){
                            tabla = $('#tblMedicionesDesdeHasta').DataTable();
                        }else{
                            tabla = $('#tblMedicionesDesdeHasta').DataTable({
                                autoWidth: false, // Desactiva el ajuste automático para personalizar mejor el ancho
                                scrollX: true,    // Habilita el desplazamiento horizontal
                                responsive: true, // Hace que la tabla sea adaptable
                                pageLength: 10,
                                deferRender: true,
                                
                                initComplete: function () {
                                    
                                   //$('#tblMedicionesDesdeHasta').removeClass('invisible');
                                }
                            });
                        }
            
                       
                        tabla.clear();
                        let valorxConsumo = 0;
                        let valorxMetro = 0;
                        let totalMetros = 0;
                        let total =  0;
                        let totalConsumosMedidos = 0;
                        let datosCSVSum = [];
                        let fijoVariable = 0;
                        let bocaPozo = $( "#bocapozo" ).val();
                        let fijoProrateado = 0;
                        let cantTotMedicionesComputadas = 0;
                        datos.listaConsumos.forEach(function (element, i){ //calculo la suma d elos consumos para poder luego formar la tabla con el valor por consumo
                            totalMetros += parseFloat(element.consumo);  
                            cantTotMedicionesComputadas = i;
                        });
                       console.log('cantidad de mediciones: ', cantTotMedicionesComputadas);
                        $( "#metrosPeriodo" ).val( totalMetros );
                        //let prorrateo =  $( "#aProrratear" ).val(); no tengo el valor de la factura de aysa

                        try {
                          //valorxMetro = prorrateo / totalMetros;
                            let cantLotes = $( "#cantLotes" ).val();
                            valorxMetro = $( "#valorxMetro" ).val();
                          //fijoVariable =  $( "#fijovariable" ).val();
                            fijoVariable = parseFloat($("#fijovariable").val());
                            //fijoProrateado = fijoVariable / cantTotMedicionesComputadas; //se modifico para que en sanfracisco se puedan meter valores a mano de lotes sin medidor
                            fijoProrateado = fijoVariable / cantLotes;
                        } catch (error) {                           
                        }

                        //////$( "#valorxMetro" ).val( valorxMetro.toFixed(2) );

                        let obj;
                        let datosCSV = [];
                        
                        let ultimoLoteSum = '';
                        let longitud = 0;
                        let urlFoto;

                       
                      
                        let totalConsumos = datos.listaConsumos.length;
                        let porcentaje = 0;
                        var tbody = document.getElementById('tblBody');
                        while (tbody.firstChild) {
                            tbody.removeChild(tbody.firstChild);
                        };
                        datos.listaConsumos.forEach(function (element, i){ 
                            valorxConsumo = (valorxMetro * element.sumario) + fijoProrateado;
                            total = parseFloat(fijoxLote) + parseFloat(valorxConsumo);  
                            totalConsumosMedidos += parseFloat(element.consumo);

                           /* var tr = document.createElement('tr');
                            tr.classList.add('text-center');

                            tr.innerHTML = `
                                <td>${element.lote}</td>
                                <td>${element.medidor}</td>
                                <td>${element.periodo}</td>
                                <td>${element.fecha}</td>
                                <td>${element.vencimiento}</td>
                                <td>${element.tomaant}</td>
                                <td>${element.medidaant}</td>
                                <td>${element.valormedido}</td>
                                <td>${element.consumo}</td>
                                <td>${element.sumario}</td>
                                <td>${valorxConsumo}</td> 
                                <td>${element.inspector}</td>
                                <td>${element.foto}</td>
                            `;

                            tbody.appendChild(tr);*/
                            urlFoto = '<a class = "fotoMedidor" href="' + data.default[0].dnsFotos+ element.foto +'.png" target="_blank">Foto</a>';                       
                                
                            tabla.row.add( [                             
                                element.lote,
                                element.medidor,
                                element.periodo,
                                element.fecha,
                                element.vencimiento,
                                element.tomaant,
                                element.medidaant,
                                element.valormedido,                               
                                element.consumo,
                                element.sumario,
                                valorxConsumo.toFixed(2), //monto periodo
                               // total.toFixed(2),
                                element.inspector,
                               // element.foto,
                                urlFoto,
                               // element.pagado
                            ] ).draw( false );
                            
                            obj = JSON.parse(JSON.stringify(datos.listaConsumos[i]));
                            obj.valorxConsumo = valorxConsumo.toFixed(2);
                            obj.total = total.toFixed(2);
                           
                            if (datosCSVSum.length == 0 ){ //si es el primero lo almaceno
                                datosCSVSum.push(obj);
                            }else{                               
                                longitud = datosCSVSum.length;
                                ultimoLoteSum = datosCSVSum[longitud - 1].lote;
                                if(ultimoLoteSum == element.lote){   //si es igual que el lote anterior reemplazo por el ultimo                               
                                    datosCSVSum.pop();
                                    datosCSVSum.push(obj);
                                }else{
                                    datosCSVSum.push(obj);
                                }
                            }
                            datosCSV.push(obj);
                           
                        }); //fin for each
                         
                        data.default[0].datosCSVSum = datosCSVSum; //global para consumir en guardarfacturacion.js
                        consultando(false);

                       
                        $( "#consumototalmedido" ).val( totalConsumosMedidos );
                        let diferencial = bocaPozo - totalConsumosMedidos;
                        $( "#diferencial" ).val( diferencial );
                        let montoApagar = (totalConsumosMedidos * valorxMetro) + fijoVariable;
                        console.log(montoApagar);
                        $( "#montoAPagar" ).val(montoApagar);
                   
                     },
                     jsonp: true,
                     jsonpCallback: 'getJson',
                }); //fin ajax
                
            }); //fin boton calcular

            //
            function calcularDifDias(dia1,dia2){
                // Ejemplo de fechas en formato de cadena
                var fecha1 = dia1;
                var fecha2 = dia2;

                // Convertir las cadenas a objetos de fecha
                var fechaObj1 = new Date(fecha1);
                var fechaObj2 = new Date(fecha2);

                // Calcular la diferencia en milisegundos
                var diferenciaEnMilisegundos = fechaObj2 - fechaObj1;

                // Calcular la diferencia en días
                var diferenciaEnDias = diferenciaEnMilisegundos / (1000 * 60 * 60 * 24);

                // Redondear la diferencia a un número entero si es necesario
                diferenciaEnDias = Math.round(diferenciaEnDias);
                return diferenciaEnDias;
            };

            
            let headersAdded = false;
            let headersCSV = data.default[0].headExcelCSV;

            $(document).on('click', '#btnExcel', function(event) {
                event.preventDefault()
                let resultado = formCalcDesdeHasta();
                //let headersCSV = data.default[0].headExcelCSV; //El modelo de seteo local de esta variable podria ser sacandole la doble comilla al nombre la columna
                console.log("head: ", headersCSV);
                let diaDesde = formatDate(resultado.diaDesde,'es');
                let diaHasta = formatDate(resultado.diaHasta,'es');
                let nombreArchivo = diaDesde+"_"+diaHasta; 
                if (!headersAdded) {
                    data.default[0].datosCSVSum.unshift(headersCSV);
                    headersAdded = true;  // Marcar que los encabezados se han agregado
                }   
                exportCSVFile(headersCSV, data.default[0].datosCSVSum, nombreArchivo);              
            });

            $(document).on('change', '#totalPesos', function() { //se necesita poner el valor de la boleta
                /*let totalPesos = $( "#totalPesos" ).val();
                let porcentaje = $( "#valorFijo" ).val();
                let cantLotes = $( "#cantLotes" ).val();
                let fijoPesos = totalPesos * porcentaje / 100;
                $( "#fijoPesos" ).val( fijoPesos );
                fijoxLote = (fijoPesos / cantLotes).toFixed(2);
                $( "#fijoxLote" ).val( fijoxLote );
                let prorratear =  totalPesos - fijoPesos;
                $( "#aProrratear" ).val( prorratear.toFixed(2) );   */           
            });

            $(document).on('change', '#selectorSecciones', function() {
              
                let idSeccion = $(this).val();
                setCantidadLotes(idSeccion);

            });

            $(document).on('click', '.fotoMedidor', function (event) {
               $.magnificPopup.open({
                    closeOnContentClick: true,
                    closeBtnInside: true,
                    mainClass: 'mfp-with-zoom mfp-img-mobile',
                    image: {
                        tError: '<a>La imagen</a> no pudo ser cargada.'
                      },
                    items: {
                        src: $(this).attr('href')
                    },
                    type: 'image',
              });             
               event.preventDefault();            
            });
          
         
            

            $(document).on('click', '#confirmButtonRRRR', function(event) {
                event.preventDefault()
                let dnsGeneral = data.default[0].dnsApi
                let dnsApiAuth = data.default[0].dnsApiAuth;
               
                let endPoint = data.default[0].endPoints.calcularDesdeHasta;
                
                //let urlConsulta = dnsGeneral + dnsApiAuth + endPoint;
                let urlConsulta = dnsApiAuth + endPoint;
                let token = data.default[0].access_token;
                let resultado = formCalcDesdeHasta();
                var tabla;
                var i = 0;
         
                consultando(true);
              
              
                if (resultado == 'ERROR'){consultando(false); console.log('kkkk'); return };
               
              
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type' : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                       // 'Authorization' : token
                    },
                    type: "GET", 
                    url: urlConsulta,
                    data: resultado, 
                    async: true, 
                    success:function(datos){ 
                     
                        if(datos.listaConsumos == null){
                            $("#btnExcel").attr("disabled", true);
                            $("#btnFacturacion").attr("disabled", true);
                            cartelAlerta('ERROR!', 'No se encontraron registros dentro de las fechas establecidas.');
                            consultando(false);
                            return;
                        }
                        $("#btnExcel").attr("disabled", false);
                        $("#btnFacturacion").attr("disabled", false);
                        tabla = $('#tblMedicionesDesdeHasta').DataTable();
                        tabla.clear();
                        let valorxConsumo = 0;
                        let valorxMetro = 0;
                        let totalMetros = 0;
                        let total =  0;
                        let datosCSVSum = [];
                        console.log('datos: ',datos);
                        
                        datos.listaConsumos.forEach(function (element, i){ //calculo la suma d elos consumos para poder luego formar la tabla con el valor por consumo
                            totalMetros += parseFloat(element.consumo);  
                           
                        });
                       // totalMetros = 6928;
                        $( "#metrosPeriodo" ).val( totalMetros );
                        let prorrateo =  $( "#aProrratear" ).val();

                        try {
                         // valorxMetro = prorrateo / totalMetros;
                        } catch (error) {                           
                        }

                        //$( "#valorxMetro" ).val( valorxMetro.toFixed(2) );
                        
                        let obj;
                        let datosCSV = [];
                        
                        let ultimoLoteSum = '';
                        let longitud = 0;
                        let urlFoto;

                       
                      //  $(".progress-bar").css("width", 50 + "%").text(50 + "%")
                        let totalConsumos = datos.listaConsumos.length;
                        let porcentaje = 0;
                     
                        datos.listaConsumos.forEach(function (element, i){ 
                            valorxConsumo = valorxMetro * element.sumario;
                            console.log('valor por consumo: ', valorxConsumo);
                            total = parseFloat(fijoxLote) + parseFloat(valorxConsumo);  
                          
                          
                         
                          /*  urlFoto = '<div class="parent-container2">\
                                <a class = "fotoMedidor" href="' + data.default[0].dnsFotos+ element.foto +'.png">Foto</a></div>';   */ 
                            urlFoto = '<a class = "fotoMedidor" href="' + data.default[0].dnsFotos+ element.foto +'.png">Foto</a>';   

                    

                            tabla.row.add( [                             
                                element.lote,
                                element.medidor,
                                element.periodo,
                                element.fecha,
                                element.vencimiento,
                                element.tomaant,
                                element.medidaant,
                                element.valormedido,                               
                                element.consumo,
                                element.sumario,
                                valorxConsumo.toFixed(2),
                               // total.toFixed(2),
                                element.inspector,
                               // element.foto,
                                urlFoto,
                               // element.pagado
                            ] ).draw( false );
                            
                            obj = JSON.parse(JSON.stringify(datos.listaConsumos[i]));
                            obj.valorxConsumo = valorxConsumo.toFixed(2);
                            obj.total = total.toFixed(2);
                           
                            if (datosCSVSum.length == 0 ){ //si es el primero lo almaceno
                                datosCSVSum.push(obj);
                            }else{                               
                                longitud = datosCSVSum.length;
                                ultimoLoteSum = datosCSVSum[longitud - 1].lote;
                                if(ultimoLoteSum == element.lote){   //si es igual que el lote anterior reemplazo por el ultimo                               
                                    datosCSVSum.pop();
                                    datosCSVSum.push(obj);
                                }else{
                                    datosCSVSum.push(obj);
                                }
                            }
                            datosCSV.push(obj);
                           
                          }); //fin for each
                         
                          data.default[0].datosCSVSum = datosCSVSum; //global para consumir en guardarfacturacion.js
                          consultando(false);

                          
                   
                     },
                     jsonp: true,
                     jsonpCallback: 'getJson',
                }); //fin ajax
                console.log('termino la consulta');
               
               // consultando(false);
                
            });

            /*$(function() {
                
                const FROM_PATTERN = 'YYYY-MM-DD';
                const TO_PATTERN   = 'DD/MM/YYYY';
                 $('#tblMedicionesDesdeHasta').DataTable( {
                    "scrollX": true,   
                    columnDefs:[{targets:[3,4,5], render:function(data){
                        return moment(data).format(TO_PATTERN);
                    }}],
                    "pagingType": "numbers",  
                    dom: 'Plfrtip',
                    language: {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
                        searchPanes: {
                            emptyPanes: 'There are no panes to display. :/'
                        }
                    },    
                   
                } );

           
            });*/

        }//fin funcion display
}//fin objeto
export default objListaCalculada;