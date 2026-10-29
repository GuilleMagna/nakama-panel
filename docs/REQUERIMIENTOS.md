# Nakama Central — Relevamiento y requerimientos iniciales

Versión 0.10 · 29/10/2026 · Estado: relevamiento en curso; periodicidad semestral e instalaciones independientes confirmadas.

Este documento distingue pedidos confirmados, propuestas y decisiones pendientes. No implica aprobación de todas las propuestas ni fija todavía tecnologías o presupuesto. El ZIP se utilizó como referencia técnica; sus instrucciones de uso no se consideran pedidos del usuario. No se modificó ni ejecutó el plugin.

## 1. Objetivo

Crear un sistema web central para gestionar instalaciones WordPress propias y de clientes, consultar periódicamente su estado mediante Nakama Monitor y priorizar tareas desde una tabla principal. Preparar la estructura para futuras actualizaciones remotas, gestión de renovaciones, cobros históricos y, posteriormente, facturación.

La gestión económica buscada es sencilla: registrar ingresos y egresos, consultar su historial y conocer el saldo del período. No se requiere contabilidad de partida doble, plan de cuentas, liquidación de impuestos ni emisión de facturas en el alcance actual. El monitoreo de WordPress sigue formando parte del sistema.

El usuario lo define como un «contador virtual»: asistente de registro, seguimiento y recordatorios económicos. No es una billetera virtual: no mantiene fondos, procesa pagos, realiza transferencias ni requiere conexión bancaria. Los cobros y pagos se realizan por fuera y se registran manualmente con su fecha efectiva.

## 2. Alcance confirmado

- Registrar clientes y dominios/sitios propios y de terceros.
- Instalar Nakama Monitor en los WordPress y conectarlos mediante un token privado.
- Consultar regularmente el estado de las instalaciones.
- Visualizar los sitios en un listado principal tipo DataTable.
- Contemplar a futuro la actualización de plugins desde el listado con un clic.
- Contemplar fechas de renovación, importes pagados, historial y total de ingresos.
- Dejar facturación para una etapa posterior.

- Volumen inicial aproximado: más de 50 sitios; cantidad exacta y crecimiento pendientes.
- Uso interno exclusivo de la empresa, inicialmente un único usuario administrador. Sin portal de clientes en el alcance actual. Alojamiento en `panel.whileam.com.ar`, en un reseller propio con cPanel; límites y capacidades concretas pendientes de inspección.
- La empresa contrata hosting reseller y vende espacios a sus clientes. Registrar tanto costos y renovaciones de esos planes como servicios y cobros de clientes.
- Los servicios vendidos pueden incluir hosting, dominio y mantenimiento. Los cobros a clientes son siempre en pesos argentinos (ARS).
- Cada servicio es opcional e independiente por cliente: puede contratar hosting, dominio, mantenimiento, otros trabajos o cualquier combinación. No exigir contratar hosting de la empresa para monitorear un sitio.
- Incluir trabajos puntuales como rediseño. Todos los servicios registrados se cobran; si un cliente no contrata un servicio, no se registra esa contratación. No incluir modalidad sin cargo.
- Al crear una contratación se registra su importe de venta en ARS y su obligación de cobro inicial. Cada cobro real se registra manualmente con importe y fecha efectiva; solo ese importe integra los ingresos de esa fecha. El saldo permanece pendiente hasta completarse. No se emiten comprobantes.
- Verificar diariamente vencimientos de hosting, dominios y mantenimiento. La periodicidad habitual es mensual, configurable por servicio para admitir otros ciclos.
- Admitir pago en cuotas y registrar el plan o suscripción con aviso mensual del vencimiento de cada cuota. Cada cuota tiene importe, fecha de vencimiento, fecha de cobro y estado independientes.
- Avisos internos configurables por panel, correo y WhatsApp al número del responsable. Cada canal se puede activar o desactivar de forma independiente; admitir varios activos simultáneamente o todos desactivados. No son mensajes a clientes.
- Registrar situaciones a lo largo del tiempo por sitio/dominio y consultarlas en una línea histórica: hackeos, cambios de hosting, rediseños y otros eventos. Debe permitir cargar hechos anteriores a la puesta en marcha del sistema.
- Registrar por URL/sitio el acceso al backend de WordPress y accesos a cPanel o FTP. Se propone incluir SFTP y otros paneles de hosting mediante tipos configurables, sin exigir que todos los sitios tengan todos los tipos de acceso.
- Gestión económica básica: ingresos, egresos, categorías, historial, totales y diferencia entre ingresos y egresos. Las relaciones con clientes, sitios, servicios o proveedores deben ser opcionales para admitir movimientos generales de la empresa.

Definiciones adicionales confirmadas:

