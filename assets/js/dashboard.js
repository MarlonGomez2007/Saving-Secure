var Fila = null;

function onSubmit() {
    let DataForm = Leer();

    
    if (!DataForm.nom || !DataForm.ape || !DataForm.pais || !DataForm.categoria) {
        alert('Todos los campos deben estar completos.');
        return; 
    }

    
    if (esDuplicado(DataForm)) {
        alert('Este gasto ya está registrado en la tabla.');
        return; 
    }

    if (Fila == null) {
        InsertarDatos(DataForm);
        GuardarEnBaseDeDatos(DataForm);
    } else {
        Actualizar(DataForm);
        Vaciar();
    }

   
    RecalcularTotal();
}

function Leer() {
    let DataForm = {};
    DataForm["nom"] = document.getElementById("gasto").value;
    DataForm["ape"] = document.getElementById("descripcion").value;

    
    DataForm["pais"] = Math.floor(formatearMonto(document.getElementById("monto").value));  

    DataForm["categoria"] = document.getElementById("categoria").value;
    let ahora = new Date();
    DataForm["fecha"] = ahora.toISOString().split('T')[0];
    DataForm["hora"] = ahora.toTimeString().split(' ')[0];
    return DataForm;
}

function formatearMonto(monto) {
   
    let numero = parseFloat(monto.replace(/[^0-9,.-]+/g, '').replace(',', '.'));

    
    if (numero % 1 === 0) {
        return numero.toString();
    } else {
        return numero.toFixed(2);
    }
}




function esDuplicado(DataForm) {
    let table = document.getElementById("tabla").getElementsByTagName('tbody')[0];

   
    for (let i = 0; i < table.rows.length; i++) {
        let row = table.rows[i];
        
        
        let nombre = row.cells[0].innerHTML;
        let descripcion = row.cells[1].innerHTML;
        let monto = row.cells[2].innerHTML.replace('COP', '').replace(/,/g, '').trim();
        let categoria = row.cells[5].innerHTML;

       
        if (nombre === DataForm.nom && descripcion === DataForm.ape && monto === DataForm.pais && categoria === DataForm.categoria) {
            return true;
        }
    }

    return false;
}

function InsertarDatos(data) {
    let table = document.getElementById("tabla").getElementsByTagName('tbody')[0];
    let Fila = table.insertRow(table.length);
    Fila.insertCell(0).innerHTML = data.nom;
    Fila.insertCell(1).innerHTML = data.ape;
    Fila.insertCell(2).innerHTML = `${data.pais} COP`; 
    Fila.insertCell(3).innerHTML = data.fecha;
    Fila.insertCell(4).innerHTML = data.hora;
    Fila.insertCell(5).innerHTML = data.categoria;
    Fila.insertCell(6).innerHTML = `
        <input class="submit" type="button" onClick="Editarr(this)" value="Editar">
        <input class="submit" type="button" onClick="Borrarr(this)" value="Borrar">
    `;
    document.getElementById("gasto").focus();
    Vaciar();
    RecalcularTotal();
}

function RecalcularTotal() {
    let total = 0;
    let table = document.getElementById("tabla").getElementsByTagName('tbody')[0];
    for (let i = 0; i < table.rows.length; i++) {
        let montoCelda = table.rows[i].cells[2].innerHTML;
        let monto = parseFloat(montoCelda.replace('COP', '').replace(/,/g, '').trim());
        if (!isNaN(monto)) {
            total += monto;
        }
    }
    document.getElementById("totalMonto").innerText = `${total.toLocaleString('es-CO')} COP`;
}

function Vaciar() {
    document.getElementById("gasto").value = "";
    document.getElementById("descripcion").value = "";
    document.getElementById("monto").value = "";
    document.getElementById("categoria").value = "";
    Fila = null;
}

function Editarr(td) {
    Fila = td.parentElement.parentElement;
    document.getElementById("gasto").value = Fila.cells[0].innerHTML;
    document.getElementById("descripcion").value = Fila.cells[1].innerHTML;
    document.getElementById("monto").value = Fila.cells[2].innerHTML.replace('COP', '').replace(/\./g, '').trim();
    document.getElementById("categoria").value = Fila.cells[5].innerHTML;
}

