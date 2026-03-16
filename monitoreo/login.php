<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Chochua - Iniciar Sesión</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(145deg, #0a2f3a 0%, #1e5f7a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Elementos decorativos de fondo */
        .bg-decoration {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }

        .bg-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .bg-circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
        }

        .bg-circle:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
        }

        .bg-circle:nth-child(3) {
            width: 150px;
            height: 150px;
            bottom: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.03);
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-box {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-header h1 {
            font-size: 2.8em;
            margin-bottom: 10px;
            letter-spacing: 2px;
            position: relative;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .login-header p {
            font-size: 1.1em;
            opacity: 0.95;
            position: relative;
            font-weight: 300;
        }

        .login-form {
            padding: 40px 35px;
            background: white;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.95em;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        .form-group.focused label {
            color: #0f3c4c;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 14px 45px 14px 15px;
            border: 2px solid #e0e7ef;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0f3c4c;
            background: white;
            box-shadow: 0 8px 20px -10px rgba(15, 60, 76, 0.4);
            transform: translateY(-2px);
        }

        .form-group input.error {
            border-color: #dc3545;
            animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-group input:focus + .input-icon {
            color: #0f3c4c;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            color: #64748b;
            transition: all 0.3s ease;
            z-index: 2;
            padding: 5px;
        }

        .toggle-password:hover {
            color: #0f3c4c;
            transform: translateY(-50%) scale(1.1);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: 0.95em;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .remember:hover {
            color: #0f3c4c;
        }

        .remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0f3c4c;
            border-radius: 4px;
        }

        .forgot-link {
            color: #0f3c4c;
            text-decoration: none;
            font-size: 0.95em;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 20px;
            background: rgba(15, 60, 76, 0.1);
        }

        .forgot-link:hover {
            background: #0f3c4c;
            color: white;
            transform: translateY(-2px);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 1px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -10px rgba(15, 60, 76, 0.6);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #dc2626;
            font-size: 0.95em;
            text-align: center;
            animation: slideIn 0.3s ease;
            display: none;
        }

        .success-message {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #16a34a;
            font-size: 0.95em;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-footer {
            background: #f1f5f9;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .login-footer p {
            color: #475569;
            font-size: 0.9em;
        }

        .login-footer a {
            color: #0f3c4c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #1e5f7a;
            text-decoration: underline;
        }

        /* Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 450px;
            width: 90%;
            position: relative;
            animation: modalSlideUp 0.4s ease;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 30px;
            cursor: pointer;
            color: #94a3b8;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #0f3c4c;
            background: #f1f5f9;
            transform: rotate(90deg);
        }

        .modal-content h2 {
            color: #0f3c4c;
            margin-bottom: 15px;
            font-size: 1.8em;
        }

        .modal-content p {
            color: #475569;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .modal-content input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .modal-content input:focus {
            outline: none;
            border-color: #0f3c4c;
            box-shadow: 0 8px 20px -10px rgba(15, 60, 76, 0.3);
        }

        .btn-recuperar {
            background: linear-gradient(135deg, #0f3c4c 0%, #1e5f7a 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            letter-spacing: 1px;
        }

        .btn-recuperar:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -10px rgba(15, 60, 76, 0.5);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-header {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 2.2em;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .forgot-link {
                align-self: flex-start;
            }
        }

        /* Animación de carga */
        .loading-dots {
            display: inline-block;
        }

        .loading-dots::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
            width: 0;
            display: inline-block;
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
    <div class="bg-decoration">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>El Chochua</h1>
                <p>Sistema de Monitoreo de Salud Infantil</p>
            </div>
            
            <form id="loginForm" class="login-form" method="POST" autocomplete="off">
                <div class="form-group" id="usuarioGroup">
                    <label for="usuario">Usuario</label>
                    <div class="input-wrapper">
                        <input type="text" 
                               id="usuario" 
                               name="usuario" 
                               placeholder="Ingrese su usuario"
                               required 
                               autocomplete="off">
                        <span class="input-icon">👤</span>
                    </div>
                </div>
                
                <div class="form-group" id="passwordGroup">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Ingrese su contraseña"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <label class="remember">
                        <input type="checkbox" name="recordar" id="recordar">
                        <span>Recordarme</span>
                    </label>
                    <a href="#" class="forgot-link" onclick="recuperarPassword(event)">¿Olvidó su contraseña?</a>
                </div>
                
                <div id="mensajeError" class="error-message" style="display: none;"></div>
                
                <button type="submit" class="btn-login" id="btnLogin">
                    <span>Ingresar</span>
                    <div class="spinner" style="display: none;"></div>
                </button>
            </form>
            
            <div class="login-footer">
                <p>© 2026 El Chochua - Todos los derechos reservados</p>
                <p style="margin-top: 5px; font-size: 0.85em;">
                    Desarrollado por <a href="#">YONI - PACHECO Xd</a>
                </p>
            </div>
        </div>
        
        <!-- Modal para recuperar contraseña -->
        <div id="recuperarModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="cerrarModal()">&times;</span>
                <h2>Recuperar Contraseña</h2>
                <p>Ingrese su correo electrónico registrado para recibir instrucciones de recuperación.</p>
                <input type="email" id="emailRecuperar" placeholder="correo@ejemplo.com" autocomplete="off">
                <button onclick="enviarRecuperacion()" class="btn-recuperar">Enviar instrucciones</button>
            </div>
        </div>
    </div>
    
    <script>
        // Animación de focus en los inputs
        document.querySelectorAll('.form-group input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-group').classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-group').classList.remove('focused');
            });
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el icono
            const toggleBtn = document.querySelector('.toggle-password');
            toggleBtn.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        }

        // Recuperar password
        function recuperarPassword(e) {
            e.preventDefault();
            document.getElementById('recuperarModal').style.display = 'flex';
        }

        // Cerrar modal
        function cerrarModal() {
            document.getElementById('recuperarModal').style.display = 'none';
            document.getElementById('emailRecuperar').value = '';
        }

        // Enviar recuperación
        function enviarRecuperacion() {
            const email = document.getElementById('emailRecuperar').value.trim();
            
            if (!email) {
                alert('Por favor ingrese su correo electrónico');
                return;
            }
            
            if (!email.includes('@') || !email.includes('.')) {
                alert('Por favor ingrese un correo electrónico válido');
                return;
            }
            
            // Simular envío
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = 'Enviando...';
            btn.disabled = true;
            
            setTimeout(() => {
                alert('Se han enviado las instrucciones a su correo electrónico');
                btn.textContent = originalText;
                btn.disabled = false;
                cerrarModal();
            }, 1500);
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('recuperarModal');
            if (event.target === modal) {
                cerrarModal();
            }
        }

        // Login form submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value;
            const recordar = document.getElementById('recordar').checked;
            const btnLogin = document.getElementById('btnLogin');
            const btnText = btnLogin.querySelector('span');
            const spinner = btnLogin.querySelector('.spinner');
            const mensajeError = document.getElementById('mensajeError');
            
            // Validaciones
            if (!usuario || !password) {
                mostrarError('Por favor complete todos los campos');
                if (!usuario) document.getElementById('usuario').classList.add('error');
                if (!password) document.getElementById('password').classList.add('error');
                return;
            }
            
            // Mostrar loading
            btnLogin.disabled = true;
            btnText.style.opacity = '0.7';
            spinner.style.display = 'inline-block';
            mensajeError.style.display = 'none';
            
            // Remover clases de error
            document.getElementById('usuario').classList.remove('error');
            document.getElementById('password').classList.remove('error');
            
            try {
                const formData = new FormData();
                formData.append('usuario', usuario);
                formData.append('password', password);
                formData.append('recordar', recordar);
                
                const response = await fetch('validar_login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('Respuesta:', data);
                
                if (data.success) {
                    // Login exitoso
                    mensajeError.className = 'success-message';
                    mensajeError.textContent = 'Iniciando sesión' + '...';
                    mensajeError.style.display = 'block';
                    
                    // Guardar usuario si se marcó recordar
                    if (recordar) {
                        localStorage.setItem('ultimo_usuario', usuario);
                    }
                    
                    // Redirigir
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    // Login fallido
                    mostrarError(data.message || 'Usuario o contraseña incorrectos');
                    document.getElementById('usuario').classList.add('error');
                    document.getElementById('password').classList.add('error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión. Intente nuevamente.');
            } finally {
                // Ocultar loading
                btnLogin.disabled = false;
                btnText.style.opacity = '1';
                spinner.style.display = 'none';
            }
        });

        function mostrarError(mensaje) {
            const mensajeError = document.getElementById('mensajeError');
            mensajeError.className = 'error-message';
            mensajeError.textContent = mensaje;
            mensajeError.style.display = 'block';
            
            // Auto ocultar después de 5 segundos
            setTimeout(() => {
                mensajeError.style.display = 'none';
            }, 5000);
        }

        // Cargar último usuario si existe
        document.addEventListener('DOMContentLoaded', function() {
            const ultimoUsuario = localStorage.getItem('ultimo_usuario');
            if (ultimoUsuario) {
                document.getElementById('usuario').value = ultimoUsuario;
                document.getElementById('recordar').checked = true;
            }
            
            // Animación de entrada
            document.querySelector('.login-box').style.animation = 'fadeInUp 0.6s ease-out';
        });
    </script>
</body>
</html>