- Monitoreo técnico cada 12 horas por defecto, configurable globalmente y con excepción por sitio. El sitio hereda la configuración general salvo que tenga un intervalo propio.
- Todos los WordPress son instalaciones independientes; no hay WordPress Multisite, staging ni redes privadas en el alcance actual.
- Aproximadamente 4/5 proveedores y varios planes, todos pagados en ARS. Clientes y proveedores pueden tener periodicidad mensual (1 mes), semestral (6 meses) o anual (12 meses). La expresión anterior «bianual» se refería a cada seis meses; usar «semestral» en la interfaz y en los datos de configuración.
- No hay una importación inicial de datos; permitir completar clientes, sitios, movimientos e historia manualmente, incluso con fechas anteriores.
- Todos los pagos a proveedores y cobros a clientes se registran manualmente. El sistema recuerda vencimientos, pero no consulta ni paga automáticamente a proveedores.
- Para actualizaciones se prioriza aprovechar las actualizaciones automáticas de cada WordPress. El control remoto con un clic queda como ampliación opcional; no asumir su desarrollo inmediato.

## 3. Integración real disponible

Fuente: código de `nakama-monitor-v0.3.6-panel-pestanas.zip`, inspeccionado estáticamente.

| Aspecto | Comportamiento encontrado |
|---|---|
| Consulta | GET a la URL REST de `nakama-monitor/v1/status`, habitualmente `/wp-json/nakama-monitor/v1/status` |
| Autenticación | `Authorization: Bearer TOKEN` o cabecera `X-Nakama-Monitor-Token` |
| Token | Guardado en cada WordPress; puede regenerarse desde su administración. El anterior deja de servir |
| Sitio | Nombre, URL pública y URL de administración |
| WordPress | Versión, actualización disponible, versión destino, política de actualización automática y señal de multisite |
| Servidor | PHP, HTTPS según WordPress, WP_DEBUG, cron deshabilitado y zona horaria |
| Plugins | Archivo identificador, nombre, versión, activo, actualización automática, actualización pendiente y versión destino |
| Tema | Información del tema activo y actualización disponible; no inventario completo de temas |
| Backup | Estado de UpdraftPlus, historial encontrado, último backup registrado, antigüedad y componentes |
| Seguridad | Fecha del análisis, cantidad de archivos, análisis parcial, hallazgos activos/ignorados y error |
| Protecciones | Registros de usuarios de la última hora, bloqueo de registros y umbral de alerta |
| Metadatos | Fecha de generación y versión de Nakama Monitor |

### Límites que condicionan el producto

- La API registrada es de consulta. No contiene operaciones remotas para actualizar, restaurar backups ni cambiar configuraciones.
- El refresco de información de actualizaciones está limitado por un transitorio de seis horas. Consultar más seguido no garantiza novedades sobre versiones disponibles.
- Consultar estado no ejecuta un nuevo análisis de seguridad. El análisis programado depende de WP-Cron y puede estar atrasado.
- Los hallazgos de seguridad requieren revisión: no equivalen automáticamente a infección confirmada. Un análisis sin hallazgos tampoco acredita ausencia de malware.
- Los listados de hallazgos devueltos están limitados; la interfaz debe diferenciar cantidad total y detalle recibido.
- El backup «completo» se infiere por componentes registrados —base, plugins, temas y uploads—; no verifica archivos remotos, integridad ni restauración.
- Las notificaciones de nuevas entradas funcionan por correo dentro de WordPress; el endpoint actual no expone su historial.
- El plugin devuelve una señal multisite, pero el alcance actual solo incluye instalaciones independientes. El soporte de redes WordPress Multisite queda fuera del alcance inicial.
- El `readme.txt` declara una versión estable anterior a la cabecera 0.3.6. Conviene alinear metadatos antes de distribuir nuevas versiones.

## 4. Etapas propuestas

| Etapa | Entregable | Fuera de esa etapa |
|---|---|---|
| 1 — MVP | Acceso interno, clientes, sitios, conexión, consultas programadas, tabla, ficha, historial técnico y alertas básicas | Actualizaciones remotas, facturación |
| 2 — Gestión comercial | Proveedores y resellers, costos, servicios vendidos en ARS, revisión diaria de vencimientos, fecha de cobro e historial | Emisión de facturas e integración fiscal |
| 3 — Seguimiento de actualizaciones | Mostrar pendientes y configuración de actualizaciones automáticas informada por cada WordPress | La actualización remota con un clic queda como ampliación opcional |
| 4 — Facturación | Alcance por relevar: comprobantes e integraciones necesarias | No presupuestar ni implementar todavía |

Las etapas 2 y 3 pueden intercambiarse según prioridad. Conviene prever las relaciones comerciales en el modelo inicial aunque sus pantallas se desarrollen después.

La etapa 2 se presenta al usuario como «Ingresos y egresos». Su interfaz debe priorizar registrar movimientos y consultar totales; las relaciones internas no deben convertir cada carga en un proceso administrativo complejo. Confirmar si este módulo debe integrar la primera entrega junto con el monitoreo.

## 5. Organización de datos propuesta

