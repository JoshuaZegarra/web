// app.js - Modificado para cargar todos los filtros desde obtener_establecimientos.php
// Variables para controlar el ordenamiento
let columnaActual = '';
let ordenActual = 'asc';
let datosOriginales = [];

// Cargar todos los datos al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarFiltrosYEstablecimientos();
    cargarIndicadores();
    cargarDatosGraficos();
    cargarListaNiños();
});

// Función para cargar filtros y establecimientos juntos
function cargarFiltrosYEstablecimientos() {
    console.log('Cargando filtros y establecimientos...');
    
    fetch('obtener_establecimientos.php')
        .then(response => {
            console.log('Respuesta recibida:', response);
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            // Verificar si hay error
            if (data.error) {
                console.error('Error en datos:', data.error);
                return;
            }
            
            // Cargar establecimientos
            const selectEst = document.getElementById('establecimiento');
            if (selectEst) {
                // Limpiar opciones existentes
                selectEst.innerHTML = '<option value="">Todos</option>';
                
                // Agregar establecimientos
                if (data.establecimientos && data.establecimientos.length > 0) {
                    data.establecimientos.forEach(est => {
                        const option = document.createElement('option');
                        option.value = est.id || est.nombre;
                        option.textContent = est.nombre || 'Sin nombre';
                        selectEst.appendChild(option);
                    });
                    console.log(`✅ ${data.establecimientos.length} establecimientos cargados`);
                } else {
                    console.warn('⚠️ No hay establecimientos disponibles');
                }
            } else {
                console.error('❌ No se encontró el elemento select#establecimiento');
            }
            
            // Cargar años
            const selectAño = document.querySelector('select[data-filter="año"]');
            if (selectAño && data.años) {
                selectAño.innerHTML = '<option value="">Todos</option>';
                data.años.forEach(año => {
                    const option = document.createElement('option');
                    option.value = año;
                    option.textContent = año;
                    selectAño.appendChild(option);
                });
            }
            
            // Cargar meses
            const selectMes = document.querySelector('select[data-filter="mes"]');
            if (selectMes && data.meses) {
                selectMes.innerHTML = '<option value="">Todos</option>';
                const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                
                data.meses.forEach(mes => {
                    const option = document.createElement('option');
                    option.value = mes;
                    option.textContent = nombresMeses[mes - 1] || 'Mes ' + mes;
                    selectMes.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar filtros:', error);
        });
}

// Función para cargar la lista de niños con todos los indicadores
function cargarListaNiños() {
    // Resetear variables de ordenamiento
    columnaActual = '';
    ordenActual = 'asc';
    datosOriginales = [];
    
    // Quitar clases de ordenamiento
    document.querySelectorAll('#tablaNiños th.ordenable').forEach(th => {
        th.classList.remove('orden-asc', 'orden-desc');
    });
    
    const establecimiento = document.getElementById('establecimiento')?.value || '';
    const mes = document.querySelector('select[data-filter="mes"]')?.value || '';
    const año = document.querySelector('select[data-filter="año"]')?.value || '';
    
    let url = 'obtener_lista_niños.php?';
    const params = [];
    
    if (establecimiento && establecimiento !== 'todos' && establecimiento !== '') {
        params.push('establecimiento=' + encodeURIComponent(establecimiento));
    }
    if (mes && mes !== 'todos' && mes !== '') {
        params.push('mes=' + encodeURIComponent(mes));
    }
    if (año && año !== 'todos' && año !== '') {
        params.push('año=' + encodeURIComponent(año));
    }
    
    url += params.join('&');
    
    console.log('Cargando lista de niños desde:', url);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Datos recibidos:', data);
            
            const tbody = document.getElementById('listaNiñosBody');
            
            if (data.error || !data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="23" style="text-align: center;">No hay datos disponibles</td></tr>';
                return;
            }
            
            let html = '';
            data.forEach((niño, index) => {
                html += '<tr>';
                html += `<td>${index + 1}</td>`;
                html += `<td>${niño.documento || ''}</td>`;
                html += `<td>${niño.paciente || 'S/N'}</td>`;
                html += `<td>${niño.establecimiento || ''}</td>`;
                html += `<td>${niño.fecha_nac || ''}</td>`;
                html += `<td>${niño.edad || 'Recién nacido'}</td>`;
                
                // Vacunas RN (individuales)
                html += `<td class="indicadores-grupo">
                    <div class="grupo-vacunas">
                        <div class="indicador-circulo ${niño.bcg == 1 ? 'cumple' : 'no-cumple'}" title="BCG"></div>
                        <div class="indicador-circulo ${niño.hvb == 1 ? 'cumple' : 'no-cumple'}" title="HVB"></div>
                    </div>
                </td>`;
                
                // CRED RN
                html += `<td class="indicadores-grupo">
                    <div class="grupo-cred">
                        <div class="indicador-circulo ${niño.rn1 == 1 ? 'cumple' : 'no-cumple'}" title="CRED RN 1"></div>
                        <div class="indicador-circulo ${niño.rn2 == 1 ? 'cumple' : 'no-cumple'}" title="CRED RN 2"></div>
                        <div class="indicador-circulo ${niño.rn3 == 1 ? 'cumple' : 'no-cumple'}" title="CRED RN 3"></div>
                    </div>
                </td>`;
                
                // Tamizaje
                html += `<td class="indicadores-grupo">
                    <div class="grupo-tamizaje">
                        <div class="indicador-circulo ${niño.tamizaje == 1 ? 'cumple' : 'no-cumple'}" title="Tamizaje Neonatal"></div>
                    </div>
                </td>`;
                
                // CRED Mensual
                html += `<td class="indicadores-grupo">
                    <div class="grupo-cred-mensual">
                        <div class="fila-cred">
                            <div class="indicador-circulo ${niño.cred1 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 1"></div>
                            <div class="indicador-circulo ${niño.cred2 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 2"></div>
                            <div class="indicador-circulo ${niño.cred3 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 3"></div>
                            <div class="indicador-circulo ${niño.cred4 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 4"></div>
                        </div>
                        <div class="fila-cred">
                            <div class="indicador-circulo ${niño.cred5 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 5"></div>
                            <div class="indicador-circulo ${niño.cred6 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 6"></div>
                            <div class="indicador-circulo ${niño.cred7 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 7"></div>
                        </div>
                    </div>
                </td>`;
                
                // Vacuna 1 Dosis
                html += `<td class="indicadores-grupo">
                    <div class="grupo-vacunas1d">
                        <div class="fila-vacuna">
                            <div class="indicador-circulo ${niño.neumococo1 == 1 ? 'cumple' : 'no-cumple'}" title="Neumococo 1D"></div>
                            <div class="indicador-circulo ${niño.rotavirus1 == 1 ? 'cumple' : 'no-cumple'}" title="Rotavirus 1D"></div>
                        </div>
                        <div class="fila-vacuna">
                            <div class="indicador-circulo ${niño.polio1 == 1 ? 'cumple' : 'no-cumple'}" title="Polio 1D"></div>
                            <div class="indicador-circulo ${niño.pentavalente1 == 1 ? 'cumple' : 'no-cumple'}" title="Pentavalente 1D"></div>
                        </div>
                    </div>
                </td>`;
                
                // Vacuna 2 Dosis
                html += `<td class="indicadores-grupo">
                    <div class="grupo-vacunas2d">
                        <div class="fila-vacuna">
                            <div class="indicador-circulo ${niño.neumococo2 == 1 ? 'cumple' : 'no-cumple'}" title="Neumococo 2D"></div>
                            <div class="indicador-circulo ${niño.rotavirus2 == 1 ? 'cumple' : 'no-cumple'}" title="Rotavirus 2D"></div>
                        </div>
                        <div class="fila-vacuna">
                            <div class="indicador-circulo ${niño.polio2 == 1 ? 'cumple' : 'no-cumple'}" title="Polio 2D"></div>
                            <div class="indicador-circulo ${niño.pentavalente2 == 1 ? 'cumple' : 'no-cumple'}" title="Pentavalente 2D"></div>
                        </div>
                    </div>
                </td>`;
                
                // Vacuna 3 Dosis
                html += `<td class="indicadores-grupo">
                    <div class="grupo-vacunas3d">
                        <div class="indicador-circulo ${niño.polio3 == 1 ? 'cumple' : 'no-cumple'}" title="Polio 3D"></div>
                        <div class="indicador-circulo ${niño.pentavalente3 == 1 ? 'cumple' : 'no-cumple'}" title="Pentavalente 3D"></div>
                    </div>
                </td>`;
                
                html += '</tr>';
            });
            
            tbody.innerHTML = html;
            
            // Resetear datos originales después de cargar nuevos datos
            datosOriginales = [];
        })
        .catch(error => {
            console.error('Error al cargar lista de niños:', error);
            document.getElementById('listaNiñosBody').innerHTML = 
                '<tr><td colspan="23" style="text-align: center; color: red;">Error al cargar datos</td></tr>';
        });
}

// Función para ordenar la tabla
function ordenarTabla(columna) {
    const ths = document.querySelectorAll('#tablaNiños th.ordenable');
    
    // Remover clases de ordenamiento de todos los th
    ths.forEach(th => {
        th.classList.remove('orden-asc', 'orden-desc');
    });
    
    // Determinar el nuevo orden
    if (columnaActual === columna) {
        ordenActual = ordenActual === 'asc' ? 'desc' : 'asc';
    } else {
        columnaActual = columna;
        ordenActual = 'asc';
    }
    
    // Agregar clase al th actual
    const thActual = event.currentTarget;
    thActual.classList.add(ordenActual === 'asc' ? 'orden-asc' : 'orden-desc');
    
    // Obtener datos de la tabla
    const tbody = document.getElementById('listaNiñosBody');
    const filas = Array.from(tbody.querySelectorAll('tr'));
    
    // Si no hay datos originales guardados, guardarlos
    if (datosOriginales.length === 0) {
        datosOriginales = filas.map(fila => ({
            elemento: fila,
            html: fila.outerHTML,
            celdas: Array.from(fila.cells).map(celda => celda.textContent.trim())
        }));
    }
    
    // Ordenar filas
    const filasOrdenadas = [...datosOriginales].sort((a, b) => {
        let valorA, valorB;
        
        // Mapear columna a índice
        const indices = {
            'numero': 0,
            'documento': 1,
            'paciente': 2,
            'establecimiento': 3,
            'fecha_nac': 4,
            'edad': 5,
            'vacunas_rn': 6,
            'cred_rn': 7,
            'tamizaje': 8,
            'cred_mensual': 9,
            'vacuna_1d': 10,
            'vacuna_2d': 11,
            'vacuna_3d': 12
        };
        
        const indice = indices[columna];
        
        if (indice !== undefined) {
            valorA = a.celdas[indice];
            valorB = b.celdas[indice];
            
            // Ordenamiento especial para números
            if (columna === 'numero' || columna === 'edad' || columna.includes('vacuna') || columna.includes('cred')) {
                valorA = parseInt(valorA.replace(/[^0-9]/g, '')) || 0;
                valorB = parseInt(valorB.replace(/[^0-9]/g, '')) || 0;
            } 
            // Ordenamiento para fechas (formato DD/MM/YYYY)
            else if (columna === 'fecha_nac') {
                const partesA = valorA.split('/');
                const partesB = valorB.split('/');
                if (partesA.length === 3 && partesB.length === 3) {
                    valorA = new Date(partesA[2], partesA[1]-1, partesA[0]);
                    valorB = new Date(partesB[2], partesB[1]-1, partesB[0]);
                }
            }
            // Ordenamiento para edad (ej: "3 meses", "1 año, 2 meses")
            else if (columna === 'edad') {
                valorA = convertirEdadAMeses(valorA);
                valorB = convertirEdadAMeses(valorB);
            }
        }
        
        // Comparación
        if (valorA < valorB) return ordenActual === 'asc' ? -1 : 1;
        if (valorA > valorB) return ordenActual === 'asc' ? 1 : -1;
        return 0;
    });
    
    // Reconstruir tbody
    tbody.innerHTML = filasOrdenadas.map(f => f.html).join('');
    
    // Actualizar números de fila
    actualizarNumerosFila();
}

// Función auxiliar para convertir edad a meses
function convertirEdadAMeses(edadTexto) {
    if (!edadTexto || edadTexto === 'Recién nacido') return 0;
    
    let meses = 0;
    
    // Buscar años
    const añosMatch = edadTexto.match(/(\d+)\s*año/);
    if (añosMatch) {
        meses += parseInt(añosMatch[1]) * 12;
    }
    
    // Buscar meses
    const mesesMatch = edadTexto.match(/(\d+)\s*mes/);
    if (mesesMatch) {
        meses += parseInt(mesesMatch[1]);
    }
    
    return meses;
}

// Función para actualizar números de fila
function actualizarNumerosFila() {
    const filas = document.querySelectorAll('#listaNiñosBody tr');
    filas.forEach((fila, index) => {
        const celdaNumero = fila.cells[0];
        if (celdaNumero) {
            celdaNumero.textContent = index + 1;
        }
    });
}

// Función para cargar indicadores (KPIs)
function cargarIndicadores() {
    const establecimiento = document.getElementById('establecimiento')?.value || '';
    const mes = document.querySelector('select[data-filter="mes"]')?.value || '';
    const año = document.querySelector('select[data-filter="año"]')?.value || '';
    
    console.log('Año seleccionado:', año);
    console.log('Tipo de dato:', typeof año);
    console.log('Filtros enviados:', { establecimiento, mes, año });
    
    // Construir URL - SOLO agregar si tienen valor
    let url = 'obtener_indicadores.php?';
    const params = [];
    
    if (establecimiento && establecimiento !== 'todos' && establecimiento !== '') {
        params.push('establecimiento=' + encodeURIComponent(establecimiento));
    }
    
    if (mes && mes !== 'todos' && mes !== '') {
        params.push('mes=' + encodeURIComponent(mes));
    }
    
    if (año && año !== 'todos' && año !== '') {
        params.push('año=' + encodeURIComponent(año));
    }
    
    url += params.join('&');
    
    console.log('URL generada:', url);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta indicadores:', data);
            
            if (data.error) {
                console.error('Error en indicadores:', data.error);
                return;
            }
            
            // Actualizar KPIs
            document.getElementById('totalEvaluados').innerText = data.totalEvaluados || 0;
            document.getElementById('Vacunas').innerText = data.Vacunas || 0;
            document.getElementById('Cred_RN').innerText = data.Cred_RN || 0;
            document.getElementById('Tamizaje_Neonatal').innerText = data.Tamizaje_Neonatal || 0;
            document.getElementById('Cred_Mensual').innerText = data.Cred_Mensual || 0;
            document.getElementById('Vacuna_1D').innerText = data.Vacuna_1D || 0;
            document.getElementById('Vacuna_2D').innerText = data.Vacuna_2D || 0;
            document.getElementById('Vacuna_3D').innerText = data.Vacuna_3D || 0;
        })
        .catch(error => {
            console.error('Error al cargar indicadores:', error);
        });
}