function Actualizar(DataForm) {
  
    Fila.cells[0].innerHTML = DataForm.nom;
    Fila.cells[1].innerHTML = DataForm.ape;
    Fila.cells[2].innerHTML = `${DataForm.pais} COP`;
    Fila.cells[3].innerHTML = DataForm.fecha;
    Fila.cells[4].innerHTML = DataForm.hora;
    Fila.cells[5].innerHTML = DataForm.categoria;
  
    let montoSinDecimales = parseInt(DataForm.pais);
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "actualizar_gasto.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    let params = `nombre=${encodeURIComponent(DataForm.nom)}&descripcion=${encodeURIComponent(DataForm.ape)}&monto=${encodeURIComponent(montoSinDecimales)}&categoria=${encodeURIComponent(DataForm.categoria)}&fecha=${encodeURIComponent(DataForm.fecha)}&hora=${encodeURIComponent(DataForm.hora)}`;
    xhr.send(params);

    xhr.onload = function() {
        if (xhr.status === 200) {
            if (xhr.responseText === "Actualizado") {
                console.log("Gasto actualizado correctamente.");
                alert("El gasto se ha actualizado correctamente.");
            } else {
                console.error("Error: " + xhr.responseText);
                alert("Hubo un error al actualizar el gasto.");
            }
        } else {
            console.error("Error al realizar la solicitud: " + xhr.status);
            alert("Hubo un problema al hacer la solicitud al servidor.");
        }
    };

    document.getElementById("gasto").focus();
    RecalcularTotal();
}


function Borrarr(td) {
    if (confirm('¿Seguro de borrar este Gasto?')) {
        let row = td.parentElement.parentElement;
        let nombre = row.cells[0].innerHTML;
        let descripcion = row.cells[1].innerHTML;
        let monto = row.cells[2].innerHTML.replace('COP', '').replace(/,/g, '').trim();
        let categoria = row.cells[5].innerHTML;
        
      
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "borrar_gasto.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        let params = `nombre=${encodeURIComponent(nombre)}&descripcion=${encodeURIComponent(descripcion)}&monto=${encodeURIComponent(monto)}&categoria=${encodeURIComponent(categoria)}`;
        xhr.send(params);

        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText === "Borrado") {
                    console.log("Gasto eliminado correctamente.");
                    alert("El gasto ha sido eliminado.");
                    document.getElementById("tabla").deleteRow(row.rowIndex);
                    Vaciar();
                    RecalcularTotal();
                } else {
                    console.error("Error: " + xhr.responseText);
                    alert("Hubo un error al eliminar el gasto.");
                }
            } else {
                console.error("Error al realizar la solicitud: " + xhr.status);
                alert("Hubo un problema al hacer la solicitud al servidor.");
            }
        };
    }
}


function GuardarEnBaseDeDatos(data) {
    
    let montoSinDecimales = parseInt(data.pais);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "guardar_gasto.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    let params = `nombre=${encodeURIComponent(data.nom)}&descripcion=${encodeURIComponent(data.ape)}&monto=${encodeURIComponent(montoSinDecimales)}&categoria=${encodeURIComponent(data.categoria)}&fecha=${encodeURIComponent(data.fecha)}&hora=${encodeURIComponent(data.hora)}`;
    
    xhr.send(params);

    xhr.onload = function() {
        if (xhr.status === 200) {
            if (xhr.responseText === "Duplicado") {
                alert("Este gasto ya está registrado.");
            } else if (xhr.responseText === "Gasto guardado correctamente") {
                console.log("Gasto guardado correctamente en la base de datos.");
                alert("El gasto se ha guardado exitosamente.");
                
            } else {
                console.error("Error: " + xhr.responseText);
                alert("Hubo un error al guardar el gasto.");
            }
        } else {
            console.error("Error al realizar la solicitud: " + xhr.status);
            alert("Hubo un problema al hacer la solicitud al servidor.");
        }
    };
}