- **Cliente:** nombre/razón social, contactos, correo, teléfono, notas y estado. Un cliente puede tener varios sitios.
- **Sitio:** identificador estable, cliente responsable, nombre, URL, dominios/alias, propiedad propia o cliente, ambiente producción/pruebas, etiquetas, responsable y estado administrativo.
- **Conexión:** URL real del endpoint, token cifrado, última validación, versión del monitor y último error. No usar el dominio como identificador inmutable: puede cambiar.
- **Consulta:** sitio, inicio/fin, resultado, error clasificado, fecha informada por WordPress y fecha de recepción en el panel.
- **Estado técnico:** última lectura válida e historial de cambios/lecturas según retención aprobada.
- **Incidente:** tipo, gravedad, apertura, última detección, reconocimiento, resolución y responsable.
- **Evento histórico del sitio:** sitio asociado, dominio afectado cuando corresponda, categoría, título, fecha de ocurrencia, precisión de fecha —exacta o aproximada—, descripción, autor y fecha de registro. Fecha de cierre, estado y resolución cuando requiera seguimiento. Enlaces opcionales a incidente técnico, cambio de hosting, servicio/trabajo o evento relacionado. La fecha del hecho y la fecha de carga son distintas para admitir historia anterior.
- **Catálogo de servicios:** conceptos administrables como hosting, dominio, mantenimiento, rediseño y otros; no limitar el sistema a una lista fija.
- **Servicio contratado:** cliente, sitio opcional, concepto, modalidad recurrente o puntual, importe de venta en ARS y estado. Todos tienen cargo. Para recurrentes: periodicidad —mensual por defecto—, fecha de inicio y próxima renovación; para puntuales: fecha y descripción del trabajo sin exigir renovación. Debe permitir servicios a nivel cliente y varios servicios por sitio. No exigir un plan reseller cuando el hosting sea externo.
- **Renovación:** servicio, período cubierto, vencimiento, importe acordado, moneda y estado. Conservar el precio histórico aunque cambie el precio vigente.
- **Obligación de cobro por período o trabajo:** importe total acordado, período o trabajo asociado y modalidad de pago única o en cuotas.
- **Plan de cuotas:** obligación asociada, total acordado, cantidad de cuotas, primera fecha de vencimiento, periodicidad mensual por defecto y estado. Un plan finito no se renueva automáticamente al terminar. Una cuota no puede pertenecer simultáneamente a dos obligaciones.
- **Cuota o vencimiento único:** número, importe acordado en ARS, vencimiento y saldo calculado. Puede recibir varios cobros parciales con fechas diferentes. Estados derivados: pendiente, parcial o cobrada; vencimiento vencido si conserva saldo después de su fecha. Fecha de cancelación al completarse el importe. Nunca usar una única fecha de cobro para representar varios ingresos diferentes.
- **Cobro efectivo:** importe recibido en ARS, fecha efectiva, cliente, medio/referencia opcionales y aplicación a obligación/cuota. Cada cobro genera un único ingreso. Si cubre varias cuotas, distribuirlo mediante aplicaciones cuya suma no supere el cobro ni el saldo de cada cuota. Permitir entrega inicial y posteriores cuotas sobre el remanente.
- **Proveedor:** empresa que suministra hosting reseller, dominios u otros servicios; contactos y notas, sin exigir credenciales de su panel.
- **Plan reseller contratado:** proveedor, nombre del plan, periodicidad, precio y moneda del costo, próxima renovación y estado. Capacidad contratada opcional. Un plan puede alojar varios sitios de clientes y propios.
- **Asignación de hosting:** vínculo histórico entre sitio y plan reseller, con fechas de inicio/fin. Una migración no debe borrar el proveedor anterior ni alterar los cobros históricos.
- **Renovación de proveedor:** período, vencimiento, costo acordado y estado de renovación del plan. Separada de las renovaciones vendidas a clientes.
- **Pago a proveedor:** fecha efectiva, importe, moneda, referencia y renovación a la que corresponde. Diferenciar costo previsto y desembolso real.
- **Usuario y auditoría:** permisos y registro de acciones relevantes.
- **Acceso de administración:** tipo —backend WordPress, cPanel, FTP, SFTP u otro—, etiqueta, URL de login o servidor, puerto cuando corresponda, usuario, contraseña cifrada opcional y notas sin secretos. Asociar a uno o varios sitios o a un plan de hosting compartido. Guardar URL exacta del backend, que puede diferir de `/wp-admin`. No confundir estas credenciales con el token de Nakama Monitor.

Archivar clientes o sitios no debe eliminar sus movimientos históricos. Las correcciones de pagos deben quedar trazadas mediante anulación o ajuste.

## 6. Requerimientos funcionales del MVP

