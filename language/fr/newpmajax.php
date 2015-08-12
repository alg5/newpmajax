<?php
/**
*
* New Private Message With Ajax extension for the phpBB Forum Software package.
* French translation by Galixte (http://www.galixte.com)
*
* @copyright (c) 2015 alg
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'PMAJAX_GROUP_ALREADY_RECIPIENT'		=> 'Le groupe %s  est déjà présent dans la liste des destinataires.',
	'PMAJAX_NO_SUCH_USER'		=> 'L’utilisateur %s n’existe pas',
	'PMAJAX_TOO_MANY_RECIPIENTS'		=> 'Vous ne pouvez pas dépasser le nombre maximum de destinataires, soit %d.',
	'PMAJAX_USER_ALREADY_RECIPIENT'		=> 'L’utilisateur %s est déjà présent dans la liste des destinataires.',
	'PMAJAX_USER_REMOVED_NO_PERMISSION'	=> 'L’utilisateur %s ne peut pas être ajouté à la liste des destinataires, puisqu’il n’est pas autorisé à lire ses messages privés.',
	'PMAJAX_USER_REMOVED_NO_PM'	=> 'L’utilisateur %s ne peut pas être ajouté à la liste des destinataires, puisqu’il a désactivé sa messagerie privée.',
));
