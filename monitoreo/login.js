document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const usuario = document.getElementById('usuario').value.trim();
    const password = document.getElementById('password').value;
    const recordar = document.querySelector('input[name="recordar"]').checked;
    const btnLogin = document.getElementById('btnLogin');
    const btnText = btnLogin.querySelector('span');
    const spinner = btnLogin.querySelector('.spinner');
    const mensajeError = document.getElementById('mensajeError');
    
    // Validaciones
    if (!usuario || !password) {
        mostrarError('Por favor complete todos los campos');
        return;
    }
    
    // Mostrar loading
    btnLogin.disabled = true;
    btnText.style.opacity = '0.7';
    spinner.style.display = 'inline-block';
    mensajeError.style.display = 'none';
    
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
        
        if (data.success) {
            // Login exitoso
            mostrarExito('Iniciando sesión...');
            
            // Guardar datos si es necesario
            if (data.usuario) {
                localStorage.setItem('ultimo_usuario', data.usuario.nombre);
            }
            
            // Redirigir al dashboard
            setTimeout(() => {
                window.location.href = data.redirect || 'dashboard.php';
            }, 500);
        } else {
            // Login fallido
            mostrarError(data.message || 'Usuario o contraseña incorrectos');
            
            // Animar campos
            document.getElementById('usuario').classList.add('error');
            document.getElementById('password').classList.add('error');
            
            setTimeout(() => {
                document.getElementById('usuario').classList.remove('error');
                document.getElementById('password').classList.remove('error');
            }, 500);
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
    mensajeError.textContent = mensaje;
    mensajeError.style.display = 'block';
    mensajeError.style.background = '#f8d7da';
    mensajeError.style.color = '#721c24';
    mensajeError.style.border = '1px solid #f5c6cb';
}

function mostrarExito(mensaje) {
    const mensajeError = document.getElementById('mensajeError');
    mensajeError.textContent = mensaje;
    mensajeError.style.display = 'block';
    mensajeError.style.background = '#d4edda';
    mensajeError.style.color = '#155724';
    mensajeError.style.border = '1px solid #c3e6cb';
}

function togglePassword() {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
}

function recuperarPassword() {
    document.getElementById('recuperarModal').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('recuperarModal').style.display = 'none';
}

function enviarRecuperacion() {
    const email = document.getElementById('emailRecuperar').value.trim();
    
    if (!email) {
        alert('Por favor ingrese su correo electrónico');
        return;
    }
    
    // Aquí iría la lógica para enviar el email
    alert('Se han enviado las instrucciones a su correo electrónico');
    cerrarModal();
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('recuperarModal');
    if (event.target === modal) {
        cerrarModal();
    }
}

// Cargar último usuario si existe
document.addEventListener('DOMContentLoaded', function() {
    const ultimoUsuario = localStorage.getItem('ultimo_usuario');
    if (ultimoUsuario) {
        document.getElementById('usuario').value = ultimoUsuario;
    }
    
    // Animar entrada
    document.querySelector('.login-box').style.animation = 'fadeIn 0.5s ease';
});