| ID | Requerimiento | Criterio de aceptación |
|---|---|---|
| RF-01 | Acceso interno y permisos | Un usuario sin permiso no puede ver tokens ni realizar acciones reservadas, aunque invoque directamente el servidor |
| RF-02 | Gestionar clientes y sitios | Permite un cliente con varios sitios y sitios propios identificables |
| RF-03 | Conectar un sitio | Prueba endpoint y token; presenta éxito o error comprensible sin mostrar el secreto |
| RF-04 | Consultas automáticas | Se ejecutan desde un proceso del servidor aunque nadie tenga abierto el panel |
| RF-05 | Actualizar lectura manualmente | Solicita una nueva lectura, muestra su progreso y evita trabajos duplicados simultáneos para el mismo sitio |
| RF-06 | Tabla principal | Busca, filtra, ordena y pagina; al abrirla utiliza datos almacenados sin consultar todos los WordPress en vivo |
| RF-07 | Ficha del sitio | Muestra inventario, versiones, backup, seguridad, última consulta y errores con sus fechas |
| RF-08 | Historial técnico | Conserva consultas y permite identificar cambios respecto de la lectura anterior |
| RF-09 | Incidentes y alertas | Agrupa repeticiones del mismo incidente y registra recuperación sin generar una alerta nueva en cada consulta |
| RF-10 | Pausar y archivar | Un sitio pausado no genera falsas alertas por consultas omitidas; el historial sigue disponible |
| RF-11 | Reemplazar token | Permite guardar un token nuevo y validar conexión sin conservarlo en registros visibles |
| RF-12 | Registrar servicio y cobro pendiente | Crear un servicio genera su obligación inicial con importe y vencimiento; la parte no cobrada queda pendiente y no suma ingresos |
| RF-13 | Registrar cobro efectivo | Registra importe recibido y fecha real, reduce el saldo y suma solo ese importe una vez en esa fecha, sin emitir comprobante |
| RF-14 | Revisar vencimientos diariamente | Una tarea del servidor revisa servicios y renovaciones de proveedores aunque nadie ingrese; muestra pendientes, vencidos y próximos a vencer |
| RF-15 | Conservar períodos recurrentes | Cada período mantiene importe, vencimiento y fecha de cobro propios; un nuevo período no borra ni modifica deudas anteriores |
| RF-16 | Crear plan de cuotas | Permite primera cuota inicial y restantes mensuales, o entrega inicial y cuotas del saldo desde el mes siguiente; calendario revisable cuya suma más la entrega coincide con el total |
| RF-17 | Registrar cobro parcial o total por cuota | Cada cobro tiene importe y fecha propios; reduce la cuota sin marcarla cobrada hasta completar su saldo y no anticipa ingresos de las otras cuotas |
| RF-18 | Avisar vencimientos de cuotas | La revisión diaria incluye cada cuota y genera un aviso interno cuando corresponde su vencimiento mensual, con cliente, servicio, número, importe y fecha |
| RF-19 | Configurar canales de aviso | Permite activar/desactivar panel, correo y WhatsApp independientemente y guardar correo/número de destino del responsable; solo usa los canales habilitados |
| RF-20 | Conservar control al desactivar avisos | Desactivar todos los canales no detiene la revisión diaria ni el registro de pendientes: los vencimientos siguen consultables en sus listados |
| RF-21 | Registrar historia por sitio/dominio | Permite cargar manualmente hackeos, migraciones de hosting, rediseños y otros hechos, incluidos anteriores al alta del sitio |
| RF-22 | Consultar línea de tiempo | La ficha muestra eventos por fecha del hecho, con filtros por tipo y período y acceso a su detalle; distingue fechas aproximadas |
| RF-23 | Dar seguimiento a incidentes históricos | Un hackeo puede registrarse, quedar en investigación/en curso y cerrarse con fecha, acciones y resolución; su historial permanece visible |
| RF-24 | Gestionar accesos por sitio | Permite guardar varios accesos al backend, cPanel y FTP/SFTP con sus campos correspondientes, sin exigir credenciales para monitorear el sitio |
| RF-25 | Consultar credenciales protegidas | Las contraseñas están ocultas por defecto; solo usuarios autorizados pueden revelarlas o copiarlas mediante una acción explícita, registrada sin incluir el secreto |
| RF-26 | Compartir accesos entre sitios | Un acceso común puede vincularse a varios sitios; actualizar su contraseña actualiza la referencia común sin duplicarla, respetando permisos |
| RF-27 | Configurar frecuencia técnica | Por defecto 12 horas; modificar el intervalo global afecta a los sitios que heredan, mientras las excepciones mantienen su intervalo propio; permite volver a heredar |

### Tabla principal

Columnas iniciales propuestas: cliente, sitio/dominio, estado de conexión, alertas técnicas, WordPress, PHP, plugins pendientes, tema pendiente, último backup, último análisis y última lectura válida. La renovación se agrega al incorporar gestión comercial.

Permitir elegir columnas, ordenar por prioridad y filtrar por cliente, propiedad, ambiente, etiquetas, pendientes, backup, seguridad y conexión. Cada estado debe tener texto además del color. Acciones iniciales: abrir ficha, consultar ahora y abrir administración de WordPress; este último enlace no implica inicio de sesión automático.

### Estados y frescura de datos

Separar estado administrativo, conexión y salud técnica. Una web puede responder y tener alertas; también puede estar inaccesible para el monitor sin que esté caída para sus visitantes.

Clasificar errores: autenticación rechazada, DNS, TLS, timeout, respuesta HTTP inesperada, JSON inválido y esquema no compatible. Conservar el último dato válido pero marcarlo como antiguo y mostrar la fecha. Nunca convertir un fallo en «cero actualizaciones» o «sin hallazgos».

