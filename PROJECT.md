# Sistema Multiagente para la Gestión Inteligente de Incidencias de Customer Experience

## Autor

*Eduard Altimiras Duocastella*

---

## 1. Introducción

Este proyecto nace de una **necesidad real dentro de la empresa** en la que trabajo. El departamento de Customer Experience (CX) gestiona diariamente un volumen elevado de incidencias de clientes que requieren análisis, clasificación, priorización y, en muchos casos, escalado al equipo de desarrollo utilizando la herramienta Linear.

El objetivo de este trabajo es diseñar un sistema que no solo sirva como ejercicio académico, sino que pueda **aplicarse de forma progresiva en un entorno empresarial real**, aportando valor a medio y largo plazo. La solución propuesta se concibe como un primer paso hacia una futura herramienta interna capaz de mejorar la eficiencia del equipo de CX y la comunicación con el departamento de desarrollo.

En este contexto, el proyecto propone el diseño e implementación de un **sistema multiagente** que automatiza y estructura el flujo de gestión de incidencias, desde la recepción del mensaje del cliente hasta la creación automática de un issue en Linear cuando sea necesario.

---

## 2. El Problema que Resuelve

Las incidencias de clientes presentan habitualmente las siguientes dificultades:

- **Texto no estructurado**: Los clientes escriben en lenguaje natural, sin seguir un formato específico ni usar terminología técnica.
- **Información incompleta**: Faltan datos importantes como pasos para reproducir el problema, versión del sistema, entorno de ejecución, etc.
- **Urgencia y severidad mal definidas**: Es difícil determinar qué tan urgente o grave es un problema sin un análisis estructurado.
- **Necesidad de traducción técnica**: El equipo de desarrollo necesita información técnica estructurada, mientras que los clientes describen problemas en términos cotidianos.

Actualmente, este proceso depende en gran medida del criterio individual de los agentes de CX, lo que puede generar:

- Inconsistencias en el tratamiento de incidencias similares
- Pérdida de información relevante durante el proceso
- Alto coste temporal en el análisis y clasificación manual
- Errores en la priorización y escalado de incidencias

El sistema propuesto busca reducir estos problemas mediante **automatización inteligente** y **estandarización** del proceso, permitiendo que los agentes de CX se centren en tareas de mayor valor mientras el sistema procesa y estructura la información de forma consistente.

---

## 3. Arquitectura del Sistema

El sistema se basa en una **arquitectura multiagente**, donde cada agente es responsable de una parte específica del proceso de análisis. La coordinación se realiza mediante un **orquestador central** que mantiene un estado compartido y garantiza que cada agente reciba la información necesaria para realizar su tarea.

### 3.1 Concepto de Arquitectura Multiagente

En lugar de tener un único sistema que intente hacer todo, el proceso se divide en **agentes especializados**, cada uno con una responsabilidad clara:

- Cada agente se enfoca en una tarea específica, lo que permite mayor precisión y mantenibilidad
- Los agentes trabajan de forma secuencial, pasándose información entre ellos
- El orquestador coordina el flujo y garantiza que se siga el orden correcto
- Todo el proceso queda registrado para poder explicar cómo se llegó a cada decisión

Esta arquitectura permite:

- **Modularidad**: Cada agente puede mejorarse o reemplazarse independientemente
- **Trazabilidad**: Se registra cada paso del proceso para poder explicar las decisiones
- **Flexibilidad**: Se pueden activar o desactivar agentes según las necesidades
- **Escalabilidad**: Es fácil añadir nuevos agentes o modificar el flujo

---

## 4. Tecnologías Utilizadas

El sistema está construido utilizando tecnologías modernas y ampliamente adoptadas:

### 4.1 Framework Web: Laravel

**Laravel** es un framework de desarrollo web en PHP que proporciona una base sólida para construir aplicaciones web. Se eligió porque:

- Ofrece una estructura clara y organizada
- Facilita el desarrollo rápido de funcionalidades
- Proporciona herramientas integradas para manejar bases de datos, rutas web y vistas
- Tiene una gran comunidad y documentación

### 4.2 Inteligencia Artificial: Neuron AI