// Función para cargar datos de gráficos
function cargarDatosGraficos() {
    const establecimiento = document.getElementById('establecimiento')?.value || '';
    const mes = document.querySelector('select[data-filter="mes"]')?.value || '';
    const año = document.querySelector('select[data-filter="año"]')?.value || '';
    
    let url = 'obtener_datos_graficos.php?';
    const params = [];
    
    if (establecimiento && establecimiento !== 'todos' && establecimiento !== '') {
        params.push('establecimiento=' + encodeURIComponent(establecimiento));
    }
    if (mes && mes !== 'todos' && mes !== '') {
        params.push('mes=' + encodeURIComponent(mes));
    }
    if (año && año !== 'todos' && año !== '') {
        params.push('año=' + encodeURIComponent(año));
    }
    
    url += params.join('&');
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error en gráficos:', data.error);
                return;
            }
            
            if (window.pieChart) {
                window.pieChart.data.datasets[0].data = data.pie?.data || [0, 0];
                window.pieChart.update();
            }
            
            if (window.lineChart) {
                window.lineChart.data.datasets[0].data = data.line?.data || [];
                window.lineChart.update();
            }
        })
        .catch(error => {
            console.error('Error al cargar gráficos:', error);
        });
}

// Variable para almacenar el último documento buscado
let ultimoDocumentoBuscado = '';