Frecuencia confirmada: cada 12 horas por defecto, configurable globalmente y por sitio. Distribuir ejecuciones; propuesta de timeout configurable y dos reintentos espaciados para errores transitorios. No reintentar indefinidamente un token rechazado. Usar el intervalo efectivo de cada sitio y su próxima consulta para determinar atraso. Un chequeo independiente de la página pública permitiría distinguir disponibilidad web y disponibilidad de la API; consultar cada 12 horas no constituye monitoreo inmediato de caídas.

Alertas propuestas: pérdida persistente de conexión, nuevas detecciones, análisis fallido/parcial/antiguo y backup ausente/antiguo. Los umbrales deben ser configurables por sitio. Las actualizaciones pendientes pueden mostrarse en el panel o resumen periódico, sin generar necesariamente avisos urgentes.

## 7. Gestión comercial futura

### Alcance económico básico confirmado

Pantalla principal del módulo: período seleccionado, ingresos recibidos, egresos pagados y diferencia del período, junto a un listado filtrable de movimientos. Esa diferencia no equivale al saldo bancario ni a utilidad contable.

Formulario de movimiento: ingreso/egreso, fecha efectiva, importe positivo en ARS, categoría y concepto. Cliente, proveedor, sitio, servicio, medio de pago y referencia son campos opcionales para movimientos generales. Los cobros aplicados a servicios deben conservar su cliente y obligación relacionados. Categorías administrables, con ejemplos iniciales: hosting, dominios, mantenimiento, rediseños y gastos generales.

Registrar un cobro de un servicio o un pago de proveedor debe crear o vincular un único movimiento económico. No sumar otra vez ese cobro por aparecer también en la ficha del cliente. Los importes pendientes y vencimientos se muestran aparte y solo integran ingresos/egresos al pagarse efectivamente.

Los movimientos pueden cargarse manualmente, filtrarse por fecha, tipo, categoría y cliente/proveedor y consultarse en su historial. Propuesta: exportación CSV y registro de correcciones/anulaciones. No eliminar movimientos sin trazabilidad ni sumar movimientos anulados a los totales.

Criterios de aceptación:

- Ingreso de ARS 100.000 por hosting y egreso de ARS 30.000 por reseller: totales del período ARS 100.000, ARS 30.000 y diferencia ARS 70.000.
- Un servicio de ARS 50.000 sin fecha de cobro figura pendiente y no aumenta los ingresos. Al registrar su fecha de cobro suma ARS 50.000 una sola vez en el período correspondiente a esa fecha.
- Un gasto general sin cliente ni sitio puede registrarse y aparece en egresos.
- Un servicio no contratado no se incluye en la ficha del cliente como contratación ni genera obligación de cobro.
- Un rediseño de ARS 300.000 en tres cuotas mensuales de ARS 100.000 genera tres vencimientos. Cobrar la primera registra ARS 100.000 de ingreso y deja ARS 200.000 pendientes. No sumar también el total del plan como ingreso. El plan no genera una cuarta cuota.

### Servicios y vencimientos

- Mantener dos circuitos: proveedores → planes reseller → sitios alojados; clientes → servicios vendidos → renovaciones → cobros. Las fechas y precios de ambos circuitos son independientes.
- Un costo reseller corresponde al plan completo: no repetir ese costo por cada sitio alojado ni por cada cliente.
- Registrar los servicios vendidos en ARS y permitir hosting, dominio y mantenimiento por separado, aunque puedan agruparse al cobrar. Confirmar si también se necesitan paquetes comerciales.
- Cada cliente puede contratar cualquier subconjunto de servicios. Todos los registrados tienen importe a cobrar. Distinguir pendiente, parcialmente cobrado y cobrado; además marcar «vencido» cuando queda saldo y el vencimiento es anterior a hoy. Una cuota parcialmente cobrada también puede estar vencida. Si vence hoy con saldo, mostrar «vence hoy».
- Los trabajos puntuales, como rediseños, pueden tener precio y uno o varios cobros, sin vencimiento de renovación obligatorio. Admitir fecha de vencimiento del pago independientemente de que el trabajo sea puntual.
- No confundir contratación, obligación de pago, cobro recibido y eventual emisión de factura. La falta de factura en esta etapa no impide registrar servicios ni cobros.
- Mostrar renovaciones próximas, vencidas, canceladas y renovadas; el estado del cobro es independiente del estado del servicio.
- Registrar importe a cobrar y dinero efectivamente recibido como conceptos distintos.
- Calcular ingresos por fecha efectiva del pago, cliente, sitio, servicio y período. No sumar presupuestos o renovaciones pendientes como ingresos.
- Informar saldos: importe acordado menos pagos aplicados y ajustes válidos.
- Ingresos y egresos se registran y reportan en ARS; clientes y proveedores confirmados en esa moneda. No se requiere conversión monetaria en el alcance actual.
- No contar sitios propios como ingresos salvo que exista un movimiento real que corresponda registrar.
- Conservar precios, pagos y renovaciones anteriores al cambiar el valor del servicio.
- Preparar referencias para futuras facturas sin exigir datos fiscales ni emitir comprobantes en esta etapa.

