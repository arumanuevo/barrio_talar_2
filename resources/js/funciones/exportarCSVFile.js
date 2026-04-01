    /**
 *
 * @param objArray
 */
/**
 * Convierte el array de objetos a una cadena CSV.
 * @param {Array} objArray - Array de objetos.
 * @returns {string} - Cadena CSV.
 */
 function convertToCSV(objArray) {
    const array = typeof objArray !== "object" ? JSON.parse(objArray) : objArray;
    let str = "";
    for (let i = 0; i < array.length; i++) {
        let line = "";
        for (let index in array[i]) {
            if (line !== "") line += ";";
            line += array[i][index];
        }
        str += line + "\r\n";
    }
    return str;
}

/**
 * Exporta un archivo CSV con los encabezados y los items proporcionados.
 * @param {Array} headers - Encabezados.
 * @param {Array} items - Datos.
 * @param {string} fileName - Nombre del archivo.
 */
function exportCSVFile(headers, items, fileName) {
    // Copiar el array para evitar modificaciones no deseadas.
    const itemsCopy = [...items];

    // Agregar encabezados solo si no se han agregado antes.
    if (!itemsCopy.some(item => JSON.stringify(item) === JSON.stringify(headers))) {
        itemsCopy.unshift(headers);
    }

    const jsonObject = JSON.stringify(itemsCopy);
    const csv = convertToCSV(jsonObject);
    const exportName = fileName + ".csv" || "export.csv";
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });

    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, exportName);
    } else {
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", exportName);
            link.style.visibility = "hidden";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}

export { exportCSVFile };

