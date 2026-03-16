<?php
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
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitoreo de Niño menor de 1 AÑO</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>El Chochua</h2>
    <ul>
        <li class="active" onclick="mostrarSeccion('inicio')"><a href="javascript:void(0)">👦 Niño</a></li>
        <li onclick="mostrarSeccion('cred')"><a href="javascript:void(0)">👶 CRED Recién Nacido</a></li>
        <li onclick="mostrarSeccion('inmunizaciones')"><a href="javascript:void(0)">💉 Inmunizaciones</a></li>
        <li onclick="mostrarSeccion('adolescentes')"><a href="javascript:void(0)">🧑 Adolescentes</a></li>
        
        <!-- ATENCIONES POR DNI - ENLACE DIRECTO -->
<li style="border-top: 1px dashed rgba(255,255,255,0.3); margin-top: 5px;">
    <a href="atenciones.php" style="display: block; padding: 12px; color: #ffffff; text-decoration: none; font-weight: bold;" onclick="window.location.href='atenciones.php'; return false;">
        📋 Atenciones por DNI
    </a>
</li>
        
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
        <li style="border-top: 1px solid rgba(255,255,255,0.2); margin-top: 10px; padding-top: 10px;">
            <a href="admin_dashboard.php" onclick="window.location.href='admin_dashboard.php'; return false;">
                👥 Administrar Usuarios
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">

<!-- HEADER -->
<div class="header">
    <div class="header-top">
        <h1>Niño menor de 1 año</h1>
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

<!-- FILTROS -->
<div class="filters">
    <div class="filter-group">
        <label>Año:</label>
        <select data-filter="año" id="filtroAño">
            <option value="">Todos</option>
        </select>
    </div>

    <div class="filter-group">
        <label>Mes:</label>
        <select data-filter="mes" id="filtroMes">
            <option value="">Todos</option>
        </select>
    </div>

    <div class="filter-group">
        <label>Establecimiento:</label>
        <select id="establecimiento">
            <option value="">Todos</option>
        </select>
    </div>

    <button class="btn-filtrar">Filtrar</button>
</div>

<!-- KPIs -->
<div class="cards">
    <div class="card">
        <h3>Total Evaluados</h3>
        <p id="totalEvaluados">0</p>
    </div>
    <div class="card">
        <h3>Vacunas RN</h3>
        <p id="Vacunas">0</p>
    </div>
    <div class="card">
        <h3>Cred RN</h3>
        <p id="Cred_RN">0</p>
    </div>
    <div class="card">
        <h3>Tamizaje Neonatal</h3>
        <p id="Tamizaje_Neonatal">0</p>
    </div>
    <!-- NUEVAS CARDS -->
    <div class="card">
        <h3>CRED Mensual</h3>
        <p id="Cred_Mensual">0</p>
    </div>
    <div class="card">
        <h3>Vacuna 1 Dosis</h3>
        <p id="Vacuna_1D">0</p>
    </div>
    <div class="card">
        <h3>Vacuna 2 Dosis</h3>
        <p id="Vacuna_2D">0</p>
    </div>
    <div class="card">
        <h3>Vacuna 3 Dosis</h3>
        <p id="Vacuna_3D">0</p>
    </div>
</div>



<!-- LISTA DE NIÑOS -->
<div class="lista-niños">
    <div class="lista-header">
        <h2>Lista de Niños Evaluados</h2>
        <div class="acciones-container">
            <div class="buscador-container">
                <input type="text" 
                       id="buscadorDocumento" 
                       placeholder="Buscar por documento..." 
                       class="buscador-input">
                <button onclick="buscarPorDocumento()" class="btn-buscar">Buscar</button>
                <button onclick="limpiarBusqueda()" class="btn-limpiar">Limpiar</button>
            </div>
            <button onclick="descargarExcel()" class="btn-excel">
                <span>📥</span>
                <span>Descargar Excel</span>
            </button>
        </div>
    </div>
    <div class="tabla-container">
        <table id="tablaNiños">
            <thead>
                <tr>
                    <th onclick="ordenarTabla('numero')" class="ordenable">Nº</th>
                    <th onclick="ordenarTabla('documento')" class="ordenable">Documento</th>
                    <th onclick="ordenarTabla('paciente')" class="ordenable">Paciente</th>
                    <th onclick="ordenarTabla('establecimiento')" class="ordenable">Establecimiento</th>
                    <th onclick="ordenarTabla('fecha_nac')" class="ordenable">Fecha Nac.</th>
                    <th onclick="ordenarTabla('edad')" class="ordenable">Edad</th>
                    <th onclick="ordenarTabla('vacunas_rn')" class="ordenable">Vacunas RN</th>
                    <th onclick="ordenarTabla('cred_rn')" class="ordenable">CRED RN</th>
                    <th onclick="ordenarTabla('tamizaje')" class="ordenable">Tamizaje</th>
                    <th onclick="ordenarTabla('cred_mensual')" class="ordenable">Cred Mensual</th>
                    <th onclick="ordenarTabla('vacuna_1d')" class="ordenable">Vacuna 1 Dosis</th>
                    <th onclick="ordenarTabla('vacuna_2d')" class="ordenable">Vacuna 2 Dosis</th>
                    <th onclick="ordenarTabla('vacuna_3d')" class="ordenable">Vacuna 3 Dosis</th>
                </tr>
            </thead>
            <tbody id="listaNiñosBody">
                <tr>
                    <td colspan="13" style="text-align: center;">Cargando datos...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CONTENEDOR PARA MENSAJE DE PÁGINAS EN CONSTRUCCIÓN (OCULTO POR DEFECTO) -->
    <div id="construccionContainer" class="construccion-container" style="display: none;">
        <div class="construccion-content">
            <img src="img/en-construccion.png" alt="En construcción" class="construccion-img">
            <h2>¡Algún día trabajaremos en ello!</h2>
            <p>Esta sección se encuentra en "desarrollo" dicen :v. Pronto estará disponible, si me aumentan el sueldo xD.</p>
            <button onclick="volverAlInicio()" class="btn-volver">Volver al Inicio</button>
        </div>
    </div>
</div>


<script src="app.js"></script>

</body>
</html>