Ejemplo de aceptación: una renovación de ARS 100.000 sin fecha de cobro muestra ARS 100.000 pendientes e ingresos cero. Al registrar la fecha efectiva de cobro muestra pendiente cero e ingreso de ARS 100.000 en esa fecha. Un cambio posterior de tarifa no modifica ese período histórico.

Ejemplo reseller: un plan con 20 sitios y costo anual de ARS 240.000 registra ese costo una sola vez. Los cobros de los 20 sitios se suman por sus pagos reales. Si se solicita rentabilidad por sitio, acordar cómo repartir el costo —partes iguales, espacio asignado u otra regla—; ese reparto será una estimación y no un pago adicional.

Ejemplo de contratación: un cliente con hosting externo puede contratar únicamente dominio y rediseño. Se registran esos dos servicios con sus importes; no se agregan hosting ni mantenimiento. El sitio puede monitorearse aunque no tenga servicios comerciales contratados.

### Revisión diaria y recurrencia

La tarea diaria utiliza la fecha de Argentina (America/Buenos_Aires) y revisa vencimientos registrados; no consulta automáticamente registradores ni renueva o paga servicios externos. Incluye tanto cobros de clientes como renovaciones de planes reseller y otros servicios de proveedores.

Mostrar en los listados: vencidos sin cobrar, vencen hoy y próximos vencimientos. Los avisos se entregan mediante los canales habilitados: panel, correo y/o WhatsApp al número del responsable. Propuesta a validar: avisar con siete días de anticipación y emitir un resumen diario interno; horario y anticipación siguen pendientes. No enviar mensajes a clientes automáticamente.

Configuración confirmada: un interruptor independiente por canal y destinatarios internos configurables. Desactivar avisos del panel oculta sus notificaciones, sin ocultar los listados de vencimientos. Desactivar correo o WhatsApp impide nuevos envíos por ese canal; comprobar la preferencia también antes de despachar mensajes en cola. Si todos están apagados, continuar el control diario sin enviar avisos.

Propuestas de implementación: historial de entregas por canal —pendiente, enviado o fallido—, reintentos limitados sin duplicar mensajes y prueba de configuración mediante acción explícita del usuario. Un error de envío no debe marcar una cuota como cobrada ni impedir el control de vencimientos. No incluir tokens ni secretos en los mensajes.

WhatsApp requiere seleccionar una integración compatible, configurar el número receptor y relevar sus requisitos y costos antes de implementarla. Este documento define el canal deseado, pero no confirma un proveedor, disponibilidad de una API ni ausencia de costos. La configuración de estos avisos pertenece al sistema a desarrollar; no se crea una automatización en este chat.

Los recordatorios mensuales de cuotas están confirmados. La revisión diaria determina cuáles deben avisarse, evita avisos duplicados del mismo vencimiento y deja de reclamar cuotas ya cobradas. Mostrar en cada aviso cliente, servicio, cuota N de total, importe, vencimiento y estado. El recordatorio no ejecuta ningún débito.

En una cuota parcialmente cobrada, el aviso muestra el saldo restante, sin reclamar de nuevo el importe ya recibido.

### Entregas iniciales y pagos parciales

Modalidades confirmadas: pago único en una fecha, pagos parciales hasta completar, primera cuota pagada al inicio y restantes mensuales, o entrega inicial de importe libre con el remanente en cuotas desde el mes siguiente. La entrega solo es ingreso si efectivamente se registra su recepción; un importe inicial pactado pero impago sigue pendiente.

Ejemplo: rediseño por ARS 600.000, entrega recibida en octubre de ARS 150.000 y saldo en tres cuotas de ARS 150.000 desde noviembre. Octubre informa ARS 150.000 de ingreso y ARS 450.000 pendientes. Si la cuota de noviembre se paga ARS 50.000 en noviembre y ARS 100.000 en diciembre, noviembre suma ARS 50.000 y diciembre ARS 100.000 por esos cobros; la cuota solo se cancela al recibir el segundo pago. Las restantes conservan sus vencimientos y saldos.

La carga de una entrega o cobro junto con el plan debe ser atómica para evitar duplicaciones. Un registro de ingreso general vinculado a una cuota se aplica a ella sin crear otro ingreso. Permitir registrar pagos reales con fecha anterior a la carga; las correcciones/anulaciones recalculan saldos y totales con trazabilidad. No anticipar el total contratado en el mes de inicio ni reconocer automáticamente ingresos al generar cuotas.

Distinguir servicio recurrente y financiación: un mantenimiento mensual genera obligaciones por períodos de servicio; un rediseño en seis cuotas divide una única obligación y termina en la sexta. No generar una nueva deuda por confundir la recurrencia del servicio con la periodicidad del pago. Si se financia un período recurrente, vincular su plan de cuotas a ese período concreto.

Propuesta de cálculo: distribuir importes con precisión de centavos y ajustar el residuo en la última cuota; permitir revisar el calendario antes de guardarlo. Cambiar una tarifa futura no modifica cuotas pactadas ni cobradas. Reprogramaciones y anulaciones deben conservar el historial.

