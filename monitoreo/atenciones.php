<?php
// atenciones.php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario
$usuario_nombre = $_SESSION['nombre_completo'] ?? $_SESSION['usuario'];
$usuario_rol = $_SESSION['rol'] ?? 'usuario';
$usuario_microred = $_SESSION['microred'] ?? 'TODAS';

// Manejar secciones por URL
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atenciones por DNI - El Chochua</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos adicionales específicos para atenciones */
        .atenciones-container {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .filtros-atenciones {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }

        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filtro-grupo {
            display: flex;
            flex-direction: column;
        }

        .filtro-grupo label {
            font-weight: 600;
            color: #0f3c4c;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .filtro-grupo input, .filtro-grupo select {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filtro-grupo input:focus, .filtro-grupo select:focus {
            outline: none;
            border-color: #0f3c4c;
            box-shadow: 0 0 10px rgba(15,60,76,0.2);
        }

        .btn-buscar-atenciones {
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            transition: all 0.3s;
            height: 45px;
        }

        .btn-buscar-atenciones:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(15,60,76,0.4);
        }

        /* Info paciente plegable */
        .info-paciente-container {
            margin-bottom: 25px;
        }

        .info-paciente-header {
            background: #e8f4fd;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            border-left: 5px solid #0f3c4c;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .info-paciente-header:hover {
            background: #d1e7fd;
        }

        .info-paciente-header h3 {
            color: #0f3c4c;
            font-size: 18px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-icon {
            font-size: 24px;
            color: #0f3c4c;
            transition: transform 0.3s;
        }

        .info-paciente-content {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            border-left: 5px solid #0f3c4c;
            border-top: 1px solid #b8d9f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            transition: all 0.3s;
        }

        .info-paciente-content.hidden {
            display: none;
        }

        .info-paciente-content p {
            margin: 5px 0;
            font-size: 15px;
        }

        .info-paciente-content strong {
            color: #0f3c4c;
            min-width: 100px;
            display: inline-block;
        }

        /* Tabla de atenciones - ESTILOS CORREGIDOS */
        .tabla-atenciones {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .tabla-atenciones table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 1300px;
            background: white;
        }

        .tabla-atenciones th {
            background: #0f3c4c;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 500;
            white-space: nowrap;
            border-right: 1px solid #1a5f7a;
        }

        .tabla-atenciones th:last-child {
            border-right: none;
        }

        .tabla-atenciones td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
            text-align: center;
            vertical-align: middle;
        }

        .tabla-atenciones td:last-child {
            border-right: none;
        }

        .tabla-atenciones tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .tabla-atenciones tbody tr:last-child {
            border-bottom: none;
        }

        .tabla-atenciones tbody tr:hover {
            background-color: #f5f5f5;
        }

        .tabla-atenciones tr.atencion-principal td {
            background-color: #f8f9fa;
            border-bottom: 1px solid #d0d0d0;
        }

        .tabla-atenciones tr.atencion-secundaria td {
            background-color: white;
        }

        .badge-codigo {
            background: #0f3c4c;
            color: white;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
            white-space: nowrap;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .loading::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }

        .sin-resultados {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        /* Estilos para página en construcción */
        .construccion-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
            padding: 40px;
        }

        .construccion-content {
            text-align: center;
            max-width: 500px;
        }

        .construccion-img {
            max-width: 300px;
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .construccion-content h2 {
            color: #0f3c4c;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .construccion-content p {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-volver {
            background-color: #0f3c4c;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-volver:hover {
            background-color: #1a5f7a;
            transform: scale(1.05);
        }

        .btn-volver:active {
            transform: scale(0.95);
        }

        /* Autocomplete para código */
        .autocomplete-suggestions {
            position: absolute;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .autocomplete-suggestion {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .autocomplete-suggestion:hover {
            background: #f0f0f0;
        }

        .autocomplete-suggestion.selected {
            background: #0f3c4c;
            color: white;
        }

        /* Resumen de atenciones */
        .resumen-atenciones {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 30px;
            border: 1px solid #e0e0e0;
        }

        .resumen-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .resumen-item strong {
            color: #0f3c4c;
            font-size: 14px;
        }

        .resumen-item span {
            background: #0f3c4c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>El Chochua</h2>
    <ul>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">🏠 Dashboard Principal</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'atenciones.php' && $seccion == '' ? 'active' : ''; ?>">
            <a href="atenciones.php">📋 Atenciones por DNI</a>
        </li>
        <li class="<?php echo ($seccion == 'cred') ? 'active' : ''; ?>">
            <a href="atenciones.php?seccion=cred">👶 CRED Recién Nacido</a>
        </li>
        <li class="<?php echo ($seccion == 'inmunizaciones') ? 'active' : ''; ?>">
            <a href="atenciones.php?seccion=inmunizaciones">💉 Inmunizaciones</a>
        </li>
        <li class="<?php echo ($seccion == 'adolescentes') ? 'active' : ''; ?>">
            <a href="atenciones.php?seccion=adolescentes">🧑 Adolescentes</a>
        </li>
        
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
        <li style="border-top: 1px solid rgba(255,255,255,0.2); margin-top: 10px; padding-top: 10px;"
            class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <a href="admin_dashboard.php">👥 Administrar Usuarios</a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">

<!-- HEADER -->
<div class="header">
    <div class="header-top">
        <h1>Atenciones por DNI</h1>
        <div class="user-info">
            <span class="user-name"><?php echo $usuario_nombre; ?></span>
            <span class="user-role">(<?php echo $usuario_rol; ?>)</span>
            <?php if ($usuario_microred !== 'TODAS'): ?>
                <span class="user-microred">MicroRed: <?php echo $usuario_microred; ?></span>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout" onclick="return confirm('¿Cerrar sesión?')">
                <span>Cerrar Sesión</span>
                <span class="logout-icon">🚪</span>
            </a>
        </div>
    </div>
</div>

<?php if ($seccion == ''): ?>

<!-- FILTROS DE ATENCIONES -->
<div class="filtros-atenciones">
    <div class="filtros-grid">
        <div class="filtro-grupo">
            <label>📄 DNI / Documento</label>
            <input type="text" id="dniBusqueda" placeholder="Ingrese DNI del paciente" value="94504590">
        </div>

        <div class="filtro-grupo">
            <label>📅 Fecha Inicial</label>
            <input type="date" id="fechaInicial" value="2025-01-01">
        </div>

        <div class="filtro-grupo">
            <label>📅 Fecha Final</label>
            <input type="date" id="fechaFinal" value="2026-03-15">
        </div>

        <div class="filtro-grupo">
            <label>🔍 Código (autocompletado)</label>
            <div style="position: relative;">
                <input type="text" id="cie10" placeholder="Ej: 96150, 99411, etc." autocomplete="off">
                <div id="autocompleteList" class="autocomplete-suggestions" style="display: none;"></div>
            </div>
        </div>

        <div class="filtro-grupo">
            <button class="btn-buscar-atenciones" onclick="buscarAtenciones()">
                <span>🔍</span>
                <span>Buscar Atenciones</span>
            </button>
        </div>
    </div>
</div>

<!-- INFORMACIÓN DEL PACIENTE (plegable) -->
<div id="infoPaciente" class="info-paciente-container" style="display: none;">
    <div class="info-paciente-header" onclick="togglePacienteInfo()">
        <h3>
            <span>👤 Información del Paciente</span>
        </h3>
        <span class="toggle-icon" id="toggleIcon">▼</span>
    </div>
    <div class="info-paciente-content" id="pacienteContent">
        <div>
            <p><strong>Documento:</strong> <span id="pacienteDoc"></span></p>
            <p><strong>Nombre:</strong> <span id="pacienteNombre"></span></p>
            <p><strong>Edad:</strong> <span id="pacienteEdad"></span></p>
        </div>
        <div>
            <p><strong>Total atenciones:</strong> <span id="totalAtenciones"></span></p>
            <p><strong>Periodo:</strong> <span id="periodoBusqueda"></span></p>
        </div>
    </div>
</div>

<!-- RESUMEN DE ATENCIONES -->
<div id="resumenAtenciones" class="resumen-atenciones" style="display: none;">
    <div class="resumen-item">
        <strong>Total atenciones:</strong>
        <span id="resumenTotal">0</span>
    </div>
    <div class="resumen-item">
        <strong>Periodo:</strong>
        <span id="resumenPeriodo"></span>
    </div>
</div>

<!-- TABLA DE ATENCIONES -->
<div class="atenciones-container">
    <h2>Historial de Atenciones</h2>
    <div class="tabla-atenciones">
        <table id="tablaAtenciones">
            <thead>
                <tr>
                    <th>Tipo Doc</th>
                    <th>Cond EESS - Serv</th>
                    <th>Fecha Atención</th>
                    <th>Lote</th>
                    <th>Pag</th>
                    <th>Reg</th>
                    <th>Código</th>
                    <th>Tipo Dx</th>
                    <th>Lab</th>
                    <th>Edad</th>
                    <th>Tipo Edad</th>
                    <th>Peso</th>
                    <th>Talla</th>
                    <th>Hb</th>
                    <th>Lugar Aten.</th>
                    <th>Personal</th>
                    <th>Fech Reg o Modif</th>
                </tr>
            </thead>
            <tbody id="tablaAtencionesBody">
                <tr>
                    <td colspan="17" class="loading">Ingrese un DNI y presione Buscar</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>

<!-- CONTENEDOR PARA MENSAJE DE PÁGINAS EN CONSTRUCCIÓN -->
<div class="construccion-container">
    <div class="construccion-content">
        <img src="img/en-construccion.png" alt="En construcción" class="construccion-img">
        <h2>¡Algún día trabajaremos en ello!</h2>
        <p>Esta sección se encuentra en "desarrollo" dicen :v. Pronto estará disponible, si me aumentan el sueldo xD.</p>
        <button onclick="window.location.href='atenciones.php'" class="btn-volver">Volver al Inicio</button>
    </div>
</div>

<?php endif; ?>

</div> <!-- Fin main -->

<script>
    let codigosSugeridos = [];
    let selectedSuggestionIndex = -1;
    let timeoutId = null;

    // Función para toggle de información del paciente
    function togglePacienteInfo() {
        const content = document.getElementById('pacienteContent');
        const icon = document.getElementById('toggleIcon');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.textContent = '▼';
        } else {
            content.classList.add('hidden');
            icon.textContent = '▶';
        }
    }

    // Función para calcular edad actual
    function calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento || fechaNacimiento === 'No disponible') return 'No disponible';
        
        try {
            // Manejar formato DD/MM/YYYY
            const partes = fechaNacimiento.split('/');
            if (partes.length !== 3) return 'No disponible';
            
            const fechaNac = new Date(partes[2], partes[1] - 1, partes[0]);
            const hoy = new Date();
            
            // Validar fecha
            if (isNaN(fechaNac.getTime())) return 'Fecha inválida';
            
            let años = hoy.getFullYear() - fechaNac.getFullYear();
            let meses = hoy.getMonth() - fechaNac.getMonth();
            let días = hoy.getDate() - fechaNac.getDate();
            
            // Ajustar días negativos
            if (días < 0) {
                meses--;
                const ultimoMes = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
                días += ultimoMes.getDate();
            }
            
            // Ajustar meses negativos
            if (meses < 0) {
                años--;
                meses += 12;
            }
            
            // Formatear según edad
            if (años === 0 && meses === 0) {
                return `${días} día${días !== 1 ? 's' : ''}`;
            } else if (años === 0) {
                return `${meses} mes${meses !== 1 ? 'es' : ''}, ${días} día${días !== 1 ? 's' : ''}`;
            } else {
                return `${años} año${años !== 1 ? 's' : ''}, ${meses} mes${meses !== 1 ? 'es' : ''}, ${días} día${días !== 1 ? 's' : ''}`;
            }
        } catch (e) {
            console.error('Error calculando edad:', e);
            return 'No disponible';
        }
    }
    
    // Función para buscar atenciones
    function buscarAtenciones() {
        const dni = document.getElementById('dniBusqueda').value.trim();
        const fechaInicial = document.getElementById('fechaInicial').value;
        const fechaFinal = document.getElementById('fechaFinal').value;
        const cie10 = document.getElementById('cie10').value.trim();

        if (!dni) {
            alert('Por favor ingrese un DNI');
            return;
        }

        // Mostrar loading
        document.getElementById('tablaAtencionesBody').innerHTML = 
            '<tr><td colspan="17" class="loading">Buscando atenciones...</td></tr>';
        document.getElementById('infoPaciente').style.display = 'none';
        document.getElementById('resumenAtenciones').style.display = 'none';

        // Construir URL
        let url = `buscar_atenciones.php?dni=${encodeURIComponent(dni)}&fecha_inicial=${fechaInicial}&fecha_final=${fechaFinal}`;
        if (cie10) {
            url += `&cie10=${encodeURIComponent(cie10)}`;
        }

        console.log('Buscando:', url);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Datos recibidos completos:', data);
                console.log('Datos del paciente:', data.paciente);
                
                // Verificar la estructura de la primera atención
                if (data.atenciones && data.atenciones.length > 0) {
                    console.log('Primera atención:', data.atenciones[0]);
                    console.log('Campos disponibles:', Object.keys(data.atenciones[0]));
                }
                
                if (data.error) {
                    alert('Error: ' + data.error);
                    document.getElementById('tablaAtencionesBody').innerHTML = 
                        '<tr><td colspan="17" style="color: red; text-align: center;">' + data.error + '</td></tr>';
                    return;
                }

                if (!data.atenciones || data.atenciones.length === 0) {
                    document.getElementById('tablaAtencionesBody').innerHTML = 
                        '<tr><td colspan="17" class="sin-resultados">No se encontraron atenciones para el período seleccionado</td></tr>';
                    document.getElementById('infoPaciente').style.display = 'none';
                    document.getElementById('resumenAtenciones').style.display = 'none';
                    return;
                }

                // Mostrar información del paciente
                document.getElementById('pacienteDoc').textContent = data.paciente.documento || dni;
                document.getElementById('pacienteNombre').textContent = data.paciente.nombre || 'No disponible';
                
                // Calcular edad si hay fecha de nacimiento
                if (data.paciente.fecha_nacimiento) {
                    document.getElementById('pacienteEdad').textContent = calcularEdad(data.paciente.fecha_nacimiento);
                } else {
                    document.getElementById('pacienteEdad').textContent = data.paciente.edad || 'No disponible';
                }
                
                // Mostrar resumen
                document.getElementById('resumenTotal').textContent = data.atenciones.length;
                document.getElementById('totalAtenciones').textContent = data.atenciones.length;
                
                const fechaInicialFormatted = new Date(fechaInicial).toLocaleDateString('es-PE');
                const fechaFinalFormatted = new Date(fechaFinal).toLocaleDateString('es-PE');
                const periodoText = `${fechaInicialFormatted} - ${fechaFinalFormatted}`;
                document.getElementById('periodoBusqueda').textContent = periodoText;
                document.getElementById('resumenPeriodo').textContent = periodoText;
                
                document.getElementById('infoPaciente').style.display = 'block';
                document.getElementById('resumenAtenciones').style.display = 'flex';
                document.getElementById('pacienteContent').classList.remove('hidden');
                document.getElementById('toggleIcon').textContent = '▼';

                // Generar tabla agrupada por cita
                let html = '';
                let currentCita = null;
                
                // Primero, contar cuántos códigos tiene cada cita
                const citas = {};
                data.atenciones.forEach(atencion => {
                    if (!citas[atencion.id_cita]) {
                        citas[atencion.id_cita] = [];
                    }
                    citas[atencion.id_cita].push(atencion);
                });
                
                console.log('Citas agrupadas:', citas);
                
                // Generar tabla con rowspan
                data.atenciones.forEach((atencion, index) => {
                    if (!currentCita || currentCita !== atencion.id_cita) {
                        currentCita = atencion.id_cita;
                        const rowspanCount = citas[currentCita].length;
                        
                        // Mostrar fila principal con rowspan
                        html += '<tr class="atencion-principal">';
                        html += `<td rowspan="${rowspanCount}">${atencion.tipo_doc || 'DNI'}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.cond_eess || ' - '}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.fecha_atencion || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.lote || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.pagina || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.registro || ''}</td>`;
                        html += `<td><span class="badge-codigo">${atencion.codigo || ''}</span></td>`;
                        html += `<td>${atencion.tipo_dx || ''}</td>`;
                        html += `<td>${atencion.lab || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.edad_atencion || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.tipo_edad_atencion || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.peso || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.talla || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.hb || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.lugar_atencion || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.personal || ''}</td>`;
                        html += `<td rowspan="${rowspanCount}">${atencion.fecha_registro || ''}</td>`;
                        html += '</tr>';
                    } else {
                        // Códigos adicionales de la misma cita
                        html += '<tr class="atencion-secundaria">';
                        html += `<td><span class="badge-codigo">${atencion.codigo || ''}</span></td>`;
                        html += `<td>${atencion.tipo_dx || ''}</td>`;
                        html += `<td>${atencion.lab || ''}</td>`;
                        html += '</tr>';
                    }
                });

                document.getElementById('tablaAtencionesBody').innerHTML = html;
                console.log('Tabla generada correctamente');
                
                // Extraer códigos únicos para autocompletado
                codigosSugeridos = [...new Set(data.atenciones.map(a => a.codigo))].filter(c => c);
                console.log('Códigos para autocompletado:', codigosSugeridos);
            })
            .catch(error => {
                console.error('Error en fetch:', error);
                document.getElementById('tablaAtencionesBody').innerHTML = 
                    '<tr><td colspan="17" style="color: red; text-align: center;">Error al buscar atenciones: ' + error.message + '</td></tr>';
            });
    }

    // Función para autocompletado de códigos
    function setupAutocomplete() {
        const input = document.getElementById('cie10');
        const list = document.getElementById('autocompleteList');
        
        input.addEventListener('input', function() {
            const valor = this.value.toLowerCase();
            
            if (timeoutId) clearTimeout(timeoutId);
            
            timeoutId = setTimeout(() => {
                if (valor.length < 1 || codigosSugeridos.length === 0) {
                    list.style.display = 'none';
                    return;
                }
                
                const filtrados = codigosSugeridos.filter(codigo => 
                    codigo.toLowerCase().includes(valor)
                ).slice(0, 10);
                
                if (filtrados.length === 0) {
                    list.style.display = 'none';
                    return;
                }
                
                let html = '';
                filtrados.forEach((codigo, idx) => {
                    html += `<div class="autocomplete-suggestion" onclick="selectCodigo('${codigo}')">${codigo}</div>`;
                });
                
                list.innerHTML = html;
                list.style.display = 'block';
                selectedSuggestionIndex = -1;
            }, 300);
        });
        
        input.addEventListener('keydown', function(e) {
            const items = list.querySelectorAll('.autocomplete-suggestion');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, items.length - 1);
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
                updateSelection(items);
            } else if (e.key === 'Enter' && selectedSuggestionIndex >= 0) {
                e.preventDefault();
                items[selectedSuggestionIndex].click();
            } else if (e.key === 'Escape') {
                list.style.display = 'none';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !list.contains(e.target)) {
                list.style.display = 'none';
            }
        });
    }
    
    function updateSelection(items) {
        items.forEach((item, idx) => {
            if (idx === selectedSuggestionIndex) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }
    
    function selectCodigo(codigo) {
        document.getElementById('cie10').value = codigo;
        document.getElementById('autocompleteList').style.display = 'none';
        buscarAtenciones();
    }

    // Búsqueda con Enter
    document.getElementById('dniBusqueda').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarAtenciones();
        }
    });

    // Inicializar autocompletado
    document.addEventListener('DOMContentLoaded', function() {
        setupAutocomplete();
    });
</script>

</body>
</html>