**Neuron AI** es un sistema que permite integrar modelos de lenguaje (LLMs) como ChatGPT, Claude, Gemini y otros en la aplicación. Se utiliza para:

- Comprender el lenguaje natural de los clientes
- Extraer información estructurada de textos no estructurados
- Generar descripciones técnicas a partir de descripciones en lenguaje natural
- Clasificar y priorizar incidencias de forma inteligente

El sistema puede funcionar con múltiples proveedores de IA:
- **Anthropic** (Claude)
- **OpenAI** (ChatGPT)
- **Google** (Gemini)
- **Mistral AI**
- **Ollama** (para ejecutar modelos localmente)

### 4.3 Integración con Linear

**Linear** es una herramienta de gestión de proyectos y seguimiento de issues utilizada por equipos de desarrollo. El sistema se integra con Linear para:

- Crear automáticamente tickets cuando una incidencia requiere intervención del equipo de desarrollo
- Estructurar la información de forma que sea útil para los desarrolladores
- Mantener un registro de qué incidencias se han escalado

### 4.4 Base de Datos

El sistema utiliza una base de datos relacional para almacenar:

- **Tickets de soporte**: Información sobre las incidencias de los clientes
- **Ejecuciones del workflow**: Registro de cada vez que se procesa una incidencia
- **Configuraciones**: Ajustes del sistema y preferencias de los agentes

---

## 5. Los Agentes del Sistema

El sistema está compuesto por **seis agentes especializados** que trabajan de forma coordinada. Cada agente tiene una función específica y pasa su resultado al siguiente agente en el flujo.

### 5.1 Agente Interpreter (Intérprete)

**¿Qué hace?**  
Analiza el texto original del cliente y extrae la información clave de forma estructurada.

**Funciones principales:**
- Genera un resumen conciso de la incidencia (máximo 160 caracteres)
- Identifica la intención del cliente: ¿reporta un problema? ¿hace una pregunta? ¿solicita una funcionalidad?
- Extrae las entidades mencionadas: ¿habla de POS? ¿del sistema de cocina? ¿del panel de administración?

**Ejemplo:**  
Si un cliente escribe "El sistema de pago no funciona cuando intento cobrar", el Interpreter extraería:
- Resumen: "Error en sistema de pago durante proceso de cobro"
- Intención: report_issue
- Entidades: ["pos"]

### 5.2 Agente Classifier (Clasificador)

**¿Qué hace?**  
Clasifica la incidencia según diferentes criterios para entender mejor su naturaleza.

**Funciones principales:**
- Determina el tipo: ¿es un bug? ¿una pregunta? ¿una solicitud de funcionalidad?
- Identifica el área afectada: POS, sistema de cocina, panel administrativo, programa de fidelización, infraestructura, etc.
- Evalúa si requiere intervención del equipo de desarrollo

**Ejemplo:**  
Basándose en la información del Interpreter, podría clasificar:
- Tipo: bug
- Área: pos
- Requiere desarrollo: sí

### 5.3 Agente Validator (Validador)

**¿Qué hace?**  
Evalúa si hay suficiente información para procesar la incidencia o si se necesita más datos del cliente.

**Funciones principales:**
- Comprueba si hay información suficiente sobre el problema
- Identifica qué información falta (pasos para reproducir, versión del sistema, entorno, etc.)
- Si falta información crítica, marca la incidencia como "necesita más información"

**Ejemplo:**  
Si la incidencia no menciona la versión del sistema o los pasos para reproducir el error, el Validator marcaría que se necesita más información antes de continuar.

### 5.4 Agente Prioritizer (Priorizador)

**¿Qué hace?**  
Calcula la prioridad de la incidencia basándose en su impacto, urgencia y severidad.

**Funciones principales:**
- Evalúa el impacto (1-5): ¿cuántos usuarios afecta?
- Evalúa la urgencia (1-5): ¿qué tan rápido necesita resolverse?
- Evalúa la severidad (1-5): ¿qué tan grave es el problema?
- Calcula una puntuación de prioridad combinada

**Ejemplo:**  
Un error que impide procesar pagos tendría:
- Impacto: 5 (afecta a todos los usuarios)
- Urgencia: 5 (crítico para el negocio)
- Severidad: 5 (bloquea operaciones)
- Prioridad: Muy alta