// Función para buscar por documento - VERSIÓN CORREGIDA
function buscarPorDocumento() {
    const documento = document.getElementById('buscadorDocumento').value.trim();
    console.log('Buscando documento:', documento);
    
    if (!documento) {
        alert('Por favor ingrese un documento para buscar');
        return;
    }
    
    ultimoDocumentoBuscado = documento;
    
    // Obtener los filtros actuales
    const establecimiento = document.getElementById('establecimiento')?.value || '';
    const mes = document.querySelector('select[data-filter="mes"]')?.value || '';
    const año = document.querySelector('select[data-filter="año"]')?.value || '';
    
    let url = 'obtener_lista_niños.php?';
    const params = [];
    
    if (establecimiento && establecimiento !== 'todos' && establecimiento !== '') {
        params.push('establecimiento=' + encodeURIComponent(establecimiento));
    }
    if (mes && mes !== 'todos' && mes !== '') {
        params.push('mes=' + encodeURIComponent(mes));
    }
    if (año && año !== 'todos' && año !== '') {
        params.push('año=' + encodeURIComponent(año));
    }
    
    // SIEMPRE agregar el documento
    params.push('documento=' + encodeURIComponent(documento));
    
    url += params.join('&');
    
    console.log('URL de búsqueda:', url);
    
    // Mostrar indicador de carga
    document.getElementById('listaNiñosBody').innerHTML = 
        '<tr><td colspan="23" style="text-align: center;">Buscando...</td></tr>';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Resultados búsqueda:', data);
            
            const tbody = document.getElementById('listaNiñosBody');
            
            // Si hay un error específico
            if (data.error) {
                tbody.innerHTML = '<tr><td colspan="23" style="text-align: center; color: red;">' + 
                    (data.mensaje || 'Error en la búsqueda') + '</td></tr>';
                return;
            }
            
            // Si no hay datos (array vacío)
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="23" style="text-align: center;">No se encontraron niños con ese documento</td></tr>';
                return;
            }
            
            // Construir la tabla con los resultados
            let html = '';
            data.forEach((niño, index) => {
                html += '<tr>';
                html += `<td>${index + 1}</td>`;
                html += `<td>${niño.documento || ''}</td>`;
                html += `<td>${niño.paciente || 'S/N'}</td>`;
                html += `<td>${niño.establecimiento || ''}</td>`;
                html += `<td>${niño.fecha_nac || ''}</td>`;
                html += `<td>${niño.edad || 'Recién nacido'}</td>`;
                
                // Vacunas RN
                html += `<td class="indicadores-grupo"><div class="grupo-vacunas">`;
                html += `<div class="indicador-circulo ${niño.bcg == 1 ? 'cumple' : 'no-cumple'}" title="BCG"></div>`;
                html += `<div class="indicador-circulo ${niño.hvb == 1 ? 'cumple' : 'no-cumple'}" title="HVB"></div>`;
                html += `</div></td>`;
                
                // CRED RN
                html += `<td class="indicadores-grupo"><div class="grupo-cred">`;
                html += `<div class="indicador-circulo ${niño.rn1 == 1 ? 'cumple' : 'no-cumple'}" title="CRED RN 1"></div>`;
                html += `<div class="indicador-circulo ${niño.rn2 == 1 ? 'cumple' : 'no-cumple'}" title="CRED RN 2"></div>`;
                html += `<div class="indicador-circulo ${niño.rn3 == 1 ? 'cumple' : 'no-cumple'}" title="CRED RN 3"></div>`;
                html += `</div></td>`;
                
                // Tamizaje
                html += `<td class="indicadores-grupo"><div class="grupo-tamizaje">`;
                html += `<div class="indicador-circulo ${niño.tamizaje == 1 ? 'cumple' : 'no-cumple'}" title="Tamizaje"></div>`;
                html += `</div></td>`;
                
                // CRED Mensual
                html += `<td class="indicadores-grupo"><div class="grupo-cred-mensual">`;
                html += `<div class="fila-cred">`;
                html += `<div class="indicador-circulo ${niño.cred1 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 1"></div>`;
                html += `<div class="indicador-circulo ${niño.cred2 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 2"></div>`;
                html += `<div class="indicador-circulo ${niño.cred3 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 3"></div>`;
                html += `<div class="indicador-circulo ${niño.cred4 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 4"></div>`;
                html += `</div><div class="fila-cred">`;
                html += `<div class="indicador-circulo ${niño.cred5 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 5"></div>`;
                html += `<div class="indicador-circulo ${niño.cred6 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 6"></div>`;
                html += `<div class="indicador-circulo ${niño.cred7 == 1 ? 'cumple' : 'no-cumple'}" title="CRED 7"></div>`;
                html += `</div></div></td>`;
                
                // Vacuna 1 Dosis
                html += `<td class="indicadores-grupo"><div class="grupo-vacunas1d">`;
                html += `<div class="fila-vacuna">`;
                html += `<div class="indicador-circulo ${niño.neumococo1 == 1 ? 'cumple' : 'no-cumple'}" title="Neumococo 1D"></div>`;
                html += `<div class="indicador-circulo ${niño.rotavirus1 == 1 ? 'cumple' : 'no-cumple'}" title="Rotavirus 1D"></div>`;
                html += `</div><div class="fila-vacuna">`;
                html += `<div class="indicador-circulo ${niño.polio1 == 1 ? 'cumple' : 'no-cumple'}" title="Polio 1D"></div>`;
                html += `<div class="indicador-circulo ${niño.pentavalente1 == 1 ? 'cumple' : 'no-cumple'}" title="Pentavalente 1D"></div>`;
                html += `</div></div></td>`;
                
                // Vacuna 2 Dosis
                html += `<td class="indicadores-grupo"><div class="grupo-vacunas2d">`;
                html += `<div class="fila-vacuna">`;
                html += `<div class="indicador-circulo ${niño.neumococo2 == 1 ? 'cumple' : 'no-cumple'}" title="Neumococo 2D"></div>`;
                html += `<div class="indicador-circulo ${niño.rotavirus2 == 1 ? 'cumple' : 'no-cumple'}" title="Rotavirus 2D"></div>`;
                html += `</div><div class="fila-vacuna">`;
                html += `<div class="indicador-circulo ${niño.polio2 == 1 ? 'cumple' : 'no-cumple'}" title="Polio 2D"></div>`;
                html += `<div class="indicador-circulo ${niño.pentavalente2 == 1 ? 'cumple' : 'no-cumple'}" title="Pentavalente 2D"></div>`;
                html += `</div></div></td>`;
                
                // Vacuna 3 Dosis
                html += `<td class="indicadores-grupo"><div class="grupo-vacunas3d">`;
                html += `<div class="indicador-circulo ${niño.polio3 == 1 ? 'cumple' : 'no-cumple'}" title="Polio 3D"></div>`;
                html += `<div class="indicador-circulo ${niño.pentavalente3 == 1 ? 'cumple' : 'no-cumple'}" title="Pentavalente 3D"></div>`;
                html += `</div></td>`;
                
                html += '</tr>';
            });
            
            tbody.innerHTML = html;
            datosOriginales = [];
        })
        .catch(error => {
            console.error('Error al buscar:', error);
            document.getElementById('listaNiñosBody').innerHTML = 
                '<tr><td colspan="23" style="text-align: center; color: red;">Error en la conexión</td></tr>';
        });
}

