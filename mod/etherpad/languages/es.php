<?php
/**
 * Etherpads Spanish language file
 * 
 * package ElggPad
 */

$spanish = array(

	/**
	 * Menu items and titles
	 */
	 
	'etherpad' => "Pads",
	'etherpad:owner' => "Pads de %s",
	'etherpad:friends' => "Pads de los amigos",
	'etherpad:all' => "Todos los pads",
	'etherpad:add' => "Nuevo pad",
	'etherpad:timeslider' => 'Historial',
	'etherpad:fullscreen' => 'Pantalla completa',
	'etherpad:none' => 'No se ha creado ningún pad por el momento',
	
	'etherpad:group' => 'Pads del grupo',
	'groups:enablepads' => 'Activar pads del grupo',
	
	/**
	 * River
	 */
	'river:create:object:etherpad' => '%s creó un nuevo pad colaborativo %s',
	'river:create:object:subpad' => '%s creó un nuevo pad colaborativo %s',
	'river:update:object:etherpad' => '%s actualizó el pad colaborativo %s',
	'river:update:object:subpad' => '%s actualizó el pad colaborativo %s',
	'river:comment:object:etherpad' => '%s comentó en el pad colaborativo %s',
	'river:comment:object:subpad' => '%s comentó en el pad colaborativo %s',
	
	'item:object:etherpad' => 'Pads',
	'item:object:subpad' => 'Subpads',

	/**
	 * Status messages
	 */

	'etherpad:saved' => "Tu pad ha sido creado satisfactoriamente.",
	'etherpad:delete:success' => "Tu pad ha sido eliminado satisfactoriamente.",
	'etherpad:delete:failure' => "Tu pad no pudo ser eliminado. Pruébalo de nuevo más tarde.",
	
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
	'etherpad:showchat' => "Mostrar chat?",
	'etherpad:linenumbers' => "Mostar números de línea?",
	'etherpad:showcontrols' => "Mostrar controles?",
	'etherpad:monospace' => "Usar fuente monoespacio?",
	'etherpad:showcomments' => "Mostrar comentarios?",
	'etherpad:newpadtext' => "Texto de los nuevos pads:",
	'etherpad:pad:message' => 'Nuevo pad creado satisfactoriamente.',
	'etherpad:integrateinpages' => "Integrar pads y páginas? (Requirere que el plugin Pages esté activado)",
	
	/**
	 * Widget
	 */
	'etherpad:profile:numbertodisplay' => "Número de pads a mostar",
    'etherpad:profile:widgetdesc' => "Muestra tus últimos pads",
    
    /**
	 * Sidebar items
	 */
	'etherpad:newchild' => "Crea un sub-pad",
);

add_translation('es', $spanish);
