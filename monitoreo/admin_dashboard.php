<?php
// admin_dashboard.php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

$usuario_nombre = $_SESSION['nombre_completo'] ?? $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - El Chochua</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background: #f4f6f9;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0a2f3a 0%, #1e5f7a 100%);
            color: white;
            padding: 25px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .sidebar-header p {
            font-size: 13px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            gap: 12px;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid #ffc107;
        }

        .sidebar-menu a i {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header h1 {
            color: #0f3c4c;
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #0f3c4c;
            display: block;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .stat-info h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 28px;
            font-weight: 700;
            color: #0f3c4c;
        }

        /* Actions Bar */
        .actions-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(15,60,76,0.4);
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
            background: #f8f9fa;
            padding: 5px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .search-box input {
            border: none;
            background: transparent;
            padding: 10px;
            width: 250px;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
        }

        .search-box button {
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 18px;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px 10px;
            background: #f8f9fa;
            color: #0f3c4c;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }

        td {
            padding: 15px 10px;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-admin {
            background: #0f3c4c;
            color: white;
        }

        .badge-user {
            background: #e9ecef;
            color: #495057;
        }

        .badge-microred {
            background: #ffc107;
            color: #000;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #0f3c4c;
            color: white;
        }

        .btn-edit:hover {
            background: #1e5f7a;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            width: 500px;
            max-width: 90%;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 20px 25px;
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #ffc107;
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #0f3c4c;
            box-shadow: 0 0 10px rgba(15,60,76,0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-save {
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(15,60,76,0.4);
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>El Chochua</h2>
            <p>Panel de Administración</p>
        </div>
        <div class="sidebar-menu">
            <a href="index.php">
                <i>🏠</i>
                <span>Dashboard Principal</span>
            </a>
            <a href="#" class="active">
                <i>👥</i>
                <span>Usuarios</span>
            </a>
            <a href="#">
                <i>📊</i>
                <span>Reportes</span>
            </a>
            <a href="#">
                <i>⚙️</i>
                <span>Configuración</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1>Gestión de Usuarios</h1>
            <div class="user-info">
                <div class="user-details">
                    <span class="user-name"><?php echo $usuario_nombre; ?></span>
                    <span class="user-role">Administrador</span>
                </div>
                <a href="logout.php" class="btn-logout">
                    <span>🚪</span>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>Total Usuarios</h3>
                    <p id="totalUsuarios">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👑</div>
                <div class="stat-info">
                    <h3>Administradores</h3>
                    <p id="totalAdmins">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👤</div>
                <div class="stat-info">
                    <h3>Usuarios Normales</h3>
                    <p id="totalUsuariosNormales">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>Activos</h3>
                    <p id="totalActivos">0</p>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <button class="btn-primary" onclick="abrirModal()">
                <span>➕</span>
                <span>Nuevo Usuario</span>
            </button>
            <div class="search-box">
                <input type="text" id="buscadorUsuario" placeholder="Buscar usuario...">
                <button onclick="buscarUsuarios()">🔍</button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>MicroRed</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="listaUsuariosBody">
                    <tr>
                        <td colspan="8" class="loading">Cargando usuarios</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Crear/Editar Usuario -->
    <div class="modal" id="usuarioModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitulo">Nuevo Usuario</h3>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="usuarioForm">
                    <input type="hidden" id="usuarioId" name="id">
                    
                    <div class="form-group">
                        <label for="usuario">Usuario *</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña <span id="passwordLabel">*</span></label>
                        <input type="password" id="password" name="password" 
                               placeholder="Mínimo 6 caracteres">
                    </div>

                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo *</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="rol">Rol *</label>
                            <select id="rol" name="rol" required>
                                <option value="usuario">Usuario</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="microred">MicroRed *</label>
                            <select id="microred" name="microred" required>
                                <option value="TODAS">Todas</option>
                                <option value="AGUAYTIA">Aguaytía</option>
                                <option value="SAN ALEJANDRO">San Alejandro</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="activo" name="activo" checked>
                            Usuario activo
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarUsuario()">Guardar Usuario</button>
            </div>
        </div>
    </div>

    <script>
        // Cargar usuarios al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarUsuarios();
        });

        // Función para cargar usuarios
        function cargarUsuarios() {
            fetch('obtener_usuarios.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Usuarios:', data);
                    
                    if (data.error) {
                        alert('Error al cargar usuarios: ' + data.error);
                        return;
                    }
                    
                    actualizarTabla(data);
                    actualizarStats(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('listaUsuariosBody').innerHTML = 
                        '<tr><td colspan="8" style="color: red; text-align: center;">Error al cargar usuarios</td></tr>';
                });
        }

        // Actualizar tabla de usuarios
        function actualizarTabla(usuarios) {
            const tbody = document.getElementById('listaUsuariosBody');
            
            if (!usuarios || usuarios.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No hay usuarios registrados</td></tr>';
                return;
            }
            
            let html = '';
            usuarios.forEach(user => {
                const estadoClass = user.activo == 1 ? 'badge' : 'badge';
                const estadoText = user.activo == 1 ? 'Activo' : 'Inactivo';
                
                html += '<tr>';
                html += `<td>${user.id}</td>`;
                html += `<td>${user.usuario}</td>`;
                html += `<td>${user.nombre_completo}</td>`;
                html += `<td>${user.email || '-'}</td>`;
                html += `<td><span class="badge ${user.rol === 'administrador' ? 'badge-admin' : 'badge-user'}">${user.rol}</span></td>`;
                html += `<td><span class="badge badge-microred">${user.microred || 'TODAS'}</span></td>`;
                html += `<td><span class="badge" style="background: ${user.activo == 1 ? '#28a745' : '#dc3545'}; color: white;">${estadoText}</span></td>`;
                html += `<td class="actions">
                    <button class="btn-edit" onclick="editarUsuario(${user.id})">✏️ Editar</button>
                    <button class="btn-delete" onclick="eliminarUsuario(${user.id})">🗑️ Eliminar</button>
                </td>`;
                html += '</tr>';
            });
            
            tbody.innerHTML = html;
        }

        // Actualizar estadísticas
        function actualizarStats(usuarios) {
            const total = usuarios.length;
            const admins = usuarios.filter(u => u.rol === 'administrador').length;
            const normales = usuarios.filter(u => u.rol === 'usuario').length;
            const activos = usuarios.filter(u => u.activo == 1).length;
            
            document.getElementById('totalUsuarios').textContent = total;
            document.getElementById('totalAdmins').textContent = admins;
            document.getElementById('totalUsuariosNormales').textContent = normales;
            document.getElementById('totalActivos').textContent = activos;
        }

        // Abrir modal para nuevo usuario
        function abrirModal() {
            document.getElementById('modalTitulo').textContent = 'Nuevo Usuario';
            document.getElementById('usuarioForm').reset();
            document.getElementById('usuarioId').value = '';
            document.getElementById('password').required = true;
            document.getElementById('passwordLabel').textContent = '*';
            document.getElementById('usuarioModal').classList.add('active');
        }

        // Cerrar modal
        function cerrarModal() {
            document.getElementById('usuarioModal').classList.remove('active');
        }

        // Editar usuario
        function editarUsuario(id) {
            fetch(`obtener_usuarios.php?id=${id}`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('modalTitulo').textContent = 'Editar Usuario';
                    document.getElementById('usuarioId').value = user.id;
                    document.getElementById('usuario').value = user.usuario;
                    document.getElementById('nombre_completo').value = user.nombre_completo;
                    document.getElementById('email').value = user.email || '';
                    document.getElementById('rol').value = user.rol;
                    document.getElementById('microred').value = user.microred || 'TODAS';
                    document.getElementById('activo').checked = user.activo == 1;
                    
                    // La contraseña no es requerida en edición
                    document.getElementById('password').required = false;
                    document.getElementById('passwordLabel').textContent = '(opcional)';
                    
                    document.getElementById('usuarioModal').classList.add('active');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar datos del usuario');
                });
        }

        // Guardar usuario
        function guardarUsuario() {
            const form = document.getElementById('usuarioForm');
            const formData = new FormData(form);
            
            // Validaciones básicas
            const usuario = formData.get('usuario');
            const nombre = formData.get('nombre_completo');
            const email = formData.get('email');
            const password = formData.get('password');
            const id = formData.get('id');
            
            if (!usuario || !nombre || !email) {
                alert('Por favor complete todos los campos obligatorios');
                return;
            }
            
            if (!id && (!password || password.length < 6)) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            // Enviar datos
            fetch('guardar_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    cerrarModal();
                    cargarUsuarios();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el usuario');
            });
        }

        // Eliminar usuario
        function eliminarUsuario(id) {
            if (!confirm('¿Está seguro de eliminar este usuario?')) {
                return;
            }
            
            fetch('eliminar_usuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    cargarUsuarios();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el usuario');
            });
        }

        // Buscar usuarios
        function buscarUsuarios() {
            const texto = document.getElementById('buscadorUsuario').value.toLowerCase();
            const filas = document.querySelectorAll('#tablaUsuarios tbody tr');
            
            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.includes(texto) || texto === '') {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>