// Función para limpiar la búsqueda
function limpiarBusqueda() {
    document.getElementById('buscadorDocumento').value = '';
    ultimoDocumentoBuscado = '';
    cargarListaNiños(); // Recarga sin filtro de documento
}

// Función de filtro
function filtrar() {
    console.log('Aplicando filtros...');
    cargarIndicadores();
    cargarDatosGraficos();
    // Si hay un documento buscado, mantener la búsqueda con los nuevos filtros
    if (ultimoDocumentoBuscado) {
        buscarPorDocumento();
    } else {
        cargarListaNiños();
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltrar = document.querySelector('.btn-filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', filtrar);
    }
    
    const selectEstablecimiento = document.getElementById('establecimiento');
    if (selectEstablecimiento) {
        selectEstablecimiento.addEventListener('change', function() {
            cargarIndicadores();
            cargarDatosGraficos();
            if (ultimoDocumentoBuscado) {
                buscarPorDocumento();
            } else {
                cargarListaNiños();
            }
        });
    }
    
    const selectAño = document.querySelector('select[data-filter="año"]');
    if (selectAño) {
        selectAño.addEventListener('change', function() {
            cargarIndicadores();
            cargarDatosGraficos();
            if (ultimoDocumentoBuscado) {
                buscarPorDocumento();
            } else {
                cargarListaNiños();
            }
        });
    }
    
    const selectMes = document.querySelector('select[data-filter="mes"]');
    if (selectMes) {
        selectMes.addEventListener('change', function() {
            cargarIndicadores();
            cargarDatosGraficos();
            if (ultimoDocumentoBuscado) {
                buscarPorDocumento();
            } else {
                cargarListaNiños();
            }
        });
    }
    
    // Agregar evento para buscar con Enter
    const buscador = document.getElementById('buscadorDocumento');
    if (buscador) {
        buscador.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarPorDocumento();
            }
        });
    }
});

