## 1\. Resumen del Proyecto
Una aplicación llamada “valoración”  que puntee con estrellas del 1 al 5 las asistencias de los pacientes a los turnos que le asignaron los últimos 6 meses. Por ejemplo si un paciente en los últimos 6 meses tuvo 3 turnos pero solo vino uno tendrá 1 estrella de valoración. Estos datos los va a sacar de un API externa
## 2\. Objetivos Principales
•	Objetivo 1: Poder calcular cuantas veces asistió al hospital el paciente en los últimos 6 meses para poder calificarlo
•	Objetivo 2: Consultar todo con la API (Pacientes, Turnos, Estado del turno, Especialidad) y guardad las valoraciones en la base de datos local
•	Objetivo 3: Poder determinar si el paciente es un cliente que cumple la asistencia cuando saca un turno
## 3\. Actores y Roles
•	Administrador: Solo tendrán acceso al programa personas de estadísticas.
•	Autenticación de usuarios: Login para administradores.
•	Sincronización de Turnos: Un proceso (manual o automático) para obtener los turnos desde la API externa.
•	Interfaz de Valoración: Una pantalla para ver el detalle de un turno y asignar una calificación por estrellas (de 1 a 5) y añadir notas.
•	Listado de Turnos: Una vista principal que muestre los próximos turnos y los ya valorados, con filtros (por fecha, por paciente, etc.).
•	Base de Datos: Usar Firestore para almacenar las valoraciones asociadas a cada turno.
•	...
## 4\. Tecnologías y Plataforma
•	Backend/Base de Datos: Firebase (Firestore, Authentication).
•	Frontend: Es una aplicación web y necesito que usemos Bootstrap
•	Conexion con la base de datos
    powerbihu.servidorvirtual.com.ar
    puerto: 43306
    usr:  reportes
    clave:  readonly2024
## 5\. Estructura de tTablas en Base de datos


## 6\. Diseño y Experiencia de Usuario (UI/UX)

"Queremos un diseño limpio y profesional. Paleta de colores azules y blancos. La interfaz debe ser muy intuitiva y rápida de usar."