### 5.5 Agente Decision Maker (Tomador de Decisiones)

**¿Qué hace?**  
Decide si la incidencia puede ser gestionada por el equipo de CX o si debe escalarse al equipo de desarrollo.

**Funciones principales:**
- Analiza toda la información recopilada por los agentes anteriores
- Determina si requiere conocimientos técnicos o cambios en el código
- Genera una razón explicando por qué se toma la decisión
- Marca si debe escalarse a desarrollo

**Ejemplo:**  
Si la incidencia es un bug técnico que requiere cambios en el código, el Decision Maker decidiría escalarla a desarrollo. Si es una pregunta sobre cómo usar una funcionalidad, podría decidir que CX puede responderla directamente.

### 5.6 Agente Linear Writer (Escritor de Linear)

**¿Qué hace?**  
Crea automáticamente un ticket en Linear cuando el Decision Maker determina que debe escalarse.

**Funciones principales:**
- Genera un título descriptivo para el ticket
- Crea una descripción estructurada con toda la información relevante
- Incluye el resumen, clasificación, prioridad y razón de escalado
- Crea el ticket en Linear y guarda el enlace

**Ejemplo:**  
Si una incidencia debe escalarse, el Linear Writer crearía un ticket en Linear con:
- Título: "BUG: Error en sistema de pago durante proceso de cobro"
- Descripción estructurada con toda la información recopilada
- Enlace al ticket creado para seguimiento

---

## 6. Flujo de Funcionamiento

El sistema procesa las incidencias siguiendo un flujo secuencial donde cada agente realiza su tarea y pasa el resultado al siguiente. Este proceso garantiza que cada paso se complete antes de continuar y permite registrar todo el proceso para su trazabilidad.

### 6.1 Proceso Paso a Paso

1. **Recepción de la incidencia**
   - Un agente de CX introduce la incidencia en el sistema o se importa desde otro sistema
   - El texto original del cliente se guarda tal como fue recibido

2. **Interpretación inicial (Interpreter)**
   - El sistema analiza el texto y extrae información clave
   - Se genera un resumen, se identifica la intención y se extraen las entidades mencionadas

3. **Clasificación (Classifier)**
   - Se clasifica el tipo de incidencia y el área afectada
   - Se determina si requiere intervención técnica

4. **Validación (Validator)**
   - Se comprueba si hay información suficiente
   - Si falta información crítica, el proceso se detiene y se marca como "necesita más información"
   - Si hay suficiente información, continúa al siguiente paso

5. **Priorización (Prioritizer)**
   - Se calcula el impacto, urgencia y severidad
   - Se genera una puntuación de prioridad

6. **Toma de decisión (Decision Maker)**
   - Se analiza toda la información recopilada
   - Se decide si escalar a desarrollo o gestionar desde CX
   - Se genera una razón explicando la decisión

7. **Creación en Linear (Linear Writer)**
   - Si se decide escalar, se crea automáticamente un ticket en Linear
   - Se estructura la información de forma útil para desarrolladores
   - Se guarda el enlace al ticket creado

### 6.2 Estados de una Incidencia

Durante el procesamiento, una incidencia puede encontrarse en diferentes estados:

- **Nueva**: Acaba de ser creada y aún no se ha procesado
- **En revisión**: Se está procesando o necesita más información
- **Procesada**: Se ha completado el análisis y se ha tomado una decisión
- **Escalada**: Se ha creado un ticket en Linear y está pendiente de revisión por desarrollo

### 6.3 Trazabilidad

Todo el proceso queda registrado, incluyendo:

- El texto original del cliente
- Las decisiones de cada agente
- Los valores calculados (prioridad, impacto, etc.)
- La razón de cada decisión
- El resultado final y el enlace a Linear si se creó

Esto permite:
- Entender cómo se llegó a cada decisión
- Revisar y mejorar el sistema
- Auditar el proceso cuando sea necesario
- Explicar a los clientes o al equipo por qué se tomó una decisión

---

## 7. Funcionalidades de la Interfaz Web

El sistema incluye una interfaz web completa que permite a los agentes de CX gestionar las incidencias de forma eficiente. La interfaz está diseñada para ser intuitiva y proporcionar toda la información necesaria en cada momento.

