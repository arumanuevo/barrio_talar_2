import './bootstrap';
import objListaCalculada from './modules/calculos';
import guardarFacturacion from './modules/guardarFacturacion';
import objMedidor from './modules/medidor';
import objCamara from './modules/camara';
import { calcularFechaSumandoPeriodo } from './funciones/onReady';
import objListaMediciones from './modules/listaMediciones';
import objGuardarFacturacion from './modules/guardarFacturacion';

window.displayCalculos = objListaCalculada.display;
window.displayGuardarFacturacion = guardarFacturacion.display;
window.objMedidor = objMedidor.display;
window.objCamara = objCamara.display;
window.calcularFechaSumandoPeriodo = calcularFechaSumandoPeriodo;
window.objListaMediciones = objListaMediciones.display;
window.objGuardarFacturacion = objGuardarFacturacion.display;
