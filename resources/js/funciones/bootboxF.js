function bootboxF(titulo,mensaje){
    var dialog = bootbox.dialog({
        title: titulo,
        message: mensaje,
        size: 'medium',
        locale: 'es',
        centerVertical: true,
    });
}
function bootboxConfirm(titulo,mensaje){
    let resultado;
    bootbox.confirm({
        title: titulo,
        message: mensaje,
        size: 'medium',
        locale: 'es',
        centerVertical: true,
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> Cancelar'
            },
            confirm: {
                label: '<i class="fa fa-check"></i> Confirmar'
            }
        },
        callback: function (result) {
            console.log(result);
            resultado = result;
           
        }
    });
    return resultado;
}
export {bootboxF, bootboxConfirm}