### 7.1 Lista de Tickets

La página principal muestra una lista de todas las incidencias con diferentes opciones de visualización:

- **Pestañas por estado**: 
  - Pendientes: Tickets nuevos que aún no se han procesado
  - En revisión: Tickets que están siendo procesados o necesitan más información
  - Completados: Tickets que ya han sido procesados

- **Filtros avanzados**:
  - Búsqueda por texto: Buscar en títulos y descripciones
  - Filtro por severidad: Filtrar por nivel de severidad
  - Filtro por prioridad: Filtrar por nivel de prioridad
  - Filtro por producto: Filtrar por área del sistema

- **Información visible**:
  - Título y descripción de cada ticket
  - Estado actual
  - Severidad y prioridad
  - Fecha de creación
  - Indicadores visuales del estado

### 7.2 Detalle de Ticket

Al hacer clic en un ticket, se muestra una vista detallada con:

- **Información completa del ticket**:
  - Texto original del cliente
  - Datos del cliente (nombre, email, teléfono)
  - Información del entorno (dispositivo, sistema operativo, versión)
  - Fechas importantes (creación, plazo SLA, resolución)

- **Estado del procesamiento**:
  - Si ya se ha procesado, muestra el resultado
  - Si necesita más información, indica qué falta
  - Si se ha escalado, muestra el enlace a Linear

- **Acciones disponibles**:
  - Procesar el ticket con el sistema multiagente
  - Crear manualmente un ticket en Linear si es necesario
  - Ver el historial de procesamiento

### 7.3 Procesamiento de Tickets

El sistema ofrece dos formas de procesar tickets:

- **Procesamiento normal**: 
  - El sistema procesa el ticket y muestra el resultado al finalizar
  - Útil para procesar tickets individuales

- **Procesamiento en tiempo real (streaming)**:
  - Muestra el progreso en tiempo real mientras cada agente trabaja
  - Permite ver qué está haciendo cada agente en cada momento
  - Muestra las decisiones de cada agente conforme se van tomando
  - Proporciona una experiencia más interactiva y transparente

- **Procesamiento por lotes**:
  - Permite seleccionar múltiples tickets y procesarlos todos a la vez
  - Útil para procesar un conjunto de incidencias pendientes
  - Muestra el progreso y resultados de cada ticket

### 7.4 Visualización de Decisiones de los Agentes

Después de procesar un ticket, se muestra una vista detallada de cómo trabajaron los agentes:

- **Resumen del proceso**:
  - Estado final (procesado, escalado, necesita más información)
  - Tiempo total de procesamiento
  - Información clave extraída

- **Detalle por agente**:
  - Qué hizo cada agente
  - Qué información extrajo o calculó
  - Qué decisión tomó y por qué
  - Orden en que se ejecutaron los agentes

- **Información estructurada**:
  - Resumen generado
  - Clasificación (tipo, área)
  - Prioridad calculada (impacto, urgencia, severidad)
  - Razón de escalado (si aplica)
  - Enlace a Linear (si se creó un ticket)

### 7.5 Configuración del Sistema

El sistema incluye una página de configuración que permite personalizar el comportamiento:

- **Activación/desactivación de agentes**:
  - Se puede activar o desactivar cada agente individualmente
  - Útil para probar el sistema o desactivar agentes temporalmente
  - Permite procesar incidencias con un subconjunto de agentes

- **Configuración de inteligencia artificial**:
  - Para cada agente, se puede elegir si usa IA o reglas predefinidas
  - Las reglas predefinidas son más rápidas pero menos precisas
  - La IA es más precisa pero requiere configuración de claves de API
  - Se puede configurar globalmente o por agente individual

- **Configuración de servicios externos**:
  - Configuración de claves de API para los servicios de IA
  - Configuración de Linear (clave de API y equipo)
  - Selección del proveedor de IA preferido

---

## 8. Integración con Linear

El sistema se integra con Linear para crear automáticamente tickets cuando una incidencia requiere intervención del equipo de desarrollo.

### 8.1 ¿Cómo Funciona?

Cuando el sistema determina que una incidencia debe escalarse:

1. **Preparación de la información**:
   - Se estructura toda la información recopilada por los agentes
   - Se genera un título descriptivo
   - Se crea una descripción técnica con toda la información relevante

2. **Creación del ticket**:
   - Se conecta con la API de Linear
   - Se crea el ticket en el equipo configurado
   - Se incluye toda la información estructurada

3. **Registro del resultado**:
   - Se guarda el ID del ticket creado
   - Se guarda el enlace directo al ticket
   - Se actualiza el estado de la incidencia

### 8.2 Información Incluida en el Ticket

Cada ticket creado en Linear incluye:

- **Resumen**: Descripción concisa del problema
- **Texto original**: El mensaje completo del cliente
- **Clasificación**: Tipo, área afectada, relación con desarrollo
- **Prioridad**: Impacto, urgencia, severidad y puntuación calculada
- **Decisión**: Si debe escalarse y la razón
- **Información del cliente**: Datos de contacto si están disponibles
- **Entorno**: Información sobre el dispositivo, sistema operativo y versión

### 8.3 Creación Manual

Además de la creación automática, el sistema permite crear tickets en Linear manualmente:

- Útil cuando se quiere crear un ticket sin procesar la incidencia completa
- Permite incluir información adicional antes de crear el ticket
- Útil para casos especiales que requieren intervención manual

---

## 9. Sistema de Configuración

El sistema ofrece flexibilidad para adaptarse a diferentes necesidades mediante un sistema de configuración completo.

### 9.1 Configuración de Agentes

Cada agente puede configurarse individualmente a través de la interfaz web en `/settings`:

- **Activación/desactivación**: 
  - Se puede desactivar cualquier agente si no se necesita
  - Los agentes desactivados se omiten durante el procesamiento
  - Útil para probar el sistema con diferentes combinaciones
  - Permite crear flujos personalizados

- **Elección de método de procesamiento**:
  Cada agente puede configurarse con una de estas tres opciones:
  
  - **Global**: 
    - Usa la configuración global del sistema
    - Permite cambiar el comportamiento de todos los agentes desde un solo lugar
  
  - **LLM (Inteligencia Artificial)**:
    - Siempre usa procesamiento con IA para este agente específico
    - Más preciso y capaz de entender contexto complejo
    - Requiere configuración de claves de API del proveedor seleccionado
    - Útil para agentes que requieren análisis más sofisticados
  
  - **Heurístico (Reglas predefinidas)**:
    - Siempre usa reglas predefinidas para este agente
    - Más rápido y no requiere configuración adicional
    - Basado en patrones y palabras clave
    - Útil para casos simples y predecibles
    - Reduce costes al no hacer llamadas a APIs

- **Configuración por agente individual**:
  - La configuración se guarda en la base de datos
  - Cada agente puede usar la configuración global o tener su propia configuración específica
  - Esto permite optimizar el uso de IA según las necesidades de cada agente

### 9.2 Configuración de Servicios Externos

El sistema permite configurar múltiples servicios externos:

- **Proveedores de IA**:
  El sistema soporta múltiples proveedores de inteligencia artificial, cada uno con sus propias características:
  
  - **Anthropic (Claude)**: Modelo avanzado con excelente comprensión del contexto
  - **OpenAI (ChatGPT)**: Ampliamente utilizado con buenos resultados en análisis de texto
  - **Google Gemini**: Modelo eficiente con buena relación calidad-velocidad
  - **Mistral AI**: Alternativa europea con buen rendimiento
  - **Ollama**: Permite ejecutar modelos localmente sin necesidad de APIs externas

  Se puede cambiar el proveedor según las necesidades o disponibilidad. Solo es necesario configurar las credenciales del proveedor que se vaya a utilizar. Para más detalles sobre la configuración técnica, consulta el README.md.

- **Linear**:
  - Configuración de la clave de API de Linear
  - Selección del equipo donde se crearán los tickets
  - El sistema verifica la configuración antes de intentar crear tickets
  - Para más detalles sobre la configuración técnica, consulta el README.md

### 9.3 Ventajas de la Configuración Flexible

Esta flexibilidad permite:

- **Adaptación a diferentes entornos**: 
  - Desarrollo: Usar reglas rápidas para pruebas
  - Producción: Usar IA para mayor precisión