// Inicializar gráficos
document.addEventListener('DOMContentLoaded', function() {
    const pieCtx = document.getElementById('pieChart');
    if (pieCtx) {
        window.pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Con Anemia', 'Sin Anemia'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: ['#e74c3c', '#2ecc71']
                }]
            }
        });
    }

    const lineCtx = document.getElementById('lineChart');
    if (lineCtx) {
        window.lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Total Niños',
                    data: [],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true
                }]
            }
        });
    }
});

// Variable para controlar la sección actual
let seccionActual = 'inicio';

// Función para mostrar diferentes secciones
function mostrarSeccion(seccion) {
    // Prevenir que el enlace haga algo raro
    event.preventDefault();
    
    seccionActual = seccion;
    
    // Quitar clase active de todos los items del menú
    document.querySelectorAll('.sidebar li').forEach(li => {
        li.classList.remove('active');
    });
    
    // Agregar clase active al item seleccionado
    event.currentTarget.classList.add('active');
    
    const mainContent = document.querySelector('.main');
    const construccionContainer = document.getElementById('construccionContainer');
    const filters = document.querySelector('.filters');
    const cards = document.querySelector('.cards');
    const listaNiños = document.querySelector('.lista-niños');
    
    switch(seccion) {
        case 'inicio':
            // Mostrar contenido normal
            filters.style.display = 'flex';
            cards.style.display = 'grid';
            listaNiños.style.display = 'block';
            construccionContainer.style.display = 'none';
            mainContent.classList.remove('construccion-active');
            
            // Recargar datos
            cargarIndicadores();
            cargarDatosGraficos();
            cargarListaNiños();
            break;
            
        case 'cred':
        case 'inmunizaciones':
        case 'adolescentes':
            // Ocultar contenido normal y mostrar construcción
            filters.style.display = 'none';
            cards.style.display = 'none';
            listaNiños.style.display = 'none';
            construccionContainer.style.display = 'flex';
            mainContent.classList.add('construccion-active');
            break;
    }
}

