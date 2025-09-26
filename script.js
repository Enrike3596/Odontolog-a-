// Variables globales
let pacienteSeleccionado = null;

// Elementos del DOM
const formUsuario = document.getElementById("form-usuario");
const moduloRegistro = document.getElementById("modulo-registro");
const moduloAgendar = document.getElementById("modulo-agendar");
const buscarPacienteInput = document.getElementById("buscar-paciente");
const listaPacientes = document.getElementById("lista-pacientes");
const formCita = document.getElementById("form-cita");
const listarCitas = document.getElementById("listar-citas");
const moduloCitas = document.getElementById("modulo-citas");

// Toast con Tailwind
function mostrarToast(mensaje, exito = true) {
    const toast = document.getElementById("toast");
    toast.textContent = mensaje;
    toast.className = `fixed bottom-5 right-5 z-50 px-6 py-4 rounded-lg min-w-[200px] text-center ${exito ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
    toast.classList.remove("hidden");
    setTimeout(() => {
        toast.classList.add("hidden");
    }, 3000);
}

// Registrar paciente
formUsuario.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(formUsuario);
    try {
        const response = await fetch("crear_paciente.php", {
            method: "POST",
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarToast("Paciente registrado exitosamente", true);
            formUsuario.reset();
            moduloRegistro.classList.add("hidden");
            moduloAgendar.classList.remove("hidden");
            cargarPacientes();
        } else {
            mostrarToast(data.message || "Error al registrar paciente", false);
        }
    } catch (err) {
        mostrarToast("Error al registrar paciente", false);
    }
});

// Buscar y mostrar pacientes
async function cargarPacientes(filtro = "") {
    try {
        const response = await fetch("listar_pacientes.php");
        const pacientes = await response.json();
        listaPacientes.innerHTML = "";
        let filtrados = pacientes;
        if (filtro) {
            filtrados = pacientes.filter(p =>
                p.nombre.toLowerCase().includes(filtro.toLowerCase()) ||
                p.documento.toLowerCase().includes(filtro.toLowerCase())
            );
        }
        if (filtrados.length === 0) {
            listaPacientes.innerHTML = '<li class="text-gray-400 px-2 py-1">No se encontraron pacientes</li>';
            return;
        }
        filtrados.forEach(p => {
            const li = document.createElement("li");
            li.className = "hover:bg-blue-50 cursor-pointer px-2 py-1";
            li.textContent = `${p.nombre} (${p.documento})`;
            li.onclick = () => seleccionarPaciente(p);
            listaPacientes.appendChild(li);
        });
    } catch (err) {
        listaPacientes.innerHTML = '<li class="text-red-500 px-2 py-1">Error al cargar pacientes</li>';
    }
}

buscarPacienteInput.addEventListener("input", (e) => {
    cargarPacientes(e.target.value);
});

function seleccionarPaciente(paciente) {
    pacienteSeleccionado = paciente;
    document.getElementById("paciente_id").value = paciente.id;
    formCita.classList.remove("hidden");
    mostrarToast(`Paciente seleccionado: ${paciente.nombre}`, true);
}

// Agendar cita para paciente seleccionado
formCita.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!pacienteSeleccionado) {
        mostrarToast("Seleccione un paciente primero", false);
        return;
    }
    const formData = new FormData(formCita);
    formData.set('paciente_id', pacienteSeleccionado.id);
    try {
        const response = await fetch("crear_cita.php", {
            method: "POST",
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarToast("Cita agendada exitosamente", true);
            formCita.reset();
            formCita.classList.add("hidden");
            pacienteSeleccionado = null;
            cargarCitas();
        } else {
            mostrarToast(data.message || "Error al agendar cita", false);
        }
    } catch (err) {
        mostrarToast("Error al agendar cita", false);
    }
});

// Mostrar citas asignadas
async function cargarCitas() {
    try {
        const response = await fetch("listar_citas.php");
        const citas = await response.json();
        listarCitas.innerHTML = "";
        if (!Array.isArray(citas) || citas.length === 0) {
            listarCitas.innerHTML = '<li class="text-gray-400 px-2 py-1">No hay citas registradas</li>';
            return;
        }
        citas.forEach(cita => {
            const li = document.createElement("li");
            li.className = "flex flex-col md:flex-row md:items-center justify-between px-2 py-2 hover:bg-gray-50";
            li.innerHTML = `
                <span><span class="font-semibold text-blue-700">${cita.paciente || cita.paciente_nombre || ''}</span> - ${cita.fecha} a las ${cita.hora}</span>
                <span class="text-sm text-gray-500">Dr. ${cita.odontologo} <span class="ml-2 px-2 py-1 rounded bg-blue-100 text-blue-700">${cita.estado || 'pendiente'}</span></span>
            `;
            listarCitas.appendChild(li);
        });
    } catch (err) {
        listarCitas.innerHTML = '<li class="text-red-500 px-2 py-1">Error al cargar citas</li>';
    }
}

// Inicialización
document.addEventListener("DOMContentLoaded", () => {
    cargarCitas();
    cargarPacientes();
    // Mostrar solo el módulo de registro al inicio
    moduloRegistro.classList.remove("hidden");
    moduloAgendar.classList.add("hidden");
    formCita.classList.add("hidden");
});