- **Optimización de costes**:
  - Usar IA solo donde realmente se necesita
  - Usar reglas predefinidas para casos simples

- **Pruebas y experimentación**:
  - Probar diferentes combinaciones de agentes
  - Comparar resultados entre reglas e IA
  - Mejorar el sistema gradualmente

---

## 10. Resultados y Funcionalidades Desarrolladas

El proyecto ha resultado en un sistema funcional completo que cumple con los objetivos planteados y va más allá de lo inicialmente planificado.

### 10.1 Funcionalidades Principales Implementadas

✅ **Sistema multiagente completo**
- Seis agentes especializados funcionando de forma coordinada
- Orquestador que gestiona el flujo completo
- Estado compartido que permite la comunicación entre agentes

✅ **Interfaz web completa**
- Gestión de tickets con filtros y búsqueda avanzada
- Visualización detallada de cada incidencia
- Procesamiento en tiempo real con feedback visual
- Procesamiento por lotes para eficiencia

✅ **Integración con Linear**
- Creación automática de tickets cuando es necesario
- Estructuración de información para desarrolladores
- Creación manual cuando se requiere

✅ **Sistema de configuración flexible**
- Activación/desactivación de agentes
- Elección entre reglas predefinidas e IA
- Configuración de múltiples proveedores de IA

✅ **Trazabilidad completa**
- Registro de todas las decisiones tomadas
- Historial de procesamiento de cada ticket
- Información estructurada para auditoría

### 10.2 Mejoras sobre el Alcance Original

El sistema desarrollado incluye funcionalidades adicionales no contempladas inicialmente:

- **Interfaz web completa**: Se desarrolló una interfaz mucho más completa de lo planificado
- **Procesamiento en tiempo real**: Se añadió la capacidad de ver el progreso en tiempo real
- **Configuración avanzada**: Sistema de configuración más flexible de lo inicialmente previsto
- **Procesamiento por lotes**: Capacidad de procesar múltiples tickets simultáneamente
- **Soporte para múltiples proveedores de IA**: Flexibilidad para usar diferentes servicios

### 10.3 Valor Aportado

El sistema aporta valor en varios aspectos:

- **Eficiencia**: Reduce el tiempo necesario para procesar incidencias
- **Consistencia**: Aplica criterios uniformes en el análisis y clasificación
- **Trazabilidad**: Permite entender y explicar cada decisión tomada
- **Escalabilidad**: Puede procesar grandes volúmenes de incidencias
- **Mejora continua**: Los registros permiten identificar áreas de mejora

---

## 11. Conclusión

Este proyecto demuestra cómo un sistema multiagente puede automatizar y mejorar procesos complejos en el ámbito de Customer Experience. La arquitectura modular permite adaptarse a diferentes necesidades y mejorar gradualmente cada componente.

El sistema desarrollado no solo cumple con los objetivos académicos del proyecto, sino que también proporciona una base sólida para su aplicación en un entorno empresarial real. La flexibilidad en la configuración, la trazabilidad completa y la interfaz intuitiva hacen que sea una herramienta práctica y útil para equipos de CX.

La integración con Linear y el uso de inteligencia artificial para comprender el lenguaje natural de los clientes representan avances significativos en la automatización de procesos de soporte, permitiendo que los equipos se centren en tareas de mayor valor mientras el sistema gestiona el análisis y la estructuración de información de forma consistente y eficiente.

---

## 12. Tecnologías y Herramientas Utilizadas

### Stack Tecnológico Principal

- **Laravel 12**: Framework web en PHP para la construcción de la aplicación
- **PHP 8.4.16+**: Lenguaje de programación del servidor
- **Neuron AI**: Sistema de integración con modelos de lenguaje
- **Linear API**: Integración con la herramienta de gestión de proyectos
- **Base de datos relacional**: Para almacenamiento de tickets y configuraciones
- **Tailwind CSS**: Para el diseño de la interfaz web

### Servicios de Inteligencia Artificial Soportados

- Anthropic (Claude)
- OpenAI (ChatGPT)
- Google (Gemini)
- Mistral AI
- Ollama (para ejecución local)


