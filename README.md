# Plugin Proyectos Grid para WordPress

Plugin personalizado para WordPress que permite crear y mostrar proyectos en una grilla responsive con administración completa.

## Características

### Custom Post Type y Taxonomías
- **Custom Post Type**: "Proyectos" con soporte completo
- **Taxonomía jerárquica**: Categorías de proyecto
- **Taxonomía no jerárquica**: Etiquetas de proyecto
- **Campos personalizados**: Valor, moneda individual, enlace personalizado

### Administración
- Menú administrativo completo: "Proyectos" > "Todos los proyectos", "Añadir nuevo", "Configuración"
- Página de configuración con:
  - Moneda global (USD, EUR, CLP, ARS, MXN)
  - Enlace base global para botones de contacto

### Lógica de Precios y Enlaces
- Campo "valor" solo acepta números
- Cada proyecto puede usar moneda global o individual (override)
- Enlace del botón se construye automáticamente: `[enlace_base] + "?service=" + [título_del_servicio_url_encoded]`
- Posibilidad de enlace personalizado por proyecto

### Frontend - Shortcode
- **Shortcode**: `[proyectos_grid]`
- **Parámetros opcionales**: 
  - `category="categoria-slug"` - Filtrar por categoría específica
  - `posts_per_page="6"` - Limitar número de proyectos mostrados
- **Grid responsive**: 
  - Desktop: Múltiples columnas adaptables
  - Tablet: 2 columnas
  - Mobile: 1 columna

### Diseño de Tarjetas
Cada tarjeta incluye:
- Imagen destacada con efecto hover
- Título con estilo específico
- Descripción limitada con ellipsis
- Precio formateado: $XXXX + MONEDA
- Etiqueta (izquierda) y botón "Inscríbete hoy" (derecha)
- Bordes redondeados y sombras sutiles

### ⚠️ Importante: Interactividad de las Tarjetas
- **Solo el botón "Inscríbete hoy" es clickeable** y redirige al enlace configurado
- **El resto de la tarjeta NO es clickeable** (imagen, título, descripción, precio)
- Esta funcionalidad está diseñada para evitar confusión y dirigir la acción específicamente al botón de contacto

## Instalación

1. Sube la carpeta del plugin a `/wp-content/plugins/`
2. Activa el plugin desde el panel de administración de WordPress
3. Ve a "Proyectos" > "Configuración" para configurar la moneda global y enlace base
4. Comienza a crear proyectos desde "Proyectos" > "Añadir nuevo"

## Uso del Shortcode

### Básico
\`\`\`
[proyectos_grid]
\`\`\`

### Con filtro por categoría
\`\`\`
[proyectos_grid category="desarrollo-web"]
\`\`\`

### Ejemplos de Filtrado por Categoría

Para filtrar por categoría, usa el **slug** de la categoría (no el nombre):

\`\`\`
[proyectos_grid category="desarrollo-web"]      # Muestra solo proyectos de "Desarrollo Web"
[proyectos_grid category="diseno-grafico"]      # Muestra solo proyectos de "Diseño Gráfico"  
[proyectos_grid category="marketing-digital"]   # Muestra solo proyectos de "Marketing Digital"
\`\`\`

**Nota**: El slug se genera automáticamente desde el nombre de la categoría:
- "Desarrollo Web" → `desarrollo-web`
- "Diseño Gráfico" → `diseno-grafico`
- "Marketing Digital" → `marketing-digital`

### Limitando número de proyectos
\`\`\`
[proyectos_grid posts_per_page="6"]
\`\`\`

### Combinando parámetros
\`\`\`
[proyectos_grid category="diseno" posts_per_page="4"]
\`\`\`

## Estructura de Archivos

\`\`\`
proyectos-plugin/
├── proyectos-plugin.php          # Archivo principal del plugin
├── assets/
│   ├── style.css                 # Estilos frontend
│   └── admin-style.css           # Estilos admin
├── languages/
│   └── proyectos-grid.pot        # Archivo de traducciones
└── README.md                     # Documentación
\`\`\`

## Características Técnicas

- **Código bien estructurado** y comentado
- **Estándares de WordPress** seguidos estrictamente
- **Nonce verification** y sanitización de datos
- **Soporte para traducciones** (i18n ready)
- **CSS moderno** con Flexbox/Grid
- **Responsive design** completo
- **Accesibilidad** mejorada con estados de focus
- **Compatibilidad** con temas de WordPress

## Personalización

### Colores
Los colores principales se pueden modificar en `assets/style.css`:
- **Primario**: `#8b5cf6` (púrpura)
- **Acento**: `#3b82f6` (azul)
- **Neutrales**: Grises y blancos

### Tipografía
- **Títulos**: Geist Sans
- **Contenido**: Manrope
- Fallbacks a fuentes del sistema

## Soporte

Para soporte técnico o consultas sobre el plugin, contacta al desarrollador.

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.