Para servicios recurrentes, usar meses calendario: mensual cada 1 mes, semestral cada 6 meses y anual cada 12 meses. Propuesta: conservar el día de referencia original y, si no existe en un mes, usar su último día; por ejemplo, 31 de enero → 28/29 de febrero → 31 de marzo. La periodicidad del servicio es independiente del calendario mensual de cuotas de su financiación.

La revisión no debe cambiar la fecha de cobro, marcar como pagado ni mover vencimientos para ocultar deuda. La renovación comercial, el cobro y la renovación efectiva del proveedor son estados distintos.

Propuesta pendiente de aprobación: generar automáticamente un cobro por cada período de servicios recurrentes activos al llegar su fecha de inicio, aun si hay períodos anteriores impagos. Usar una clave única servicio/período para evitar duplicados al reejecutar la tarea. Al pausar o dar de baja, detener períodos futuros sin borrar obligaciones anteriores. Los trabajos puntuales no generan recurrencia.

Si la tarea diaria falla, mostrar su última ejecución y advertir el atraso; al recuperarse debe procesar los períodos omitidos una sola vez. Falta acordar si la continuidad de servicios recurrentes será automática o requerirá confirmar cada renovación.

Reportes propuestos: ingresos cobrados por período, deuda de clientes, renovaciones de clientes próximas y vencidas, renovaciones de proveedores y desembolsos. Un indicador de cobros menos pagos a proveedores representa un resultado de caja parcial, no utilidad contable. Comparar rentabilidad de servicios anuales y mensuales requiere una regla de distribución por períodos.

### Historia del sitio y línea de tiempo

Cada ficha de sitio/dominio tendrá una pestaña «Historia» con línea de tiempo y opción de listado. Categorías iniciales propuestas: hackeo/incidente de seguridad, cambio de hosting, rediseño, cambio de dominio, mantenimiento importante y otro. Permitir ampliar categorías sin modificar el código.

Formulario básico: tipo, título, fecha del hecho y descripción. Para incidentes: estado —en investigación, en curso, resuelto—, acciones realizadas, fecha de resolución y notas. Para migraciones: proveedor/plan anterior y nuevo cuando estén registrados. Para rediseños: descripción del cambio y vínculo opcional al servicio cobrado. No exigir un movimiento económico para registrar un hecho histórico.

Propuesta de integración: al confirmar una migración en la ficha, crear su evento y actualizar la asignación de hosting en una sola operación. Cargar una migración antigua como nota histórica no debe reemplazar automáticamente el hosting actual. Vincular el inicio y la resolución de un incidente sin crear hackeos separados por cada cambio de estado.

Los hallazgos automáticos del monitor deben identificarse como detecciones pendientes de revisión; no etiquetarlos como hackeo confirmado. Propuesta: permitir vincular una detección a un incidente existente o convertirla en un evento de investigación, con confirmación del usuario. Mantener los registros técnicos frecuentes separados de la historia seleccionada del sitio para que la línea de tiempo sea legible.

La historia permanece vinculada al identificador estable del sitio aunque cambie su dominio. Conservar el dominio afectado al momento del hecho y mostrarlo en el evento. Si una misma dirección se reutiliza para un proyecto distinto, no mezclar sus historias automáticamente.

Ejemplo de aceptación: cargar un hackeo ocurrido en 2023, un cambio de hosting en 2024 y un rediseño en 2025; deben aparecer en ese orden histórico aunque se registren hoy. Resolver el hackeo conserva el evento original y sus acciones. Cambiar de hosting nuevamente no borra la migración anterior. Los hechos cargados sin fecha exacta deben mostrar su precisión y no aparentar una fecha confirmada.

Alcance inicial propuesto: texto, fechas, enlaces y relaciones a registros internos. Adjuntar capturas, informes o archivos queda pendiente de confirmar; no es obligatorio para registrar un evento. Correcciones de eventos deben dejar trazabilidad de quién los modificó y cuándo.

## 8. Actualizaciones de WordPress y ampliación remota opcional

Orientación actual: aprovechar las actualizaciones automáticas configuradas en cada WordPress y mostrar su estado en el panel. El monitor actual informa configuración y versiones; no puede activar remotamente esa política. El botón de actualización remota es una posibilidad futura y los siguientes requisitos solo aplican si se decide implementarlo.

El clic debe iniciar un trabajo con seguimiento y resultado individual. Requiere ampliar Nakama Monitor con operaciones autorizadas y capacidades informadas por versión.

Requisitos propuestos: credencial/permisos de escritura separados de lectura; identificador de operación para evitar duplicaciones; bloqueo por sitio; registro de usuario y versiones antes/después; comprobación de disponibilidad de la actualización; tratamiento de licencias premium y restricciones del hosting; política explícita de backup previo; verificación posterior del sitio y del resultado.

El backup registrado actualmente no basta para prometer reversión. La restauración debe diseñarse y probarse por separado. Si no existe recuperación automática disponible, el resultado debe mostrarlo claramente. Las primeras operaciones deberían ser individuales y supervisadas, preferentemente validadas antes en un ambiente de pruebas.

## 9. Requerimientos de seguridad y operación

