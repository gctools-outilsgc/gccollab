<?php
/**
 * Etherpads French language file
 * 
 * package ElggPad
 */

$french = array(

	/**
	 * Menu items and titles
	 */
	 
	'etherpad' => "Docs",
	'etherpad:docs' => "Docs",
	'etherpad:owner' => "Docs de %s",
	'etherpad:friends' => "Docs de los amigos",
	'etherpad:all' => "Todos los docs",
	'docs:add' => "Nuevo doc",
	'etherpad:add' => "Nuevo doc",
	'etherpad:timeslider' => 'Historial',
	'etherpad:fullscreen' => 'Pantalla completa',
	'etherpad:none' => 'No se ha creado ningún doc por el momento',
	
	'etherpad:group' => 'Doc del grupo',
	'groups:enablepads' => 'Activar docs del grupo',
	
	/**
	 * River
	 */
	'river:create:object:etherpad' => '%s creó un nuevo doc colaborativo %s',
	'river:create:object:subpad' => '%s creó un nuevo doc colaborativo %s',
	'river:update:object:etherpad' => '%s actualizó el doc colaborativo %s',
	'river:update:object:subpad' => '%s actualizó el doc colaborativo %s',
	'river:comment:object:etherpad' => '%s comentó en el doc colaborativo %s',
	'river:comment:object:subpad' => '%s comentó en el doc colaborativo %s',
	
	'item:object:etherpad' => 'Docs',
	'item:object:subpad' => 'Subdocs',

	/**
	 * Status messages
	 */

	'etherpad:saved' => "Tu doc ha sido creado satisfactoriamente.",
	'etherpad:delete:success' => "Tu doc ha sido eliminado satisfactoriamente.",
	'etherpad:delete:failure' => "Tu doc no pudo ser eliminado. Pruébalo de nuevo más tarde.",
	
	/**
	 * Edit page
	 */
	 
	 'etherpad:title' => "Título",
	 'etherpad:tags' => "Etiquetas",
	 'etherpad:access_id' => "Acceso a lectura",
	 'etherpad:write_access_id' => "Acceso a escritura",

	/**
	 * Admin settings
	 */

	'etherpad:etherpadhost' => "Dirección del host de Etherpad lite:",
	'etherpad:etherpadkey' => "Api Key de Etherpad lite:",
	'etherpad:showfullscreen' => "Mostrar full screen button?",
	'etherpad:showchat' => "Mostrar chat?",
	'etherpad:linenumbers' => "Mostar números de línea?",
	'etherpad:showcontrols' => "Mostrar controles?",
	'etherpad:monospace' => "Usar fuente monoespacio?",
	'etherpad:showcomments' => "Mostrar comentarios?",
	'etherpad:newpadtext' => "Texto de los nuevos docs:",
	'etherpad:pad:message' => 'Nuevo doc creado satisfactoriamente.',
	'etherpad:integrateinpages' => "Integrar docs y páginas? (Requirere que el plugin Pages esté activado)",
	
	/**
	 * Widget
	 */
	'etherpad:profile:numbertodisplay' => "Número de docs a mostar",
    'etherpad:profile:widgetdesc' => "Muestra tus últimos docs",
    
    /**
	 * Sidebar items
	 */
	'etherpad:newchild' => "Crea un subdoc",
);

add_translation('fr', $french);
