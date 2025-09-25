// Variables globales
let pacienteId = null;

// Obtener elementos del DOM con los IDs correctos del HTML
const formUsuario = document.getElementById("form-usuario");
const formCita = document.getElementById("form-cita");
const listarCitas = document.getElementById("listar-citas");

// Función para mostrar toast
function mostrarToast(mensaje, esExito) {
    const toast = document.getElementById("toast");
    toast.textContent = mensaje;
    toast.className = esExito ? "toast-success" : "toast-error";
    toast.classList.remove("hidden");
    
    setTimeout(() => {
        toast.classList.add("hidden");
    }, 3000);
}

// Crear usuario/paciente
formUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const formData = new FormData(formUsuario);
    
    // Debug: mostrar datos en consola
    console.log("Enviando datos del paciente:");
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    try {
        const response = await fetch("crear_paciente.php", {
            method: "POST",
            body: formData
        });
        
        console.log("Status de respuesta:", response.status);
        
        // Verificar si la respuesta es OK
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        // Obtener el texto de la respuesta primero
        const responseText = await response.text();
        console.log("Respuesta cruda:", responseText);
        
        // Intentar parsear como JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error("Error al parsear JSON:", jsonError);
            console.error("Respuesta recibida:", responseText);
            throw new Error("El servidor no devolvió un JSON válido");
        }
        
        console.log("Datos parseados:", data);
        
        if (data.success) {
            mostrarToast(data.message, true);
            pacienteId = data.id;
            formCita.style.display = "block";
            formUsuario.reset();
            console.log("Paciente creado con ID:", pacienteId);
        } else {
            mostrarToast(data.message || "Error desconocido al registrar paciente", false);
        }
        
    } catch (err) {
        console.error("Error completo:", err);
        mostrarToast("Error al registrar paciente: " + err.message, false);
    }
});

// Agendar cita
formCita.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    if (!pacienteId) {
        mostrarToast("Primero debe registrar un paciente", false);
        return;
    }
    
    const formData = new FormData(formCita);
    formData.append('paciente_id', pacienteId);
    
    console.log("Enviando datos de la cita:");
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    try {
        const response = await fetch("crear_cita.php", {
            method: "POST",
            body: formData
        });
        
        const responseText = await response.text();
        console.log("Respuesta de cita:", responseText);
        
        const data = JSON.parse(responseText);
        
        if (data.success) {
            mostrarToast(data.message, true);
            formCita.reset();
            formCita.style.display = "none";
            pacienteId = null;
            cargarCitas(); // Recargar la lista de citas
        } else {
            mostrarToast(data.message || "Error al agendar cita", false);
        }
        
    } catch (err) {
        console.error("Error:", err);
        mostrarToast("Error al agendar cita: " + err.message, false);
    }
});

// Función para cargar y mostrar citas
async function cargarCitas() {
    try {
        const response = await fetch("listar_citas.php");
        const citas = await response.json();
        
        if (listarCitas) {
            listarCitas.innerHTML = "";
            
            if (citas.length === 0) {
                listarCitas.innerHTML = "<li>No hay citas registradas</li>";
                return;
            }
            
            citas.forEach(cita => {
                const li = document.createElement("li");
                li.innerHTML = `
                    <strong>${cita.paciente_nombre}</strong> - 
                    ${cita.fecha} a las ${cita.hora} - 
                    Dr. ${cita.odontologo} 
                    <span class="estado-${cita.estado}">(${cita.estado})</span>
                `;
                listarCitas.appendChild(li);
            });
        }
    } catch (err) {
        console.error("Error al cargar citas:", err);
    }
}

// Cargar citas al iniciar la página
document.addEventListener("DOMContentLoaded", () => {
    cargarCitas();
});