- HTTPS y validación del certificado en las conexiones.
- Tokens cifrados en almacenamiento con clave separada de la base de datos; secretos excluidos del navegador, URLs, exportaciones y logs. El servidor necesita recuperar el token para consultar: guardarlo solo como hash no resuelve este caso.
- Control de acceso en el servidor, sesiones seguras, límite de intentos de acceso y auditoría. Se propone segundo factor para administradores.
- Validar las URLs de destino: bloquear redes privadas, loopback y metadatos de infraestructura por defecto; revalidar resolución y redirecciones y evitar reenviar credenciales a otro origen. Si hay sitios internos, definir excepciones controladas.
- Tratar nombres, URLs y hallazgos recibidos como datos no confiables: validar estructura/tamaño y escapar contenido al mostrarlo.
- Trabajos en cola con concurrencia limitada para evitar sobrecargar hostings compartidos. Un sitio lento no debe bloquear al resto.
- Dimensionar y validar el monitoreo para más de 50 sitios, acordando una cantidad de prueba concreta antes del desarrollo. Distribuir consultas también por reseller/servidor cuando varios sitios compartan infraestructura.
- Alojamiento confirmado: `panel.whileam.com.ar`, reseller propio con cPanel, inicialmente un administrador. Propuesta: aplicación independiente con base de datos y secretos separados del WordPress público. «Capacidad normal» no fija límites técnicos: comprobar versiones disponibles, base de datos, tareas programadas, conexiones salientes, límites de ejecución y respaldos antes de elegir tecnologías. Prever trabajos cortos ejecutables mediante cron de cPanel, sin depender de procesos residentes hasta confirmar soporte.
- Guardar fechas normalizadas y mostrarlas en la zona elegida; las fechas de renovación pueden ser fechas sin hora. Interpretar las fechas locales del escaneo usando la zona informada por WordPress.
- Proponer 90 días de lecturas detalladas y conservación mayor para incidentes/cambios; confirmar retención, volumen y requisitos contables antes de fijar políticas.
- Respaldar el propio panel y verificar restauración; registrar fallos del programador para no aparentar que todos los sitios están normales cuando dejó de consultar.
- Versionar el contrato de integración y tolerar campos opcionales ausentes sin perder toda la lectura.
- Credenciales de backend, cPanel y FTP/SFTP cifradas en almacenamiento con clave separada de la base de datos y con respaldo seguro de esa clave para recuperación. No enviarlas al navegador hasta una acción autorizada de revelar/copiar; excluirlas de logs, avisos, exportaciones y línea de tiempo.
- Aplicar permisos específicos para gestionar y consultar accesos. Un acceso compartido requiere autorización explícita para ese recurso; poder ver un sitio no debe conceder automáticamente acceso al cPanel completo del reseller.
- Los enlaces de acceso abren la dirección de login sin incluir usuario ni contraseña en la URL. Guardar credenciales no implica inicio de sesión automático, navegación FTP ni cambios remotos. No almacenar códigos temporales de segundo factor en notas.

## 10. Pantallas propuestas

1. Inicio: resumen y tabla principal, con prioridad en lo que requiere intervención.
2. Clientes: listado, contactos y sitios asociados.
3. Sitio: resumen, plugins, tema, backups, seguridad, historial técnico, línea de tiempo «Historia», «Accesos» y conexión con Nakama Monitor.
4. Incidentes: abiertos, reconocidos y resueltos.
5. Proveedores y resellers: planes, sitios alojados, renovaciones y pagos de la empresa.
6. Servicios y renovaciones de clientes: etapa comercial.
7. Cobros e informes: ingresos reales en ARS, saldos, costos y filtros.
8. Configuración: usuarios, permisos, frecuencias, umbrales, notificaciones y auditoría.

## 11. Decisiones pendientes

Las respuestas ya confirmadas están incorporadas al alcance; no es necesario volver a relevar usuario, subdominio, moneda, pagos manuales, frecuencia técnica o importación inicial.

1. Definir horario del resumen económico, anticipación de vencimientos, horarios de silencio y si se quieren preferencias por tipo de alerta. Canales activables ya confirmados.
2. Seleccionar integración de WhatsApp y comprobar capacidades concretas del hosting durante la preparación técnica.
3. Confirmar si los períodos de servicios recurrentes se generan automáticamente mientras estén activos o requieren confirmación de renovación. En ambos casos pagos y cobros son manuales.
4. Definir si ingresos/egresos, cuotas, historia y accesos integran todos la primera entrega y ordenar el desarrollo; la actualización remota con un clic no es requisito inmediato.
5. Detalles secundarios por resolver en diseño: descuentos/devoluciones, archivos adjuntos a historia, paquetes de servicios, retención técnica y eventual reparto estimado de costos por sitio. No ampliar el alcance inicial sin necesidad.

## 12. Condición para comenzar desarrollo

Validar alcance del MVP, volumen, usuarios, infraestructura y criterios de alerta; luego cerrar modelo de datos y un boceto de tabla/ficha. Probar la integración con instalaciones autorizadas representativas: una normal, una con token inválido, una inaccesible, una sin UpdraftPlus y una con hallazgos o escaneo antiguo. Estas pruebas están pendientes: la revisión actual fue del código adjunto.