// Función para volver al inicio
function volverAlInicio() {
    mostrarSeccion('inicio');
    
    // Activar el item del menú correspondiente
    document.querySelectorAll('.sidebar li').forEach(li => {
        li.classList.remove('active');
        if (li.querySelector('a').textContent.trim() === 'Niño') {
            li.classList.add('active');
        }
    });
}

// Modificar la carga inicial para asegurar que se muestra la sección correcta
document.addEventListener('DOMContentLoaded', function() {
    cargarFiltrosYEstablecimientos();
    cargarIndicadores();
    cargarDatosGraficos();
    cargarListaNiños();
    
    // Asegurar que la sección de inicio está visible
    const mainContent = document.querySelector('.main');
    const construccionContainer = document.getElementById('construccionContainer');
    const filters = document.querySelector('.filters');
    const cards = document.querySelector('.cards');
    const listaNiños = document.querySelector('.lista-niños');
    
    filters.style.display = 'flex';
    cards.style.display = 'grid';
    listaNiños.style.display = 'block';
    construccionContainer.style.display = 'none';
    mainContent.classList.remove('construccion-active');
});

// Prevenir que los enlaces recarguen la página
document.querySelectorAll('.sidebar li a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
    });
});

// Función para descargar Excel
function descargarExcel() {
    // Obtener filtros actuales
    const establecimiento = document.getElementById('establecimiento')?.value || '';
    const mes = document.querySelector('select[data-filter="mes"]')?.value || '';
    const año = document.querySelector('select[data-filter="año"]')?.value || '';
    const documento = document.getElementById('buscadorDocumento')?.value || '';
    
    // Construir URL con los filtros actuales
    let url = 'descargar_excel_final.php?';
    const params = [];
    
    if (establecimiento && establecimiento !== 'todos' && establecimiento !== '') {
        params.push('establecimiento=' + encodeURIComponent(establecimiento));
    }
    if (mes && mes !== 'todos' && mes !== '') {
        params.push('mes=' + encodeURIComponent(mes));
    }
    if (año && año !== 'todos' && año !== '') {
        params.push('año=' + encodeURIComponent(año));
    }
    if (documento) {
        params.push('documento=' + encodeURIComponent(documento));
    }
    
    url += params.join('&');
    
    console.log('Descargando Excel con filtros:', url);
    
    // Mostrar mensaje de carga (opcional)
    const btnExcel = document.querySelector('.btn-excel');
    const textoOriginal = btnExcel.innerHTML;
    btnExcel.innerHTML = '<span>⏳</span><span>Generando Excel...</span>';
    btnExcel.disabled = true;
    
    // Redirigir para descargar
    window.location.href = url;
    
    // Restaurar botón después de un tiempo
    setTimeout(() => {
        btnExcel.innerHTML = textoOriginal;
        btnExcel.disabled = false;
    }, 3000);
}